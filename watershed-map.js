function isIE( version, comparison ) {
    var $div = $('<div style="display:none;"/>').appendTo($('body'));
    $div.html('<!--[if '+(comparison||'')+' IE '+(version||'')+']><a>&nbsp;</a><![endif]-->');
    var ieTest = $div.find('a').length;
    $div.remove();
    return ieTest;
}

if (typeof Array.isArray === 'undefined') {
    Array.isArray = function(obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    }
}

if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function (searchElement, fromIndex) {

    var k;

    // 1. Let O be the result of calling ToObject passing
    //    the this value as the argument.
    if (this == null) {
      throw new TypeError('"this" is null or not defined');
    }

    var O = Object(this);

    // 2. Let lenValue be the result of calling the Get
    //    internal method of O with the argument "length".
    // 3. Let len be ToUint32(lenValue).
    var len = O.length >>> 0;

    // 4. If len is 0, return -1.
    if (len === 0) {
      return -1;
    }

    // 5. If argument fromIndex was passed let n be
    //    ToInteger(fromIndex); else let n be 0.
    var n = +fromIndex || 0;

    if (Math.abs(n) === Infinity) {
      n = 0;
    }

    // 6. If n >= len, return -1.
    if (n >= len) {
      return -1;
    }

    // 7. If n >= 0, then Let k be n.
    // 8. Else, n<0, Let k be len - abs(n).
    //    If k is less than 0, then let k be 0.
    k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

    // 9. Repeat, while k < len
    while (k < len) {
      var kValue;
      // a. Let Pk be ToString(k).
      //   This is implicit for LHS operands of the in operator
      // b. Let kPresent be the result of calling the
      //    HasProperty internal method of O with argument Pk.
      //   This step can be combined with c
      // c. If kPresent is true, then
      //    i.  Let elementK be the result of calling the Get
      //        internal method of O with the argument ToString(k).
      //   ii.  Let same be the result of applying the
      //        Strict Equality Comparison Algorithm to
      //        searchElement and elementK.
      //  iii.  If same is true, return k.
      if (k in O && O[k] === searchElement) {
        return k;
      }
      k++;
    }
    return -1;
  };
}

