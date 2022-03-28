<?php

namespace IDG\Configuration;

use IDG\Base_Theme\Templates;
use function IDG\Base_Theme\Utils\idg_get_canonical_url;
use IDG\Products\Article;
use IDG\Products\Reviews;

/**
 * Class for setting page structured data.
 *
 * Structured data docs: https://developers.google.com/search/docs/guides/intro-structured-data
 */
class Structured_Data {
	const ALLOWED_BLOCKS = [
		'core/html',
		'core/paragraph',
		'core/heading',
	];
	const EXCLUDE_BLOCKS = [
		'idg-base-theme/product-chart-block',
		'idg-base-theme/review-block',
		'idg-base-theme/price-comparison-block',
	];

	/**
	 * Add all hooks required for structured data.
	 */
	public function __construct() {
		// Add stuctured data to non-amp pages.
		add_action( 'wp_head', [ $this, 'add_structured_data' ] );
		// Add structured data to amp pages.
		add_filter( 'amp_schemaorg_metadata', [ $this, 'add_structured_data' ] );
	}

	/**
	 * Get Organization schema.
	 *
	 * @return array
	 */
	public function get_organization() : array {
		$social_links        = cf_get_value( 'global_settings', 'navigation' );
		$social_facebook_url = $social_links['navigation_social_icons']['nav_social_facebook_url'] ?: '';
		$social_twitter_url  = $social_links['navigation_social_icons']['nav_social_twitter_url'] ?: '';
		$social_youtube_url  = $social_links['navigation_social_icons']['nav_social_youtube_url'] ?: '';
		$publisher_logo      = cf_get_value( 'global_settings', 'structured_data', 'struc_data' )['structured_data_publisher_logo'];

		if ( ! $publisher_logo ) {
			$publisher_logo = '';
		}

		return [
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => get_bloginfo( 'name' ),
			'url'      => get_bloginfo( 'url' ),
			'logo'     => $publisher_logo,
			'sameAs'   => [
				$social_facebook_url,
				$social_twitter_url,
				$social_youtube_url,
			],
		];
	}

	/**
	 * Get page breadcrumb structured data.
	 *
	 * @return array
	 */
	public function get_breadcrumbs() : array {
		$type = '';

		if ( Templates\article() ) {
			$type = 'single';
		}

		if ( Templates\archive() ) {
			$type = 'archive';
		}

		// Handles breadcrumbs for post (articles), post type and archive pages.
		$breadcrumbs = ! empty( $type ) ? idg_get_breadcrumbs( $type ) : [];

		// Handles breadcrumbs for page post type.
		if ( empty( $breadcrumbs ) && is_page() ) {
			$post_id = get_the_ID();
			$post    = get_post( $post_id );

			$breadcrumbs = [
				[
					'url'   => esc_url( get_home_url() ),
					'label' => esc_html( 'Home' ),
				],
				[
					'url'   => get_permalink( $post ),
					'label' => $post->post_title,
				],
			];
		}

		$structured_data = [
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => [],
		];

		foreach ( $breadcrumbs as $index => $breadcrumb ) {
			$structured_data['itemListElement'][] = [
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $breadcrumb['label'],
				'item'     => $breadcrumb['url'],
			];
		}

		return $structured_data;
	}

	/**
	 * Get vendor code from vendor codes array.
	 *
	 * @param string $code code in array that your looking for.
	 * @param array  $vendor_codes array of vendor codes.
	 * @return string
	 */
	public function get_code( $code, $vendor_codes ) : string {
		if ( ! $code || empty( $vendor_codes ) ) {
			return '';
		}

		foreach ( $vendor_codes as $vendor_code ) {
			if ( $code === $vendor_code->vendor ) {
				return $vendor_code->code;
			}
		}

		return '';
	}

	/**
	 * Get post body content.
	 *
	 * We exclude certain block which we don't want to look inside for allowed blocks and then we only add allowed blocks
	 * to the content blocks variable.
	 *
	 * @param array $blocks array of blocks.
	 * @return array
	 */
	public function get_content_blocks( $blocks, $allowed_blocks = self::ALLOWED_BLOCKS, $exclude_blocks = self::EXCLUDE_BLOCKS ) : array {
		$content_blocks = [];

		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'], $exclude_blocks, true ) ) {
				continue;
			}

