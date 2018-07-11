<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 09/07/2018
 * Time: 18:08
 */

namespace Tofandel\Apidae\Objects;


class ApidaeRequest {
	/**
	 * retourne le détail d'un objet touristique
	 *
	 * @param int $id identifiant de l'objet
	 * @param array $fields champs retournés
	 * @param string $locale langues demandées
	 *
	 * @return array|false
	 */
	public static function getSingleObject( $id, $fields, $locale ) {
		global $tofandel_apidae;

		$query = array( 'projetId' => $tofandel_apidae['project_id'], 'apiKey' => $tofandel_apidae['api_key'] );
		if ( $fields != '' ) {
			$query['responseFields'] = $fields;
		}
		$query['locales'] = $locale;
		$url              = sprintf( 'https://api.apidae-tourisme.com/api/v002/objet-touristique/get-by-id/%d/?', $id ) . http_build_query( $query );
		$md               = md5( $url );
		$cache            = self::getCache( $md );
		$isValid          = true;
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
	 * retourne une chaine de caractère aléatoire de 8 caractères de longueur dans les chiffres et lettres minuscules
	 * @return string
	 */
	public static function genRandomSeed() {
		$sAR  = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$sRet = '';
		for ( $i = 0; $i < 9; $i ++ ) {
			$sRet .= $sAR[ rand( 0, 35 ) ];
		}

		return $sRet;
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

		if ( array_key_exists( 'order', $query ) && $query['order'] === 'RANDOM' ) {
			if ( array_key_exists( 'WP84randomSeed', $_SESSION ) ) {
				$query['randomSeed'] = $_SESSION['WP84randomSeed'];
			} else {
				$seed                       = self::genRandomSeed();
				$_SESSION['WP84randomSeed'] = $seed;
				$query['randomSeed']        = $seed;
			}
		}
		$query   = array( 'query' => json_encode( $query ) );
		$url     = 'https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques?' . http_build_query( $query );
		$md      = md5( $url );
		$cache   = self::getCache( $md );
		$isValid = true;
		if ( $cache === false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
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
				if ( $cache === false ) {
					self::setCache( $md, $rep );
				}
				$rep['numFound'] = array_key_exists( 'numFound', $rep ) ? intval( $rep['numFound'] ) : 0;

				//$nbPages= $numFound>0?ceil($numFound/$cnt):0;
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
	 *
	 * @return boolean
	 */
	static public function setCache( $md, $content ) {
		global $tofandel_apidae;
		$iCache = $tofandel_apidae['cache_duration'];
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
	static public function emptyCache() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wpp_apidae_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wpp_apidae_' ) . '%'
		) );
	}
}