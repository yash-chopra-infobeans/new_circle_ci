<?php

namespace IDG\Publishing_Flow;

/**
 * The base class for handling deployments.
 */
class Deploy {

	/**
	* Filter hook for changing the request arguments of an api request.
	*/
	const FILTER_REQUEST_ARGS = 'idg_publishing_flow_deploy_request_args';

	const FILTER_DISALLOWED_META = 'idg_publishing_flow_disallowed_meta';

	/**
	 * The current post being deployed.
	 *
	 * @var \WP_Post
	 */
	 public $post = null;

	/**
	 * The current term being deployed.
	 *
	 * @var \WP_Term
	 */
	public $term = null;

	/**
	 * The current response from term deployment.
	 *
	 * @var object
	 */
	public $response = null;

	/**
	 * The http response from term deployment.
	 *
	 * @var object
	 */
	public $response_http = null;

	/**
	 * Whether there is an erroneous response.
	 *
	 * @var boolean
	 */
	public $response_error = false;

	/**
	 * Get the payload that is to be sent.
	 *
	 * @return array
	 */
	public function get_payload() : array {
		return $this->payload;
	}

	/**
	 * Get the target endpoint url of the given type.
	 *
	 * @param string $type The type of endpoint.
	 * @return string
	 */
	public function get_target_url( $type ) : string {
		$publication = Sites::get_publication_by_id( $this->publication_id );
		$endpoint    = $this->endpoints[ $type ];

		if ( ! $publication ) {
			return '';
		}

		$http = 'http';

		if ( \is_ssl() ) {
			$http = 'https';
		}
		

		$www = '';

		if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) && 'production' === VIP_GO_APP_ENVIRONMENT ) {
			$www = 'www.';
		}

		return "{$http}://{$www}{$publication->host}/wp-json/idg/v1{$endpoint}";
	}

	/**
	 * Get the source for passing to the target deployment. This
	 * is usually the current site.
	 *
	 * @return string
	 */
	public function get_source() : string {
		$site_url = get_site_url();
		$site_url = preg_replace( '/http[s]?:\/\//', '', $site_url );

		return $site_url;
	}

	/**
	 * Compile the headers for the payload and request to
	 * the destination site.
	 *
	 * @param array   $headers Any additional headers that might be required.
	 * @param boolean $merge Whether passed headers are to be merged or replaced.
	 * @return object
	 */
	public function add_headers( $headers = [], $merge = true ) : object {
		if ( ! $this->publication_id ) {
			$publication          = Sites::get_post_publication( $this->post->ID );
			$this->publication_id = $publication->term_id;
		}

		$auth_tokens = json_decode( get_term_meta( $this->publication_id, Sites::TERM_META_ACCESS_TOKEN, true ) );

		$this->headers[ PUBLISHING_FLOW_ENTRY_ORIGIN_HEADER ] = $this->get_source();
		$this->headers['Authorization']                       = "Bearer $auth_tokens->access_token";

		if ( $merge ) {
			$this->headers = array_merge( $this->headers, $headers );
		} else {
			$this->headers = $headers;
		}

		return $this;
	}

	/**
	 * Get the status of the request.
	 *
	 * @return mixed
	 */
	public function get_status() {
		if ( $this->response_http ) {
			return $this->response_http->get_status();
		}

		return null;
	}

	/**
	 * Get the data of the request.
	 *
	 * @return object
	 */
	public function get_data() {
		if ( $this->response_error ) {
			return [
				$this->response->get_error_message(),
			];
		}

		if ( $this->response_http ) {
			return json_decode( $this->response_http->get_data() );
		}

		return null;
	}

	/**
	 * Check whether the request has failed.
	 *
	 * @return boolean
	 */
	public function failed() : bool {
		if ( $this->response_error ) {
			return true;
		}

		return ( $this->response_http->get_status() !== 200 );
	}
}
