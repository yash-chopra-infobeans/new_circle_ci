<?php
/**
 * Plugin Name: IDG Products Plugin
 * Plugin URI: https://bigbite.net
 * Description: The products plugin for IDG.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Products;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_PRODUCTS_DIR' ) ) {
	define( 'IDG_PRODUCTS_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_PRODUCTS_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
