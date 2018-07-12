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

		return apply_filters( 'apidae_twig_functions', $functions );
	}


	/**
	 * @return \Twig_Filter[]
	 */
	public function getFilters() {
		$filters                = array();
		$filters['slugify']     = new \Twig_Filter( 'slugify', 'wpp_slugify' );
		$filters['applyScheme'] = new \Twig_Filter( 'applyScheme', [ Apidae_List::class, 'applyScheme' ] );
		$filters['orderBy']     = new \Twig_Filter( 'orderBy', function ( $array, $path ) {
			if ( ! is_array( $array ) ) {
				throw new \Exception( 'This filter can only be used on array' );
			}
			$path = explode( '.', $path );
			$c    = count( $path );
			// Sort the multidimensional array
			usort( $array, function ( $a, $b ) use ( $path, $c ) {
				$v1 = $a;
				for ( $i = 0; $i < $c; $i ++ ) {
					$k = $path[ $i ];
					if ( isset( $v1[ $k ] ) ) {
						$v1 = $v1[ $k ];
					} else {
						$v1 = '0';
					}
				}
				$v2 = $b;
				for ( $i = 0; $i < $c; $i ++ ) {
					$k = $path[ $i ];
					if ( isset( $v2[ $k ] ) ) {
						$v2 = $v2[ $k ];
					} else {
						$v2 = '0';
					}
				}

				return $v1 > $v2;
			} );

			return $array;
		} );
		$filters['groupBy']     = new \Twig_Filter( 'groupBy', function ( $array, $path ) {
			if ( ! is_array( $array ) ) {
				throw new \Exception( 'This filter can only be used on array' );
			}
			$new_array = array();
			$path      = explode( '.', $path );
			$c         = count( $path );
			foreach ( $array as $a ) {
				$v = $a;
				for ( $i = 0; $i < $c; $i ++ ) {
					$k = $path[ $i ];
					if ( isset( $v[ $k ] ) ) {
						$v = $v[ $k ];
					} else {
						$v = '0';
					}
				}
				if ( ! is_scalar( $v ) ) {
					throw new \Exception( 'The path must be final and so return a scalar' );
				}
				if ( ! empty( $new_array[ $v ] ) ) {
					$new_array[ $v ] = array_merge_recursive( $new_array[ $v ], $a );
				} else {
					$new_array[ $v ] = $a;
				}
			}

			return $new_array;
		} );

		return apply_filters( 'apidae_twig_filters', $filters );
	}
}