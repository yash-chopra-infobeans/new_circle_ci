<?php
if ( ! function_exists( 'idg_base_theme_setup' ) ) {
	/**
	 * Registers theme support for a given features.
	 */
	function idg_base_theme_setup() {

		add_theme_support( 'title-tag' );

		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );

		register_nav_menus(
			[
				'menu-1' => esc_html__( 'Primary', 'idg-base-theme' ),
			]
		);

		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			]
		);

		add_theme_support(
			'custom-background',
			apply_filters(
				'idg_base_theme_custom_background_args',
				[
					'default-color' => 'ffffff',
					'default-image' => '',
				]
			)
		);

		add_theme_support( 'customize-selective-refresh-widgets' );

		add_theme_support(
			'custom-logo',
			[
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			]
		);
	}
}
add_action( 'after_setup_theme', 'idg_base_theme_setup' );
