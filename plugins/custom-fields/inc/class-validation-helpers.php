<?php
/**
 * This file is used for validations.
 *
 * @package  IDG
 */

namespace Custom_Fields;

/**
 * This Class is used for validations.
 *
 * @category Class
 */
class Validation_Helpers {
	
	/**
	 * This function validates uri
	 */
	public static function uri() {
		return (object) [
			'schema'  => (object) [
				'type'   => 'string',
				'format' => 'uri',
			],
			'message' => '* Invalid URI',
		];
	}
	/**
	 * This function is used to check required fields.
	 */
	public static function required() {
		return (object) [
			'schema'  => (object) [
				'type'      => 'string',
				'minLength' => 1,
			],
			'message' => '* Required field',
		];
	}
	/**
	 * This function is used to check comma separated asin codes in vendor block.
	 */
	public static function commaSeparated() {
		return (object) [
			'schema'  => (object) [
				'type'      => 'string',
				'minLength' => 1,
				'pattern'   => '^(?:[a-zA-Z0-9]*-?[a-zA-Z0-9]+,)*[a-zA-Z0-9]*-?[a-zA-Z0-9]+$',
			],
			'message' => '* Empty or invalid pattern',
		];
	}
}
