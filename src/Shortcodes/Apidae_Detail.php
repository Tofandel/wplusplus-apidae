<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 03/07/2018
 * Time: 16:06
 */

namespace Tofandel\Apidae\Shortcodes;

use Tofandel\Apidae\Modules\TemplateFilesHandler;
use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Interfaces\WP_Shortcode;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_Detail
 * @package Tofandel\Apidae\Shortcodes
 *
 * @required-param  string  'template'    The slug of the detail template
 * @param           string  'langs'       Comma separated list of languages that you want to receive in the template (defaults to 'fr')
 */
class Apidae_Detail implements WP_Shortcode {
	use WP_VC_Shortcode;

	static $doing_header = false;

	/**
	 * Ajout des règles de rewrite avec flush_rules si les donnees ne sont pas en base, plus demarrage de session.
	 * @global $wp_rewrite
	 */
	public static function add_detail_rewrite() {
		$redirectUrl = 'index.php?pagename=$matches[1]&apioid=$matches[3]';
		add_rewrite_tag( '%apioid%', '([0-9]+)' );
		$rule = '^([^/]+)/?(.+)/id/([0-9]+)';
		add_rewrite_rule( $rule, $redirectUrl, 'top' );
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
		if ( $post->post_type == 'page' && wpp_has_shortcode( $post, self::getName() ) ) {
			self::$doing_header = true;
			global $shortcode_tags;
			$_tags = $shortcode_tags;
			$tags  = self::getNames();
			foreach ( $_tags as $tag => $callback ) {
				if ( ! in_array( $tag, $tags ) ) // filter unwanted shortcode
				{
					unset( $shortcode_tags[ $tag ] );
				}
			}
			do_shortcode( $post->post_content );
			$shortcode_tags     = $_tags;
			self::$doing_header = false;
		}
	}


	protected function __init() {
		global $WPlusPlusApidae, $pagenow;

		self::add_detail_rewrite();

		$file_names = array();
		$langs      = array();

		if ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( wp_doing_ajax() && $_REQUEST['action'] == 'vc_edit_form' ) ) {
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
					'always_save'      => true,
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
		$json['locales'] = $atts['langs'];

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
			add_filter( 'the_title', function () {
				global $post;

				return $post->post_title;
			}, 10, 0 );
			$pid = $post->ID;
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
			$f = TemplateFilesHandler::TPL_DIR . 'detail-layout.twig';
		} else {
			$f = TemplateFilesHandler::DETAIL_DIR . basename( $atts['template'] ) . '.twig';
		}

		try {
			$tpl = new Template( $f );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : "";
		}

		try {
			global $tofandel_apidae;
			$content = $tpl->render( apply_filters( 'apidae_single_twig_vars', array(
				'referer'    => wp_get_referer(),
				'siteUrl'    => site_url(),
				'o'          => $object,
				//'categories' => Apidae_Categories::getCategoriesCriterias(),
				'useMaps'    => $tofandel_apidae['maps_enable']
			) ) );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : '';
		}

		return do_shortcode( $content );
	}
}