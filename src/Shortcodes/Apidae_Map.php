<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_I;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_Map
 * @package Tofandel\Apidae\Shortcodes
 *
 * @param   string  'width'                 The width of the map element (defaults to '100%')
 * @param   string  'height'                The height of the map element (defaults to '300px')
 * @param   int     'zoom'                  The zoom level between 1 and 21, leave blank for auto
 * @param   string  'type'                  The type of the map (available: 'roadmap','satellite','hybrid','terrain') (defaults to 'roadmap')
 * @param   string  'marker_animation'      The marker animation on map load (available: 'none','bounce','drop') (defaults to 'drop')
 * @param   int     'animation_duration'    The time it will take for all the animations to be completed (defaults to '2000' => 2 seconds)
 * @param   bool    'disable_ui'            If you want to disable the controls for the map (defaults to 'false')
 * @param   bool    'disable_scrollwheel'   If you want to disable zooming with the scrollwhell (defaults to 'false')
 * @param   bool    'draggable'             If the user should be able to move the map (defaults to 'true')
 * @param   bool    'use_clusters'          If you want close markers to be grouped on the map (defaults to 'false')
 * @param   string  'preset'                The color preset you want to use (available:
 * 'apple-maps-esque','avocado-world','becomeadinosaur','black-white','blue-essence','blue-water','cool-grey','flat-map','greyscale','light-dream','light-monochrome',
 * 'mapbox','midnight-commander','neutral-blue','pale-down','paper','retro','shades-of-grey','subtle-grayscale','ultra-light-with-labels','unsaturated-browns')
 */
class Apidae_Map implements WP_VC_Shortcode_I {
	use WP_VC_Shortcode;

	protected static $pro = [
		'use_clusters',
		'use_spiderfier',
		'color_scheme',
		'hue',
		'preset',
		'json'
	];

	protected static $atts = [
		'width'               => '100%',
		'height'              => '300px',
		'zoom'                => '',
		'type'                => 'roadmap',
		'marker_animation'    => 'drop',
		'animation_duration'  => '2000',
		'disable_ui'          => '',
		'disable_scrollwheel' => '',
		'draggable'           => 'true',
		'use_clusters'        => '',
		'use_spiderfier'      => '',
		'color_scheme'        => '',
		'hue'                 => '',
		'preset'              => '',
		'json'                => '',
	];

