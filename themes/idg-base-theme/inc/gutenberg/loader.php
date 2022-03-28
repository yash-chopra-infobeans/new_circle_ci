<?php
/**
 * Files for registering blocks and their attributes
 *
 * @package frontend
 */

if ( ! function_exists( 'gutenberg_idg_assets' ) ) {
	/**
	 * Enqueues gutenberg assets
	 */
	function gutenberg_idg_assets() {
		wp_enqueue_script(
			'packages',
			get_template_directory_uri() . '/dist/scripts/' . IDG_BASE_THEME_PACKAGES_JS,
			[
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-edit-post',
				'wp-data',
				'wp-date',
			],
			'1.1.0',
			false
		);

		wp_register_script(
			'gutenberg_idg_assets',
			get_template_directory_uri() . '/dist/scripts/' . IDG_BASE_THEME_GUTENBERG_JS,
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'idg-products', 'idg-asset-manager-gutenberg-script' ],
			filemtime( get_template_directory() . '/dist/scripts/' . IDG_BASE_THEME_GUTENBERG_JS ),
			false
		);

		wp_localize_script(
			'gutenberg_idg_assets',
			'baseTheme',
			[
				'root'      => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'is_origin' => IDG\Publishing_Flow\Sites::is_origin(),
			]
		);

		wp_enqueue_style(
			'admin-styles',
			get_template_directory_uri() . '/dist/styles/' . IDG_BASE_THEME_GUTENBERG_CSS,
			[ 'idg-products' ],
			filemtime( get_template_directory() . '/dist/styles/' . IDG_BASE_THEME_GUTENBERG_CSS ),
			'all'
		);

		wp_localize_script(
			'gutenberg_idg_assets',
			'productsSettings',
			[
				'theme_assets_directory' => get_template_directory_uri(),
			]
		);

		wp_enqueue_script( 'gutenberg_idg_assets' );
	}
}
add_action( 'enqueue_block_editor_assets', 'gutenberg_idg_assets' );

require_once plugin_dir_path( __FILE__ ) . '/blocks/block-feed-utils.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/hero.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/article-feed.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/jw-player.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/review.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/price-comparison.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/product-chart.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/product-widget.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/tab-navigation.php';
require_once plugin_dir_path( __FILE__ ) . '/blocks/card-block.php';

if ( ! function_exists( 'register_php_rendered_blocks' ) ) {
	/**
	 * Register the block via php so it can be rendered with php.
	 */
	function register_php_rendered_blocks() {
		register_block_type(
			'idg-base-theme/hero',
			[
				'render_callback' => 'idg_render_hero',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/article-feed',
			[
				'render_callback' => 'idg_render_article_feed',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/jwplayer',
			[
				'render_callback' => 'jw_player_block_render_callback',
				'editor_script'   => 'gutenberg_idg_assets',
				'attributes'      => [
					'id'       => [
						'type' => 'integer',
					],
					'mediaId'  => [
						'type' => 'string',
					],
					'domId'    => [
						'type' => 'string',
					],
					'title'    => [
						'type' => 'string',
					],
					'playerId' => [
						'type' => 'string',
					],
				],
			]
		);
		register_block_type(
			'idg-base-theme/review-block',
			[
				'render_callback' => 'idg_render_review',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/price-comparison-block',
			[
				'attributes'      => [
					'productId'     => [
						'type'    => 'number',
						'default' => 0,
					],
					'linksInNewTab' => [
						'type'    => 'boolean',
						'default' => true,
					],
					'instanceId'    => [
						'type'    => 'number',
						'default' => 0,
					],
					'footerText'    => [
						'type'    => 'string',
						'default' => __( 'Price comparison from over 24,000 stores worldwide', 'idg-base-theme' ),
					],
				],
				'render_callback' => 'idg_render_price_comparison',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/product-chart-block',
			[
				'attributes'      => [
					'productData'   => [
						'type'    => 'array',
						'default' => '[]',
					],
					'isShowingRank' => [
						'type'    => 'boolean',
						'default' => true,
					],
					'linksInNewTab' => [
						'type'    => 'boolean',
						'default' => true,
					],
				],
				'render_callback' => 'idg_render_product_chart',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/product-widget-block',
			[
				'attributes'      => [
					'productId'       => [
						'type'    => 'number',
						'default' => 0,
					],
					'blockTitle'      => [
						'type'    => 'string',
						'default' => '',
					],
					'productImage'    => [
						'type'    => 'number',
						'default' => 0,
					],
					'imageFromOrigin' => [
						'type'    => 'boolean',
						'default' => false,
					],
					'linksInNewTab'   => [
						'type'    => 'boolean',
						'default' => true,
					],
				],
				'render_callback' => 'idg_render_product_widget',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/product-chart-item',
			[
				'attributes'    => [
					'rank'             => [
						'type'    => 'number',
						'default' => 0,
					],
					'productId'        => [
						'type'    => 'number',
						'default' => 0,
					],
					'productTitle'     => [
						'type'    => 'string',
						'default' => '',
					],
					'titleOverride'    => [
						'type'    => 'boolean',
						'default' => false,
					],
					'productContent'   => [
						'type'    => 'string',
						'default' => '',
					],
					'productRating'    => [
						'type'    => 'number',
						'default' => 0,
					],
					'ratingOverride'   => [
						'type'    => 'boolean',
						'default' => false,
					],
					'productImageSize' => [
						'type'    => 'string',
						'default' => 'medium',
					],
					'productImage'     => [
						'type'    => 'number',
						'default' => 0,
					],
					'imageFromOrigin'  => [
						'type'    => 'boolean',
						'default' => false,
					],
					'activeReview'     => [
						'type'    => 'number',
						'default' => 0,
					],
				],
				'editor_script' => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/tab-navigation',
			[
				'render_callback' => 'idg_render_tab_navigation',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/card-block',
			[
				'render_callback' => 'idg_render_card_block',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
		register_block_type(
			'idg-base-theme/sponsored-embed',
			[
				'render_callback' => 'idg_base_theme_sponsored_embed_wrapper',
				'editor_script'   => 'gutenberg_idg_assets',
			]
		);
	}
}

add_action( 'init', 'register_php_rendered_blocks' );
