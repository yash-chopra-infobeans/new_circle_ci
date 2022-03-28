<?php

use  WP_Cypress\Fixtures\Fixture;
use  WP_Cypress\Utils;

class Territory extends Fixture {
	const TAXONOMY = 'territory';

	public function __construct( $properties ) {
		add_action( 'created_' . self::TAXONOMY, [ $this, 'add_currency' ] );

		parent::__construct( $properties );
	}

	public function defaults(): array {
		return [
			'description' => '',
		];
	}

	public function generate(): void {
		$id = wp_insert_term( $this->properties['name'], self::TAXONOMY, [ 'slug' => $this->properties['country_code'] ] );

		if ( is_wp_error( $id ) ) {
			throw new Exception( $id->getMessage() );
		}

		remove_action( 'created_' . self::TAXONOMY, [ $this, 'add_currency' ] );
	}

	public function add_currency( $id ): void {
		update_term_meta( $id, 'default_currency', $this->properties['currency_code'] );
	}
}
