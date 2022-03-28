<?php

use function \IDG\Base_Theme\Utils\get_sponsored_posts;

/**
 * Enqueues the scripts needed for loading posts via ajax.
 */
function ajaxload_posts_scripts() {
	wp_register_script( 'ajaxload_posts', get_theme_file_uri( '/dist/scripts/' . IDG_BASE_THEME_AJAXLOAD_POSTS_JS ), [ 'jquery' ], 1, true );

	wp_localize_script(
		'ajaxload_posts',
		'ajaxload_params',
		[
			'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php',
			'nonce'   => wp_create_nonce( 'ajax-load' ),
		]
	);

	wp_enqueue_script( 'ajaxload_posts' );
}
add_action( 'wp_enqueue_scripts', 'ajaxload_posts_scripts' );

/**
 * Handles the ajax functionality.
 */
function ajaxload_posts_ajax_handler() {

	// Check nonce.
	if ( ! isset( $_POST['_ajaxnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_ajaxnonce'] ) ) ) {
		return false;
	}

	// Check and sanitize data.
	$sanitize_args = [
		'page'    => FILTER_SANITIZE_NUMBER_INT,
		'perpage' => FILTER_SANITIZE_NUMBER_INT,
		'offset'  => FILTER_SANITIZE_NUMBER_INT,
		'exclude' => FILTER_SANITIZE_NUMBER_INT,
	];
	$post_data     = filter_input_array( INPUT_POST, $sanitize_args );

	$paged   = isset( $post_data['page'] ) ? $post_data['page'] + 1 : 1;
	$perpage = isset( $post_data['perpage'] ) ? $post_data['perpage'] : 1;
	$offset  = isset( $post_data['offset'] ) ? $post_data['offset'] : null;
	$exclude = isset( $post_data['exclude'] ) ? intval( $post_data['exclude'] ) : 0;

	$filters = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
	if ( ! empty( $filters ) ) {
		$stripped_filter = stripslashes( str_replace( "'", '"', $filters ) );
		$filter          = json_decode( $stripped_filter );
	}

	// Set arguments.
	$args                = [];
	$args['post_status'] = [ 'publish', 'updated' ];
	$args['post_type']   = 'post';

	if ( ! empty( $filter ) ) {

		$new_filter = [];
		$tax_item   = [];
		$tax_query  = [];

		foreach ( $filter as $key => $value ) {
			$new_filter[ $value->tax ][ $key ] = $value;
		}

		if ( empty( $new_filter ) ) {
			return [];
		}

		foreach ( $new_filter as $key => $value ) {
			$terms = array_map(
				function( $value ) {
					return $value->value;
				},
				$value
			);

			$tax_item = [
				'taxonomy' => $key, // phpcs:ignore
				'field'    => 'term_id',
				'terms'    => $terms,
			];

			array_push( $tax_query, $tax_item );
		}

		$args['tax_query'] = $tax_query;
	}

	if ( $offset ) {
		/**
		 * Alters the arguments to get the id's of the offset posts
		 * then unsets the `fields` argument as not needed.
		 * `posts_per_page` will be set back to $perpage and not $offset
		 * after this has ran.
		 */

		$args['posts_per_page'] = $offset > 0 ? $offset : null;
		$args['fields']         = 'ids';

		$offset_posts = idg_wp_query_cache( $args, 'idg_ajaxload_posts_offset' );
		$offset_ids   = $offset_posts->posts;

		unset( $args['fields'] );
	}

	$args['paged']          = $paged;
	$args['posts_per_page'] = $perpage <= 20 ? $perpage : 1;
	$args['post__not_in']   = $offset_ids ? $offset_ids : [];

	// Exclude sponsored posts.
	if ( 1 === $exclude ) {
		$excluded_posts = get_sponsored_posts();
		array_push( $args['post__not_in'], $excluded_posts );
	}

	$query = idg_wp_query_cache( $args, 'idg_ajaxload_posts' );

	$current_page = $args['paged'];
	$total_pages  = intval( $query->max_num_pages );

	if ( $query->have_posts() ) :
		$index = 0;

		while ( $query->have_posts() ) :
			$query->the_post();

			get_template_part( 'template-parts/snippet-post' );

			$offset = $args['posts_per_page'] * intval( $args['paged'] - 1 );

			do_action( 'idg_render_article_feed_item', $index, $offset );

			$index++;
		endwhile;

	endif;

	if ( $current_page === $total_pages ) {
		echo '<div id="end-of-posts"></div>';
	}

	die;
}
add_action( 'wp_ajax_ajaxload', 'ajaxload_posts_ajax_handler' );
add_action( 'wp_ajax_nopriv_ajaxload', 'ajaxload_posts_ajax_handler' );
