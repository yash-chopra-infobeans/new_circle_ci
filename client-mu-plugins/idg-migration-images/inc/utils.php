<?php

/**
 * Downloads file, uploads it to WordPress and returns array of file data.
 *
 * @param string $image_url file url.
 * @throws \ErrorException If there's an error such as file doesn't exist.
 * @return array
 */
function idg_download_image( string $image_url ) {
	$image_url = strtok( $image_url, '?' );  // Remove all query parameters.

	$http     = new \WP_Http();
	$response = $http->request( $image_url );

	if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
		throw new \ErrorException( 'Could not request image.' );
	}

	$upload = wp_upload_bits( basename( $image_url ), null, $response['body'] );

	if ( ! empty( $upload['error'] ) ) {
		throw new \ErrorException( 'Could not upload image.' );
	}

	$file_path        = $upload['file'];
	$file_name        = basename( $file_path );
	$file_type        = wp_check_filetype( $file_name, null );
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
	$wp_upload_dir    = wp_upload_dir();

	$post_info = [
		'file_path'      => $file_path,
		'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
		'post_mime_type' => $file_type['type'],
		'post_title'     => $attachment_title,
		'post_status'    => 'inherit',
	];

	return $post_info;
}

/**
 * Try to find the id of the attachment with the guid passed.
 *
 * @param string $guid guid to look for.
 * @return int|null
 */
function idg_get_post_by_guid( string $guid ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- We do not want to use cached results for this process and require current data at time of request.
	return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE guid=%s OR meta_value=%s LIMIT 1", $guid, $guid ) );
}

/**
 * Update post guid field.
 *
 * @param integer $post_id id of the post to update.
 * @param string  $guid value to insert.
 * @return int|false
 */
function idg_update_post_guid( int $post_id, string $guid ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- We do not want to use cached results for this process and require current data at time of request.
	return $wpdb->update( $wpdb->posts, [ 'guid' => $guid ], [ 'ID' => $post_id ] );
}

/**
 * Check if the image has already been migrated by comparing the image domain/host to the site url(content hub url).
 *
 * @param string $image image url.
 * @return boolean
 */
function idg_can_image_be_migrated( string $image ) : bool {
	$image_host = wp_parse_url( $image, PHP_URL_HOST );
	$site_host  = wp_parse_url( get_site_url(), PHP_URL_HOST );

	$allowed_hosts = [
		'images.idgesg.net',
		'images.techhive.com',
		'images.pcworld.com',
		'images.macworld.com',
		'cms-images.idgesg.net',
	];

	if ( ! in_array( $image_host, $allowed_hosts, true ) ) {
		return false;
	}

	// Sanity check in case wp_parse_url return false.
	if ( ! $image_host || ! isset( $image_host ) || ! $site_host || ! isset( $site_host ) ) {
		return false;
	}

	// If $image_host host is equal to $site_url host then the image already exists within the conentub VIP FS. So no need to migrate.
	if ( $image_host === $site_host ) {
		return false;
	}

	return true;
}

/**
 * Check if image is valid.
 *
 * @param string $url image url.
 * @return boolean
 */
function idg_is_valid_image_url( string $url ) : bool {
	$http     = new \WP_Http();
	$response = $http->request( $url );

	if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
		return false;
	}

	return true;
}
