<?php
/**
 * Plugin Name: IDG Post Types Filters
 * Plugin URI: https://bigbite.net
 * Description: IDG Post Types Filters
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0
 */

namespace IDG\Post_Type_Filters;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_POST_TYPE_FILTERS_DIR' ) ) {
	define( 'IDG_POST_TYPE_FILTERS_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_POST_TYPE_FILTERS_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
