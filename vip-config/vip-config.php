<?php
/**
 * Hi there, VIP dev!
 *
 * This vip-config.php is where you put things you'd usually put in wp-config.php. Don't worry about database settings
 * and such, we've taken care of that for you. This is just for if you need to define an API key or something
 * of that nature.
 *
 * WARNING: This file is loaded very early (immediately after `wp-config.php`), which means that most WordPress APIs,
 *   classes, and functions are not available. The code below should be limited to pure PHP.
 *
 * @see https://vip.wordpress.com/documentation/vip-go/understanding-your-vip-go-codebase/
 *
 * Happy Coding!
 *
 * - The WordPress.com VIP Team
 * 
 * @package IDG
 **/

if ( ! defined( 'VIP_GO_APP_ENVIRONMENT' ) ) {
	define( 'VIP_GO_APP_ENVIRONMENT', 'local' );
}

// VIP: Bump cron object cache buckets to 10.
// Each bucket holds 0.95 Mb of data.
if ( ! defined( 'CRON_CONTROL_MAX_CACHE_BUCKETS' ) ) {
	define( 'CRON_CONTROL_MAX_CACHE_BUCKETS', 10 );
}

// Set a high default limit to avoid too many revisions from polluting the database.
// Posts with extremely high revisions can result in fatal errors or have performance issues.
// Feel free to adjust this depending on your use cases.
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 500 );
}

if ( ! defined( 'VIP_GO_DISABLE_RAMP' ) && ( ! defined( 'WP_CLI' ) || ! WP_CLI ) ) {
	define( 'VIP_GO_DISABLE_RAMP', true );
}

define( 'PUBLISHING_FLOW_ENTRY_ORIGIN_HEADER', 'X-IDG-Entry-Origin' ); // Origin Header - can reuse this.

/**
 * Disable scripts concatenation.
 */
define( 'CONCATENATE_SCRIPTS', false );

if ( file_exists( __DIR__ . '/env-' . VIP_GO_APP_ENVIRONMENT . '-config.php' ) ) {
	require_once __DIR__ . '/env-' . VIP_GO_APP_ENVIRONMENT . '-config.php';
}

/**
 * To disallow access.
 */
if ( file_exists( __DIR__ . '/class-blocked-access.php' ) ) {
	require_once __DIR__ . '/class-blocked-access.php';
}

$configuration_file = ABSPATH . 'wp-content/client-mu-plugins/idg-configuration/vendor/autoload_packages.php';

if ( file_exists( $configuration_file ) ) {
	require_once $configuration_file;
}

if ( file_exists( __DIR__ . '/redirects.php' ) ) {
	require_once __DIR__ . '/redirects.php';
}