$(document).ready(function() {
    if (isIE(8, 'lte')) {
        $('#map').css({minHeight: (document.body.clientHeight - 42) + 'px'});
        $('#explanation').css({minHeight: (document.body.clientHeight - 42) + 'px'});
    }
    var dashedPolySymbol = {
        path: 'M 0,-1 0,1',
        strokeOpacity: 1,
        scale: 2
    };

    function getGeoJsonProperty(geoJsonProperties, property) {
        if (!geoJsonProperties) return null;
        if (geoJsonProperties[property]) return geoJsonProperties[property];
        else {
            for (var i = 0; i < geoJsonPropertyTranslations.length; i++) {
                if (property === geoJsonPropertyTranslations[i].name_on_website) {
                    if (geoJsonProperties[geoJsonPropertyTranslations[i].name_in_shapefile]) return geoJsonProperties[geoJsonPropertyTranslations[i].name_in_shapefile];
                }
            }
            return false;
        }
    }

    var analytics = {
        trackLayerClick: function(layerName, show) {
            ga('send', 'event', 'Layers', 'Details', layerName);
        },
        trackLayerToggle: function(layerName, show) {
            ga('send', 'event', 'Layers', 'Toggle', layerName, show ? 'Show' : 'Hide');
        },
        trackMarkerClick: function(layerName, markerName) {
            ga('send', 'event', 'Features', 'Click', markerName);
        }
    };

    //set up tabs and accordion on explanation control
    $('#explanation-details').tabs({idPrefix: 'explanation-details-layer-'});
    $('.explanation-details-layer-description-toggle i').click(function() {
        var $this = $(this),
            fixed = $this.parents('.explanation-details-layer-fixed'), fixedDescription = fixed.find('.explanation-details-layer-description'),
            parents = $this.parents('.explanation-details-layer-nopad'), hiddenDescription = parents.find('.explanation-details-layer-description.explanation-details-description-hidden');
        if ($this.hasClass('icon-chevron-up')) {
            $this.parent().css({'top': '-2em'});
            $this.removeClass('icon-chevron-up').addClass('icon-chevron-down');
            fixedDescription.slideUp();
            hiddenDescription.slideUp();
        } else {
            $this.parent().css({'top': '0'});
            $this.removeClass('icon-chevron-down').addClass('icon-chevron-up');
            fixedDescription.slideDown();
            hiddenDescription.slideDown();
        }
    });
    $('#explanation').accordion({
        autoHeight: false,
        heightStyle: 'content',
        clearStyle: true,
        activate: function() {
            $('#explanation').accordion("refresh");
        }});
    $('.layer-toggle-name').click(function() {
        var $this = $(this), index = $this.data('index'), sw = $('#layer-toggle-' + index);
        if (index !== undefined) {
            $('#explanation').accordion('option', 'active', 1); //activate details panel
            $('#explanation-details').tabs('option', 'active', $.makeArray($('#explanation-details-tabs li')).map(function(elm) {
                return elm.id;
            }).indexOf('explanation-details-tab-' + index));
            if (sw && sw[0] && !sw[0].checked) sw.click();
        }
        return false;
    });
    $('#map-popup-static-close').click(function() {
        $('#map-popup-static').hide();
    });

    //set up map
    var map = new google.maps.Map(document.getElementById('map'), {
        mapTypeId: googleMapTileType,
        center: new google.maps.LatLng(mapCenterLongitude, mapCenterLatitude),
        zoom:  mapZoom,
        mapTypeControlOptions: {position: google.maps.ControlPosition.TOP_LEFT}
    });

    var manager = new MarkerManager(map);
    var managerLoaded = false;
    google.maps.event.addListener(manager, 'loaded', function() {
        managerLoaded = true;
    });

    //set up layers
    $('#explanation-layers li').each(function(elmIndex, elm) {
        var $elm = $(elm), postId = $elm.data('post-id'), elmCategory = $elm.data('layer-category'),
            layerName = $elm.data('layer-name');
        if(elmCategory) {
            var category = $('.explanation-layers-category[data-layer-category="' + elmCategory + '"]');
            if (category.length == 0) {
                category = $('<li class="explanation-layers-category" data-layer-category="' +
                    elmCategory +
                    '"><div><span><input type="checkbox" class="layer-category-checkbox" /></span>' +
                    '<span class="layer-toggle-name"><label>' +
                    elmCategory +
                    '</label></span><ul class="layer-category"></ul></div><li>');
                category.find('input').click(function() {
                    processingCategoryTogglers = true;
                    if ($(this).is(':checked')) {
                        $.each(category.find('li input'), function(i, input) {
                            if(!$(input).is(':checked')) {
                                $(input).click();
                            }
                        });
                    } else {
                        $.each(category.find('li input'), function(i, input) {
                            if($(input).is(':checked')) {
                                $(input).click();
                            }
                        });
                    }
                    processingCategoryTogglers = false;
                });
                $elm.before(category);
            }
            category.find('ul').append($elm);
            $elm.find('span.layer-toggle-name').addClass('layer-toggle-name-indent');
        }
        var jsonUrl = $elm.data('json-location');
        var jsonUrlA = document.createElement('a');
        var windowA = document.createElement('a');
        windowA.href = window.location.href;
        jsonUrlA.href = jsonUrl;
        jsonUrlA.protocol = windowA.protocol;
        $.getJSON(jsonUrlA.href, function(data) {
            if ($elm.data('json-is-topojson')) {
                data = topojson.feature(data, data.objects[Object.keys(data.objects)[0]]);
            }
            var vector = new GeoJSON(data, {
                fillColor: $elm.data('polygon-fill'),
                fillOpacity: parseInt($elm.data('polygon-opacity')) / 100,
                strokeColor: $elm.data('line-color'),
                strokeWeight: $elm.data('line-width') || 0,
                //clickable: $elm.data('layer-show-popups') ? true : false,
                icon: $elm.find('img')[0].src
            }), input = $elm.find('input.layer-toggle')[0],
                minZoom = $elm.data('min-zoom') || null;
            function setMap(vector, map) {
                if (vector.setMap) {
                    if (minZoom) {
                        if (managerLoaded) {
                            manager[map ? 'addMarker' : 'removeMarker'](vector, minZoom);
                        } else {
                            google.maps.event.addListenerOnce(manager, 'loaded', function() {
                                try {
                                    manager[map ? 'addMarker' : 'removeMarker'](vector, minZoom);
                                } catch (err) {
                                    console.log(err);
                                }
                            });
                        }

                    } else {
                        vector.setMap(map);
                    }
                }
                else if (Array.isArray(vector)) {
                    vector.reverse();
                    jQuery.each(vector, function(elmIndex, elm) {
                        setMap(elm, map);
                    });
                }
            }
            function setUp(vector, map, z, addToVector) {
                if (vector.setMap) {
                    if (z !== undefined) vector.setOptions({zIndex: z});
                    function interact() {
                        analytics.trackMarkerClick($elm.data('layer-name'), objectid);
                        if ($elm.data('layer-show-popups')) {
                            $('#map-popup-static-container').html(detailDiv.clone(true));
                            $('#map-popup-static').show();
                        }

                        if (vector.getPosition) {
                            map.panTo(vector.getPosition());
                        } else if (vector.getPath) {
                            map.panTo(vector.getPath().getAt(0));
                        }
                    }
                    if ($elm.data('layer-show-popups')) {
                        google.maps.event.addListener(vector, 'click', function(e) {
                            interact();

                            if ($('#explanation').accordion('option', 'active') !== 1) {
                                $('#explanation').accordion('option', 'active', 1).one('accordionactivate', function() {
                                    var pos = detailDiv.position(), holder = detailDiv.parents('.explanation-details-layer-holder'),
                                        currentScrollTop = holder.scrollTop(), fixedHeight = detailDiv.parent().siblings('.explanation-details-layer-fixed').height();
                                    holder.animate({ scrollTop: currentScrollTop + pos.top - fixedHeight });
                                });
                            }
                            $('#explanation-details-tab-' + postId + ' a').click();
                        });
                    }
                    var geoJsonProperties = vector.get('geojsonProperties');
                    if (geoJsonProperties != null) {
                        var name = getGeoJsonProperty(geoJsonProperties, 'Name')  || getGeoJsonProperty(geoJsonProperties, 'LegendDesc'),
                            objectid = (geoJsonProperties['LegendDesc'] || geoJsonProperties['name'] || geoJsonProperties['Name'] || geoJsonProperties['OBJECTID_1'] || geoJsonProperties['OBJECTID']),
                            detailDiv = $('#explanation-details-layer-' + postId + ' .explanation-details-popup[data-detail-objectid="' + objectid + '"]'),
                            detailHeader, detailBody;
                        if (detailDiv.length == 0 || !$elm.data('merge-same-names')) {
                            detailDiv = $('<div class="explanation-details-popup" data-detail-objectid="' + objectid + '"><p></p></div>');
                            detailHeader = $('<h3 class="explanation-details-popup-header"></h3>');
                            detailBody = $('<table><tr><td><div class="explanation-details-popup-name"></div></td><td><span class="explanation-details-popup-photo"></span></td></tr></table>');
                            detailHeader.html($elm.data('layer-name'));
                            detailBody.find('.explanation-details-popup-name').html(getGeoJsonProperty(geoJsonProperties, 'Name'));
                            var photos = getGeoJsonProperty(geoJsonProperties, 'Photos');
                            if (photos)
                                detailBody.find('.explanation-details-popup-photo').html(photos);
                            var detailParent = detailBody.find('.explanation-details-popup-name').parent();
                            var area = getGeoJsonProperty(geoJsonProperties, 'Area');
                            if (area) {
                                detailParent.append('<div>Acres: ' + area + '</div>');
                            }
                            var items = ['Type', 'SubWatersh'];
                            for (i = 0; i < items.length; i++) {
                                item = items[i];
                                gitem = getGeoJsonProperty(geoJsonProperties, item);
                                if (gitem) {
                                    detailParent.append('<div>' + gitem + '</div>')
                                }
                            }
                            var address = getGeoJsonProperty(geoJsonProperties, 'Address');
                            if (address) {
                                var city = getGeoJsonProperty(geoJsonProperties, 'City'), zip = getGeoJsonProperty(geoJsonProperties, 'ZIP');
                                if (city && zip)
                                    address += ', ' + city + ' ' + zip;
                                detailParent.append('<div class="explanation-details-popup-address"><a target="_blank" href="http://maps.google.com?q=' + encodeURIComponent(address) + '">' + address + '</a></div>');
                            }
                            items = ['LID Elements', 'Hours of Operation', 'Phone Number', 'Notes', 'Location', 'PhoneNumbe', 'LID_Elemen'];
                            for (var i = 0; i < items.length; i++) {
                                var item = items[i];
                                var gitem = getGeoJsonProperty(geoJsonProperties, item);
                                if (gitem) {
                                    detailParent.append('<div>' + gitem + '</div>')
                                }
                            }
                            var website = getGeoJsonProperty(geoJsonProperties, 'Website');
                            if (website) {
                                var welm = document.createElement('div');
                                if (website.indexOf('<a') === -1) welm.innerHTML = '<a target="_blank" href="' + website + '">' + website + '</a>';
                                else welm.innerHTML = website;
                                if (layerName.indexOf("Beach Monitoring Location") != -1) {
                                    welm.childNodes[0].innerHTML = "Bacteriological Test Results";
                                    welm.childNodes[0].style.color = 'black';
                                }
                                else if (welm.innerHTML.indexOf("sonoma-county.org/parks") != -1) welm.childNodes[0].innerHTML = "Sonoma County Park Details";
                                else if (layerName.indexOf("Cleanup Location") != -1) {
                                    welm.childNodes[0].innerHTML = "Event Details";
                                    if (welm.innerHTML.indexOf("stormwatercreeks/events") != -1) {
                                        welm.childNodes[0].innerHTML = "Additional Details";
                                        $(welm).prepend('<a href="http://ci.santa-rosa.ca.us/departments/utilities/stormwatercreeks/steward/Pages/CREEKWEEK.aspx" target="_blank">Event Details</a><br />');
                                    }
                                }
                                else if (layerName.indexOf("RRWA Member Agency") != -1) {
                                    welm.innerHTML = '<a href="' + website + '" target="_blank">Website</a>';
                                }
                                else if (layerName.indexOf("Rain Gauge") != -1) {
                                    welm.innerHTML = '<a href="' + website + '" target="_blank">Data</a>';
                                }
                                else if (layerName.indexOf("Stream Gauge") != -1) {
                                    welm.innerHTML = '<a href="' + website + '" target="_blank">Stream Gauge Data</a>';
                                }
                                else if (layerName.indexOf("Data Logger Reach") != -1) {
                                    welm.innerHTML = '<a href="' + website + '" target="_blank">Hobo Temperature Data</a>';
                                }
                                else if (layerName.indexOf("Flood Zone") != -1) {
                                    welm.innerHTML = '<a href="' + website + '" target="_blank">Definition</a>';
                                }
                                detailParent.append(welm);
                            }
                            if (layerName.indexOf('Monitoring Location') == 0) {
                                var welm = document.createElement('div');
                                var chartUrl = 'colgan_creek_chart';
                                if ($elm.data('map') !== 'Colgan Creek Subwatershed') chartUrl = 'petaluma_river_chart';
                                $(welm).append('<div><a href="http://watershedview.com/wp-content/themes/watershedview/partials/' + chartUrl + '.php?locations[]=' + objectid + '" target="_blank">Water Quality Test Data</a></div>');
                                detailParent.append(welm);
                            }
                            var legendDesc = getGeoJsonProperty(geoJsonProperties, 'LegendDesc');
                            if (legendDesc) {
                                var bgColor = getGeoJsonProperty(geoJsonProperties, 'fillColor') ||
                                        $elm.data('polygon-fill') || "#000000",
                                    legendBox = $('<td width="25" class="explanation-details-popup-legend-box"></td>'),
                                    legendDescDiv = $('<td></td>'),
                                    legendDiv = $('<div><table class="explanation-details-popup-legend"><tr></tr></table></div>');
                                legendDescDiv.html("&nbsp;" + legendDesc);
                                if (legendDesc.length > 45) {
                                    legendDescDiv.css({
                                        'font-size': '8px'
                                    });
                                }
                                legendBox.css({'background-color': bgColor});
                                legendDiv.find('tr').append(legendBox).append(legendDescDiv);
                                detailParent.append(legendDiv);
                                if (welm) legendDescDiv.append(welm);
                            }
                            items = ['Acres', 'Source'];
                            for (i = 0; i < items.length; i++) {
                                item = items[i];
                                gitem = getGeoJsonProperty(geoJsonProperties, item);
                                if (gitem) {
                                    detailParent.append('<div>' + item + ': ' + gitem + '</div>')
                                }
                            }
                            items = ['strokeColor', 'strokeOpacity', 'strokeWeight', 'fillColor', 'fillOpacity'];
                            for (i = 0; i < items.length; i++) {
                                item = items[i];
                                gitem = getGeoJsonProperty(geoJsonProperties, item);
                                if (gitem) {
                                    if (vector.hasOwnProperty(item)) {
                                        var optionToSet = {};
                                        optionToSet[item] = gitem;
                                        vector.setOptions(optionToSet);
                                    }
                                }
                            }
                            detailDiv.prepend(detailHeader);
                            detailDiv.find('p').append(detailBody);
                            if (geoJsonProperties.Name || geoJsonProperties.LegendDesc || !$elm.data('layer-hide-nameless-features'))
                            {
                                var detailDivs = $('#explanation-details-layer-' + postId + ' .explanation-details-popup');
                                var inserted = false, lastGoodInsertIndex = -1;
                                if (!$elm.data('dont-alphabetize-features')) {
                                    for (var detailDivIndex = 0; detailDivIndex < detailDivs.length; detailDivIndex++) {
                                        var dd = $(detailDivs[detailDivIndex]), dobjectid = dd.data('detail-objectid');
                                        if (dobjectid === "undefined") {
                                            dd.before(detailDiv);
                                            inserted = true;
                                            break;
                                        }
                                        else if (objectid !== undefined && (objectid.localeCompare && objectid.localeCompare(dobjectid) < 0) || (!objectid.localeCompare && objectid < dobjectid)) {
                                            dd.before(detailDiv);
                                            inserted = true;
                                            break;
                                        }
                                        else if (objectid !== undefined) {
                                            lastGoodInsertIndex = detailDivIndex;
                                        }
                                    }
                                }
                                if (!inserted)
                                {
                                    if (lastGoodInsertIndex >= 0) {
                                        $(detailDivs[lastGoodInsertIndex]).after(detailDiv);
                                    }
                                    else
                                    {
                                        $('#explanation-details-layer-' + postId + ' .explanation-details-layer-details').after(detailDiv);
                                    }
                                }
                            }
                        }
                        detailDiv.click(interact);
                        google.maps.event.addListener(vector, 'mouseover', function(ply) {
                            if (name) {
                                tooltip.show(name);
                            }
                        });
                        google.maps.event.addListener(vector, 'mouseout', function(ply) {
                            if (name) tooltip.hide();
                        });
                        google.maps.event.addListener(vector, 'mousedown', function(ply) {
                            if (name) tooltip.hide();
                        });
                        detailDiv.hover(function() {
                            setAnimation(vector, true);
                        }, function() {
                            setAnimation(vector, false);
                        });
                    }
                    if ($elm.data('line-dash')) {
                        if (vector.getPaths) {
                            vector.getPaths().forEach(function(path) {
                                var pline = new google.maps.Polyline({
                                    strokeColor: $elm.data('line-color'),
                                    strokeWeight: $elm.data('line-width') || 0,
                                    //clickable: $elm.data('layer-show-popups') ? true : false,
                                    zIndex: z,
                                    path: path,
                                    icons: [{icon: dashedPolySymbol, offset: '0', repeat: '10px'}],
                                    strokeOpacity: 0
                                });
                                addToVector.push(pline);
                            });
                            vector.setOptions({strokeOpacity: 0});
                        } else if (vector.getPath) {
                            vector.setOptions({
                                icons: [{icon: dashedPolySymbol, offset: '0', repeat: '10px'}],
                                strokeOpacity: 0
                            });
                        }
                    }
                    if (!vector.getPaths && vector.getPath) {
                        var pline = new google.maps.Polyline({
                            strokeColor: $elm.data('line-color'),
                            strokeWeight: ($elm.data('line-width') || 0) * 10,
                            strokeOpacity: 0.01,
                            //clickable: $elm.data('layer-show-popups') ? true : false,
                            zIndex: z,
                            path: vector.getPath()
                        });
                        //pline.setMap(map);
                        addToVector.push(pline);
                        google.maps.event.addListener(pline, 'mouseover', function(ply) {
                            if (name) {
                                tooltip.show(name);
                            }
                        });
                        google.maps.event.addListener(pline, 'mouseout', function(ply) {
                            if (name) tooltip.hide();
                        });
                        google.maps.event.addListener(pline, 'mousedown', function(ply) {
                            if (name) tooltip.hide();
                        });
                        google.maps.event.addListener(pline, 'click', function(ply) {
                            interact();
                        });
                    }
                }
                else if (Array.isArray(vector)) {
                    vector.reverse();
                    jQuery.each(vector, function(elmIndex, elm) {
                        setUp(elm, map, z, addToVector);
                    });
                }
            }
            function setAnimation(vector, animation) {
                if (Array.isArray(vector)) {
                    jQuery.each(vector, function(elmIndex, elm) {
                        setAnimation(elm, animation);
                    });
                } else if (vector.setAnimation) {
                    if (vector.getMap()) {
                        vector.setAnimation(animation ? google.maps.Animation.BOUNCE : null);
                        vector.setOptions({z: animation ? 100 : $elm.data('layer-z') || 0});
                    }
                } else {
                    var geoJsonProperties = vector.get('geojsonProperties');
                    vector.setOptions( {
                        fillColor: animation ? 'black' :
                            getGeoJsonProperty(geoJsonProperties, 'fillColor') || $elm.data('polygon-fill'),
                        fillOpacity: animation ? '0.5' : parseInt(getGeoJsonProperty(geoJsonProperties, 'fillOpacity') || $elm.data('polygon-opacity')) / 100,
                        strokeColor: animation ? 'white' : getGeoJsonProperty(geoJsonProperties, 'strokeColor') || $elm.data('line-color'),
                        strokeWeight: animation ? 3 : getGeoJsonProperty(geoJsonProperties, 'strokeWeight') || $elm.data('line-width') || 0,
                        zIndex: animation ? 100 : $elm.data('layer-z') || 0
                    });
                }
            }
            var addToVector = [];
            setUp(vector, map, $elm.data('layer-z'), addToVector);
            if (addToVector.length > 0) vector = vector.concat(addToVector);
            setMap(vector, $elm.data('layer-enabled') ? map : null);
            input.checked = $elm.data('layer-enabled');
            input.disabled = false;
            $elm.find('.layer-loading').hide();
            if (minZoom) {
                var updateItemBasedOnZoom = function() {
                    var zoom = map.getZoom();
                    input.disabled = zoom < minZoom;
                    $elm.find('label').css('color', zoom < minZoom ? 'gray' : 'rgb(34, 34, 34)');
                };
                google.maps.event.addListener(map, 'zoom_changed', updateItemBasedOnZoom);
                updateItemBasedOnZoom();
            }
            $(input).click(function() {
                var category = $(this).parents('.layer-category'), all = true;
                if ($(this).is(':checked')) {
                    analytics.trackLayerToggle($elm.data('layer-name'), true);
                    setMap(vector, map);
                } else {
                    analytics.trackLayerToggle($elm.data('layer-name'), false);
                    setMap(vector, null);
                }
                if (!processingCategoryTogglers) {
                    if (category.length > 0) {
                        jQuery.each(jQuery.makeArray(category.find('li input')), function(inputIndex, input) {
                            all &= input.checked;
                        });
                        category.parent().find('.layer-category-checkbox')[0].checked = all;
                    }
                }
                if (!processingTogglers) {
                    all = true;
                    jQuery.each(jQuery.makeArray($('#explanation-layers .layer-toggle')), function(inputIndex, input) {
                        all &= input.checked;
                    });
                    document.getElementById('explanation-layers-all').checked = all;
                }
            });
            $elm.hover(function() {
                setAnimation(vector, true);
            }, function() {
                setAnimation(vector, false);
            });
        });
    });
    var processingTogglers = false, processingCategoryTogglers = false;
    $('#explanation-layers-all').click(function() {
        processingTogglers = true;
        jQuery.each(jQuery.makeArray($('.layer-toggle')), function(inputIndex, input) {
            if (input.checked != $('#explanation-layers-all').is(':checked')) $(input).click();
        });
        processingTogglers = false;
    });


    var tooltip = function() {
        var id = 'tt';
        var top = 3;
        var left = 3;
        var maxw = 300;
        var speed = 10;
        var timer = 20;
        var endalpha = 95;
        var alpha = 0;
        var tt,t,c,b,h;
        var ie = document.all ? true : false;
        return {
            show:function(v,w){
                if(tt == null){
                    tt = document.createElement('div');
                    tt.setAttribute('id',id);
                    t = document.createElement('div');
                    t.setAttribute('id',id + 'top');
                    c = document.createElement('div');
                    c.setAttribute('id',id + 'cont');
                    b = document.createElement('div');
                    b.setAttribute('id',id + 'bot');
                    tt.appendChild(t);
                    tt.appendChild(c);
                    tt.appendChild(b);
                    document.body.appendChild(tt);
                    tt.style.display = 'none';
                    var tooltip = this;
                    $(document).mousemove(function(e){ tooltip.pos(e); });
                    //document.onmousemove = this.pos;
                }
                tt.style.display = 'block';
                c.innerHTML = v;
                tt.style.width = w ? w + 'px' : 'auto';
                if(!w && ie){
                    t.style.display = 'none';
                    b.style.display = 'none';
                    tt.style.width = tt.offsetWidth;
                    t.style.display = 'block';
                    b.style.display = 'block';
                }
                if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
                h = parseInt(tt.offsetHeight) + top;
                tt.style.display = 'inline';
            },
            pos:function(e){
                var u = e.pageY;
                var l = e.pageX;
                tt.style.top = (u - h) + 'px';
                tt.style.left = (l + left) + 'px';
            },
            fade:function(d){
                var a = alpha;
                if((a != endalpha && d == 1) || (a != 0 && d == -1)){
                    var i = speed;
                    if(endalpha - a < speed && d == 1){
                        i = endalpha - a;
                    }else if(alpha < speed && d == -1){
                        i = a;
                    }
                    alpha = a + (i * d);
                    tt.style.opacity = alpha * .01;
                    tt.style.filter = 'alpha(opacity=' + alpha + ')';
                }else{
                    clearInterval(tt.timer);
                    if(d == -1){tt.style.display = 'none'}
                }
            },
            hide:function(){
                tt.style.display = 'none';
                //clearInterval(tt.timer);
                //tt.timer = setInterval(function(){tooltip.fade(-1)},timer);
            }
        };
    }();

    if (isIE(8, 'lte')) {
        $('#explanation').accordion("option", "animated", false);
        $('#explanation-details').css({minHeight: (document.body.clientHeight - 132) + 'px'});
        $('.explanation-details-layer-details .explanation-details-popup').css({minHeight: 10 + 'px'});
    }
});