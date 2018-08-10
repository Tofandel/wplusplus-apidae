<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 09/07/2018
 * Time: 18:08
 */

namespace Tofandel\Apidae\Objects;


class ApidaeRequest {
	public static function getSelections() {
		global $tofandel_apidae;

		$query['projetId'] = $tofandel_apidae['project_id'];
		$query['apiKey']   = $tofandel_apidae['api_key'];
		$query             = array( 'query' => json_encode( $query ) );
		$url               = 'https://api.apidae-tourisme.com/api/v002/referentiel/selections/?' . http_build_query( $query );
		$md                = md5( $url );
		$cache             = self::getCache( $md );
		$isValid           = true;
		if ( $cache === false ) {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$rep     = curl_exec( $ch );
			$isValid = ! curl_errno( $ch );
			curl_close( $ch );
			$rep = json_decode( $rep, true );
		} else {
			$rep = $cache;
		}
		if ( $isValid === true ) {
			if ( is_array( $rep ) ) {
				if ( $cache === false ) {
					//We cache this for 30 minutes only
					self::setCache( $md, $rep, 30 );
				}

				return $rep;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	/**
	 * retourne le détail d'un objet touristique
	 *
	 * @param int $id identifiant de l'objet
	 * @param array|false $query
	 *
	 * @return array|false
	 */
	public static function getSingleObject( $id, $query ) {
		global $tofandel_apidae;

		$query['projetId'] = $tofandel_apidae['project_id'];
		$query['apiKey']   = $tofandel_apidae['api_key'];

		$default_fields = '@all';
		if ( empty( $query['responseFields'] ) ) {
			$query['responseFields'] = $default_fields;
		}

		$query = apply_filters( 'apidae_single_request_query', $query );
		foreach ( $query as $key => $querum ) {
			if ( is_array( $querum ) ) {
				//Even if the doc says otherwise the api doesn't take this argument as an array because we send them in GET
				$query[ $key ] = implode( ',', $querum );
			}
		}

		$url     = sprintf( 'https://api.apidae-tourisme.com/api/v002/objet-touristique/get-by-id/%d/?', $id ) . http_build_query( $query );
		$md      = md5( $url );
		$cache   = self::getCache( $md );
		$isValid = true;
		if ( $cache === false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$rep     = curl_exec( $ch );
			$isValid = ! curl_errno( $ch );
			curl_close( $ch );
			$rep = json_decode( $rep, true );
		} else {
			$rep = $cache;
		}

		if ( $isValid === true ) {
			if ( is_array( $rep ) ) {
				if ( $cache === false ) {
					if ( ! empty( $rep['errorType'] ) ) {
						$rep = false;
					}
					if ( $rep && ! empty( $query['locales'] ) ) {
						$l = explode( ',', $query['locales'] );
						//TODO check if pt-BR works
						if ( ! empty( [ $l[0] ] ) ) {
							self::setLibelle( $rep, str_replace( '-', '', ucwords( strtolower( $l[0] ) ) ) );
						}
					}
					self::setCache( $md, $rep );
				}

				return $rep;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function setLibelle( &$array, $lang ) {
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				self::setLibelle( $array[ $key ], $lang );
			} elseif ( $key == 'libelle' . $lang ) {
				$array['libelle'] = $val;
			}
		}
	}

	/**
	 * Exécute une requête de recherche Apidae
	 *
	 * @param array $query tableau de paramètres
	 * @param int $count nombre de résutats
	 * @param int $first indice du premier résultat à retourner
	 *
	 * @return array|false nombre de résultats, string json des résultats
	 */
	public static function getList( $query, $count, $first = 0 ) {
		global $tofandel_apidae;

		$def_query = array(
			'projetId' => $tofandel_apidae['project_id'],
			'apiKey'   => $tofandel_apidae['api_key'],
			'count'    => $count,
			'first'    => $first
		);

		$query     = array_merge( $query, $def_query );

		$query = apply_filters( 'apidae_list_request_query', $query );

		$json_query = array( 'query' => json_encode( $query ) );
		$url        = 'https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques?' . http_build_query( $json_query );
		$md         = md5( $url );
		$cache      = self::getCache( $md );
		$isValid    = true;
		if ( $cache === false ) {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$isValid = ! curl_errno( $ch );
			$rep     = curl_exec( $ch );
			curl_close( $ch );
			$rep = json_decode( $rep, true );
		} else {
			$rep = $cache;
		}

		if ( $isValid === true ) {
			if ( is_array( $rep ) ) {
				$rep['numFound'] = array_key_exists( 'numFound', $rep ) ? intval( $rep['numFound'] ) : 0;
				if ( $cache === false ) {
					if ( ! empty( [ $query['locales'][0] ] ) && ! empty( $rep['objetsTouristiques'] ) ) {
						self::setLibelle( $rep['objetsTouristiques'], str_replace( '-', '', ucwords( strtolower( $query['locales'][0] ) ) ) );
					}
					self::setCache( $md, $rep );
				}

				return $rep;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * récupère le contenu du cache s'il existe et s'il n'est pas expiré
	 *
	 * @param string $md
	 *
	 * @return boolean
	 */
	static public function getCache( $md ) {
		return get_transient( 'wpp_apidae_' . $md );
	}

	/**
	 * détermine le contenu d'un fichier de cache
	 *
	 * @param string $md
	 * @param string $content
	 * @param int $time To override the default time
	 *
	 * @return boolean
	 */
	static public function setCache( $md, $content, $time = 0 ) {
		global $tofandel_apidae;
		$iCache = $time ?: $tofandel_apidae['cache_duration'];
		if ( $iCache == 0 ) {
			return false;
		} else {
			return set_transient( 'wpp_apidae_' . $md, $content, $iCache * 60 );
		}
	}

	/**
	 * vide le cache des fichiers arrivés à expiration
	 */
	static public function purgeCache() {
		delete_expired_transients( true );
	}

	/**
	 * vide le cache
	 */
	static public function clearCache() {
		global $wpdb, $WPlusPlusApidae;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wpp_apidae_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wpp_apidae_' ) . '%'
		) );

		return __( 'The cache has been successfully cleared', $WPlusPlusApidae->getTextDomain() );
	}
}