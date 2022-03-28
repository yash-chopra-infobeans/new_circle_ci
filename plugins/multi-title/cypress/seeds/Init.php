<?php

use \WP_Cypress\Seeder\Seeder;

class Init extends Seeder {
	public function run() {
		$multi_title_block = '
			<!-- wp:bigbite/multi-title -->
			<section class="wp-block-multi-title"><div class="container"></div></section>
			<!-- /wp:bigbite/multi-title -->
		';

		$title = 'This is our test article';

		$this->generate->posts( [
			'import_id'    => 10,
			'post_title'   => $title,
			'post_content' => $multi_title_block,
		], 1 );

		$this->generate->posts( [
			'import_id'    => 11,
			'post_title'   => $title,
			'post_content' => $multi_title_block,
		], 1 );

		$this->generate->posts( [
			'import_id'    => 12,
			'post_title'   => '',
			'post_content' => $multi_title_block,
		], 1 );

		$this->generate->posts( [
			'import_id'    => 13,
			'post_title'   => $title,
			'post_content' => $multi_title_block,
		], 1 );

		$this->generate->posts( [
			'import_id'    => 14,
			'post_title'   => $title,
			'post_content' => $multi_title_block,
		], 1 );

		$this->generate->posts( [
			'import_id'    => 15,
			'post_title'   => '',
			'post_content' => $multi_title_block,
		], 1 );
	}
}

