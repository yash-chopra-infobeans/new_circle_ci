<?php
/**
 * This file is used for management of the product post type.
 *
 * @package  Products
 */

namespace IDG\Products;

use IDG\Territories\Geolocation;
use IDG\Products\Vendors\Amazon;

/**
 * Transormers.
 */
class Transform {
	/**
	 * Transform a single product.
	 *
	 * @param object $product - The product.
	 * @return array
	 */
	public static function product( $product ) {
		$country_code = Geolocation::get_country_code() ?: 'US';

		$transformed_terms = [];

		if ( isset( $product->_embedded->{'wp:term'} ) ) {
			foreach ( $product->_embedded->{'wp:term'} as $terms ) {
				foreach ( $terms as $term ) {
					if ( ! isset( $transformed_terms[ $term->taxonomy ] ) ) {
						$transformed_terms[ $term->taxonomy ] = [];
					}

					$transformed_terms[ $term->taxonomy ][] = (object) [
						'id'   => $term->id,
						'name' => $term->name,
						'slug' => $term->slug,
					];
				}
			}
		}

		$region_info    = json_decode( $product->meta->region_info );
		$global_info    = json_decode( $product->meta->global_info );
		$geo_info       = $region_info->{$country_code} ?: [];
		$name           = $geo_info->name ?? $product->title->rendered;
		$featured_media = ! empty( $product->_embedded->{'wp:featuredmedia'} )
			? $product->_embedded->{'wp:featuredmedia'}[0]
			: null;
		$product_record = [
			'name'            => $name,
			'featured_media'  => $featured_media,
			'all_region_info' => $region_info,
			'geo_info'        => $geo_info,
			'direct_links'    => self::get_direct_links( $global_info, $country_code ),
			'reviews'         => self::transform_review_data( $product ),
			'terms'           => $transformed_terms,
			'labels'          => self::get_labels( $country_code ),
		];

		$product_record['attributes'] = [
			'data-vars-product-name' => $name,
			'data-vars-product-id'   => "$product->id",
			'data-vars-category'     => Data_Layer::get_product_terms( [ $product_record ], 'category' ),
			'data-vars-manufacturer' => Data_Layer::get_product_terms( [ $product_record ], 'manufacturer' ),
			'data-vars-vendor'       => Data_Layer::get_product_vendors( [ $product_record ] ),
			'data-vars-po'           => Data_Layer::get_product_vendors( [ $product_record ], false ),
		];

		return $product_record;
	}

	/**
	 * Get run time review data.
	 *
	 * @param object|null $product - The Product.
	 * @return array
	 */
	public static function transform_review_data( $product = null ) {
		$is_content_hub = \IDG\Publishing_Flow\Sites::is_origin();
		if ( ! $is_content_hub ) {
			if ( $product->meta->reviews ) {
				return json_decode( $product->meta->reviews, true );
			} else {
				return [];
			}
		}
		$reviews = json_decode( $product->meta->reviews );

		$data = [];
		foreach ( $reviews as $post_id => $review_data ) {
			$permalink = get_permalink( $post_id );

			if ( ! $permalink ) {
				continue;
			}

			$data[ $post_id ] = array_merge(
				(array) $review_data,
				[
					'id'            => $post_id,
					'title'         => get_the_title( $post_id ),
					'status'        => get_post_status( $post_id ),
					'permalink'     => get_permalink( $post_id ),
					'formattedTime' => get_the_date( 'M j, Y g:i a T', $post_id ),
				]
			);
		}
		return (array) $data;
	}

	/**
	 * Get geolocated direct vendor links.
	 *
	 * @param object $global_info - A product's global info.
	 * @param string $country_code - The users geo.
	 * @return array
	 */
	public static function get_direct_links( $global_info, $country_code ) : array {
		$links = [];

		foreach ( $global_info->purchase_options->vendor_links ?? [] as $link ) {
			// User has selected 'All'.
			if ( empty( $link->territory ) ) {
				$links[] = $link;
				continue;
			}

			if ( strtoupper( $link->territory ) === $country_code ) {
				$links[] = $link;
			}
		}

		return $links;
	}

	/**
	 * Get labels.
	 *
	 * @param string $country_code - The geolocation.
	 * @return array
	 */
	public static function get_labels( $country_code ) {
		$rrp = 'GB' === $country_code ? __( 'RRP', 'idg-base-theme' ) : __( 'MSRP', 'idg-base-theme' );

		return (object) [
			'rrp_field_label'         => $rrp,
			'best_prices_field_label' => __( 'Best Prices Today', 'idg-base-theme' ),
		];
	}
}
