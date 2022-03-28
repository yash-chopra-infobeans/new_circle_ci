<?php
/**
 * Plugin Name: IDG Asset Manager
 * Plugin URI: https://bigbite.net
 * Description: Advanced media library with cropping and simple editing features.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Asset_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_ASSET_MANAGER_DIR' ) ) {
	define( 'IDG_ASSET_MANAGER_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_ASSET_MANAGER_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
