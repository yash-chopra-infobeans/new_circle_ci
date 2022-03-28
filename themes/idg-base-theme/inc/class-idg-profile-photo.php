<?php
/**
 * Profile photo handler.
 *
 * @package IDG.
 */

if ( class_exists( 'IDG_Profile_Photo' ) ) {
	return new IDG_Profile_Photo();
}

/**
 * Class to add custom `profile-photo` to
 * profile edit screen.
 * 
 * @SuppressWarnings(PHPMD)
 */
class IDG_Profile_Photo {

	/**
	 * Class construct:
	 *
	 * Adds ajax action for deleting author photo.
	 * Removes Gravatar field.
	 * Adds Author Profile field to user edit pages.
	 * Uploads Image and Attaches meta.
	 * Allow form to accept files.
	 * Modifys WordPress `get_avatar()` function to use `idg_get_author_photo()`.
	 */
	public function __construct() {

		// Adds ajax action for deleting author photo.
		add_action( 'wp_ajax_delete_author_image', [ $this, 'photo_delete' ] );

		// Removes Gravatar field.
		add_action( 'admin_enqueue_scripts', [ $this, 'remove_gravatar_field' ] );

		// Adds Author Profile field to user edit pages.
		add_action( 'show_user_profile', [ $this, 'edit_user_profile' ] );
		add_action( 'edit_user_profile', [ $this, 'edit_user_profile' ] );

		// Uploads Image and Attaches meta.
		add_action( 'personal_options_update', [ $this, 'edit_user_profile_update' ] );
		add_action( 'edit_user_profile_update', [ $this, 'edit_user_profile_update' ] );

		// Allow form to accept files.
		add_action( 'user_edit_form_tag', [ $this, 'user_edit_form_tag' ] );

		// Modifys WordPress `get_avatar()` function to use `idg_get_author_photo()`.
		add_filter( 'get_avatar', [ $this, 'get_photo' ], 10, 5 );
	}

	/**
	 * Gets the author photo using `idg_get_author_photo`.
	 *
	 * @param string $photo HTML for the user's avatar.
	 * @param int    $user_id The user_id.
	 * @param int    $size Square avatar width and height in pixels to retrieve.
	 * @param string $default URL for the default image or a default type.
	 */
	public function get_photo( $photo = '', $user_id = 0, $size = 96, $default = '' ) { //phpcs:ignore -- $photo never used.
		return idg_get_author_photo( $user_id, $size, $default );
	}

