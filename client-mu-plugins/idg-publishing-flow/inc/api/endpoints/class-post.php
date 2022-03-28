<?php
/**
 * Manages/creates post from the recieved request.
 *
 * @package idg-publishing-flow
 */

namespace IDG\Publishing_Flow\API\Endpoints;

use IDG\Publishing_Flow\API\Request;
use IDG\Publishing_Flow\Data\Featured_Image;
use IDG\Publishing_Flow\Data\Taxonomies;
use IDG\Publishing_Flow\Data\Content;
use IDG\Publishing_Flow\API\Data\Users;

/**
 * Handles and manages any Post requests.
 *
 * Each method should correspond with the defined
 * endpoint routes.
 * 
 * @SuppressWarnings(PHPMD).
 */
class Post extends Request {
	const FILTER_CLEAN_POST_META_KEYS = 'idg_publishing_flow_clean_meta_keys';

	const HOOK_AFTER_DEPLOY_ARTICLE = 'idg_publishing_flow_after_deploy_article';

	const HOOK_PREINSERT_META = 'idg_publishing_flow_preinsert_meta';

	const HOOK_AFTER_INGEST = 'idg_publishing_flow_after_ingest';

	/**
	 * A list of endpoints to register and use.
	 *
	 * Keys should coincide with the method to be used
	 * for the endpoint called. In the below case,
	 * /post/create will call the create() method due
	 * to the key definition.
	 */
	const REST_ROUTES = [
		'create' => '/post/create',
		'update' => '/post/update',
		'delete' => '/post/unpublish',
	];

	/**
	 * The validation requirements that are used
	 * to check against the request data.
	 *
	 * @var array
	 */
	public $validate = [
		'authors'                => 'required|array',
		'authors.*.display_name' => 'required',
		'authors.*.email'        => 'required|email',
		'title'                  => 'required',
		'post_name'              => 'alpha_dash',
		'content'                => 'required',
	];

	/**
	 * Post id being processed.
	 *
	 * @var int
	 */
	public $post_id = null;

	/**
	 * Whether the current request is
	 * an update request.
	 *
	 * @var boolean
	 */
	public $updating = false;

	/**
	 * Create the post from the request.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function create( $request ) {
		// Disable revisions to keep ids intact and avoid overwrite for updates.
		// Revisions kept on Content Hub.
		add_filter( 'wp_revisions_to_keep', '__return_zero', 1, 1 );
		add_filter( 'idg_bypass_query_cache', '__return_true' );

		if ( ! $this->check_entry_origin( $request ) ) {
			return $this->create_response();
		}

		if ( ! $this->validate_payload( $request ) ) {
			return $this->create_response();
		}

		$body = $request->get_body_params();

		$this->post_id = $body['id'];

		idg_set_error_report_meta( [ 'content_body' => $body ] );

		$response = [];

		try {
			$inserted_post = $this->insert_post( $body );

			$this->post_id = $inserted_post['post_id'];

			$response = $inserted_post;

			// May be no longer required. Leaving commented out incase.

			do_action( self::HOOK_AFTER_INGEST, $response );
		} catch ( \ErrorException $e ) {
			wp_trash_post( $this->post_id );

			$this->response_code = 400;
			$this->errors        = [
				$e->getMessage(),
			];

			idg_set_error_report_meta(
				[
					'errors' => [
						'ErrorException'             => $e,
						'ErrorException::getMessage' => $e->getMessage(),
					],
				]
			);
		}

		return $this->create_response( $response );
	}

	/**
	 * Handles removal of duplicates when syncing between
	 * content hub and delivery site by checking the onecms
	 * id values.
	 *
	 * @TODO: This is likely to be redundant in post-launch, if
	 * not it should be moved to the workarounds.php file.
	 *
	 * @param array $body Post body data.
	 * @return void
	 */
	public function remove_duplicates( array $body ) : void {
		if ( isset( $body['meta']['old_id_in_onecms'] ) && ! empty( $body['meta']['old_id_in_onecms'] ) ) {
			idg_notify_error(
				'DeliverySite',
				'Removing legacy duplicates'
			);

			$onecms_args  = [
				'posts_per_page' => -1,
				'post__not_in'   => [ $this->post_id ],
				'post_type'      => 'post',
				'meta_key'       => 'old_id_in_onecms',
				'meta_value'     => $body['meta']['old_id_in_onecms'], // phpcs:ignore
			];
			$onecms_query = new \WP_Query( $onecms_args );

			if ( $onecms_query->post_count > 0 ) {
				array_walk(
					$onecms_query->posts,
					function( $post ) {
						wp_trash_post( $post->ID );
					}
				);
			}
		}
	}

