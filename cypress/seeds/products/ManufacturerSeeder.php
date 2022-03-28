<?php

use WP_Cypress\Seeder\Seeder;
use WP_Cypress\Fixtures;

class ManufacturerSeeder extends Seeder {
	const MANUFACTURERS = [
		[
			'name' => 'Apple',
		],
		[
			'name' => 'Microsoft',
		],
		[
			'name' => 'Belkin',
		],
		[
			'name' => 'Sony',
		],
	];

	public function run() {
		foreach ( self::MANUFACTURERS as $manufacturer ) {
			( new Manufacturer( $manufacturer ) )->create();
		}
	}
}

