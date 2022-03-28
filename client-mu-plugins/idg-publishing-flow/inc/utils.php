<?php
/**
 * Used for utility functions.
 */

/**
 * Allows for finding a specific item that has a
 * key that is equal to a value within a multidimensional array.
 *
 * @param string $needle   The value to look for.
 * @param array  $haystack The array to find the value in.
 * @param string $key      The key to check against.
 * @return array
 */
function find_in_array( $needle, $haystack, $key ) {
	return array_search( $needle, array_column( $haystack, $key ) );
}

/**
 * Uses find_in_array to check that a value exists.
 *
 * @param string $needle   The value to look for.
 * @param array  $haystack The array to find the value in.
 * @param string $key      The key to check against.
 * @return boolean
 */
function is_in_array( $needle, $haystack, $key ) {
	$haystack_key = find_in_array( $needle, $haystack, $key );

	return is_int( $haystack_key );
}

/**
 * Check whether the provided array is
 * associative or incremental.
 *
 * @param array $array Array to check.
 * @return boolean
 */
function is_associative_array( array $array ) {
	if ( [] === $array ) {
		return false;
	}
	return array_keys( $array ) !== range( 0, count( $array ) - 1 );
}

/**
 * Check whether a provided string is a
 * JSON array.
 *
 * @param string $string String to check.
 * @return boolean
 */
function is_json( string $string ) {
	json_decode( $string );
	return ( json_last_error() === JSON_ERROR_NONE );
}

/**
 * Get the value of an array item from a dot-notation key.
 *
 * @param string $key The dot notation keys.
 * @param array  $element The array to search in.
 * @return array
 */
function get_from_dot( string $key, array $element ) {
	if ( empty( $key ) ) {
		return $element;
	}

	foreach ( explode( '.', $key ) as $segment ) {
		if ( ! isset( $element[ $segment ] ) ) {
			return [];
		}

		$element = &$element[ $segment ];
	}

	return $element;
}

/**
 * Groups an array by the given key. If a sub key
 * is passed in the third param it will attempt to
 * return the value of this item.
 *
 * @param string $key The key to group by.
 * @param array  $elements The values to be grouped.
 * @param string $sub_key The subkey to get the value of.
 * @return array
 */
function group_by_key( string $key, array $elements, string $sub_key = '' ) {
	$values = [];

	foreach ( $elements as $element ) {
		$values[ $element[ $key ] ][] = get_from_dot( $sub_key, $element );
	}

	return $values;
}

function idg_get_publication_by_id( $publication_id ) {
	$args = [
		'taxonomy'     => \IDG\Publishing_Flow\Sites::TAXONOMY,
		'hide_empty'   => false,
		'hierarchical' => false,
	];

	if ( \IDG\Publishing_Flow\Sites::is_origin() ) {
		$args['object_ids'] = $publication_id;
	} else {
		$args['meta_query'] = [
			[
				'key'     => 'content_hub_id',
				'value'   => $publication_id,
				'compare' => '=',
			],
		];
	}

	$existing = get_terms( $args );

	if ( empty( $existing ) || is_wp_error( $existing ) ) {
		return null;
	}

	return $existing[0];
}

/**
 * Get a structured array of sites/publications stored.
 *
 * @param boolean $get_all Whether to get all or limit by user BU.
 * @return array
 */
function idg_get_publications( bool $get_all = false ) {
	return \IDG\Publishing_Flow\Sites::get_sites_list( $get_all );
}

/**
 * Get a structured array of business units stored.
 *
 * @param boolean $get_all Whether to get all or limit by user BU.
 * @return array
 */
function idg_get_business_units( bool $get_all = false ) {
	return \IDG\Publishing_Flow\Sites::get_business_units_list( $get_all );
}

/**
 * Set post post_modified and post_modified_gmt if there set within $postarr.
 *
 * @param array $data An array of slashed, sanitized, and processed post data.
 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
 * @return array
 */
function idg_alter_post_modification_time( $data, $postarr ) {
	if ( ! isset( $postarr['post_modified'] ) || ! isset( $postarr['post_modified_gmt'] ) ) {
		return $data;
	}

	$data['post_modified']     = $postarr['post_modified'];
	$data['post_modified_gmt'] = $postarr['post_modified_gmt'];

	return $data;
}
