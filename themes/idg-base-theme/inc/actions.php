<?php
/**
 * Clears `get_sponsored_posts` cache on post save.
 *
 * @package idg-base-theme
 */

add_action(
	'save_post',
	function() {
		wp_cache_delete( 'get_sponsored_posts', 'idg-base-theme' );
	},
	10,
	3
);

if ( ! function_exists( 'idg_base_theme_disable_native_search' ) ) {
	/**
	 * Checks whether the current query is a search, and if so, disable it
	 * before the query can be output.
	 *
	 * @param WP_Query $query The query currently being performed by WordPress.
	 * @param boolean  $error Whether the current query is erroring.
	 * @return void
	 */
	function idg_base_theme_disable_native_search( $query, $error = true ) { //phpcs:ignore -- $error never used.
		// Allow native search to be performed in the Admin and via REST.
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( is_search() ) {
			header( 'HTTP/1.0 404 Not Found' );

			$query->is_search       = false;
			$query->query_vars['s'] = false;
			$query->query['s']      = false;

			$query->is_404 = true;
		}
	}
}

add_action( 'parse_query', 'idg_base_theme_disable_native_search' );

if ( ! function_exists( 'idg_base_theme_headers' ) ) {
	/**
	 * Sets the HTTP headers for the page request.
	 *
	 * @return void
	 */
	function idg_base_theme_headers() {
		header( 'Referrer-Policy: no-referrer-when-downgrade' );
	}
}
add_action( 'send_headers', 'idg_base_theme_headers' );

if ( ! function_exists( 'idg_get_breadcrumbs' ) ) {
	/**
	 * Prints HTML for the site breadcrumb.
	 *
	 * @param string $article_type the article type.
	 */
	function idg_get_breadcrumbs( $article_type ) {
		$breadcrumb = [];

		if ( 'single' === $article_type ) {
			$home                 = get_home_url();
			$get_cat              = get_post_meta( get_the_ID(), '_idg_post_categories' )[0];
			$get_cat_archive      = get_term_meta( $get_cat[0], 'archive_page' )[0];
			$get_cat_archive_term = get_post( $get_cat_archive );
			$story_type           = get_the_terms( get_the_ID(), 'story_types' )[0];
			$story_type_link      = ( $home . '/' . $get_cat_archive_term->post_name . '/' . $story_type->slug );
			$third_level_terms    = [
				'news',
				'reviews',
				'how-to',
				'howto',
			];

			if ( $get_cat ) {
				$breadcrumb[] = [
					'url'   => esc_url( $home ),
					'label' => esc_html( 'Home' ),
				];
			}

			if ( $get_cat_archive ) {
				$breadcrumb[] = [
					'url'   => esc_url( $home . '/' . $get_cat_archive_term->post_name ),
					'label' => esc_html( $get_cat_archive_term->post_title ),
				];
			}
			if ( ( $get_cat_archive && $story_type && $story_type_link ) && in_array( $story_type->slug, $third_level_terms ) ) {
				$breadcrumb[] = [
					'url'   => esc_url( $story_type_link ),
					'label' => esc_html( $story_type->name ),
				];
			}
		} elseif ( 'archive' === $article_type ) {
			$archive_tax = get_queried_object()->taxonomy;
			$home        = get_home_url();
			$get_term    = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$get_tax     = get_taxonomy( $archive_tax );
			$get_tax_url = $home . '/' . $get_tax->rewrite['slug'];

			if ( $get_tax || $get_term || is_author() ) {
				$breadcrumb[] = [
					'url'   => esc_url( $home ),
					'label' => esc_html( 'Home ' ),
				];

				if ( $get_tax->label ) {
					$breadcrumb[] = [
						'url'   => esc_url( $get_tax_url ),
						'label' => esc_html( $get_tax->label ),
					];
				}

				if ( $get_term->name ) {
					$breadcrumb[] = [
						'label' => esc_html( $get_term->name ),
					];
				}
			}
		}

		return $breadcrumb;
	}
}

if ( ! function_exists( 'disable_feed_on_search' ) ) {
	/**
	 * Redirect user to main RSS feed and block the generation of RSS and RSS2 feed 
	 */
	function disable_feed_on_search() {
		global $wp;
		$first_char    = substr( $wp->request, 0, 6 );
		$capture_match = [];
		$last_char     = substr( $wp->request, strrpos( $wp->request, '/' ) + 1 );
		$regex         = preg_match( '/^search\/(.+)\/feed/', $wp->request, $capture_match );

		if ( 1 === $regex && ! empty( $capture_match ) ) {
			wp_safe_redirect( site_url( '404' ) );
			exit;
		}
	}
}
add_action( 'do_feed_rdf', 'disable_feed_on_search', 1 );
add_action( 'do_feed_rss', 'disable_feed_on_search', 1 );
add_action( 'do_feed_rss2', 'disable_feed_on_search', 1 );
add_action( 'do_feed_atom', 'disable_feed_on_search', 1 );
add_action( 'do_feed_rss2_comments', 'disable_feed_on_search', 1 );
add_action( 'do_feed_atom_comments', 'disable_feed_on_search', 1 );

if ( ! function_exists( 'idg_feed_customization_for_deal_dealpost' ) ) {
	/**
	 * Customization to handle Deal & Dealpost terms in feed.
	 *
	 * @param WP_Query $query The query currently being performed by WordPress.
	 */
	function idg_feed_customization_for_deal_dealpost( $query ) {
		// Check to run only for Feed.
		if ( $query->is_feed() && $query->is_main_query() ) {
			// Check to run filter only for taxonomy story_types.
			if ( isset( $query->query['taxonomy'] ) && $query->query['taxonomy'] === 'story_types' ) {
				if ( isset( $query->query['term'] ) && ! empty( $query->query['term'] ) ) {
					if ( 'deals' === $query->query['term'] || 'dealpost' === $query->query['term'] ) {
						$query_arr                      = [
							'relation' => 'OR',
							[
								'taxonomy' => 'story_types',
								'field'    => 'slug',
								'terms'    => 'deals',
							],
							[
								'taxonomy' => 'story_types',
								'field'    => 'slug',
								'terms'    => 'dealpost',
							],
						];
						$query->query_vars['tax_query'] = $query_arr; //phpcs:ignore
						$query->query['tax_query']      = $query_arr; //phpcs:ignore
					} 
				}
			}
		}        
	}
}
add_action( 'pre_get_posts', 'idg_feed_customization_for_deal_dealpost', 10 );
