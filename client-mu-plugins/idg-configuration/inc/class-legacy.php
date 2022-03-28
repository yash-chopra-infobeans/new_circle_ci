<?php

namespace IDG\Configuration;

/**
 * Class to handle minor legacy configuration.
 */
class Legacy {
	const META_ONECMS_REF = 'old_id_in_onecms';

	/**
	 * Instantiate with hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'onecms_register_meta' ] );
	}

	/**
	 * Register the OneCMS ID meta.
	 *
	 * @return void
	 */
	public function onecms_register_meta() {
		register_meta(
			'post',
			self::META_ONECMS_REF,
			[
				'type'         => 'string',
				'description'  => 'The ID of the article in One CMS',
				'single'       => true,
				'default'      => '',
				'show_in_rest' => true,
			]
		);
	}
}
