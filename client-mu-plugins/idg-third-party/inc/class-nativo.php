<?php

namespace IDG\Third_Party;

use function IDG\Base_Theme\Utils\is_amp;

/**
 * Nativo integration.
 */
class Nativo {
	/**
	 * Nativo settings defined in third party settings.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Add actions
	 */
	public function __construct() {
		$this->settings = Settings::get( 'nativo' );

		add_action( 'idg_render_article_feed_item', [ $this, 'render_nativo_in_article_feed' ], 10, 3 );
		add_filter( 'idg_article_paragraph', [ $this, 'render_after_paragraph_n' ], 10, 2 );
	}

	/**
	 * Add nativo element after nth paragraph.
	 *
	 * Note: Only there should only be nativo placement wihtin an article.
	 *
	 * @param string  $paragraph_content - The current paragraph content.
	 * @param integer $count - The current paragraph count.
	 * @return string
	 */
	public function render_after_paragraph_n( string $paragraph_content, int $count ) : string {
		$n = (int) $this->settings['config']['insert_after_p'] ?: 10;

		if ( $count !== $n ) {
			return $paragraph_content;
		}

		ob_start();

		$this->render_nativo_element();

		return $paragraph_content . ob_get_clean();
	}


	/**
	 * Render nativo after every nth article item.
	 *
	 * Note: There can be multiple nativo placements within an article feed.
	 *
	 * @param int $index - The index of the article feed item.
	 * @param int $offset - The current page offeset.
	 * @return void
	 */
	public function render_nativo_in_article_feed( int $index, int $offset ) {
		$insert_offset = $this->settings['config']['insert_after_article_offset'] ?: 4;
		$insert_after  = $this->settings['config']['insert_after_article'] ?: 20;
		$curr_index    = ( $index + 1 ) + $offset;

		if ( $curr_index < $insert_offset ) {
			return;
		}

		if ( $insert_offset === $curr_index ) {
			$this->render_nativo_element();
			return;
		}

		if ( 0 === ( ( $insert_after + $insert_offset ) - $curr_index ) % $insert_after ) {
			$count = ( $curr_index - $insert_offset ) / $insert_after;
			$this->render_nativo_element( $count + 1 );
		}
	}

	/**
	 * Render a nativo element.
	 *
	 * @param int $count - The count.
	 * @return void.
	 */
	public function render_nativo_element( int $count = 1 ) {
		if ( is_amp() ) {
			$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

			printf(
				'<amp-ad type="nativo" width="400" height="350" layout="responsive"
				data-request-url="https://amp.%s/amp/nativo" data-block-on-consent>
				</amp-ad>',
				esc_attr( $host )
			);

			return;
		}

		printf(
			'<div id="nativo%s" class="nativo"></div>',
			esc_attr( $count )
		);
	}
}
