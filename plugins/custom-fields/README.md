# Custom Fields

> Custom Fields is a WIP

Build  custom fields for different entity types. Custom Fields is designed to work with modern WordPress development. Fields are rendered using React and utilise components, principles and functionality from the block editor.

## Supported Entities

- [x] Post Types
- [x] Options
- [ ] Terms (TODO)
- [ ] Users (TODO)
- [ ] Comments (TODO)
- [ ] Custom Entities (TODO)

## Requirements

- Minimum WP Version: 5.5
- Minimum PHP Version: 7.2

## Features

- Add custom fields to the block editor to easily manage post meta.
- Add option pages with custom fields.
- Create your own field types.
- Extendable in various ways allowing you to extend or modify the plugin without editing it directly.
- Nested repeatable fields.
- Reduces amount of data stored in the database by stringifying grouped fields and storing them as a JSON representation.
- Re-useable field groups.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Configuration Object](#configuration-object)
- [Custom Field Types](#custom-field-types)
- [Plugins and Slots](#plugins-and-slots)
- [Hooks and Filters](#hooks-and-filters)

## Basic Usage

Use one of the following helper functions to register or create an entity with a configuration object and retrieve a value from a registered field.

**`cf_register_post_type`**

Register custom fields to a post type.

- `@param object $config` - The config object
- `@param string $post_type` - The post type to attach fields to
- `@param boolean $lock_template` - Should other blocks be allowed?
- `@return Custom_Fields\Fields\Post_Type`

**`cf_register_options_page`**

Create a new options page with custom fields.

- `@param string $config` - The post type to attach fields to
- `@param string $key` - The post type to attach fields to
- `@param string $title` - The id of the options page.
- `@return Custom_Fields\Fields\Options_Page`

**`cf_get_value`**

Retrive a value from a registered custom field.

- `@param mixed $id` - The id of the entity.
- `@param string $prop` - The entity prop.
- `@param string $key` - Key value in dot notation - can be blank.
- `@param string $kind` - The entity kind.
- `@return mixed` - The value or null

### Example

#### Defining a config object

```json
{
  "sections": [{
    "title": "My Section",
    "name": "my_section"
  }],
  "field_groups": [{
    "title": "My Field Group",
    "name": "my_field_group",
    "sections": ["my_section"],
    "fields":  [{
      "title": "Example Field",
      "key": "example_field",
      "type": "textarea",
      "width": 100
    }]
  }]
}
```

#### Registering a settings page

```php
<?php

$config = json_decode( file_get_contents( 'config.json' ) );

cf_register_options_page( $config, 'example_settings', __( 'Example' ) );
```

#### Retrieving a value

```php
$example_field_value = cf_get_value( 'example_settings', 'my_section', 'my_field_group.example_field' );
```

## Configuration Object

The configuration object is validated using [JSON schema Draft-07](https://json-schema.org/draft-07/json-schema-release-notes.html).

If prefered, the JSON schemas can be used as a reference when defining the configuration object: 
- `inc/base-schema.json`
- `inc/default-fields-schema.json`

### Sections

A section defines a section visually within the gutenberg editor **and** contextually. Each section creates a new prop on whicever entity it has been defined using the `name` value as it's key.

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | No | If provided, render a title for the section. | `string`
`name` | Yes | The section prop key. | `string`
`tabs` | No | Splits the section into tabs. Subsequent fields can be rendered conitionally per tab.  | `array<object>`
`tabs<item>.title` | Yes | The title of the tab, rendered in the editor.  | `string`
`tabs<item>.name` | Yes | The tab identifier and they key of the key value pair fot this tab's data.  | `string`

### Field Groups

A field group creates a group of fields that can be re-used in multiple sections.

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | No | The title of the field group, rendered in the editor. | `string`
`sections` | Yes | An array of sections for this field group to be attached to.  | `array<string>`
`fields` | Yes |  An array of fields to be associated with this group.  | `array<object>`

### Fields

Fields are an object defined within field groups. Some settings can apply to all field types:

Setting | Required | Description | Type
--- | --- | --- | --- 
`type` | Yes | Defines the type of field to render | `string`
`exclude_from` | No | Defines the section(s) **or** tab(s) to exclude this field from. | `array<string>`
`width` | No | Each field is a flex item and it's default state is `flex-grow: 1`. If a width is defined this will act as a percentage flex basis instead. | `integer`
`validation` | No | Supply a schema validation and custom message

### Validation

The validation key takes an object with two properties `message` and `schema`. The message is the message to be rendered alongside the corresponding field if the field fails validation and the schema can be any valid JSON schema that will be checked against the field value.  

### Default Field Types

- [text](#text)
- [textarea](#text-area)
- [richtext](#rich-text)
- [number](#number)
- [toggle](#toggle)
- [select](#select)
- [repeater](#repeater)

#### text

Standard text input.

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`

#### textarea

Standard text area input.

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`

#### handlebars

A text input with handlebar variables.

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`
`variables` | No | A list of handlebar variables. | `array`

#### richtext

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`
`disabled_features` | No | A list of features to disable. | `array`
`handlebar_variables` | No | A list of handlebar variables to be used within the rich text. | `array`
`single_line` | No | Display the rich text input as a single line input | `boolean`
`plain_text` | No | Ensure the saved output is plain text instead of HTML (useful when only wanting to use a single feature such as handlebar variables) | `boolean`

#### number

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`

#### toggle

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`

#### select

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | Yes | The field label, rendered in the editor. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`
`options` | Yes | An array of options for selection | `array<object>`
`options<item>.label` | Yes | The option label, rendered in the editor. | `string`
`options<item>.value` | Yes | The option's value. | `string`
`default` | No | The default value to be used | `string`
`default_tabs` | No | The default value to be used within a specific tab; `"{ "tab_name": "default_value" }"` | `object`

#### repeater

Setting | Required | Description | Type
--- | --- | --- | --- 
`title` | No | The field label and plural name for an item. | `string`
`key` | Yes | The field key, used as a key in it's key value pair. | `string`
`singular` | Yes | The singular term for an item. | `string`
`plural` | Yes | The plural term for an item. | `string`
`fields` | Yes | An array of fields to be used within an item. Any fields that are defined can be used, including repeaters which can be nested indefinitely. | `array<object>`
`panel` | No | If used, renders the field using a panel component. | `object|boolean`
`panel.title` | No | The title of the panel. | `string`
`panel.title_keys` | No | Used instead of `panel.title`. Replaces each item with the value of the field in that repeater, if it exists. Formatted as a key value pair where the key is the field key and the value is the default value if that field key does not exist or has not data; `{ "field_key": "Default Title", "field_key_2": "Default Title Two"}` | `object`

## Custom Field Types

To create a custom field type, first define a field type using the `cf_field_types` filter. A field type must be an object with the following properties:

### Properties
Property | Required | Description | Type
--- | --- | --- | --- 
`type` | Yes | The field type, which will be used as a reference. | `string`
`schema` | Yes | A schema following [JSON schema Draft-07](https://json-schema.org/draft-07/json-schema-release-notes.html) to define the properties a user will need to supply when using the field type. | `object`

### Example
```php
add_filter( 'cf_field_types', function( $fields ) {
  $example_field_schema = <<<'JSON'
{
  "type": "example",
  "schema": {
    "properties": {
      "title": { "type": "string" },
      "key": { "type": "string", "pattern": "^\\S*$" }
    },
    "required": ["title", "key"]
  }
}
JSON;

  $fields[] = json_decode( $example_field_schema );

  return $fields;
} );
```

Secondly, include a script that registers a component to be used in it's place. To include the script use the `cf_enqueue_assets` action which passes in the entity that is loaded.

### Example
```php
add_action( 'cf_enqueue_assets', function( $entity ) {
  if ( $entity->prop !== 'product' ) {
    return;
  }

  // Enqueue Scripts
} );
```

Then, using the public api attached to the window register a field component for the field that has been defined.

### Example
```js
const { TextControl } = wp.components
const { register } = window.CustomFields;

register('fieldTypes', {
  name: 'example', // The field type
  render: ({ field, getValue, updateValue }) => (
    <>
      <h3>My Example Field</h3>
      <TextControl
        label={field.title}
        type="text"
        value={getValue(field.key)}
        onChange={value => updateValue(value, field.key)}
      />
    </>
  ),
});
```

## Plugins and Slots

There are various [slots](https://developer.wordpress.org/block-editor/components/slot-fill/) available to extend what is rendered. To define one you will first need to define a plugin and then apply the fills you wish to add in that plugin from the available slots: 

- `cf/before-fields`
- `cf/after-fields`
- `cf/before-section-${section}`
- `cf/after-section-${section}`

### Example

```js
const { Fill } = wp.components;
const { register } = window.CustomFields;

register('plugins', {
  name: 'example',
  render: () => (
    <>
      <Fill name="cf/before-fields">
        <p>This is rendered before all of the fields.</p>
      </Fill>
      <Fill name="cf/after-fields">
        <p>This is rendered after all of the fields.</p>
      </Fill>
    </>
  ),
});
```

## Hooks and Filters

#### `cf_field_types`

Filter used to add additional field types to the fields schema.

```php 
add_filter( 'cf_field_types', function( array $field_types ) {
  // ...

  return $field_types;
});
```

#### `cf_enqueue_assets`

Action executed when the cf assets are enqueud, useful for adding scripts to extend cf using plugins and slots.

```php 
add_action( 'cf_enqueue_assets', function( object $entity ) {
  // ...
});
```













