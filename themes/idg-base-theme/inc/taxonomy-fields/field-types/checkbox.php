<?php

namespace IDG\Base_Theme\Taxonomy;

class Tax_Checkbox_Field extends Tax_Field_Template {
	public function __construct( $data ) {
		$this->data = $data;
	}

	public function display_field() {
		$this->pre_display_taxonomy_field();

		printf(
			'
			<input
				type="checkbox"
				name="%s"
				id="%s"
				class="checkbox"
				style="float: left; margin-top: 5px;"
			/>
		',
			esc_attr( $this->data['field_name'] ),
			esc_attr( $this->data['field_name'] )
		);

		$this->post_display_taxonomy_field();
	}

	public function edit_field() {
		$this->pre_edit_taxonomy_field();

		printf(
			'
			<input
				type="checkbox"
				name="%s"
				id="%s"
				class="checkbox"
				style="float: left; margin-top: 5px;"
				%s
			/>
		',
			esc_attr( $this->data['field_name'] ),
			esc_attr( $this->data['field_name'] ),
			$this->data['value'] ? 'checked' : ''
		);

		$this->post_edit_taxonomy_field();
	}
}
