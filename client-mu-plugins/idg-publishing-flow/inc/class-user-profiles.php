<?php

namespace IDG\Publishing_Flow;

/**
 * Handles the user profiles and configurations.
 */
class User_Profiles {
	/**
	 * Meta key of assigned business units to users.
	 */
	const USER_META_ASSIGNED = 'assigned_business_units';

	/**
	 * Initialise the class and attache the required hooks.
	 */
	public function __construct() {
		add_action( 'personal_options', [ $this, 'user_profile_business_unit_display' ] );
		add_action( 'personal_options_update', [ $this, 'user_profile_business_unit_store' ] );
		add_action( 'edit_user_profile_update', [ $this, 'user_profile_business_unit_store' ] );
	}

	/**
	 * Set the values required for displaying on a user profile
	 * depending on what capabilities the current user has.
	 *
	 * @param \WP_User $user The user object.
	 * @return void
	 */
	public function user_profile_business_unit_display( $user ) {
		$current_user_business_unit = get_user_meta( $user->ID, self::USER_META_ASSIGNED, true );

		$business_unit_id    = false;
		$business_unit_terms = false;
		$business_unit_name  = 'Unassigned';

		if ( isset( $current_user_business_unit[0] ) ) {
			$business_unit      = get_term( $current_user_business_unit[0], Sites::TAXONOMY, OBJECT );
			$business_unit_id   = $business_unit->term_id;
			$business_unit_name = $business_unit->name;
		}

		if ( current_user_can( 'edit_users' ) ) {
			$business_unit_terms = Sites::get_business_units();
		}

		require_once IDG_PUBLISHING_FLOW_DIR . '/inc/templates/user-business-unit.php';
	}

	/**
	 * Store the any updates that are changed or altered
	 * from a user profile.
	 *
	 * @param string $user_id The user ID.
	 * @return void
	 */
	public function user_profile_business_unit_store( $user_id ) {
		$business_unit_id = filter_input( INPUT_POST, 'business_unit', FILTER_SANITIZE_NUMBER_INT );

		if (
			wpcom_vip_term_exists( intval( $business_unit_id ), Sites::TAXONOMY )
			|| empty( $business_unit_id )
		) {
			update_user_meta( $user_id, self::USER_META_ASSIGNED, [ $business_unit_id ] );
		}

		Cache::clear_all_publications( [ $user_id ] );
	}

	/**
	 * Get the business units that have been attached to a user.
	 *
	 * @param mixed $user_id The user ID - retrieves the current user id if false.
	 * @return int
	 */
	public static function get_business_units( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$business_units = get_user_meta( $user_id, self::USER_META_ASSIGNED, true );

		return isset( $business_units[0] ) ? $business_units[0] : 0;
	}
}
