(function ($) {

	window.apidaeMaps = [];
	var WPlusPlusApidae = typeof window.WPlusPlusApidae !== 'undefined' ? window.WPlusPlusApidae : {maps: {}};


	//Callback function to init maps
	window.initApidaeMaps = function () {
		$(function () {
			$('.apidae-google-maps').apidaeMap();
		});
	};

	//Pseudo jQuery plugin to prepare map elements before Google Maps API has been loaded
	$.fn.apidaeMap = function () {
		$(this).each(function () {
			//Use data attributes if defined
			var settings = $(this).data();

			//Create the settings object and use defaults if value isn't set
			settings = $.extend({
				type: 'roadmap',	// ROADMAP|SATELLITE|HYBRID|TERRAIN
				center: null,
				animation: 'drop',
				zoom: null,
				tilt: null,
				controls: false,
				disableUi: false,
				panorama: false,
				scrollwheel: true,
				draggable: true,
				mapStyle: null,
				animationDuration: 2000,
				clusterImagePath: WPlusPlusApidae.maps.clusterImagePath || false,
				useClusters: false,
				useSpiderfier: false
			}, settings);

			var map,
				markers = [],
				bounds,
				last_marker = false,
				infoWindow = new google.maps.InfoWindow();

			//Plugin init
			var init = function (container) {
				for (var i = 0; i < markerNodes.length; i++) {
					markerNodes[i].position = new google.maps.LatLng(parseFloat(markerNodes[i].lat), parseFloat(markerNodes[i].lng));
				}
				//Calculate center
				if (settings['center'] == null && markerNodes.length > 0) {
					bounds = new google.maps.LatLngBounds();
					for (i = 0; i < markerNodes.length; i++) {
						bounds.extend(markerNodes[i].position);
					}
					var center = bounds.getCenter();
				}

				//Init the map
				map = new google.maps.Map(container, {
					zoom: settings['zoom'] || 12,
					center: center,
					mapTypeControl: settings['controls'],
					disableDefaultUI: settings['disableUi'],
					scrollwheel: settings['scrollwheel'],
					draggable: settings['draggable'],
					styles: JSON.parse(decodeURIComponent(settings['mapStyle'])),
					mapTypeId: google.maps.MapTypeId[settings['type'].toUpperCase()]
				});

				window.apidaeMaps.push(map);

				//Fit bounds if zoom isn't set
				if (settings['zoom'] == null) {
					map.fitBounds(bounds);
				}

				//Set tilt
				if (settings['tilt'] != null) {
					map.setTilt(settings['tilt']);
				}

				if (markerNodes.length > 0) {
					render();
				}
				$(container).trigger('apidae-map-loaded', [map, markers]);
			};

			//Render markers
			var render = function () {
				//clearMarkers();
				var time = settings['animationDuration'] / markerNodes.length;
				for (var i = 0; i < markerNodes.length; i++) {
					if (settings['animation'] == '' || settings['animation'] == 'none') {
						addMarker(markerNodes[i]);
					} else {
						addMarkerWithTimeout(markerNodes[i], i * time);
					}
				}
				if (settings['useClusters'] == true) {
					if (settings['animation'] != 'none' && settings['animation'] != '') {
						setTimeout(function () {
							new MarkerClusterer(map, markers, {imagePath: settings['clusterImagePath']});
						}, settings['animationDuration'] + 100); //We add a little margin to make sur all markers are added
					}
					else {
						new MarkerClusterer(map, markers, {imagePath: settings['clusterImagePath']});
					}
				}
			};

			var showInfo = function (marker) {
				if (last_marker !== false) {
					closeInfo(last_marker);
				}
				last_marker = marker;
				infoWindow.setContent(marker.info);
				infoWindow.open(map, marker);
				marker.setAnimation(google.maps.Animation.BOUNCE);
			};

			var closeInfo = function (marker) {
				marker.setAnimation(null);
				infoWindow.close();
			};

			//Add marker to specific position with specific icon
			var addMarker = function (pin) {
				if (typeof pin.addMarker === 'function') {
					pin.addMarker(pin, markers, map, showInfo);
					return;
				}
				var html = "<a href='" + pin.link + "'><b>" + pin.name + "</b></a> <br/>" + pin.addressLine1 + "<br/>" + pin.addressLine2;
				var _marker = new google.maps.Marker({
					position: pin.position,
					map: map,
					icon: pin.icon,
					title: pin.name,
					info: html
				});
				markers.push(_marker);

				if (pin.auto_open) {
					showInfo(_marker);
				}

				//Add info window
				if (pin.info != '') {
					google.maps.event.addListener(_marker, 'click', function () {
						showInfo(_marker);
						$('#' + pin.id).addClass('pin-active');
					});
					$('#' + pin.id).on('hover', function () {
						showInfo(_marker);
					});
				}
			};

			//Add marker to specific position with specific icon, uses the specified animation
			var addMarkerWithTimeout = function (pin, timeout) {
				setTimeout(function () {
					addMarker(pin);
				}, timeout);
			};

			//Init the map
			init(this);
		});
	};
}(jQuery));
