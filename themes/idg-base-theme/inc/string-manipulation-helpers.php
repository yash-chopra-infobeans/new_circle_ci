<?php
if ( ! function_exists( 'pascal' ) ) {
	/**
	 * Convert a string to PascalCase
	 *
	 * @since 1.0.0
	 * @param  string $string  the string to texturise.
	 * @return string
	 */
	function pascal( $string = '' ) {
		$string = preg_replace( '/[\'"]/', '', $string );
		$string = preg_replace( '/[^a-zA-Z0-9]+/', ' ', $string );
		return preg_replace( '/\s+/', '', ucwords( $string ) );
	}
}

if ( ! function_exists( 'camel' ) ) {
	/**
	 * Convert a string to camelCase
	 *
	 * @since 1.0.0
	 * @param  string $string  the string to texturise.
	 * @return string
	 */
	function camel( $string = '' ) {
		return lcfirst( pascal( $string ) );
	}
}

if ( ! function_exists( 'format_for_aria_label' ) ) {
	/**
	 * Format a string for use in the aria-label attribute.
	 * Strips tags, whitespace, and normalises text transformations.
	 * This will break poorly-written acronymns, but it fixes uppercase
	 * words being read by some screen readers as acronyms.
	 *
	 * @param string $string the string to normalise.
	 * @return string
	 */
	function format_for_aria_label( $string = '' ) {
		return ucwords( strtolower( normalize_whitespace( wp_strip_all_tags( $string ) ) ) );
	}
}

if ( ! function_exists( 'spaceless' ) ) {
	/**
	 * Spaceless functions removes whitespace characters between
	 * HTML tags. Whitespaces within HTML tags or in a plain text
	 * are always left untouched.
	 *
	 * This should never be used for content compression (if you need
	 * to achieve content compress, please use gzip). Spaceless functions
	 * are mainly used to strip out extra whitespace characters between
	 * HTML tags in order to avoid some browser rendering quirks
	 * (e.g. newlines between inline-block elements).
	 */

	/**
	 * Starts capturing an output to be cleaned from whitespace characters between HTML tags.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function spaceless() {
		ob_start();
	}
}

if ( ! function_exists( 'endspaceless' ) ) {
	/**
	 * End output buffering, strip whitespace between HTML tags,
	 * and echo result.
	 *
	 * @since 1.0.0
	 * @param boolean $echo Should function echo, returns if false.
	 * @return string
	 */
	function endspaceless( $echo = true ) {
		$data = trim( preg_replace( '~>[\b\s]*~', '>', ob_get_clean() ) );

		if ( ! $echo ) {
			return $data;
		}

		echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