	/**
	 * Runs the insert post process and handles all pieces
	 * that make the post complete, such as authors, terms, etc.
	 *
	 * @param array $body The payload body.
	 * @throws \ErrorException When author could not be created.
	 * @return array
	 */
	public function insert_post( array $body ) : array {
		$stored_authors = Users::instance()->create( $body['authors'] );

		if ( is_wp_error( $stored_authors ) ) {
			$this->response_code = 500;
			$this->errors        = $stored_authors;

			throw new \ErrorException( 'Could not create author.' );
		}

		$content        = Content::instance()->process_blocks( $body['content'] );
		$parsed         = parse_blocks( $content );
		$is_new_version = true;
		foreach ( $parsed as $key => $item ) {
			$block_name = $item['blockName'];
			if ( $block_name === 'idg-base-theme/product-chart-block' ) {
				if ( is_array( $item['attrs']['productData'] ) && ! empty( $item['attrs']['productData'] ) ) {
					foreach ( $item['attrs']['productData'] as $data ) {
						$version = $data['version'];
						if ( $version === '1.0.0' || ! empty( $data['productContent'] ) ) {
							$is_new_version = false;
							break 2;
						} elseif ( $version === '1.1.0' && empty( $data['productContent'] ) ) {
							$is_new_version = true;
						}
					}
				}
			}
		}

		$post_date         = $body['post_date'] ?: '';
		$post_date         = $this->correct_datetime( $post_date );
		$post_modified     = $body['post_modified'] ?: '';
		$post_modified     = $this->correct_datetime( $post_modified );
		$post_modified_gmt = $body['post_modified_gmt'] ?: '';
		$post_modified_gmt = $this->correct_datetime( $post_modified_gmt );

		$post_data = [
			'post_author'       => $stored_authors[0],
			'post_content'      => $content,
			'post_title'        => $body['title'],
			'post_name'         => isset( $body['post_name'] ) ? $body['post_name'] : null,
			'post_status'       => 'publish',
			'post_date'         => $post_date ?: null,
			'post_modified'     => $post_modified ?: null,
			'post_modified_gmt' => $post_modified_gmt ?: null,
		];

		if ( get_post_status( $body['id'] ) ) {
			$this->updating = true;
		}

		// Set the id for the post, use `import_id` key for new articles.
		$id_key               = $this->updating ? 'ID' : 'import_id';
		$post_data[ $id_key ] = isset( $body['id'] ) ? $body['id'] : null;


		$headers = array_change_key_case( getallheaders(), CASE_UPPER );
		if ( isset( $headers['X-IDG-PUBFLOW-SKIP-MODIFIED-DATE'] ) ) {
			add_filter( 'wp_insert_post_data', 'idg_alter_post_modification_time', 99, 2 );
		}

		if ( $is_new_version ) {
			$post_id = wp_insert_post( wp_slash( $post_data ) );
		} else {
			$post_id = wp_insert_post( $post_data );
		}
		
		remove_filter( 'wp_insert_post_data', 'idg_alter_post_modification_time', 99, 2 );

		$entry_meta = [];

		if ( isset( $body['meta'] ) ) {
			$entry_meta = $body['meta'];
		}

		$entry_meta = apply_filters( self::HOOK_PREINSERT_META, $entry_meta );
		$this->insert_meta( $entry_meta, $post_id );

		$entry_terms = [];

		if ( isset( $body['terms'] ) ) {
			$entry_terms = $body['terms'];
		}

		Taxonomies::instance()->insert_post_terms( $entry_terms, $post_id );

		idg_notify_error( 'DeliverySite', 'Stored terms', [ 'terms' => $entry_terms ] );

		$featured_image_id = null;

		if ( isset( $body['featured_image'] ) && is_array( $body['featured_image'] ) ) {
			$featured_image = $body['featured_image'];

			$featured_image_id = Featured_Image::instance()->store_from_url( $featured_image );
			set_post_thumbnail( $post_id, $featured_image_id );
		}

		idg_notify_error( 'DeliverySite', 'Featured Image ID', [ 'featured_image_id' => $featured_image_id ] );

		do_action( self::HOOK_AFTER_DEPLOY_ARTICLE, $post_id, $body );

		idg_notify_error(
			'DeliverySite',
			'Post and Content',
			[
				'post_id'      => $post_id,
				'content_body' => $body,
			]
		);

		// @todo: we need a better way for managing these so they can be cleared on publish/update.
		wpcom_vip_cache_delete( sprintf( 'get_sponsorship_%s', $post_id ), 'idg_base_theme' );
		wpcom_vip_cache_delete( sprintf( 'get_eyebrow_%s', $post_id ), 'idg_base_theme' );
		wpcom_vip_purge_edge_cache_for_post( $post_id );

		idg_notify_error( 'DeliverySite', 'Article Created/Updated' );

		return [
			'featured_image_id' => $featured_image_id,
			'post_id'           => $post_id,
			'permalink'         => get_post_permalink( $post_id ),
		];
	}

