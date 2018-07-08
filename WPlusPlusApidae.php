<?php

namespace Tofandel;

use Tofandel\Apidae\Shortcodes\Apidae_List;
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
	 * @throws \ReflectionException
	 */
	public function definitions() {
		//add_shortcode( 'apidaelist', array( $this, 'apidaelist_shorttag' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
		//add_filter( 'the_posts', array( $this, 'fakepage_WP84_detect' ), - 10 );
		Apidae_List::__init__();
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
	}

	/**
	 * @throws \ReflectionException
	 */
	public static function uninstall() {
		parent::uninstall();

		global $wpdb;
		delete_option( 'wp84apidae_params' );
		delete_option( 'wp84apidae_dureecache' );
		$table_name = $wpdb->prefix . "wp84apidaeplugin";
		$sql        = "DROP TABLE $table_name;";
		$wpdb->query( $sql );
		wp_clear_scheduled_hook( 'wp84apidae_cacheclear' );

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
		if ( ! wp_next_scheduled( 'wp84apidae_cacheclear' ) ) {
			wp_schedule_event( time(), 'hourly', 'wp84apidae_cacheclear' );
		}
		if ( version_compare( $GLOBALS['wp_version'], '4.7', '>=' ) && version_compare( phpversion(), '5.6.0', '>=' ) ) {
			set_transient( 'wp84apidae_msg_status', 'install_success', 5 );
		} else {
			set_transient( 'wp84apidae_msg_status', 'version_doubt', 5 );
		}
	}

	const FAKEPAGE_URL = 'apiref';


	public function fix_logo() {
		echo '<style>.toplevel_page_wplusplus-apidae #redux-header{display: none}#adminmenu .wp-menu-image img{box-sizing:border-box;max-width: 100%}#adminmenu .toplevel_page_wplusplus-apidae .wp-menu-image img {padding: 2px;max-height: 100%}</style>';
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
			//'default_mark'       => ' (default)',
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

		$r->setHelpTab( array(
			array(
				'id'      => 'redux-help-tab-1',
				'title'   => __( 'Theme Information 1', 'admin_folder' ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', 'admin_folder' )
			),
			array(
				'id'      => 'redux-help-tab-2',
				'title'   => __( 'Theme Information 2', 'admin_folder' ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', 'admin_folder' )
			)
		) );

		$r->setSection( array(
			'title'  => __( 'Settings', $this->text_domain ),
			'id'     => 'settings',
			'desc'   => __( 'Apidae API parameters.', $this->text_domain ),
			'icon'   => 'el el-cogs',
			'fields' => array(
				array(
					'title'    => __( 'Project ID', $this->text_domain ),
					'id'       => 'apidae_project_id',
					'type'     => 'text',
					'validate' => 'numeric'
				),
				array(
					'title' => __( 'Api Key', $this->text_domain ),
					'id'    => 'apidae_api_key',
					'type'  => 'text',
				),
				array(
					'title'    => __( 'Cache duration', $this->text_domain ),
					'id'       => 'apidae_cache_duration',
					'type'     => 'text',
					'desc'     => __( 'In minutes, 0 = no cache', $this->text_domain ),
					'validate' => 'numeric',
					'default'  => 1440
				)
			)
		) );
		$r->setSection( array(
			'title'        => __( 'Object List Templates', $this->text_domain ),
			'icon'         => 'el el-file-edit',
			'fields' => array(
				array(
					'title'        => __( ' Object List Templates', $this->text_domain ),
					'id'           => 'list-template',
					'type'         => 'repeater',
					'group_values' => true,
					'item_name'    => __( 'template', $this->text_domain ),
					'icon'         => 'el el-file-edit',
					'fields'       => array(
						array(
							'title' => __( 'Template Name', $this->text_domain ),
							'id'    => 'list-name',
							'type'  => 'text',
						),
						array(
							'title'    => __( 'Template Code', $this->text_domain ),
							'id'       => 'list-code',
							'type'     => 'ace_editor',
							'mode'     => 'twig',
							'compiler' => true,
						),
					),
				)
			)
		) );
		$r->setSection( array(
			'title'        => __( 'Single Object Templates', $this->text_domain ),
			'icon'         => 'el el-file-edit',
			'fields' => array(
				array(
					'title'        => __( 'Single Object Templates', $this->text_domain ),
					'id'           => 'detail-template',
					'type'         => 'repeater',
					'group_values' => true,
					'item_name'    => __( 'template', $this->text_domain ),
					'icon'         => 'el el-file-edit',
					'fields'       => array(
						array(
							'title' => __( 'Template Name', $this->text_domain ),
							'id'    => 'detail-name',
							'type'  => 'text',
						),
						array(
							'title'    => __( 'Template Code', $this->text_domain ),
							'id'       => 'detail-code',
							'type'     => 'ace_editor',
							'mode'     => 'twig',
							'compiler' => true,
						),
					),
				)
			)
		) );

		$r->setSection( array(
			'title'  => __( 'Google Maps', $this->text_domain ),
			'id'     => 'gmaps',
			'desc'   => __( 'Google Maps API parameters.', $this->text_domain ),
			'icon'   => 'el el-map-marker',
			'fields' => array(
				array(
					'title' => __( 'Api Key', $this->text_domain ),
					'id'    => 'maps_api_key',
					'type'  => 'text'
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