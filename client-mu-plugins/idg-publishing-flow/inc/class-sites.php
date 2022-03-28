<?php

namespace IDG\Publishing_Flow;

/**
 * Manages the sites provided.
 */
class Sites {
	/**
	 * The taxonomy being used.
	 */
	const TAXONOMY = 'publication';

	/**
	 * Meta key of the publication host.
	 */
	const TERM_META_HOST = 'publication_host';

	/**
	 * Meta key of the publication client id for auth.
	 */
	const TERM_META_CLIENT = 'publication_client_key';

	/**
	 * Meta key of the publication host.
	 */
	const TERM_META_TYPE = 'publication_type';

	/**
	 * The access token data for the publication.
	 */
	const TERM_META_ACCESS_TOKEN = 'publication_access_token';

	const SCRIPT_NAME = 'publication-taxonomy-script';
	const STYLE_NAME  = 'publication-taxonomy-style';

	/**
	 * List of slug and names of types
	 * that are allowed to be used by the
	 * taxonomy listing.
	 *
	 * @var array
	 */
	private $allowed_types = [
		'business-unit' => 'Business Unit',
		'publication'   => 'Publication',
	];

	/**
	 * Initialise the class with taxonomy registration and building.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_taxonomy' ], 0 );
		add_action( self::TAXONOMY . '_row_actions', [ $this, 'remove_quick_actions' ], 10, 1 );
		add_action( self::TAXONOMY . '_pre_add_form', [ $this, 'enqueue_assets' ] );
		add_action( self::TAXONOMY . '_pre_edit_form', [ $this, 'enqueue_assets' ] );
		add_action( self::TAXONOMY . '_add_form_fields', [ $this, 'add_template' ], 10, 2 );
		add_action( self::TAXONOMY . '_edit_form_fields', [ $this, 'edit_template' ], 10, 2 );
		add_action( 'create_' . self::TAXONOMY, [ $this, 'update_term_meta' ], 10, 2 );
		add_action( 'edited_' . self::TAXONOMY, [ $this, 'update_term_meta' ], 10, 2 );
		add_action( 'manage_edit-' . self::TAXONOMY . '_columns', [ $this, 'remove_default_taxonomy_fields' ] );
		add_action( 'added_term_relationship', [ $this, 'assign_parent_to_article' ], 10, 3 );
		add_filter( 'rest_pre_echo_response', [ $this, 'ensure_taxonomy_order' ] );
		add_filter( 'taxonomy_parent_dropdown_args', [ $this, 'parent_select_args' ], 10, 1 );
		add_filter( 'rest_request_after_callbacks', [ $this, 'suppress_taxonomy_preloading' ], 10, 3 );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column', [ $this, 'set_column_content' ], 10, 3 );
	}

	/**
	 * Removes quick actions from term listing that are not required.
	 *
	 * @param array $actions List of current available actions.
	 * @return array
	 */
	public function remove_quick_actions( $actions = [] ) : array {
		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}

