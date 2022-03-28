<?php

namespace IDG\Configuration\Redirects;

/**
 * Base class for managing redirects and rewrites.
 */
class Base {
	/**
	 * Whether the current request is secure.
	 *
	 * @var boolean
	 */
	private $is_https = false;

	/**
	 * The current request host.
	 *
	 * @var string|null
	 */
	protected $host = null;

	/**
	 * The current request path.
	 *
	 * @var string|null
	 */
	protected $uri = null;

	/**
	 * Array of rules to be used in the
	 * redirect or rewrites.
	 *
	 * @var array
	 */
	public $rules = [];

	/**
	 * Instantiates the class and assigns class objects.
	 */
	public function __construct() {
		$https          = isset( $_SERVER['HTTPS'] ) ?: '';
		$this->is_https = strtolower( $https ) ? true : false;
		$this->host     = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING );
		$this->uri      = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );
	}

	/**
	 * Adds a rule to the list that will be checked and executed.
	 *
	 * @param string $pattern The pattern to be used to check against the uri.
	 * @param string $target The target redirect or write.
	 * @param int    $code The redirect status code.
	 * @return self
	 */
	public function add_rule( string $pattern, string $target = null, int $code = null ) {
		if ( ! $this->check_host() ) {
			return $this;
		}

		$this->rules[] = new Rule(
			(object) [
				'pattern' => $pattern,
				'target'  => $target,
				'code'    => $code,
			]
		);

		return $this;
	}

	/**
	 * Check whether the current uri is the WP healthcheck.
	 *
	 * @return boolean
	 */
	protected function is_healthcheck() : bool {
		return ( '/cache-healthcheck' === $this->uri );
	}

	/**
	 * Check whether currently running under WP cli.
	 *
	 * @return boolean
	 */
	protected function is_cli() : bool {
		return ( defined( 'WP_CLI' ) && WP_CLI );
	}

	/**
	 * Get the scheme of the given URL.
	 *
	 * @param string $url The url to get the scheme of.
	 * @return string
	 */
	protected function get_url_scheme( string $url ) : string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Executing before wp_parse_url() is available.
		$schema = parse_url( $url, PHP_URL_SCHEME );

		if ( $schema ) {
			return '';
		}

		return 'http://';
	}

	/**
	 * Checks wheter the current host contains a subdomain.
	 *
	 * @return boolean
	 */
	public function is_subdomain() : bool {
		preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $this->host, $matches );
		$domain    = defined( 'IDG_REDIRECT_RULES_DOMAIN' ) ? IDG_REDIRECT_RULES_DOMAIN : $matches['domain'];
		$subdomain = rtrim( strstr( $this->host, $domain, true ), '.' );

		return ! empty( $subdomain );
	}

	/**
	 * Get the current full url.
	 *
	 * @param boolean $use_protocol Whether to return the protocol/scheme.
	 * @return string
	 */
	public function get_full_url( bool $use_protocol = true ) : string {
		$protocol = $this->is_https ? 'https' : 'http';
		$protocol = $use_protocol ? "$protocol://" : '';
		return rtrim( "$protocol$this->host$this->uri", '/' );
	}

	/**
	 * Check that the host and URI are set.
	 *
	 * @return bool
	 */
	protected function check_host() : bool {
		return ( $this->host && $this->uri );
	}
}
