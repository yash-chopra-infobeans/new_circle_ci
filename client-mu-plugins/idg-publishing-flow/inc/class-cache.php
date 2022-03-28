<?php

namespace IDG\Publishing_Flow;

/**
 * Management of some cached elements.
 */
class Cache {
	/**
	 * The group being used for caching.
	 */
	const CACHE_GROUP = 'publishing_flow';

	/**
	 * Initialise the storage of saving the request to cache.
	 */
	public function __construct() {
		add_filter( 'rest_pre_insert_post', [ $this, 'set_request' ], 10, 2 );
	}

	/**
	 * Caches the request before a post is entered/stored in
	 * the database.
	 *
	 * This is generally used for aiding in management of data
	 * being create by Gutenberg during it's save cycle, whether
	 * if that is from an autosave, actioned save or publish action.
	 *
	 * @param object           $prepared_post     The post from the request - not used, for hook purposes only.
	 * @param \WP_REST_Request $request The request object.
	 * @return object
	 */
	public function set_request( $prepared_post, \WP_REST_Request $request ) {
		$user_id      = get_current_user_id();
		$request_body = $request->get_json_params();

		if ( isset( $request_body['__publishing_flow_action'] ) ) {
			wpcom_vip_cache_set( "action_${user_id}", $request_body['__publishing_flow_action'], self::CACHE_GROUP );
		}

		wpcom_vip_cache_set( "request_${user_id}", $request_body, self::CACHE_GROUP );

		return $prepared_post;
	}

	/**
	 * Get the current action of the post save.
	 *
	 * @return mixed
	 */
	public static function get_action() {
		$user_id = get_current_user_id();
		return wpcom_vip_cache_get( "action_${user_id}", self::CACHE_GROUP );
	}

	/**
	 * Method is for retrieving known meta that is called multiple
	 * times in one render.
	 *
	 * @param boolean $post_id The post id the meta is stored against.
	 * @return mixed
	 */
	public static function get_all_meta( $post_id = false ) {
		$user_id = get_current_user_id();
		$cache   = wpcom_vip_cache_get( "request_${user_id}", self::CACHE_GROUP );

		if ( isset( $cache['meta'] ) ) {
			return $cache['meta'];
		} elseif ( $post_id ) {
			$value = get_post_meta( $post_id );
		}

		return $value;
	}

	/**
	 * Method is for retrieving known meta that is called multiple
	 * times in one render.
	 *
	 * @param string  $key      The meta key of data to retrieve.
	 * @param boolean $post_id The post id the meta is stored against.
	 * @return mixed
	 */
	public static function get_meta( string $key, $post_id = false ) {
		$user_id = get_current_user_id();
		$cache   = wpcom_vip_cache_get( "request_${user_id}", self::CACHE_GROUP );

		if ( isset( $cache['meta'][ $key ] ) ) {
			$value = $cache['meta'][ $key ];
		} elseif ( $post_id ) {
			$value = get_post_meta( $post_id, $key, true );
		}

		return $value;
	}

	/**
	 * Method is for retrieving known post terms that is called multiple
	 * times in one render.
	 *
	 * @param string  $key      The term key of data to retrieve.
	 * @param boolean $post_id The post id the term is stored against.
	 * @return mixed
	 */
	public static function get_post_term( string $key, $post_id = false ) {
		$user_id   = get_current_user_id();
		$cache_key = "post_${post_id}_term_${key}_${user_id}";
		$value     = wpcom_vip_cache_get( $cache_key, self::CACHE_GROUP );

		if ( $value ) {
			return $value;
		}

		$cache = wpcom_vip_cache_get( "request_${user_id}", self::CACHE_GROUP );

		if ( isset( $cache[ $key ] ) ) {
			$value = array_map(
				function( $id ) use ( $key ) {
					return get_term_by( 'ID', $id, $key );
				},
				$cache[ $key ]
			);
		} elseif ( $post_id ) {
			$value = wp_get_post_terms( $post_id, $key );
		}

		wpcom_vip_cache_set( $cache_key, $value, self::CACHE_GROUP );

		return $value;
	}

	/**
	 * Clears the cache set for the publication taxonomies.
	 *
	 * @param array $users A list of user ids to remove the cache of.
	 * @return void
	 */
	public static function clear_all_publications( array $users = [] ) : void {
		if ( empty( $users ) ) {
			$users = get_users(
				[
					'number' => -1, // Get all of them.
					'fields' => 'ID', // only get IDs.
				]
			);
		}

		// Clear publication cache for each user.
		foreach ( $users as $user_id ) {
			$cache_string     = 'term_' . Sites::TAXONOMY . "_${user_id}";
			$cache_string_all = 'term_all_' . Sites::TAXONOMY . "_${user_id}";

			wpcom_vip_cache_delete( $cache_string, self::CACHE_GROUP );
			wpcom_vip_cache_delete( $cache_string_all, self::CACHE_GROUP );
		}
	}
}
