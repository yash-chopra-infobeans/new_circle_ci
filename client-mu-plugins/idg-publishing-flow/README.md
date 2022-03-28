# IDG Status Flow

<!-- A high level description of what the plugin is for and what it does. -->

## Preview
![](screenshot.png)

---
## Table of Contents
<!-- Add and remove from here where required. -->
- [Configuration Notes](#configuration-notes)
- [Building Assets](#building-assets)
- Javascript
	- [Store Methods](#store-methods)
		- [Actions](#actions)
		- [Selectors](#selectors)
	- [Javascript Hooks](#javascript-hooks)
- PHP
	- [PHP Methods](#php-methods)
	- [PHP Hooks](#php-hooks)

---

## Configuration Notes
<!-- This includes short guides and information for other developers with plugins that may require a certain level of configuration. -->
<!-- Include code examples where needed. -->
<!-- If there are dependencies of other plugis, also list them here with a short sentence on why they're needed. -->

---

## Building Assets
As with most plugins you will need to ensure that your installed packages are up to date. You can do this by running `yarn` to have them install. All commands for assets can be found in the `./packages.json` file.

Basic commands include the following:

### Development
Development assets can be compiled using `yarn build:dev`

If you are actively developing this plugin you can use the watch command `yarn watch:dev` to update assets during development.

### Production
Production assets can be compiled using `yarn build:prod`.

### Running Tests
Tests are built using JEST. Jest tests can be run using `yarn test:js` with coverage output using the relevant flags `yarn test:js --coverage`.

## Store Methods
Editorial notes store reside on the `namespace/store` namespace so relevant methods can be accessible from `dispatch` and `select`.

### Actions
#### `methodName`
<!-- Description about this action -->

**Arguments:**
<!-- List of arguments accepted by the method. -->
<!-- objects should list accepted properties if required. -->

- exampleArg (`type`): Description on what this argument is for.
- *options* (`object`): Object of options and configuration settings.
	- options.property (`type`): Description for the property.
	- options.propertyFunction (`function`): Description for the function.

<!-- Arguments should be documented for object properties that are functions. -->
##### `options.propertyFunction` arguments 
Name | Type | Default | Description
--- | --- | --- | ---
`example` | `type` | n/a | Description on what this argument is for.

**Usage:**
```js
const { dispatch } = wp.data;

dispatch('namespace/store').methodName('some value', {
  property: 'some prop value',
  propertyFunction: (example) => {
    // ...
  },
});
```

---

### Selectors
<!-- Selectors have the same documentation requirements as Actions. -->

---

## Javascript Hooks
### Actions
#### `namespace.pluginName.hookName`
<!-- Description about this hook -->

**Parameters:**

- exampleParam (`string`): Some description of the expected param.

**Usage:**

```js
const { addAction } = wp.hooks;

addAction('namespace.pluginName.hookName', 'your-plugin-name', exampleParam => {
	// ...
});
```

---

### Filters
<!-- Filters have the same documentation requirements as Actions. -->

---

## PHP Utility Methods
#### `Namespace\Example_Class::example_method`
<!-- Description of this method. -->

**Arguments:**

- `$some_arg` (`type`) *`default_value`*: Some description of the argument.

**Usage:**

```php
$value = Namespace\Example_Class::example_method( $some_arg );
```

---

### PHP Utility Functions
<!-- Utility Functions have the same documentation requirements as Utility Methods. -->

---

## PHP Hooks
### Filters
#### `project_plugin_name_hook_name`
<!-- Description of this hook. -->

**Constant:** `Namespace\Example_Class::HOOK_NAME_FILTER`

**Arguments:**

- `$filter_arg` (`type`) *`default_value`*: The value that will be passed to the provided method/function.

**Usage:**

```php
add_filter( 'project_plugin_name_hook_name', function( $filter_arg ) {
	// ...
} );
```

---

### Actions
<!-- Actions have the same documentation requirements as Filters, exception being that the constant should be ACTION rather than FILTER. -->
