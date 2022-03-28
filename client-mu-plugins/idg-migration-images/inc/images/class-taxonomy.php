<?php

namespace IDG\Migration\Images;

use WP_CLI;
use IDG\Migration\Images\CLI;
use IDG\Publishing_Flow\Data\Taxonomies;
use IDG\Publishing_Flow\Deploy\Taxonomy as Deploy_Taxonomy;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
class Taxonomy extends Base {
	private $cli = null;

	public function __construct( CLI $cli ) {
		$this->cli          = $cli;
		$this->amount       = $cli->amount;
		$this->offset       = $cli->offset;
		$this->include      = $cli->include;
		$this->taxonomy     = $cli->taxonomy;
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
			$terms = get_terms(
				[
					'taxonomy'   => $this->taxonomy,
					'hide_empty' => false,
					'number'     => 1,
					'offset'     => $i,
					'include'    => $this->include,
				]
			);

			$term = isset( $terms[0] ) ? $terms[0] : false;

			if ( ! $term ) {
				WP_CLI::line( WP_CLI::colorize( '%yNo Term matching get_terms query.%n' ) );
				continue;
			}

			$this->process_term( $term, $this->taxonomy );

			if ( $this->publish ) {
				$this->deploy_term( $term );
			}
		}
	}

	/**
	 * Migrate attachment passed.
	 *
	 * @param integer $attachment_id attachment id.
	 * @return void
	 */
	public function migrate_attachment( $term, string $meta_key, int $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment ) {
			// Delete term meta.
			delete_metadata( 'term', $term->term_id, $meta_key, false, true );
			WP_CLI::line( "{$meta_key} meta deleted." );
			return;
		}

		// Check the image needs to be migrated.
		if ( ! idg_can_image_be_migrated( $attachment->guid ) ) {
			WP_CLI::line( "Skipping Image: {$attachment->guid}" );
			return;
		}

		// Check the image is valid(doesn't 404), if it 404 do nothing.
		if ( ! idg_is_valid_image_url( $attachment->guid ) ) {
			// Delete attachment.
			wp_delete_attachment( $attachment_id, true );
			// Delete term meta.
			delete_metadata( 'term', $term->term_id, $meta_key, false, false );
			WP_CLI::line( "{$meta_key} meta deleted." );
			WP_CLI::line( "{$attachment_id} attachment deleted." );
			return;
		}

		$this->handle_image( $attachment->guid, $attachment->ID );
	}

	/**
	 * Checks if the meta_key field has a value and tries to migrate it if it does.
	 *
	 * @param \WP_Term $term term to migrate attachment for.
	 * @param string   $meta_key meta key containing attachment id.
	 * @return void
	 */
	public function handle_meta_attachment( $term, $meta_key ) {
		if ( ! $term || ! $meta_key ) {
			return;
		}

		$meta_value = get_term_meta( $term->term_id, $meta_key, true );

		if ( ! $meta_value ) {
			return;
		}

		$this->migrate_attachment( $term, $meta_key, (int) $meta_value );
	}

	/**
	 * Term processing here.
	 *
	 * @param \ WP_User $term The user object to be processed.
	 * @return void
	 */
	private function process_term( $term ) {
		switch ( $this->taxonomy ) {
			case 'blogs':
				// logo.
				$this->handle_meta_attachment( $term, 'logo' );
				break;
			case 'podcast_series':
				// logo.
				$this->handle_meta_attachment( $term, 'logo' );
				break;
			case 'sponsorships':
				// logo.
				$this->handle_meta_attachment( $term, 'logo' );
				// brand_image.
				$this->handle_meta_attachment( $term, 'brand_image' );
				break;
			default:
				break;
		}

		WP_CLI::success( "Term {$term->term_id}({$term->name}) content imported." );
	}

	/**
	 * Publish term to publication(s).
	 *
	 * @param \WP_Term $term term object.
	 * @return void
	 */
	private function deploy_term( $term ) {
		$term->meta = Taxonomies::get_meta_values( $term );

		foreach ( $this->publications as $publication ) {
			$deploy = new Deploy_Taxonomy( $term, $publication );

			$deploy->add_headers( [ 'X-IDG-PUBFLOW-SKIP-MISSING-IMAGES' => true ] );
			$deploy->create();

			if ( $deploy->failed() ) {
				$error_messages = $deploy->get_data()->errors ?: [ 'No error message provided.' ];
				WP_CLI::error_multi_line(
					array_merge(
						[
							"Term {$term->term_id} could not be deployed to {$publication} publication.",
							'-------',
						],
						json_decode( wp_json_encode( $error_messages ), true ),
					)
				);
			} else {
				WP_CLI::success( "#{$term->taxonomy} term {$term->name} ({$term->term_id}) deployed to {$publication} publication." );
			}
		}
	}
}
