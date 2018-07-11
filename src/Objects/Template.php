<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 09/07/2018
 * Time: 17:56
 */

namespace Tofandel\Apidae\Objects;


use Tofandel\Apidae\TwigExtensions\ExtensionsLoader;
use Tofandel\Core\Traits\StaticInitializable;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Template {
	use StaticInitializable;

	/**
	 * @var Twig_Environment
	 */
	static $twig;

	public static function __init__() {
		global $WPlusPlusApidae;
		$loader       = new Twig_Loader_Filesystem( $WPlusPlusApidae->folder( 'templates' ) );
		static::$twig = new Twig_Environment( $loader, array(
			'cache' => $WPlusPlusApidae->folder( 'templates/cache' ),
			'debug' => WP_DEBUG,
		) );
		static::$twig->addExtension( new ExtensionsLoader() );
	}

	/**
	 * @var \Twig_TemplateWrapper
	 */
	protected $template;

	/**
	 * Template constructor.
	 *
	 * @param $file
	 *
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function __construct( $file ) {
		$this->template = static::$twig->load( $file );
	}

	public function render( $variables = array() ) {
		return $this->template->render( $variables );
	}


	public static function delete_templates() {
		global $WPlusPlusApidae;
		$WPlusPlusApidae->deleteFolder( '/templates/list' );
		$WPlusPlusApidae->deleteFolder( '/templates/detail' );
	}

	public static function update_templates( $options, $changed_values = array() ) {
		if ( empty( $changed_values ) ) {
			return;
		}
		global $WPlusPlusApidae;

		$list_titles   = array();
		$detail_titles = array();

		if ( ! empty( $changed_values['list-template'] ) ) {
			$WPlusPlusApidae->deleteFolder( '/templates/cache' );
			$WPlusPlusApidae->deleteFolder( '/templates/list' );
			foreach ( $options['list-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['list-template']['list-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $list_titles ) ) {
					$i ++;
				}
				$title         = $title . $i;
				$list_titles[] = $title;
				$WPlusPlusApidae->mkdir( '/templates/list' );
				$WPlusPlusApidae->put_contents( '/templates/list/' . $title . '.twig', $options['list-template']['list-code'][ $k ] );
			}
		}
		if ( ! empty( $changed_values['detail-template'] ) ) {
			$WPlusPlusApidae->deleteFolder( '/templates/cache' );
			$WPlusPlusApidae->deleteFolder( '/templates/detail' );
			foreach ( $options['detail-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['detail-template']['detail-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $detail_titles ) ) {
					$i ++;
				}
				$title           = $title . $i;
				$detail_titles[] = $title;
				$WPlusPlusApidae->mkdir( '/templates/detail' );
				$WPlusPlusApidae->put_contents( '/templates/detail/' . $title . '.twig', $options['detail-template']['detail-code'][ $k ] );
			}
		}
	}

}

Template::__init__();