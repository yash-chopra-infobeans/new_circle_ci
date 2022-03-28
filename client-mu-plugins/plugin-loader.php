<?php
/*
 * We recommend all plugins for your site are
 * loaded in code, either from a file like this
 * one or from your theme (if the plugins are
 * specific to your theme and do not need to be
 * loaded as early as this in the WordPress boot
 * sequence.
 *
 * @see https://vip.wordpress.com/documentation/vip-go/understanding-your-vip-go-codebase/
 */

/**
 * Note the above requires a specific naming structure: /plugin-name/plugin-name.php
 * You can also specify a specific root file: wpcom_vip_load_plugin( 'plugin-name/plugin.php' );
 *
 * The wpcom_vip_load_plugin only loads plugins from the `WP_PLUGIN_DIR` directory.
 * For client-mu-plugins `require __DIR__ . '/plugin-name/plugin-name.php'` works.
 */
wpcom_vip_load_plugin( 'custom-fields' );
wpcom_vip_load_plugin( 'multi-title' );
wpcom_vip_load_plugin( 'oauth2' );
wpcom_vip_load_plugin( 'oauth-route-filter/plugin.php' );
wpcom_vip_load_plugin( 'safe-svg' );
wpcom_vip_load_plugin( 'ads-txt' );
wpcom_vip_load_plugin( 'amp' );

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	wpcom_vip_load_plugin( 'bugsnag' );
}

require_once __DIR__ . '/idg-migration-images/idg-migration-images.php';
require_once __DIR__ . '/idg-asset-manager/idg-asset-manager.php';
require_once __DIR__ . '/idg-publishing-flow/idg-publishing-flow.php';
require_once __DIR__ . '/idg-territories/idg-territories.php';
require_once __DIR__ . '/idg-products/idg-products.php';
require_once __DIR__ . '/idg-configuration/idg-configuration.php';
require_once __DIR__ . '/idg-post-type-filters/idg-post-type-filters.php';
require_once __DIR__ . '/idg-golden-taxonomy/idg-golden-taxonomy.php';
require_once __DIR__ . '/idg-sponsored-links/idg-sponsored-links.php';
require_once __DIR__ . '/idg-third-party/idg-third-party.php';
