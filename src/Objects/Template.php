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
		$loader       = new Twig_Loader_Filesystem( $WPlusPlusApidae->folder( TemplateFilesHandler::TPL_DIR ) );
		static::$twig = new Twig_Environment( $loader, array(
			'cache' => TemplateFilesHandler::CACHE_DIR,
			'debug' => (bool) WP_DEBUG,
		) );
		if ( WP_DEBUG ) {
			static::$twig->addExtension( new \Twig_Extension_Debug() );
		}
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
	 */
	public function __construct( $file ) {
		$this->template = static::$twig->load( $file );
	}

	/**
	 * Renders the constructed template
	 *
	 * @param array $variables
	 *
	 * @return string
	 */
	public function render( $variables = array() ) {
		return $this->template->render( $variables );
	}
}

Template::__init__();