<?php
/**
 * Plugin Name: Custom Fields
 * Plugin URI: https://bigbite.net
 * Description: Add custom fields to the post editor and settings page.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace Custom_Fields;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CUSTOM_FIELDS_DIR' ) ) {
	define( 'CUSTOM_FIELDS_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once CUSTOM_FIELDS_DIR . '/vendor/autoload_packages.php';

