<?php

use IDG\Base_Theme\Utils;

if ( ! function_exists( 'jw_player_block_render_callback' ) ) {
	/**
	 * Render callback for JW Player block.
	 *
	 * @param array $attributes block attributes.
	 * @return string
	 */
	function jw_player_block_render_callback( $attributes ) : string {
		if ( ! isset( $attributes['mediaId'] ) ) {
			return '';
		}

		$title = isset( $attributes['title'] ) ? $attributes['title'] : '';

		if ( Utils\is_amp() ) {
			ob_start();

			idg_jw_player_amp_video(
				[
					'data-media-id' => $attributes['mediaId'],
				],
			);

			$player = ob_get_contents();

			ob_end_clean();

			return $player;
		}

		$dom_id = 'id' . uniqid();

		return sprintf(
			'<style>%s</style><div id="%s" data-media-id="%s" data-title="%s" class="jwplayer"></div>',
			'#' . $dom_id . ' .jw-wrapper::before { content: "' . $title . '" !important; }',
			esc_attr( $dom_id ),
			esc_attr( $attributes['mediaId'] ),
			esc_attr( $title ),
		);
	}
}
