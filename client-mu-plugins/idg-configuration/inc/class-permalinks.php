<?php

namespace IDG\Configuration;

/**
 * Handles any specifics around Permalink structures.
 */
class Permalinks {
	const META_ONECMS_REF = 'old_id_in_onecms';

	const HOOK_PERMALINK_STRUCTURE = 'idg_configuration_permalink_structure';

	const HOOK_PERMALINK_ID_PREFIX = 'idg_configuration_permalink_id_prefix';

	const HOOK_PERMALINK_ID_POSTFIX = 'idg_configuration_permalink_id_postfix';

	const PERMALINK_ID_PREFIX = '';

	const PERMALINK_ID_POSTFIX = '';

	/**
	 * Add all hooks required for permalink handling.
	 */
	public function __construct() {
		add_filter( 'request', [ $this, 'legacy_query_vars' ] );
		add_filter( 'request', [ $this, 'author_query_vars' ] );
		add_action( 'init', [ $this, 'hardcode_permalinks' ] );
	}

	/**
	 * Hardcodes permalinks and additional structures
	 * for custom routing across posts and taxonomies.
	 *
	 * @return void
	 */
	public function hardcode_permalinks() : void {
		global $wp_rewrite;
		// Force the permalink structure.
		$permalink_prefix    = apply_filters( self::HOOK_PERMALINK_ID_PREFIX, self::PERMALINK_ID_PREFIX );
		$permalink_postfix   = apply_filters( self::HOOK_PERMALINK_ID_POSTFIX, self::PERMALINK_ID_POSTFIX );
		$permalink_structure = apply_filters( self::HOOK_PERMALINK_STRUCTURE, "/article/${permalink_prefix}%post_id%${permalink_postfix}/%postname%.html" );
		$wp_rewrite->set_permalink_structure( $permalink_structure );
		// Author rewrite rules.
		$wp_rewrite->author_structure = 'author/%author%';
		$wp_rewrite->add_rule( 'article/([0-9]+)/([^/]+).html(?:/([0-9]+))?/?$', 'index.php?p=$matches[1]&page=$matches[3]', 'top' );
		$wp_rewrite->add_rule( 'article/([0-9]+)/?$', 'index.php?p=$matches[1]', 'top' );
		// Set an alternative for blog rewrite rules.
		$wp_rewrite->add_rule( '^column/([^/]+)/?$', 'index.php?blogs=$matches[1]', 'top' );
	}

	/**
	 * Checks that the selected post in the `p` value in
	 * query_vars and whether it is a legacy ID or part of
	 * the content hub. If either of those are true, we then
	 * assign the actual ID to ensure the correct post is loaded.
	 * This is expected to be used with the `request` hook.
	 *
	 * @param array $query_vars The current query vars.
	 * @return array
	 */
	public function legacy_query_vars( array $query_vars ) : array {
		global $is_legacy_article;

		if ( ! isset( $query_vars['p'] ) ) {
			return $query_vars;
		}

		$is_legacy_article = false;

		$query_args = [
			'meta_query' => [
				[
					'key'     => self::META_ONECMS_REF,
					'value'   => $query_vars['p'],
					'compare' => '=',
				],
			],
		];

		$query = new \WP_Query( $query_args );

		if ( $query->found_posts > 0 ) {
			$query_vars['p']   = $query->posts[0]->ID;
			$is_legacy_article = true;
		}

		return $query_vars;
	}

	/**
	 * Allow for querying of authors by author display name, login or nicename.
	 *
	 * @param array $query_vars The current query vars.
	 * @return array
	 */
	public function author_query_vars( array $query_vars ) : array {
		if ( ! isset( $query_vars['author_name'] ) ) {
			return $query_vars;
		}

		$name = str_replace( '-', ' ', $query_vars['author_name'] );

		$args = [
			'search'        => $name,
			'search_fields' => [ 'user_login', 'user_nicename', 'display_name' ],
		];
		$user = new \WP_User_Query( $args );

		if ( 1 === $user->total_users ) {
			$query_vars['author_name'] = $user->results[0]->user_login;
		}

		return $query_vars;
	}
}
