<?php

namespace IDG\Configuration;

/**
 * Handles registration and meta data.
 */
class Updated {
	const META_UPDATED_DATE = '_idg_updated_date';

	/**
	 * Instatiate the class and register hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_updated_date' ] );
	}

	/**
	 * Register the updated flag meta.
	 *
	 * @return void
	 */
	public function register_updated_date() : void {
		register_meta(
			'post',
			self::META_UPDATED_DATE,
			[
				'type'          => 'string',
				'single'        => true,
				'description'   => 'The updated date of an article.',
				'default'       => '',
				'show_in_rest'  => true,
				'auth_callback' => '__return_true',
			]
		);
	}
}
