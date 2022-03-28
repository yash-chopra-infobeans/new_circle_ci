<?php

namespace IDG\Publishing_Flow\API\Validator;

use Rakit\Validation\Rule;

/**
 * Custom validator for checking that the post exists.
 *
 * @see Rakit\Validation\Rule
 */
class Post_ID_Exists extends Rule {
	/**
	 * Set the message when the validation fails.
	 *
	 * @var string
	 */
	protected $message = ':attribute - :value does not exist.';

	/**
	 * Params that are allowed.
	 *
	 * @var array
	 */
	protected $fillableParams = []; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * Checks that the value exists as a post status and is a string.
	 *
	 * @param mixed $value The expected value to validate.
	 * @return boolean
	 */
	public function check( $value ) : bool {
		return is_string( get_post_status( $value ) );
	}
}
