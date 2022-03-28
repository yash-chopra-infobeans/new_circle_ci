<?php

namespace IDG\Configuration\Plugins\Publishing_Flow;

use IDG\Publishing_Flow\API\Data\Users;
use IDG\Publishing_Flow\Data\Authors;
use IDG\Publishing_Flow\Data\Images;

class Taxonomies {
	/**
	 * Add the required hooks.
	 */
	public function __construct() {
		add_filter( 'idg_publishing_flow_get_term_meta_category', [ $this, 'remove_meta'], 10, 1);
		add_filter( 'idg_publishing_flow_get_term_meta', [ $this, 'format_term_meta' ], 10, 1 );
		add_action( 'idg_publishing_flow_insert_term_meta', [ $this, 'insert_term_meta' ], 10, 2 );
		add_action( 'idg_publishing_flow_after_deploy_article', [ $this, 'order_taxonomy' ], 10, 2 );
	}

	/**
	 * Maps the metadata for the category order with
	 * the new term id values.
	 *
	 * @param int $post_id The post id to alter the mapping for.
	 * @return void
	 */
	public function order_taxonomy( $post_id, $body ) {
		if( ! isset($body['meta']['_idg_post_categories'] ) ) {
			return;
		}

		if( is_array( $body['meta']['_idg_post_categories'][0] ) ) {
			$category_meta = $body['meta']['_idg_post_categories'][0];
		} else {
			$category_meta = $body['meta']['_idg_post_categories'];
		}

		$categories    = wp_get_post_categories( $post_id );

		foreach( $categories as $category_id ) {
			$content_hub_id = get_term_meta( $category_id, 'content_hub_id', true );

			$key = array_search( $content_hub_id, $category_meta );

			if($key === false) {
				continue;
			}

			$category_meta[ $key ] = $category_id;
		}

		update_post_meta( $post_id, '_idg_post_categories', $category_meta );
	}

	/**
	 * Handle the removal of taxonomy meta on deploy.
	 *
	 * @param array $meta The meta values prior to filter.
	 * @return array
	 */
	public function remove_meta( array $meta ) : array {
		unset( $meta['archive_page'] );

		return $meta;
	}

	/**
	 * Formats the passed meta where there are authors,
	 * logos and brand images.
	 *
	 * @param array $meta The post meta that is to be formatted.
	 * @return array
	 */
	public function format_term_meta( $meta ) {
		if ( isset( $meta['author'] ) ) {
			$meta['author'] = Authors::instance()->format( $meta['author'] );
		}

		if ( isset( $meta['logo'] ) ) {
			$meta['logo'] = Images::instance()->format( $meta['logo'] );
		}

		if ( isset( $meta['brand_image'] ) ) {
			$meta['brand_image'] = Images::instance()->format( $meta['brand_image'] );
		}

		return $meta;
	}

	/**
	 * Handle the insertion of term meta from collection
	 * of data.
	 *
	 * @param array $meta The meta data to be inserted.
	 * @param string $term The term which meta being inserted against.
	 * @return void
	 */
	public function insert_term_meta( $meta, $term ) {
		if(!is_array($meta)) {
			return $meta;
		}

		foreach( $meta as $key => $value ) {
			$name = preg_replace( '/[\W\d_]/i', '', $key );

			if ( method_exists( $this, "insert_meta_$name" ) ) {
				$this->{"insert_meta_$name"}( $value, $term );
			} else {
				$this->insert_meta_default( $key, $value, $term );
			}
		}

		return $meta;
	}

	/**
	 * The fallback/default handler for inserting term meta.
	 *
	 * @param string $key The meta key which to insert against.
	 * @param mixed $value The value being inserted.
	 * @param mixed $term The term to attach to.
	 * @return void
	 */
	private function insert_meta_default( $key, $value, $term ) {
		update_term_meta( $term['term_id'], $key, $value );
	}

	/**
	 * Insertion handler for all sponsorship meta.
	 *
	 * @param string $sponsor The sponsor id to attach.
	 * @param mixed $term The term to attach to.
	 * @return void
	 */
	private function insert_meta_sponsorship( $sponsor, $term ) {
		$existing = get_terms( [
			'taxonomy'   => 'sponsorships',
			'hide_empty' => false,
			'meta_key'   => 'content_hub_id',
			'meta_value' => $sponsor,
		] );

		if ( ! is_wp_error( $existing ) || ! empty( $existing ) ) {
			update_term_meta( $term['term_id'], 'sponsorship', $existing[0]->term_id );
		}
	}

	/**
	 * Insertion handler for all author meta. Will attempt
	 * author creation before insertion.
	 *
	 * @param array $authors The authors to attach.
	 * @param mixed $term The term to attach to.
	 * @return void
	 */
	private function insert_meta_author( $authors, $term ) {
		$user_ids = Users::instance()->create( $authors );

		foreach( $user_ids as $user_id ) {
			update_term_meta( $term['term_id'], 'author', $user_id );
		}
	}

	/**
	 * Insertion handler for all image meta. Will attempt
	 * image creation before insertion.
	 *
	 * @param array $image The image to attach.
	 * @param mixed $term The term to attach to.
	 * @return void
	 */
	private function insert_meta_logo( $image, $term ) {
		if ( empty( $image ) ) {
			$image_id = '';
		} else {
			$image_id = Images::instance()->store_from_url( $image );
		}

		update_term_meta( $term['term_id'], 'logo', $image_id );
	}

	/**
	 * Insertion handler for all brand images meta. Will attempt
	 * image creation before insertion.
	 *
	 * @param array $image The image to attach.
	 * @param mixed $term The term to attach to.
	 * @return void
	 */
	private function insert_meta_brandimage( $image, $term ) {
		if ( empty( $image ) ) {
			$image_id = '';
		} else {
			$image_id = Images::instance()->store_from_url( $image );
		}

		update_term_meta( $term['term_id'], 'brand_image', $image_id );
	}
}
