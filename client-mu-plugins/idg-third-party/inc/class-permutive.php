<?php

namespace IDG\Third_Party;

/**
 * Permutive integration.
 */
class Permutive {
	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( Loader::INLINE_HEAD_ACTION, [ $this, 'head' ] );
		add_action( 'amp_post_template_footer', [ $this, 'amp' ] );
	}

	/**
	 * Add google tag manager script to head
	 *
	 * @see https://developers.google.com/tag-manager/quickstart
	 * @return void
	 */
	public function head() {
		$api_key = Settings::get( 'permutive' )['account']['api_key'] ?? false;

		if ( ! $api_key ) {
			return;
		}

		?>

		// Permutive Stub
		!function(n,e,i){if(!n){n=n||{},window.permutive=n,n.q=[],n.config={}||{},n.config.apiKey=e,n.config.environment=n.config.environment||"production";for(var o=["addon","identify","track","trigger","query","segment","segments","ready","on","once","user","consent"],r=0;r<o.length;r++){var t=o[r];n[t]=function(e){return function(){var i=Array.prototype.slice.call(arguments,0);n.q.push({functionName:e,arguments:i})}}(t)}}}(
			window.permutive,
			'<?php echo esc_html( $api_key ); ?>'
		);
		window.googletag=window.googletag||{},window.googletag.cmd=window.googletag.cmd||[],window.googletag.cmd.push(function(){if(0===window.googletag.pubads().getTargeting("permutive").length){var g=window.localStorage.getItem("_pdfps");window.googletag.pubads().setTargeting("permutive",g?JSON.parse(g):[])}});

		<?php
	}

	public function get_permutive_amp_data( $api_key ) {
		if ( empty( $api_key ) ) {
			return [];
		}

		$base_data_layer = Base_Data_Layer::$data;

		$data = [
			'vars'           => [
				'namespace' => 'idg',
				'key'       => $api_key,
			],
			'extraUrlParams' => [
				'properties.type'                         => $base_data_layer['page_type'] ?: '',
				'properties.keywords!list'                => '',
				'properties.language'                     => 'en',
				'properties.tags!list'                    => $base_data_layer['tags'] ?: '',
				'properties.audience'                     => $base_data_layer['audience'] ?: '',
				'properties.description'                  => $base_data_layer['description'] ?: '',
				'properties.article.authors!list'         => $base_data_layer['author'] ?: '',
				'properties.article.description'          => $base_data_layer['description'] ?: '',
				'properties.article.id'                   => $base_data_layer['articleId'] ?: '',
				// 'properties.article.isInsiderContent'     => false, // Post-mvp, CIO?
				'properties.article.modifiedAt'           => $base_data_layer['dateUpdate'] ?: '',
				'properties.article.publishedAt'          => $base_data_layer['datePublished'] ?: '',
				'properties.article.source'               => $base_data_layer['source'] ?: '',
				'properties.article.title'                => $base_data_layer['articleTitle'] ?: '',
				'properties.article.type'                 => $base_data_layer['articleType'] ?: '',
				// 'properties.article.purchaseIntent'       => '', // Post-mvp, CIO?
				// 'properties.ads.adblocker'                => false,
				'properties.ads.enabled'                  => $base_data_layer['adBlockerEnabled'] ?: '',
				'properties.gTax.primaryIds!list'         => $base_data_layer['gtaxPrimaryIdsList'] ?: '',
				'properties.gTax.secondaryIds!list'       => $base_data_layer['gtaxIdList'] ?: '',
				'properties.tax.primaryCategories!list'   => $base_data_layer['primaryAncestorCategoryListSlugs'] ?: '',
				'properties.tax.secondaryCategories!list' => $base_data_layer['categoriesSlugs'] ?: '',
			],
		];

		return $data;
	}

	/**
	 * Load Permutive in amp
	 *
	 * @return void
	 */
	public function amp() {
		$api_key      = Settings::get( 'permutive' )['account']['api_key'] ?? false;
		$workspace_id = Settings::get( 'permutive' )['account']['workspace_id'] ?? false;

		if ( ! $api_key || ! $workspace_id ) {
			return;
		}

		?>
		<!-- amp-iframe once on the page to initialize Permutive -->
		<amp-iframe width="1" height="1" layout="fixed" sandbox="allow-scripts allow-same-origin" src="https://idg.amp.permutive.com/amp-iframe.html?project=<?php echo esc_attr( $workspace_id ); ?>&amp;key=<?php echo esc_attr( $api_key ); ?>"></amp-iframe>

		<!-- amp-analytics page-level tracking for Permutive -->
		<amp-analytics data-block-on-consent config="https://amp.permutive.com/amp-analytics-v2.json">
			<script type="application/json"><?php echo wp_json_encode( $this->get_permutive_amp_data( $api_key ) ); ?></script>
		</amp-analytics>

		<?php
	}
}
