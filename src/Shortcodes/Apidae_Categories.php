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


class Apidae_Categories implements WP_Shortcode {
	use WP_VC_Shortcode;

	protected function __init() {
		global $WPlusPlusApidae, $tofandel_apidae;

		$cats = array();

		//TODO unique cats
		foreach ( $tofandel_apidae['categories']['category-name'] as $i => $name ) {
			$cats[ $name ] = wpp_slugify( $name );
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to display the list of categories', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Categories', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'dashicons dashicons-category',
			'params'      => array(
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Template', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'categories',
					'value'       => $cats,
					'admin_label' => true,
					'always_save' => true
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
		global $tofandel_apidae;

		$atts['categories'] = explode( ',', $atts['categories'] );

		$cats = array();

		//TODO unique cats
		foreach ( $tofandel_apidae['categories']['category-name'] as $i => $name ) {
			$cats[ wpp_slugify( $name ) ] = $name;
		}

		//TODO multiple categories
		$current = get_query_var( 'apicritere', '' );
		$content = '<ul class="categories"><li class="cat-all"><a href="' . get_page_link() . '"></a></li>';
		foreach ( $atts['categories'] as $cat ) {
			if ( ! empty( $cats[ $cat ] ) ) {
				$content .= '<li class="cat-' . $cat . ' ' . ( $cat == $current ? ' current' : '' ) . '"><a href="' . add_query_arg( array( 'apicritere' => $cat ) ) . '"></a></li>';
			}
		}
		$content .= "</ul>";

		return do_shortcode( $content );
	}
}