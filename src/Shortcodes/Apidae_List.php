<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Apidae\Modules\TemplateFilesHandler;
use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Interfaces\WP_VC_Shortcode as WP_VC_Shortcode_Interface;
use Tofandel\Core\Traits\WP_VC_Shortcode;

/**
 * Shortcode Apidae_List
 * @package Tofandel\Apidae\Shortcodes
 *
 * @required-param  string  'template'      The slug of the list template
 * @required-param  int     'detail_id'     The ID of the detail page
 * @required-param  int     'selection_ids' Comma separated list of apidae selection's id
 *
 * @param           bool    'paged'         Whether the list should be paginated or not (defaults to 'true')
 * @param           int     'nb_result'     The number of result per page (defaults to '30')
 * @param           string  'more_json'     If you need to modify the query sent to Apidae you can do this here in json format
 * @param           string  'order'         How do you want the result to be ordered (available: 'NOM','IDENTIFIANT','RANDOM','DATE_OUVERTURE','PERTINENCE','DISTANCE') (defaults to 'PERTINENCE')
 * @param           bool    'reverse_order' Whether you want the order to be ascendant or descendant (defaults to 'false' => ascendant)
 * @param           string  'langs'         Comma separated list of languages that you want to receive in the template (defaults to 'fr')
 * @param           string  'search_fields' Where do you want the search query to look in (available: 'NOM', 'NOM_DESCRIPTION', 'NOM_DESCRIPTION_CRITERES') (defaults to 'NOM_DESCRIPTION_CRITERES')
 * @param           string  'detail_scheme' The link scheme to the detail template (defaults to '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%') you can use any path from the apidae object
 */
class Apidae_List implements WP_VC_Shortcode_Interface {
	use WP_VC_Shortcode {
		__StaticInit__ as parentStaticInit;
	}

	public static function __StaticInit__() {
		self::parentStaticInit();
		add_filter( 'query_vars', array( static::class, 'add_query_vars_filter' ) );
	}

	/**
	 * @param array $vars
	 *
	 * @return array
	 */
	public static function add_query_vars_filter( $vars ) {
		$vars[] = "apicategories";
		$vars[] = "apisearch";
		$vars[] = "datedebut";
		$vars[] = "datefin";

		return $vars;
	}

	public static function getLangs() {
		global $WPlusPlusApidae;

		return array(
			__( 'French', $WPlusPlusApidae->getTextDomain() )              => 'fr',
			__( 'English', $WPlusPlusApidae->getTextDomain() )             => 'en',
			__( 'German', $WPlusPlusApidae->getTextDomain() )              => 'de',
			__( 'Dutch', $WPlusPlusApidae->getTextDomain() )               => 'nl',
			__( 'Italian', $WPlusPlusApidae->getTextDomain() )             => 'it',
			__( 'Spanish', $WPlusPlusApidae->getTextDomain() )             => 'es',
			__( 'Russian', $WPlusPlusApidae->getTextDomain() )             => 'ru',
			__( 'Chinese', $WPlusPlusApidae->getTextDomain() )             => 'zh',
			__( 'Portuguese (Brazil)', $WPlusPlusApidae->getTextDomain() ) => 'pt-br',
		);
	}

	protected static $atts = [
		'template'      => '',
		'detail_id'     => '',
		'selection_ids' => '',
		'paged'         => 'true',
		'nb_result'     => '30',
		'order'         => 'PERTINENCE',
		'reverse_order' => '',
		'search_fields' => 'NOM_DESCRIPTION_CRITERES',
		'langs'         => 'fr',
		'detail_scheme' => '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%',
		'more_json'     => ''
	];

