<?php

namespace IDG\Configuration\Redirects;

/**
 * The rewrite handling class.
 */
class Rewrites extends Base {
	/**
	 * Run the check squence for Rewrites.
	 *
	 * @return void
	 */
	public function check() : void {
		global $wp_rewrite;

		foreach ( $this->rules as $rule ) {
			$target  = $rule->target();
			$pattern = $rule->pattern();
			$wp_rewrite->add_external_rule( $pattern, $target );
		}
	}
}
