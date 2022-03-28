<?php
/**
 * Class GTM 
 *
 * @package idg-third-party plugin
 */

namespace IDG\Third_Party;

/**
 * Google Tag Manager integration.
 */
class GTM {
	/**
	 * Add actions
	 */
	public function __construct() {
		add_action( Loader::INLINE_HEAD_ACTION, [ $this, 'head' ] );
		add_action( 'wp_footer', [ $this, 'footer' ] );
		add_action( 'amp_post_template_head', [ $this, 'amp' ] );
	}

	/**
	 * Add google tag manager script to head
	 *
	 * @see https://developers.google.com/tag-manager/quickstart
	 * @return void
	 */
	public function head() {
		$id = Settings::get( 'gtm' )['account']['id'];

		if ( ! $id ) {
			return;
		}

		?>
		// Google Tag Manager
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_html( $id ); ?>');
		<?php
	}

	/**
	 * Add google tag manager noscript to footer
	 *
	 * @see https://developers.google.com/tag-manager/quickstart
	 * @return void
	 */
	public function footer() {
		$id = Settings::get( 'gtm' )['account']['id'];

		if ( ! $id ) {
			return;
		}

		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript>
			<iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_html( $id ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
		</noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}

	/**
	 * Load GTM in amp
	 *
	 * @return void
	 */
	public function amp() {
		$id        = Settings::get( 'gtm' )['account']['amp_id'];
		$ua_number = Settings::get( 'gtm' )['account']['ua_number'];
		$dataLayer = Base_Data_Layer::$data;

		if ( ! empty( $id ) ) {
			?>

		<amp-analytics config="https://www.googletagmanager.com/amp.json?id=<?php echo esc_attr( $id ); ?>&gtm.url=SOURCE_URL" data-credentials="include">
			<script type="application/json">
				{
					"vars": <?php echo wp_json_encode( $dataLayer ); ?>
				}
			</script>
		</amp-analytics>

			<?php 
		}
								
		if ( ! empty( $ua_number ) ) {
			?>

		<amp-analytics type="googleanalytics" id="analytics2">
			<script type="application/json">			
				{
					"requests": {
					"pageviewWithCsCm": "${pageview}&cs=${cs}&cm=${cm}"
					},
					"vars": {
					"account": "<?php echo esc_attr( $ua_number ); ?>"
					},
					"extraUrlParams": {
					"cd1": <?php echo esc_attr( $dataLayer[ adBlockerEnabled ] === true ? 'true' : 'false' ); ?>,
					"cd9": "<?php echo esc_attr( $dataLayer[ environment ] ); ?>",
					"cd10": "<?php echo esc_attr( strtolower( $dataLayer[ property ] ) ); ?>",
					"cd11": "US",
					"cd12": "<?php echo esc_attr( $dataLayer[ audience ] ); ?>",
					"cd15": "AMP",
					"cd16": "<?php echo esc_attr( $dataLayer[ contentStrategy ] ); ?>",
					"cd17": "<?php echo esc_attr( $dataLayer[ primaryCategory ] ); ?>",
					"cd18": "<?php echo esc_attr( $dataLayer[ goldenTaxonomyIdPrimary ] ); ?>",
					"cd19": "<?php echo esc_attr( $dataLayer[ categories ] ); ?>",
					"cd20": "<?php echo esc_attr( $dataLayer[ gtaxIdList ] ); ?>",
					"cd21": "<?php echo esc_attr( $dataLayer[ prodVendors ] ); ?>",
					"cd22": "<?php echo esc_attr( $dataLayer[ prodManufacturers ] ); ?>",
					"cd24": "<?php echo esc_attr( $dataLayer[ prodIds ] ); ?>",
					"cd25": <?php echo wp_json_encode( $dataLayer[ tags ] ); ?>,
					"cd26": "<?php echo esc_attr( $dataLayer[ content_type ] ); ?>",
					"cd27": "<?php echo esc_attr( $dataLayer[ articleId ] ); ?>",
					"cd28": "<?php echo esc_attr( $dataLayer[ displayType ] ); ?>",
					"cd29": "<?php echo esc_attr( $dataLayer[ author ] ); ?>",
					"cd30": "<?php echo esc_attr( $dataLayer[ source ] ); ?>",
					"cd39": "<?php echo esc_attr( $dataLayer[ datePublished ] ); ?>",
					"cd40": "<?php echo esc_attr( $dataLayer[ dateUpdate ] ); ?>",
					"cd41": "<?php echo esc_attr( $dataLayer[ daysSincePublished ] ); ?>",
					"cd42": "<?php echo esc_attr( $dataLayer[ daysSinceUpdated ] ); ?>",
					"cd44": "<?php echo esc_attr( $dataLayer[ isBlog ] ); ?>",
					"cd45": "<?php echo esc_attr( $dataLayer[ blogname ] ); ?>",
					"cd53": "<?php echo esc_attr( $dataLayer[ goldenTaxonomyIdPrimary ] ); ?>",
					"cd54": "<?php echo esc_attr( $dataLayer[ gtaxIdList ] ); ?>",
					"cd56": "<?php echo esc_attr( $dataLayer[ sponsorName ] ); ?>",
					"cd62": "<?php echo esc_attr( $dataLayer[ legacyCmsId ] ); ?>",
					"cd81": "<?php echo esc_attr( $dataLayer[ brandpost ] ); ?>",
					"cd82": "<?php echo esc_attr( $dataLayer[ podcastSponsored ] ); ?>",
					"cd83": "<?php echo esc_attr( ! empty( $_GET[ utm_date ] ) ? sanitize_text_field( $_GET[ utm_date ] ) : '' );  // phpcs:ignore -- Processing form data without nonce verification, not submitting a form here. ?>",
					"cg1": "<?php echo esc_attr( $dataLayer[ primaryCategory ] ); ?>",
					"cg2": "<?php echo esc_attr( $dataLayer[ content_type ] ); ?>",
					"cg3": "<?php echo esc_attr( $dataLayer[ author ] ); ?>",
					"cg4": "<?php echo esc_attr( $dataLayer[ displayType ] ); ?>"
					},
					"triggers": {
					"trackPageviewWithCustom": {
						"on": "visible",
						"request": "pageviewWithCsCm",
						"vars": {
						"cs": "google",
						"cm": "google amp"
						}
					},
					"trackClickOnAmpTwitterLink": {
						"on": "click",
						"selector": ".share-icons--twitter",
						"request": "social",
						"vars": {
						"socialNetwork": "Twitter",
						"socialAction": "Tweet AMP",
						"socialTarget": "<?php echo esc_attr( get_the_title() ); ?>"
						}
					},
					"trackClickOnAmpFacebookLink": {
						"on": "click",
						"selector": ".share-icons--facebook",
						"request": "social",
						"vars": {
						"socialNetwork": "Facebook",
						"socialAction": "Share AMP",
						"socialTarget": "<?php echo esc_attr( get_the_title() ); ?>"
						}
					},
					"trackClickOnAmpLinkedinLink": {
						"on": "click",
						"selector": ".share-icons--linkedin",
						"request": "social",
						"vars": {
						"socialNetwork": "LinkedIn",
						"socialAction": "Share AMP",
						"socialTarget": "<?php echo esc_attr( get_the_title() ); ?>"
						}
					},
					"trackClickOnAmpRedditLink": {
						"on": "click",
						"selector": ".share-icons--reddit",
						"request": "social",
						"vars": {
						"socialNetwork": "Reddit",
						"socialAction": "Share AMP",
						"socialTarget": "<?php echo esc_attr( get_the_title() ); ?>"
						}
					},
					"trackClickOnAmpEmailLink": {
						"on": "click",
						"selector": ".share-icons--email",
						"request": "social",
						"vars": {
						"socialNetwork": "Email",
						"socialAction": "Share AMP",
						"socialTarget": "<?php echo esc_attr( get_the_title() ); ?>"
						}
					},
					"affiliateLink": {
						"on": "click",
						"selector": "a[data-vars-product-id]",
						"request": "event",
						"vars": {
						"eventCategory": "Affiliate Link",
						"eventAction": "Click",
						"eventLabel": "${vendor} | ${productName}"
						},
						"extraUrlParams": {
						"cd15": "AMP",
						"cd21": "${vendor}",
						"cd22": "${manufacturer}",
						"cd23": "${productName}",
						"cd24": "${productId}",
						"cd50": "${linkPosition}"
						}
					}
					}
				}			
			</script>
		</amp-analytics>

			<?php
		}
	}
}