	/**
	 * Adds `profile-photo` field to profile edit screen.
	 *
	 * @param object $profile_user The user object of the page currentlt being edited.
	 */
	public function edit_user_profile( $profile_user ) {
		?>
		<h3><?php echo esc_html( __( 'Author Photo', 'idg-base-theme' ) ); ?></h3>

		<table class="form-table">
			<tr>
				<th scope="row"><label for="profile-photo"><?php echo esc_html( __( 'Upload Photo', 'idg-base-theme' ) ); ?></label></th>
				<?php
				if ( get_user_meta( $profile_user->ID, 'profile-photo', true ) ) {
					printf( '<td class="profile-photo-image" id="profile-photo-image"><div class="author-image">%s</div></div>', wp_kses_post( idg_get_author_photo( $profile_user->ID ) ) );
				}
				?>
				<script type="text/javascript">
					jQuery(function($) {
						$('.delete_author_image').click(function(){
							if( confirm( 'Are you sure you want to delete the image?' ) ) {
								var result = $.ajax({
									url: '/wp-admin/admin-ajax.php',
									type: 'GET',
									data: {
										action: 'delete_author_image',
										user_id: '<?php echo intval( $profile_user->ID ); ?>',
										wp_nonce: '<?php echo esc_attr( wp_create_nonce( 'delete_author_image_nonce' ) ); ?>'
									},
									dataType: 'text'
								});

								result.success( function( data ) {
									$('.delete_author_image').remove();
									$('.profile-photo-image').remove();
								});

								result.fail( function( jqXHR, textStatus ) {
									console.log( "Request failed: " + textStatus );
								});
							}
						});
					});
				</script>
				<?php if ( ! empty( get_user_meta( $profile_user->ID, 'profile-photo', true ) ) ) : ?>
					<a href="javascript:void(0)" class="delete_author_image" style="color:#a00;font-size:13px;text-decoration:none;">Delete</a>
				<?php endif; ?>
				</td>
				<td>
				<?php
				if ( current_user_can( 'upload_files' ) ) {
					wp_nonce_field( 'profile_photo_nonce', '_profile_photo_nonce', false );
					?>
						<p class="profile-photo-description">
							<span class="description"><?php _e( 'Choose an image from your computer:' ); ?></span><br />
							<input type="file" name="profile-photo" id="profile-photo" class="standard-text" />
						</p>
					<?php
				} else {
					echo wp_kses_post( '<span class="description">' . __( 'You do not have media management permissions.', 'idg-base-theme' ) . '</span>' );
				}
				?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Assigns `profile-photo` to the correct user.
	 *
	 * @param int $media_id ID of the image in question.
	 * @param int $user_id ID of the user in question.
	 */
	private function assign_new_author_photo( $media_id, $user_id ) {
		$this->auto_photo_delete( $user_id );

		$meta_value = [];

		$meta_value['media_id'] = $media_id;
		$media_id               = wp_get_attachment_url( $media_id );

		$meta_value['full'] = $media_id;

		update_user_meta( $user_id, 'profile-photo', $meta_value );
	}

	/**
	 * Saves the `profile-photo` and uploads image.
	 *
	 * @param int $user_id ID of the user in question.
	 */
	public function edit_user_profile_update( $user_id ) {
		$profile_photo_nonce = filter_input( INPUT_POST, '_profile_photo_nonce', FILTER_SANITIZE_STRING );

		if ( ! isset( $profile_photo_nonce ) && ! wp_verify_nonce( $profile_photo_nonce ) ) {
			return false;
		}

		if ( ! empty( $_FILES['profile-photo']['name'] ) ) {

			if ( false !== strpos( esc_url_raw( $_FILES['profile-photo']['name'] ), '.php' ) ) {
				$this->photo_upload_error = __( 'For security reasons, the extension ".php" cannot be in your file name.', 'idg-base-theme' );
				add_action( 'user_profile_update_errors', [ $this, 'user_profile_update_errors' ] );
				return;
			}

			$this->user_id_being_edited = $user_id;
			$photo_id                   = media_handle_upload(
				'profile-photo',
				0,
				[
					'meta_input' => [
						'media_type' => 'author-image',
					],
				],
				[
					'mimes'                    => [
						'jpg|jpeg|jpe' => 'image/jpeg',
						'gif'          => 'image/gif',
						'png'          => 'image/png',
					],
					'test_form'                => false,
					'unique_filename_callback' => [ $this, 'unique_filename_callback' ],
				]
			);

			if ( is_wp_error( $photo_id ) ) {
				$this->photo_upload_error = '<strong>' . __( 'There was an error uploading the photo:', 'idg-base-theme' ) . '</strong> ' . esc_html( $photo_id->get_error_message() );
				add_action( 'user_profile_update_errors', [ $this, 'user_profile_update_errors' ] );
				return;
			}

			$this->assign_new_author_photo( $photo_id, $user_id );

		}
	}

	/**
	 * Changes the form tag on the profile edit screen to allow files.
	 */
	public function user_edit_form_tag() {
		echo 'enctype="multipart/form-data"';
	}

	/**
	 * Automatically deletes `profile-photo` when user is deleted.
	 *
	 * @param int $user_id ID of the user in question.
	 */
	public function auto_photo_delete( $user_id ) {
		$old_photo = get_user_meta( $user_id, 'profile-photo', true );

		if ( empty( $old_photo ) ) {
			return;
		}

		wp_delete_attachment( $old_photo['media_id'] );
		delete_user_meta( $user_id, 'profile-photo' );
	}

	/**
	 * Util function to delete `profile-photo` when ran via ajax action `delete_author_image`.
	 */
	public function photo_delete() {
		$wp_nonce   = filter_input( INPUT_GET, 'wp_nonce', FILTER_SANITIZE_STRING );
		$field_none = sanitize_text_field( $wp_nonce, 'delete_author_image_nonce' );

		if ( ! isset( $wp_nonce ) && ! wp_verify_nonce( $field_none ) ) {
			exit;
		}

		if ( ! isset( $_GET['user_id'] ) ) {
			echo 'Not Set or Empty';
			exit;
		}

		$user_id  = intval( $_GET['user_id'] );
		$image_id = get_user_meta( $user_id, 'profile-photo', true );

		if ( is_numeric( $image_id['media_id'] ) ) {
			wp_delete_attachment( $image_id['media_id'] );
			delete_user_meta( $user_id, 'profile-photo' );
			exit;
		}
		echo 'Contact Administrator';
		exit;
	}

	/**
	 * Util function to generate a unique filename.
	 *
	 * @param string $dir Directory name.
	 * @param string $name File name.
	 * @param string $ext File extension.
	 */
	public function unique_filename_callback( $dir, $name, $ext ) {
		$user      = get_user_by( 'id', (int) $this->user_id_being_edited );
		$base_name = sanitize_file_name( 'author_photo_' . $user->display_name . '_' . time() );
		$name      = $base_name;

		$number = 1;
		while ( file_exists( $dir . "/$name$ext" ) ) {
			$name = $base_name . '_' . $number;
			$number++;
		}

		return $name . $ext;
	}

	/**
	 * Enqueues script to remove gravatar field from edit profile page.
	 */
	public function remove_gravatar_field() {
		wp_register_script( 'remove-gravatar-field', false, [], 1, true );
		wp_enqueue_script( 'remove-gravatar-field' );
		wp_add_inline_script( 'remove-gravatar-field', 'jQuery(document).ready(function(){jQuery("tr.user-profile-picture").remove();});' );
	}

	/**
	 * Error handler.
	 *
	 * @param WP_Error $errors Error object.
	 */
	public function user_profile_update_errors( WP_Error $errors ) { // phpcs:ignore Squiz.Commenting.FunctionComment.InvalidTypeHint
		$errors->add( 'author_photo_error', $this->photo_upload_error );
	}

}

/**
 * Modifies default get_avatar() function.
 *
 * @param int    $user_id the user ID.
 * @param int    $size (Optional) Height and width of the avatar image file in pixels. Default value: 96.
 * @param string $default (Optional) Default image URL. Currently not used.
 *
 * @return string $photo Author photo in an image tag with height and width and class `author_photo`.
 *
 * All other standard get_avatar() options not available.
 * @SuppressWarnings(PHPMD)
 */
function idg_get_author_photo( $user_id, $size = 150, $default = '' ) { //phpcs:ignore -- $default never used.
	if ( empty( $user_id ) ) {
		return;
	}

	$author_photo = get_user_meta( $user_id, 'profile-photo', true );

	if ( empty( $author_photo['full'] ) ) {
		return;
	}

	if ( ! empty( $author_photo['media_id'] ) && ! wp_get_attachment_image_src( $author_photo['media_id'], [ $size, $size ] ) ) {
		return;
	}

	$author_photo_arr = wp_get_attachment_image_src( $author_photo['media_id'], [ $size, $size ] );
	$author_photo_url = $author_photo_arr[0];

	$size = $size;

	$alt = get_the_author_meta( 'display_name', $user_id );

	$photo = sprintf( '<img data-hero alt="%s" src="%s" class="author_photo" height="%s" width="%s" />', esc_attr( $alt ), esc_url( $author_photo_url ), esc_attr( $size ), esc_attr( $size ) );

	return $photo;
}
