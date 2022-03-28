<?php
/**
 * Creates column for `old_id_in_onecms` in dashboard.
 *
 * @param array $columns Array of `posts` columns.
 */
function idg_base_theme_filter_posts_columns( $columns ) {
	$columns['onecmsid'] = __( 'OneCMS ID' );
	return $columns;
}
add_filter( 'manage_posts_columns', 'idg_base_theme_filter_posts_columns' );

/**
 * Populates column for `old_id_in_onecms` in dashboard.
 *
 * @param string $column The current `posts` column.
 * @param int    $post_id The current post id.
 */
function idg_base_theme_posts_column( $column, $post_id ) {
	if ( 'onecmsid' === $column ) {
		$onecmsid = get_post_meta( $post_id, 'old_id_in_onecms', true );
		if ( $onecmsid ) {
			echo esc_attr( $onecmsid );
		} else {
			echo '-';
		}
	}
}
add_action( 'manage_posts_custom_column', 'idg_base_theme_posts_column', 10, 2 );

/**
 * Sets the columns which should be sortable.
 *
 * @param array $columns The sortable columns.
 * @return array
 */
function idg_base_theme_sortables( $columns ) {
	$columns['onecmsid'] = 'onecmsid';
	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'idg_base_theme_sortables' );

/**
 * Sets the query for any defined sortable columns
 * in the admin post list.
 *
 * @param \WP_Query $query The query object.
 * @return void
 */
function idg_base_theme_sortables_query( $query ) {
	if ( ! is_admin() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );
	if ( 'onecmsid' === $orderby ) {
		$query->set( 'meta_key', 'old_id_in_onecms' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'idg_base_theme_sortables_query' );
