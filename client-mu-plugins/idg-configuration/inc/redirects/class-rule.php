<?php

namespace IDG\Configuration\Redirects;

/**
 * Class handler for redirect and rewrite rules.
 */
class Rule {
	/**
	 * The ruleset for the provided rule.
	 *
	 * @var array
	 */
	private $ruleset = [];

	/**
	 * Whether the rule is valid or not.
	 *
	 * @var boolean
	 */
	public $is_valid = false;

	/**
	 * Constructor for instantiating the rule.
	 *
	 * @param object $ruleset The ruleset for defining the rule parameters.
	 */
	public function __construct( object $ruleset ) {
		$this->ruleset  = $ruleset;
		$this->is_valid = $this->validate( $this->ruleset );
	}

	/**
	 * Validates the rule.
	 *
	 * @param object $rule The ruleset for defining the rule parameters.
	 * @throws ErrorException Throws when not setting a valid redirect code.
	 * @return boolean
	 */
	public function validate( object $rule ) : bool {
		if ( ! isset( $rule->pattern ) ) {
			return false;
		}

		if ( ! isset( $rule->target ) ) {
			return false;
		}

		$valid_codes = [ 300, 301, 302, 303, 304, 307, 308 ];

		if ( isset( $rule->code ) && ! in_array( $rule->code, $valid_codes, true ) ) {
			throw new ErrorException( "$rule->code is not a valid redirection status code." );
		}

		return true;
	}

	/**
	 * Gets the rule pattern while performaing a domain replace.
	 *
	 * @return string
	 */
	public function pattern() : string {
		$regex_pattern = $this->domain_replace( $this->ruleset->pattern );

		return $regex_pattern;
	}

	/**
	 * Gets the rule target while performaing a domain replace.
	 *
	 * @return string
	 */
	public function target() : string {
		return $this->domain_replace( $this->ruleset->target );
	}

	/**
	 * Performs a domain replacement on the passed string if
	 * the IDG_REDIRECT_DOMAIN_REPLACEMENTS constant is set.
	 * This is useful for testing on environments where the rule
	 * domain does not match. Example
	 * target: macworld.com
	 * replacement: macworld-go.vip.net
	 *
	 * @param string $target The target to replace the domain of.
	 * @return string
	 */
	private function domain_replace( string $target ) : string {
		if ( ! defined( 'IDG_REDIRECT_DOMAIN_REPLACEMENTS' ) ) {
			return $target;
		}

		$replacements = IDG_REDIRECT_DOMAIN_REPLACEMENTS;
		$keys         = array_keys( $replacements );
		$values       = array_values( $replacements );

		$target = \str_replace( $keys, $values, $target );

		return $target;
	}

	/**
	 * Get the status code set for the rule.
	 *
	 * @return integer
	 */
	public function code() : int {
		return isset( $this->ruleset->code ) ? $this->ruleset->code : 301;
	}

	/**
	 * Check whether the target is eternal for redirect.
	 *
	 * @return boolean
	 */
	public function is_external_target() : bool {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Executing before wp_parse_url() is available.
		$target_host = parse_url( $this->ruleset->target, PHP_URL_HOST );

		if ( $target_host ) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether the current rule is a redirect.
	 *
	 * @return boolean
	 */
	public function is_redirect() : bool {
		if ( isset( $this->ruleset->code ) ) {
			return true;
		}

		if ( $this->is_external_target() ) {
			return true;
		}

		return isset( $this->ruleset->redirect ) ? $this->ruleset->redirect : true;
	}

	/**
	 * Check whether the current rule is a rewrite.
	 *
	 * @return boolean
	 */
	public function is_rewrite() : bool {
		if ( $this->is_external_target() ) {
			return false;
		}

		return isset( $this->ruleset->rewrite ) ? $this->ruleset->rewrite : false;
	}

	/**
	 * Check whether the current should be performing a redirect.
	 * Returns false if working from the admin.
	 *
	 * @return boolean
	 */
	public function should_redirect() : bool {
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return false;
		}

		$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
		$uri = ltrim( $uri, '/' );
		return ( $this->pattern() === $uri || $this->is_regex_target() );
	}

	/**
	 * Check whether the current target has regex placeholders.
	 *
	 * @return boolean
	 */
	public function is_regex_target() : bool {
		$target      = $this->target();
		$preg_result = @preg_match_all( '/\$[0-9]/', $target );

		return ( $preg_result > 0 );
	}
}
