<?php

namespace IDG\Configuration;

/**
 * Runs the plugin setup sequence.
 *
 * @return void
 */
function setup() {
	// START WORKAROUNDS - SEE ./workarounds.php FOR MORE INFORMATION.
	add_action( 'admin_init', 'idg_wp_posts_increment_padding' );
	add_filter( 'pre_wp_unique_post_slug', 'idg_bypass_page_slug_uniqueness', 10, 5 );
	// END WORKAROUNDS.

	new Loader();
	new Legacy();
	new Permalinks();
	new Plugins\Publishing_Flow\Images();
	new Plugins\Publishing_Flow\Featured_Video();
	new Plugins\Publishing_Flow\Meta();
	new Plugins\Publishing_Flow\Taxonomies();
	new Plugins\Bugsnag();
	new Plugins\Query_Monitor();
	new Plugins\Jetpack\Sitemaps();
	new Image();
	new Meta();
	new Structured_Data();
	new Updated();
}
