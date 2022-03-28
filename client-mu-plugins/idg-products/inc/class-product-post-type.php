<?php
/**
 * Product Post type features in back-end.
 *
 * @package idg-products
 */

namespace IDG\Products;

use WP_Error;
use WP_REST_Request;

/**
 * Management of the product post type.
 */
class Product_Post_Type {
	const SCRIPT_NAME           = 'idg-products';
	const STYLE_NAME            = 'idg-products';
	const POST_TYPE_SCRIPT_NAME = 'idg-product-post-type';
	const POST_TYPE_STYLE_NAME  = 'idg-product-post-type';

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_filter( 'rest_pre_insert_product', [ $this, 'handle_validation' ], 10, 2 );
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ], 1 );
		add_filter( 'enter_title_here', [ $this, 'set_custom_title' ] ); 
	}

	/**
	 * Initalize function.
	 *
	 * @return void
	 */
	public function init() : void {
		$this->register_product_post_type();
		$this->register_manufacturer_taxonomy();
		$this->register_origin_cms_taxonomy();
		$this->register_vendor_codes_taxonomy();
		$this->add_custom_fields();
		$this->register_assets();
	}

	/**
	 * Register the product post type.
	 *
	 * @return void
	 */
	public function register_product_post_type() : void {
		register_post_type(
			'product',
			[
				'labels'             => [
					'name'          => 'Products',
					'singular_name' => 'Product',
				],
				'public'             => true,
				'has_archive'        => false,
				'menu_icon'          => 'dashicons-products',
				'supports'           => [
					'title',
					'thumbnail',
					'editor',
					'featured-image',
					'custom-fields',
				],
				'taxonomies'         => [ 'category', 'vendor_code', 'manufacturer', 'origin' ],
				'show_in_rest'       => true,
				'publicly_queryable' => false,
				'rewrite'            => true,
			]
		);
	}

	/**
	 * Register the vendor codes taxonomy.
	 *
	 * @return void
	 */
	public function register_vendor_codes_taxonomy() : void {
		register_taxonomy(
			'vendor_code',
			[ 'product' ],
			[
				'labels'            => create_product_taxonomy_labels( 'Vendor Codes', 'Vendor Code' ),
				'hierarchical'      => false,
				'query_var'         => true,
				'rewrite'           => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => false,
				'show_admin_column' => false,
				'public'            => false,
				'show_in_menu'      => true,
				'show_in_rest'      => false,
				'capabilities'      => [
					'manage_terms' => 'manage_vendor_code',
					'edit_terms'   => 'edit_vendor_code',
					'delete_terms' => 'delete_vendor_code',
					'assign_terms' => 'assign_vendor_code',
				],
			]
		);
	}

	/**
	 * Register the manufacturer taxonomy.
	 *
	 * @return void
	 */
	public function register_manufacturer_taxonomy() : void {
		register_taxonomy(
			'manufacturer',
			[ 'product' ],
			[
				'labels'            => create_product_taxonomy_labels( 'Manufacturers', 'Manufacturer' ),
				'public'            => true,
				'hierarchical'      => false,
				'query_var'         => true,
				'rewrite'           => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => false,
				'show_in_rest'      => true,
				'capabilities'      => [
					'manage_terms' => 'manage_manufacturer',
					'edit_terms'   => 'edit_manufacturer',
					'delete_terms' => 'delete_manufacturer',
					'assign_terms' => 'assign_manufacturer',
				],
			]
		);
	}

	/**
	 * Register the origin CMS taxonomy
	 *
	 * @return void
	 */
	public function register_origin_cms_taxonomy() : void {
		register_taxonomy(
			'origin',
			[ 'product' ],
			[
				'labels'            => create_product_taxonomy_labels( 'Origin CMS', 'Origin CMS' ),
				'public'            => true,
				'hierarchical'      => false,
				'query_var'         => true,
				'rewrite'           => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => false,
				'show_in_rest'      => true,
				'capabilities'      => [
					'manage_terms' => 'manage_origin',
					'edit_terms'   => 'edit_origin',
					'delete_terms' => 'delete_origin',
					'assign_terms' => 'assign_origin',
				],
			]
		);
	}

	/**
	 * Enqueue scripts and styles to alter the product post type.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() : void {
		$plugin_name = basename( IDG_PRODUCTS_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_enqueue_script(
			self::POST_TYPE_SCRIPT_NAME,
			plugins_url( $plugin_name . '/dist/scripts/' . IDG_PRODUCTS_POST_TYPE_JS, $plugin_dir ),
			[],
			filemtime( IDG_PRODUCTS_DIR . '/dist/scripts/' . IDG_PRODUCTS_POST_TYPE_JS ),
			false
		);

		wp_enqueue_style(
			self::POST_TYPE_STYLE_NAME,
			plugins_url( $plugin_name . '/dist/styles/' . IDG_PRODUCTS_POST_TYPE_CSS, $plugin_dir ),
			[],
			filemtime( IDG_PRODUCTS_DIR . '/dist/styles/' . IDG_PRODUCTS_POST_TYPE_CSS )
		);
	}

	/**
	 * Prevent products from being saved without adding post title, category & manufacturer.
	 *
	 * @param object          $post - The post object.
	 * @param WP_REST_Request $request - The rest request object.
	 * @return mixed
	 */
	public function handle_validation( $post, WP_REST_Request $request ) {
		if ( is_autosave() ) {
			return $post;
		}

		$term_args            = [
			'orderby'    => 'name', 
			'hide_empty' => false,
		];
		$saved_categories     = wp_get_post_terms( $post->ID, 'category', $term_args );
		$saved_manufacturers  = wp_get_post_terms( $post->ID, 'manufacturer', $term_args );
		$data                 = $request->get_json_params();
		$validate_categories  = $this->validate_categories( $saved_categories, $data );
		$validate_manufacture = $this->validate_manufacturers( $saved_manufacturers, $data );
		if ( isset( $post->post_title ) && empty( $post->post_title ) ) {
			return new WP_Error(
				400,
				__( 'Product title can not be empty.', 'idg-products' )
			);
		}

		if ( $validate_categories ) { 
			return $validate_categories;
		}
		if ( $validate_manufacture ) { 
			return $validate_manufacture;
		}
		
		return $post;
	}

	/**
	 * Validates categories before publishing/saving product.
	 *
	 * @param array $saved_categories - Array of saved categories.
	 * @param array $data - Array of JSON response.
	 * @return mixed
	 */
	protected function validate_categories( $saved_categories, $data ) {
		// Will be true when the post is created first.
		$first_condition = ( empty( $saved_categories ) && empty( $data['categories'] ) );
		// Will be true when the post already has categories, but the user tries to deselect all and save the post.
		$second_condition = ( ! empty( $saved_categories ) && isset( $data['categories'] ) && empty( $data['categories'] ) );
		if ( $first_condition || $second_condition ) {
			return new WP_Error(
				400,
				__( 'Please select at least one category to continue.', 'idg-products' )
			);
		}
	}

	/**
	 * Validates manufacturers before publishing/saving product.
	 *
	 * @param array $saved_manufacturers - Array of saved manufacturers.
	 * @param array $data - Array of JSON response.
	 * @return mixed
	 */
	protected function validate_manufacturers( $saved_manufacturers, $data ) {
		// Will be true when the post is created first.
		$first_condition = ( empty( $saved_manufacturers ) && empty( $data['manufacturer'] ) );
		// Will be true when the post already has manufacturers, but the user tries to deselect all and save the post.
		$second_condition = ( ! empty( $saved_manufacturers ) && isset( $data['manufacturer'] ) && empty( $data['manufacturer'] ) );
		if ( $first_condition || $second_condition ) {
			return new WP_Error(
				400,
				__( 'Please select at least one manufacturer to continue.', 'idg-products' )
			);
		}
	}

	/**
	 * Add custom fields using \IDG\CustomFields
	 *
	 * @see \CustomFields
	 * @return void
	 */
	public function add_custom_fields() : void {
		$config = json_decode(
			file_get_contents( IDG_PRODUCTS_DIR . '/inc/config/product-fields.json' )
		);

		cf_register_post_type( $config, 'product', true );
	}

	/**
	 * Register scripts/styles to be used as dependencies.
	 */
	public function register_assets() : void {
		$plugin_name = basename( IDG_PRODUCTS_DIR );
		$plugin_dir  = WPCOM_VIP_CLIENT_MU_PLUGIN_DIR . '/' . $plugin_name;

		wp_register_script(
			self::SCRIPT_NAME,
			plugins_url( "{$plugin_name}/dist/scripts/" . IDG_PRODUCTS_PRODUCTS_JS, $plugin_dir ),
			[ 'wp-element', 'wp-url' ],
			filemtime( IDG_PRODUCTS_DIR . '/dist/scripts/' . IDG_PRODUCTS_PRODUCTS_JS ),
			false
		);

		wp_register_style(
			self::STYLE_NAME,
			plugins_url( "{$plugin_name}/dist/styles/" . IDG_PRODUCTS_PRODUCTS_CSS, $plugin_dir ),
			[],
			filemtime( IDG_PRODUCTS_DIR . '/dist/styles/' . IDG_PRODUCTS_PRODUCTS_CSS )
		);
	}

	/**
	 * Get vendor codes with label and value key value pairs.
	 *
	 * @return array
	 */
	public static function get_vendor_code_options() {
		$vendor_codes = get_terms(
			[
				'taxonomy'   => 'vendor_code',
				'hide_empty' => false,
			]
		);

		$options = [
			[
				'label' => __( 'Select a Code Type' ),
				'value' => false,
			],
		];

		foreach ( $vendor_codes as $vendor_code ) {
			$options[] = [
				'label' => $vendor_code->name,
				'value' => $vendor_code->slug,
			];
		}

		return $options;
	}

	/**
	 * Get vendor codes with label and value key value pairs.
	 *
	 * @return array
	 */
	public static function get_valid_vendor_codes() {
		$vendor_codes = get_terms(
			[
				'taxonomy'   => 'vendor_code',
				'hide_empty' => false,
			]
		);

		return array_map(
			function( $vendor_code ) {
				return $vendor_code->slug;
			},
			$vendor_codes
		);
	}
	
	/**
	 * This function is used to change the placeholder of title.
	 *
	 * @param string $input - Default placeholder.
	 * @return string
	 */
	public function set_custom_title( $input ) {
		if ( 'product' === get_post_type() ) {
			return __( 'Add Product Name' );
		}
		return $input;
	}
}
