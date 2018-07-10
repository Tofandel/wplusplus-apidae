<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 03/07/2018
 * Time: 16:06
 */

namespace Tofandel\Apidae\Shortcodes;


use Tofandel\Apidae\Objects\ApidaeRequest;
use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Traits\WP_VC_Shortcode;


class Apidae_List {
	use WP_VC_Shortcode;

	protected function __init() {
		global $WPlusPlusApidae;
		static::$atts = array(
			'selection_ids' => '',
			'order'         => 'PERTINENCE',
			'reverse_order' => '',
			'search_fields' => 'NOM_DESCRIPTION_CRITERES',
			'lang'          => 'fr',
			'more_json'     => ''
		);

		$templates  = glob( $WPlusPlusApidae->folder( 'template/list/*.twig' ) );
		$file_names = array();

		foreach ( $templates as $template ) {
			$slug                = basename( $template, '.twig' );
			$file_names[ $slug ] = $slug;
		}

		static::$vc_params = array(
			'category'    => esc_html__( 'Apidae', $WPlusPlusApidae->text_domain ),
			'description' => __( 'Shortcode to create an apidae list', $WPlusPlusApidae->text_domain ),
			'name'        => __( 'Apidae List', $WPlusPlusApidae->text_domain ),
			'params'      => array(
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Template', $WPlusPlusApidae->text_domain ),
					'param_name' => 'template',
					'value'      => $file_names,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Paged', $WPlusPlusApidae->text_domain ),
					'param_name'  => 'paged',
					'description' => __( 'If unchecked the results will not be paginated.', $WPlusPlusApidae->text_domain ),
				),
				array(
					'type'       => 'integer',
					'heading'    => esc_html__( 'Number of results per page', $WPlusPlusApidae->text_domain ),
					'param_name' => 'nb_result',
					'std'        => 30,
					'dependency' => array( 'element' => 'paged', 'value' => array( true, '1' ) )
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Selection IDs', $WPlusPlusApidae->text_domain ),
					'param_name'  => 'selection_ids',
					'description' => __( 'The identifiers of the selections to retrieve, comma separated', $WPlusPlusApidae->text_domain )
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Order', $WPlusPlusApidae->text_domain ),
					'param_name' => 'order',
					'value'      => array(
						'NOM'            => __( 'Name', $WPlusPlusApidae->text_domain ),
						'IDENTIFIANT'    => __( 'Identifier', $WPlusPlusApidae->text_domain ),
						'RANDOM'         => __( 'Random', $WPlusPlusApidae->text_domain ),
						'DATE_OUVERTURE' => __( 'Date', $WPlusPlusApidae->text_domain ),
						'PERTINENCE'     => __( 'Pertinence', $WPlusPlusApidae->text_domain ),
						'DISTANCE'       => __( 'Distance', $WPlusPlusApidae->text_domain ),
					),
					"std"        => 'PERTINENCE'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Reverse Order', $WPlusPlusApidae->text_domain ),
					'param_name'  => 'reverse_order',
					'description' => __( 'If checked the ordering will be inverted.', $WPlusPlusApidae->text_domain ),
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Search Fields', $WPlusPlusApidae->text_domain ),
					'param_name' => 'search_fields',
					'value'      => array(
						'NOM'                      => __( 'Name', $WPlusPlusApidae->text_domain ),
						'NOM_DESCRIPTION'          => __( 'Name & description', $WPlusPlusApidae->text_domain ),
						'NOM_DESCRIPTION_CRITERES' => __( 'Name, description & criteria', $WPlusPlusApidae->text_domain ),
					),
					"std"        => 'NOM_DESCRIPTION_CRITERES'
				),
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Lang', $WPlusPlusApidae->text_domain ),
					'param_name' => 'lang',
					'value'      => array(
						'fr'    => __( 'French', $WPlusPlusApidae->text_domain ),
						'en'    => __( 'English', $WPlusPlusApidae->text_domain ),
						'de'    => __( 'German', $WPlusPlusApidae->text_domain ),
						'nl'    => __( 'Dutch', $WPlusPlusApidae->text_domain ),
						'it'    => __( 'Italian', $WPlusPlusApidae->text_domain ),
						'es'    => __( 'Spanish', $WPlusPlusApidae->text_domain ),
						'ru'    => __( 'Russian', $WPlusPlusApidae->text_domain ),
						'zh'    => __( 'Chinese', $WPlusPlusApidae->text_domain ),
						'pt-br' => __( 'Portuguese (Brazil)', $WPlusPlusApidae->text_domain ),
					),
					"std"        => 'fr'
				),
				array(
					'type'        => 'textarea',
					'heading'     => esc_html__( 'More JSON', $WPlusPlusApidae->text_domain ),
					'param_name'  => 'more_json',
					'description' => __( 'Additional configuration in JSON (ex: {"territoires": [95938, 156922]})<br><strong style="color:red">This will override any already present parameters!</strong>', $WPlusPlusApidae->text_domain )
				),
			)
		);
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
		global $WPlusPlusApidae;
		$f = $WPlusPlusApidae->folder( 'template/list/' . basename( $atts['template'] ) . '.twig' );
		if ( ! file_exists( $f ) ) {
			error_log( "Template " . $atts['template'] . " doesn't exist", 'error_log' );

			return WP_DEBUG ? "Template " . $atts['template'] . " doesn't exist" : "";
		}
		try {
			$tpl = new Template( $f );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage(), 'error_log' );

			return WP_DEBUG ? $e->getMessage() : "";
		}

		$currentPage = get_query_var( 'page', 1 );
		$currentPage = $currentPage == 0 ? 1 : $currentPage;


		$searchCriteres = get_query_var( 'apicritere', '' ) != '' ? explode( '/', get_query_var( 'apicritere', '' ) ) : array();
		$full_query     = ( count( $searchCriteres ) > 0 ) ? array( 'apicritere' => implode( '/', $searchCriteres ) ) : array();

		$dateDebut = get_query_var( 'datedebut', '' );
		$dateFin   = get_query_var( 'datefin', '' );
		if ( $dateDebut !== '' && self::checkDateFormat( $dateDebut ) ) {
			$full_query['datedebut'] = $dateDebut;
		}
		if ( $dateFin !== '' && self::checkDateFormat( $dateFin ) ) {
			$full_query['datefin'] = $dateFin;
		}

		if ( $atts['paged'] ) {
			$urlNbPage                       = ( $currentPage > 1 ) ? $currentPage . '/' : '';
			$_SESSION['wpp_apidae_url_list'] = count( $full_query ) > 0 ? add_query_arg( $full_query, get_page_link() . $urlNbPage ) : get_page_link() . $urlNbPage;
		}

		$json = json_decode( $atts['more_json'] ) ?: array();

		$full_query = array_merge( array(
			'selectionIds' => ! empty( $atts['selection_ids'] ) ? array_map( 'trim', explode( ',', $atts['selection_ids'] ) ) : array(),
			'order'        => $atts['order'],
			'searchFields' => $atts['search_fields'],
			'asc'          => (bool) $atts['reverse_order'],
			'locales'      => array( $atts['lang'] )
		), $full_query, $json );

		$searchQuery = get_query_var( 'apisearch', '' );
		if ( ! empty( $searchQuery ) ) {
			if ( ! empty( $full_query['searchQuery'] ) ) {
				$full_query['searchQuery'] .= ' ' . $searchQuery;
			} else {
				$full_query['searchQuery'] = $searchQuery;
			}
		}


		if ( $currentPage > 1 && $atts['paged'] ) {
			$first = intval( $currentPage - 1 ) * intval( $atts['nb_result'] );
		} else {
			$first = 0;
		}


		ApidaeRequest::getList( $full_query, intval( $atts['nb_result'] ), $first );

		$tpl->render();

		return do_shortcode( $content );
	}
}