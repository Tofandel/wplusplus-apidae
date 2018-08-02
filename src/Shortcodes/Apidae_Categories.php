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
class Apidae_Categories implements WP_Shortcode {
	use WP_VC_Shortcode;

	protected function __init() {
		global $WPlusPlusApidae, $pagenow;

		$cats = array();

		if ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( wp_doing_ajax() && $_REQUEST['action'] == 'vc_edit_form' ) ) {
			$cats = array_reverse( self::getCategories() );
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to display the list of categories', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Categories', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-category',
			'params'      => array(
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Categories', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'categories',
					'value'       => $cats,
					'admin_label' => true
				),
				array(
					'type'       => 'checkbox',
					'heading'    => esc_html__( 'Show the "All" link', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'all_link',
					'std'        => 'true'
				)
			)
		);
	}

	public static function getCategories() {
		static $cats;

		if ( ! isset( $cats ) ) {
			global $tofandel_apidae;

			$cats = array();

			if ( isset( $tofandel_apidae['categories']['category-name'] ) ) {
				//TODO unique cats
				foreach ( $tofandel_apidae['categories']['category-name'] as $i => $name ) {
					$cats[ wpp_slugify( $name ) ] = $name;
				}
			}
		}

		return $cats;
	}

	public static function getCategoriesCriterias() {
		static $cats;

		if ( ! isset( $cats ) ) {
			foreach ( self::getCategories() as $slug => $category ) {
				$cats[ $slug ] = self::getCriteria( $slug );
				if ( ! empty( $cats[ $slug ]['criteresQuery'] ) ) {
					$crit                           = explode( ' ', $cats[ $slug ]['criteresQuery'] );
					$cats[ $slug ]['criteresQuery'] = array();
					foreach ( $crit as $key => $critere ) {
						$c                                                                 = explode( ':', $critere );
						$cats[ $slug ]['criteresQuery'][ str_replace( '+', '', $c[0] ) ][] = $c[1];
					}
				}

			}
		}

		return $cats;
	}


	public static function getCriteria( $categorie_slug ) {
		$cats = self::getCategories();

		$i = array_search( $categorie_slug, array_keys( $cats ) );

		if ( $i !== false ) {
			global $tofandel_apidae;
			if ( ! empty( $tofandel_apidae['categories']['category-query'][ $i ] ) ) {
				return json_decode( $tofandel_apidae['categories']['category-query'][ $i ], true );
			}
		}

		return false;
	}


	/**
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {
		global $WPlusPlusApidae;

		$atts['categories'] = explode( ',', $atts['categories'] );

		$cats = self::getCategories();

		//TODO multiple categories
		$current = get_query_var( 'apicategories', '' );
		$content = '<ul class="categories">';
		if ( $atts['all_link'] == 'true' ) {
			$content .= '<li class="cat-all' . ( empty( $current ) ? ' current' : '' ) . '"><a href="' . get_page_link() . '">' . __( 'All', $WPlusPlusApidae->getTextDomain() ) . '</a></li>';
		}
		$search = get_query_var( 'apisearch' );
		$args   = ! empty( $search ) ? array( 'apisearch' => $search ) : array();
		foreach ( $atts['categories'] as $cat ) {
			if ( ! empty( $cats[ $cat ] ) ) {
				$args['apicategories'] = $cat;
				$content               .= '<li class="cat-' . $cat . ( $cat == $current ? ' current' : '' ) . '"><a href="' . add_query_arg( $args, get_page_link() ) . '">' . $cats[ $cat ] . '</a></li>';
			}
		}
		$content .= "</ul>";

		return do_shortcode( $content );
	}
}