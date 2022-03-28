<?php
/**
 * Plugin Name: IDG Configuration
 * Plugin URI: https://bigbite.net
 * Description: Global configuration for 3rd party plugins used on IDG websites
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0
 */

namespace IDG\Configuration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_CONFIGURATION_DIR' ) ) {
	define( 'IDG_CONFIGURATION_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_CONFIGURATION_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );

add_filter(
	'wpcom_vip_rest_read_response_ttl',
	function( $ttl, $response, $rest_server, $request ) {
		// Cache REST API GET requests for 5 minutes.
		return MINUTE_IN_SECONDS * 5;
	},
	10,
	4
);
