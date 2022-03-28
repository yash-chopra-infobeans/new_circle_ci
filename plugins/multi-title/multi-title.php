<?php
/**
 * Plugin Name: Multi-title
 * Plugin URI: https://bigbite.net
 * Description: Adds the ability to customise the article title depending on where it's used.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.1.6
 * Text Domain: bigbite
 * Domain Path: /languages
 */
namespace BigBite\MultiTitle;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MULTI_TITLE_PLUGIN_DIR' ) ) {
	define( 'MULTI_TITLE_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once MULTI_TITLE_PLUGIN_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );