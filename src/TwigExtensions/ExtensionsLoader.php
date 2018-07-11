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
		$functions['pagination_data'] = new \Twig_Function( 'pagination_data', [
			Paginate::class,
			'PaginationDataFunction'
		] );
		$functions['paginate']        = new \Twig_Function( 'paginate', [ Paginate::class, 'PaginateFunction' ], array(
			'is_safe' => array( 'html' )
		) );

		return $functions;
	}
}