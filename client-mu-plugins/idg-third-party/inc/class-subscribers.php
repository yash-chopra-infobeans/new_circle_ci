<?php

namespace IDG\Third_Party;

/**
 * Subscribers integration.
 */
class Subscribers {
	const SCRIPT_NAME = 'subscribers';

	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'add_subscribers' ] );
	}

	/**
	 * Add subscribers scripts.
	 *
	 * @return void
	 */
	public function add_subscribers() {
		$config = Settings::get( 'subscribers' )['config'];

		if ( ! $config || empty( $config ) ) {
			return;
		}

		// phpcs:ignore
		wp_enqueue_script( self::SCRIPT_NAME, $config['script'], [], false, true );

		wp_add_inline_script(
			self::SCRIPT_NAME,
			"var subscribersSiteId = '{$config['id']}';",
			'before'
		);

		wp_register_script( 'subscribers-firebase', false, [], 1, true );
		wp_enqueue_script( 'subscribers-firebase' );
		wp_add_inline_script( 'subscribers-firebase', 'var version = "1.5.1"; importScripts("https://cdn.subscribers.com/assets/subscribers-sw.js");' );
	}
}
