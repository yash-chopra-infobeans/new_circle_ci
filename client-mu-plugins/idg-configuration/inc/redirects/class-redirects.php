<?php

namespace IDG\Configuration\Redirects;

/**
 * The redirect handling class.
 */
class Redirects extends Base {
	/**
	 * Run the check squence for Redirects.
	 *
	 * @return void
	 */
	public function check() : void {
		if ( ! $this->check_host() ) {
			return;
		}

		if ( $this->is_healthcheck() ) {
			return;
		}

		if ( $this->is_cli() ) {
			return;
		}

		foreach ( $this->rules as $rule ) {
			$target  = $rule->target();
			$pattern = $rule->pattern();
			$uri     = $this->get_full_url();

			if (
				! @preg_match( "/$pattern/", $uri, $matches )
				&& ! @preg_match( "/$pattern/", $this->uri, $matches )
			) {
				continue;
			}

			if ( $rule->is_external_target() && ! $this->is_subdomain() ) {
				$uri = $this->uri;
			}

			$uri = ltrim( $uri, '/' );

			if ( $rule->is_regex_target() ) {
				unset( $matches[0] );
				$matches = array_values( $matches );

				$key          = 0;
				$placeholders = array_map(
					function( $val ) use ( &$key ) {
						$key++;
						return "$$key";
					},
					$matches
				);

				/**
				 * Normally we might expect a preg_replace() here as we're working with
				 * regex, however preg_replace() will not replace the entire subject an
				 * will append the replacement to it rather than replace the subject with
				 * our $target value with dollar placeholders. To get around this and keep
				 * the $target as the full subject, we create an array of placeholders based
				 * on the amount of matches and use a simple str_replace() to replace the $target
				 * placeholders with the paired values from $placeholders and $matches.
				 */
				$target = str_replace( $placeholders, $matches, $target );
			}

			if ( ! $rule->is_external_target() && ! $this->is_subdomain() ) {
				$target = ltrim( $target, '/' );
				$target = "/$target";
			}

			header( "Location: $target", $rule->code() );
			exit;
		}

		$this->rules = []; // Reset to avoid potential large memory usage.
	}
}
