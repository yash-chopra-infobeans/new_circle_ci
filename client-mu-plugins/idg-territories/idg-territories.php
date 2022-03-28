<?php
/**
 * Plugin Name: IDG Territories Plugin
 * Plugin URI: https://bigbite.net
 * Description: The territories plugin for IDG.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Territories;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_TERRITORIES_DIR' ) ) {
	define( 'IDG_TERRITORIES_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_TERRITORIES_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
