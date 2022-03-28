<?php

namespace IDG\Base_Theme\Taxonomy;

class Tax_Image_Field extends Tax_Field_Template {
	public function __construct( $data ) {
		$this->data = $data;
	}

	public function display_field() {
		$this->pre_display_taxonomy_field();

		printf( '<input type="file" name="%s" id="%s">', esc_attr_e( $this->data['field_name'] ), esc_attr_e( $this->data['field_name'] ) );

		$this->post_display_taxonomy_field();
	}

	public function edit_field() {
		$this->pre_edit_taxonomy_field();
		?>
		<script type="text/javascript">
			jQuery(function($) {
				$('.delete_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>').click(function(){
					if( $( '#uploaded_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>' ).length > 0 && confirm( 'Are you sure you want to delete the image?' ) ) {
						var result = $.ajax({
							url: '/wp-admin/admin-ajax.php',
							type: 'GET',
							data: {
								action: '<?php echo esc_attr( $this->data['taxonomy'] . '_' . $this->data['meta_name'] . '_delete_image' ); ?>',
								term_id: '<?php echo intval( $this->data['term']->term_id ); ?>',
								wp_nonce: '<?php echo esc_attr( wp_create_nonce( $this->data['taxonomy'] . '_' . $this->data['meta_name'] . '_delete_image_nonce' ) ); ?>'
							},
							dataType: 'text'
						});

						result.success( function( data ) {
							$('#uploaded_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>').remove();
							$('.delete_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>').remove();
						});

						result.fail( function( jqXHR, textStatus ) {
							console.log( "Request failed: " + textStatus );
						});
					}
				});
			});
		</script>
		<input type="file" name="<?php esc_attr_e( $this->data['field_name'] ); ?>" id="<?php esc_attr_e( $this->data['field_name'] ); ?>">
		<?php
		if ( is_numeric( $this->data['value'] ) ) :
			$image_arr = wp_get_attachment_image_src( $this->data['value'], $this->data['image_preview_size'] );
			$image_url = $image_arr[0];
			?>
			<div id="uploaded_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>">
				<img class="tax-uploaded-thumbnail" src="<?php echo esc_url( $image_url ); ?>" />
			</div>
			<?php
		elseif ( ! empty( $this->data['value_feedback'] ) ) :
			?>
			<p class="tax-image-feedback">
				<?php echo wp_kses_data( $this->data['value_feedback'] ); ?>
			</p>
			<?php
			delete_term_meta( $this->data['term']->term_id, $this->data['meta_name'] . '_feedback' );
		endif;
		?>
		<?php if ( ! empty( $this->data['value'] ) ) : ?>
			<a href="javascript:void(0)" class="delete_image_<?php esc_attr_e( $this->data['meta_name'] ); ?>" style="color:#a00;font-size:13px;text-decoration:none;">Delete</a>
		<?php endif; ?>
		<?php
		$this->post_edit_taxonomy_field();
	}
}
