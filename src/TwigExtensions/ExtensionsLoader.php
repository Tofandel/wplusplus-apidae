<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Tofandel\Apidae\TwigExtensions;


use Tofandel\Apidae\Shortcodes\Apidae_Categories;
use Tofandel\Apidae\Shortcodes\Apidae_List;
use Twig_Filter;
use Twig_Function;

class ExtensionsLoader extends \Twig_Extension {

	/**
	 * @return \Twig_Function[]
	 */
	public function getFunctions() {
		$functions                            = array();
		$functions['pagination_data']         = new Twig_Function( 'pagination_data', [
			Paginate::class,
			'PaginationDataFunction'
		] );
		$functions['paginate']                = new Twig_Function( 'paginate', [
			Paginate::class,
			'PaginateFunction'
		], array(
			'is_safe' => array( 'html' )
		) );
		$functions['__']                      = new Twig_Function( '__', function ( $str ) {
			global $WPlusPlusApidae;

			return __( $str, $WPlusPlusApidae->getTextDomain() );
		} );
		$functions['enqueue_script']          = new Twig_Function( 'enqueue_script', 'wp_enqueue_script' );
		$functions['enqueue_style']           = new Twig_Function( 'enqueue_style', 'wp_enqueue_style' );
		$functions['getCategoryFromObject']   = new Twig_Function( 'getCategoryFromObject', [
			Apidae_Categories::class,
			'getCategoryFromObject'
		] );
		$functions['getCategoriesFromObject'] = new Twig_Function( 'getCategoriesFromObject', [
			Apidae_Categories::class,
			'getCategoriesFromObject'
		] );

		return apply_filters( 'apidae_twig_functions', $functions );
	}


	/**
	 * @return \Twig_Filter[]
	 */
	public function getFilters() {
		$filters                = array();
		$filters['slugify']     = new Twig_Filter( 'slugify', 'wpp_slugify' );
		$filters['applyScheme'] = new Twig_Filter( 'applyScheme', [ Apidae_List::class, 'applyScheme' ] );
		$filters['orderBy']     = new Twig_Filter( 'orderBy', 'wpp_order_by' );
		$filters['groupBy']     = new Twig_Filter( 'groupBy', 'wpp_group_by' );

		return apply_filters( 'apidae_twig_filters', $filters );
	}
}