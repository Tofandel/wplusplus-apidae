<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 03/07/2018
 * Time: 16:06
 */

namespace Tofandel\Apidae\Shortcodes;


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

		static::$vc_params = array(
			'category'              => esc_html__('Apidae', $WPlusPlusApidae->text_domain),
			'description'             => __( 'Shortcode to create an apidae list', $WPlusPlusApidae->text_domain),
			'name'   => __( 'Apidae List', $WPlusPlusApidae->text_domain ),
			'params' => array(
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__( 'Template', $WPlusPlusApidae->text_domain ),
					'param_name' => 'template',
					'value'      => array(
					),
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
	 * @param array $atts
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name ) {


		return do_shortcode( $content );
	}
}