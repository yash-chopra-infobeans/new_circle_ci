<?php

if ( empty( $current_post_id ) ) {
	return;
}

wp_nonce_field( 'tag_selection_fields', 'tag_selection_fields_nonce' );

$args          = [
	'fields' => 'id=>name',
];
$selected_tags = wp_get_post_terms( $current_post_id, 'post_tag', $args );

?>
<label
	for="_idg_tag_selection_metabox"
	class="components-form-token-field__label"
>
	<?php esc_html_e( 'Select Tags', 'idg-golden-taxonomy' ); ?>
</label>
<select
	multiple
	id="_idg_tag_selection_metabox"
	name="_idg_post_tags[]"
	style="width: 100%"
>
	<?php foreach ( $selected_tags as $tag_id => $name ) : ?>
		<option value="<?php echo esc_attr( $tag_id ); ?>" selected="selected" >
			<?php echo esc_html( $name ); ?>
		</option>
	<?php endforeach; ?>
</select>
