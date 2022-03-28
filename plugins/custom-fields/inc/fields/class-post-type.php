<?php

namespace Custom_Fields\Fields;

/**
 * Class to apply custom fields to a post type.
 */
final class Post_Type extends Fields_Base {
	const BLOCK_NAME = 'cf/block';

	const CAPABILITY_FILTER = 'cf_capability_';

	/**
	 * The post type to apply fields to.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Should the template be locked, ensuring only custom fields are shown?
	 *
	 * @var boolean
	 */
	public $lock_template;

	/**
	 * Initialize post fields class.
	 *
	 * @param object $config
	 * @param string $post_type - The post type to attach fields to
	 * @param boolean $lock_template
	 */
	public function __construct( object $config, string $post_type, $lock_template = null ) {
		parent::__construct( $config, (object) [
			'kind' => 'postType',
			'name' => $post_type,
			'prop' => 'meta',
		] );

		$this->set_default_capability(
			apply_filters( self::CAPABILITY_FILTER . $post_type, 'edit_posts' )
		);

		$this->enable_add_slashes();

		$this->post_type = $post_type;

		$this->lock_template = $lock_template;

		$this->register_meta();

		add_action( 'admin_init', [ $this, 'set_post_template' ]);

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ], 1 );

		add_action( "rest_pre_insert_{$post_type}", [ $this, 'handle_validation' ], 10, 2 );
	}

	/**
	 * Register post meta for each section that has been registered.
	 *
	 * @return void
	 */
	private function register_meta() : void {
		$meta_args = [
			'auth_callback' => [ $this, 'auth'],
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
		];

		foreach ( $this->config->sections as $section ) {
			register_post_meta(
				$this->post_type,
				$section->name,
				$meta_args
			);
		}
	}

	/**
	 * Auth callback for editing meta.
	 *
	 * @return boolean
	 */
	public function auth() {
		return $this->is_user_authorized();
	}

	/**
	 * Set the post template with the custom fields block to drive functionality.
	 *
	 * @return void
	 */
	public function set_post_template() : void {
		if ( ! $this->is_post_type() ) {
			return;
		}

		$post_type_object = get_post_type_object( $this->post_type );

		if ( isset ( $post_type_object->template ) ) {
			if ( ! $this->should_set_template( $post_type_object->template ) ) {
				return;
			}
		}

		$post_type_object->template = [
			[ self::BLOCK_NAME ],
		];

		// If it is locked, it has been locked elsewhere and we should leave it alone
		// or we have already locked it.
		if (
			$this->lock_template &&
			( ! isset( $post_type_object->template_lock ) || $post_type_object->template_lock !== 'all' )
		) {
			$post_type_object->template_lock = 'all';
		}
	}

	/**
	 * Determine whether to set & lock the post template
	 *
	 * @param array|null $template
	 * @return boolean
	 */
	public function should_set_template( $template ) {
		if ( ! is_array( $template ) ) {
			return true;
		}

		$filtered_template = array_filter( $template ?? [], function( $item ) {
			return $item[0] === self::BLOCK_NAME;
		} );

		if ( count( $filtered_template ) === 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if we are adding or editing a post with the registered post type.
	 *
	 * @return boolean
	 */
	private function is_post_type() {
		global $pagenow;

		if ( ! in_array( $pagenow, ['post.php', 'post-new.php'] ) ) {
			return false;
		}

		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

		if ( $post_type && $this->post_type !== $post_type ) {
			return false;
		}

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( $post_id && $this->post_type !== get_post_type( $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue scripts and styles, but only for the registered post type.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() : void {
		if ( ! $this->is_post_type() ) {
			return;
		}

		$this->enqueue_assets();
	}

	/**
	 * Handle verification of fields on post save.
	 */
	public function handle_validation( $post, $request ) {
		if ( is_autosave() ) {
			return $post;
		}

		$meta = $request->get_param( 'meta' ) ?? [];

		$fields = [];

		foreach ( $this->config->sections as $section ) {
			$fields[ $section->name ] =  $meta[$section->name] ?: get_post_meta( $post->ID, $section->name )[0] ?: '';
		}

		$this->validate( $fields );

		$errors = $this->get_errors();

		if ( is_wp_error( $errors ) ) {
			return $errors;
		}

		$request->set_param( 'meta', array_merge( $meta, $this->get_values() ) );

		return $post;
	}
}
