<?php
if ( ! function_exists( 'idg_get_post_terms' ) ) {
	/**
	 * A more performant version of wp_get_object_terms.
	 *
	 * @since 1.0.0
	 * @param int    $post_id The post whose terms are to be retrieved.
	 * @param string $taxonomy The taxonomy type whose terms are to be retrieved.
	 * @return array
	 */
	function idg_get_post_terms( $post_id = 0, $taxonomy = '' ) {

		global $wpdb;

		if ( $taxonomy ) {
			$cached = wp_cache_get( sprintf( 'idg_get_post_terms_%s_%s', $post_id, $taxonomy ) );
		} else {
			$cached = wp_cache_get( sprintf( 'idg_get_post_terms_%s', $post_id ) );
		}

		if ( $cached ) {
			return $cached;
		}

		// phpcs:ignore
		$terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT term.*, tax.* FROM {$wpdb->terms} AS term
				INNER JOIN {$wpdb->term_taxonomy} AS tax ON term.term_id = tax.term_id
				INNER JOIN {$wpdb->term_relationships} AS rel ON tax.term_taxonomy_id = rel.term_taxonomy_id
				WHERE rel.object_id = %d
				AND tax.taxonomy IN (%s, %s, %s, %s)
				AND tax.count > 0",
				$post_id,
				'post_tag',
				'category',
				'story_types',
				'article_type'
			)
		);

		if ( $taxonomy ) {
			foreach ( $terms as $key => $value ) {
				if ( $value->taxonomy !== $taxonomy ) {
					unset( $terms[ $key ] );
				}
			}
		}

		$terms = array_map(
			function( $term ) {
				return new WP_Term( $term );
			},
			$terms
		);

		if ( $taxonomy ) {
			wp_cache_set( sprintf( 'idg_get_post_terms_%s_%s', $post_id, $taxonomy ), $terms );
		} else {
			$cached = wp_cache_get( sprintf( 'idg_get_post_terms_%s', $post_id ) );
		}

		return $terms;
	}
}

if ( ! function_exists( 'idg_get_terms_by_post_type' ) ) {
	/**
	 * Gets all populated terms of post_type ignoring population of other post types
	 *
	 * @since 1.0.0
	 * @param string $taxonomy The taxonomy type whose populated terms are to be retrieved.
	 * @param string $post_type The post_type slug whose populated terms are to be retrieved.
	 * @return array
	 */
	function idg_get_terms_by_post_type( $taxonomy = '', $post_type = '' ) {

		global $wpdb;

		$cached = wp_cache_get( sprintf( 'idg_get_terms_by_post_type_%s_taxonomy_%s', $post_type, $taxonomy ) );

		if ( $cached ) {
			return $cached;
		}

		// phpcs:ignore
		$terms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT term.*, COUNT(*) from $wpdb->terms AS term
				INNER JOIN $wpdb->term_taxonomy AS tax ON term.term_id = tax.term_id
				INNER JOIN $wpdb->term_relationships AS rel ON rel.term_taxonomy_id = tax.term_taxonomy_id
				INNER JOIN $wpdb->posts AS post ON post.ID = rel.object_id
				WHERE post.post_type = %s AND tax.taxonomy = %s
				GROUP BY term.term_id",
				$post_type,
				$taxonomy
			)
		);

		$cached = wp_cache_get( sprintf( 'idg_get_terms_by_post_type_%s_taxonomy_%s', $post_type, $taxonomy ) );

		return $terms;
	}
}

