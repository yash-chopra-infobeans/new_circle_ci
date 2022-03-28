<?php

namespace IDG\Asset_Manager;

use IDG\Publishing_Flow\Sites;
use \IDG\Asset_Manager\Rest_Video_Controller;

/**
 * Contains hooks and filters that aren;t large enough to seperate out in to there own class.
 */
class Loader {
	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'custom_image_sizes' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'rest_insert_attachment', [ $this, 'insert_custom_attachment' ], 10, 3 );
		add_action( 'rest_api_init', [ $this, 'register_custom_video_rest_controller' ] );

		add_filter( 'https_ssl_verify', [ $this, 'filter_https_ssl_verify' ], 10, 2 );
		add_filter( 'image_size_names_choose', [ $this, 'custom_editor_sizes' ], 10, 1 );
	}

	/**
	 * Register custom controller for video REST endpoints.
	 *
	 * @return void
	 */
	public function register_custom_video_rest_controller() {
		$controller = new Rest_Video_Controller();
		$controller->register_routes();
	}

	/**
	 * Set custom sizes to an empty array so that the sizes dropdown doesn't show
	 *
	 * @return array
	 */
	public function custom_editor_sizes() : array {
		return [];
	}

	/**
	 * Add our custom sizes/ratios
	 *
	 * @return void
	 */
	public function custom_image_sizes() : void {
		add_image_size( '1240-r1:1', 1240, 1240, true );
		add_image_size( '300-r1:1', 300, 300, true );

		add_image_size( '1240-r3:2', 1240, 826, true );
		add_image_size( '300-r3:2', 300, 200, true );
		add_image_size( '150-r3:2', 150, 100, true );

		add_image_size( '1240-r16:9', 1240, 697, true );
		add_image_size( '300-r16:9', 300, 168, true );
		add_image_size( '150-r16:9', 150, 84, true );
	}

	/**
	 * If any terms are passed set the relationship between the attachment and term
	 *
	 * @param integer          $asset_id attachment id.
	 * @param \WP_REST_Request $request REST request.
	 * @return void
	 */
	public function handle_terms( int $asset_id, \WP_REST_Request $request ) {
		$taxonomies = wp_list_filter( get_object_taxonomies( 'attachment', 'objects' ), [ 'show_in_rest' => true ] );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			wp_set_object_terms( $asset_id, $request[ $base ], $taxonomy->name );
		}
	}

	/**
	 * Insert attachment term relationships(s)
	 *
	 * @param \WP_Post         $attachment WP_Post object.
	 * @param \WP_REST_Request $request REST request.
	 * @return void
	 */
	public function insert_custom_attachment( \WP_Post $attachment, \WP_REST_Request $request ) {
		$this->handle_terms( $attachment->ID, $request );
	}

	/**
	 * SSL verification sometimes causes error's when developing lcoally.
	 *
	 * This filter allows us to bypass SSL verification on our local development setup(s).
	 *
	 * @param boolean $ssl_verify Whether to verify the SSL connection.
	 * @return boolean
	 */
	public function filter_https_ssl_verify( bool $ssl_verify ) : bool {
		if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) && 'local' === VIP_GO_APP_ENVIRONMENT ) {
			return false;
		}

		return $ssl_verify;
	}

	/**
	 * Register attachment taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomy() : void {
		register_taxonomy(
			'asset_tag',
			'attachment',
			[
				'label'        => __( 'Asset Tags', 'idg-plugin-assets' ),
				'labels'       => [
					'name'              => __( 'Asset Tags', 'idg-plugin-assets' ),
					'singular_name'     => _x( 'Tag', 'taxonomy singular name', 'idg-plugin-assets' ),
					'search_items'      => __( 'Search Tags', 'idg-plugin-assets' ),
					'all_items'         => __( 'All GenTagsres', 'idg-plugin-assets' ),
					'parent_item'       => __( 'Parent Tag', 'idg-plugin-assets' ),
					'parent_item_colon' => __( 'Parent Tag:', 'idg-plugin-assets' ),
					'edit_item'         => __( 'Edit Tag', 'idg-plugin-assets' ),
					'update_item'       => __( 'Update Tag', 'idg-plugin-assets' ),
					'add_new_item'      => __( 'Add New Tag', 'idg-plugin-assets' ),
					'new_item_name'     => __( 'New Tag Name', 'idg-plugin-assets' ),
					'menu_name'         => __( 'Tag', 'idg-plugin-assets' ),
					'back_to_items'     => __( '&larr; Back to Asset Tags', 'idg-plugin-assets' ),
				],
				'public'       => true,
				'rewrite'      => false,
				'show_in_rest' => true,
				'capabilities' => [
					'manage_terms' => 'manage_asset_tag',
					'edit_terms'   => 'edit_asset_tag',
					'delete_terms' => 'delete_asset_tag',
					'assign_terms' => 'assign_asset_tag',
				],
			]
		);

		register_taxonomy(
			'asset_image_rights',
			'attachment',
			[
				'label'        => __( 'Image Rights', 'idg-plugin-assets' ),
				'labels'       => [
					'name'              => __( 'Image Rights', 'idg-plugin-assets' ),
					'singular_name'     => _x( 'Image Right', 'taxonomy singular name', 'idg-plugin-assets' ),
					'search_items'      => __( 'Search Image Rights', 'idg-plugin-assets' ),
					'all_items'         => __( 'All Image Rights', 'idg-plugin-assets' ),
					'parent_item'       => __( 'Parent Image Right', 'idg-plugin-assets' ),
					'parent_item_colon' => __( 'Parent Image Right:', 'idg-plugin-assets' ),
					'edit_item'         => __( 'Edit Image Right', 'idg-plugin-assets' ),
					'update_item'       => __( 'Update Image Right', 'idg-plugin-assets' ),
					'add_new_item'      => __( 'Add Image Right', 'idg-plugin-assets' ),
					'new_item_name'     => __( 'New Image Right', 'idg-plugin-assets' ),
					'menu_name'         => __( 'Image right', 'idg-plugin-assets' ),
					'back_to_items'     => __( '&larr; Back to Image Rights', 'idg-plugin-assets' ),
				],
				'public'       => true,
				'rewrite'      => false,
				'show_in_rest' => true,
				'capabilities' => [
					'manage_terms' => 'manage_asset_image_rights',
					'edit_terms'   => 'edit_asset_image_rights',
					'delete_terms' => 'delete_asset_image_rights',
					'assign_terms' => 'assign_asset_image_rights',
				],
			]
		);
	}
}
