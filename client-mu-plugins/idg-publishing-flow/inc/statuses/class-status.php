<?php

namespace IDG\Publishing_Flow\Statuses;

use IDG\Publishing_Flow\Statuses\Transition\Transition;

/**
 * Base class for statuses.
 */
class Status {
	use Transition;

	/**
	 * The disable state for when the class is parsed
	 * for HTML <option> creation.
	 *
	 * @var boolean
	 */
	public $option_disable = false;

	/**
	 * Initialise the statis class and register the
	 * transition hook with the child `on_transition` method.
	 */
	public function __construct() {
		add_action( 'transition_post_status', [ $this, 'on_transition' ], 10, 3 );
	}

	/**
	 * Return the defined name when class used as a string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
}