			if ( in_array( $block['blockName'], $allowed_blocks, true ) ) {
				$content_blocks[] = $block;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$content_blocks = array_merge( $content_blocks, $this->get_content_blocks( $block['innerBlocks'] ) );
			}
		}

		return $content_blocks;
	}

	/**
	 * Convert blocks to string.
	 *
	 * @param array $blocks array of blocks.
	 * @return string
	 */
	public function blocks_to_string( $blocks ) : string {
		$content = [];

		foreach ( $blocks as $block ) {
			$content[] = $block['innerHTML'];
		}

		return implode( '', $content );
	}

	/**
	 * Get number of reviews, excluding reviews without a rating ie comparison reviews.
	 *
	 * @param stdClass $reviews Object containing product reviews.
	 * @return int
	 */
	public function get_review_count( $reviews ) {
		$review_count = 0;

		foreach ( $reviews as $review ) {
			// We do not count reviews where the rating is not set this could be becuase it's a comparison review or it's not been set.
			if ( ! isset( $review['rating'] ) ) {
				continue;
			}

			$review_count++;

		}

		return $review_count;
	}

	/**
	 * Get average product rating.
	 *
	 * @param stdClass $reviews Object containing product reviews.
	 * @return float
	 */
	public function get_average_review_rating( $reviews ) {
		$review_count = 0;
		$total        = 0;

		foreach ( $reviews as $review ) {
			// We do not count reviews where the rating is not set this could be becuase it's a comparison review or it's not been set.
			if ( ! isset( $review['rating'] ) ) {
				continue;
			}

			$review_count++;
			$total += floatval( $review['rating'] );
		}

		return 0 !== $total ? $total / $review_count : 0;
	}

	/**
	 * Get product review structured data.
	 *
	 * Since it's a bit more complex/data variables compared to the other types, it has it's own function.
	 *
	 * @param \WP_Post $post post.
	 * @return array
	 */
	public function get_product_review_data( $post ) : array {
		if ( ! $post ) {
			return [];
		}

		$review_block = Reviews::find_review_block( $post->post_content );
		$products     = Article::get_products( $post->ID );

		if ( ! isset( $products[ $review_block['attrs']['primaryProductId'] ] ) ) {
			return [];
		}

		$primary_product = $products[ $review_block['attrs']['primaryProductId'] ];

		$review_count          = $this->get_review_count( $primary_product['reviews'] );
		$average_review_rating = $this->get_average_review_rating( $primary_product['reviews'] );

		$product_title              = $primary_product['name'] ?? '';
		$product_featured_image     = isset( $primary_product['featured_media'] ) ? wp_get_attachment_image_src( (int) $primary_product['featured_media'], 'full' ) : '';
		$product_featured_image_url = $product_featured_image[0] ?? '';
		$product_sku_code           = $this->get_code( 'sku', $primary_product['geo_info']->purchase_options->vendor_codes );
		$product_mpn_code           = $this->get_code( 'mpn', $primary_product['geo_info']->purchase_options->vendor_codes );

		$review_title        = $post->post_title ?? '';
		$review_rating       = $review_block['attrs']['rating'] ?: 0.5;
		$review_author       = idg_base_theme_get_author_name( (int) $post->post_author, intval( $post->ID ) );
		$multi_title         = json_decode( get_post_meta( $post->ID, 'multi_title', true ) );
		$review_description  = $multi_title->titles->headline->additional->headline_desc ?? '';
		$review_publish_date = get_the_date( 'c' );

		$blocks         = parse_blocks( $post->post_content );
		$review_blocks  = $this->get_content_blocks( $blocks );
		$review_content = $this->blocks_to_string( $review_blocks );
		$review_body    = wp_strip_all_tags( $review_content );

		$publisher      = get_bloginfo( 'name' );
		$publisher_logo = cf_get_value( 'global_settings', 'structured_data', 'struc_data' )['structured_data_publisher_logo'];
		$site_url       = get_site_url();
		$canonical_url  = idg_get_canonical_url();

		if ( ! $publisher_logo ) {
			$publisher_logo = '';
		}

		return [
			'@context'        => 'https://schema.org/',
			'@type'           => 'Product',
			'name'            => $product_title,
			'image'           => $product_featured_image_url,
			'description'     => 'Description.',
			'sku'             => $product_sku_code,
			'mpn'             => $product_mpn_code,
			'review'          => [
				'@type'            => 'Review',
				'reviewRating'     => [
					'@type'       => 'Rating',
					'ratingValue' => $review_rating * 2, // times 2 as the rating is out of 10 not 5.
					'bestRating'  => 10,
					'worstRating' => 1,
				],
				'name'             => $review_title,
				'author'           => [
					'@type' => 'Person',
					'name'  => $review_author,
				],
				'publisher'        => [
					'@type' => 'Organization',
					'name'  => $publisher,
					'url'   => $site_url,
					'logo'  => [
						'@type' => 'ImageObject',
						'url'   => $publisher_logo,
					],
				],
				'datePublished'    => $review_publish_date,
				'description'      => $review_description,
				'reviewBody'       => $review_body,
				'mainEntityOfPage' => $canonical_url,
			],
			'aggregateRating' => [
				'@type'       => 'AggregateRating',
				'ratingValue' => floor( $average_review_rating * 2 ) / 2,
				'reviewCount' => $review_count,
			],
		];

	}

	/**
	 * Get images used within an article.
	 *
	 * @param \WP_Post $post \WP_Post object.
	 * @return array An array of image urls.
	 */
	public function get_post_images( $post ) : array {
		if ( ! $post ) {
			return [];
		}

		$images             = [];
		$blocks             = parse_blocks( $post->post_content );
		$image_blocks       = $this->get_content_blocks( $blocks, [ 'core/image' ], [] );
		$featured_image_url = get_the_post_thumbnail_url( $post->ID, 'full' );

		if ( $featured_image_url ) {
			$images[] = $featured_image_url;
		}

		foreach ( $image_blocks as $image_block ) {
			$image_id = (int) $image_block['attrs']['id'] ?? 0;

			if ( empty( $image_id ) ) {
				continue;
			}

			$image = wp_get_attachment_image_src( $image_id, 'full' );

			if ( ! $image ) {
				continue;
			}

			$images[] = $image[0];
		}

		return $images;
	}

	/**
	 * Get page structured data.
	 *
	 * @return array
	 */
	public function get_page_data() : array {
		// If not is_single && post_type === 'post, return empty array.
		if ( ! Templates\article() ) {
			return [];
		}

		$post = get_post();

		$review_block  = Reviews::find_review_block( $post->post_content );
		$product_id    = $review_block['attrs']['primaryProductId'] ?? false;
		$legacy_byline = get_post_meta( $post->ID, 'byline', true );

		// Product review - https://schema.org/Product.
		if ( $product_id ) {
			return $this->get_product_review_data( $post );
		}

		if ( ! empty( $legacy_byline ) && is_string( $legacy_byline ) ) {
			$amended_author = $legacy_byline; 
		} else {
			$amended_author = idg_base_theme_get_author_name( (int) $post->post_author, intval( $post->ID ) );
		}

		$author            = $amended_author;
		$blocks            = parse_blocks( $post->post_content );
		$content_blocks    = $this->get_content_blocks( $blocks );
		$content_to_string = $this->blocks_to_string( $content_blocks );
		$article_body      = wp_strip_all_tags( $content_to_string );
		$word_count        = str_word_count( $article_body );
		$canonical_url     = idg_get_canonical_url();
		$date_published    = get_the_date( 'c' );
		$title             = $post->post_title ?? '';
		$images            = $this->get_post_images( $post );
		$publisher         = get_bloginfo( 'name' );
		$publisher_logo    = cf_get_value( 'global_settings', 'structured_data', 'struc_data' )['structured_data_publisher_logo'];
		$url               = get_permalink( $post->ID );
		$site_url          = get_site_url();

		if ( ! $publisher_logo ) {
			$publisher_logo = '';
		}

		/**
		 * Base information which can apply to all below instances of an article because there all types of an article object.
		 *
		 * Documentation ref: https://developers.google.com/search/docs/data-types/article#article-types
		 */
		$structured_data = [
			'url'              => $url,
			'publisher'        => [
				'@type' => 'Organization',
				'name'  => $publisher,
				'url'   => $site_url,
				'logo'  => [
					'@type' => 'ImageObject',
					'url'   => $publisher_logo,
				],
			],
			'author'           => [
				'@type' => $author ? 'Person' : 'Organization',
				'name'  => $author ? $author : $publisher,
			],
			'name'             => $title,
			'headline'         => $title,
			'articleBody'      => $article_body,
			'wordCount'        => $word_count,
			'image'            => $images,
			'datePublished'    => $date_published,
			'mainEntityOfPage' => $canonical_url,
		];

		// NewsArticle - https://schema.org/NewsArticle.
		if ( has_term( 'news', 'story_types', $post->ID ) ) {
			return array_merge(
				[
					'@context' => 'https://schema.org',
					'@type'    => 'NewsArticle',
				],
				$structured_data
			);
		}

		$blog = get_the_terms( $post->ID, 'blogs' );

		// BlogPosting - https://schema.org/BlogPosting.
		if ( $blog ) {
			return array_merge(
				[
					'@context' => 'https://schema.org',
					'@type'    => 'BlogPosting',
				],
				$structured_data
			);
		}

		// Article - https://schema.org/Article.
		return array_merge(
			[
				'@context' => 'https://schema.org',
				'@type'    => 'Article',
			],
			$structured_data
		);
	}

	/**
	 * Get page structured data.
	 *
	 * @return array
	 */
	public function get_structured_data() {
		$structured_data = [];

		$breadcrumbs = $this->get_breadcrumbs();

		if ( ! empty( $breadcrumbs ) ) {
			$structured_data[] = $breadcrumbs;
		}

		$organization = $this->get_organization();

		if ( ! empty( $organization ) ) {
			$structured_data[] = $organization;
		}

		$page_data = $this->get_page_data();

		if ( ! empty( $page_data ) ) {
			$structured_data[] = $page_data;
		}

		return $structured_data;
	}

	/**
	 * Add structured data to page head.
	 *
	 * @return void
	 */
	public function add_structured_data() {
		$structured_data = apply_filters( 'idg_insert_structured_data', $this->get_structured_data() );

		printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $structured_data ) );
	}
}
