<?php

namespace IDG\Configuration;

/**
 * Core Plugin class.
 */
class Loader {
	const SCRIPT_NAME = 'idg-configuration-script';
	const STYLE_NAME  = 'idg-configuration-style';

	/**
	 * Add the required hooks.
	 */
	public function __construct() {
		add_filter( 'wp_is_application_passwords_available', '__return_false' );

		// Is called before `admin_enqueue_scripts` in priority order.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ], 1 );
		// phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_timeout -- Bumps limit due to publishing flow deployment processing time.
		add_filter( 'http_request_timeout', [ $this, 'http_request_timeout' ] );
		add_action( 'init', [ $this, 'remove_post_type_support' ] );
		add_filter( 'use_block_editor_for_post', [ $this, 'debug_editor' ], 10, 1 );
	}

	/**
	 * Increase timeout limit required for response time waiting.
	 * This is only required because the response for
	 * the delivery site may take longer than the default when
	 * multiple images are being used in the content.
	 *
	 * @return int
	 */
	public function http_request_timeout() {
		// @todo: Add wrapper to only set this time when going through publishing flow process.
		return 120;
	}

	/**
	 * Enqueue any required assets for the admin.
	 *
	 * @return void
	 */
	public function enqueue_assets() : void {
		$plugin_name = basename( IDG_CONFIGURATION_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_CONFIGURATION_ADMIN_JS, $plugin_dir ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-edit-post' ],
			filemtime( IDG_CONFIGURATION_DIR . '/dist/scripts/' . IDG_CONFIGURATION_ADMIN_JS ),
			false
		);

		wp_localize_script( self::SCRIPT_NAME, 'IDGConfiguration', [] );

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_CONFIGURATION_ADMIN_CSS, $plugin_dir ),
			[],
			filemtime( IDG_CONFIGURATION_DIR . '/dist/styles/' . IDG_CONFIGURATION_ADMIN_CSS )
		);
	}

	/**
	 * Removes specific features, globally, from the given post types.
	 *
	 * @return void
	 */
	public function remove_post_type_support() {
		remove_post_type_support( 'post', 'comments' );
		remove_post_type_support( 'post', 'trackbacks' );
		remove_post_type_support( 'post', 'excerpt' );

		remove_post_type_support( 'page', 'comments' );
		remove_post_type_support( 'page', 'trackbacks' );
		remove_post_type_support( 'page', 'excerpt' );
	}

	/**
	 * Allow for disabling the gutenberg editor to debug.
	 *
	 * @param boolean $block_editor Whether to use the block editor.
	 * @return boolean
	 */
	public function debug_editor( $block_editor = true ) : bool {
		$show_legacy = \filter_input( INPUT_GET, 'disable-editor', FILTER_VALIDATE_BOOLEAN );

		if ( $show_legacy ) {
			return false;
		}

		return $block_editor;
	}
}
