<?php
// Most method names need to be used interchangably with their Bug Snag counterpart, pascal case required.
// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace IDG\Configuration;

/**
 * Helper functionality for Error Reporting
 * between native methods and using Bug Snag.
 */
class Error_Reporting {

	const CAN_USE_BUGSNAG_HOOK = 'idg_error_report_can_use_bugsnag';
	const LOG_DATA_OUTPUT_HOOK = 'idg_error_report_log_data_output';

	/**
	 * The method that is called when used statically.
	 *
	 * @var string
	 */
	private $called = null;

	/**
	 * The arguments that are used when calling a method
	 * statically.
	 *
	 * @var array
	 */
	private $called_args = [];

	/**
	 * Build the class and assign the called items.
	 *
	 * @param string $called The called method.
	 * @param array  $called_args The method arguments being called.
	 */
	public function __construct( string $called = '', array $called_args = [] ) {
		$this->called      = $called;
		$this->called_args = $called_args;
	}

	/**
	 * Call any class methods that are requested statically.
	 *
	 * @param string $name Name of the called method.
	 * @param array  $args List of arguments/params sent with the call.
	 * @return mixed
	 */
	public static function __callStatic( $name, $args ) {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		global $bugsnagWordpress;

		$instance = new self( $name, $args );

		if ( self::can_use_bugsnag() ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			return call_user_func_array( [ $bugsnagWordpress, $name ], $args );
		}

		if ( ! defined( 'IDG_ENABLE_FALLBACK_LOGGING' ) ) {
			define( 'IDG_ENABLE_FALLBACK_LOGGING', true );
		}

		if ( method_exists( $instance, $name ) ) {
			return call_user_func_array( [ $instance, $name ], $args );
		}

		return $instance;
	}

	/**
	 * Checks whether BugSnag is active on the current
	 * setup.
	 *
	 * @return boolean
	 */
	public static function can_use_bugsnag() : bool {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		global $bugsnagWordpress;

		$api_key = get_site_option( 'bugsnag_api_key' );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$can_use = ! ( ! $api_key || ! $bugsnagWordpress );

		/**
		 * Allows to define whether bugsnag can be used
		 * at that given time, so it can be disabled for specific events.
		 * Returning false will cause local debug logs to be used.
		 *
		 * @param bool $can_use Whether bugsnag can be used.
		 */
		return apply_filters( static::CAN_USE_BUGSNAG_HOOK, $can_use );
	}

	/**
	 * The local variant of BugSnags notifyError() method.
	 * Saves the error report to the local error log if that
	 * is active.
	 *
	 * @see BugSnag::notifyError
	 * @throws Error Throws error when a valid type is assigned.
	 * @param string $error_type The type of error to be notified about.
	 * @param string $message The message to include in the error.
	 * @param array  $data The data which should be saved against the error.
	 * @param string $type The type of notification.
	 * @return void
	 */
	private function notifyError( string $error_type, string $message, array $data, string $type = 'info' ) {
		$valid_types = [
			'error',
			'warning',
			'info',
		];

		if ( ! in_array( $type, $valid_types, true ) ) {
			throw new Error( "$type is not a valid severity. Please use error, warning or info" );
		}

		$this->write_to_log( $type, $data, $message );
	}

	/**
	 * The local variant of BugSnags setMetaData() method.
	 * When setting the meta data, if using the local variant it is
	 * sent straight to the debug log.
	 *
	 * @see BugSnag::setMetaData
	 * @param array $meta An array of meta data to output.
	 * @return void
	 */
	private function setMetaData( array $meta ) {
		$this->write_to_log( 'info', $meta, 'Meta Data' );
	}

	/**
	 * Writes any sent data to the error log on when working
	 * on an environment that is defined as `local`.
	 *
	 * @param string $type The type of data being sent.
	 * @param array  $log_data The data which should be output to the log file.
	 * @param string $message Accompanying message for the data log.
	 * @return void
	 */
	private function write_to_log( string $type, array $log_data, string $message = '' ) {
		if ( ! defined( 'IDG_ENABLE_FALLBACK_LOGGING' ) || false === IDG_ENABLE_FALLBACK_LOGGING ) {
			return;
		}

		if ( ! defined( 'VIP_GO_APP_ENVIRONMENT' ) || 'local' !== VIP_GO_APP_ENVIRONMENT ) {
			return;
		}

		/**
		 * Allows for adjustment of data that is to be output
		 * to the debuglog.
		 *
		 * @param array $output The data being sent to the debug log.
		 * @param string $called The called method.
		 * @param array $called_args The arguments sent with the method call.
		 */
		$data_output = apply_filters(
			static::LOG_DATA_OUTPUT_HOOK,
			[
				'type' => $type,
				'data' => $log_data,
			],
			$this->called,
			$this->called_args
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$data_output = print_r( $data_output, true ); // Return output rather than print.

		// Checking for local environment prior to call. Ignore the following for this singular instance.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		error_log( "Notification: $message => $data_output" );
	}
}
