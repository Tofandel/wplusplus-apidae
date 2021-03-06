/*
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

(function ($) {
	window.apidaeMaps = [];
	var WPlusPlusApidae = typeof window.WPlusPlusApidae !== 'undefined' ? window.WPlusPlusApidae : {maps: {}},
		inited = false;

	//Callback function to init maps

	window.initApidaeMaps = function () {
		if (!inited) {
			inited = true;
			$(function () {
				$('.apidae-google-maps').apidaeMap();
			});
			clearInterval(try_init);
		}
	};

	function try_init() {
		//Fallback if the script with the callback is not enqueued
		if (typeof google === 'object' && typeof google.maps === 'object') {
			initApidaeMaps();
		}
	}

	setInterval(try_init, 100);


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
				if (typeof markerNodes !== 'undefined') {
					for (var i = 0; i < markerNodes.length; i++) {
						markerNodes[i].position = new google.maps.LatLng(parseFloat(markerNodes[i].lat), parseFloat(markerNodes[i].lng));
					}
				}
				//Calculate center
				if (settings['center'] == null && typeof markerNodes !== 'undefined' && markerNodes.length > 0) {
					bounds = new google.maps.LatLngBounds();
					for (i = 0; i < markerNodes.length; i++) {
						bounds.extend(markerNodes[i].position);
					}
					var center = bounds.getCenter();
				} else if (settings['center'] == null) {
					center = new google.maps.LatLng(48, 0);
				}

				//Init the map
				map = new google.maps.Map(container, {
					zoom: settings['zoom'] || 12,
					center: center,
					mapTypeControl: settings['controls'],
					disableDefaultUI: settings['disableUi'],
					scrollwheel: settings['scrollwheel'],
					draggable: settings['draggable'],
					styles: settings['mapStyle'] ? JSON.parse(decodeURIComponent(settings['mapStyle'])) : null,
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

			if (settings['useSpiderfier']) {
				var oms = false,
					addMarker = function (pin) {
						if (typeof pin.addMarker === 'function') {
							pin.addMarker(pin, markers, map, showInfo);
							return;
						}
						oms = oms ? oms : new OverlappingMarkerSpiderfier(map, {
							markersWontMove: true,
							markersWontHide: true,
							basicFormatEvents: true
						});
						var html = "<a href='" + pin.link + "'><b>" + pin.name + "</b></a> <br/>" + pin.addressLine1 + "<br/>" + pin.addressLine2;
						var _marker = new google.maps.Marker({
							position: pin.position,
							icon: pin.icon,
							title: pin.name,
							info: html,
							pin: pin
						});
						oms.addMarker(_marker);
						markers.push(_marker);

						if (pin.auto_open) {
							showInfo(_marker);
						}

						//Add info window
						if (pin.info != '') {
							google.maps.event.addListener(_marker, 'spider_click', function () {
								showInfo(_marker);
								$('#' + pin.id).addClass('pin-active');
							});
							$('#' + pin.id).on('hover', function () {
								showInfo(_marker);
							});
						}
					};
			} else {
				//Add marker to specific position with specific icon
				addMarker = function (pin) {
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
			}

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
