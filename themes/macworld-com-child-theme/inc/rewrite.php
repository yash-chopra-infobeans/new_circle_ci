<?php

if ( ! function_exists( 'macworld_rewrite_rules' ) ) {
	/**
	 * Sets the rewrite rules for Macworld.
	 *
	 * @param WP_Rewrite $wp_rewrite the rewrite class object.
	 * @return void
	 */
	function macworld_rewrite_rules( WP_Rewrite $wp_rewrite ) : void {
		$wp_rewrite->add_external_rule( '^apple-touch-icon-120x120-precomposed\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed-129.png' );
		$wp_rewrite->add_external_rule( '^apple-touch-icon-120x120\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed-129.png' );
		$wp_rewrite->add_external_rule( '^apple-touch-icon-152x152-precomposed\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed-144.png' );
		$wp_rewrite->add_external_rule( '^apple-touch-icon-152x152\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed-144.png' );
		$wp_rewrite->add_external_rule( '^apple-touch-icon-precomposed\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed.png' );
		$wp_rewrite->add_external_rule( '^apple-touch-icon\.png$', 'wp-content/themes/idg-base-theme/dist/static/img/apple-touch-icon-precomposed.png' );

		$wp_rewrite->add_external_rule( '^firebase-messaging-sw\.js$', 'wp-content/themes/idg-base-theme/firebase-messaging-sw.js' );
	}
}

add_action( 'generate_rewrite_rules', 'macworld_rewrite_rules' );
