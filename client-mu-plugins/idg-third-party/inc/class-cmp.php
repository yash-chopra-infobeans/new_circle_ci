<?php

namespace IDG\Third_Party;

use function IDG\Base_Theme\Utils\is_amp;

/**
 * CMP [Sourcepoint].
 */
class CMP {
	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( Loader::INLINE_HEAD_ACTION, [ $this, 'head' ] );
		add_action( 'amp_post_template_head', [ $this, 'amp' ] );
		add_action( 'idg_footer_base', [ $this, 'cmp_buttons' ] );
	}

	/**
	 * Load amp stuff.
	 *
	 * @return void
	 */
	public function amp() {
		$settings      = Settings::get( 'cmp' );
		$base_endpoint = $settings['account']['base_endpoint'];

		?>
		<style amp-custom>
			#eea-consent-ui,
			#ccpa-consent-ui {
				display: none;
			}
			body.amp-geo-group-eea #eea-consent-ui,
			body.amp-geo-group-ccpa #ccpa-consent-ui {
				display: block;
			}
		</style>
		<amp-geo layout="nodisplay">
			<script type="application/json">
				{
					"ISOCountryGroups": {
						"eea": ["preset-eea", "unknown"],
						"ccpa": ["preset-us-ca"]
					}
				}
			</script>
		</amp-geo>
		<amp-consent id='consent' layout='nodisplay' type='SourcePoint'>
			<script type="application/json">
				{
					"consentInstanceId": "sourcepoint",
					"consentRequired": false,
					"geoOverride": {
						"ccpa": {
							"consentRequired": "remote",
							"checkConsentHref": "<?php echo esc_attr( $base_endpoint ); ?>/ccpa/consent/amp",
							"promptUISrc": "<?php echo esc_attr( $base_endpoint ); ?>/amp/index.html?authId=CLIENT_ID",
							"postPromptUI": "ccpa-consent-ui",
							"uiConfig": {
								"overlay": true
							},
							"clientConfig": {
								"accountId": <?php echo esc_attr( $settings['account']['id'] ); ?>,
								"siteHref": "<?php echo esc_attr( $settings['ccpa']['href_amp'] ); ?>",
								"privacyManagerId": "<?php echo esc_attr( $settings['ccpa']['privacy_manager_uuid'] ); ?>",
								"siteId": <?php echo esc_attr( $settings['ccpa']['property_id'] ); ?>,
								"stageCampaign": false,
								"getDnsMsgMms": true,
								"isCCPA": true,
								"alwaysDisplayDns": false,
								"showNoticeUntilAction": true
							}
						},
						"eea": {
							"consentRequired": "remote",
							"checkConsentHref": "<?php echo esc_attr( $base_endpoint ); ?>/wrapper/tcfv2/v1/amp-v2",
							"promptUISrc": "<?php echo esc_attr( $base_endpoint ); ?>/amp/index.html?authId=CLIENT_ID",
							"postPromptUI": "privacy-settings-prompt",
							"uiConfig": {
								"overlay": true
							},
							"clientConfig": {
								"accountId": <?php echo esc_attr( $settings['account']['id'] ); ?>,
								"propertyHref": "<?php echo esc_attr( $settings['gdpr']['href_amp'] ); ?>",
								"mmsDomain": "<?php echo esc_attr( $base_endpoint ); ?>",
								"privacyManagerId": <?php echo esc_attr( $settings['gdpr']['privacy_manager_id_amp'] ); ?>,
								"propertyId": <?php echo esc_attr( $settings['gdpr']['property_id'] ); ?>,
								"isTCFV2": true,
								"pmTab": "purposes",
								"stageCampaign": false
							}
						}
					}
				}
			</script>
		</amp-consent>
		<?php
	}

	/**
	 * Render CMP buttons
	 *
	 * @return void
	 */
	public function cmp_buttons() {
		?>

		<div class="cmp">
			<div id="ccpa-consent-ui">
				<button <?php echo is_amp() ? 'on="tap:consent.prompt(consent=SourcePoint)"' : ''; ?> >
					<?php echo esc_html( __( 'Do Not Sell My Info' ) ); ?>
				</button>
			</div>
			<div id="eea-consent-ui">
				<button <?php echo is_amp() ? 'on="tap:consent.prompt(consent=SourcePoint)"' : ''; ?> >
					<?php echo esc_html( __( 'Privacy Settings' ) ); ?>
				</button>
			</div>
		</div>

		<?php
	}

	/**
	 * Add CMP Stubs to head.
	 *
	 * @return void
	 */
	public function head() {
		?>
			// GDPR Stub
			!function () { var e = function () { var e, t = "__tcfapiLocator", a = [], n = window; for (; n;) { try { if (n.frames[t]) { e = n; break; } } catch (e) { } if (n === window.top) break; n = n.parent } e || (!function e() { var a = n.document, r = !!n.frames[t]; if (!r) if (a.body) { var i = a.createElement("iframe"); i.style.cssText = "display:none", i.name = t, a.body.appendChild(i) } else setTimeout(e, 5); return !r }(), n.__tcfapi = function () { for (var e, t = arguments.length, n = new Array(t), r = 0; r < t; r++)n[r] = arguments[r]; if (!n.length) return a; if ("setGdprApplies" === n[0]) n.length > 3 && 2 === parseInt(n[1], 10) && "boolean" == typeof n[3] && (e = n[3], "function" == typeof n[2] && n[2]("set", !0)); else if ("ping" === n[0]) { var i = { gdprApplies: e, cmpLoaded: !1, cmpStatus: "stub" }; "function" == typeof n[2] && n[2](i) } else a.push(n) }, n.addEventListener("message", (function (e) { var t = "string" == typeof e.data, a = {}; try { a = t ? JSON.parse(e.data) : e.data } catch (e) { } var n = a.__tcfapiCall; n && window.__tcfapi(n.command, n.version, (function (a, r) { var i = { __tcfapiReturn: { returnValue: a, success: r, callId: n.callId } }; t && (i = JSON.stringify(i)), e.source.postMessage(i, "*") }), n.parameter) }), !1)) }; "undefined" != typeof module ? module.exports = e : e() }();

			// CCPA Stub
			(function () { var e = false; var c = window; var t = document; function r() { if (!c.frames["__uspapiLocator"]) { if (t.body) { var a = t.body; var e = t.createElement("iframe"); e.style.cssText = "display:none"; e.name = "__uspapiLocator"; a.appendChild(e) } else { setTimeout(r, 5) } } } r(); function p() { var a = arguments; __uspapi.a = __uspapi.a || []; if (!a.length) { return __uspapi.a } else if (a[0] === "ping") { a[2]({ gdprAppliesGlobally: e, cmpLoaded: false }, true) } else { __uspapi.a.push([].slice.apply(a)) } } function l(t) { var r = typeof t.data === "string"; try { var a = r ? JSON.parse(t.data) : t.data; if (a.__cmpCall) { var n = a.__cmpCall; c.__uspapi(n.command, n.parameter, function (a, e) { var c = { __cmpReturn: { returnValue: a, success: e, callId: n.callId } }; t.source.postMessage(r ? JSON.stringify(c) : c, "*") }) } } catch (a) { } } if (typeof __uspapi !== "function") { c.__uspapi = p; __uspapi.msgHandler = l; c.addEventListener("message", l, false) } })();
		<?php
	}
}
