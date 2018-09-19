<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Apidae\Shortcodes;

use Tofandel\Apidae\Modules\TemplateFilesHandler;
use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_Interface;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_Detail
 * @package Tofandel\Apidae\Shortcodes
 *
 * @required-param  string  'template'    The slug of the detail template
 *
 * @param           string  'langs'       Comma separated list of languages that you want to receive in the template (defaults to 'fr')
 */
class Apidae_Detail implements WP_VC_Shortcode_Interface {
	use WP_VC_Shortcode {
		__StaticInit as ParentStaticInit;
	}

	static $doing_header = false;

	public static function __StaticInit() {
		self::ParentStaticInit();
		self::add_detail_rewrite();
	}

	/**
	 * Ajout des règles de rewrite avec flush_rules si les donnees ne sont pas en base
	 * @global $wp_rewrite
	 */
	public static function add_detail_rewrite() {
		$redirectUrl = 'index.php?pagename=$matches[1]&apioid=$matches[3]';
		add_rewrite_tag( '%apioid%', '([0-9]+)' );
		$rule = '^(.+?)/for/(.+?)/id/([0-9]+)';
		add_rewrite_rule( $rule, $redirectUrl, 'top' );
		add_rewrite_rule( '(.+)/for/(.+)/id/([0-9]+)', '$1?apioid=$matches[3]' );
		$rules = get_option( 'rewrite_rules' );
		if ( ! isset( $rules[ $rule ] ) ) {
			flush_rewrite_rules( true );
		}
	}

	/**
	 * This is to set 404 pages or the page title if we find the detail shortcode we execute it before anything is displayed on the page
	 * @throws \ReflectionException
	 */
	public static function setPageTitle() {
		global $post;
		if ( isset( $post ) && $post->post_type == 'page' && wpp_has_shortcode( $post->post_content, self::getName() ) ) {
			self::$doing_header = true;
			global $shortcode_tags;
			$_tags = $shortcode_tags;
			$s_tag = self::getName();
			foreach ( $_tags as $tag => $callback ) {
				if ( ! $tag != $s_tag ) {
					// filter unwanted shortcode
					unset( $shortcode_tags[ $tag ] );
				}
			}
			do_shortcode( $post->post_content );
			$shortcode_tags     = $_tags;
			self::$doing_header = false;
		}
	}


	protected static $atts = array(
		'title_scheme' => '%nom.libelle%',
		'template'     => '',
		'langs'        => 'fr'
	);

	public static function initVCParams() {
		global $WPlusPlusApidae;

		$langs      = Apidae_List::getLangs();
		$templates  = glob( $WPlusPlusApidae->file( TemplateFilesHandler::TPL_DIR . TemplateFilesHandler::DETAIL_DIR . '*.twig' ) );
		$file_names = array( esc_html__( 'Please select a template', $WPlusPlusApidae->getTextDomain() ) => '' );

		foreach ( $templates as $template ) {
			$slug                = basename( $template, '.twig' );
			$file_names[ $slug ] = $slug;
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to create the details of any apidae object', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Detail', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => plugins_url( 'logo.svg', $WPlusPlusApidae->getFile() ),
			'params'      => array(
				array(
					'group'       => __( 'Advanced', $WPlusPlusApidae->getTextDomain() ),
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Title scheme', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'title_scheme',
					'description' => __( 'The scheme of the page title', $WPlusPlusApidae->getTextDomain() ),
					'std'         => '%nom.libelle%'
				),
				array(
					'type'             => 'dropdown',
					'heading'          => esc_html__( 'Template', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'template',
					'value'            => $file_names,
					'admin_label'      => true,
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
				),
				/*array(
					'group'       => __( 'Advanced', $WPlusPlusApidae->getTextDomain() ),
					'type'        => 'textarea',
					'heading'     => esc_html__( 'More JSON', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'more_json',
					'description' => __( 'Additional configuration in JSON (ex: {"territoires": [95938, 156922]})<br><strong style="color:red">This will override any already present parameters!</strong>', $WPlusPlusApidae->getTextDomain() )
				),*/
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

		$json = array();
		//$json            = json_decode( $atts['more_json'] ) ?: array();
		$langs = array_map( 'trim', explode( ',', $atts['langs'] ) );
		if ( empty( $langs ) ) {
			$langs = array( strtolower( explode( '_', get_locale() )[0] ) );
		}
		$json['locales'] = $langs;

		if ( empty( $oid ) || ( $object = ApidaeRequest::getSingleObject( $oid, $json ) ) === false ) {
			if ( static::$doing_header ) {
				global $wp_query;
				$wp_query->set_404();
			}

			return "";
		}
		if ( static::$doing_header ) {
			global $post;
			$post->post_title = Apidae_List::applyScheme( $atts['title_scheme'], $object );
			add_filter( 'document_title_parts', function ( $title ) {
				global $post;
				$title['title'] = $post->post_title;

				return $title;
			}, 10, 1 );
			$pid = $post->ID;
			add_filter( 'the_title', function ( $title, $post_id ) use ( $pid ) {
				if ( $post_id == $pid ) {
					global $post;

					return $post->post_title;
				}

				return $title;
			}, 9, 2 );
			add_filter( 'page_link', function ( $permalink, $post_id ) use ( $pid ) {
				global $wp;
				if ( $pid == $post_id ) {
					return home_url( $wp->request );
				}

				return $permalink;
			}, 10, 2 );

			return "";
		}

		if ( empty( $atts['template'] ) ) {
			$f = 'detail-layout.twig';
		} else {
			$f = TemplateFilesHandler::DETAIL_DIR . basename( $atts['template'] ) . '.twig';
		}

		try {
			$tpl = new Template( $f );
			global $tofandel_apidae;
			$content = $tpl->render( apply_filters( 'apidae_single_twig_vars', array(
				'referer' => wp_get_referer(),
				'siteUrl' => site_url(),
				'o'       => $object,
				'langs'   => $langs,
				//'categories' => Apidae_Categories::getCategoriesCriterias(),
				'useMaps' => $tofandel_apidae['maps_enable']
			) ) );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : '';
		}

		return do_shortcode( $content );
	}
}