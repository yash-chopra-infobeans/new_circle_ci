<?php

namespace IDG\Products;

/**
 * Generate the subtag.
 */
class Subtag {
	const SEPERATOR = '-';

	const PARTS = [ 'site_id', 'medium_code', 'article_id', 'position', 'product_id', 'manufacturer_id' ];

	/**
	 * Retrieve the side id used within the subtag.
	 *
	 * @return int
	 */
	public static function site_id() {
		return cf_get_value( 'linkwrapping_rules', 'subtag', 'site.id' ) ?? 0;
	}

	/**
	 * Return the current medium, represent as an integer.
	 *
	 * @return int
	 */
	public static function medium_code() {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return 4;
		}

		$detect = new \Mobile_Detect();

		if ( $detect->isMobile() && ! $detect->isTablet() ) {
			return 3;
		}

		if ( $detect->isTablet() ) {
			return 2;
		}

		return 1;
	}

	/**
	 * Return the article id.
	 *
	 * @return int.
	 */
	public static function article_id() {
		return get_the_ID() ?? 0;
	}

	/**
	 * TO DO
	 *
	 * @return int
	 */
	public static function position() {
		return 0;
	}


	/**
	 * Return the attached product id, if present.
	 *
	 * @param object $a - The link element.
	 * @return int
	 */
	public static function product_id( $a ) {
		$id = $a->getAttribute( 'data-product' );

		if ( $id ) {
			return $id;
		}

		return 0;
	}

	/**
	 * Return the attached manufacturer ids, if present.
	 *
	 * @param object $a - The link element.
	 * @return int
	 */
	public static function manufacturer_id( $a ) {
		$ids = $a->getAttribute( 'data-manufacturer' );

		if ( $ids ) {
			return $ids;
		}

		return 0;
	}

	/**
	 * Generate the subtag.
	 *
	 * @param object $a - The link element.
	 * @return string
	 */
	public static function generate( $a ) {
		$ids = array_map(
			function( $item ) use ( $a ) {
				// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.SelfInsideClosure
				return self::$item( $a );
			},
			self::PARTS
		);

		return join( self::SEPERATOR, $ids );
	}
}
