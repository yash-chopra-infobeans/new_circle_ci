<?php

/**
 * Recursively map array
 *
 * @param array    $array Array to map over.
 * @param function $callback Function to use as callback on item.
 * @return void
 */
function array_map_recursive( &$array, $callback ) {
	$output = [];

	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			$output[ $key ] = array_map_recursive( $value, $callback );
		} else {
			$output[ $key ] = $callback( $value );
		}
	}

	return $output;
}

/**
 * Get an instance of `$bugsnagWordPress` global that has some predefined
 * configuration for handling stacktraces.
 *
 * BugSnag WordPress does not support the ability to remove frames.
 * (See `getStacktrace` documentation in full PHP package.)
 * This functionality is required to remove the file from the stacktrace,
 * as when a report is made using one of these utility functions, this
 * file becomes the calling point in the trace. This is not accurate from
 * the developer perspective so we clean it up before creating any reports.
 *
 * @return \Bugsnag_WordPress
 */
function bugsnag_with_clean_stacktrace() {
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	global $bugsnagWordpress;

	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	$bugsnagWordpress->setBeforeNotifyFunction(
		function( $report ) {
			foreach ( $report->stacktrace->frames as $key => $frame ) {
				if ( __FILE__ === $frame['file'] ) {
					unset( $report->stacktrace->frames[ $key ] );
				}
			}

			$report->stacktrace->frames = array_values( $report->stacktrace->frames );
		}
	);

	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	return $bugsnagWordpress;
}

/**
 * Helper function for checking if BugSnag is available to use.
 *
 * @return bool
 */
function idg_can_use_bugsnag() {
	return IDG\Configuration\Error_Reporting::can_use_bugsnag();
}

/**
 * Create a notification in BugSnag or fallback to
 * custom error reporting if BugSnag is not available
 * on the current install.
 *
 * @see BugSnag_WordPress::notifyError
 * @see IDG\Configuration\Error_Reporting::notifyError
 *
 * @param string $error_type The type of error to be notified about.
 * @param string $message The message to include in the error.
 * @param array  $data The data which should be saved against the error.
 * @param string $type The type of notification.
 * @return mixed
 */
function idg_notify_error( string $error_type, string $message, array $data = [], string $type = 'info' ) {
	if ( idg_can_use_bugsnag() ) {
		return bugsnag_with_clean_stacktrace()->notifyError( $error_type, $message, $data, $type );
	}

	return IDG\Configuration\Error_Reporting::notifyError( $error_type, $message, $data, $type );
}

/**
 * Add meta to a bugsnag error report, or fallback to
 * custom error reporting if BugSnag is not available
 * on the current install.
 *
 * @see BugSnag_WordPress::setMetaData
 * @see IDG\Configuration\Error_Reporting::setMetaData
 *
 * @param array $meta An array of meta data to output.
 * @return mixed
 */
function idg_set_error_report_meta( array $meta ) {
	if ( idg_can_use_bugsnag() ) {
		return bugsnag_with_clean_stacktrace()->setMetaData( $meta );
	}

	return IDG\Configuration\Error_Reporting::setMetaData( $meta );
}
