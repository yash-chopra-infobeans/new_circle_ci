<?php

if ( ! function_exists( 'create_product_taxonomy_labels' ) ) {
	/**
	 * Generate plural & singular taxonomy labels.
	 *
	 * @param string $plural - The plural name.
	 * @param string $singular - The singular name.
	 * @return array
	 */
	function create_product_taxonomy_labels( $plural, $singular ) : array {
		return [
			// phpcs:disable WordPress.WP.I18n.NoEmptyStrings
			/* translators: %s is the plural taxonomy name */
			'name'                       => sprintf( __( '%s', 'idg' ), $plural ),
			/* translators: %s is the singular taxonomy name */
			'singular_name'              => sprintf( __( '%s', 'idg' ), $singular ),
			/* translators: %s is the plural taxonomy name */
			'menu_name'                  => sprintf( __( '%s', 'idg' ), $plural ),
			/* translators: %s is the singular taxonomy name */
			'name_admin_bar'             => sprintf( __( '%s', 'idg' ), $singular ),
			/* translators: %s is the plural taxonomy name */
			'archives'                   => sprintf( __( 'All %s', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'all_items'                  => sprintf( __( 'All %s', 'idg' ), $plural ),
			/* translators: %s is the singular taxonomy name */
			'edit_item'                  => sprintf( __( 'Edit %s', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'view_item'                  => sprintf( __( 'View %s', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'update_item'                => sprintf( __( 'Update %s', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'add_new_item'               => sprintf( __( 'Add New %s', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'new_item_name'              => sprintf( __( 'New %s Name', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'parent_item'                => sprintf( __( 'Parent %s', 'idg' ), $singular ),
			/* translators: %s is the singular taxonomy name */
			'parent_item_colon'          => sprintf( __( 'Parent %s:', 'idg' ), $singular ),
			/* translators: %s is the plural taxonomy name */
			'search_items'               => sprintf( __( 'Search %s', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'add_or_remove_items'        => sprintf( __( 'Add or Remove %s', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'not_found'                  => sprintf( __( 'No %s Found', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'no_terms'                   => sprintf( __( 'No %s', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'items_list_navigation'      => sprintf( __( '%s list navigation', 'idg' ), $plural ),
			/* translators: %s is the plural taxonomy name */
			'items_list'                 => sprintf( __( '%s list', 'idg' ), $plural ),
			// translators: Taxonomy `most_used`.
			'most_used'                  => sprintf( \__( 'Most used $s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `back_to_items`.
			'back_to_items'              => sprintf( \__( '&larr; Back to %s', 'idg-base-theme' ), $plural ),
			// phpcs:enable
		];
	}
}