	public static function initVCParams() {
		global $WPlusPlusApidae, $tofandel_apidae;

		$params = empty( $tofandel_apidae['maps_api_key'] ) ? array(
			array(
				'heading'    => esc_html__( 'Google Maps API Key is missing', $WPlusPlusApidae->getTextDomain() ),
				'message'    => sprintf( esc_html__( 'Click %shere%s to set an API key', $WPlusPlusApidae->getTextDomain() ),
					'<a href="' . esc_url( add_query_arg( array(
						'page' => 'wplusplus-apidae',
						'tab'  => 5
					), admin_url( 'admin.php' ) ) ) . '" target="_blank">', '</a>' ),
				'type'       => 'warning',
				'param_name' => false
			)
		) : array();

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => __( 'Shortcode to create an apidae map', $WPlusPlusApidae->getTextDomain() ),
			'name'        => __( 'Apidae Map', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-location',
			//plugins_url( 'admin/logo.svg', $WPlusPlusApidae->getFile() ),
			'params'      => array_merge( $params, array(
				array(
					'heading'          => esc_html__( 'Width', $WPlusPlusApidae->getTextDomain() ),
					'description'      => 'Eg: 300px or 100%',
					'param_name'       => 'width',
					'type'             => 'textfield',
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_textfield vc_wrapper-param-type-textfield vc_shortcode-param vc_column-with-padding',
					'admin_label'      => true
				),
				array(
					'heading'          => esc_html__( 'Height', $WPlusPlusApidae->getTextDomain() ),
					'description'      => 'Eg: 300px',
					'param_name'       => 'height',
					'type'             => 'textfield',
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_textfield vc_wrapper-param-type-textfield vc_shortcode-param',
					'admin_label'      => true
				),
				array(
					'heading'          => esc_html__( 'Zoom', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'zoom',
					'type'             => 'number',
					'extra'            => array(
						'min' => 1,
						'max' => 21
					),
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_number vc_wrapper-param-type-number vc_shortcode-param',
					'admin_label'      => true
				),
				array(
					'heading'          => esc_html__( 'Map type', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'type',
					'type'             => 'dropdown',
					'value'            => array(
						esc_html__( 'Roadmap', $WPlusPlusApidae->getTextDomain() )   => 'roadmap',
						esc_html__( 'Satellite', $WPlusPlusApidae->getTextDomain() ) => 'satellite',
						esc_html__( 'Hybrid', $WPlusPlusApidae->getTextDomain() )    => 'hybrid',
						esc_html__( 'Terrain', $WPlusPlusApidae->getTextDomain() )   => 'terrain',
						//esc_html__( 'Panorama', $WPlusPlusApidae->getTextDomain() )  => 'panorama'
					),
					'std'              => 'roadmap',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param',
					'admin_label'      => true
				),
				array(
					'heading'          => esc_html__( 'Marker Animation', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'marker_animation',
					'type'             => 'dropdown',
					'value'            => array(
						esc_html__( 'None', $WPlusPlusApidae->getTextDomain() )   => 'none',
						esc_html__( 'Drop', $WPlusPlusApidae->getTextDomain() )   => 'drop',
						esc_html__( 'Bounce', $WPlusPlusApidae->getTextDomain() ) => 'bounce'
					),
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param',
					'admin_label'      => true,
					'save_always'      => true,
				),
				array(
					'heading'          => esc_html__( 'Marker Animation', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'animation_duration',
					'type'             => 'number',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_number vc_wrapper-param-type-number vc_shortcode-param',
					'save_always'      => true,
				),
				array(
					'heading'          => esc_html__( 'Disable UI', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'disable_ui',
					'type'             => 'checkbox',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param'
				),
				array(
					'heading'          => esc_html__( 'Disable Scroll Wheel', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'disable_scrollwheel',
					'type'             => 'checkbox',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param'
				),
				array(
					'heading'          => esc_html__( 'Draggable', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'draggable',
					'type'             => 'checkbox',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param',
					'always_save'      => true
				),
				array(
					'heading'          => esc_html__( 'Use Clusters', $WPlusPlusApidae->getTextDomain() ),
					'description'      => '<a href="https://developers.google.com/maps/documentation/javascript/marker-clustering">' . __( 'Example', $WPlusPlusApidae->getTextDomain() ) . '</a>',
					'param_name'       => 'use_clusters',
					'type'             => 'checkbox',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param',
				),
				array(
					'heading'          => esc_html__( 'Use Spiderfier', $WPlusPlusApidae->getTextDomain() ),
					'description'      => '<a href="https://github.com/jawj/OverlappingMarkerSpiderfier">' . __( 'Example', $WPlusPlusApidae->getTextDomain() ) . '</a>',
					'param_name'       => 'use_spiderfier',
					'type'             => 'checkbox',
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param',
				),
				array(
					'heading'          => esc_html__( 'Scheme', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'color_scheme',
					'type'             => 'dropdown',
					'value'            => array(
						esc_html__( 'Default', $WPlusPlusApidae->getTextDomain() ) => '',
						esc_html__( 'Preset', $WPlusPlusApidae->getTextDomain() )  => 'preset',
						esc_html__( 'JSON', $WPlusPlusApidae->getTextDomain() )    => 'json',
						esc_html__( 'Custom', $WPlusPlusApidae->getTextDomain() )  => 'custom-color'
					),
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
					'group'            => esc_html__( 'Color', $WPlusPlusApidae->getTextDomain() )
				),
				array(
					'heading'          => esc_html__( 'Color', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'hue',
					'type'             => 'colorpicker',
					'dependency'       => array(
						'element' => 'color_scheme',
						'value'   => array( 'custom-color' )
					),
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_colorpicker vc_wrapper-param-type-colorpicker vc_shortcode-param',
					'group'            => esc_html__( 'Color', $WPlusPlusApidae->getTextDomain() )
				),
				array(
					'heading'          => esc_html__( 'Preset', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'preset',
					'type'             => 'dropdown',
					'save_always'      => true,
					'value'            => array(
						'Apple Maps Esque'        => 'apple-maps-esque',
						'Avocado World'           => 'avocado-world',
						'Becomeadinosaur'         => 'becomeadinosaur',
						'Black & White'           => 'black-white',
						'Blue Essence'            => 'blue-essence',
						'Blue Water'              => 'blue-water',
						'Cool Grey'               => 'cool-grey',
						'Flat Map'                => 'flat-map',
						'Greyscale'               => 'greyscale',
						'Light Dream'             => 'light-dream',
						'Light Monochrome'        => 'light-monochrome',
						'MapBox'                  => 'mapbox',
						'Midnight Commander'      => 'midnight-commander',
						'Neutral Blue'            => 'neutral-blue',
						'Pale Down'               => 'pale-down',
						'Paper'                   => 'paper',
						'Retro'                   => 'retro',
						'Shades of Grey'          => 'shades-of-grey',
						'Subtle Grayscale'        => 'subtle-grayscale',
						'Ultra Light with Labels' => 'ultra-light-with-labels',
						'Unsaturated Browns'      => 'unsaturated-browns'
					),
					'dependency'       => array(
						'element' => 'color_scheme',
						'value'   => 'preset'
					),
					'edit_field_class' => 'vc_col-xs-4 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param',
					'group'            => esc_html__( 'Color', $WPlusPlusApidae->getTextDomain() )
				),
				array(
					'heading'     => esc_html__( 'JSON', $WPlusPlusApidae->getTextDomain() ),
					'description' => sprintf( esc_html__( 'Use exported JSON for map style. See map presets %shere%s', $WPlusPlusApidae->getTextDomain() ), '<a href="https://snazzymaps.com/" target="_blank">', '</a>' ),
					'param_name'  => 'json',
					'type'        => 'textarea_raw_html',
					'dependency'  => array(
						'element' => 'color_scheme',
						'value'   => 'json'
					),
					'group'       => esc_html__( 'Color', $WPlusPlusApidae->getTextDomain() )
				)
			) )
		);
		if ( ! $WPlusPlusApidae->isLicensed() ) {
			foreach ( self::$vc_params['params'] as $key => $param ) {
				if ( isset( $param['param_name'] ) && in_array( $param['param_name'], self::$pro ) ) {
					self::$vc_params['params'][ $key ]['type']    = 'pro';
					self::$vc_params['params'][ $key ]['buy_url'] = $WPlusPlusApidae->getBuyUrl();
				}
			}
		}
	}

	/**
	 * Fonction outil pour vérifier une date (format + date réelle)
	 *
	 * @param string $date valide si en format "YYYY-MM-DD"
	 *
	 * @return bool
	 */
	public static function checkDateFormat( $date ) {
		// match the format of the date
		if ( preg_match( "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts ) ) {
			// check whether the date is valid or not
			if ( checkdate( $parts[2], $parts[3], $parts[1] ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {
		$presets = array(
			'apple-maps-esque'        => '[{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f7f1df"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#d0e3b4"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","elementType":"geometry","stylers":[{"color":"#fbd3da"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#bde6ab"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffe15f"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#efd151"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"black"}]},{"featureType":"transit.station.airport","elementType":"geometry.fill","stylers":[{"color":"#cfb2db"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a2daf2"}]}]',
			'avocado-world'           => '[{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#aee2e0"}]},{"featureType":"landscape","elementType":"geometry.fill","stylers":[{"color":"#abce83"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#769E72"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#7B8758"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"color":"#EBF4A4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#8dab68"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#5B5B3F"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#ABCE83"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#A4C67D"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#9BBF72"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#EBF4A4"}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"color":"#87ae79"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#7f2200"},{"visibility":"off"}]},{"featureType":"administrative","elementType":"labels.text.stroke","stylers":[{"color":"#ffffff"},{"visibility":"on"},{"weight":4.1}]},{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#495421"}]},{"featureType":"administrative.neighborhood","elementType":"labels","stylers":[{"visibility":"off"}]}]',
			'becomeadinosaur'         => '[{"elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"color":"#f5f5f2"},{"visibility":"on"}]},{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"featureType":"poi.attraction","stylers":[{"visibility":"off"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"visibility":"on"}]},{"featureType":"poi.business","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","stylers":[{"visibility":"off"}]},{"featureType":"poi.place_of_worship","stylers":[{"visibility":"off"}]},{"featureType":"poi.school","stylers":[{"visibility":"off"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#ffffff"},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"visibility":"simplified"},{"color":"#ffffff"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"color":"#ffffff"},{"visibility":"off"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","stylers":[{"color":"#ffffff"}]},{"featureType":"poi.park","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#71c8d4"}]},{"featureType":"landscape","stylers":[{"color":"#e5e8e7"}]},{"featureType":"poi.park","stylers":[{"color":"#8ba129"}]},{"featureType":"road","stylers":[{"color":"#ffffff"}]},{"featureType":"poi.sports_complex","elementType":"geometry","stylers":[{"color":"#c7c7c7"},{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#a0d3d3"}]},{"featureType":"poi.park","stylers":[{"color":"#91b65d"}]},{"featureType":"poi.park","stylers":[{"gamma":1.51}]},{"featureType":"road.local","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"poi.government","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"landscape","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","stylers":[{"visibility":"simplified"}]},{"featureType":"road"},{"featureType":"road"},{},{"featureType":"road.highway"}]',
			'black-white'             => '[{"featureType":"road","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"weight":1}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"weight":0.8}]},{"featureType":"landscape","stylers":[{"color":"#ffffff"}]},{"featureType":"water","stylers":[{"visibility":"off"}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"elementType":"labels","stylers":[{"visibility":"off"}]},{"elementType":"labels.text","stylers":[{"visibility":"on"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#ffffff"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#000000"}]},{"elementType":"labels.icon","stylers":[{"visibility":"on"}]}]',
			'blue-essence'            => '[{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#e0efef"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"hue":"#1900ff"},{"color":"#c0e8e8"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":700}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7dcdcd"}]}]',
			'blue-water'              => '[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}]',
			'cool-grey'               => '[{"featureType":"landscape","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"stylers":[{"hue":"#00aaff"},{"saturation":-100},{"gamma":2.15},{"lightness":12}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"lightness":24}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":57}]}]',
			'flat-map'                => '[{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"visibility":"on"},{"color":"#f3f4f4"}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"weight":0.9},{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#83cead"}]},{"featureType":"road","elementType":"all","stylers":[{"visibility":"on"},{"color":"#ffffff"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"on"},{"color":"#fee379"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"on"},{"color":"#fee379"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#7fc8ed"}]}]',
			'greyscale'               => '[{"featureType":"all","elementType":"all","stylers":[{"saturation":-100},{"gamma":0.5}]}]',
			'light-dream'             => '[{"featureType":"landscape","stylers":[{"hue":"#FFBB00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#00FF6A"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1}]}]',
			'light-monochrome'        => '[{"featureType":"administrative.locality","elementType":"all","stylers":[{"hue":"#2c2e33"},{"saturation":7},{"lightness":19},{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":31},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":31},{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":-2},{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"hue":"#e9ebed"},{"saturation":-90},{"lightness":-8},{"visibility":"simplified"}]},{"featureType":"transit","elementType":"all","stylers":[{"hue":"#e9ebed"},{"saturation":10},{"lightness":69},{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#e9ebed"},{"saturation":-78},{"lightness":67},{"visibility":"simplified"}]}]',
			'mapbox'                  => '[{"featureType":"water","stylers":[{"saturation":43},{"lightness":-11},{"hue":"#0088ff"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"},{"saturation":-100},{"lightness":99}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#808080"},{"lightness":54}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#ece2d9"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#ccdca1"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#767676"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#ffffff"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#b8cb93"}]},{"featureType":"poi.park","stylers":[{"visibility":"on"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"on"}]},{"featureType":"poi.medical","stylers":[{"visibility":"on"}]},{"featureType":"poi.business","stylers":[{"visibility":"simplified"}]}]',
			'midnight-commander'      => '[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"lightness":13}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#144b53"},{"lightness":14},{"weight":1.4}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#08304b"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#0c4152"},{"lightness":5}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#0b434f"},{"lightness":25}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"color":"#0b3d51"},{"lightness":16}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"}]},{"featureType":"transit","elementType":"all","stylers":[{"color":"#146474"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#021019"}]}]',
			'neutral-blue'            => '[{"featureType":"water","elementType":"geometry","stylers":[{"color":"#193341"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#2c5a71"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#29768a"},{"lightness":-37}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#3e606f"},{"weight":2},{"gamma":0.84}]},{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"weight":0.6},{"color":"#1a3541"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#2c5a71"}]}]',
			'pale-down'               => '[{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2e5d4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]}]',
			'paper'                   => '[{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"visibility":"simplified"},{"hue":"#0066ff"},{"saturation":74},{"lightness":100}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"off"},{"weight":0.6},{"saturation":-85},{"lightness":61}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"simplified"},{"color":"#5f94ff"},{"lightness":26},{"gamma":5.86}]}]',
			'retro'                   => '[{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"simplified"}]},{"featureType":"water","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"visibility":"off"}]},{"featureType":"road.local","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","stylers":[{"color":"#84afa3"},{"lightness":52}]},{"stylers":[{"saturation":-17},{"gamma":0.36}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#3f518c"}]}]',
			'shades-of-grey'          => '[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}]',
			'subtle-grayscale'        => '[{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}]',
			'ultra-light-with-labels' => '[{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]}]',
			'unsaturated-browns'      => '[{"elementType":"geometry","stylers":[{"hue":"#ff4400"},{"saturation":-68},{"lightness":-4},{"gamma":0.72}]},{"featureType":"road","elementType":"labels.icon"},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"hue":"#0077ff"},{"gamma":3.1}]},{"featureType":"water","stylers":[{"hue":"#00ccff"},{"gamma":0.44},{"saturation":-33}]},{"featureType":"poi.park","stylers":[{"hue":"#44ff00"},{"saturation":-23}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"hue":"#007fff"},{"gamma":0.77},{"saturation":65},{"lightness":99}]},{"featureType":"water","elementType":"labels.text.stroke","stylers":[{"gamma":0.11},{"weight":5.6},{"saturation":99},{"hue":"#0091ff"},{"lightness":-86}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"lightness":-48},{"hue":"#ff5e00"},{"gamma":1.2},{"saturation":-23}]},{"featureType":"transit","elementType":"labels.text.stroke","stylers":[{"saturation":-64},{"hue":"#ff9100"},{"lightness":16},{"gamma":0.47},{"weight":2.7}]}]'
		);

		// Set width and height
		$atts['width']  = ( ! preg_match( '~(px|%)$~', $atts['width'] ) ? $atts['width'] . 'px' : $atts['width'] );
		$atts['height'] = ( ! preg_match( '~(px|%)$~', $atts['height'] ) ? $atts['height'] . 'px' : $atts['height'] );

		$map_style = '';
		// Enqueue maps js
		global $WPlusPlusApidae, $tofandel_apidae;
		$WPlusPlusApidae->addScript( 'maps', array( 'jquery' ), $atts['use_clusters'] ? array( 'clusterImagePath' => $WPlusPlusApidae->fileUrl( 'images/clusters/m' ) ) : false );

		if ( $WPlusPlusApidae->isLicensed() ) {
			if ( $atts['color_scheme'] == 'preset' || ! empty( $atts['preset'] ) ) {
				$map_style = $presets [ $atts['preset'] ];
			} elseif ( $atts['color_scheme'] == 'custom-color' || ! empty( $atts['hue'] ) ) {
				$hue       = $atts['hue'];
				$map_style = '[{"featureType":"water","elementType":"geometry","stylers":[{"color":"' . $hue . '"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"' . self::adjust_brightness( $hue, 51 ) . '"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"' . self::adjust_brightness( $hue, 51 ) . '"},{"lightness":-37}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"' . self::adjust_brightness( $hue, 51 ) . '"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"' . self::adjust_brightness( $hue, 51 ) . '"}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"' . $hue . '"},{"weight":2},{"gamma":0.84}]},{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"weight":0.6},{"color":"' . self::adjust_brightness( $hue, 51 ) . '"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"' . self::adjust_brightness( $hue, 51 ) . '"}]}]';
			} elseif ( $atts['color_scheme'] == 'json' || ! empty( $atts['json'] ) ) {
				$map_style = preg_replace( '~\s+~', '', urldecode( base64_decode( $atts['json'] ) ) );
			}

			if ( $atts['use_clusters'] ) {
				$WPlusPlusApidae->addScript( 'markerclusterer' );
			} elseif ( $atts['use_spiderfier'] ) {
				$WPlusPlusApidae->addScript( 'oms' );
			}
		}

		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?callback=window.initApidaeMaps&key=' . $tofandel_apidae['maps_api_key'], array( 'maps' ) );

		$atts['draggable']           = ( $atts['draggable'] == 'true' ? 'true' : 'false' );
		$atts['disable_ui']          = ( $atts['disable_ui'] == 'true' ? 'true' : 'false' );
		$atts['disable_scrollwheel'] = ( $atts['disable_scrollwheel'] == 'true' ? 'true' : 'false' );
		$map_style                   = urlencode( $map_style );

		$sp_or_cluster = $atts['use_clusters'] ? 'data-use-clusters="' . $atts['use_clusters'] . '"' : 'data-use-spiderfier="' . $atts['use_spiderfier'] . '"';

		$html = <<<HTML
<div class="apidae-google-maps" style="width:{$atts['width']};height:{$atts['height']}" data-type="{$atts['type']}"
data-animation="{$atts['marker_animation']}" data-animation-duration="{$atts['animation_duration']}" data-zoom="{$atts['zoom']}"
data-disable-ui="{$atts['disable_ui']}" data-scrollwheel="{$atts['disable_scrollwheel']}" data-draggable="{$atts['draggable']}"
data-map-style="{$map_style}" {$sp_or_cluster}></div>
HTML;

		return $html;
	}

	/**
	 * Set brightness for color
	 *
	 * @param string $hex
	 * @param int $steps
	 *
	 * @return string
	 */
	public static function adjust_brightness( $hex, $steps ) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max( - 255, min( 255, $steps ) );

		// Normalize into a six character long hex string
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( (array) $color_parts as $color ) {
			$color  = hexdec( $color ); // Convert to decimal
			$color  = max( 0, min( 255, $color + $steps ) ); // Adjust color
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
		}

		return $return;
	}

	protected function __init() {
	}
}