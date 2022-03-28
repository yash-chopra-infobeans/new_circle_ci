<?php
/**
 * Plugin Name: IDG Third Party Integrations
 * Plugin URI: https://bigbite.net
 * Description: Third party integrations for IDG.
 * Author: Big Bite
 * Author URI: https://bigbite.net
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Third_Party;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_THIRD_PARTY_DIR' ) ) {
	define( 'IDG_THIRD_PARTY_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_THIRD_PARTY_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
