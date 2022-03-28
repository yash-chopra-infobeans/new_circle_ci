<?php
/**
 * Plugin Name: Cypress Test Setup
 * Plugin URI: https://bigbite.net
 * Description: Adds a custom Multi-title setup so we can test validation etc.
 * Author: BigBite
 * Author URI: https://bigbite.net
 * Version: 1.0
 * Text Domain: multi-title
 * Domain Path: /languages
 */

function enqueue_assets() {
	wp_enqueue_script(
		'multi-title-test-script',
		plugins_url( '/cypress-plugin/tab-setup.js', dirname( __FILE__ ) ),
		[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-editor' ],
		filemtime( rtrim( plugin_dir_path( __FILE__ ), '/' ) . '/tab-setup.js' ),
		false
	);
}

add_action( 'enqueue_block_editor_assets', 'enqueue_assets' );

function register_multi_title_meta() {
	$args = array(
		'auth_callback' => 'is_user_logged_in',
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
	);

	register_meta( 'post', 'multi_title_seokeywords', $args );
}

add_action( 'init', 'register_multi_title_meta' );
