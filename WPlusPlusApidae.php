<?php

namespace Tofandel;

use Tofandel\Apidae\Modules\TemplateFilesHandler;
use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Shortcodes\Apidae_Categories;
use Tofandel\Apidae\Shortcodes\Apidae_Detail;
use Tofandel\Apidae\Shortcodes\Apidae_List;
use Tofandel\Apidae\Shortcodes\Apidae_Map;
use Tofandel\Core\Interfaces\WP_Plugin as WP_Plugin_Interface;
use Tofandel\Core\Objects\ReduxConfig;
use Tofandel\Core\Objects\WP_Plugin;

if ( is_admin() && ! wp_doing_ajax() ) {
	require_once __DIR__ . '/plugins/tgmpa-config.php';
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( 'Tofandel\WPlusPlusCore' ) ) {
	return;
}

/**
 * Plugin Name: W++ Apidae
 * Plugin URI: https://github.com/Tofandel/wplusplus-apidae/
 * Description: W++ apidae allows you to use apidae with worpress simply by creating Twig templates
 * Version: 1.2.7.1
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wplusplus-apidae
 * Domain Path: /languages/
 */
class WPlusPlusApidae extends WP_Plugin implements WP_Plugin_Interface {
	protected $redux_opt_name = 'tofandel_apidae';
	protected $is_licensed = true;
	protected $download_url = 'https://tukangroup.com/product/premium-plugins/wplusplus-apidae/';
	protected $product_id = 'wplusplus-apidae';
	protected $repo_url = 'https://github.com/Tofandel/wplusplus-apidae/';

	/**
	 * Add actions and filters here
	 */
	public function actionsAndFilters() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
	}

	public function admin_enqueue() {
		$this->addStyle( 'admin' );
	}

	/**
	 * Add the tables and settings and any plugin variable specifics here
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function definitions() {
		$this->setSubModule( new TemplateFilesHandler( $this ) );
		$this->setShortcodes( array(
			Apidae_Detail::class,
			Apidae_List::class,
			Apidae_Map::class,
			Apidae_Categories::class
		) );
	}

	public function uninstall() {
		flush_rewrite_rules( true );
	}

	/**
	 * Called function after a plugin update
	 * Can be used if options needs to be added or if previous database entries need to be modified
	 */
	protected function upgrade( $last_version ) {
		global $tofandel_apidae;
		//We regenerate the templates
		TemplateFilesHandler::saveTemplate( $tofandel_apidae, 'list' );
		TemplateFilesHandler::saveTemplate( $tofandel_apidae, 'detail' );
	}

	public function activated() {
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	public function reduxConfig() {
		$r = new ReduxConfig( $this, array(
			'display_name'     => __( 'Apidae', $this->text_domain ),
			'page_slug'        => $this->slug,
			'page_title'       => __( 'Apidae Options', $this->text_domain ),
			'menu_type'        => 'menu',
			'menu_title'       => __( 'Apidae', $this->text_domain ),
			'menu_icon'        => plugins_url( 'logo.svg', $this->file ),
			'allow_sub_menu'   => true,
			'page_priority'    => '39',
			'compiler'         => false,
			'page_permissions' => 'manage_options'
		) );

		//Todo Doc
		$r->setHelpTab( array(
			array(
				'id'      => 'list-template-help',
				'title'   => __( 'List templates', $this->getTextDomain() ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', $this->getTextDomain() )
			),
			array(
				'id'      => 'detail-template-help',
				'title'   => __( 'Detail templates', $this->getTextDomain() ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', $this->getTextDomain() )
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
					'title'    => __( 'Clear the cache', $this->getTextDomain() ),
					'id'       => 'clear_cache',
					'type'     => 'action_button',
					'function' => [ ApidaeRequest::class, 'clearCache' ],
				)
			)
		) );
		$r->setSection( array(
			'title'  => __( 'Categories', $this->getTextDomain() ),
			'icon'   => 'el el-th-list',
			'fields' => array(
				array(
					'title'        => __( 'Categories', $this->getTextDomain() ),
					'type'         => 'repeater',
					'group_values' => true,
					'id'           => 'categories',
					'item_name'    => __( 'category', $this->getTextDomain() ),
					'bind-title'   => 'category-name',
					'fields'       => array(
						array(
							'title' => __( 'Category Name', $this->getTextDomain() ),
							'type'  => 'text',
							'id'    => 'category-name',
						),
						array(
							'title'   => __( 'Query', $this->getTextDomain() ),
							'type'    => 'ace_editor',
							'id'      => 'category-query',
							'mode'    => 'json',
							'options' => array( 'minLines' => 6, 'maxLines' => 40 ),
							'default' => '{' . "\n" .
							             '    "criteresQuery":"",' . "\n" .
							             '    "searchQuery":"",' . "\n" .
							             '    "territoireIds":[],' . "\n" .
							             '    "communeCodesInsee":[]' . "\n" .
							             '}',
							'desc'    => __( 'Fill the query fields that the selected category will modify during the request to apidae in JSON.<br>
<a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/format-des-recherches#communeCodesInsee" target="_blank" rel="noopener">Read the documentation</a> for the list of available query fields', $this->getTextDomain() ),
						)
					)
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
					//'sortable'     => false,
					'group_values' => true,
					'item_name'    => __( 'template', $this->getTextDomain() ),
					'bind-title'   => 'list-name',
					'fields'       => array(
						array(
							'title' => __( 'Template Name', $this->getTextDomain() ),
							'id'    => 'list-name',
							'type'  => 'text',
						),
						array(
							'title'             => __( 'Template Code', $this->getTextDomain() ),
							'id'                => 'list-code',
							'type'              => 'ace_editor',
							'mode'              => 'twig',
							'options'           => array( 'minLines' => 20, 'maxLines' => 400 ),
							'default'           => $this->get_contents( 'templates/list-layout.twig' ),
							'validate_callback' => [ TemplateFilesHandler::class, 'templateValidation' ]
						),
						array(
							'title'   => __( 'More request parameters', $this->getTextDomain() ),
							'desc'    => __( 'Checkout <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/format-des-recherches" rel="noopener" target="_blank">the apidae documentation</a> for more information', $this->getTextDomain() ),
							'id'      => 'more_json',
							'type'    => 'ace_editor',
							'mode'    => 'json',
							'default' => '{}'
						)
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
					//'sortable'     => false,
					'group_values' => true,
					'item_name'    => __( 'template', $this->getTextDomain() ),
					'bind-title'   => 'detail-name',
					'fields'       => array(
						array(
							'title' => __( 'Template Name', $this->getTextDomain() ),
							'id'    => 'detail-name',
							'type'  => 'text',
						),
						array(
							'title'             => __( 'Template Code', $this->getTextDomain() ),
							'id'                => 'detail-code',
							'type'              => 'ace_editor',
							'mode'              => 'twig',
							'options'           => array( 'minLines' => 20, 'maxLines' => 400 ),
							'default'           => $this->get_contents( 'templates/detail-layout.twig' ),
							'validate_callback' => [ TemplateFilesHandler::class, 'templateValidation' ]
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
	}
}

global $WPlusPlusApidae;

try {
	$WPlusPlusApidae = new WplusPlusApidae();
} catch ( \Exception $e ) {
	echo $e->getMessage();
}