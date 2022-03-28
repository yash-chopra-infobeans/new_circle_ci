<?php

use IDG\Base_Theme\Taxonomy;

require_once __DIR__ . '/vendor/autoload.php';

class IDG_Tax_Form_Field {
	public function __construct( $taxonomy, $args ) {
		$this->taxonomy = $taxonomy;

		$this->field_type         = $args['field_type'];
		$this->meta_name          = $args['meta_name'];
		$this->field_name         = $args['field_name'];
		$this->display_name       = $args['display_name'];
		$this->helper_text        = $args['helper_text'];
		$this->options            = ( isset( $args['options'] ) && is_array( $args['options'] ) ) ? $args['options'] : [];
		$this->image_preview_size = isset( $args['image_preview_size'] ) ? $args['image_preview_size'] : '150s-r1:1';

		register_term_meta(
			$taxonomy,
			$this->meta_name,
			[
				'single' => true,
			]
		);
	}

	public function add_actions() {
		add_action( $this->taxonomy . '_add_form_fields', [ $this, 'add_field' ] );
		add_action( $this->taxonomy . '_edit_form_fields', [ $this, 'edit_field' ] );
		add_action( 'edited_' . $this->taxonomy, [ $this, 'save_field' ] );
		add_action( 'create_' . $this->taxonomy, [ $this, 'save_field' ] );

		if ( $this->field_type === 'image' ) {
			add_action( $this->taxonomy . '_term_edit_form_tag', [ $this, 'edit_form_tag' ] );
			add_action( 'pre_delete_term', [ $this, 'auto_delete_image' ], 10, 2 );
			add_action( 'wp_ajax_' . $this->taxonomy . '_' . $this->meta_name . '_delete_image', [ $this, 'delete_image' ] );
		}
	}

	public function edit_form_tag() {
		echo ' enctype="multipart/form-data"';
	}

	public function auto_delete_image( $term_id, $tax ) {
		global $wp_taxonomies;

		if ( isset( $term_id, $tax, $wp_taxonomies ) && isset( $wp_taxonomies[ $tax ] ) ) {
			$image_id = get_term_meta( $term_id, $this->meta_name, true );

			if ( is_numeric( $image_id ) ) {
				wp_delete_attachment( $image_id );
			}
		}
	}

	function delete_image() {
		$wp_nonce   = filter_input( INPUT_GET, 'wp_nonce', FILTER_SANITIZE_STRING );
		$field_none = sanitize_text_field( $wp_nonce, $this->taxonomy . '_' . $this->meta_name . '_delete_image_nonce' );

		if ( ! isset( $wp_nonce ) && ! wp_verify_nonce( $field_none ) ) {
			exit;
		}

		if ( ! isset( $_GET['term_id'] ) ) {
			echo 'Not Set or Empty';
			exit;
		}

		$term_id = intval( $_GET['term_id'] );
		$imageID = get_term_meta( $term_id, $this->meta_name, true );

		if ( is_numeric( $imageID ) ) {
			wp_delete_attachment( $imageID );
			delete_term_meta( $term_id, $this->meta_name );
			delete_term_meta( $term_id, $this->meta_name . '_feedback' );
			exit;
		}
		echo 'Contact Administrator';
		exit;
	}

	public function add_field() {
		$data = [
			'field_name'   => $this->field_name,
			'display_name' => $this->display_name,
			'helper_text'  => $this->helper_text,
			'options'      => $this->options,
		];

		switch ( $this->field_type ) {
			case 'text':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Text_Field( $data );
				$field->display_field();
				break;
			case 'textarea':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Textarea_Field( $data );
				$field->display_field();
				break;
			case 'dropdown':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Dropdown_Field( $data );
				$field->display_field();
				break;
			case 'color':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Color_Field( $data );
				$field->display_field();
				break;
			case 'email':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Email_Field( $data );
				$field->display_field();
				break;
			case 'url':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Url_Field( $data );
				$field->display_field();
				break;
			case 'checkbox':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Checkbox_Field( $data );
				$field->display_field();
				break;
			case 'multi-select':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Multi_Select( $data );
				$field->display_field();
				break;
		}
	}

	public function edit_field( $term ) {
		$term_id        = $term->term_id;
		$value          = get_term_meta( $term_id, $this->meta_name, true );
		$value_feedback = get_term_meta( $term_id, $this->meta_name . '_feedback', true );

		$data = [
			'term'               => $term,
			'value'              => $value,
			'field_name'         => $this->field_name,
			'meta_name'          => $this->meta_name,
			'value_feedback'     => $value_feedback ? $value_feedback : '',
			'taxonomy'           => $this->taxonomy,
			'display_name'       => $this->display_name,
			'helper_text'        => $this->helper_text,
			'options'            => $this->options,
			'image_preview_size' => $this->image_preview_size,
		];

		switch ( $this->field_type ) {
			case 'text':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Text_Field( $data );
				$field->edit_field();
				break;
			case 'textarea':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Textarea_Field( $data );
				$field->edit_field();
				break;
			case 'dropdown':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Dropdown_Field( $data );
				$field->edit_field();
				break;
			case 'color':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Color_Field( $data );
				$field->edit_field();
				break;
			case 'email':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Email_Field( $data );
				$field->edit_field();
				break;
			case 'url':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Url_Field( $data );
				$field->edit_field();
				break;
			case 'checkbox':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Checkbox_Field( $data );
				$field->edit_field();
				break;
			case 'multi-select':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Multi_Select( $data );
				$field->edit_field();
				break;
			case 'image':
				$field = new IDG\Base_Theme\Taxonomy\Tax_Image_Field( $data );
				$field->edit_field();
				break;
		}
	}

	public function validate( $value, $term_id ) {
		if ( ! $value ) {
			return;
		}

		if ( $this->field_type === 'textarea' ) {
			return sanitize_textarea_field( $value );
		}

		if ( $this->field_type === 'email' ) {
			return sanitize_email( $value );
		}

		if ( $this->field_type === 'url' ) {
			return esc_url_raw( $value );
		}

		if ( $this->field_type === 'image' ) {
			$image = new IDG\Base_Theme\Taxonomy\Tax_Image_Upload( $value, $this->meta_name, $term_id );
			$image->upload();
		}

		if ( $this->field_type === 'multi-select' ) {
			return $value;
		}

		return sanitize_text_field( $value );
	}

	public function save_field( $term_id ) {
		if ( ! isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ) ) ) {
			if ( ! isset( $_POST['_wpnonce_add-tag'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce_add-tag'] ) ) ) {
				return false;
			}
		}

		// Value is sanitized inside `validate` function
		if ( $this->field_type === 'image' ) {
			$value = isset( $_FILES[$this->field_name] ) ? $_FILES[$this->field_name] : NULL; // phpcs:ignore
			$this->validate( $value, $term_id );
			return;
		} else {
			$value = isset( $_POST[$this->field_name] ) ? $_POST[$this->field_name] : NULL; // phpcs:ignore
			$value = $this->validate( $value, $term_id );
		}

		$value = $this->validate( $value, $term_id );
		update_term_meta( $term_id, $this->meta_name, $value );
	}
}
