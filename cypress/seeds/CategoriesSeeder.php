<?php

use WP_Cypress\Seeder\Seeder;
use WP_Cypress\Fixtures;

class CategoriesSeeder extends Seeder {
	const CATEGORIES = [
		[
			'name' => 'iPhone',
		],
		[
			'name' => 'Apple',
		],
		[
			'name' => 'Mac',
		],
		[
			'name' => 'Software',
		],
	];

	public function run() {
		foreach ( self::CATEGORIES as $category ) {
			( new Category( $category ) )->create();
		}
	}
}

