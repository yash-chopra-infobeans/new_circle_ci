<?php
/**
 * File for handling the link wrapping for URLs.
 *
 * @package idg-base-theme
 */

namespace IDG\Products;

use IDG\Territories\Geolocation;
use function IDG\Base_Theme\Utils\is_amp;

/**
 * Management of Link Wrapping related features
 */
class Link_Wrapping {
	const KEY            = 'linkwrapping_rules';
	const HANDLEBAR_VARS = [ '{{target_url}}', '{{subtag}}', '{{origin_url}}' ];

	/**
	 * Init action: Create Link Wrapping options page.
	 * Content filter: Apply transforms to links.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'create_options_page' ] );
		add_filter( 'the_content', [ $this, 'transform' ], 99 );
		add_filter( 'idg_linkwrapping', [ $this, 'transform' ] );
	}

	/**
	 * Create the options page to define link wrapping rules.
	 *
	 * @return void
	 */
	public function create_options_page() {
		$config = json_decode(
			file_get_contents( IDG_PRODUCTS_DIR . '/inc/config/linkwrapping-fields.json' )
		);

		cf_register_options_page( $config, self::KEY, __( 'Link Wrapping', 'idg' ) );
	}

	/**
	 * Apply transformation to links within HTML.
	 *
	 * @param string $html - The html to transform.
	 * @return string
	 */
	public static function transform( string $html = '' ) : string {
		global $pagenow;

		$disallowed = [
			'post.php',
		];

		if ( in_array( $pagenow, $disallowed, true ) ) {
			return $html;
		}

		$rules = cf_get_value( self::KEY, 'rules' );

		if ( ! $rules ) {
			return $html;
		}

		/**
		 * Later in the parsing of html, DOMDocument::loadHtml with the LIBXML_HTML_NOIMPLIED
		 * flag expects the injested html to contain a single root element. This isn't alwaus
		 * a guarentee so we wrap the html in an element to ensure we have that single root.
		 */
		$html = "<div id=\"link_wrapped_content\">$html</div>";

		$country_code = Geolocation::get_country_code() ?? '';
		$dom          = new \DOMDocument();

		libxml_use_internal_errors( true );
		$dom->loadHTML(
			mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ),
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		foreach ( $dom->getElementsByTagName( 'a' ) as $a ) {
			/**
			 * This is mainly a workaround for handling the price links in product charts/widgets.
			 * 
			 * The amp components does not allow dom manipulation in href links.
			 * Thus, links got broken and href was left blank, so added the condition here for avoiding
			 * Dom manipulation of such links.
			 * 
			 * Unique attribute added on such links for differentiating.
			 * Works only for AMP pages.
			 */
			if ( $a->hasAttribute( 'data-amp-link' ) ) {
				continue;
			}
			
			/**
			 * This is mainly a workaround for migrated product links from OneCMS having empty href values
			 * due to them previously being created on pageload via JS.
			 *
			 * However, it does act as a nice fallback for when editors don't add a URL to a product link.
			 */
			$product_id = self::get_product_id( $a );
			$prev_href  = $a->getAttribute( 'href' );
			if ( $product_id && self::is_href_empty( $prev_href ) ) {
				$a->setAttribute( 'href', self::create_product_link( $product_id, $prev_href ) );
			}

			$original_link = $a->getAttribute( 'href' );
			$href          = self::process_link( $a, $rules, $country_code );

			if ( ! $href ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$text = $dom->createTextNode( $a->nodeValue );
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$a->parentNode->replaceChild( $text, $a );
			} else {
				if ( ! self::is_blocked_url( $original_link, $rules ) ) {
					$a->setAttribute( 'rel', 'nofollow' );
				}

				$product_id = self::get_product_id( $a );

				if ( $product_id ) {
					// No biggie, cached.
					$products = Article::get_products( get_the_ID() );
					$product  = $products[ $product_id ] ?? false;

					if ( $product ) {
						foreach ( $product['attributes'] as $attr_key => $attr ) {
							$a->setAttribute( $attr_key, $attr );
						}

						$a->setAttribute( 'data-vars-link-position-id', '000' );
						$a->setAttribute( 'data-vars-link-position', 'Body Text' );
						$a->setAttribute( 'data-vars-outbound-link', $original_link );
					}

					$a->setAttribute( 'rel', 'nofollow' );
				}

				$a->setAttribute( 'href', htmlspecialchars_decode( $href ) );
			}
		}
		
		/**
		 * This is mainly a workaround for handling the price links in product charts/widgets.
		 * 
		 * The links contains special characters, which '$dom->saveHTML' cannot handle 
		 * and outputs garbage values.
		 * 
		 * Works only for AMP pages.
		 */
		$html = $dom->saveHTML();
		if ( is_amp() ) {
			$urlper     = '/(href=.*|src=.*)(%7B){2}(.*)(%7D){2}/m'; // regex.
			$substitute = '$1{{$3}}';
			$html       = preg_replace( $urlper, $substitute, $html );
		}
		
		return $html;
	}

