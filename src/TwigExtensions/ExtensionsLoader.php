<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 11/07/2018
 * Time: 07:02
 */

namespace Tofandel\Apidae\TwigExtensions;


use Tofandel\Apidae\Shortcodes\Apidae_List;

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
		$functions['enqueue_script']  = new \Twig_Function( 'enqueue_script', 'wp_enqueue_script' );
		$functions['enqueue_style']   = new \Twig_Function( 'enqueue_style', 'wp_enqueue_style' );

		return apply_filters( 'apidae_twig_functions', $functions );
	}


	/**
	 * @return \Twig_Filter[]
	 */
	public function getFilters() {
		$filters                = array();
		$filters['slugify']     = new \Twig_Filter( 'slugify', 'wpp_slugify' );
		$filters['applyScheme'] = new \Twig_Filter( 'applyScheme', [ Apidae_List::class, 'applyScheme' ] );
		$filters['orderBy']     = new \Twig_Filter( 'orderBy', 'wpp_order_by' );
		$filters['groupBy']     = new \Twig_Filter( 'groupBy', 'wpp_group_by' );

		return apply_filters( 'apidae_twig_filters', $filters );
	}
}