if ( ! function_exists( 'create_taxonomy_labels' ) ) {
	/**
	 * Generate taxonomy labels.
	 *
	 * @param string $plural plural text.
	 * @param string $singular singular text.
	 * @return array
	 */
	function create_taxonomy_labels( $plural, $singular ) : array {
		return [
			// translators: Taxonomy `name`.
			'name'                       => sprintf( \__( '%s', 'idg-base-theme' ), $plural ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
			// translators: Taxonomy `singular_name`.
			'singular_name'              => sprintf( \__( '%s', 'idg-base-theme' ), $singular ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
			// translators: Taxonomy `menu_name`.
			'menu_name'                  => sprintf( \__( '%s', 'idg-base-theme' ), $plural ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
			// translators: Taxonomy `name_admin_bar`.
			'name_admin_bar'             => sprintf( \__( '%s', 'idg-base-theme' ), $singular ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
			// translators: Taxonomy `archives`.
			'archives'                   => sprintf( \__( 'All %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `all_items`.
			'all_items'                  => sprintf( \__( 'All %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `edit_item`.
			'edit_item'                  => sprintf( \__( 'Edit %s', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `view_item`.
			'view_item'                  => sprintf( \__( 'View %s', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `update_item`.
			'update_item'                => sprintf( \__( 'Update %s', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `add_new_item`.
			'add_new_item'               => sprintf( \__( 'Add New %s', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `new_item_name`.
			'new_item_name'              => sprintf( \__( 'New %s Name', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `parent_item`.
			'parent_item'                => sprintf( \__( 'Parent %s', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `parent_item_colon`.
			'parent_item_colon'          => sprintf( \__( 'Parent %s:', 'idg-base-theme' ), $singular ),
			// translators: Taxonomy `search_items`.
			'search_items'               => sprintf( \__( 'Search %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `separate_items_with_commas`.
			'separate_items_with_commas' => sprintf( \__( 'Separate %s with commas', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `add_or_remove_items`.
			'add_or_remove_items'        => sprintf( \__( 'Add or Remove %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `choose_from_most_used`.
			'choose_from_most_used'      => sprintf( \__( 'Choose from most used %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `not_found`.
			'not_found'                  => sprintf( \__( 'No %s Found', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `no_terms`.
			'no_terms'                   => sprintf( \__( 'No %s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `items_list_navigation`.
			'items_list_navigation'      => sprintf( \__( '%s list navigation', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `items_list`.
			'items_list'                 => sprintf( \__( '%s list', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `most_used`.
			'most_used'                  => sprintf( \__( 'Most used $s', 'idg-base-theme' ), $plural ),
			// translators: Taxonomy `back_to_items`.
			'back_to_items'              => sprintf( \__( '&larr; Back to %s', 'idg-base-theme' ), $plural ),
		];
	}
}

if ( ! function_exists( 'register_story_types_taxonomy' ) ) {
	/**
	 * Adds `story_types` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_story_types_taxonomy() {
		$args = [
			'labels'            => create_taxonomy_labels( 'Story Types', 'Story Type' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'capabilities'      => [
				'manage_terms' => 'manage_story_types',
				'edit_terms'   => 'edit_story_types',
				'delete_terms' => 'delete_story_types',
				'assign_terms' => 'assign_story_types',
			],
		];
		register_taxonomy( 'story_types', 'post', $args );

	}
}
add_action( 'init', 'register_story_types_taxonomy', 0 );

if ( ! function_exists( 'register_article_type_taxonomy' ) ) {
	/**
	 * Add `article_type` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_article_type_taxonomy() : void {
		$args = [
			'labels'            => create_taxonomy_labels( 'Article Types', 'Article Type' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		];

		register_taxonomy( 'article_type', [ 'post' ], $args );
	}
}
add_action( 'init', 'register_article_type_taxonomy', 0 );

if ( ! function_exists( 'register_sponsorships_taxonomy' ) ) {
	/**
	 * Adds `sponsorships` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_sponsorships_taxonomy() {
		$args = [
			'labels'            => create_taxonomy_labels( 'Sponsorships', 'Sponsorship' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'capabilities'      => [
				'manage_terms' => 'manage_sponsorships',
				'edit_terms'   => 'edit_sponsorships',
				'delete_terms' => 'delete_sponsorships',
				'assign_terms' => 'assign_sponsorships',
			],
		];
		register_taxonomy( 'sponsorships', 'post', $args );

	}
}
add_action( 'init', 'register_sponsorships_taxonomy', 0 );

if ( ! function_exists( 'register_sponsorships_taxonomy_fields' ) ) {
	/**
	 * Adds `sponsorships` taxonomy fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_sponsorships_taxonomy_fields() {
		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'text',
				'meta_name'    => 'display_name',
				'field_name'   => 'display-name',
				'display_name' => __( 'Display name', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Display name', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'textarea',
				'meta_name'    => 'introductory_text',
				'field_name'   => 'introductory-text',
				'display_name' => __( 'Introductory text', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose your Introductory text', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'text',
				'meta_name'    => 'series_name',
				'field_name'   => 'series-name',
				'display_name' => __( 'Series name', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Series name', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'image',
				'meta_name'    => 'logo',
				'field_name'   => 'brand-logo',
				'display_name' => __( 'Brand logo', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Brand logo', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'color',
				'meta_name'    => 'brand_color',
				'field_name'   => 'brand-color',
				'display_name' => __( 'Brand color', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Brand color', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'url',
				'meta_name'    => 'brand_url',
				'field_name'   => 'brand-url',
				'display_name' => __( 'Brand URL', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Brand URL', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'         => 'image',
				'meta_name'          => 'brand_image',
				'field_name'         => 'brand-image',
				'display_name'       => __( 'Brand image', 'idg-base-theme' ),
				'helper_text'        => __( 'Choose a Brand image to be used as a background', 'idg-base-theme' ),
				'image_preview_size' => '150s-r3:2',
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'checkbox',
				'meta_name'    => 'disable_ads',
				'field_name'   => 'disable-ads',
				'display_name' => __( 'Suppress Monetization', 'idg-base-theme' ),
				'helper_text'  => __( 'Suppress all monetization on content with this sponsorship.', 'idg-base-theme' ),
			]
		);

		$publications = [];
		$publications = idg_get_publications( true );
		foreach ( $publications as $key => $item ) {
			$publications[ $key ]       = new stdClass();
			$publications[ $key ]->val  = $item['value'];
			$publications[ $key ]->name = $item['label'];
		}

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'multi-select',
				'meta_name'    => 'publication',
				'field_name'   => 'publication',
				'display_name' => __( 'Publication', 'idg-base-theme' ),
				'helper_text'  => __( 'Select a Publication', 'idg-base-theme' ),
				'options'      => $publications,
			]
		);

		$business_units = [];
		$business_units = idg_get_business_units( true );
		foreach ( $business_units as $key => $item ) {
			$business_units[ $key ]       = new stdClass();
			$business_units[ $key ]->val  = $item['value'];
			$business_units[ $key ]->name = $item['label'];
		}

		idg_base_theme_register_taxonomy_fields(
			'sponsorships',
			[
				'field_type'   => 'dropdown',
				'meta_name'    => 'business_unit',
				'field_name'   => 'business_unit',
				'display_name' => __( 'Business unit', 'idg-base-theme' ),
				'helper_text'  => __( 'Select a Business unit', 'idg-base-theme' ),
				'options'      => $business_units,
			]
		);
	}
}
add_action( 'init', 'register_sponsorships_taxonomy_fields' );

if ( ! function_exists( 'idg_base_theme_hide_items_on_sponsorships' ) ) {
	/**
	 * Hides `name` helper text `description` field on `sponsorship` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function idg_base_theme_hide_items_on_sponsorships() {
		echo '<style> .term-description-wrap, .term-name-wrap p { display:none; } </style>';
	}
}
add_action( 'sponsorships_edit_form', 'idg_base_theme_hide_items_on_sponsorships' );
add_action( 'sponsorships_add_form', 'idg_base_theme_hide_items_on_sponsorships' );

if ( ! function_exists( 'idg_sponsorships_columns' ) ) {
	/**
	 * Hides description column and adds `introductory_text` meta column to sponsorships table.
	 *
	 * @param array $columns Tax table columns.
	 * @return array $columns Tax table columns.
	 */
	function idg_sponsorships_columns( $columns ) {
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}
		$columns['introductory_text'] = 'Introductory text';
		return $columns;
	}
}
if ( ! function_exists( 'idg_sponsorships_column_content' ) ) {
	/**
	 * Populates `introductory_text` column contents for sponsorships table.
	 *
	 * @param string $content Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id The term id.
	 * @return string $content Updated column content.
	 */
	function idg_sponsorships_column_content( $content, $column_name, $term_id ) {
		$term_meta = get_term_meta( $term_id, 'introductory_text', true );
		switch ( $column_name ) {
			case 'introductory_text':
				$content = $term_meta ? $term_meta : '-';
				break;
			default:
				break;
		}
		return $content;
	}
}
add_filter( 'manage_edit-sponsorships_columns', 'idg_sponsorships_columns' );
add_filter( 'manage_sponsorships_custom_column', 'idg_sponsorships_column_content', 10, 3 );

if ( ! function_exists( 'register_blogs_taxonomy' ) ) {
	/**
	 * Adds `blogs` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_blogs_taxonomy() {
		$args = [
			'labels'              => create_taxonomy_labels( 'Blogs', 'Blog' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_admin_column'   => true,
			'show_in_nav_menus'   => true,
			'show_tagcloud'       => false,
			'show_in_rest'        => true,
			'capabilities'        => [
				'manage_terms' => 'manage_blogs',
				'edit_terms'   => 'edit_blogs',
				'delete_terms' => 'delete_blogs',
				'assign_terms' => 'assign_blogs',
			],
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'rewrite'             => [
				'slug'       => 'blogs',
				'with_front' => false,
			],
		];
		register_taxonomy( 'blogs', 'post', $args );

	}
}
add_action( 'init', 'register_blogs_taxonomy', 0 );

if ( ! function_exists( 'register_blogs_taxonomy_fields' ) ) {
	/**
	 * Adds `blogs` taxonomy fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_blogs_taxonomy_fields() {

		idg_base_theme_register_taxonomy_fields(
			'blogs',
			[
				'field_type'   => 'text',
				'meta_name'    => 'blog_status',
				'field_name'   => 'blog-status',
				'display_name' => __( 'Blog Status', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Blog Status', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'blogs',
			[
				'field_type'   => 'image',
				'meta_name'    => 'logo',
				'field_name'   => 'blog-logo',
				'display_name' => __( 'Logo', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Logo', 'idg-base-theme' ),
			]
		);

		$users = [];
		$users = get_users();

		foreach ( $users as $key => $item ) {
			$users[ $key ]       = new stdClass();
			$users[ $key ]->val  = $item->ID;
			$users[ $key ]->name = $item->display_name . '(' . $item->user_email . ')';
		}

		idg_base_theme_register_taxonomy_fields(
			'blogs',
			[
				'field_type'   => 'dropdown',
				'meta_name'    => 'author',
				'field_name'   => 'author',
				'display_name' => __( 'Author', 'idg-base-theme' ),
				'helper_text'  => __( 'Select an Author', 'idg-base-theme' ),
				'options'      => $users,
			]
		);
	}
}
add_action( 'init', 'register_blogs_taxonomy_fields', 0 );

if ( ! function_exists( 'register_podcast_series_taxonomy' ) ) {
	/**
	 * Adds `podcast_series` taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_podcast_series_taxonomy() {
		$args = [
			'labels'            => create_taxonomy_labels( 'Podcast series', 'Podcast series' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'capabilities'      => [
				'manage_terms' => 'manage_podcast_series',
				'edit_terms'   => 'edit_podcast_series',
				'delete_terms' => 'delete_podcast_series',
				'assign_terms' => 'assign_podcast_series',
			],
			'rewrite'           => [
				'slug'       => 'podcast',
				'with_front' => false,
			],
		];
		register_taxonomy( 'podcast_series', 'post', $args );

	}
}
add_action( 'init', 'register_podcast_series_taxonomy', 0 );

if ( ! function_exists( 'register_podcast_series_taxonomy_fields' ) ) {
	/**
	 * Adds `podcast_series` taxonomy fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_podcast_series_taxonomy_fields() {
		idg_base_theme_register_taxonomy_fields(
			'podcast_series',
			[
				'field_type'   => 'image',
				'meta_name'    => 'logo',
				'field_name'   => 'podcast-series-logo',
				'display_name' => __( 'Logo', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a logo', 'idg-base-theme' ),
			]
		);

		idg_base_theme_register_taxonomy_fields(
			'podcast_series',
			[
				'field_type'   => 'url',
				'meta_name'    => 'apple_podcast_url',
				'field_name'   => 'apple-podcast-url',
				'display_name' => __( 'Apple Podcast URL', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose an Apple Podcast URL for this Podcast series', 'idg-base-theme' ),
			]
		);
		idg_base_theme_register_taxonomy_fields(
			'podcast_series',
			[
				'field_type'   => 'url',
				'meta_name'    => 'google_play_url',
				'field_name'   => 'google-play-url',
				'display_name' => __( 'Google Play URL', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose a Google play URL for this Podcast series', 'idg-base-theme' ),
			]
		);
		idg_base_theme_register_taxonomy_fields(
			'podcast_series',
			[
				'field_type'   => 'url',
				'meta_name'    => 'rss_url',
				'field_name'   => 'rss-url',
				'display_name' => __( 'RSS URL', 'idg-base-theme' ),
				'helper_text'  => __( 'Choose an RSS URL for this Podcast series', 'idg-base-theme' ),
			]
		);
	}
}
add_action( 'init', 'register_podcast_series_taxonomy_fields', 0 );

if ( ! function_exists( 'register_category_taxonomy_fields' ) ) {
	/**
	 * Adds `category` taxonomy fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function register_category_taxonomy_fields() {
		$pages = [];
		$pages = get_pages();

		foreach ( $pages as $key => $item ) {
			$pages[ $key ]       = new stdClass();
			$pages[ $key ]->val  = $item->ID;
			$pages[ $key ]->name = $item->post_title;
		}

		idg_base_theme_register_taxonomy_fields(
			'category',
			[
				'field_type'   => 'dropdown',
				'meta_name'    => 'archive_page',
				'field_name'   => 'archive-page',
				'display_name' => __( 'Archive Page', 'idg-base-theme' ),
				'helper_text'  => __( 'Select an Archive Page', 'idg-base-theme' ),
				'options'      => $pages,
			]
		);
	}
}
add_action( 'init', 'register_category_taxonomy_fields', 0 );
if ( ! function_exists( 'idg_base_theme_remove_default_archives' ) ) {
	/**
	 * Removes archives for default `category` and `tag` taxonomies.
	 */
	function idg_base_theme_remove_default_archives() {
		if ( is_category() || is_tag() ) {
			global $wp_query;
			$wp_query->set_404();
		}
	}
}
add_action( 'template_redirect', 'idg_base_theme_remove_default_archives' );
