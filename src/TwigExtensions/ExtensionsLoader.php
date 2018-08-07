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
		$functions['pagination_data'] = new \Twig\TwigFunction( 'pagination_data', [
			Paginate::class,
			'PaginationDataFunction'
		] );
		$functions['paginate']        = new \Twig\TwigFunction( 'paginate', [
			Paginate::class,
			'PaginateFunction'
		], array(
			'is_safe' => array( 'html' )
		) );
		$functions['__']              = new \Twig\TwigFunction( '__', function ( $str ) {
			global $WPlusPlusApidae;

			return __( $str, $WPlusPlusApidae->getTextDomain() );
		} );
		$functions['enqueue_script']  = new \Twig\TwigFunction( 'enqueue_script', 'wp_enqueue_script' );
		$functions['enqueue_style']   = new \Twig\TwigFunction( 'enqueue_style', 'wp_enqueue_style' );

		return apply_filters( 'apidae_twig_functions', $functions );
	}


	/**
	 * @return \Twig_Filter[]
	 */
	public function getFilters() {
		$filters                = array();
		$filters['slugify']     = new \Twig\TwigFilter( 'slugify', 'wpp_slugify' );
		$filters['applyScheme'] = new \Twig\TwigFilter( 'applyScheme', [ Apidae_List::class, 'applyScheme' ] );
		$filters['orderBy']     = new \Twig\TwigFilter( 'orderBy', 'wpp_order_by' );
		$filters['groupBy']     = new \Twig\TwigFilter( 'groupBy', 'wpp_group_by' );

		return apply_filters( 'apidae_twig_filters', $filters );
	}
}