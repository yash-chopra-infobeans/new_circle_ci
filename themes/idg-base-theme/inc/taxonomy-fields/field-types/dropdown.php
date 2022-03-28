<?php

namespace IDG\Base_Theme\Taxonomy;

class Tax_Dropdown_Field extends Tax_Field_Template {
	public function __construct( $data ) {
		$this->data = $data;
	}

	public function display_field() {
		if ( empty ( $this->data['options'] ) ) {
			return;
		}
		$this->pre_display_taxonomy_field();

		printf('
			<select
				name="%s"
				id="%s">
				<option value="">
					%s
				</option>
		', esc_attr( $this->data['field_name'] ), esc_attr( $this->data['field_name'] ), esc_html_x( 'Select', 'dropdown select', 'idg-base-theme' ) );

		foreach ( $this->data['options'] as $option ) {
			printf('<option value="%s">%s</option>', esc_attr( $option->val ),  esc_html( $option->name ) );
		}

		printf('</select>');

		$this->post_display_taxonomy_field();
	}

	public function edit_field() {
		if ( empty ( $this->data['options'] ) ) {
			return;
		}
		$this->pre_edit_taxonomy_field();

		printf('
			<select
				name="%s"
				id="%s">
				<option value="">
					%s
				</option>
		', esc_attr( $this->data['field_name'] ), esc_attr( $this->data['field_name'] ), esc_html_x( 'Select', 'dropdown select', 'idg-base-theme' ) );

		foreach ( $this->data['options'] as $option ) {
			$option_val = is_int( $option->val ) ? strval( $option->val ) : $option->val;
			printf('<option value="%s" %s>%s</option>', esc_attr( $option_val ), $this->data['value'] === $option_val ? 'selected' : '', esc_html( $option->name ) );
		}

		printf('</select>');

		$this->post_edit_taxonomy_field();
	}
}
