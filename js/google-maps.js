(function ($) {

	window.apidaeMaps = [];

	//Callback function to init maps
	window.initApidaeMaps = function () {
		$(function () {
			$('.apidae-google-maps').each(function () {
				$(this).apidaeMap();
			});
		});
	};

	//Pseudo jQuery plugin to prepare map elements before Google Maps API has been loaded
	$.fn.apidaeMap = function () {
		$(this).each(function () {
			//Use data attributes if defined
			var settings = $(this).data();

			//Create the settings object and use defaults if value isn't set
			settings = $.extend({
				type: 'roadmap',	// ROADMAP|SATELLITE|HYBRID|TERRAIN|PANORAMA
				center: null,
				animation: 'drop',
				delay: 0,
				zoom: null,
				tilt: null,
				controls: false,
				disableUi: false,
				panorama: false,
				scrollwheel: true,
				draggable: true,
				panoramaHeading: 0,
				panoramaPitch: 0,
				panoramaControls: true,
				panoramaEffect: 'slideLeft',
				mapStyle: null
			}, settings);

			var map;
			var panorama;
			var markers = [];
			var pins = [];
			var bounds;
			var that = $(this);
			var panorama_margin;

			//Plugin init
			var init = function (container) {
				//Calculate center
				if (settings['center'] == null && pins.length > 0) {
					bounds = new google.maps.LatLngBounds();
					for (var i in pins) {
						bounds.extend(pins[i]['position']);
					}
					var center = bounds.getCenter();
				}

				if (settings['type'].match(/^panorama$/i)) {
					//Init panorama
					panorama = new google.maps.StreetViewPanorama(
						container, {
							position: center,
							addressControlOptions: {
								position: google.maps.ControlPosition.BOTTOM_CENTER
							},
							disableDefaultUI: !settings['panoramaControls']
						});
				}
				else {
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

					//Panorama (street view)
					if (settings['panorama'] == true) {
						//Set margin based on selected show/hide effect
						if (settings['panoramaEffect'].match(/^slide/)) {
							if (settings['panoramaEffect'] == 'slideLeft') {
								panorama_margin = '0 0 0 ' + $(that).css('width');
							}
							else if (settings['panoramaEffect'] == 'slideRight') {
								panorama_margin = '0 0 0 -' + $(that).css('width');
							}
							else if (settings['panoramaEffect'] == 'slideUp') {
								panorama_margin = $(that).css('height') + ' 0 0 0';
							}
							else if (settings['panoramaEffect'] == 'slideDown') {
								panorama_margin = '-' + $(that).css('height') + ' 0 0 0';
							}
						}
						else {
							panorama_margin = '0';
						}
						var panorama_pseudo = $('<div>', {
							'class': 'panorama-pseudo-container',
							'style': 'position:absoulute;top:0px;left:-0px;z-index:1;width:100%;height:100%;transition: margin 0.8s ease;margin:' + panorama_margin
						}).prependTo($(that)).get(0);

						//Init panorama
						panorama = new google.maps.StreetViewPanorama(
							panorama_pseudo, {
								position: center,
								addressControlOptions: {
									position: google.maps.ControlPosition.BOTTOM_CENTER
								},
								disableDefaultUI: !settings['panoramaControls']
							});

						//Set the control
						var panoramaCenterControl = document.createElement('div');
						var panoramaControl = new CreateControl('panoramaSlideOut', panoramaCenterControl, panorama);

						panorama.controls[google.maps.ControlPosition.TOP_RIGHT].push(panoramaControl);
					}
					//Fit bounds if zoom isn't set
					if (settings['zoom'] == null) {
						map.fitBounds(bounds);
					}

					//Set tilt
					if (settings['tilt'] != null) {
						map.setTilt(settings['tilt']);
					}
				}

				if (pins.length > 0) {
					setTimeout(render, settings['delay']);
				}

			};

			//Start address geocode loop
			init();


			//Render markers
			var render = function () {
				//clearMarkers();
				for (var i in pins) {
					if (settings['animation'] != 'none') {
						addMarkerWithTimeout(pins[i], i * 200);
					}
					else {
						addMarker(pins[i]);
					}
				}
			};

			//Add marker to specific position with specific icon
			//TODO
			var addMarker = function (pin) {
				var _marker = new google.maps.Marker({
					position: pin.position,
					map: map,
					icon: pin.icon,
					info: pin.info
				});
				markers.push(_marker);

				if (pin.auto_open) {
					pin.info.open(map, _marker);
				}

				//Add info window
				if (typeof pin.info.content != 'undefined') {
					_marker.addListener('click', function () {
						pin.info.open(map, _marker);
					});
				}
			};

			//Add marker to specific position with specific icon, uses the specified animation
			var addMarkerWithTimeout = function (pin, timeout) {
				window.setTimeout(function () {
					addMarker(pin);
					if (pin.auto_open) {
						setTimeout(function () {
							pin.info.open(map, _marker);
						}, 500);
					}
				}, timeout);
			};

			//Create control
			var CreateControl = function (type, controlDiv, container) {
				// Set CSS for the control border.
				var controlUI = $('<div>', {
					'style': 'background: #fff;border: 2px solid #fff;border-radius:3px;box-shadow:0 2px 6px rgba(0,0,0,.3);cursor:pointer;margin:0 0 22px 0 text-align:center;'
				}).appendTo($(controlDiv));

				var controlText = $('<div>', {
					'style': 'color:rgb(25,25,25);font-family:Roboto,Arial,sans-serif;font-size:16px;line-height:38px;padding: 0 5px 0 5px'
				}).appendTo($(controlUI));

				// Set event and labels
				if (type == 'panoramaSlideIn') {
					$(controlText).text('Street view');
					$(controlUI).on('click', function () {
						$(that).find('.panorama-pseudo-container').css('margin', '0');
					});
				}
				else if (type == 'panoramaSlideOut') {
					$(controlText).text('X');
					$(controlUI).on('click', function () {
						$(that).find('.panorama-pseudo-container').css('margin', panorama_margin);
					});
				}
			};
		});
	};
}(jQuery));
