<?php

namespace IDG\Golden_Taxonomy\Utils;

/**
 * Get plugin template.
 *
 * @param string $template  Name or path of the template within /templates folder without php extension.
 * @param array  $variables pass an array of variables you want to use in template.
 * @param bool   $echo      Whether to echo out the template content or not.
 *
 * @return string|void Template markup.
 */
function get_template( $template, $variables = [], $echo = false ) {

	$template_file = sprintf( '%1$s/templates/%2$s.php', IDG_GOLDEN_TAXONOMY_DIR, $template );

	if ( ! file_exists( $template_file ) ) {
		return '';
	}

	if ( ! empty( $variables ) && is_array( $variables ) ) {
		extract( $variables, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Used as an exception as there is no better alternative.
	}

	ob_start();

	include $template_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

	$markup = (string) ob_get_clean();

	if ( ! $echo ) {
		return $markup;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaping is done in templates.
}

/**
 * Map array by index.
 *
 * @param string $index - The index key.
 * @param array  $array - The array to map.
 * @return array
 */
function map_by_index( string $index = '', array $array = [] ) : array {
	return array_map(
		function( $item ) use ( $index ) {
			return $item[ $index ];
		},
		array_values( $array )
	);
}
