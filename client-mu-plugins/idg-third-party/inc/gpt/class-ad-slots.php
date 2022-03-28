<?php

namespace IDG\Third_Party\GPT;

use IDG\Base_Theme\Templates;
use IDG\Third_Party\Settings;

/**
 * Define templates and conditions.
 */
class Ad_Slots {
	/**
	 * The ad slot name for the loaded page.
	 *
	 * @var string|null
	 */
	public static $ad_slot_name = null;

	/**
	 * Add actions/filters.
	 */
	public function __construct() {
		// "Page" ads.
		add_action( 'idg_after_header', [ $this, 'render_banner_ad' ] );
		add_filter( 'render_block', [ $this, 'render_hero_banner_ad' ], 10, 2 );
		add_action( 'idg_right_rail', [ $this, 'render_right_rail_ad' ] );
		add_action( 'idg_before_footer', [ $this, 'render_footer_ad' ] );

		// "Out of page" ads.
		add_action( 'wp_footer', [ $this, 'render_out_of_page_ads' ] );

		// "Content" ads.
		add_action( 'idg_render_article_feed_item', [ $this, 'render_article_feed_ad' ], 10, 2 );
		add_action( 'idg_article_paragraph', [ $this, 'render_ad_every_nth_paragraph' ], 10, 2 );
		add_action( 'idg_after_product_chart_item', [ $this, 'render_product_chart_ad' ] );
	}

	/**
	 * Render the banner below the nav.
	 *
	 * @return void
	 */
	public function render_banner_ad() {
		if ( Templates\home() ) {
			return;
		}

		Ad_Templates::render( 'banner' );
	}

	/**
	 * Render the banner on the home page beneath the hero block.
	 *
	 * @param string $block_content - The current block content.
	 * @param array  $block - The corrent block.
	 * @return string
	 */
	public function render_hero_banner_ad( $block_content, $block ) {
		if ( 'idg-base-theme/hero' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( ! Templates\home() ) {
			return $block_content;
		}

		ob_start();

		Ad_Templates::render( 'banner' );

		return $block_content . ob_get_clean();
	}

	/**
	 * Render footer ad.
	 *
	 * @return void
	 */
	public function render_footer_ad() {
		Ad_Templates::render( 'footer' );
	}

	/**
	 * Render right rail ad.
	 *
	 * @return void
	 */
	public function render_right_rail_ad() {
		Ad_Templates::render( 'right_rail' );
	}

	/**
	 * Render out of page ads.
	 *
	 * @return void
	 */
	public function render_out_of_page_ads() {
		Ad_Templates::render( 'overlay' );
		Ad_Templates::render( 'skin' );
		Ad_Templates::render( 'bouncex' );
	}

	/**
	 * Render an article feed ad
	 *
	 * @param int $index - The index of the article feed item.
	 * @param int $offset - The current page offeset.
	 * @return void
	 */
	public function render_article_feed_ad( int $index, int $offset ) {
		$slot          = self::get_slot_config( 'article' );
		$insert_after  = (int) $slot['insert_after_article'] ?: 4;
		$insert_offset = (int) $slot['insert_after_article_offset'] ?: 6;
		$curr_index    = ( $index + 1 ) + $offset;

		if ( $curr_index < $insert_offset ) {
			return;
		}

		if ( $insert_offset === $curr_index ) {
			Ad_Templates::render( 'article' );
			return;
		}

		if ( 0 === ( ( $insert_after + $insert_offset ) - $curr_index ) % $insert_after ) {
			Ad_Templates::render( 'article' );
		}
	}

	/**
	 * Render ad every nth paragraph.
	 *
	 * @param string  $paragraph_content - The current paragraph content.
	 * @param integer $count - The current paragraph count.
	 * @return string
	 */
	public function render_ad_every_nth_paragraph( string $paragraph_content, int $count ) : string {
		global $post;

		if ( idg_can_display_floating_video( $post->ID ) ) {
			$video_player_inserted_after = (int) Settings::get( 'jw_player' )['config']['insert_after_p'] ?: 4;

			if ( $video_player_inserted_after === $count ) {
				return $paragraph_content;
			}
		}

		$slot         = $this->get_slot_config( 'article' );
		$insert_after = (int) $slot['insert_after_p'] ?: 4;

		if ( 0 !== ( $count % $insert_after ) ) {
			return $paragraph_content;
		}

		ob_start();

		Ad_Templates::render( 'article' );

		return $paragraph_content . ob_get_clean();
	}

	/**
	 * Render ad after product chart item.
	 *
	 * @return void
	 */
	public function render_product_chart_ad() {
		Ad_Templates::render( 'article' );
	}

	/**
	 * Get config for a slot template.
	 *
	 * @param string $template - The template name.
	 * @return array
	 */
	public static function get_slot_config( string $template ) {
		$slots      = Settings::get( 'gpt' )['config']['slots'];
		$config_key = array_search( $template, array_column( $slots, 'template' ), true );
		return $slots[ $config_key ];
	}

	/**
	 * Create at slot name.
	 *
	 * @return string
	 */
	public static function create_ad_slot_name() : string {
		global $template;

		if ( self::$ad_slot_name ) {
			return self::$ad_slot_name;
		}

		if ( Templates\home() ) {
			return 'homepage_door';
		}

		if ( Templates\index() ) {
			return 'page_door';
		}

		if ( Templates\archive() ) {
			return sanitize_title( get_the_archive_title() ) . '_door';
		}

		if ( Templates\article() ) {
			$post       = get_post();
			$categories = get_the_terms( $post->ID, 'category' );

			if ( ! empty( $categories ) ) {
				return $categories[0]->slug . '_section';
			}
		}

		self::$ad_slot_name = str_replace( '.php', '', basename( $template ) );

		return self::$ad_slot_name;
	}
}
