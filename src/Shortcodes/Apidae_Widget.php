<?php
/**
 * Copyright (c) 2018.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Tofandel\Apidae\Shortcodes;

use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_Interface;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Class Apidae_Widget
 * @package Tofandel\Apidae\Shortcodes
 * @see https://base.apidae-tourisme.com/diffuser/widget/
 */
class Apidae_Widget extends \WP_Widget implements WP_VC_Shortcode_Interface {
	use WP_VC_Shortcode;

	public function __construct( $id_base, $name, array $widget_options = array(), array $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	protected static $atts = array(
		'widget_id' => '',
		'width'     => '100%',
		'height'    => '700px',
	);

	public static function initVCParams() {
		global $WPlusPlusApidae;

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => __( 'Shortcode to display an Apidae Widget', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Widget', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-welcome-widgets-menus',
			'params'      => array(
				array(
					'type'        => 'number',
					'heading'     => esc_html__( 'Widget ID', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'widget_id',
					'admin_label' => true,
				),
				array(
					'type'        => 'text',
					'heading'     => esc_html__( 'Width', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'width',
					'admin_label' => true,
				),
				array(
					'type'        => 'text',
					'heading'     => esc_html__( 'Height', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'height',
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
<div class="sitra-widget" id="sitra-widget-{$id}" style="width:{$width}; height:{$height}"></div>
HTML;

		return $html;
	}
}