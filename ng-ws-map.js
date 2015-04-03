var ws = angular.module('watershedview', ['rx']);

ws.factory('wsAnalytics', ['ga', function(ga) {
    return {
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
}]);

ws.value('wsThemeDir', 'http://watershedview.com/wp-content/themes/watershedview/');

ws.service('wsMapCache', ['google', 'wsMarkerManager', function(google, wsMarkerManager) {
    var _maps = {};
    this.setMap = function(id, map) {
        var entry = _maps[id] = {
            map: map,
            manager: wsMarkerManager.createManager(map),
            managerLoaded: false
        };
        google.maps.event.addListenerOnce(entry.manager, 'loaded', function() { entry.managerLoaded = true; });
    }
}]);

ws.directive('wsGoogleMap', ['wsMapCache', 'google', function(wsMapCache, google) {
    return {
        restrict: 'EA',
        replace: true,
        template: '<div class="ws-google-map"></div>',
        scope: {
            id: '@',
            centerLatitude: '@',
            centerLongitude: '@',
            zoom: '@',
            mapType: '@='
        },
        controller: function($scope, $element, $attrs) {
        },
        link: function(scope, element, attrs) {
            scope.map = new google.maps.Map(
                element[0],
                {
                    center: new google.maps.LatLng(attrs.centerLatitude, attrs.centerLongitude),
                    zoom: attrs.zoom,
                    mapTypeId:  attrs.mapTypeId || google.maps.mapType.SATELLITE,
                    mapTypeControlOptions: { position: google.maps.ControlPosition.TOP_LEFT }
                }
            );
            wsMapCache.setMap(attrs.id, scope.map);
        }
    };
}]);

ws.directive('wsMapLayer', [function() {
    return {
        restrict: 'EA',
        replace: true,
        template: '',
        scope: {

        },
        controller: function($scope, $element, $attrs) {

        },
        link: function(scope, element, attrs) {

        }
    };
}]);

ws.directive('wsMap', ['wsThemeDir', function(wsThemeDir) {
    return {
        restrict: 'EA',
        replace: true,
        templateUrl: wsThemeDir + 'partials/ws-map.html',
        scope: {

        }
    }
}]);

ws.directive('wsTabbedBars', [function() {

}]);

ws.directive('wsLayerDetail', [function() {

}]);

ws.directive('wsDetail', [function() {

}]);

ws.directive('wsStaticDetail', ['wsThemeDir', function(wsThemeDir) {
    return {
        restrict: 'EA',
        replace: true,
        templateUrl: wsThemeDir + 'partials/ws-static-detail.html',
        scope: {
            title: '=',
            body: '='
        }
    }
}]);
