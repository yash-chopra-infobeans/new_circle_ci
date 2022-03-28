<?php

namespace IDG\Configuration;

/**
 * Handles registration and meta data.
 */
class Meta {
	const META_UPDATED_FLAG = '_idg_updated_flag';

	/**
	 * Instatiate the class and register hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_updated_flag' ] );
	}

	/**
	 * Register the updated flag meta.
	 *
	 * @return void
	 */
	public function register_updated_flag() : void {
		register_meta(
			'post',
			self::META_UPDATED_FLAG,
			[
				'description'   => 'String value for whether post is updated.',
				'default'       => false,
				'type'          => 'boolean',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => 'is_user_logged_in',
			]
		);
	}
}
