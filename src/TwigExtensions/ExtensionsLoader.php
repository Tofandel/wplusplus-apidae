<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 11/07/2018
 * Time: 07:02
 */

namespace Tofandel\Apidae\TwigExtensions;


class ExtensionsLoader extends \Twig_Extension {

	/**
	 * @return \Twig_Function[]
	 */
	public function getFunctions() {
		$functions                    = array();
		$functions['pagination_data'] = new \Twig_Function( 'pagination_data', [
			Paginate::class,
			'PaginationDataFunction'
		] );
		$functions['paginate']        = new \Twig_Function( 'paginate', [ Paginate::class, 'PaginateFunction' ], array(
			'is_safe' => array( 'html' )
		) );
		$functions['__']              = new \Twig_Function( '__', function ( $str ) {
			global $WPlusPlusApidae;

			return __( $str, $WPlusPlusApidae->getTextDomain() );
		} );

		return apply_filters( 'apidae_twig_functions', $functions );
	}


	/**
	 * @return \Twig_Filter[]
	 */
	public function getFilters() {
		$filters            = array();
		$filters['slugify'] = new \Twig_Filter( 'slugify', 'wpp_slugify' );

		return apply_filters( 'apidae_twig_filters', $filters );
	}
}