<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Apidae\Shortcodes;

use Tofandel\Core\Interfaces\StaticSubModule;
use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_Interface;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Class Apidae_Search
 * @package Tofandel\Apidae\Shortcodes
 */
class Apidae_Search implements WP_VC_Shortcode_Interface, StaticSubModule {
	use WP_VC_Shortcode;

	protected static $atts = array(
		'url'                    => '',
		'search_input'           => 'true',
		'categories_input'       => 'true',
		'date_inputs'            => 'true',
		'start_placeholder'      => '',
		'end_placeholder'        => '',
		'search_placeholder'     => '',
		'categories_placeholder' => '',
		'submit_title'           => '',
		'submit_text'            => '',
		'langs'                  => ''
	);

	public static function initVCParams() {
		global $WPlusPlusApidae;
		$langs             = Apidae_List::getLangs();
		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to display an Apidae search form', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Search', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-search',
			'params'      => array(
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Destination url', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'url',
					'admin_label' => true,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Search Input', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'search_input',
					'admin_label' => true,
					'std'         => 'true'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Search Input', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'categories_input',
					'admin_label' => true,
					'std'         => 'true'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Date Inputs', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'date_inputs',
					'std'         => 'true',
					'admin_label' => true,
				),
				array(
					'type'       => 'text',
					'heading'    => esc_html__( 'Start Date Placeholder', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'start_placeholder',
				),
				array(
					'type'       => 'text',
					'heading'    => esc_html__( 'End Date Placeholder', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'end_placeholder',
				),
				array(
					'type'       => 'text',
					'heading'    => esc_html__( 'Search Placeholder', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'search_placeholder',
				),
				array(
					'type'       => 'text',
					'heading'    => esc_html__( 'Categories Placeholder', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'categories_placeholder',
				),
				array(
					'type'       => 'text',
					'heading'    => esc_html__( 'Submit Title', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'submit_title',
				),
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Languages', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'langs',
					'value'       => $langs,
					'admin_label' => true
				),
				array(
					'type'        => 'textarea_raw_html',
					'heading'     => esc_html__( 'Submit Text', $WPlusPlusApidae->getTextDomain() ),
					'description' => esc_html__( 'Leave it empty to display a SVG search icon', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'submit_text',
				)
			)
		);
	}

	/**
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {
		static $once = true;
		global $WPlusPlusApidae;

		$url        = esc_attr( $atts['url'] );
		$page_query = array();

		$langs = array_map( 'trim', explode( ',', $atts['langs'] ) );
		if ( empty( $langs ) ) {
			$langs = array( strtolower( explode( '_', get_locale() )[0] ) );
		}


		$search          = get_query_var( 'apisearch', '' );
		$dateDebut       = get_query_var( 'datedebut', '' );
		$dateFin         = get_query_var( 'datefin', '' );
		$queryCategories = Apidae_Categories::getQueryCategories( $langs );

		if ( ! empty( $dateDebut ) && ! Apidae_List::checkDateFormat( $dateDebut ) ) {
			$dateDebut = '';
		}
		if ( ! empty( $dateFin ) && ! Apidae_List::checkDateFormat( $dateFin ) ) {
			$dateFin = '';
		}

		$query = '';
		foreach ( $page_query as $k => $v ) {
			if ( ! empty( $v ) ) {
				$query .= "<input type='hidden' name='$k' value='$v'>";
			}
		}

		$submit_text = ! empty( $atts['submit_text'] ) ? strip_tags( $atts['submit_text'], '<span><i><p><b><strong>' ) : '<svg width="32" height="32" viewBox="0 0 32 32" class="search-svg"><style type="text/css">.se0{fill:none;stroke:#000000;stroke-width:2;stroke-linecap:round;}</style><circle class="se0" cx="13" cy="13" r="7"/><line class="se0" x1="18" x2="24" y1="18" y2="24"/></svg>';

		$search_input     = $atts['search_input'] == 'true';
		$date_inputs      = $atts['date_inputs'] == 'true';
		$categories_input = $atts['categories_input'] == 'true';

		$WPlusPlusApidae->addStyle( 'public' );

		$html = $searchPlaceholder = $submitTitle = $startPlaceholder = $endPlaceholder = $hasSearch = $hasDates = '';
		if ( $date_inputs ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			$WPlusPlusApidae->addStyle( 'datepicker' );
			$startPlaceholder = ! empty( $atts['start_placeholder'] ) ? esc_attr( $atts['start_placeholder'] ) : esc_attr__( 'Start date', $WPlusPlusApidae->getTextDomain() );
			$endPlaceholder   = ! empty( $atts['end_placeholder'] ) ? esc_attr( $atts['end_placeholder'] ) : esc_attr__( 'End date', $WPlusPlusApidae->getTextDomain() );
			$hasDates         = 'has-dates';
		}
		if ( $search_input ) {
			$searchPlaceholder = ! empty( $atts['search_placeholder'] ) ? esc_attr( $atts['search_placeholder'] ) : esc_attr__( 'Search...', $WPlusPlusApidae->getTextDomain() );
			$submitTitle       = ! empty( $atts['submit_title'] ) ? esc_attr( $atts['submit_title'] ) : esc_attr__( 'Search', $WPlusPlusApidae->getTextDomain() );
			$hasSearch         = 'has-search';
		}

		if ( $once ) {
			$once = false;
			$html = <<<HTML
<script>
jQuery(document).ready(function($){
$('select.select2').each(function() { $(this).select2({placeholder: $(this).data('placeholder')})});
$('.apidae-searchform input[type=date]').attr('type', 'text').attr('autocomplete', 'off').datepicker({dateFormat : 'yy-mm-dd'});
$(".apidae-searchform input[name=apisearch]").on("search", function (){ $(this).closest('form').submit();});
});
</script>
HTML;
		}

		$search_input = $search_input ? 'search' : 'hidden';
		$date_inputs  = $date_inputs ? 'date' : 'hidden';

		if ( $categories_input ) {
			$cats = Apidae_Categories::getCategories( $langs );
			if ( empty( $atts['categories_placeholder'] ) ) {
				$atts['categories_placeholder'] = __( "Select categories", $WPlusPlusApidae->getTextDomain() );
			}
			$searchCategories = '<select class="select2 category" multiple="multiple" name="apicategories[]" data-placeholder="' . esc_attr( $atts['categories_placeholder'] ) . '">';
			foreach ( $cats as $slug => $label ) {
				$selected = '';
				if ( in_array( $slug, $queryCategories ) ) {
					$selected = 'selected="selected"';
				}
				$searchCategories .= "<option value='$slug' $selected>$label</option>";
			}
			$searchCategories .= '</select>';
			global $WPlusPlusCore;
			$WPlusPlusCore->addScript( 'select2' );
			$WPlusPlusCore->addStyle( 'select2' );
		} else {
			$searchCategories = ! empty( $queryCategories ) ? '<input type="hidden" name="apicategories" value="' . esc_attr( implode( ',', $queryCategories ) ) . '">' : '';
		}

		$html .= <<<HTML
<form action="{$url}" class="apidae-searchform {$hasDates} {$hasSearch}" method="get">
	<input type="{$date_inputs}" name="datedebut" class="date" value="{$dateDebut}" placeholder="{$startPlaceholder}">
	<input type="{$date_inputs}" name="datefin" class="date" value="{$dateFin}" placeholder="$endPlaceholder">
	{$searchCategories}
	<input type="{$search_input}" name="apisearch" value="{$search}" placeholder="{$searchPlaceholder}" class="searchinput">
	<button type="submit" title="{$submitTitle}" class="search-submit button">{$submit_text}</button>
</form>
HTML;

		return $html;
	}
}