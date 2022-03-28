<?php

namespace IDG\Third_Party\GPT;

use IDG\Third_Party\Base_Data_Layer;

/**
 * Get page leven targeting for ads.
 */
class Ad_Targeting {
	/**
	 * Order is important as they are ordered in the priority of
	 * which should match first.
	 *
	 * Future: We could potentially add this as a setting in WordPress or re-create it from
	 * a setting to make it dynamic.
	 */
	const MANUFACTURERS = [
		'Dell'              => 'dell',
		'IBM'               => 'ibm',
		'Cisco'             => 'cisco',
		'HP'                => 'hp',
		'Hewlett - Packard' => 'hp',
		'Microsoft'         => 'microsoft',
		'Lenovo'            => 'lenovo',
		'Brother'           => 'brother',
		'Espon'             => 'espon',
		'LG'                => 'lg',
		'Sony'              => 'sony',
		'Intel'             => 'intel',
		'Samsung'           => 'samsung',
		'Toshiba'           => 'toshiba',
		'Symantec'          => 'symantec',
		'Acer'              => 'acer',
		'Asus'              => 'asus',
		'Lexmark'           => 'lexmark',
		'Canon'             => 'canon',
		'Nikon'             => 'nikon',
		'Panasonic'         => 'panasonic',
		'Sharp'             => 'sharp',
		'Motorola'          => 'motorola',
		'HTC'               => 'htc',
		'Trend Micro'       => 'trendmicro',
		'Amazon'            => 'amazon',
		'Apple'             => 'apple',
		'Nokia'             => 'nokia',
		'T - mobile'        => 'tmobile',
		'Adobe'             => 'adobe',
		'Pentax'            => 'pentax',
		'Ricoh'             => 'pentax',
	];

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	public static $data = [];

	/**
	 * Get all page level add targetting.
	 *
	 * @return array
	 */
	public static function get() {
		if ( ! empty( self::$data ) ) {
			return self::$data;
		}

		$data_layer = Base_Data_Layer::$data;
		$story_type = self::get_story_type();

		$data = [
			'articleId'      => $data_layer['articleId'],
			'author'         => $data_layer['author'],
			// phpcs:ignore
			'browser'        => filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_URL ),
			'templateType'   => $data_layer['articleType'],
			'categorySlugs'  => $data_layer['categoriesSlugs'],
			'categoryIds'    => $data_layer['categoryIds'],
			'env'            => filter_input( INPUT_GET, 'env', FILTER_SANITIZE_URL ),
			'productId'      => $data_layer['prodIds'],
			'goldenIds'      => $data_layer['gtaxIdList'],
			'channel'        => $data_layer['channel'],
			'fireplace'      => $data_layer['fireplace'],
			'templateType'   => sanitize_title( $data_layer['page_type'] ),
			'type'           => $story_type ? $story_type->slug : '',
			'typeId'         => $story_type ? (string) $story_type->term_id : '',
			'sponsored'      => $data_layer['sponsorName'] ? 'true' : 'false',
			'video-autoplay' => self::video_autoplay() ? 'true' : 'false',
			'manufactuer'    => self::get_manufacturer(),
			'url'            => $data_layer['url'],
			'zone'           => self::get_zone(),
		];

		self::$data = $data;

		return $data;
	}

	/**
	 * Get the story type.
	 *
	 * @return null|object
	 */
	public static function get_story_type() {
		$post_ID = get_the_ID();

		if ( ! isset( $post_ID ) ) {
			return false;
		}

		$story_types = get_the_terms( $post_ID, 'story_types' );

		if ( $story_types && is_array( $story_types ) ) {
			return $story_types[0];
		}

		return null;
	}

	/**
	 * Is autoplay enabled?
	 *
	 * @return bool
	 */
	public static function video_autoplay() {
		$post_ID = get_the_ID();

		if ( ! isset( $post_ID ) ) {
			return false;
		}

		if ( idg_can_display_floating_video( $post_ID ) ) {
			return true;
		}

		$featured_video = get_post_meta( $post_ID, 'featured_video_id' );

		if ( isset( $featured_video ) && $featured_video ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the 'zone'.
	 *
	 * @return string
	 */
	public static function get_zone() : string {
		$data_layer = Base_Data_Layer::$data;

		$zone = '';

		if ( isset( $data_layer['content_type'] ) ) {
			$zone .= sanitize_title( $data_layer['content_type'] );
		}

		if ( isset( $data_layer['page_type'] ) ) {
			$zone .= '-' . sanitize_title( $data_layer['page_type'] );
		}

		if ( isset( $data_layer['primaryCategory'] ) ) {
			$zone .= '/' . sanitize_title( $data_layer['primaryCategory'] );
		}

		return $zone;
	}

	/**
	 * Not to be confused with product manufactuer, the ad targeting key "manufacturer"
	 * requires specific logic:
	 *
	 * - Company names in article body, and return matching manufacturer name.
	 * - Only one company per article - whatever it hits first in the article body.
	 *
	 * @return string
	 */
	public static function get_manufacturer() {
		$post = get_post();

		if ( ! isset( $post->post_content ) ) {
			return '';
		}

		$value = array_filter(
			array_keys( self::MANUFACTURERS ),
			function( $manufacturer ) use ( $post ) {
				return strpos( $post->post_content, $manufacturer );
			}
		);

		$key = array_values( $value )[0] ?? false;

		if ( $key ) {
			return self::MANUFACTURERS[ $key ];
		}

		return '';
	}
}
