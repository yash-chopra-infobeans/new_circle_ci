# IDG Third Party

This plugin handles most third party integrations.

## Settings

Refer to the settings schema in `inc/config/settings-fields.json` for a reference to all third party options. Any config per intergration that could differ per delivery site should be defined as a setting where each vendor is a seperate section. All settings are localized to the window. See examples below for retrieving settings:

```php
$vendor_settings = \IDG\Third_Party\Settings::get( '<vendor_name>', '<key>' );
```

```js
$vendor_settings = window.IDG.settings['<vendor_name>']['<key>'];
```

## CMP

To comply with GDPR and CCPA, some third party vendors must require consent. Sourcepoint is the third party tool using to require consent from the user. Please refer to their documentation for more information https://documentation.sourcepoint.com/web-implementation/web-implementation/sourcepoint-gdpr-and-tcf-v2-support. A wrapper around sourcepoint is provided to abstract the consent logic. See the example below:

> Some vendors integrate with sourcepoint automatically and therefore will just need to wait untill sourcepoint has done it's thing before loading. However, there are custom vendors that require an aditional check.

```js
window.IDG.CMP.onConsentApplied(() => {
  // Consent has been applied.

  // Load vendors.
  
  if (window.ID.CMP.hasConsentForVendor('<vendor_id>')) {
    // Load Custom Vendor 
  }
});
```

## DataLayer

The datalayer should act as a point of reference for any third party integrations to pull data from. See examples below on how to add or get an item from the datalayer.

```php
	add_filter( 'idg_data_layer', function( $data_layer ) {
    $data_layer['example'] = 'test';
    return $data_layer;
  } );
```

```js
  const example = window.IDG.getItemFromDataLayer('<key>');
  const dataLayer = window.IDG.getDataLayer();
```

## Nativo
Nativo is a third-party service we are using to bring in programmatic "native" (sponsored) content on the IDGE and CSMB brands. Per the Nativo integration guide: "For publishers, Nativo provides a complete native ad solution enabling easy activation, deployment, and management of native advertising campaigns across all digital platforms including: desktop, mobile, & apps."

## Integral Ad Science (IAS)
Integral Ad Science is a global technology company that offers data and solutions to establish a safer, more effective advertising ecosystem. Analyzes the value of digital advertising placements.

## Outbrain
Outbrain is a native advertising company. It uses targeted advertising to recommend articles, slideshows, blog posts, photos or videos to a reader. Some of the content recommended by Outbrain link to publisher's own content, while others link to other sites.

## Permutive
Permutive is a publisher-focused data management platform. Permutive provides a SaaS data management platform that allows publishers to increase their targetable advertising and deliver ROI.

[Documentation](https://developer.permutive.com/) - Password protected, request access from IDG.

### Example:
```
 window.permutive.track('AffiliateLinkClick', {});
```
## Bounce X
Bounce Exchange is a software for behavioural marketing technologies, created to de-anonymise site visitors, analyse their digital behaviour and create relevant digital experiences regardless of channel or device.

## JW Player
JW Player offers an advanced and flexible media player for publishing videos, running video ads, and streaming web content

## Subscribers

