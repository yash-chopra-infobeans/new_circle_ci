<?php

namespace IDG\Asset_Manager;

use Exception;

/**
 * Extend JW Player API class.
 */
class Jw_Player extends \Jwplayer\JwplatformAPI {
	/**
	 * JwPlayer account key
	 *
	 * @var string JwPlayer account key
	 */
	private $key;

	/**
	 * JwPlayer account secret
	 *
	 * @var string JwPlayer account secret
	 */
	private $secret;

	/**
	 * JwPlayer reporting api key
	 *
	 * @var string JwPlayer reporting api key
	 */
	private $reporting_api_key = null;

	/**
	 * Library.
	 *
	 * @var string library.
	 */
	private $library;

	/**
	 * Call parent constructor.
	 *
	 * @param string $key JW Player API key.
	 * @param string $secret JW Player API secret.
	 * @param string $reporting_api_key JW Player Rerporting API key.
	 */
	public function __construct( $key, $secret, $reporting_api_key = '' ) {
		$this->key               = $key;
		$this->secret            = $secret;
		$this->reporting_api_key = $reporting_api_key;

		// Determine which HTTP library to use:
		// check for cURL, else fall back to file_get_contents.
		if ( function_exists( 'curl_init' ) ) {
			$this->library = 'curl';
		} else {
			$this->library = 'fopen';
		}

		parent::__construct( $key, $secret, $reporting_api_key );
	}

	/**
	 * Upload video to JW player.
	 *
	 * @param string $file_path file path.
	 * @param array  $upload_link upload link.
	 * @param string $api_format api format.
	 * @throws Exception If we failed to get a file handle, throw an Exception.
	 * @return mixed|string
	 */
	public function upload( $file_path, $upload_link = [], $api_format = 'php' ) {
		// phpcs:disable
		$url = $upload_link['protocol'] . '://' . $upload_link['address'] . $upload_link['path'] .
			'?key=' . $upload_link['query']['key'] . '&token=' . $upload_link['query']['token'] .
			'&api_format=' . $api_format;

		// A new variable included with curl in PHP 5.5 - CURLOPT_SAFE_UPLOAD - prevents the
		// '@' modifier from working for security reasons (in PHP 5.6, the default value is true)
		// http://stackoverflow.com/a/25934129
		// http://php.net/manual/en/migration56.changed-functions.php
		// http://comments.gmane.org/gmane.comp.php.devel/87521
		if ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < 50500 ) {
			$post_data = [ 'file' => '@' . $file_path ];
		} else {
			if ( ! filter_var( $file_path, FILTER_SANITIZE_URL ) ) {
				$post_data = [ 'file' => new \CURLFile( $file_path ) ];
			} else {
				$temp = tmpfile();
				// open original file
				$file_in = fopen($file_path, "rb");
				//If we failed to get a file handle, throw an Exception.
				if ($file_in === false) throw new Exception('Could not get file handle.');
				// read until EOF
				while (!feof($file_in)) {
					// read bytes
					$bytes = fread($file_in, 8192);
					// write to temp file
					fwrite($temp, $bytes);
				}
				// close original file handler
				fclose($file_in);
				fseek( $temp, 0 );
				$local_file_path = stream_get_meta_data( $temp )['uri'];
				$post_data       = [ 'file' => new \CURLFile( $local_file_path ) ];
			}
		}

		$response = null;
		switch ( $this->library ) {
			case 'curl':
				$curl = curl_init();
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $curl, CURLOPT_URL, $url );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data );

				$response = curl_exec( $curl );

				$err_no = curl_errno( $err_no );

				curl_close( $curl );
				break;
			default:
				$response = 'Error: No cURL library';
		}

		if ( $err_no == 0 ) {
			return unserialize( $response );
		} else {
			return 'Error #' . $err_no . ': ' . curl_error( $curl );
		}

		// phpcs:enable
	}
}
