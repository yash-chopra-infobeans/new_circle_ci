<?php

namespace IDG\Territories;

use Rinvex\Country\CountryLoader;

/**
 * Decorator class for Rinvex\Country\Country.
 */
class Territory {
	/**
	 * The associated term to the territory.
	 *
	 * @var object
	 */
	public $term;

	/**
	 * The defaylt iso 4217 currency code.
	 *
	 * @var string
	 */
	public $default_currency;

	/**
	 * Rinvex country instance.
	 *
	 * @var Rinvex\Country\Country
	 */
	protected $country;

	/**
	 * Initialize class.
	 *
	 * @param object $term - The associated term.
	 */
	public function __construct( $term ) {
		$this->term = $term;

		try {
			$this->country = CountryLoader::country( $this->term->slug );
		} catch ( \Rinvex\Country\CountryLoaderException $e ) {

		}

		if ( ! empty( $this->term->term_id ) ) {
			$term_id = absint( $this->term->term_id );
		}

		if ( ! empty( $this->term->id ) ) {
			$term_id = absint( $this->term->id );
		}

		$this->default_currency = get_term_meta( $term_id, 'default_currency', true );
	}

	/**
	 * Magic method to allow access to Rinvex\Country\Country methods.
	 *
	 * @param string $method - The method to invoke.
	 * @param mixed  $args - Args to pass to the method.
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return call_user_func_array( [ $this->country, $method ], $args );
	}

	/**
	 * Magic method to allow access to Rinvex\Country\Country variables.
	 *
	 * @param string $key - The key to retrieve.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->country->$key;
	}

	/**
	 * Return the default currency for a territory with it's corresponding symbol.
	 *
	 * @return array
	 */
	public function get_default_currency() : array {
		$currency = $this->getCurrencies()[ $this->default_currency ] ?? null;

		if ( ! $currency ) {
			$currency = Territory_Taxonomy::DEFAULT_CURRENCIES[ $this->default_currency ] ?? null;
		}

		if ( ! $currency ) {
			$currency = Territory_Taxonomy::DEFAULT_CURRENCIES['USD'];
		}

		$symbols = json_decode(
			file_get_contents( IDG_TERRITORIES_DIR . '/inc/currency-symbols.json' ),
			true
		);

		$currency['symbol'] = $symbols[ $currency['iso_4217_code'] ]['symbol_native'];

		return $currency;
	}

	/**
	 * Retrieve the term name.
	 *
	 * @return string
	 */
	public function get_term_name() : string {
		return $this->term->name;
	}
}
