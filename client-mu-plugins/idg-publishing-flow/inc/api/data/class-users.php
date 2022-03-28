<?php

namespace IDG\Publishing_Flow\API\Data;

/**
 * Handles any User related methods.
 */
class Users {
	const HOOK_AFTER_AUTHOR_CREATE = 'idg_publishing_flow_after_author_create';

	/**
	 * Get an instance of the class.
	 */
	public static function instance() {
		return new self();
	}

	/**
	 * Creates any users that currently do not exist in
	 * the database by checking against the provided
	 * email field.
	 *
	 * @throws ErrorException When a user cannot be created.
	 * @param array $users List of users to be created.
	 * @throws ErrorException When user cannot be created or updated.
	 * @return array IDs of the users.
	 */
	public function create( array $users ) {
		$stored = [];

		foreach ( $users as $user ) {
			$existing_user = get_user_by( 'email', $user['email'] );

			$wp_user_data = [
				'user_login'   => $user['login'],
				'user_email'   => $user['email'],
				'display_name' => $user['display_name'],
				'first_name'   => $user['first_name'],
				'last_name'    => $user['last_name'],
			];

			if ( $existing_user ) {
				$additional   = [
					'ID' => $existing_user->ID,
				];
				$wp_user_data = array_merge( $wp_user_data, $additional );

				$user_id = wp_update_user( $wp_user_data );
			} else {
				$additional   = [
					'user_pass' => wp_generate_password( 32 ),
					'role'      => 'subscriber',
				];
				$wp_user_data = array_merge( $wp_user_data, $additional );

				$user_id = wp_insert_user( $wp_user_data );
			}

			if ( is_wp_error( $user_id ) ) {
				$display_name = $user['display_name'];
				throw new \ErrorException( "Could not create or update user ($display_name)" );
			}

			$this->update_meta( $user_id, $user['meta'] );

			do_action( self::HOOK_AFTER_AUTHOR_CREATE, intval( $user_id ), $user );

			$stored[] = $user_id;
		}

		return $stored;
	}

	/**
	 * Loop through the meta and insert as required.
	 *
	 * @param integer $user_id The user to insert meta against.
	 * @param array   $meta The list of meta to insert/update.
	 * @return void
	 */
	public function update_meta( $user_id, $meta ) : void {
		foreach ( $meta as $meta_key => $meta_value ) {
			update_user_meta( $user_id, $meta_key, $meta_value );
		}
	}
}
