<?php
/**
 * Handles asset manager meta fields.
 *
 * @package IDG Asset Manager plugin
 */

namespace IDG\Asset_Manager;

/**
 * Attachment meta class.
 */
class Meta_Fields {
	const META_IMAGE_RIGHTS_NOTES = 'image_rights_notes';
	const META_CREDIT_URL         = 'credit_url';
	const META_CREDIT             = 'credit';
	const META_ACTIVE             = 'active';
	const META_FILE               = '_wp_attached_file';
	const META_METADATA           = '_wp_attachment_metadata';
	const META_ALT                = '_wp_attachment_image_alt';
	const META_TYPE               = 'media_type';
	const META_JW_PLAYER_MEDIA_ID = 'jw_player_media_id';
	const META_CUSTOM_STATUS      = 'status';
	const META_ALTREQUIRED        = 'isAltRequired';
	

	/**
	 * Add hooks and filters when class is initialized.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_attachment_meta' ] );
		add_action( 'init', [ $this, 'register_attachment_fields' ] );
		add_action( 'init', [ $this, 'register_assetManager_fields' ] );
		add_action( 'init', [ $this, 'register_asset_fields' ] );
		add_filter( 'is_protected_meta', [ $this, 'override_protected_meta' ], 10, 3 );
	}

	/**
	 * Register attachment meta fields.
	 *
	 * @return void
	 */
	public function register_attachment_meta() : void {
		register_post_meta(
			'attachment',
			self::META_ACTIVE,
			[
				'type'         => 'boolean',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => true,
			]
		);
		
		register_post_meta(
			'attachment',
			self::META_ALTREQUIRED,
			[
				'type'         => 'boolean',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => false,
			]
		);
	}
	/**
	 * Register attachment fields.
	 *
	 * @return void
	 */
	public function register_attachment_fields() : void {
		register_post_meta(
			'attachment',
			self::META_METADATA,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_ALT,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_IMAGE_RIGHTS_NOTES,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Register assetManager fields.
	 *
	 * @return void
	 */
	public function register_assetManager_fields() : void {
		register_post_meta(
			'attachment',
			self::META_CREDIT_URL,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_CREDIT,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_TYPE,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}
	/**
	 * Register asset fields.
	 *
	 * @return void
	 */
	public function register_asset_fields() : void {
		register_post_meta(
			'attachment',
			self::META_JW_PLAYER_MEDIA_ID,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_CUSTOM_STATUS,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
		register_post_meta(
			'attachment',
			self::META_FILE,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}   

	/**
	 * As we want to be able to update attachment meta and alt text we need to use this
	 * filter so we can return false for those protected meta fields.
	 *
	 * @param boolean $protected whether or not the meta field is protected.
	 * @param string  $meta_key meta field key.
	 * @return boolean
	 */
	public function override_protected_meta( bool $protected, string $meta_key ) : bool {
		if (
			'_wp_attached_file' === $meta_key
			|| '_wp_attachment_metadata' === $meta_key
			|| '_wp_attachment_image_alt' === $meta_key
		) {
			return false;
		}

		return $protected;
	}
}
