<?php
if ( ! function_exists( 'idg_asset' ) ) {
	/**
	 * Require an asset.
	 *
	 * @param string $name the file name.
	 *
	 * @return void
	 */
	function idg_asset( $name = '' ) {
		$file = '';

		// attempt to get a child theme asset if is a child theme first.
		if ( is_child_theme() ) {
			$file = get_stylesheet_directory() . '/dist/static/' . $name;
		}

		// if no asset or not a child theme attempt to get asset.
		if ( ! file_exists( $file ) ) {
			$file = get_template_directory() . '/dist/static/' . $name;
		}

		if ( ! file_exists( $file ) ) {
			echo '';
		}

		// @codingStandardsIgnoreLine
		echo file_get_contents( $file );
	}
}

if ( ! function_exists( 'get_idg_asset' ) ) {
	/**
	 * Require an asset.
	 *
	 * @param string $name the file name.
	 *
	 * @return string
	 */
	function get_idg_asset( $name = '' ) {
		$file = '';

		// attempt to get a child theme asset if is a child theme first.
		if ( is_child_theme() ) {
			$file = get_stylesheet_directory() . '/dist/static/' . $name;
		}

		// if no asset or not a child theme attempt to get asset.
		if ( ! file_exists( $file ) ) {
			$file = get_template_directory() . '/dist/static/' . $name;
		}

		if ( ! file_exists( $file ) ) {
			return '';
		}

		// @codingStandardsIgnoreLine
		return file_get_contents( $file );
	}
}

if ( ! function_exists( 'idg_child_asset' ) ) {
	/**
	 * Require a child theme asset.
	 *
	 * @param string $name the file name.
	 *
	 * @return void
	 */
	function idg_child_asset( $name = '' ) {
		$file = get_stylesheet_directory() . '/dist/static/' . $name;

		if ( ! file_exists( $file ) ) {
			echo '';
		}

		// @codingStandardsIgnoreLine
		echo file_get_contents( $file );
	}
}
