<?php

namespace IDG\Territories;

use IDG\Territories\Territory;
use IDG\Publishing_Flow\Sites;
use IDG\Territories\Territory_Taxonomy;

/**
 * Retrieve territory data.
 */
class Territory_Loader {
	const CACHE_GROUP = 'territory';

	/**
	 * Add actions.
	 */
	public function __construct() {
		wp_cache_add_non_persistent_groups( [ self::CACHE_GROUP ] );
	}

	/**
	 * Get all territory terms.
	 *
	 * @return array
	 */
	public static function territory_terms() : array {
		$cached_terms = wp_cache_get( 'terms', self::CACHE_GROUP );

		if ( $cached_terms ) {
			return $cached_terms;
		}

		$terms = get_terms(
			[
				'taxonomy'   => 'territory',
				'hide_empty' => false,
			]
		);

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		wp_cache_set( 'terms', $terms, self::CACHE_GROUP );

		return $terms;
	}

	/**
	 * Get a single territory term.
	 *
	 * @param string $country_code - The country code.
	 * @return WP_Term|null
	 */
	public static function territory_term( string $country_code ) {
		$cached_term = wp_cache_get( 'term_' . $country_code, self::CACHE_GROUP );

		if ( $cached_term ) {
			return $cached_term;
		}

		$term = get_term_by( 'slug', $country_code, 'territory' );

		if ( is_wp_error( $term ) ) {
			return null;
		}

		wp_cache_set( 'term_' . $country_code, $term, self::CACHE_GROUP );

		return $term;
	}

	/**
	 * Get all territories
	 *
	 * @return array<IDG\Territories\Territory>
	 */
	public static function territories() : array {
		$territories = self::territory_terms();

		return array_map(
			function( $term ) {
				return self::territory( $term );
			},
			$territories
		);
	}

	/**
	 * Get a territory.
	 *
	 * @param object $term - The term to retrieve the territory for.
	 * @return IDG\Territories\Territory
	 */
	public static function territory( $term ) : Territory {
		return new Territory( $term );
	}

	/**
	 * Fetch and cache territory terms from Origin.
	 *
	 * @return array Territory terms.
	 */
	public static function get_cached_territories_from_origin() {

		$cached_terms = wp_cache_get( 'terms_origin', self::CACHE_GROUP );

		if ( ! empty( $cached_terms ) && is_array( $cached_terms ) ) {
			return $cached_terms;
		}

		$origin_url = Sites::get_origin_url();

		if ( empty( $origin_url ) ) {
			return [];
		}

		$request_url = sprintf(
			'https://%s/wp-json/wp/v2/%s',
			$origin_url,
			Territory_Taxonomy::TAXONOMY_SLUG
		);

		$response = wp_safe_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$fetched_territories = wp_remote_retrieve_body( $response );
		$fetched_territories = json_decode( $fetched_territories );

		if ( empty( $fetched_territories ) || ! is_array( $fetched_territories ) ) {
			return [];
		}

		wp_cache_set( 'terms_origin', $fetched_territories, self::CACHE_GROUP );

		return $fetched_territories;
	}
}