	/**
	 * @throws \ReflectionException
	 */
	public static function initVCParams() {
		global $WPlusPlusApidae;

		$selections = $details_pages = array();

		$langs      = self::getLangs();
		$templates  = glob( $WPlusPlusApidae->file( 'templates/list/*.twig' ) );
		$file_names = array( esc_html__( 'Please select a template', $WPlusPlusApidae->getTextDomain() ) => '' );

		foreach ( $templates as $template ) {
			$slug                = basename( $template, '.twig' );
			$file_names[ $slug ] = $slug;
		}

		$pages = get_pages();

		foreach ( $pages as $page ) {
			if ( empty( $page->post_content ) ) {
				continue;
			}
			/** @var \WP_Post $page */
			if ( wpp_has_shortcode( $page->post_content, Apidae_Detail::getName() ) ) {
				$details_pages[ $page->post_title ] = $page->ID;
			}
		}

		$tmp_selections = ApidaeRequest::getSelections();
		if ( ! empty( $tmp_selections ) ) {
			foreach ( $tmp_selections as $selection ) {
				$selections[ $selection['nom'] . ' (' . $selection['id'] . ')' ] = $selection['id'];
			}
		} else {
			$selections[ __( 'No selection found', $WPlusPlusApidae->getTextDomain() ) ] = 0;
		}


		$params = empty( $file_names ) ? array(
			array(
				'heading'    => esc_html__( 'No template created', $WPlusPlusApidae->getTextDomain() ),
				'message'    => sprintf( esc_html__( 'Click %shere%s to create one', $WPlusPlusApidae->getTextDomain() ),
					'<a href="' . esc_url( add_query_arg( array(
						'page' => 'wplusplus-apidae',
						'tab'  => 3
					), admin_url( 'admin.php' ) ) ) . '" target="_blank">', '</a>' ),
				'type'       => 'warning',
				'param_name' => 'template'
			)
		) : array(
			array(
				'type'             => 'dropdown',
				'heading'          => esc_html__( 'Template', $WPlusPlusApidae->getTextDomain() ),
				'param_name'       => 'template',
				'value'            => $file_names,
				'admin_label'      => true,
				'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
			)
		);

		$params[1] = empty( $details_pages ) ?
			array(
				'heading'    => esc_html__( 'No detail page found', $WPlusPlusApidae->getTextDomain() ),
				'message'    => sprintf( esc_html__( 'Click %shere%s to create one', $WPlusPlusApidae->getTextDomain() ),
					'<a href="' . esc_url( add_query_arg( array(
						'page' => 'wplusplus-apidae',
						'tab'  => 4
					), admin_url( 'admin.php' ) ) ) . '" target="_blank">', '</a>' ),
				'type'       => 'warning',
				'param_name' => 'detail_id'
			) : array(
				'type'             => 'dropdown',
				'heading'          => esc_html__( 'Detail page', $WPlusPlusApidae->getTextDomain() ),
				'param_name'       => 'detail_id',
				'value'            => $details_pages,
				'admin_label'      => true,
				'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
			);

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->getTextDomain() ),
			'description' => esc_html__( 'Shortcode to create an apidae list', $WPlusPlusApidae->getTextDomain() ),
			'name'        => esc_html__( 'Apidae List', $WPlusPlusApidae->getTextDomain() ),
			'icon'        => plugins_url( 'logo.svg', $WPlusPlusApidae->getFile() ),
			'params'      => array_merge( $params, array(
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Selections', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'selection_ids',
					'value'       => $selections,
					'description' => __( 'The identifiers of the selections to retrieve, comma separated', $WPlusPlusApidae->getTextDomain() ),
					'admin_label' => true
				),
				array(
					'type'             => 'checkbox',
					'heading'          => esc_html__( 'Paged', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'paged',
					'description'      => __( 'If unchecked the results will not be paginated.', $WPlusPlusApidae->getTextDomain() ),
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param vc_column-with-padding',
				),
				array(
					'type'             => 'number',
					'heading'          => esc_html__( 'Number of results per page', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'nb_result',
					'std'              => 30,
					'dependency'       => array( 'element' => 'paged', 'value' => array( 'true', true ) ),
					'extra'            => array( 'min' => 1, 'max' => 999 ),
					'admin_label'      => true,
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_number vc_wrapper-param-type-number vc_shortcode-param vc_column-with-padding',
				),
				array(
					'type'             => 'dropdown',
					'heading'          => esc_html__( 'Order by', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'order',
					'value'            => array(
						__( 'Name', $WPlusPlusApidae->getTextDomain() )       => 'NOM',
						__( 'Identifier', $WPlusPlusApidae->getTextDomain() ) => 'IDENTIFIANT',
						__( 'Random', $WPlusPlusApidae->getTextDomain() )     => 'RANDOM',
						__( 'Date', $WPlusPlusApidae->getTextDomain() )       => 'DATE_OUVERTURE',
						__( 'Pertinence', $WPlusPlusApidae->getTextDomain() ) => 'PERTINENCE',
						__( 'Distance', $WPlusPlusApidae->getTextDomain() )   => 'DISTANCE',
					),
					'admin_label'      => true,
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column-with-padding',
				),
				array(
					'type'             => 'checkbox',
					'heading'          => esc_html__( 'Reverse Order', $WPlusPlusApidae->getTextDomain() ),
					'param_name'       => 'reverse_order',
					'description'      => __( 'If checked the ordering will be inverted.', $WPlusPlusApidae->getTextDomain() ),
					'edit_field_class' => 'vc_col-xs-6 vc_column wpb_el_type_checkbox vc_wrapper-param-type-checkbox vc_shortcode-param vc_column-with-padding'
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Search Fields', $WPlusPlusApidae->getTextDomain() ),
					'param_name' => 'search_fields',
					'value'      => array(
						__( 'Name', $WPlusPlusApidae->getTextDomain() )                         => 'NOM',
						__( 'Name & description', $WPlusPlusApidae->getTextDomain() )           => 'NOM_DESCRIPTION',
						__( 'Name, description & criteria', $WPlusPlusApidae->getTextDomain() ) => 'NOM_DESCRIPTION_CRITERES',
					),
					"std"        => 'NOM_DESCRIPTION_CRITERES'
				),
				array(
					'type'        => 'multidropdown',
					'heading'     => esc_html__( 'Languages', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'langs',
					'value'       => $langs,
					'admin_label' => true
				),
				array(
					'group'       => __( 'Advanced', $WPlusPlusApidae->getTextDomain() ),
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Detail link scheme', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'detail_scheme',
					'description' => __( 'The link scheme to the detail template', $WPlusPlusApidae->getTextDomain() ),
					'std'         => '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%'
				),
				array(
					'group'       => __( 'Advanced', $WPlusPlusApidae->getTextDomain() ),
					'type'        => 'textarea',
					'heading'     => esc_html__( 'More JSON', $WPlusPlusApidae->getTextDomain() ),
					'param_name'  => 'more_json',
					'description' => __( 'Additional configuration in JSON (ex: {"territoires": [95938, 156922]})<br><strong style="color:red">This will override any already present parameters!</strong>', $WPlusPlusApidae->getTextDomain() )
				),
			) )
		);
	}


	public static function applyScheme( $scheme, $object ) {
		$object = (array) $object;

		return preg_replace_callback( '#%([^%]*)%#', function ( $var ) use ( $object ) {
			$path = array_reverse( explode( '.', $var[1] ) );
			$v    = $object;
			while ( $k = array_pop( $path ) ) {
				if ( isset( $v[ $k ] ) ) {
					$v = $v[ $k ];
				} else {
					return "";
				}
			}

			return (string) $v;
		}, $scheme );
	}

	/**
	 * Fonction outil pour vérifier une date (format + date réelle)
	 *
	 * @param string $date valide si en format "YYYY-MM-DD"
	 *
	 * @return bool
	 */
	public static function checkDateFormat( $date ) {
		// match the format of the date
		if ( preg_match( "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts ) ) {
			// check whether the date is valid or not
			if ( checkdate( $parts[2], $parts[3], $parts[1] ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {
		if ( empty( $atts['template'] ) ) {
			$f = 'list-layout.twig';
		} else {
			$f = TemplateFilesHandler::LIST_DIR . basename( $atts['template'] ) . '.twig';
		}

		try {
			$tpl = new Template( $f );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : "";
		}

		$numPerPage = max( 1, intval( $atts['nb_result'] ) );

		$inter = array(
			'apisearch' => 'searchQuery',
			'datedebut' => 'dateDebut',
			'datefin'   => 'dateFin'
		);

		$searchWords = get_query_var( 'apisearch', '' );
		//$searchCategories = get_query_var( 'apicategories', '' ) != '' ? explode( '/', get_query_var( 'apicategories', '' ) ) : array();
		$searchCategories = Apidae_Categories::getQueryCategories();

		$page_query = array();

		if ( ! empty( $searchCategories ) ) {
			$page_query['apicategories'] = implode( ',', $searchCategories ); //implode( '/', $searchCategories );
		}

		if ( ! empty( $searchWords ) ) {
			$page_query['apisearch'] = $searchWords;
		}

		$dateDebut = get_query_var( 'datedebut', '' );
		$dateFin   = get_query_var( 'datefin', '' );

		if ( ! empty( $dateDebut ) && self::checkDateFormat( $dateDebut ) ) {
			$page_query['datedebut'] = $dateDebut;
		}
		if ( ! empty( $dateFin ) && self::checkDateFormat( $dateFin ) ) {
			$page_query['datefin'] = $dateFin;
		}

		$currentPage = max( 1, intval( get_query_var( 'page', 1 ) ) );

		$url = '';
		if ( $atts['paged'] == 'true' ) {
			$url = '%PAGE%/';
		}
		$urlScheme = add_query_arg( $page_query, trailingslashit( get_page_link() ) . $url );
		$url       = add_query_arg( $page_query, get_page_link() );

		$search_query = array();

		foreach ( $page_query as $k => $v ) {
			if ( isset( $inter[ $k ] ) ) {
				$search_query[ $inter[ $k ] ] = $v;
			}
		}

		$json = json_decode( $atts['more_json'] ) ?: array();

		$full_query      = array_merge( array(
			'selectionIds'  => ! empty( $atts['selection_ids'] ) ? array_map( 'trim', explode( ',', $atts['selection_ids'] ) ) : array(),
			'order'         => $atts['order'],
			'searchFields'  => $atts['search_fields'],
			'asc'           => (bool) $atts['reverse_order'],
			'locales'       => explode( ',', $atts['langs'] ),
			'searchQuery'   => '',
			'criteresQuery' => ''
		), $json );
		$prevSearchQuery = $full_query['searchQuery'];

		//If some searchQuery or critereQuery where defined in json we append them to what the user defined
		if ( ! empty( $prevSearchQuery ) && ! empty( $searchWords ) ) {
			$search_query['searchQuery'] = $prevSearchQuery . ' ' . $searchWords;
		}
		/*
		$prevSearchQuery = $full_query['criteresQuery'];

		if ( ! empty( $prevSearchQuery ) && ! empty( $searchCategories ) ) {
			$search_query['criteresQuery'] = $prevSearchQuery . ' ' . $searchCategories; //implode( ' ', $searchCriteres );
		}*/

		foreach ( $searchCategories as $category ) {
			if ( $criteria = Apidae_Categories::getCriteria( $category ) ) {
				foreach ( $criteria as $k => $val ) {
					if ( empty( $val ) ) {
						continue;
					}
					if ( ! empty( $full_query[ $k ] ) && is_string( $full_query[ $k ] ) ) {
						$full_query[ $k ] .= ' ' . $val;
					} elseif ( ! empty( $full_query[ $k ] ) && is_array( $full_query[ $k ] ) ) {
						$full_query[ $k ] = array_merge( $full_query[ $k ], (array) $val );
					} else {
						$full_query[ $k ] = $val;
					}
				}
			}
		}

		$full_query = array_merge( $full_query, $search_query );

		if ( $currentPage > 1 && $atts['paged'] ) {
			$first = intval( $currentPage - 1 ) * $numPerPage;
		} else {
			$first = 0;
		}

		$numFound = 0;
		$list     = ApidaeRequest::getList( $full_query, $numPerPage, $first );
		if ( ! is_array( $list ) || empty( $list['objetsTouristiques'] ) ) {
			global $wp_query;
			$wp_query->set_404();
		}
		if ( is_array( $list ) ) {
			$numFound = $list['numFound'];
		}
		$totalPages  = ceil( $numFound / $numPerPage );
		$currentPage = min( $totalPages, $currentPage );

		$detailSlug = '';
		if ( ! empty( $atts['detail_id'] ) ) {
			$p = get_post( $atts['detail_id'] );
			if ( $p ) {
				$detailSlug = $p->post_name;
			}
		}

		try {
			global $tofandel_apidae;
			$content = $tpl->render( apply_filters( 'apidae_list_twig_vars', array(
				'numResult'      => $numFound,
				'searchResult'   => isset( $list['objetsTouristiques'] ) ? $list['objetsTouristiques'] : false,
				'currentPage'    => $currentPage,
				'totalPages'     => $totalPages,
				'urlScheme'      => $urlScheme,
				'url'            => $url,
				'useMaps'        => $tofandel_apidae['maps_enable'],
				'detailPageSlug' => $detailSlug,
				'detailScheme'   => ! empty( $atts['detail_scheme'] ) ? $atts['detail_scheme'] : '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%',
				'siteUrl'        => site_url(),
				'pageQuery'      => $page_query,
				'searchWords'    => $searchWords,
				'query'          => $full_query
				//'categories'     => Apidae_Categories::getCategoriesCriterias()
			) ) );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );

			return WP_DEBUG ? $e->getMessage() : '';
		}

		return do_shortcode( $content );
	}
}