	/**
	 * Formats the given timestamp and corrects with
	 * the timezone offset in the WordPress settings.
	 *
	 * @param string $datetime The date/time string to correct.
	 * @return string
	 */
	private function correct_datetime( string $datetime = null ) : string {
		if ( ! $datetime || ! \is_string( $datetime ) ) {
			return '';
		}

		$timezone = wp_timezone();

		$datetime = new \DateTime( $datetime );
		$datetime->setTimeZone( $timezone );

		return $datetime->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Cleans out the provided meta keys when an
	 * article is sync'd across, allowing refresh of
	 * certain meta or tidyup pre-insert of the post.
	 *
	 * @param int $post_id The post ID to clean meta of.
	 * @return void
	 */
	private function clean_post_meta( $post_id ) : void {
		$clean_meta = apply_filters(
			self::FILTER_CLEAN_POST_META_KEYS,
			[
				'_oembed_.*',
				'_edit_last',
			]
		);

		$meta = get_post_meta( $post_id );

		$found_keys = [];

		foreach ( $clean_meta as $pattern ) {
			$found_keys = array_merge( preg_grep( "/^$pattern$/", array_keys( $meta ) ), $found_keys );
		}

		foreach ( $found_keys as $found_key ) {
			delete_post_meta( $post_id, $found_key );
		}
	}

	/**
	 * Insert meta depending on how it's passed, handled recusively.
	 *
	 * @param array $entry_meta The list of meta and values to be stored.
	 * @param int   $post_id The post id which the meta should be attached to.
	 * @param mixed $meta_key The key which the mata should be inserted under.
	 * @return void
	 *
	 * @TODO: Check the meta is registered before storage.
	 */
	private function insert_meta( array $entry_meta, $post_id, $meta_key = null ) : void {
		$registered_meta = get_registered_meta_keys( 'post', '' );

		$this->clean_post_meta( $post_id );

		foreach ( $entry_meta as $key => $meta ) {
			if ( isset( $registered_meta[ $key ] ) && $registered_meta[ $key ]['single'] && is_array( $meta ) ) {
				update_post_meta( $post_id, $key, $meta[0] );

				continue;
			}

			if ( is_array( $meta ) && is_associative_array( $meta ) ) {
				$this->insert_meta( $meta, $post_id );
			} else {
				if ( ! is_int( $key ) ) {
					$meta_key = $key;
				}

				update_post_meta( $post_id, $meta_key, $meta );
			}
		}
	}

	/**
	 * Update the post from the request.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function update( $request ) {
		$this->validate['id'] = 'required|numeric|idExists';
		$this->updating       = true;

		return $this->create( $request );
	}

	/**
	 * Delete the post from the request.
	 *
	 * Does not completely delete the post from
	 * the databse, but instead marks as trash incase
	 * recovery is required.
	 *
	 * @param \WP_Request $request The request being made.
	 * @return object
	 */
	public function delete( $request ) {
		$this->validate = [
			'id' => 'required|numeric|idExists',
		];

		if ( ! $this->validate_payload( $request ) ) {
			return $this->create_response();
		}

		$body = $request->get_body_params();

		$post_status = get_post_status( $body['id'] );

		if ( 'publish' !== $post_status ) {
			$this->response_code = 400;
			$this->errors        = [
				'Post is not published.',
			];
		} else {
			$trashed = wp_trash_post( $body['id'] );

			if ( ! $trashed ) {
				$this->response_code = 400;
				$this->errors        = [
					'Could not remove post.',
				];
			}
		}

		// @todo: we need a better way for managing these so they can be cleared on publish/update.
		wpcom_vip_cache_delete( sprintf( 'get_sponsorship_%s', $post_id ), 'idg_base_theme' );
		wpcom_vip_cache_delete( sprintf( 'get_eyebrow_%s', $post_id ), 'idg_base_theme' );
		wpcom_vip_purge_edge_cache_for_post( $post_id );

		do_action( self::HOOK_AFTER_INGEST, $response );

		return $this->create_response(
			[
				'message' => 'Post removed.',
			]
		);
	}
}
