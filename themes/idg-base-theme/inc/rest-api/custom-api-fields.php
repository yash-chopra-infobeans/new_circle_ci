<?php

add_action( 'rest_api_init', 'idg_base_theme_register_api_hooks' );
if ( ! function_exists( 'idg_base_theme_register_api_hooks' ) ) {
	/**
	 * Registers new rest field for eyebrows.
	 */
	function idg_base_theme_register_api_hooks() {
		register_rest_field(
			'post',
			'eyebrow',
			[
				'get_callback' => 'idg_base_theme_return_eyebrow',
				'schema'       => null,
			]
		);
		register_rest_field(
			'post',
			'review_score',
			[
				'get_callback' => 'idg_base_theme_return_score',
				'schema'       => null,
			]
		);
	}
}

/**
 * Callback function to return an eyebrow to the rest field.
 *
 * @param object $object Current post object.
 */
function idg_base_theme_return_eyebrow( $object ) {
	return idg_base_theme_get_eyebrow( $object['id'] );
}

/**
 * Callback function to return a review score to the rest field.
 *
 * @param object $object Current post object.
 */
function idg_base_theme_return_score( $object ) {
	return idg_base_theme_review_score( $object['id'] );
}
