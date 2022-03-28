<?php

if ( ! function_exists( 'idg_allow_additional_pubflow_meta' ) ) {
	/**
	 * Allows for additional meta data that isn't core WordPress to be sent
	 * using the Publication Flow plugin.
	 *
	 * @return array
	 */
	function idg_allow_additional_pubflow_meta( array $meta_keys ) {
		return array_merge(
			$meta_keys,
			[
				'job_title',
				'twitter',
				'linkedin',
				'facebook',
				'social_email',
				'hide_.*_on_articles', // Regex, no need to be specific.
			]
		);
	}
}

add_filter( 'idg_publishing_flow_allowed_author_meta', 'idg_allow_additional_pubflow_meta' );

if ( ! function_exists( 'idg_additional_profile_fields' ) ) {
	/**
	 * Adds additional user fields to user profile edit and new user screens.
	 *
	 * @param object $user Current user object.
	 */
	function idg_additional_profile_fields( $user ) { ?>
		<h3><?php esc_html_e( 'Author Details', 'idg-base-theme' ); ?></h3>

		<table class="form-table">
		<tr>
			<th><label for="job_title"><?php esc_html_e( 'Job Title', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="text"
					name="job-title"
					id="job_title"
					value="<?php echo esc_attr( get_the_author_meta( 'job_title', $user->ID ) ); ?>"
					class="regular-text"
				/>
				<br />
				<span class="description"><?php esc_html_e( 'Please enter your Job Title', 'idg-base-theme' ); ?></span>
			</td>
		</tr>
		</table>
		<?php
	}
}

add_action( 'show_user_profile', 'idg_additional_profile_fields' );
add_action( 'edit_user_profile', 'idg_additional_profile_fields' );

if ( ! function_exists( 'idg_save_additional_profile_fields' ) ) {
	/**
	 * Saves additional profile fields to user profile edit and new user screens.
	 *
	 * @param int $user_id Current users ID.
	 */
	function idg_save_additional_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( ! isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ) ) ) {
			return false;
		}

		// Additional meta fields.
		if ( isset( $_POST['job-title'] ) ) {
			$job_title = sanitize_text_field( $_POST['job-title'] );
			update_user_meta( $user_id, 'job_title', $job_title );
		}
	}
}

add_action( 'personal_options_update', 'idg_save_additional_profile_fields' );
add_action( 'edit_user_profile_update', 'idg_save_additional_profile_fields' );

