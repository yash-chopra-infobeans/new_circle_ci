<?php

namespace IDG\Third_Party;

use IDG\Base_Theme\Templates;
use function IDG\Base_Theme\Utils\is_amp;

/**
 * Outbrain integration.
 */
class Outbrain {
	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( 'idg_before_footer', [ $this, 'render_smartfeed' ] );
	}

	/**
	 * Add the outbrain div.
	 *
	 * @return void
	 */
	public function render_smartfeed() {
		if ( ! Templates\article() ) {
			return;
		}

		$config = Settings::get( 'outbrain' )['config'];

		if ( is_amp() ) {
			printf(
				'<amp-embed width="100" height="100" type="outbrain" layout="responsive" data-widgetIds="%s" data-block-on-consent></amp-embed>',
				esc_attr( $config['amp_widget_ids'] ?? '' )
			);

			return;
		}

		printf(
			'<div class="OUTBRAIN" data-src="%s" data-widget-id="%s"></div>',
			esc_attr( $config['src'] ?? '' ),
			esc_attr( $config['widget_id'] ?? '' )
		);
	}
}
