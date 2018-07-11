<?php

namespace Tofandel;

use Tofandel\Apidae\Shortcodes\Apidae_List;
use Tofandel\Apidae\Shortcodes\Apidae_Map;
use Tofandel\Core\Objects\ReduxConfig;
use Tofandel\Core\Objects\WP_Plugin;

require_once __DIR__ . '/admin/tgmpa-config.php';

require_once __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( 'Tofandel\WPlusPlusCore' ) ) {
	return;
}

define( 'WP84APIDAE_VERSION', '1.0b' );
define( 'WP84APIDAE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP84APIDAE_PLUGIN_INC', plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR );
define( 'WP84APIDAE_PLUGIN_JS', plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR );
define( 'WP84APIDAE_PLUGIN_CSS', plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR );

/**
 * Plugin Name: W++ Apidae
 * Plugin URI: https://github.com/Tofandel/wplusplus-apidae/
 * Description: W++ apidae allows you to use apidae with worpress simply by creating Twig templates
 * Version: 1.0
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wplusplusapidae
 * Domain Path: /languages/
 * Download Url: https://github.com/Tofandel/wplusplus-apidae/
 * WC tested up to: 4.8
 */
class WPlusPlusApidae extends WP_Plugin {

	/**
	 * Add the tables and settings and any plugin variable specifics here
	 *
	 * @return void
	 */
	public function definitions() {

		//add_shortcode( 'apidaelist', array( $this, 'apidaelist_shorttag' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
		//add_filter( 'the_posts', array( $this, 'fakepage_WP84_detect' ), - 10 );
		add_action( 'init', function () {
			Apidae_List::__init__();
			Apidae_Map::__init__();
		}, 1 );
		add_action( 'redux_not_loaded', '' );
	}

