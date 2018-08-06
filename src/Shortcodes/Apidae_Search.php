<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 03/07/2018
 * Time: 16:06
 */

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Core\Interfaces\WP_Shortcode;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_Categories
 * @package Tofandel\Apidae\Shortcodes
 *
 * @required-param  string  'categories'    Comma separated list of the Apidae categories slug you want displayed (you have to create them first in the apidae options)
 *
 * @param           bool    'all_link'      Whether to display the 'All' link or not (defaults to true)
 */
class Apidae_Search implements WP_Shortcode {
	use WP_VC_Shortcode;

	protected function __init() {
		global $WPlusPlusApidae, $pagenow;

		$cats = array();

		if ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( wp_doing_ajax() && $_REQUEST['action'] == 'vc_edit_form' ) ) {
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to display the list of categories', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Categories', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-category',
			'params'      => array(
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Destination url', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'url',
					'std'         => '',
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
					'heading'     => esc_html__( 'Date Inputs', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'date_inputs',
					'std'         => 'true',
					'admin_label' => true,
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

		$url        = urlencode( $atts['url'] );
		$page_query = array();


		$search    = get_query_var( 'apisearch', '' );
		$dateDebut = get_query_var( 'datedebut', '' );
		$dateFin   = get_query_var( 'datefin', '' );

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

		$search_input = $atts['search_input'] == 'true';
		$date_inputs  = $atts['date_inputs'] == 'true';

		$WPlusPlusApidae->addStyle( 'public' );

		$html = $searchPlaceholder = $submitTitle = $startPlaceholder = $endPlaceholder = $hasSearch = $hasDates = '';
		if ( $date_inputs ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			$WPlusPlusApidae->addStyle( 'datepicker' );
			$startPlaceholder = ! empty( $atts['start_placeholder'] ) ? $atts['start_placeholder'] : __( 'Start date', $WPlusPlusApidae->getTextDomain() );
			$endPlaceholder   = ! empty( $atts['end_placeholder'] ) ? $atts['end_placeholder'] : __( 'End date', $WPlusPlusApidae->getTextDomain() );
			$hasDates         = 'has-dates';
		}
		if ( $search_input ) {
			$searchPlaceholder = ! empty( $atts['search_placeholder'] ) ? $atts['search_placeholder'] : __( 'Search...', $WPlusPlusApidae->getTextDomain() );
			$submitTitle       = ! empty( $atts['submit_title'] ) ? $atts['submit_title'] : __( 'Search', $WPlusPlusApidae->getTextDomain() );
			$hasSearch         = 'has-search';
		}

		if ( $once ) {
			$once = false;
			$html = <<<HTML
<script>
jQuery(document).ready(function($){
$('.apidae-searchform input[type=date]').attr('type', 'text').datepicker({dateFormat : 'yy-mm-dd'});
$(".apidae-searchform input[name=apisearch]").on("search", function (){ $(this).closest('form').submit();});
});
</script>
HTML;
		}

		$search_input = $search_input ? 'search' : 'hidden';
		$date_inputs  = $date_inputs ? 'date' : 'hidden';

		$html .= <<<HTML
<form action="{$url}" class="apidae-searchform {$hasDates} {$hasSearch}" method="get">
	<input type="{$date_inputs}" name="datedebut" class="date" value="{$dateDebut}" placeholder="{$startPlaceholder}">
	<input type="{$date_inputs}" name="datefin" class="date" value="{$dateFin}" placeholder="$endPlaceholder">
	<input type="{$search_input}" name="apisearch" value="{$search}" placeholder="{$searchPlaceholder}" class="searchinput">
	<button type="submit" title="{$submitTitle}" class="search-submit button"><svg width="32" height="32" viewBox="0 0 32 32" class="search-svg"><style type="text/css">.se0{fill:none;stroke:#000000;stroke-width:2;stroke-linecap:round;}</style><circle class="se0" cx="13" cy="13" r="7"/><line class="se0" x1="18" x2="24" y1="18" y2="24"/></svg></button>
</form>
HTML;

		return $html;
	}
}