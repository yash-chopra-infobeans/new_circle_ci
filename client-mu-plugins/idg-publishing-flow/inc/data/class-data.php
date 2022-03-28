<?php

namespace IDG\Publishing_Flow\Data;

/**
 * Base handler for additional data, such as meta
 * and taxonomies.
 */
class Data {
	/**
	 * Ensures that the given keys are preserved in their
	 * entirity within the given array whilst stripping out
	 * the remainder.
	 *
	 * @param array $data The data to process.
	 * @param array $keys The keys to preserve.
	 * @return array
	 */
	public function preserve_keys( array $data, array $keys ) : array {
		$retained = array_filter(
			$data,
			function( $key ) use ( $keys ) {
				return in_array( $key, $keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		return $retained;
	}

	/**
	 * The opposite of preserve_keys - will strip
	 * out the given keys whilst preserving the
	 * remainder.
	 *
	 * @param array $data The data to process.
	 * @param array $keys The keys to strip out.
	 * @return array
	 */
	public function strip_keys( array $data, array $keys ) : array {
		$stripped = array_filter(
			$data,
			function( $key ) use ( $keys ) {
				return ! in_array( $key, $keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		return $stripped;
	}

	/**
	 * Inserts an entire list of meta with the attached post.
	 *
	 * @param integer $post_id The post id.
	 * @param array   $meta The meta to be attached.
	 * @return void
	 */
	public function insert_meta( int $post_id, array $meta ) : void {
		$unserialize = [
			'_wp_attachment_metadata',
		];

		foreach ( $meta as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = $value[0];
			}

			if ( in_array( $key, $unserialize, true ) ) {
				/**
				 * Unavoidable usage as passed data is always
				 * serialized due to the entry being passed from
				 * the api.
				 */
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$value = unserialize( $value );
			}

			update_post_meta( $post_id, $key, $value );
		}
	}
}
