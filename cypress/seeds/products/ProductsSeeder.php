<?php

use WP_Cypress\Seeder\Seeder;
use WP_Cypress\Fixtures;

class ProductsSeeder extends Seeder {
	const TERRITORIES = [
		[
			'name'          => 'Australia',
			'country_code'  => 'AU',
			'currency_code' => 'AUD'
		],
		[
			'name'          => 'UK',
			'country_code'  => 'GB',
			'currency_code' => 'GBP'
		],
		[
			'name'          => 'Germany',
			'country_code'  => 'DE',
			'currency_code' => 'EUR'
		],
		[
			'name'          => 'Canada',
			'country_code'  => 'CA',
			'currency_code' => 'CAD'
		],
		[
			'name'          => 'Ireland',
			'country_code'  => 'IE',
			'currency_code' => 'EUR'
		],
		[
			'name'          => 'Mexico',
			'country_code'  => 'MX',
			'currency_code' => 'MXN'
		],
		[
			'name'          => 'Spain',
			'country_code'  => 'ES',
			'currency_code' => 'EUR'
		],
		[
			'name'          => 'Sweden',
			'country_code'  => 'SE',
			'currency_code' => 'SEK'
		],
		[
			'name'          => 'US',
			'country_code'  => 'US',
			'currency_code' => 'USD'
		],
	];

	public function run() {
		/**
		 * Normally this would not belong here, but WP-Cypress currently
		 * does not support setup code, this will have to suffice.
		 * Sets the admin capabilities.
		 */
		idg_role_administrator_capabilities();

		// Loop through each
		foreach ( self::TERRITORIES as $territory ) {
			( new Territory( $territory ) )->create();
		}
	}
}

