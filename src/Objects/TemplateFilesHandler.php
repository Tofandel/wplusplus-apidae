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
		add_action( 'redux/options/tofandel_apidae/saved', [ self::class, 'update_templates' ], 10, 2 );
		add_action( 'redux/options/tofandel_apidae/import', [ self::class, 'update_templates' ], 10, 2 );
		add_action( 'redux/options/tofandel_apidae/reset', [ self::class, 'delete_templates' ], 10, 0 );
	}

	const CACHE_FOLDER = WP_CONTENT_DIR . '/cache/twig';

	public static function delete_templates() {
		global $WPlusPlusApidae;
		$WPlusPlusApidae->deleteFolder( '/templates/list' );
		$WPlusPlusApidae->deleteFolder( '/templates/detail' );
		$WPlusPlusApidae->deleteFolder( self::CACHE_FOLDER );
	}

	public static function update_templates( $options, $changed_values = array() ) {
		if ( empty( $changed_values ) ) {
			return;
		}
		if ( is_a( $options, \reduxCorePanel::class ) ) {
			$options = $changed_values;
		}
		global $WPlusPlusApidae;

		$list_titles   = array();
		$detail_titles = array();

		if ( ! empty( $changed_values['list-template'] ) ) {
			$WPlusPlusApidae->deleteFolder( '/templates/list' );
			$WPlusPlusApidae->deleteFolder( self::CACHE_FOLDER );
			foreach ( $options['list-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['list-template']['list-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $list_titles ) ) {
					$i ++;
				}
				$title         = $title . $i;
				$list_titles[] = $title;
				$WPlusPlusApidae->mkdir( '/templates/list' );
				$WPlusPlusApidae->put_contents( '/templates/list/' . $title . '.twig', $options['list-template']['list-code'][ $k ] );
			}
		}
		if ( ! empty( $changed_values['detail-template'] ) ) {
			$WPlusPlusApidae->deleteFolder( '/templates/detail' );
			$WPlusPlusApidae->deleteFolder( self::CACHE_FOLDER );
			foreach ( $options['detail-template']['redux_repeater_data'] as $k => $data ) {
				$title = wpp_slugify( $options['detail-template']['detail-name'][ $k ] );
				$i     = '';
				while ( in_array( $title . $i, $detail_titles ) ) {
					$i ++;
				}
				$title           = $title . $i;
				$detail_titles[] = $title;
				$WPlusPlusApidae->mkdir( '/templates/detail' );
				$WPlusPlusApidae->put_contents( '/templates/detail/' . $title . '.twig', $options['detail-template']['detail-code'][ $k ] );
			}
		}
	}

}