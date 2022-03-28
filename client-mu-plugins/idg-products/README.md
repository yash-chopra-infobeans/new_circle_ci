# IDG Products Plugin

This plugin handles the creation and management of the products and the application of affiliate link wrapping.

## Requirements

- Custom Fields
- Territories

## Contents

- [Scripts and Styles](#scripts-and-styles)
  - [Components](#components)
  - [Hooks](#hooks)
- [Product Records](#product-records)
  - [Product Title](#product-title)
  - [Manufacturers](#manufacturers)
  - [Global Info](#global-info)
  - [Region Info](#region-info)
  - [Reviews](#reviews)
- [Product Blocks](#product-blocks)
  - [Price comparison block](#price-comparison-block)
  - [Product chart block](#price-comparison-block)
  - [Product widget block](#product-widget-block)
- [Link Wrapping](#link-wrapping)
  - [Subtag](#subtag)
  - [Settings](#settings)

## Scripts and Styles

Include the `idg-products` script and `idg-products` style dependencies to leverage dashboard dependencies related to products.

```php
wp_enqueue_script(
  'my-script',
  get_template_directory_uri() . '/path/to/my/script',
  [ 'idg-products' ],
  filemtime( get_template_directory() . '/path/to/my/script' ),
  false
);

wp_enqueue_style(
  'my-styles',
  get_template_directory_uri() . '/path/to/my/styles',
  [ 'idg-products' ],
  filemtime( get_template_directory() . '/path/to/my/styles' ),
  'all'
);
```

### Components

#### [Selector](src/gutenberg/components/Selector.js)

A component for selecting a product within the dashboard using a modal interface.

```js
const { __ }
const { Selector: ProductSelector } = window.IDGProducts.components;

const Edit = ({ attributes, setAttributes }) => {
  return (
    <ProductSelector
      id={attributes.productId}
      prefix={__('Product:')}
      title={__('Select a Product')}
      onSelect={id => setAttributes({ productId: Number(id) })}
    />
  );
}
```

### Hooks

#### useProduct

A hook for retrieving product data by id, that will update when an ID changes.

**Parameters**

1. `id {Number}` Product ID

**Returns** `Object|null|undefined`

#### usePaginatedProducts

A hook for interfacing with the post type endpoint to recieve a list of paginated products with a loading state.

**Parameters**

1. `search {String}` Search query
2. `filters {Array}` An array of taxonomy filters. 

**Returns** `Object`

```js
{
  products<Array>, 
  page<Number>
  totalPages<Number>, 
  fetchProducts<Function>, 
  isFetching<Boolean> 
}
```

##  Product Records

Product records are stored against the Content Hub and are shared across sites. Each product record covers multiple configurations of a single product, but products within a line will likely have separate records – for example, an Apple MacBook Pro 13-inch will have a different record to an Apple MacBook Pro 16-inch, but the MBP 16-inch product record will represent all configurations of that product. 

A product record in the context of WordPress is a custom post type, `product`, with the following data:

### Product Title

The default name of the product and is stored as the `post_title`.

### Manufacturers

A product's manufacturers stored as the taxonomy `manufacturer`.

### Categories

A product's categories stored as the taxonomy `category`.

### Origin CMS

If the product was migrated, the Origin CMS stored as the taxonomy `origin`.

### Region Info

Region info is stored as a single piece of product meta under they key `region_info` represented as a JSON string. The config for the creation and management of these fields can be found in `inc/config/product-fields.json`. They are rendered and managed using the custom fields plugin.

Region info will be stored against each territory defined in the territory taxonomy using an object with key value pairs where they `key` is the country code of the territory and the `value` is the data for that territory. For more information on territories see [`IDG Territories Plugin`]().

The schema for the data for each region is:

Key | Type | Description
--- | --- | --- 
`product_info` | `object` | Generic product info
`product_info.name` | `string` | An ovveride for the product name
`product_info.append_manufacturer` | `boolean` | Should the manufacturer names be appened?
`pricing` | `object` | Product pricing info
`pricing.price_options` | `string` | Free text pricing options
`pricing.currency` | `string` | The currency 
`pricing.price` | `number` | The price
`purchase_options` | `object` | Region specific purchase options
`purchase_options.vendor_codes` | `array<object>` | Product codes to connect Product Records to live pricing
`purchase_options.vendor_codes[].vendor` | `string` | The vendor code vendor
`purchase_options.vendor_codes[].code` | `string` | The vendor code

### Global Info

Global info stored as product meta under they key `global_info` as a JSON string. Global info is not attached to a specific region, or if it is it can also be applied to `all` regions. The config for the creation and management of these fields can be found in `inc/config/product-fields.json`. They are rendered and managed using the custom fields plugin.

The schema for `global_info` is:

Key | Type | Description
--- | --- | --- 
`purchase_options` | `object` | Global purchase options
`purchase_options.vendor_links` | `array<object>` | Direct links that enable editors to manually attach product links.
`purchase_options.vendor_links[].vendor` | `string` | The vendor name.
`purchase_options.vendor_links[].territory` | `string` | The territory to apply the link to.
`purchase_options.vendor_links[].url` | `string` | The direct link to the product.
`purchase_options.vendor_links[].currency` | `string` | The currency of the attached price.
`purchase_options.vendor_links[].price` | `number` | The price of the product at this vendor.

### Reviews

When a product is linked to a review block in a post, the associated data is stored as product meta with key `reviews`. Also for internal logic, an array of product ids are stored as meta with they key `reviews` on the post itself.

The  `reviews` meta on the product post type stores a JSON object where they key is the post ID and the value follows the following schema.

Key | Type | Description
--- | --- | --- 
`type` | `string` | 'primary|comparison - What type of review is it?'
`timestamp` | `number` | Timestamp of when the post was updated.
`primary` | `number` | The id of the primary product.
`comparison` | `number` | The id of the comparison product if provided.
`publication` | `object` | The term publication of the delivery site the post is published to.
`editors_choice` | `boolean` | Is it an editor's choice product.
`rating` | `number` | Rating out of 5.

## Product Blocks
### Price comparison block
When a product is linked to a Price comparison block, the block fetched pricing details for the selected product from different sources. You can attach a maximum of one product per block. Right now, the data are pulled in from two sources, Amazon API and product meta.

Price comparison block considers two product meta fields, `Purchase Options` and `Global Product Info`. Check [Global Info](#global-info) and [Region Info](#region-info) for detailed keys information. All the information fetched is rendered considering user's geolocation. In editor though, user's geolocation isn't considered and you are served `US` based data.

Price comparison block sorts fetched prices from lowest to highest. Items specified as `Out of stock` explicitly are slid down to the bottom of list. This detail comes from the Amazon API.

The Price comparison block, if has more than four records, will show a button to allow user to expand full list of pricing details.

The block provides and uses `idg/v1/product_pricing/<product_id>` custom endpoint to fetch product pricing details, the details received will already be sorted from lowest to highest.

Block attributes:
Key | Type | Default | Information
--- | --- | --- | ---
`productId` | `number` | 0 | Attached product's ID
`linksInNewTab` | `boolean` | `true` | Whether to open product purchase links in new tab or not
`footerText` | `string` | `Price comparison from over 24,000 stores worldwide` | Block's footer text

### Product chart block
Product chart blocks allows as many products to be attached as required. The block uses `InnerBlocks` component to achieve the functionality.

Product chart block's every attached product relies on multiple sources to get product information. Each product's pricing information is fetched from `idg/v1/product_pricing` custom endpoint and top three prices are rendered. Product chart block's each product uses data in  `Pricing` product meta present under [Region Info](#region-info) for `RRP` field. The pricing field and RRP fields' default values can be changed from global settings, `Settings > Global > Product Chart Block`.
All the information will be rendered considering current user's geolocation. However, in editor the user's geolocation isn't considered and `US` based data are rendered.

Each product in the block may have a link linking it to the most recent review of the attached product on current publication. On contenthub, publication of the review isn't considered and most recent review link will be used.

Products inside a product chart block can be re-ordered and the rank to all products will be auto-assigned starting from rank `1` to first product and so on.

`Product chart` block attributes:
Key | Type | Default | Information
--- | --- | --- | ---
`productData` | `array` | [] | Array of inner blocks' attributes
`isShowingRank` | `boolean` | true | Whether or not we are showing the product's rank
`linksInNewTab` | `boolean` | `true` | Whether to open product purchase links in new tab or not

The `Product chart` block uses `Product chart item` block as inner block which represents an individual product and its data.

`Product chart item` block attributes:
Key | Type | Default | Information
--- | --- | --- | ---
`rank` | `number` | 0 | Rank of the product among other products. It's auto assigned from the code.
`productId` | `number` | 0 | Attached product's ID
`productTitle` | `string` |  | Product's title/name (Source: Product meta/Editor)
`titleOverride` | `boolean` | false | Whether the product's title has been overridden from the editor
`productContent` | `string` |  | Product's description (Source: Editor)
`productRating` | `number` | 0 | Product's rating (Source: Product meta/Editor)
`ratingOverride` | `boolean` | false | Whether the product's rating has been overridden from the editor
`productImageSize` | `string` | `medium` | Product's image size. Possible values are: `small`, `medium`, `large`
`productImage` | `number` | 0 | Product's image ID
`imageFromOrigin` | `boolean` | false | Whether or not the overridden product image was uploaded from Contenthub or not

### Product widget block
Product widget block's data sources are exact same as a single product of [Product chart block](#product-chart-block). The block's attributes also resemble mostly to a single product of `Product chart block`.

Block attributes:
Key | Type | Default | Information
--- | --- | --- | ---
`productId` | `number` | 0 | Attached product's ID
`blockTitle` | `string` | `` | Block's title, appears in the block's header
`productImage` | `number` | 0 | Product's image ID
`imageFromOrigin` | `boolean` | false | Whether or not the overridden product image was uploaded from Contenthub or not
`isHalfWidth` | `boolean` | false | Whether or not the block is half width
`isFloatRight` | `boolean` | false | If it's a half width block, should it be floating right or left
`linksInNewTab` | `boolean` | `true` | Whether to open product purchase links in new tab or not

## Link Wrapping

The linkwrapper turns clean links, manually added by editors in articles, into affiliate links. This allows IDG to monetise these links and track/report on conversions.

### Subtag

The subtag is an alphanumeric code that forms part of the URL when linking to affiliate partners which helps to identify elements within the subtag for tracking purposes.

### Settings

Each delivery site will have a site ID, default rule and list of custom rules defined in a settings page located at `Settings -> Linkwrapping`. These settings are rendered and managed using the custom field plugin. The config for these settings can be found at `inc/config/linkwrapping-fields.json`. 

#### What is a rule?

A rule simply defines what the transformed url should look like. It is defined as a text input with the ability to pass in handlebar variables:

- `{{subtag}}` The dynamically generated subtag
- `{{target_url}}` - The original url
- `{{origin_url}}` - The current url of the page you are visiting

#### Default Rule

The default rule will act as a fallback and be applied to all links that don't have a custom rule applied. 

#### Block List

A list of URLs that the linkwrapping should **not** be applied to.

#### Custom Rules

Specific links can be targetted to apply different transformations. There is no limit to the amount of targets that can be added. A target is defined using a text input and can use plain text or regex to target a link. Target's can have different transforms depending on the geolocation of the user. These are defined by the territory to apply the transforms and the transformation itself.



