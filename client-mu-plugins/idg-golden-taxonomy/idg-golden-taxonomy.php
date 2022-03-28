<?php
/**
 * Plugin Name: IDG Golden taxonomy
 * Plugin URI: https://rtcamp.com
 * Description: The plugin to sync golden taxonomies.
 * Author: rtCamp
 * Author URI: https://rtcamp.com
 * Version: 1.0.0-alpha.1
 */

namespace IDG\Golden_Taxonomy;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IDG_GOLDEN_TAXONOMY_DIR' ) ) {
	define( 'IDG_GOLDEN_TAXONOMY_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
}

require_once IDG_GOLDEN_TAXONOMY_DIR . '/vendor/autoload_packages.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