	/**
	 * Is it a product link return product id.
	 *
	 * @param object $a - The link element.
	 * @return boolean
	 */
	public static function get_product_id( $a ) {
		return $a->getAttribute( 'data-product' ) ?: false;
	}

	/**
	 * Determine if a href is empty.
	 *
	 * @param string $href - The url.
	 * @return boolean
	 */
	public static function is_href_empty( $href ) {
		if ( '#' === $href ) {
			return true;
		}

		if ( ctype_space( $href ) ) {
			return true;
		}

		if ( ! $href ) {
			return true;
		}

		return false;
	}

	/**
	 * Check wheter the given url is part of the block list.
	 *
	 * @param object $href The link element.
	 * @param array  $rules The transform rules.
	 * @return boolean
	 */
	public static function is_blocked_url( $href, array $rules ) : bool {
		$blocklist_urls = isset( $rules['blocklist'] ) ? $rules['blocklist']['urls'] : [];

		$blocklist = array_map(
			function( $item ) {
				return $item['url'] ?: '';
			},
			$blocklist_urls
		);

		foreach ( $blocklist as $regex ) {
			$match = self::href_has_match( $regex, $href );

			if ( $match ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a href matches a target.
	 *
	 * # is used as a delimiter to determine if it is a plain string or regex
	 * which in turn determines of the value's regex characters should be escaped.
	 *
	 * @param string $target - The target.
	 * @param string $href - The href value.
	 * @return bool
	 */
	public static function href_has_match( string $target, string $href ) : bool {
		if ( '#' === $target[0] && '#' === $target[ strlen( $target ) - 1 ] ) {
			return preg_match( $target, $href );
		}

		return preg_match( '#' . preg_quote( $target, '#' ) . '#', $href );
	}

	/**
	 * Process a single link, applying the necessary transformations to it.
	 *
	 * @param object $a - The link element.
	 * @param array  $rules - The transform rules.
	 * @param string $country_code - The geolocated country code.
	 * @return string
	 */
	public static function process_link( $a, array $rules, string $country_code ) : string {
		$href = $a->getAttribute( 'href' );

		if ( self::is_href_empty( $href ) ) {
			return $href;
		}

		if ( self::is_blocked_url( $href, $rules ) ) {
			return $href;
		}

		$rule_to_apply = $rules['default'];

		foreach ( $rules['custom']['targets'] ?? [] as $target ) {
			if ( ! self::href_has_match( $target['target'], $href ) ) {
				continue;
			}

			$territories = array_map(
				function( $rule ) {
					return $rule['territory'] ?? null;
				},
				$target['rules']
			);

			$key = array_search( $country_code, $territories, true );

			if ( ! $key && 0 !== $key ) {
				$key = array_search( null, $territories, true );
			}

			if ( $key || 0 === $key ) {
				$rule_to_apply = $target['rules'][ $key ];
			}

			break;
		}

		if ( ! isset( $rule_to_apply['transform'] ) ) {
			return $href;
		}

		if ( ctype_space( $rule_to_apply['transform'] ) || ! $rule_to_apply['transform'] ) {
			return $href;
		}

		return str_replace(
			self::HANDLEBAR_VARS,
			[
				esc_url( $href ),
				Subtag::generate( $a ),
				self::get_origin_url(),
			],
			$rule_to_apply['transform']
		);
	}

	/**
	 * Generate a product link from a product record.
	 *
	 * @param string|int $product_id - The product id.
	 * @param string     $href - The current href.
	 * @return string
	 */
	public static function create_product_link( $product_id, string $href ) : string {
		/**
		 * If no product exists at this point, then it musn't exist in the content hub.
		 * This might be because a product has been deleted or not migrated.
		 */
		$products = Article::get_products( get_the_ID() );
		$product  = $products[ $product_id ];
		if ( ! isset( $product ) || ! $product ) {
			return $href;
		}

		/**
		 * If a product has a direct link that isn't specific to a territory (user selected "All")
		 * then the first direct link is used as a fallback.
		 */
		$global_direct_links = array_filter(
			$product['direct_links'] ?: [],
			function( $link ) {
				return ! $link->territory && ! self::is_href_empty( $link->url );
			}
		);
		if ( ! empty( $global_direct_links ) ) {
			return $global_direct_links[0]->url;
		}

		/**
		 * If the link has a geolcated amazon vendor, then dynamically create
		 * it's amazon link and use that.
		 */
		if ( isset( $product['geo_info']->purchase_options->vendor_codes ) ) {
			$geo_amazon_link = self::create_amazon_link_from_vendors(
				$product['geo_info']->purchase_options->vendor_codes
			);

			if ( ! empty( $geo_amazon_link ) ) {
				return $geo_amazon_link;
			}
		}

		/**
		 * If no geolocated amazon link is created, default to US.
		 */
		if ( isset( $product['all_region_info']->US->purchase_options->vendor_codes ) ) {
			$us_amazon_link = self::create_amazon_link_from_vendors(
				$product['all_region_info']->US->purchase_options->vendor_codes
			);

			if ( ! empty( $us_amazon_link ) ) {
				return $us_amazon_link;
			}
		}

		/**
		 * If we hit this point, we tried our best but the product record doesn't have the correct data.
		 */
		return $href;
	}

	/**
	 * Create amazon link from vendors.
	 *
	 * @param array $vendors - Vendors defined in purchase options on a product record.
	 * @return string
	 */
	public static function create_amazon_link_from_vendors( array $vendors = [] ) : string {
		foreach ( $vendors as $vendor ) {
			$vendor_name = strtolower( $vendor->vendor );

			if ( strpos( 'amazon', $vendor_name ) !== false ) {
				return "https://www.amazon.com/dp/product/{$vendor->code}";
			}

			if ( strpos( 'asin', $vendor_name ) !== false ) {
				return "https://www.amazon.com/dp/product/{$vendor->code}";
			}
		}

		return '';
	}

	/**
	 * Get the full current url.
	 *
	 * @return string
	 */
	public static function get_origin_url() : string {
		$protocol = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https://' : 'http://';
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return esc_url( $protocol . $host . $uri );
	}

	/**
	 * Process a single link for AMP pages, applying the necessary transformations to it.
	 *
	 * @param string $href - The link element URL.
	 * @param string $country_code - The geolocated country code.
	 * @param string $subtag - Generated subtag for AMP's link.
	 * @param int    $post_id - Post ID to get the permalink of current post to be passed for link wrapping.
	 * @return string
	 */
	public static function process_amp_link( string $href, string $country_code, string $subtag, int $post_id ) : string {
		$rules = cf_get_value( self::KEY, 'rules' );

		if ( self::is_href_empty( $href ) ) {
			return $href;
		}

		if ( self::is_blocked_url( $href, $rules ) ) {
			return $href;
		}

		$rule_to_apply = $rules['default'];

		foreach ( $rules['custom']['targets'] ?? [] as $target ) {
			if ( ! self::href_has_match( $target['target'], $href ) ) {
				continue;
			}

			$territories = array_map(
				function( $rule ) {
					return $rule['territory'] ?? null;
				},
				$target['rules']
			);

			$key = array_search( $country_code, $territories, true );

			if ( ! $key && 0 !== $key ) {
				$key = array_search( null, $territories, true );
			}

			if ( $key || 0 === $key ) {
				$rule_to_apply = $target['rules'][ $key ];
			}

			break;
		}

		if ( ! isset( $rule_to_apply['transform'] ) ) {
			return $href;
		}

		if ( ctype_space( $rule_to_apply['transform'] ) || ! $rule_to_apply['transform'] ) {
			return $href;
		}

		return str_replace(
			self::HANDLEBAR_VARS,
			[
				esc_url( $href ),
				$subtag,
				get_the_permalink( $post_id ),
			],
			$rule_to_apply['transform']
		);
	}
}
