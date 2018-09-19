<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Apidae\Objects;


use Tofandel\Apidae\Modules\TemplateFilesHandler;
use Tofandel\Apidae\TwigExtensions\ExtensionsLoader;
use Tofandel\Core\Traits\Initializable;
use Twig_Environment;
use Twig_Error_Syntax;
use Twig_Loader_Filesystem;
use Twig_Source;

class Template {
	use Initializable;

	/**
	 * @var Twig_Environment
	 */
	static $twig;

	public static function __StaticInit() {
		global $WPlusPlusApidae;
		$loader       = new Twig_Loader_Filesystem( $WPlusPlusApidae->folder( TemplateFilesHandler::TPL_DIR ) );
		static::$twig = new Twig_Environment( $loader, array(
			'cache' => WP_CONTENT_DIR . TemplateFilesHandler::CACHE_DIR,
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
	 *
	 * @throws Twig_Error_Syntax
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
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

	/**
	 * Returns false if tpl is valid else returns the error message
	 *
	 * @param $template
	 *
	 * @return false|string
	 */
	public static function check( $template, $name ) {
		try {
			static::$twig->parse( static::$twig->tokenize( new Twig_Source( $template, $name ) ) );

			return false;
			// the $template is valid
		} catch ( Twig_Error_Syntax $e ) {
			return $e->getMessage();
			// $template contains one or more syntax errors
		}
	}
}

Template::__StaticInit();