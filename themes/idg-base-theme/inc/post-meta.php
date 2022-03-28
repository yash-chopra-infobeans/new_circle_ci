<?php
/**
 * File for managing custom post meta fields.
 *
 * @package idg-base-theme
 */

if ( ! function_exists( 'idg_register_copyright_info' ) ) {
	/**
	 * Adds meta field for copyright info.
	 *
	 * @return void
	 */
	function idg_register_copyright_info() {
		register_meta(
			'post',
			'_idg_copyright_info',
			[
				'description' => 'String value for copyright info on post.',
				'type'        => 'string',
				'single'      => true,
				'default'     => '',
			]
		);
	}
}
add_action( 'init', 'idg_register_copyright_info' );

if ( ! function_exists( 'idg_register_content_type' ) ) {
	/**
	 * Adds meta field for copyright info.
	 *
	 * @return void
	 */
	function idg_register_content_type() {
		register_post_meta(
			'',
			'content_type',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}
}
add_action( 'init', 'idg_register_content_type' );

if ( ! function_exists( 'idg_register_suppress_meta' ) ) {
	/**
	 * Post meta for supressing html meta.
	 *
	 * @return void
	 */
	function idg_register_suppress_meta() {
		register_post_meta(
			'',
			'suppress_html_meta',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '{}',
			]
		);
	}
}
add_action( 'init', 'idg_register_suppress_meta' );

if ( ! function_exists( 'idg_register_byline_meta' ) ) {
	/**
	 * Adds meta field for copyright info.
	 *
	 * @return void
	 */
	function idg_register_byline_meta() {
		register_meta(
			'post',
			'byline',
			[
				'description'  => 'String value for byline information (legacy).',
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '',
			]
		);
	}
}
add_action( 'init', 'idg_register_byline_meta' );

if ( ! function_exists( 'idg_add_copyright_info' ) ) {
	/**
	 * Adds meta field for copyright info.
	 *
	 * @return void
	 */
	function idg_add_copyright_info() {
		$post_types = apply_filters( 'idg_posttypes_allowed_copyright_info', [] );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'idg_meta_box',
				'Copyright info',
				'idg_copyright_info_html',
				$post_type,
				'side'
			);
		}
	}
}
add_action( 'add_meta_boxes', 'idg_add_copyright_info' );

if ( ! function_exists( 'idg_copyright_info_html' ) ) {
	/**
	 * Callback for copyright meta field.
	 *
	 * @param object $post Current post object.
	 * @return void
	 */
	function idg_copyright_info_html( $post ) {
		$value = get_post_meta( $post->ID, '_idg_copyright_info', true );
		?>
		<div>
			<label class="editor-meta-label"><?php echo esc_html__( 'Copyright text', 'idg-base-theme' ); ?></label>
			<input class="editor-meta-input" type="url" name="_idg_copyright_info" value="<?php echo esc_attr( $value ); ?>">
		</div>
		<?php
	}
}

if ( ! function_exists( 'idg_save_copyright_info' ) ) {
	/**
	 * Update function for copyright meta field.
	 *
	 * @param int $post_id Current post ID.
	 * @return void
	 */
	function idg_save_copyright_info( $post_id ) {
		if ( array_key_exists( '_idg_copyright_info', $_POST ) ) { // phpcs:ignore
			update_post_meta(
				$post_id,
				'_idg_copyright_info',
				$_POST['_idg_copyright_info'] // phpcs:ignore
			);
		}
	}
}
add_action( 'save_post', 'idg_save_copyright_info' );

if ( ! function_exists( 'idg_register_jwplayer_video_meta' ) ) {
	/**
	 * Adds meta field for jwplayer video meta.
	 *
	 * @return void
	 */
	function idg_register_jwplayer_video_meta() {
		register_post_meta(
			'',
			'featured_video_id',
			[
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'',
			'supress_floating_video',
			[
				'type'         => 'boolean',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}
}
add_action( 'init', 'idg_register_jwplayer_video_meta' );

if ( ! function_exists( 'idg_register_prevent_index' ) ) {
	/**
	 * Adds meta field for preventing index options in pages/posts.
	 *
	 * @return void
	 */
	function idg_register_prevent_index() {
		register_post_meta(
			'',
			'prevent_index',
			[
				'type'         => 'number',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}
}
add_action( 'init', 'idg_register_prevent_index' );

if ( ! function_exists( 'idg_register_external_post_link' ) ) {
	/**
	 * Adds meta field for External post link.
	 *
	 * @return void
	 */
	function idg_register_external_post_link() {
		register_post_meta(
			'post',
			'external_post_link',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}
}
add_action( 'init', 'idg_register_external_post_link' );

if ( ! function_exists( 'delete_prevent_index_key' ) ) {
	/**
	 * Delete 'prevent_index' meta key when value is 0.
	 *
	 * @param int $post_id it contain the post id.
	 */
	function delete_prevent_index_key( $post_id ) {
		$meta_value = (int) get_post_meta( $post_id, 'prevent_index', true );
		
		if ( 0 === $meta_value ) {
			delete_post_meta( $post_id, 'prevent_index' );
		}
	}
}
add_action( 'save_post', 'delete_prevent_index_key' );
add_action( 'idg_publishing_flow_after_deploy_article', 'delete_prevent_index_key', 10 );



if ( ! function_exists( 'delete_external_link_key' ) ) {
	/**
	 * Delete 'external_post_link' meta key when value is empty.
	 *
	 * @param int $post_id it contain the post id.
	 */
	function delete_external_link_key( $post_id ) {
		$meta_value = trim( get_post_meta( $post_id, 'external_post_link', true ) );

		if ( '' === $meta_value ) {
			delete_post_meta( $post_id, 'external_post_link' );
		}
	}
}
add_action( 'save_post', 'delete_external_link_key' );
add_action( 'idg_publishing_flow_after_deploy_article', 'delete_external_link_key', 10 );
