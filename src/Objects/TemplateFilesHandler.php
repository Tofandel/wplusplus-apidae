<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 09/07/2018
 * Time: 17:56
 */

namespace Tofandel\Apidae\Objects;


use Tofandel\Core\Traits\StaticInitializable;

class TemplateFilesHandler {
	use StaticInitializable;

	public static function __init__() {
		add_action( 'redux/options/tofandel_apidae/saved', [ self::class, 'add_settings_change_action' ], 10, 0 );
		add_action( 'redux/options/tofandel_apidae/import', [ self::class, 'add_settings_change_action' ], 10, 0 );
		add_action( 'redux/options/tofandel_apidae/reset', [ self::class, 'delete_templates' ], 10, 0 );
	}

	public static function add_settings_change_action() {
		//Prevents the hook from running kinda all the time and clearing the cache for no reason
		add_action( 'redux/options/tofandel_apidae/settings/change', [ self::class, 'update_templates' ], 10, 2 );
	}

	const TPL_DIR = 'templates/';
	const CACHE_DIR = WP_CONTENT_DIR . '/cache/twig/';
	const LIST_DIR = 'list/';
	const DETAIL_DIR = 'detail/';

	public static function delete_templates() {
		global $WPlusPlusApidae;
		$WPlusPlusApidae->delete_dir( self::TPL_DIR . self::LIST_DIR );
		$WPlusPlusApidae->delete_dir( self::TPL_DIR . self::DETAIL_DIR );
		$WPlusPlusApidae->delete_dir( self::CACHE_DIR );
		//Prevent the template from being recreated from old values
		remove_action( 'redux/options/tofandel_apidae/settings/change', [ self::class, 'update_templates' ], 10 );
	}

	public static function update_templates( $options, $changed_values = array() ) {
		if ( empty( $changed_values ) ) {
			return;
		}
		if ( ! empty( $changed_values['list-template'] ) ) {
			self::saveTemplate( $options, 'list' );
		}
		if ( ! empty( $changed_values['detail-template'] ) ) {
			self::saveTemplate( $options, 'detail' );
		}
	}

	public static function saveTemplate( $options, $type ) {
		static $list_titles = array();

		global $WPlusPlusApidae;

		$dir = $type == 'list' ? self::TPL_DIR . self::LIST_DIR : self::TPL_DIR . self::DETAIL_DIR;

		$WPlusPlusApidae->delete_dir( $dir );
		$WPlusPlusApidae->delete_dir( self::CACHE_DIR );
		if ( ! empty( $options[ $type . '-template' ]['redux_repeater_data'] ) ) {
			foreach ( $options[ $type . '-template' ]['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options[ $type . '-template' ][ $type . '-name' ][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $list_titles ) ) {
					$i ++;
				}
				$title         = $title . $i;
				$list_titles[] = $title;

				$WPlusPlusApidae->mkdir( $dir );
				$WPlusPlusApidae->put_contents( $dir . $title . '.twig',
					$options[ $type . '-template' ][ $type . '-code' ][ $k ] );
			}
		}
	}

	public static function templateValidation( $field, $value, $existing_value ) {
		$error = false;

		if ( ! empty( $msg = Template::check( $value['list-code'][0], $value['list-name'][0] ) ) ) {
			$field['msg'] = $msg;
			$error        = true;
		}

		$return['value'] = $value;
		if ( $error == true ) {
			$return['error'] = $field;
		}

		return $return;
	}

}