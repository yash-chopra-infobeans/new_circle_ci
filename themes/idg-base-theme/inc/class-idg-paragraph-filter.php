<?php

if ( class_exists( 'IDG_Paragraph_Filter' ) ) {
	return new IDG_Paragraph_Filter();
}

/**
 * Logic to insert content dynamically after paragraphs within article content.
 *
 * This is used by various different features in different
 * plugins such as JW Player, Nativo, Ads etc.
 */
class IDG_Paragraph_Filter {
	const FILTER        = 'idg_article_paragraph';
	const BLOCKS        = [ 'core/paragraph' ];
	const LEGACY_BLOCKS = [ 'core/html' ];

	/**
	 * The paragraph count.
	 *
	 * @var integer
	 */
	private static $count = 0;

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_filter( 'render_block', [ $this, 'render_block' ], 10, 2 );
	}

	/**
	 * Determine if a block is an inner block - requires modifications to WP_Block_Parser.
	 *
	 * @see /inc/class-idg-block-parser.php
	 * @param array $block - The block.
	 * @return boolean
	 */
	public function is_inner_block( $block ) {
		return isset( $block['attrs']['isInnerBlock'] ) && $block['attrs']['isInnerBlock'];
	}

	/**
	 * Does the block contain legacy content?
	 *
	 * @param array $block - The block.
	 * @return boolean
	 */
	public function is_legacy_content( $block ) {
		return ! $this->is_inner_block( $block ) && in_array( $block['blockName'], self::LEGACY_BLOCKS, true );
	}

	/**
	 * Is the block a main paragraph?
	 *
	 * @param array $block - The block.
	 * @return boolean
	 */
	public function is_main_paragraph( $block ) {
		return ! $this->is_inner_block( $block ) && in_array( $block['blockName'], self::BLOCKS, true );
	}

	/**
	 * Apply the filter content if required.
	 *
	 * @param string $block_content - The block content.
	 * @param array  $block - The block.
	 * @return string
	 */
	public function render_block( $block_content, $block ) : string {
		if ( get_post_type() !== 'post' ) {
			return $block_content;
		}

		if ( is_feed() ) {
			return $block_content;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $block_content;
		}

		if ( $this->is_legacy_content( $block ) ) {
			return $this->after_every_paragraph(
				$block_content
			);
		}

		if ( ! $this->is_main_paragraph( $block ) ) {
			return $block_content;
		}

		return $this->apply_content_filter( $block_content );
	}

	/**
	 * Increment the count and apply the content filter.
	 *
	 * @param string $content - The content to filter.
	 * @return string
	 */
	public function apply_content_filter( string $content ) : string {
		self::$count++;

		return apply_filters( self::FILTER, $content, self::$count );
	}

	/**
	 * Fire callback after every paragraph.
	 *
	 * @param string $content - The content.
	 * @return string
	 */
	public function after_every_paragraph( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		/**
		 * Later in the parsing of html, DOMDocument::loadHtml with the LIBXML_HTML_NOIMPLIED
		 * flag expects the injested html to contain a single root element. This isn't alwaus
		 * a guarentee so we wrap the html in an element to ensure we have that single root.
		 */
		$html = "<div class=\"legacy_content\">$content</div>";

		$dom = new \DOMDocument();

		libxml_use_internal_errors( true );
		$dom->loadHTML(
			mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ),
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		foreach ( $dom->getElementsByTagName( 'p' ) as $p ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( 'div' !== $p->parentNode->tagName ) {
				continue;
			}

			$fragment = $dom->createDocumentFragment();
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$fragment->appendXML( $this->apply_content_filter( $dom->saveHTML( $p ) ) );
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$p->parentNode->replaceChild( $fragment, $p );
		}

		return $dom->saveHTML();
	}
};
