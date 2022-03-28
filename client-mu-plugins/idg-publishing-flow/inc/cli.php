<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$publications_args = [
	'shortdesc' => 'Outputs a list of the available publications.',
];

WP_CLI::add_command( 'idg publications list', '\IDG\Publishing_Flow\Command\Publications', $publications_args );

$publish_args = [
	'shortdesc' => 'Deploy a single article to a given delivery site.',
	'synopsis'  => [
		[
			'type'        => 'assoc',
			'name'        => 'publication',
			'description' => 'The ID of the publication term to deploy articles to.',
			'optional'    => false,
			'repeating'   => false,
		],
		[
			'type'        => 'assoc',
			'name'        => 'post-id',
			'description' => 'The ID of the post to publish.',
			'optional'    => false,
			'repeating'   => false,
		],
		[
			'type'        => 'flag',
			'name'        => 'assign-pub',
			'description' => 'Will assign the <publication> to the article prior to deploy. [WARNING: WILL OVERWRITE EXISTING PUBLICATION ASSIGNMENTS.]',
			'optional'    => true,
		],
	],
];

WP_CLI::add_command( 'idg sync article', '\IDG\Publishing_Flow\Command\Sync_Article', $publish_args );

$publishing_args = [
	'shortdesc' => 'Deploys articles to a given delivery site.',
	'synopsis'  => [
		[
			'type'        => 'flag',
			'name'        => 'all',
			'description' => 'Deploys all articles.',
			'optional'    => true,
		],
		[
			'type'        => 'flag',
			'name'        => 'trashed',
			'description' => 'Trashes deployed articles.',
			'optional'    => true,
		],
		[
			'type'        => 'flag',
			'name'        => 'on-hold',
			'description' => 'Unpublishes all on-hold, in-review, etc articles.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'publication',
			'description' => 'The ID of the publication term to deploy articles to.',
			'optional'    => false,
			'repeating'   => false,
		],
		[
			'type'        => 'assoc',
			'name'        => 'posts-offset',
			'description' => 'The offset for the retrieved posts from get_posts() before deploying.',
			'optional'    => true,
		],
		[
			'type'        => 'assoc',
			'name'        => 'posts-num',
			'description' => 'The amount of posts to get from get_posts() before deploying.',
			'optional'    => true,
		],
		[
			'type'        => 'flag',
			'name'        => 'assign-pub',
			'description' => 'Will get all posts irregardless of publication and assign the <publication> to each article prior to deploy. [WARNING: WILL OVERWRITE EXISTING PUBLICATION ASSIGNMENTS.]',
			'optional'    => true,
		],
	],
];

WP_CLI::add_command( 'idg sync articles', '\IDG\Publishing_Flow\Command\Sync_Articles', $publishing_args );

$terms_args = [
	'shortdesc' => 'Deploy a terms to a delivery site.',
	'synopsis'  => [
		[
			'type'        => 'assoc',
			'name'        => 'publication',
			'description' => 'The ID of the publication term to deploy articles to.',
			'optional'    => false,
			'repeating'   => false,
		],
		[
			'type'        => 'assoc',
			'name'        => 'taxonomies',
			'description' => 'A comma seperated list of taxonomies to sync.',
			'optional'    => true,
			'repeating'   => false,
		],
	],
];

WP_CLI::add_command( 'idg sync terms', '\IDG\Publishing_Flow\Command\Sync_Terms', $terms_args );

$cleanup_args = [
	'shortdesc' => 'Destructive clean up of data.',
	'synopsis'  => [
		[
			'type'        => 'assoc',
			'name'        => 'type',
			'description' => 'The type to clean up (authors, terms,).',
			'options'     => [ 'terms' ],
			'optional'    => false,
			'repeating'   => false,
		],
		[
			'type'        => 'assoc',
			'name'        => 'tax',
			'description' => 'If type=terms than defines the taxonomies to process. Defaults to all.',
			'optional'    => true,
			'repeating'   => false,
		],
	],
];

WP_CLI::add_command( 'idg destroy', '\IDG\Publishing_Flow\Command\Destroy', $cleanup_args );
