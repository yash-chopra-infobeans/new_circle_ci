<?php

namespace IDG\Migration;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$migrate_attachment_args = [
	'shortdesc' => 'Processes a given number of attachments.',
	'synopsis'  => [
		[
			'type'        => 'assoc',
			'name'        => 'type',
			'description' => 'The type of query to make when processing attachments.',
			'options'     => [ 'content', 'db', 'users', 'taxonomy', 'featured_image' ],
			'optional'    => false,
		],
		[
			'type'        => 'assoc',
			'name'        => 'post_type',
			'description' => 'The post type to migrate images for.',
			'options'     => [ 'post', 'page', 'product' ],
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'amount',
			'description' => 'The attachment amount to process.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'offset',
			'description' => 'The attachment count offset.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'include',
			'description' => 'Comma seperated list of post IDs.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'taxonomy',
			'description' => 'Taxonomy to migrate term images for.',
			'options'     => [ 'blogs', 'podcast_series', 'sponsorships' ],
			'optional'    => true,
		],
		[
			'type'        => 'flag',
			'name'        => 'publish',
			'description' => 'If set the post(s) will be published to specified delivery sites.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'publications',
			'description' => 'Publication ID\'s that the posts should be published too.',
			'optional'    => true,
		],
	],
];

\WP_CLI::add_command( 'idg migrate attachments', '\IDG\Migration\Images\CLI', $migrate_attachment_args );
