<?php

namespace IDG\Golden_Taxonomy;

use IDG\Publishing_Flow\Sites;

/**
 * Taxonomy related functionalities.
 */
class Taxonomy {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'modify_category_taxonomy_arguments' ] );
		add_action( 'init', [ $this, 'modify_tag_taxonomy_arguments' ] );
		add_action( 'init', [ $this, 'add_terms_custom_capability' ] );

	}

	/**
	 * Modidifies the capabilty arguments for the category taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function modify_category_taxonomy_arguments() {
		$category_args = get_taxonomy( 'category' );

		$category_args->capabilities                 = (array) $category_args->cap;
		$category_args->capabilities['edit_terms']   = 'edit_terms';
		$category_args->capabilities['delete_terms'] = 'delete_terms';


		// re-register the category taxonomy with modified capabilities.
		register_taxonomy( 'category', [ 'post', 'product' ], (array) $category_args );
	}

	/**
	 * Modidifies the capabilty arguments for the post_tag taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function modify_tag_taxonomy_arguments() {

		$tag_args = get_taxonomy( 'post_tag' );

		$caps = ! empty( $tag_args->cap ) ? (array) $tag_args->cap : [];

		// Add custom post tag capabilties to manage tags.
		$tag_args->capabilities                 = $caps;
		$tag_args->capabilities['edit_terms']   = 'edit_terms';
		$tag_args->capabilities['delete_terms'] = 'delete_terms';

		// re-register the category taxonomy with modified capabilities.
		register_taxonomy( 'post_tag', 'post', (array) $tag_args );
	}

	/**
	 * Add custom manage terms capabilties to the admin role.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_terms_custom_capability() {

		$role = get_role( 'administrator' );

		if ( empty( $role ) ) {
			return;
		}

		$role->add_cap( 'manage_terms', true );
		$role->add_cap( 'edit_terms', true );
		$role->add_cap( 'delete_terms', true );
	}
}
