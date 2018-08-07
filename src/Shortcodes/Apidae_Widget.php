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
 * Class Apidae_Widget
 * @package Tofandel\Apidae\Shortcodes
 */
class Apidae_Widget extends \WP_Widget implements WP_Shortcode {
	use WP_VC_Shortcode;

	public function __construct( $id_base, $name, array $widget_options = array(), array $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	protected function __init() {
		global $WPlusPlusApidae;

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => sprintf( esc_html__( 'Shortcode to display an %sApidae Widget%s', $WPlusPlusApidae->getTextDomain() ),
				'<a href="https://base.apidae-tourisme.com/diffuser/widget/" target="_blank" rel="noopener">', '</a>' ),
			'name'        => esc_html__( 'Apidae Widget', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-welcome-widgets-menus',
			'params'      => array(
				array(
					'type'        => 'number',
					'heading'     => esc_html__( 'Widget ID', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'widget_id',
					'std'         => '',
					'admin_label' => true,
				),
				array(
					'type'        => 'text',
					'heading'     => esc_html__( 'Width', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'width',
					'std'         => '100%',
					'admin_label' => true,
				),
				array(
					'type'        => 'text',
					'heading'     => esc_html__( 'Height', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'height',
					'std'         => '700px',
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
		global $WPlusPlusApidae;

		$WPlusPlusApidae->addScript( '//widget.apidae-tourisme.com/sitraWidgetLoader.js', array(), false, 'true' );

		$id     = intval( $atts['widget_id'] );
		$width  = esc_attr( $atts['width'] );
		$height = esc_attr( $atts['height'] );
		$html   = <<<HTML
<div class="sitra-widget" id="sitra-widget-{$id}" style="width: {$width}; height: {$height};"></div>
HTML;

		return $html;
	}
}