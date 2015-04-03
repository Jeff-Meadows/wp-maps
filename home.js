function isIE( version, comparison ) {
    var $div = $('<div style="display:none;"/>').appendTo($('body'));
    $div.html('<!--[if '+(comparison||'')+' IE '+(version||'')+']><a>&nbsp;</a><![endif]-->');
    var ieTest = $div.find('a').length;
    $div.remove();
    return ieTest;
}

$(document).ready(function() {
    var isIE8 = isIE(8);
    function rescaleMap() {
        var $map = $('#map'),
            mapWidth = $map.width(),
            containerHeight = $('#container').height(),
            scale = containerHeight / 700;
        $map.css({
            'transform-origin': 'left top',
            transform: 'scale(' + scale + ')',
            width: (1 / scale * 100) + '%'
        });
        if (isIE8) {
            $map.css({
                zoom: scale
            });
        }
        $('.wv-overlay-info').css({
            'transform-origin': 'left top',
            transform: 'translateX(' + (1 / scale) + ')'
        });
    }
    rescaleMap();
    $(window).on('resize', rescaleMap);
    var grayscale = new L.tileLayer(themedir + "/assets/img/tiles/{z}/{x}/{y}.png", {
            key: '34af144782e94f61b39d811d5fd27774',
            minZoom: 9,
            maxZoom: 9
        }),
        labels = [
            { "lat": 38.158628, "lng": -123.277517, "label": "Pacific Ocean", "labelClass": "water"},
            { "lat": 38.107067, "lng": -122.472844, "label": "San Pablo <br />Bay", "labelClass": "water"},
            { "lat": 39.143831, "lng": -123.585134, "label": "MENDOCINO", "labelClass": "county"},
            { "lat": 38.091067, "lng": -122.796, "label": "MARIN", "labelClass": "county"},
            { "lat": 38.553042, "lng": -123.056667, "label": "SONOMA", "labelClass": "county"}
        ],
        map = new L.Map("map", {
            center: new L.LatLng(38.7, -123.6),
            zoom: 9,
            dragging: false,
            touchZoom: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            zoomControl: false,
            attributionControl: false
        }),
        bounds, loaded = 0,
        info = {};
    map.addLayer(grayscale);
    info.onAdd = function() {
        //this._div = L.DomUtil.create('div', 'wv-overlay-info');
        this._div = $('<div class="wv-overlay-info"></div>');
        $('#watershed-info-overlays').append(this._div);
        this.update();
        return this._div;
    };
    info.update = function(props) {
        if (props) {
            this._div.html('<h4>' + props.name + '</h4><p>' + props.description + '</p>');
            $(this._div).show();
        } else {
            $(this._div).hide();
        }
    };
    info.onAdd();
    function makeMapLabels() {
        var overlayPane = $('#map-overlays');
        overlayPane.empty();
        labels.forEach(function(label) {
            var point = map.latLngToContainerPoint(new L.LatLng(label.lat, label.lng));
            overlayPane.append('<div class="map-overlay-label ' + label.labelClass + '" style="left: ' + point.x + 'px; top: ' + point.y + 'px;">' + label.label + '</div>');
        });
    }
    makeMapLabels();
    map.on('viewreset', makeMapLabels);
    map.on('moveend', makeMapLabels);
    map.on('resize', makeMapLabels);
    layerInfos.unshift({
        'location': themedir + "/assets/geo/sonomamendomarin.geojson",
        'z': -2
    });
    layerInfos.forEach(function(layerInfo) {
        if (!layerInfo.location || layerInfo.exclude) {
            loaded++;
            return;
        }
        $.getJSON(layerInfo.location, function(data) {
            var g = layerInfo.layer = L.geoJson(data, {
                style: {
                    fillOpacity: layerInfo.color ? 0.7 : 0,
                    fillColor: layerInfo.color,
                    color: 'white',
                    dashArray: '',
                    opacity: 1,
                    weight: 1
                },
                onEachFeature: layerInfo.hoverColor ? function(feature, layer) {
                    layer.on({
                        mouseover: function(e) {
                            var layer = e.target;
                            layer.setStyle({
                                weight: 2,
                                fillColor: layerInfo.hoverColor,
                                dashArray: '',
                                fillOpacity: 0.9,
                                dropShadow: true
                            });
                            info.update(layerInfo);
                            //if (!L.Browser.ie && !L.Browser.opera) layer.bringToFront();
                        },
                        mouseout: function(e) {
                            g.resetStyle(e.target);
                            layer.setStyle({
                                fillOpacity: 0.7,
                                fillColor: layerInfo.color,
                                color: 'white',
                                dashArray: '',
                                opacity: 1,
                                weight: 1,
                                dropShadow: false
                            });
                            info.update();
                        },
                        click: function(e) {
                            window.location.href = layerInfo.link;
                        }
                    });
                } : null
            });
            if (layerInfo.id) {
                if (!bounds) bounds = g.getBounds();
                else bounds.extend(g.getBounds());
            }
            loaded++;
            if (loaded === layerInfos.length) {
                layerInfos.sort(function(a, b) {
                    return a.z - b.z;
                });
                layerInfos.forEach(function(layerInfo) {
                    if (layerInfo.location && !layerInfo.exclude) {
                        layerInfo.layer.addTo(map);
                    }
                });
                if (bounds) map.fitBounds(bounds, {paddingTopLeft: new L.Point(300, 50), paddingBottomRight: new L.Point(-100, 50)});
            }
        });
    });
    function registerExpandos() {
        function expandExpando() {
            var $this = $(this),
                height = $this.find('.wv-expando-inner').height(),
                postId = $this.data('post-id'),
                layerInfo = $.grep(layerInfos, function (layerInfo) {
                    return layerInfo.id == postId;
                })[0];
            $this.stop().animate({
                height: (height + 50) + 'px'
            }, 'fast').find('.wv-expando-button').text('-');
            $this.find('.wv-expando-outer').stop().animate({
                height: (height + 50) + 'px'
            }, 'fast');
            $this.find('.wv-expando-img').stop().animate({
                bottom: '110px'
            }, 'fast');
            info.update(layerInfo);
            if (layerInfo.layer) {
                layerInfo.layer.setStyle({
                    weight: 2,
                    fillColor: layerInfo.hoverColor,
                    dashArray: '',
                    fillOpacity: 0.9,
                    dropShadow: true
                });
            }
        }

        function contractExpando() {
            var $this = $(this),
                postId = $this.data('post-id'),
                layerInfo = $.grep(layerInfos, function (layerInfo) {
                    return layerInfo.id == postId;
                })[0];
            $this.stop().animate({
                height: '100px'
            }, 'fast').find('.wv-expando-button').text('+');
            $this.find('.wv-expando-outer').stop().animate({
                height: '75px'
            }, 'fast');
            $this.find('.wv-expando-img').stop().animate({
                bottom: '60px'
            }, 'fast');
            info.update();
            if (layerInfo.layer) {
                layerInfo.layer.resetStyle(layerInfo.layer);
                layerInfo.layer.setStyle({
                    fillOpacity: 0.7,
                    fillColor: layerInfo.color,
                    color: 'white',
                    dashArray: '',
                    opacity: 1,
                    weight: 1,
                    dropShadow: false
                });
            }
        }

        $('.wv-expando').hover(expandExpando, contractExpando);
        $('.wv-expando-button').click(function() {
            var $this = $(this);
            if ($this.text() === '+') expandExpando.call($this.parents('.wv-expando'));
            else contractExpando.call($this.parents('.wv-expando'));
        });
        $.each($('#wv-expando-container .wv-expando'), function(expandoIndex, expando) {
            $(expando).addClass('wv-expando-wv-expando-ie-' + (expandoIndex + 1));
        });
    }
    registerExpandos();

    var wvExpandoIndex = 0,
        $map = $('#map'),
        mapWidth = $map.width(),
        wvNumExpandosVisible = function(mapWidth) {
            if (mapWidth > 1000) return 3;
            if (mapWidth > 700) return 2;
            return 1;
        }(mapWidth);
    $('#wv-expando-next').click(function() {
        var overlays = $('#watershed-overlays-all .wv-expando'), numOverlays = overlays.length, nextOverlays = [];
        for (var i = 1; i <= wvNumExpandosVisible; i++) {
            nextOverlays.push((wvExpandoIndex + i) % numOverlays);
        }
        wvExpandoIndex = (wvExpandoIndex + 1) % numOverlays;
        nextOverlays = nextOverlays.map(function(n) {
            return overlays[n];
        });
        $('#wv-expando-container').quicksand(nextOverlays, {
            attribute: 'data-post-id'
        }, registerExpandos);
    });
    $('#wv-expando-prev').click(function() {
        var overlays = $('#watershed-overlays-all .wv-expando'), numOverlays = overlays.length, nextOverlays = [];
        for (var i = -1; i < wvNumExpandosVisible - 1; i++) {
            nextOverlays.push(((wvExpandoIndex + i) % numOverlays + numOverlays) % numOverlays);
        }
        wvExpandoIndex = ((wvExpandoIndex - 1) % numOverlays + numOverlays) % numOverlays;
        nextOverlays = nextOverlays.map(function(n) {
            return overlays[n];
        });
        $('#wv-expando-container').quicksand(nextOverlays, {
            attribute: 'data-post-id'
        }, registerExpandos);
    });
});
