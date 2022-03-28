<?php

namespace IDG\Base_Theme\Utils;

/**
 * Map by key
 *
 * @param string $key - The key.
 * @param array  $array - The array.
 *
 * @return array
 */
function map_by_key( string $key, array $array ) : array {
	return array_map(
		function( $item ) use ( $key ) {
			return $item->{$key};
		},
		$array
	);
}

/**
 * Determine how many days have elapsed from a date..
 *
 * @param string $date the file name.
 * @return string
 */
function days_since( string $date = null ) : string {
	if ( ! $date ) {
		return '';
	}

	return date_diff( date_create( $date ), date_create() )->format( '%a' );
}

/**
 * Determine if the post content has a block.
 *
 * @param string $block_name - The block name to find.
 * @param string $post_content - The post content.
 * @return bool
 */
function has_block( string $block_name = '', string $post_content = null ) : bool {
	if ( ! $post_content ) {
		return false;
	}

	$blocks = parse_blocks( $post_content );

	$key = array_search( $block_name, array_column( $blocks, 'blockName' ), true );

	if ( ! $key ) {
		return false;
	}

	return true;
}

/**
 * Determine if Web, Amp or FB Instant Article
 *
 * @return string
 */
function get_platform() : string {
	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		return 'amp';
	}

	// @TODO Facebook instant article?

	return 'web';
}

/**
 * Determine if current request is for AMP or not.
 *
 * @return bool
 */
function is_amp() : bool {
	return 'amp' === get_platform();
}

/**
 * Get ID's of all sponsored posts.
 *
 * @return array of ID's of posts that have a sponsorship attached.
 */
function get_sponsored_posts() {

	$sponsorships = get_terms(
		[
			'taxonomy'   => 'sponsorships',
			'hide_empty' => false,
		]
	);

	$sponsored_posts = idg_wp_query_cache(
		[
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_type'      => 'post',
			'post_status'    => [ 'publish', 'updated' ],
			'tax_query'      => [
				[
					'taxonomy' => 'sponsorships',
					'field'    => 'slug',
					'terms'    => wp_list_pluck( $sponsorships, 'slug' ),
				],
			],
		],
		'idg_sponsored_posts'
	);

	$sponsored_posts_ids = $sponsored_posts->posts;

	return $sponsored_posts_ids;
}

/**
 * Get page canonical URL, fallback to permalink.
 *
 * @return string
 */
function idg_get_canonical_url() : string {
	$url = get_the_permalink();

	if ( ! is_single() && ! is_page() ) {
		return $url;
	}

	$post_id    = get_the_ID();
	$multititle = json_decode( get_post_meta( $post_id, 'multi_title', true ), true );

	if (
		isset( $multititle['titles']['seo'] )
		&& isset( $multititle['titles']['seo']['additional']['seo_canonical_url'] )
	) {
		$url = $multititle['titles']['seo']['additional']['seo_canonical_url'];
	}

	return $url;
}

/**
 * Array -> HTML Attributes
 *
 * @param array $array - The array.
 * @return string
 */
function array_to_attrs( $array ) {
	$attributes = array_map(
		function( $value, $key ) {
			return $key . '="' . $value . '"';
		},
		array_values( $array ),
		array_keys( $array )
	);

	return implode( ' ', $attributes );
}
