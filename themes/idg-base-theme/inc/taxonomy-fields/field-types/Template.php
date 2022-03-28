<?php

namespace IDG\Base_Theme\Taxonomy;

class Tax_Field_Template {
	public function pre_display_taxonomy_field(){
		?>
		<div class="form-field">
			<label for="<?php esc_attr_e( $this->data['field_name'] ); ?>">
				<?php echo esc_html( $this->data['display_name'] ); ?>
			</label>
	<?php
	}

	public function post_display_taxonomy_field(){
		?>
			<p>
				<?php echo esc_html( $this->data['helper_text'] ); ?>
			</p>
		</div>
	<?php
	}

	public function pre_edit_taxonomy_field(){
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr class="form-field term-slug-wrap">
					<th scope="row">
						<label for="<?php esc_attr_e( $this->data['field_name'] ); ?>">
							<?php echo esc_html( $this->data['display_name'] ); ?>
						</label>
					</th>
					<td>
	<?php
	}

	public function post_edit_taxonomy_field(){
		?>
						<p class="description">
							<?php echo esc_html( $this->data['helper_text'] ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}