	/**
	 * Registers the Sites/Publications taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomy() : void {
		$description = __( 'Publication and Business Unit listings.', 'idg-publishing-flow' );

		$taxonomy_args = [
			'labels'            => [
				'name'          => __( 'Business Units & Publications', 'idg-publishing-flow' ),
				'singular_name' => __( 'Business Unit & Publication', 'idg-publishing-flow' ),
				'edit_item'     => __( 'Edit', 'idg-publishing-flow' ),
				'add_new_item'  => __( 'Add New', 'idg-publishing-flow' ),
				'parent_item'   => __( 'Business Unit', 'idg-publishing-flow' ),
				'back_to_items' => __( '&larr; Back to Business Units & Publications', 'idg-publishing-flow' ),
			],
			'description'       => $description,
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
		];

		register_taxonomy(
			self::TAXONOMY,
			[ 'post', 'attachment' ],
			$taxonomy_args
		);
	}

	/**
	 * When booting, we need to remove the publication taxonomy
	 * to prevent it from being errornously set by Gutenberg.
	 *
	 * @param mixed            $response Result to send to the client.
	 * @param mixed            $handler Route handler used for the request.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 */
	public function suppress_taxonomy_preloading( $response, $handler, \WP_REST_Request $request ) {
		global $pagenow, $typenow;

		if ( is_a( $response, 'WP_Error' ) ) {
			return $response;
		}

		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) && 'post' !== $typenow ) {
			return $response;
		}

		if ( '/wp/v2/taxonomies' !== $request->get_route() ) {
			return $response;
		}

		$data = $response->get_data();

		if ( isset( $data[ self::TAXONOMY ] ) ) {
			unset( $data[ self::TAXONOMY ] );
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Show additional information in the taxonomy term listing.
	 *
	 * @param string $content The value being rendered.
	 * @param string $column_name The name of the column in this pass.
	 * @param int    $term_id ID of the term being rendered.
	 * @return string
	 */
	public function set_column_content( $content, $column_name, $term_id ) {
		if ( 'host' === $column_name ) {
			$value   = get_term_meta( $term_id, self::TERM_META_HOST, true );
			$content = $value;
		}

		if ( 'active' === $column_name ) {
			$value   = get_term_meta( $term_id, self::TERM_META_ACCESS_TOKEN, true );
			$content = $value ? 'Active' : '';
		}

		return $content;
	}

	/**
	 * Enqueue any required assets for the admin.
	 *
	 * @return void
	 */
	public function enqueue_assets() : void {
		$plugin_name = basename( IDG_PUBLISHING_FLOW_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_PUBLISHING_FLOW_PUBLICATION_TAXONOMY_JS, $plugin_dir ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-edit-post' ],
			filemtime( IDG_PUBLISHING_FLOW_DIR . '/dist/scripts/' . IDG_PUBLISHING_FLOW_PUBLICATION_TAXONOMY_JS ),
			false
		);

		wp_localize_script(
			self::SCRIPT_NAME,
			'IDGPublishingFlow',
			[
				'statuses' => Statuses::get_status_list(),
				'sites'    => self::get_sites_list(),
			]
		);

		wp_enqueue_style(
			self::STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_PUBLISHING_FLOW_PUBLICATION_TAXONOMY_CSS, $plugin_dir ),
			[],
			filemtime( IDG_PUBLISHING_FLOW_DIR . '/dist/styles/' . IDG_PUBLISHING_FLOW_PUBLICATION_TAXONOMY_CSS )
		);
	}

	/**
	 * Removes unrequired fields and adds ones that are to be used.
	 *
	 * @param array $columns Existing list of columns at time of execution.
	 * @return array
	 */
	public function remove_default_taxonomy_fields( $columns ) : array {
		unset( $columns['description'] );
		unset( $columns['slug'] );

		$columns['host']   = __( 'Host', 'idg-publication-flow' );
		$columns['active'] = __( 'Active', 'idg-publication-flow' );

		return $columns;
	}

	/**
	 * Renders the form fields when adding or editing a publication.
	 *
	 * @return void
	 */
	public function add_template() : void {
		$hide_on_load = false;
		require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/term/add/term-general.php';
		require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/term/add/term-business-unit.php';
		require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/term/add/term-publication.php';
	}

	/**
	 * Renders the form fields when adding or editing a publication.
	 *
	 * @param WP_Term|string $term The term being targeted.
	 * @return void
	 */
	public function edit_template( $term ) : void {
		if ( ! isset( $term->term_id ) ) {
			return;
		}

		$type_meta_value = get_term_meta( $term->term_id, self::TERM_META_TYPE, true );
		$type_value      = $type_meta_value ?: '';

		$host_meta_value = get_term_meta( $term->term_id, self::TERM_META_HOST, true );
		$host_value      = $host_meta_value ?: '';

		$client_meta_value = get_term_meta( $term->term_id, self::TERM_META_CLIENT, true );
		$client_value      = $client_meta_value ?: '';

		$access_token       = json_decode( get_term_meta( $term->term_id, self::TERM_META_ACCESS_TOKEN, true ) );
		$access_token_value = isset( $access_token->access_token ) ? $access_token->access_token : false;

		if ( 'business-unit' === $type_value ) {
			require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/term/edit/term-business-unit.php';
		}

		if ( 'publication' === $type_value ) {
			$hide_on_load = is_null( $type_value ) ? '' : 'display-section';
			$auth_url     = Auth::create_token_url( $host_value, $client_value );
			require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/term/edit/term-publication.php';
		}
	}

	/**
	 * Updates the meta for the publication being saved
	 *
	 * @param int $term_id The ID of the term to save against.
	 * @return void
	 */
	public function update_term_meta( $term_id ) : void {
		$type   = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		$host   = filter_input( INPUT_POST, 'host', FILTER_SANITIZE_STRING );
		$client = filter_input( INPUT_POST, 'client', FILTER_SANITIZE_STRING );

		if ( $client ) {
			update_term_meta( $term_id, self::TERM_META_CLIENT, $client );
		} else {
			delete_term_meta( $term_id, self::TERM_META_CLIENT );
			delete_term_meta( $term_id, self::TERM_META_ACCESS_TOKEN );
		}

		if ( $host ) {
			$host = str_replace( [ 'http://', 'https://' ], '', $host );
			update_term_meta( $term_id, self::TERM_META_HOST, $host );
		}

		if ( $type && in_array( $type, array_keys( $this->allowed_types ), true ) ) {
			update_term_meta( $term_id, self::TERM_META_TYPE, $type );
		}

		Cache::clear_all_publications();
	}

	/**
	 * Get a list of available and registered sites for
	 * publishing to and managing content for.
	 *
	 * @param boolean $get_all Whether to get all publications. Passing false will
	 *                         only get those of the business unit the current user
	 *                         is assigned.
	 * @return array
	 */
	public static function get_sites_list( bool $get_all = false ) : array {
		$get_all = defined( 'DOING_CRON' ) ?: $get_all;

		$user_id            = get_current_user_id();
		$all_string         = $get_all ? 'all_' : '';
		$cache_string       = "term_${all_string}" . self::TAXONOMY . "_${user_id}";
		$cached_publication = wpcom_vip_cache_get( $cache_string, Cache::CACHE_GROUP );

		if ( $cached_publication ) {
			return $cached_publication;
		}

		$publication_array = [
			[
				'label' => __( 'None', 'idg-publishing-flow' ),
				'value' => '',
				'term'  => null,
			],
		];

		$publication_terms = self::get_publications( $get_all );

		if ( ! $publication_terms ) {
			return $publication_array;
		}

		foreach ( $publication_terms as $publication ) {
			$access_token        = get_term_meta( $publication->term_id, self::TERM_META_ACCESS_TOKEN, true );
			$publication->host   = get_term_meta( $publication->term_id, self::TERM_META_HOST, true );
			$publication->active = $access_token ? true : false;

			$publication_array[] = [
				'label'    => $publication->name,
				'value'    => $publication->term_id,
				'term'     => $publication,
				'isActive' => $publication->active,
			];
		}

		wpcom_vip_cache_set( $cache_string, $publication_array, Cache::CACHE_GROUP );

		return $publication_array;
	}

	/**
	 * When a publication is attached to an article,
	 * ensure that it's parent is also attached.
	 *
	 * @param int    $object_id The term object id.
	 * @param int    $term_id The term id.
	 * @param string $taxonomy The taxonomy to process.
	 * @return void
	 */
	public function assign_parent_to_article( int $object_id, int $term_id, string $taxonomy ) : void {
		if ( self::TAXONOMY !== $taxonomy ) {
			return;
		}

		$the_term = get_term( $term_id, self::TAXONOMY );

		if ( 0 === $the_term->parent ) {
			return;
		}

		wp_set_post_terms( $object_id, $the_term->parent, self::TAXONOMY, true );
	}

	/**
	 * When returning the term list for the taxonomy
	 * as part of the post response request, they may
	 * appear in the correct order. This will ensure
	 * that the terms are in the following:
	 *   [{parent_id}, {child_id}]
	 *
	 * @param array $response The response prior to echo.
	 * @return array
	 */
	public function ensure_taxonomy_order( $response ) : array {
		$response = (array) $response;

		if ( ! isset( $response['type'] ) || 'post' !== $response['type'] ) {
			return $response;
		}

		if ( ! isset( $response[ self::TAXONOMY ] ) ) {
			return $response;
		}

		$publication = $response[ self::TAXONOMY ];

		$term_meta = get_term_meta( $publication[0], self::TERM_META_TYPE, true );

		if ( 'publication' === $term_meta ) {
			$response[ self::TAXONOMY ] = array_reverse( $response[ self::TAXONOMY ] );
		}

		return $response;
	}

	/**
	 * Set the arguments for the parent select in the taxonomy term
	 * creation page and edit screens.
	 *
	 * @param array $arguments List of current arguments used for getting terms.
	 * @return array
	 */
	public function parent_select_args( $arguments ) {
		if ( self::TAXONOMY === $arguments['taxonomy'] ) {
			$arguments['depth'] = 1;
		}

		return $arguments;
	}

	/**
	 * Check whether the site has been registered.
	 *
	 * @param string $site The name of the site to check.
	 * @return boolean
	 */
	public static function is_registered( string $site ) : bool {
		return is_in_array( $site, static::get_sites_list(), 'value' );
	}

	/**
	 * Get the publication by the given term id along with any
	 * meta that is attached.
	 *
	 * @param integer $id The requested term id.
	 * @return WP_Term
	 */
	public static function get_publication_by_id( int $id ) {
		$term = get_term( $id, self::TAXONOMY, OBJECT );

		if ( ! $term ) {
			return false;
		}

		$term->host = get_term_meta( $term->term_id, self::TERM_META_HOST, true );

		return $term;
	}

	/**
	 * Get all publications that have been saved.
	 *
	 * @param boolean $get_all Whether to get all publications. Passing false will
	 *                         only get those of the business unit the current user
	 *                         is assigned.
	 * @return array
	 */
	public static function get_publications( $get_all = true ) {
		$term_args = [
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
			'meta_key'   => self::TERM_META_TYPE,
			/**
			 * Searching by meta value is required as there is
			 * no other way to identify an entry is a publication.
			 * May be possible alternative if finding method for
			 * getting entries that have a parent.
			 */
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required for filtering.
			'meta_value' => 'publication',
		];

		if ( ! $get_all ) {
			$parent_id = User_Profiles::get_business_units();

			$term_args['parent'] = $parent_id;
		}

		$publication_terms = get_terms( $term_args );

		return $publication_terms;
	}

	/**
	 * Get all the business units terms that have been saved.
	 *
	 * @param boolean $get_all Whether to get all BUs. Passing false will
	 *                         only get the business unit the current user
	 *                         is assigned.
	 * @return array
	 */
	public static function get_business_units( $get_all = true ) {
		$term_args = [
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
			'meta_key'   => self::TERM_META_TYPE,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required for filtering.
			'meta_value' => 'business-unit',
		];

		if ( ! $get_all ) {
			$parent_id = User_Profiles::get_business_units();

			$term_args['object_ids'] = $parent_id;
		}

		$business_unit_terms = get_terms( $term_args );

		return $business_unit_terms;
	}

	/**
	 * Get a list of available and registered business units for
	 * managing content.
	 *
	 * @param boolean $get_all Whether to get all BUs. Passing false will
	 *                         only get the business unit the current user
	 *                         is assigned.
	 * @return array
	 */
	public static function get_business_units_list( $get_all = true ) {
		$user_id               = get_current_user_id();
		$cache_key             = 'term_' . self::TAXONOMY . "_bu_${user_id}";
		$cached_business_units = wpcom_vip_cache_get( $cache_key, Cache::CACHE_GROUP );

		if ( $cached_business_units ) {
			return $cached_business_units;
		}

		$business_units = self::get_business_units( $get_all );

		$bu_array = [
			[
				'label' => __( 'None', 'idg-publishing-flow' ),
				'value' => 0,
				'term'  => null,
			],
		];

		foreach ( $business_units as $bu ) {
			$bu_array[] = [
				'label' => $bu->name,
				'value' => $bu->term_id,
				'term'  => $bu,
			];
		}

		wpcom_vip_cache_set( $cache_key, $bu_array, Cache::CACHE_GROUP );

		return $bu_array;
	}

	/**
	 * Get the publication that has been attached to
	 * a post.
	 *
	 * @param int $post_id The post ID.
	 * @return object
	 */
	public static function get_post_publication( $post_id ) {
		$publication_terms = Cache::get_post_term( self::TAXONOMY, $post_id );

		$selected_term = array_values(
			array_filter(
				$publication_terms,
				function( $single_term ) {
					return $single_term->parent > 0;
				}
			)
		);

		if ( empty( $selected_term ) ) {
			return [];
		}

		return $selected_term[0];
	}

	/**
	 * Check if the current site is the origin site.
	 *
	 * @return boolean
	 */
	public static function is_origin() : bool {
		$site_url = trim( str_replace( [ 'http://', 'https://' ], '', get_option( 'siteurl' ) ), '/' );

		if ( defined( 'PUBLISHING_FLOW_EXPECTED_SOURCE_URL' ) && PUBLISHING_FLOW_EXPECTED_SOURCE_URL === $site_url ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the origin site url.
	 *
	 * @return string
	 */
	public static function get_origin_url() {
		return constant( 'PUBLISHING_FLOW_EXPECTED_SOURCE_URL' );
	}
}
