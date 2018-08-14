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

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_Interface;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_Categories
 * @package Tofandel\Apidae\Shortcodes
 *
 * @required-param  string  'categories'    Comma separated list of the Apidae categories slug you want displayed (you have to create them first in the apidae options)
 *
 * @param           bool    'all_link'      Whether to display the 'All' link or not (defaults to true)
 */
class Apidae_Categories implements WP_VC_Shortcode_Interface {
	use WP_VC_Shortcode;

	protected function __init() {
	}

	protected static $atts = array(
		'categories' => '',
		'all_link'   => 'true'
	);

	public static function initVCParams() {
		global $WPlusPlusApidae;

		$cats = array_reverse( self::getCategories() );

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to display the list of categories', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae Categories', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => 'apidae dashicons dashicons-category',
			'params'      => array(
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Categories', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'categories',
					'value'       => $cats,
					'admin_label' => true
				),
				array(
					'type'       => 'checkbox',
					'heading'    => esc_html__( 'Show the "All" link', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'all_link',
					'std'        => 'true'
				)
			)
		);
	}

	public static function getCategories() {
		static $cats;

		if ( ! isset( $cats ) ) {
			global $tofandel_apidae;

			$cats = array();

			if ( ! empty( $tofandel_apidae['categories']['category-id'] )
			     || ! empty( $tofandel_apidae['categories']['category-name'] ) ) {
				if ( empty( $tofandel_apidae['categories']['category-id'] ) ) {
					$tofandel_apidae['categories']['category-id'] = $tofandel_apidae['categories']['category-name'];
					$tofandel_apidae['categories']['category-id'] = array_map( '__return_false', $tofandel_apidae['categories']['category-id'] );
				}
				foreach ( $tofandel_apidae['categories']['category-id'] as $i => $name ) {
					if ( empty( $name ) ) {
						$name = wpp_slugify( $tofandel_apidae['categories']['category-name'][ $i ] );
					}
					$cats[ $name ] = $tofandel_apidae['categories']['category-name'][ $i ];
				}
			}
			$cats = apply_filters( 'apidae_get_categories', $cats );
		}

		return $cats;
	}

	public static function getCategoryFromObject( $o, $searchQuery = array() ) {
		$query_cat = get_query_var( 'apicategories', '' );
		if ( ! empty( $query_cat ) ) {
			return trim( explode( ',', $query_cat )[0] );
		}

		$cats = self::getCategoriesCriterias();

		$found = array();

		foreach ( $cats as $slug => $cat ) {
			if ( ! empty( $cat['criteresQuery']['type'] ) ) {
				//TODO does not handle parenthesis and other criterias
				if ( in_array( $o['type'], $cat['criteresQuery']['type'] ) ) {
					$found['id']    = $slug;
					$found['label'] = $cat['label'];

					return $found;
				}
			}
			if ( ! empty( $cat['searchQuery'] ) ) {
				$cat['searchQuery'] = str_replace( ' ', '|', $cat['searchQuery'] );
				if ( ! empty( $o['nom']['libelle'] ) && preg_match( '/(' . $cat['searchQuery'] . ')/i', $o['nom']['libelle'] ) ) {
					$found['id']    = $slug;
					$found['label'] = $cat['label'];

					return $found;
				}
				if ( empty( $searchQuery['searchFields'] ) ) {
					$searchQuery['searchFields'] = 'NOM_DESCRIPTION';
				}
				if ( strpos( $searchQuery['searchFields'], 'DESCRIPTION' ) !== false ) {
					if ( ! empty( $o['presentation']['descriptifCourt']['libelle'] ) && preg_match( '/' . $cat['searchQuery'] . '/i', $o['presentation']['descriptifCourt']['libelle'] ) ) {
						$found['id']    = $slug;
						$found['label'] = $cat['label'];

						return $found;
					}

					if ( ! empty( $o['presentation']['descriptifDetaille']['libelle'] ) && preg_match( '/' . $cat['searchQuery'] . '/i', $o['presentation']['descriptifDetaille']['libelle'] ) ) {
						$found['id']    = $slug;
						$found['label'] = $cat['label'];

						return $found;
					}
				}
				//TODO criteres
			}
			if ( ! empty( $cat['territoireIds'] ) ) {
				if ( ! is_array( $cat['territoireIds'] ) ) {
					$cat['territoireIds'] = array_map( 'trim', explode( ',', $cat['territoireIds'] ) );
				}
				foreach ( $o['territoires'] as $territoire ) {
					if ( in_array( $territoire['id'], $cat['territoireIds'] ) ) {
						$found['id']    = $slug;
						$found['label'] = $cat['label'];
						break;
					}
				}
				if ( isset( $found[ $slug ] ) ) {
					return $found;
				}
			}
			if ( ! empty( $cat['communeCodesInsee'] ) ) {
				if ( ! is_array( $cat['communeCodesInsee'] ) ) {
					$cat['communeCodesInsee'] = array_map( 'trim', explode( ',', $cat['communeCodesInsee'] ) );
				}
				if ( in_array( $o['localisation']['adresse']['codePostal'], $cat['communeCodesInsee'] ) ) {
					$found['id']    = $slug;
					$found['label'] = $cat['label'];

					return $found;
				}
			}
		}

		return $found;
	}

	public static function getCategoriesFromObject( $o, $searchQuery = array() ) {
		$query_cat = get_query_var( 'apicategories', '' );
		if ( ! empty( $query_cat ) ) {
			return array_map( 'trim', explode( ',', $query_cat ) );
		}

		$cats = self::getCategoriesCriterias();

		$found = array();

		foreach ( $cats as $slug => $cat ) {
			if ( ! empty( $cat['criteresQuery']['type'] ) ) {
				//TODO does not handle parenthesis and other criterias
				if ( in_array( $o['type'], $cat['criteresQuery']['type'] ) ) {
					$found[ $slug ] = $cat['label'];
					continue;
				}
			}
			if ( ! empty( $cat['searchQuery'] ) ) {
				$cat['searchQuery'] = str_replace( ' ', '|', $cat['searchQuery'] );
				if ( ! empty( $o['nom']['libelle'] ) && preg_match( '/(' . $cat['searchQuery'] . ')/i', $o['nom']['libelle'] ) ) {
					$found[ $slug ] = $cat['label'];
					continue;
				}
				if ( empty( $searchQuery['searchFields'] ) ) {
					$searchQuery['searchFields'] = 'NOM_DESCRIPTION';
				}
				if ( strpos( $searchQuery['searchFields'], 'DESCRIPTION' ) !== false ) {
					if ( ! empty( $o['presentation']['descriptifCourt']['libelle'] ) && preg_match( '/' . $cat['searchQuery'] . '/i', $o['presentation']['descriptifCourt']['libelle'] ) ) {
						$found[ $slug ] = $cat['label'];
						continue;
					}

					if ( ! empty( $o['presentation']['descriptifDetaille']['libelle'] ) && preg_match( '/' . $cat['searchQuery'] . '/i', $o['presentation']['descriptifDetaille']['libelle'] ) ) {
						$found[ $slug ] = $cat['label'];
						continue;
					}
				}
				//TODO criteres
			}
			if ( ! empty( $cat['territoireIds'] ) ) {
				if ( ! is_array( $cat['territoireIds'] ) ) {
					$cat['territoireIds'] = array_map( 'trim', explode( ',', $cat['territoireIds'] ) );
				}
				foreach ( $o['territoires'] as $territoire ) {
					if ( in_array( $territoire['id'], $cat['territoireIds'] ) ) {
						$found[ $slug ] = $cat['label'];
						break;
					}
				}
				if ( isset( $found[ $slug ] ) ) {
					continue;
				}
			}
			if ( ! empty( $cat['communeCodesInsee'] ) ) {
				if ( ! is_array( $cat['communeCodesInsee'] ) ) {
					$cat['communeCodesInsee'] = array_map( 'trim', explode( ',', $cat['communeCodesInsee'] ) );
				}
				if ( in_array( $o['localisation']['adresse']['codePostal'], $cat['communeCodesInsee'] ) ) {
					$found[ $slug ] = $cat['label'];
					continue;
				}
			}
		}

		return $found;
	}

	public static function getCategoriesCriterias() {
		static $cats;

		if ( ! isset( $cats ) ) {
			foreach ( self::getCategories() as $slug => $category ) {
				$cats[ $slug ] = self::getCriteria( $slug );
				if ( ! empty( $cats[ $slug ]['criteresQuery'] ) ) {
					$crit                           = explode( ' ', $cats[ $slug ]['criteresQuery'] );
					$cats[ $slug ]['criteresQuery'] = array();
					foreach ( $crit as $key => $critere ) {
						$c                                                                 = explode( ':', $critere );
						$cats[ $slug ]['criteresQuery'][ str_replace( '+', '', $c[0] ) ][] = $c[1];
					}
				}
				$cats[ $slug ]['label'] = $category;
			}
		}

		return $cats;
	}


	public static function getCriteria( $categorie_slug ) {
		$cats = self::getCategories();

		$i = array_search( $categorie_slug, array_keys( $cats ) );

		if ( $i !== false ) {
			global $tofandel_apidae;
			if ( ! empty( $tofandel_apidae['categories']['category-query'][ $i ] ) ) {
				return json_decode( $tofandel_apidae['categories']['category-query'][ $i ], true );
			}
		}

		return false;
	}


	/**
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {
		global $WPlusPlusApidae;


		$cats = self::getCategories();
		if ( empty( $atts['categories'] ) ) {
			$atts['categories'] = $cats;
		} else {
			$atts['categories'] = explode( ',', $atts['categories'] );
		}

		//TODO multiple categories
		$current = get_query_var( 'apicategories', '' );
		$content = '<ul class="categories">';
		if ( $atts['all_link'] == 'true' ) {
			$content .= '<li class="cat-all' . ( empty( $current ) ? ' current' : '' ) . '"><a href="' . get_page_link() . '">' . __( 'All', $WPlusPlusApidae->getTextDomain() ) . '</a></li>';
		}
		$search = get_query_var( 'apisearch' );
		$args   = ! empty( $search ) ? array( 'apisearch' => $search ) : array();
		foreach ( $atts['categories'] as $cat ) {
			if ( ! empty( $cats[ $cat ] ) ) {
				$args['apicategories'] = $cat;
				$content               .= '<li class="cat-' . $cat . ( $cat == $current ? ' current' : '' ) . '"><a href="' . add_query_arg( $args, get_page_link() ) . '">' . $cats[ $cat ] . '</a></li>';
			}
		}
		$content .= "</ul>";

		return do_shortcode( $content );
	}

}