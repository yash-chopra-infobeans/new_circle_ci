<?php

namespace IDG\Publishing_Flow\Data;

/**
 * Handles any Content related formatting and processing.
 */
class Content extends Data {
	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Formats the post content to be compatible with
	 * what is expected for article deployment. This may
	 * include altering blocks that require additional information.
	 *
	 * @param string $content The content to format.
	 * @return string
	 */
	public function format( $content ) {
		$parsed = parse_blocks( $content );

		foreach ( $parsed as $key => $item ) {
			$block_name = $item['blockName'];

			if ( 'core/image' === $block_name ) {
				$item = $this->replace_image_block_data( $item, 'full', false );
			}

			$parsed[ $key ] = apply_filters( "idg_publishing_flow_parse_{$block_name}", $item );
		}

		$output = '';

		foreach ( $parsed as $block ) {
			$output .= serialize_block( $block );
		}

		return $output;
	}

	/**
	 * Process the content blocks for use on the delivery site.
	 *
	 * @param string $content The content to process.
	 * @return string
	 */
	public function process_blocks( $content ) {
		$parsed = parse_blocks( $content );

		foreach ( $parsed as $key => $item ) {
			$block_name = $item['blockName'];

			switch ( $block_name ) {
				case 'core/image':
					$item = $this->replace_image_block_data( $item );
					break;
				case 'core/html':
					$item = $this->replace_html_image_block_data( $item );
					break;
				default:
					break;
			}

			$parsed[ $key ] = apply_filters( "idg_publishing_flow_parse_{$block_name}", $item );
		}

		$output = '';

		foreach ( $parsed as $block ) {
			$output .= serialize_block( $block );
		}

		return $output;
	}

	/**
	 * Get the image src from the provided html.
	 * Will only return the first src.
	 *
	 * @param string $html The html string to get the src from.
	 * @return string
	 */
	public function get_src_from_html( string $html ) {
		libxml_use_internal_errors( true );

		$dom_doc = new \DOMDocument;
		$dom_doc->loadHTML( $html );

		$xpath = new \DOMXPath( $dom_doc );

		return $xpath->query( '//img/@src' )[0]->value;
	}

	/**
	 * Takes image data that is not part of an image block,
	 * and replaces it with a new image size
	 * or even with a completely new image. Allows for copying
	 * of the provided image.
	 *
	 * @param array $block The full block array after parse.
	 * @return array
	 */
	public function replace_html_image_block_data( array $block ) {
		libxml_use_internal_errors( true );

		$html_block = $block['innerHTML'];

		$dom = new \DOMDocument;
		$dom->loadHTML( $html_block );

		foreach ( $dom->getElementsByTagName( 'img' ) as $image ) {
			$attribute = $image->getAttribute( 'src' );

			$image_id = Images::instance()->store_from_url(
				[
					'guid' => $attribute,
				]
			);

			$size = 'large';

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Core PHP methods and objects.
			if ( $image->parentNode && 'figure' === $image->parentNode->nodeName ) {
				$classes = explode( ' ', $image->parentNode->getAttribute( 'class' ) );

				$sizes = [ 'large', 'medium', 'small' ];

				foreach ( $classes as $class ) {
					if ( in_array( $class, $sizes ) ) {
						$size = $class;
						break;
					}
				}
			}

			$image_src    = wp_get_attachment_image_src( $image_id, $size );
			$image_srcset = wp_get_attachment_image_srcset( $image_id, $size );

			$image->setAttribute( 'src', $image_src[0] );
			$image->setAttribute( 'srcset', $image_srcset );
			$image->setAttribute( 'data-imageid', $image_id );
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		$updated_block = $dom->saveHTML();

		preg_match( '/<body>(?<content>.+)<\/body>/s', $updated_block, $matches );

		$block['innerHTML']       = $matches['content'];
		$block['innerContent'][0] = $matches['content'];

		return $block;
	}

	/**
	 * Replaces the image block data with a new image size
	 * or even with a completely new image. Allows for copying
	 * of the provided image.
	 *
	 * @param array   $item The full block array after parse.
	 * @param string  $size The size to be used. `block-inherit` keeps the one set within the block.
	 * @param boolean $copy Whether to copy/clone the image from it's src.
	 * @return array
	 */
	public function replace_image_block_data( array $item, string $size = 'block-inherit', $copy = true ) {
		if ( ! isset( $item['attrs']['id'] ) ) {
			return $item;
		}

		$current_image_id  = $item['attrs']['id'];
		$current_image_src = $this->get_src_from_html( $item['innerHTML'] );

		if ( 'block-inherit' === $size ) {
			$current_image_size = $item['attrs']['sizeSlug'];
		} else {
			$current_image_size = $size;
		}

		if ( $copy ) {
			$image_id = Images::instance()->store_from_url(
				[
					'ID'   => $current_image_id,
					'guid' => $current_image_src,
				]
			);
		} else {
			$image_id = $current_image_id;
		}

		$image_src = wp_get_attachment_image_src( $image_id, $current_image_size );

		$item['attrs']['id']  = $image_id;
		$item['innerHTML']    = str_replace( $current_image_src, $image_src[0], $item['innerHTML'] );
		$item['innerContent'] = str_replace( $current_image_src, $image_src[0], $item['innerContent'] );

		$item['innerHTML']    = str_replace( "wp-image-{$current_image_id}", "wp-image-{$image_id}", $item['innerHTML'] );
		$item['innerContent'] = str_replace( "wp-image-{$current_image_id}", "wp-image-{$image_id}", $item['innerContent'] );

		return $item;
	}
}
