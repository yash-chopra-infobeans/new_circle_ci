<?php

namespace IDG\Golden_Taxonomy;

/**
 * Everything related meta-boxes.
 */
class Meta_Boxes {

	/**
	 * Meta box prefix.
	 *
	 * @var string Meta box slug.
	 */
	const PREFIX = 'idg-metaboxes';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_box_info' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_metabox_assets' ] );
	}

	/**
	 * Registers the post categories sort meta.
	 *
	 * @return void
	 */
	public function register_meta() {
		register_meta(
			'post',
			'_idg_post_categories',
			[
				'type'        => 'array',
				'description' => 'Sort order for category assignments in an article.',
				'single'      => true,
				'default'     => [],
			]
		);

		register_term_meta(
			'category',
			'golden_id',
			[
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Calls individual metabox functions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_meta_boxes() {
		$this->add_category_selection_meta_box();
		$this->add_tags_selection_meta_box();
	}

	/**
	 * Saves meta box data.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_meta_box_info( $post_id ) {
		$this->save_category_selection_meta_box_info( $post_id );
		$this->save_tag_selection_meta_box_info( $post_id );
	}

	/**
	 * Adds 'Categories' metabox.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_category_selection_meta_box() {
		$post_types = [ 'post' ];

		/**
		 * Filters allowed post types for the custom category metabox.
		 *
		 * @param array $post_types An array of allowed post types.
		 */
		$post_types = apply_filters( 'idg_category_selection_allowed_post_types', $post_types );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'idg_category_selection_metabox',
				__( 'Categories', 'idg-golden-taxonomy' ),
				[ $this, 'category_selection_render_callback' ],
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Adds 'Tags' metabox.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_tags_selection_meta_box() {
		$post_types = [ 'post' ];

		/**
		 * Filters allowed post types for the custom tags metabox.
		 *
		 * @param array $post_types An array of allowed post types.
		 */
		$post_types = apply_filters( 'idg_tag_selection_allowed_post_types', $post_types );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'idg_tag_selection_metabox',
				__( 'Tags', 'idg-golden-taxonomy' ),
				[ $this, 'tag_selection_render_callback' ],
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render callback function for 'Categories' metabox.
	 *
	 * @param WP_Post $post Current post object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tag_selection_render_callback( $post ) {

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) || empty( $post->ID ) ) {
			return;
		}

		$current_post_id = $post->ID;

		$tags = get_tags(
			[
				'hide_empty' => false,
			]
		);

		if ( empty( $tags ) || ! is_array( $tags ) ) {
			return;
		}

		Utils\get_template(
			'tag-selection-meta-box',
			[
				'current_post_id' => $current_post_id,
			],
			true
		);
	}

	/**
	 * Render callback function for 'Categories' metabox.
	 *
	 * @param WP_Post $post Current post object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function category_selection_render_callback( $post ) {

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) || empty( $post->ID ) ) {
			return;
		}

		$current_post_id = $post->ID;

		$categories = get_categories(
			[
				'hide_empty' => false,
			]
		);

		if ( empty( $categories ) || ! is_array( $categories ) ) {
			return;
		}

		Utils\get_template(
			'category-selection-meta-box',
			[
				'current_post_id' => $current_post_id,
				'categories'      => $categories,
			],
			true
		);
	}

	/**
	 * Handles saving value of categories metabox.
	 *
	 * @param int $post_id Current post's id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function save_category_selection_meta_box_info( $post_id ) {

		$is_nonce_invalid  = empty( $_POST['category_selection_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['category_selection_fields_nonce'] ), 'category_selection_fields' );
		$is_doing_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE );

		if ( $is_nonce_invalid || $is_doing_autosave ) {
			return;
		}

		$selected_categories = filter_input( INPUT_POST, '_idg_post_categories', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		$selected_categories = ! empty( $selected_categories ) ? $selected_categories : [];
		$selected_categories = array_map( 'absint', $selected_categories );


		if ( empty( $selected_categories ) ) {
			delete_post_meta( $post_id, '_idg_post_categories' );
		} else {
			update_post_meta( $post_id, '_idg_post_categories', $selected_categories );
		}
	}

	/**
	 * Handles saving value of tags metabox.
	 *
	 * @param int $post_id Current post's id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function save_tag_selection_meta_box_info( $post_id ) {

		$is_nonce_invalid  = empty( $_POST['tag_selection_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['tag_selection_fields_nonce'] ), 'tag_selection_fields' );
		$is_doing_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE );

		if ( $is_nonce_invalid || $is_doing_autosave ) {
			return;
		}

		$selected_tags = filter_input( INPUT_POST, '_idg_post_tags', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		$selected_tags = ! empty( $selected_tags ) ? $selected_tags : [];
		$selected_tags = array_map( 'absint', $selected_tags );

		wp_set_post_terms( $post_id, $selected_tags, 'post_tag' );

	}

	/**
	 * Enqueues required scripts and styles for metaboxes associated with Golden Taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_metabox_assets() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->base ) {
			return;
		}

		$plugin_name = basename( IDG_GOLDEN_TAXONOMY_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_register_script(
			self::PREFIX . '-scripts',
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_GOLDEN_TAXONOMY_METABOXES_JS, $plugin_dir ),
			[ 'jquery' ],
			filemtime( IDG_GOLDEN_TAXONOMY_DIR . '/dist/scripts/' . IDG_GOLDEN_TAXONOMY_METABOXES_JS ),
			true
		);

		wp_localize_script( self::PREFIX . '-scripts', 'postType', get_post_type() );

		wp_enqueue_script( self::PREFIX . '-scripts' );

		wp_enqueue_style(
			self::PREFIX . '-styles',
			plugins_url( $plugin_name . '/dist/styles/' . IDG_GOLDEN_TAXONOMY_METABOXES_CSS, $plugin_dir ),
			[],
			filemtime( IDG_GOLDEN_TAXONOMY_DIR . '/dist/styles/' . IDG_GOLDEN_TAXONOMY_METABOXES_CSS )
		);
	}
}
