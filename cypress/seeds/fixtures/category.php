<?php

use  WP_Cypress\Fixtures\Fixture;
use  WP_Cypress\Utils;

class Category extends Fixture {
	const TAXONOMY = 'category';

	public function defaults(): array {
		return [
			'description' => '',
		];
	}

	public function generate(): void {
		$id = wp_insert_term( $this->properties['name'], self::TAXONOMY );

		if ( is_wp_error( $id ) ) {
			throw new Exception( $id->getMessage() );
		}
	}
}
