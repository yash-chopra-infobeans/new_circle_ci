<?php

namespace IDG\Publishing_Flow;

use IDG\Publishing_Flow\Statuses\Draft;
use IDG\Publishing_Flow\Statuses\Publish;
use IDG\Publishing_Flow\Statuses\Publish_Ready;
use IDG\Publishing_Flow\Statuses\Review_Ready;
use IDG\Publishing_Flow\Statuses\On_Hold;
use IDG\Publishing_Flow\Statuses\Updated;
use IDG\Publishing_Flow\Statuses\Trash;

/**
 * Allows for management of statuses to be used
 * in the Publishing Flow.
 */
class Statuses {
	/**
	 * Filter Hook that is used for checking over the created
	 * status list.
	 */
	const FILTER_STATUS_LIST = 'idg_publishing_flow_status_list';

	/**
	 * Filter Hook for applying revision statuses.
	 */
	const FILTER_REVISION_STATUSES = 'idg_publishing_flow_revision_statuses';

	/**
	 * Filter Hook for setting the arguments and config of a status
	 * when it is going to be registered.
	 */
	const FILTER_STATUS_ARGS = 'idg_publishing_flow_status_args';

	/**
	 * Initialise the class.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_all' ] );
		add_action( 'pre_get_posts', [ $this, 'revision_statuses' ] );
	}

	/**
	 * Get an instance of the class and if the called
	 * method exists, call it.
	 *
	 * @param string $name The name of the method being called.
	 * @param array  $arguments A list of arguments passed through.
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		/**
		 * If get_status_list is called statically, ensure
		 * we can call the method by booting a new instance
		 * of the class.
		 */
		if ( 'get_status_list' === $name ) {
			$instance = new self();
			return $instance->get_status_list();
		}
	}

	/**
	 * Retrieve a list of all that status that have
	 * or will be registered.
	 *
	 * @return array
	 */
	protected function get_status_list() : array {
		$default_status_list = [
			new Draft(),
			new Review_Ready(),
			new Publish_Ready(),
			new On_Hold(),
			new Publish(),
			new Updated(),
			new Trash(),
		];

		/**
		 * Filter Hook that is used for checking over the created
		 * status list.
		 *
		 * Classes added to the array are expected to be instantiated
		 * and extend the IDG\Publishing_Flow\Statuses\Status class.
		 *
		 * @param array $default_status_list The list of status classes that are provided by default.
		 */
		return apply_filters( self::FILTER_STATUS_LIST, $default_status_list );
	}

	/**
	 * Registered all statuses provided by get_status_list.
	 *
	 * @return void
	 */
	public function register_all() : void {
		$statuses = $this->get_status_list();

		foreach ( $statuses as $status ) {
			$this->register( $status->name, $status->label );
		}
	}

	/**
	 * Registers the given status.
	 *
	 * @param string $status The status name.
	 * @param string $label  The status label.
	 * @return void
	 */
	private function register( string $status, string $label ) : void {
		$registered_statuses = array_keys( get_post_stati() );

		// Check if the status is already registered.
		if ( in_array( $status, $registered_statuses ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$translated_label = _x( $label, 'Post status label.', 'idg-status-flow' );

		/**
		 * The default set of arguments that will be provided to the
		 * status registration.
		 *
		 * @param array $default_args The args to provide to register_post_status
		 */
		$status_args = apply_filters(
			self::FILTER_STATUS_ARGS,
			[
				'label'                     => $translated_label,
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			]
		);

		register_post_status( $status, $status_args );
	}

	/**
	 * Checks the revision status of the current query.
	 *
	 * @param \WP_Query $wp_query The passed query object.
	 * @return void
	 */
	public function revision_statuses( \WP_Query $wp_query ) : void {
		$query_vars = $wp_query->query_vars;

		if ( isset( $query_vars['post_type'] ) && 'revision' === $query_vars['post_type'] ) {
			$statuses          = $this->get_status_list();
			$revision_statuses = apply_filters(
				self::FILTER_REVISION_STATUSES,
				array_merge( [ $query_vars['post_status'] ], $statuses )
			);

			/**
			 * Ensure the accepted statuses are assigned to the query.
			 * Ignoring this as been through inspection.
			 */
			// phpcs:disable
			$wp_query->query_vars['post_status'] = $revision_statuses;
		}
	}
}
