<?php
/**
 * Add or edit additional fields for taxonomy template.
 */
?>

<div class="form-field form-required term-type-wrap">
	<label for="type">
		<?php esc_html_e( 'Type', 'idg-publishing-flow' ); ?>
	</label>
	<select name="type" id="type">
		<?php foreach ( $this->allowed_types as $value => $name ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