	/**
	 * Ajout des règles de rewrite avec flush_rules si les donnees ne sont pas en base, plus demarrage de session.
	 * @global $wp_rewrite
	 */
	public static function add_wp84_rewrite() {
		global $wp_rewrite;
		$sFakePageUrl = 'index.php?pagename=$matches[1]&apidaeid=$matches[4]';
		add_rewrite_tag( '%templatedetailid%', '([^&]+)' );
		add_rewrite_tag( '%oid%', '([^&]+)' );
		add_rewrite_tag( '%typeoi%', '([^&]+)' );
		add_rewrite_tag( '%commune%', '([^&]+)' );
		add_rewrite_tag( '%nom%', '([^&]+)' );
		$rule = '^/apidae/([^/]+)/([^/]+)/([^/]+)/id/([0-9]+)';
		add_rewrite_rule( $rule, $sFakePageUrl, 'top' );
		$rules = get_option( 'rewrite_rules' );
		if ( ! isset( $rules[ $rule ] ) ) {
			$wp_rewrite->flush_rules();
		}
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * ajout de variable(s) d'url query supplementaire(s) pour les pages de liste
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public static function add_query_vars_filter( $vars ) {
		$vars[] = "apicritere";
		$vars[] = "apisearch";
		$vars[] = "datedebut";
		$vars[] = "datefin";

		return $vars;
	}

	/**
	 * Add actions and filters here
	 */
	public function actionsAndFilters() {
		add_action( 'admin_head', [ $this, 'fix_logo' ] );
		add_action( 'redux/options/tofandel_apidae/saved', [ $this, 'update_templates' ], 10, 2 );
		add_action( 'redux/options/tofandel_apidae/import', [ $this, 'update_templates' ], 10, 2 );
		add_action( 'redux/options/tofandel_apidae/reset', [ $this, 'delete_templates' ], 10, 0 );
	}

	public function delete_templates() {
		$this->deleteFolder( '/templates/list' );
		$this->deleteFolder( '/templates/detail' );
	}

	public function update_templates( $options, $changed_values = array() ) {
		if ( empty( $changed_values ) ) {
			return;
		}
		$list_titles   = array();
		$detail_titles = array();

		if ( ! empty( $changed_values['list-template'] ) ) {
			$this->deleteFolder( '/templates/list' );
			foreach ( $options['list-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['list-template']['list-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $list_titles ) ) {
					$i ++;
				}
				$title         = $title . $i;
				$list_titles[] = $title;
				$this->mkdir( '/templates/list' );
				$this->put_contents( '/templates/list/' . $title . '.twig', $options['list-template']['list-code'][ $k ] );
			}
		}
		if ( ! empty( $changed_values['detail-template'] ) ) {
			$this->deleteFolder( '/templates/detail' );
			foreach ( $options['detail-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['detail-template']['detail-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $detail_titles ) ) {
					$i ++;
				}
				$title           = $title . $i;
				$detail_titles[] = $title;
				$this->mkdir( '/templates/detail' );
				$this->put_contents( '/templates/detail/' . $title . '.twig', $options['detail-template']['detail-code'][ $k ] );
			}
		}
	}

	/**
	 * @throws \ReflectionException
	 */
	public static function uninstall() {
		parent::uninstall();
		delete_option( 'tofandel_apidae' );

		global $wpdb;
		$table_name = $wpdb->prefix . "wp84apidaeplugin";
		$sql        = "DROP TABLE $table_name;";
		$wpdb->query( $sql );

		//soft flush le plugin ne modifie pas le .htaccess
		flush_rewrite_rules( true );
	}

	/**
	 * Called function after a plugin update
	 * Can be used if options needs to be added or if previous database entries need to be modified
	 */
	protected function upgrade( $last_version ) {
	}

	public function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . "wp84apidaeplugin";
		$sql             = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          confvalue text NOT NULL,
          descript text NOT NULL,
          typeconf varchar(10) NOT NULL,
          PRIMARY KEY  (id),
          KEY typeconf_idx (typeconf(10))
        ) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	const FAKEPAGE_URL = 'apiref';


	public function fix_logo() {
		echo '<style>.vc_element-icon.dashicons{font-size: 2.5em;background-image: none}.toplevel_page_wplusplus-apidae #redux-header{display: none}.toplevel_page_wplusplus-apidae .form-table>tbody>tr>th{width: 190px}#adminmenu .wp-menu-image img{box-sizing:border-box;max-width: 100%}#adminmenu .toplevel_page_wplusplus-apidae .wp-menu-image img {padding: 2px;max-height: 100%}</style>';
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	public function reduxOptions() {
		$r = new ReduxConfig( "tofandel_apidae", array(
			'show_custom_fonts'   => false,
			'show_options_object' => false,
			'display_name'        => 'Apidae',
			'page_slug'           => 'wplusplus-apidae',
			'page_title'          => 'Apidae Options',
			'menu_type'           => 'menu',
			'menu_title'          => 'Apidae',
			'menu_icon'           => plugins_url( 'admin/logo.svg', $this->file ),
			'allow_sub_menu'      => true,
			'page_priority'       => '39',
			'customizer'          => true,
			'hints'               => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => '#071f49',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'light',
					'shadow'  => '1',
					'rounded' => '1',
					'style'   => 'bootstrap',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseleave unfocus',
					),
				),
			),
			'compiler'            => true,
			'page_permissions'    => 'manage_options',
			'save_defaults'       => true,
			'show_import_export'  => true,
			'open_expanded'       => false,
		) );

		//Todo Doc
		$r->setHelpTab( array(
			array(
				//'id'      => 'redux-help-tab-1',
				//'title'   => __( 'Theme Information 1', 'admin_folder' ),
				//'content' => __( '<p>This is the tab content, HTML is allowed.</p>', 'admin_folder' )
			),
		) );

		$r->setSection( array(
			'title'  => __( 'Settings', $this->getTextDomain() ),
			'id'     => 'settings',
			'desc'   => __( 'Apidae API parameters.', $this->getTextDomain() ),
			'icon'   => 'el el-cogs',
			'fields' => array(
				array(
					'title'    => __( 'Project ID', $this->getTextDomain() ),
					'id'       => 'project_id',
					'type'     => 'text',
					'validate' => 'numeric'
				),
				array(
					'title' => __( 'Api Key', $this->getTextDomain() ),
					'id'    => 'api_key',
					'type'  => 'text',
				),
				array(
					'title'    => __( 'Cache duration', $this->getTextDomain() ),
					'id'       => 'cache_duration',
					'type'     => 'text',
					'desc'     => __( 'In minutes, 0 = no cache', $this->getTextDomain() ),
					'validate' => 'numeric',
					'default'  => 1440
				),
				array(
					'title'   => __( 'More request parameters', $this->getTextDomain() ),
					'desc'    => __( 'Checkout <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/format-des-recherches" rel="noopener" target="_blank">the apidae documentation</a> for more information', $this->getTextDomain() ),
					'id'      => 'more_json',
					'type'    => 'ace_editor',
					'mode'    => 'json',
					'default' => '{}'
				)
			)
		) );
		$r->setSection( array(
			'title'  => __( 'Object List Templates', $this->getTextDomain() ),
			'icon'   => 'el el-file-edit',
			'fields' => array(
				array(
					'title'        => __( ' Object List Templates', $this->getTextDomain() ),
					'id'           => 'list-template',
					'type'         => 'repeater',
					'group_values' => true,
					'item_name'    => __( 'template', $this->getTextDomain() ),
					'icon'         => 'el el-file-edit',
					'bind-title'   => 'list-name',
					'fields'       => array(
						array(
							'title'    => __( 'Template Name', $this->getTextDomain() ),
							'id'       => 'list-name',
							'type'     => 'text',
							'validate' => 'unique_slug'
						),
						array(
							'title'   => __( 'Template Code', $this->getTextDomain() ),
							'id'      => 'list-code',
							'type'    => 'ace_editor',
							'mode'    => 'twig',
							'options' => array( 'minLines' => 20, 'maxLines' => 400 )
						),
					),
				)
			)
		) );
		$r->setSection( array(
			'title'  => __( 'Single Object Templates', $this->getTextDomain() ),
			'icon'   => 'el el-file-edit',
			'fields' => array(
				array(
					'title'        => __( 'Single Object Templates', $this->getTextDomain() ),
					'id'           => 'detail-template',
					'type'         => 'repeater',
					'group_values' => true,
					'item_name'    => __( 'template', $this->getTextDomain() ),
					'icon'         => 'el el-file-edit',
					'bind-title'   => 'detail-name',
					'fields'       => array(
						array(
							'title' => __( 'Template Name', $this->getTextDomain() ),
							'id'    => 'detail-name',
							'type'  => 'text',
						),
						array(
							'title'   => __( 'Template Code', $this->getTextDomain() ),
							'id'      => 'detail-code',
							'type'    => 'ace_editor',
							'mode'    => 'twig',
							'options' => array( 'minLines' => 20, 'maxLines' => 400 )
						),
					),
				)
			)
		) );

		$r->setSection( array(
			'title'  => __( 'Google Maps', $this->getTextDomain() ),
			'id'     => 'gmaps',
			'desc'   => __( 'Google Maps API parameters.', $this->getTextDomain() ),
			'icon'   => 'el el-map-marker',
			'fields' => array(
				array(
					'title'   => __( 'Enable google maps', $this->getTextDomain() ),
					'id'      => 'maps_enable',
					'type'    => 'switch',
					'default' => false
				),
				array(
					'title'    => __( 'Api Key', $this->getTextDomain() ),
					'id'       => 'maps_api_key',
					'type'     => 'text',
					'required' => array( 'maps_enable', 'equals', true )
				)
			)
		) );


		//$ext_path = __DIR__ . '/redux-extensions/';
		//Redux::setExtensions($opt_name, $ext_path);
	}
}

global $WPlusPlusApidae;

try {
	$WPlusPlusApidae = new WplusPlusApidae();
} catch ( \Exception $e ) {
	echo $e->getMessage();
}