if ( ! function_exists( 'idg_social_profile_fields' ) ) {
	/**
	 * Adds social link fields to user profile edit and new user screens.
	 *
	 * @param object $user Current user object.
	 */
	function idg_social_profile_fields( $user ) {
		?>
		<h3><?php esc_html_e( 'Social links', 'idg-base-theme' ); ?></h3>

		<table class="form-table">
		<tr>
			<th><label for="author"><?php esc_html_e( 'Author', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="checkbox"
					name="hide_author_on_articles"
					id="hide_author_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_author_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_author_on_articles"><?php esc_html_e( 'Hide author icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php esc_html_e( 'Twitter', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="url"
					name="twitter"
					id="twitter"
					value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>"
					class="regular-text code"
				/>
				<br />
				<span class="description"><?php esc_html_e( 'Please enter your Twitter URL', 'idg-base-theme' ); ?></span>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<input
					type="checkbox"
					name="hide_twitter_on_articles"
					id="hide_twitter_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_twitter_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_twitter_on_articles"><?php esc_html_e( 'Hide Twitter icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="linkedin"><?php esc_html_e( 'LinkedIn', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="url"
					name="linkedin"
					id="linkedin"
					value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>"
					class="regular-text code"
				/>
				<br />
				<span class="description"><?php esc_html_e( 'Please enter your LinkedIn URL', 'idg-base-theme' ); ?></span>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<input
					type="checkbox"
					name="hide_linkedin_on_articles"
					id="hide_linkedin_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_linkedin_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_linkedin_on_articles"><?php esc_html_e( 'Hide LinkedIn icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="social_email"><?php esc_html_e( 'Email', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="url"
					name="social_email"
					id="social_email"
					value="<?php echo esc_attr( get_the_author_meta( 'social_email', $user->ID ) ); ?>"
					class="regular-text code"
				/>
				<br />
				<span class="description"><?php esc_html_e( 'Please enter your Email Address', 'idg-base-theme' ); ?></span>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<input
					type="checkbox"
					name="hide_social_email_on_articles"
					id="hide_social_email_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_social_email_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_social_email_on_articles"><?php esc_html_e( 'Hide Email icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		<tr>
		<th><label for="rss"><?php esc_html_e( 'RSS', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="checkbox"
					name="hide_rss_on_articles"
					id="hide_rss_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_rss_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_rss_on_articles"><?php esc_html_e( 'Hide RSS icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="facebook"><?php esc_html_e( 'Facebook', 'idg-base-theme' ); ?></label></th>
			<td>
				<input
					type="url"
					name="facebook"
					id="facebook"
					value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>"
					class="regular-text code"
				/>
				<br />
				<span class="description"><?php esc_html_e( 'Please enter your Facebook URL', 'idg-base-theme' ); ?></span>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<input
					type="checkbox"
					name="hide_facebook_on_articles"
					id="hide_facebook_on_articles"
					class="checkbox"
					style="float: left;"
					<?php
					if ( get_the_author_meta( 'hide_facebook_on_articles', $user->ID ) ) {
						echo 'checked';
					}
					?>
				/>
				<label label for="hide_facebook_on_articles"><?php esc_html_e( 'Hide Facebook icon on articles', 'idg-base-theme' ); ?></label>
			</td>
		</tr>
		</table>
		<?php
	}
}

add_action( 'show_user_profile', 'idg_social_profile_fields' );
add_action( 'edit_user_profile', 'idg_social_profile_fields' );

if ( ! function_exists( 'idg_save_social_profile_fields' ) ) {
	/**
	 * Saves social link fields to user profile edit and new user screens.
	 *
	 * @param int $user_id Current users ID.
	 */
	function idg_save_social_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( ! isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ) ) ) {
			return false;
		}

		// Social meta fields.
		if ( isset( $_POST['twitter'] ) ) {
			$twitter_url = esc_url_raw( $_POST['twitter'] );
			update_user_meta( $user_id, 'twitter', $twitter_url );
		}

		if ( isset( $_POST['linkedin'] ) ) {
			$linkedin_url = esc_url_raw( $_POST['linkedin'] );
			update_user_meta( $user_id, 'linkedin', $linkedin_url );
		}

		if ( isset( $_POST['social_email'] ) ) {
			$social_email = sanitize_email( $_POST['social_email'] );
			update_user_meta( $user_id, 'social_email', $social_email );
		}

		if ( isset( $_POST['facebook'] ) ) {
			$facebook_url = esc_url_raw( $_POST['facebook'] );
			update_user_meta( $user_id, 'facebook', $facebook_url );
		}

		// Social meta fields display.
		update_user_meta( $user_id, 'hide_author_on_articles', isset( $_POST['hide_author_on_articles'] ) ? true : false );
		update_user_meta( $user_id, 'hide_twitter_on_articles', isset( $_POST['hide_twitter_on_articles'] ) ? true : false );
		update_user_meta( $user_id, 'hide_linkedin_on_articles', isset( $_POST['hide_linkedin_on_articles'] ) ? true : false );
		update_user_meta( $user_id, 'hide_social_email_on_articles', isset( $_POST['hide_social_email_on_articles'] ) ? true : false );
		update_user_meta( $user_id, 'hide_rss_on_articles', isset( $_POST['hide_rss_on_articles'] ) ? true : false );
		update_user_meta( $user_id, 'hide_facebook_on_articles', isset( $_POST['hide_facebook_on_articles'] ) ? true : false );
	}
}

add_action( 'personal_options_update', 'idg_save_social_profile_fields' );
add_action( 'edit_user_profile_update', 'idg_save_social_profile_fields' );

if ( ! function_exists( 'idg_bio_profile_field' ) ) {
	/**
	 * Changes Biographical Info field to use wp_editor() for Rich Text.
	 *
	 * @param object $user Current user object.
	 */
	function idg_bio_profile_field( $user ) {
		if ( ! isset( $_SERVER['PHP_SELF'] ) ) {
			return;
		}

		$page = basename( esc_url_raw( $_SERVER['PHP_SELF'] ) );

		if ( 'profile.php' === $page || 'user-edit.php' === $page && function_exists( 'wp_tiny_mce' ) ) {
			echo "<script>jQuery(document).ready(function($){ $('#description').remove();});</script>";
			$settings    = [
				'tinymce'       => [
					'toolbar1' => 'bold,italic,bullist,numlist,link,unlink',
				],
				'wpautop'       => true,
				'media_buttons' => false,
				'quicktags'     => false,
			];
			$description = get_user_meta( $user->ID, 'description', true );
			wp_editor( $description, 'description', $settings );
		}
	}
}
add_action( 'admin_head', 'idg_bio_profile_field' );
remove_filter( 'pre_user_description', 'wp_filter_kses' );
add_filter( 'pre_user_description', 'wp_filter_post_kses' );
