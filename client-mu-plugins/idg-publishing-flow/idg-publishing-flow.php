<?php
/**
 * Plugin Name: IDG Publishing Flow
 * Plugin URI: https://bigbite.net
 * Description: The starter skeleton for plugins being created on the IDG project.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Publishing_Flow;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_PUBLISHING_FLOW_DIR' ) ) {
	define( 'IDG_PUBLISHING_FLOW_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_PUBLISHING_FLOW_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup', 0 );
