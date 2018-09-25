<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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

	protected static $atts = array(
		'categories' => '',
		'all_link'   => 'true',
		'langs'      => ''
	);

	public static function initVCParams() {
		global $WPlusPlusApidae;

		$langs = Apidae_List::getLangs();
		$cats  = array_reverse( self::getCategories( 'all' ) );

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
				),
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Languages', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'langs',
					'value'       => $langs,
					'admin_label' => true
				),
			)
		);
	}

	/**
	 * @param string|array $langs
	 *
	 * @return array
	 */
	public static function getQueryCategories( $langs = 'all' ) {
		$queryCategories = get_query_var( 'apicategories', array() );
		if ( ! empty( $queryCategories ) && ! is_array( $queryCategories ) ) {
			$queryCategories = array_map( 'trim', explode( ',', $queryCategories ) );
		}
		$cats = self::getCategories( $langs );
		foreach ( $queryCategories as $key => $query_category ) {
			if ( ! isset( $cats[ $query_category ] ) ) {
				unset( $queryCategories[ $key ] );
			}
		}

		return $queryCategories;
	}

	public static function getCategories( $langs = false ) {
		static $cats = array();

		if ( is_array( $langs ) ) {
			$l = implode( ',', $langs );
		} else {
			$l = (string) $langs;
		}
		if ( ! isset( $cats[ $l ] ) ) {
			global $tofandel_apidae;

			$cats[ $l ] = array();

			if ( empty( $langs ) ) {
				$langs = array( strtolower( explode( '_', get_locale() )[0] ) );
			}
			if ( ! is_array( $langs ) ) {
				$langs = array( $langs );
			}

			if ( ! empty( $tofandel_apidae['categories']['category-id'] )
			     || ! empty( $tofandel_apidae['categories']['category-name'] ) ) {
				if ( empty( $tofandel_apidae['categories']['category-id'] ) ) {
					$tofandel_apidae['categories']['category-id'] = $tofandel_apidae['categories']['category-name'];
					$tofandel_apidae['categories']['category-id'] = array_map( '__return_false', $tofandel_apidae['categories']['category-id'] );
				}
				foreach ( $tofandel_apidae['categories']['category-id'] as $i => $name ) {
					if ( ! in_array( 'all', $langs ) && ! empty( $tofandel_apidae['categories']['lang'][ $i ] ) && ! in_array( $tofandel_apidae['categories']['lang'][ $i ], $langs ) ) {
						continue;
					}
					if ( empty( $name ) ) {
						$name = wpp_slugify( $tofandel_apidae['categories']['category-name'][ $i ] );
					}
					$cats[ $l ][ $name ] = $tofandel_apidae['categories']['category-name'][ $i ];
				}
			}
			$cats[ $l ] = apply_filters( 'apidae_get_categories', $cats, $langs );
		}

		return $cats[ $l ];
	}

	public static function getCategoryFromObject( $o, $searchQuery = array() ) {
		$searchQuery = (array) $searchQuery;
		$query_cat   = self::getQueryCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
		if ( count( $query_cat ) == 1 ) {
			$cats = self::getCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
			foreach ( $query_cat as $cat ) {
				if ( isset( $cats[ $cat ] ) ) {
					return array( 'id' => $cat, 'label' => $cats[ $cat ] );
				}
			}

			return false;
		}

		$cats = self::getCategoriesCriterias();
		if ( ! empty( $query_cat ) ) {
			$categories = self::getCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
			$cats       = array_intersect_key( $cats, $categories );
		}

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
		$query_cat = self::getQueryCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
		if ( count( $query_cat ) == 1 ) {
			$cats = self::getCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
			foreach ( $query_cat as $cat ) {
				if ( isset( $cats[ $cat ] ) ) {
					return array( 'id' => $cat, 'label' => $cats[ $cat ] );
				}
			}

			return false;
		}

		$cats = self::getCategoriesCriterias();
		if ( ! empty( $query_cat ) ) {
			$categories = self::getCategories( isset( $searchQuery['locales'] ) ? $searchQuery['locales'] : 'all' );
			$cats       = array_intersect_key( $cats, $categories );
		}

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
			foreach ( self::getCategories( 'all' ) as $slug => $category ) {
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
		$cats = self::getCategories( 'all' );

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

		$langs = array_map( 'trim', explode( ',', $atts['langs'] ) );
		if ( empty( $langs ) ) {
			$langs = array( strtolower( explode( '_', get_locale() )[0] ) );
		}

		$cats = self::getCategories( $langs );
		if ( empty( $atts['categories'] ) ) {
			$atts['categories'] = array_keys( $cats );
		} else {
			$atts['categories'] = explode( ',', $atts['categories'] );
		}

		//TODO multiple categories
		$currents = self::getQueryCategories( $langs );
		$content  = '<ul class="categories">';
		if ( $atts['all_link'] == 'true' ) {
			$content .= '<li class="cat-all' . ( empty( $currents ) ? ' current' : '' ) . '"><a href="' . get_page_link() . '">' . __( 'All', $WPlusPlusApidae->getTextDomain() ) . '</a></li>';
		}
		$search = get_query_var( 'apisearch' );
		$args   = ! empty( $search ) ? array( 'apisearch' => $search ) : array();
		foreach ( $atts['categories'] as $cat ) {
			if ( ! empty( $cats[ $cat ] ) ) {
				$args['apicategories'] = $cat;
				$content               .= '<li class="cat-' . $cat . ( in_array( $cat, $currents ) ? ' current' : '' ) . '"><a href="' . add_query_arg( $args, get_page_link() ) . '">' . $cats[ $cat ] . '</a></li>';
			}
		}
		$content .= "</ul>";

		return do_shortcode( $content );
	}

}