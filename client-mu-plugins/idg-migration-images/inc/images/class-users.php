<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Migration\Images\CLI;
use IDG\Publishing_Flow\Data\Authors as Authors_Data;
use IDG\Publishing_Flow\Deploy\Author as Deploy_Author;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
class Users extends Base {
	private $cli = null;

	public function __construct( CLI $cli ) {
		$this->cli          = $cli;
		$this->amount       = $cli->amount;
		$this->offset       = $cli->offset;
		$this->include      = $cli->include;
		$this->publish      = $cli->publish;
		$this->publications = $cli->publications;
	}

	public static function instance( CLI $cli ) {
		return new self( $cli );
	}

	/**
	 * Start the migration run by looping through the
	 * the amount and offset counters. Each entry will
	 * be processed and handled on it's own.
	 *
	 * @return void
	 */
	public function migrate() {
		$end_num = $this->offset + ( $this->amount - 1 );

		for ( $i = $this->offset; $i <= $end_num; $i++ ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts -- We do not want to use cached results for this process and require current data at time of request.
			$users = get_users(
				[
					'number'  => 1,
					'offset'  => $i,
					'include' => $this->include,
				]
			);

			$user = isset( $users[0] ) ? $users[0] : false;

			if ( ! $user ) {
				WP_CLI::line( WP_CLI::colorize( '%yNo User matching get_users query.%n' ) );
				continue;
			}

			$this->process_user( $user );

			if ( $this->publish ) {
				$this->deploy_user( $user );
			}
		}
	}

	/**
	 * Migrate user profile photo.
	 *
	 * @param \WP_User $user User object.
	 * @return void
	 */
	public function migrate_user_profile_photo( \WP_User $user ) {
		$profile_photo = get_user_meta( $user->ID, 'profile-photo', true );

		// If no media ID do nothing.
		if ( ! isset( $profile_photo['media_id'] ) ) {
			return;
		}

		$attachment = get_post( (int) $profile_photo['media_id'] );

		// If no attachmnent found do nothing.
		if ( ! $attachment ) {
			return;
		}

		// Check the image needs to be migrated, if it's already been migrated do nothing.
		if ( ! idg_can_image_be_migrated( $attachment->guid ) ) {
			WP_CLI::line( "Skipping Image: {$attachment->guid}" );
			return;
		}

		// Check the image is valid(doesn't 404), if it 404 do nothing.
		if ( ! idg_is_valid_image_url( $attachment->guid ) ) {
			WP_CLI::line( "Skipping Image: {$attachment->guid}" );
			return;
		}

		$attachment_id = $this->handle_image( $attachment->guid, $attachment->ID );
		$meta_value    = [
			'media_id' => $attachment_id,
			'full'     => wp_get_attachment_url( $attachment_id ),
		];

		update_user_meta( $user->ID, 'profile-photo', $meta_value );
	}

	/**
	 * User processing here.
	 *
	 * @param \ WP_User $user The user object to be processed.
	 * @return void
	 */
	private function process_user( $user ) {
		// Migrate user profile photo.
		$this->migrate_user_profile_photo( $user );

		WP_CLI::success( "User {$user->ID} content imported." );
	}

	/**
	 * Publish user to publication(s).
	 *
	 * @param \WP_User $user user object.
	 * @return void
	 */
	private function deploy_user( $user ) {
		foreach ( $this->publications as $publication ) {
			$author = Authors_Data::instance()->format( $user->ID );

			$deploy = new Deploy_Author( $author[0], intval( $publication ) );
			$deploy->create_or_update();

			if ( $deploy->failed() ) {
				$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
				WP_CLI::error_multi_line(
					array_merge(
						[
							"User {$user->ID} could not be deployed to {$publication} publication.",
							'-------',
						],
						json_decode( wp_json_encode( $error_messages ), true ),
					)
				);
			} else {
				WP_CLI::success( "User {$user->ID} deployed to {$publication} publication." );
			}
		}
	}
}
