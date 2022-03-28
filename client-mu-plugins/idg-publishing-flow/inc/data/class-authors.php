<?php

namespace IDG\Publishing_Flow\Data;

/**
 * Handles any Author related methods.
 */
class Authors extends Data {
	const HOOK_NO_AUTHORS = 'idg_publishing_flow_no_authors';

	const HOOK_AUTHOR_PAYLOAD = 'idg_publishing_flow_author_payload';

	const FILTER_ALLOWED_META = 'idg_publishing_flow_allowed_author_meta';

	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Formats the author data object to be compatible with
	 * what is expected for article deployment.
	 *
	 * @param int|string $author_id The author to retrieve and format.
	 * @return array
	 */
	public function format( $author_id ) : array {
		$author = get_user_by( 'ID', $author_id );

		if ( ! $author || 0 === $author_id ) {
			return [
				apply_filters(
					self::HOOK_NO_AUTHORS,
					[
						'login'        => 'no-author',
						'display_name' => 'No Author',
						'email'        => 'noauthor@idg.net',
						'first_name'   => 'No',
						'last_name'    => 'Author',
					]
				),
			];
		}

		$author_email        = get_the_author_meta( 'user_email', $author_id );
		$author_display_name = get_the_author_meta( 'display_name', $author_id );
		$author_first_name   = get_the_author_meta( 'first_name', $author_id );
		$author_last_name    = get_the_author_meta( 'last_name', $author_id );

		$author_meta = get_user_meta( $author_id );

		$allowed_meta = apply_filters(
			self::FILTER_ALLOWED_META,
			[
				'nickname',
				'description',
			]
		);

		$updated_meta = [];

		foreach ( $allowed_meta as $pattern ) {
			$found_keys   = array_flip( preg_grep( "/^$pattern$/", array_keys( $author_meta ) ) );
			$additional   = array_intersect_key( $author_meta, $found_keys );
			$updated_meta = array_merge( $updated_meta, $additional );
		}

		$updated_meta = array_map(
			function( $value ) {
				return $value[0];
			},
			$updated_meta
		);

		$author = [
			'login'        => $author->user_login,
			'display_name' => $author_display_name,
			'email'        => $author_email,
			'first_name'   => $author_first_name,
			'last_name'    => $author_last_name,
			'meta'         => $updated_meta,
		];
		$author = apply_filters( self::HOOK_AUTHOR_PAYLOAD, $author, intval( $author_id ) );

		return [ $author ];
	}
}
