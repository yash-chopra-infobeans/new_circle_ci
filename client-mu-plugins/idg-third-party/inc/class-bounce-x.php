<?php

namespace IDG\Third_Party;

/**
 * Bounce X intergration.
 * An ad slot is also defined for bounce x in inc/gpt.
 */
class Bounce_X {
	const QUERY_VAR = 'bx_iframe_buster';

	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'redirect' ], 10 );
		add_action( 'query_vars', [ $this, 'query_var' ] );
		add_action( 'parse_request', [ $this, 'request' ], 10, 1 );
	}

	/**
	 * Add redirect rule.
	 *
	 * @return void
	 */
	public function redirect() {
		global $wp_rewrite;
		$query_var = self::QUERY_VAR;
		$wp_rewrite->add_rule( '^BXiframebuster\.html$', "index.php?{$query_var}=true", 'top' );
	}

	/**
	 * Add bouncex query var
	 *
	 * @param array $public_query_vars - Current query vars.
	 * @return array
	 */
	public function query_var( $public_query_vars ) {
		$public_query_vars[] = self::QUERY_VAR;
		return $public_query_vars;
	}

	/**
	 * Render iframe buster
	 *
	 * @param object $wp - The wp object.
	 * @return void
	 */
	public function request( $wp ) {
		if ( isset( $wp->query_vars[ self::QUERY_VAR ] ) && 'true' === $wp->query_vars[ self::QUERY_VAR ] ) {
			header( 'Content-Type: text/html' );
			// phpcs:ignore
			echo file_get_contents( IDG_THIRD_PARTY_DIR . '/inc/templates/bouncex-iframe-buster.html' );
			exit;
		}
	}
}
