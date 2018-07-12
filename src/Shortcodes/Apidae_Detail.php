<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 03/07/2018
 * Time: 16:06
 */

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Interfaces\WP_Shortcode;
use Tofandel\Core\Traits\WP_VC_Shortcode;


class Apidae_Detail implements WP_Shortcode {
	use WP_VC_Shortcode;

	protected function __init() {
		global $WPlusPlusApidae;

		$file_names = array();
		$langs      = array();
		if ( is_admin() ) {
			$langs      = Apidae_List::getLangs();
			$templates  = glob( $WPlusPlusApidae->file( 'templates/detail/*.twig' ) );
			$file_names = array( esc_html__( 'Please select a template', $WPlusPlusApidae->getTextDomain() ) => '' );

			foreach ( $templates as $template ) {
				$slug                = basename( $template, '.twig' );
				$file_names[ $slug ] = $slug;
			}
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to create the details of any apidae object', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Detail', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => plugins_url( 'admin/logo.svg', $WPlusPlusApidae->getFile() ),
			'params'      => array(
				array(
					'type'             => 'dropdown',
					'heading'          => esc_html__( 'Template', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'template',
					'value'            => $file_names,
					'admin_label'      => true,
					'always_save'      => true,
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
				),
				array(
					'group'       => __( 'Advanced', $WPlusPlusApidae->getTextDomain() ),
					'type'        => 'textarea',
					'heading'     => esc_html__( 'More JSON', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'more_json',
					'description' => __( 'Additional configuration in JSON (ex: {"territoires": [95938, 156922]})<br><strong style="color:red">This will override any already present parameters!</strong>', $WPlusPlusApidae->getTextDomain() )
				),
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Languages', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'langs',
					'value'       => $langs,
					"std"         => 'fr',
					'admin_label' => true
				),
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
		$oid = intval( get_query_var( 'apioid', '' ) );

		$json            = json_decode( $atts['more_json'] ) ?: array();
		$json['locales'] = $atts['langs'];

		if ( empty( $oid ) || ( $object = ApidaeRequest::getSingleObject( $oid, $json ) ) === false ) {
			global $wp_query;
			$wp_query->set_404();

			return "";
		}

		$f = 'detail/' . basename( $atts['template'] ) . '.twig';

		try {
			$tpl = new Template( $f );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : "";
		}

		try {
			$content = $tpl->render( array(
				'referer' => wp_get_referer(),
				'siteUrl' => site_url(),
				'object'  => $object
			) );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : '';
		}

		return do_shortcode( $content );
	}
}