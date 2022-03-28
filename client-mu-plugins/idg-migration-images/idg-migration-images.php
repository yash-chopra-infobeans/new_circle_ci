<?php
/**
 * Plugin Name: IDG Configuration
 * Plugin URI: https://bigbite.net
 * Description: Global configuration for 3rd party plugins used on IDG websites
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0
 */

namespace IDG\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_MIGRATION_DIR' ) ) {
	define( 'IDG_MIGRATION_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_MIGRATION_DIR . '/vendor/autoload.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
