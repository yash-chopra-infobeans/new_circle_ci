<?php

namespace IDG\Territories;

use IDG\Territories\Territory_Loader;
use IDG\Publishing_Flow\Sites;

/**
 * General helpers related to territories
 */
class Helpers {
	/**
	 * Get territories with title and name key value pairs.
	 *
	 * @return array
	 */
	public static function get_territory_tabs() : array {
		$territories = Territory_Loader::territories();

		return array_map(
			function( $territory ) {
				return [
					'title' => $territory->get_term_name(),
					'name'  => $territory->getIsoAlpha2(),
				];
			},
			$territories
		);
	}

	/**
	 * Get territories with label and value key value pairs.
	 *
	 * @return array
	 */
	public static function get_territory_options() : array {
		$territories = Territory_Loader::territories();

		$options = [
			[
				'label' => 'All',
				'value' => '',
			],
		];

		$territory_options = self::maybe_get_territory_options_from_origin();

		return array_merge(
			$options,
			$territory_options
		);
	}

	/**
	 * Get territories with label and value key value pairs.
	 * Fetch terms from Contenthub if on delivery site.
	 *
	 * @return array
	 */
	public static function maybe_get_territory_options_from_origin() : array {
		$options     = [];
		$territories = [];

		if ( ! Sites::is_origin() ) {
			$fetched_territories = Territory_Loader::get_cached_territories_from_origin();

			if ( empty( $fetched_territories ) || ! is_array( $fetched_territories ) ) {
				return [];
			}

			foreach ( $fetched_territories as $territory ) {
				$territories[] = Territory_Loader::territory( $territory );
			}
		} else {
			$territories = Territory_Loader::territories();
		}

		if ( empty( $territories ) || ! is_array( $territories ) ) {
			return [];
		}

		foreach ( $territories as $territory ) {
			$options[] = [
				'label' => $territory->getName(),
				'value' => $territory->getIsoAlpha2(),
			];
		}

		return $options;
	}

	/**
	 * Get unique currency options as label and value key pairs.
	 *
	 * @return array
	 */
	public static function get_currency_options() : array {
		$territories = Territory_Loader::territories();

		$currencies = array_map(
			function( $territory ) {
				$currency = $territory->get_default_currency();

				return [
					'label' => "{$currency['symbol']} - {$currency['iso_4217_name']}",
					'value' => $currency['iso_4217_code'],
				];
			},
			$territories
		);

		return array_values( array_unique( $currencies, SORT_REGULAR ) );
	}

	/**
	 * Get currencies as country code & currency code key value pairs.
	 *
	 * @return object
	 */
	public static function get_currency_for_region() : object {
		$territories = Territory_Loader::territories();
		$currencies  = [];

		foreach ( $territories as $territory ) {
			$currency                                 = $territory->get_default_currency();
			$currencies[ $territory->getIsoAlpha2() ] = $currency['iso_4217_code'];
		}

		return (object) $currencies;
	}
}
