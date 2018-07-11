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
		static::$twig = new Twig_Environment( $loader, array( 'cache' => $WPlusPlusApidae->folder( 'templates/cache' ) ) );
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
}

Template::__init__();