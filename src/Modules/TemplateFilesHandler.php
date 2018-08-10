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

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 09/07/2018
 * Time: 17:56
 */

namespace Tofandel\Apidae\Modules;


use Tofandel\Apidae\Objects\Template;
use Tofandel\Core\Interfaces\SubModule;

class TemplateFilesHandler implements SubModule {
	use \Tofandel\Core\Traits\SubModule;

	public function actionsAndFilters() {
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

	/**
	 * Called function on plugin activation
	 */
	public function activated() {
	}

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public function upgrade( $last_version ) {
	}
}