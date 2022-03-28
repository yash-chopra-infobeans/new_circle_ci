<?php
if ( ! function_exists( 'register_idg_network_nav' ) ) {
	/**
	 * Registers 'IDG Network' nav.
	 */
	function register_idg_network_nav() {
		register_nav_menu( 'idg-network', __( 'IDG Network', 'idg-base-theme' ) );
	}
}

add_action( 'after_setup_theme', 'register_idg_network_nav' );

if ( ! function_exists( 'register_hot_topics_nav' ) ) {
	/**
	 * Registers 'Hot Topcs' nav.
	 */
	function register_hot_topics_nav() {
		register_nav_menu( 'hot-topics', __( 'Hot Topics', 'idg-base-theme' ) );
	}
}

add_action( 'after_setup_theme', 'register_hot_topics_nav' );

if ( ! function_exists( 'register_footer_primary_nav' ) ) {
	/**
	 * Registers 'Footer primary' nav.
	 */
	function register_footer_primary_nav() {
		register_nav_menu( 'footer-primary', __( 'Footer Primary', 'idg-base-theme' ) );
	}
}

add_action( 'after_setup_theme', 'register_footer_primary_nav' );

if ( ! function_exists( 'register_footer_secondary_nav' ) ) {
	/**
	 * Registers 'Footer secondary' nav.
	 */
	function register_footer_secondary_nav() {
		register_nav_menu( 'footer-secondary', __( 'Footer Secondary', 'idg-base-theme' ) );
	}
}

add_action( 'after_setup_theme', 'register_footer_secondary_nav' );
