<?php

if ( empty( $current_post_id ) || empty( $categories ) || ! is_array( $categories ) ) {
	return;
}

wp_nonce_field( 'category_selection_fields', 'category_selection_fields_nonce' );

$meta_value          = get_post_meta( $current_post_id, '_idg_post_categories', true );
$selected_categories = ! empty( $meta_value ) ? $meta_value : [];

$args = [
	'exclude' => $selected_categories,
	'fields'  => 'ids',
];

// Categories that were assigned some other way than the metabox.
$already_present_categories = wp_get_post_categories( $current_post_id, $args );
$selected_categories        = array_merge( $selected_categories, $already_present_categories ); // Push them at the end.
?>
<label
	for="_idg_category_selection_metabox"
	class="components-form-token-field__label"
>
	<?php esc_html_e( 'Select Categories', 'idg-golden-taxonomy' ); ?>
</label>
<select
	multiple
	id="_idg_category_selection_metabox"
	name="_idg_post_categories[]"
	style="width: 100%"
>
	<?php
	// Render selected categories first in the order which they are in.
	if ( ! empty( $selected_categories ) && is_array( $selected_categories ) ) :
	?>
		<?php foreach ( $selected_categories as $category_id ) : ?>
			<?php
			$category = get_term_by( 'id', $category_id, 'category' );
			if ( ! empty( $category && is_a( $category, 'WP_Term' ) ) ) :
			?>
				<option value="<?php echo esc_attr( $category->term_id ); ?>" selected><?php echo esc_html( $category->name ); ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php foreach ( $categories as $category ) : ?>
		<?php if ( ! in_array( $category->term_id, $selected_categories, true ) && is_a( $category, 'WP_Term' ) ) : ?>
			<option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
