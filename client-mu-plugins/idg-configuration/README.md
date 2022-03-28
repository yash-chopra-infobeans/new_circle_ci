# IDG Configuration
The IDG Configuration plugin is an area for globally required setup items and configuration for plugins, utilities and additional hook interactions that do not belong in a theme setting. Majority of what would be included within here is restricted to Content Hub and editor environments.

<!-- A high level description of what the plugin is for and what it does. -->

## Table of Contents
<!-- Add and remove from here where required. -->
- PHP
  - [Constants](#constants)
	- [PHP Methods](#php-methods)
	- [PHP Functions](#php-functions)
	- [PHP Hooks](#php-hooks)

---

# PHP
## Constants

#### `IDG_ENABLE_FALLBACK_LOGGING`
Whether to use the fallback logging if BugSnag is not available. When set to `true` the any meta logging usually destined for BugSnag will instead be output to `debug.log` if that is also available to use.

**default:** `true`
## PHP Methods
#### `IDG\Configuration\Error_Reporting::can_use_bugsnag`
Checks whether BugSnag is available and setup on the current environment.

**Arguments:**
`void`

**Usage:**

```php
$use_bugsnag = IDG\Configuration\Error_Reporting::can_use_bugsnag();

if ( ! $use_bugsnag ) {
  throw new Error( 'BugSnag not available on this environment' );
}
```

#### `IDG\Configuration\Error_Reporting::notifyError`
The local variant of BugSnags notifyError() method. Saves the error report to the local error log if that is active.

**Arguments:**
- [string] `$error_type` The type of error to be notified about.
- [string] `$message` The message to include in the error.
- [array] `$data` The data which should be saved against the error.
- [string] `$type` The type of notification.

**Usage:**

```php
IDG\Configuration\Error_Reporting::notifyError( 'ErrorType', 'An error has occurred.', [ 'id' => 1 ], 'info' );
```

#### `IDG\Configuration\Error_Reporting::setMetaData`
The local variant of BugSnags setMetaData() method. When setting the meta data, if using the local variant it is sent straight to the debug log.

**Arguments:**
- [array] `$meta` An array of meta data to output.

**Usage:**

```php
IDG\Configuration\Error_Reporting::setMetaData( [ 'id' => 1 ] );
```

---

### PHP Functions
<!-- Utility Functions have the same documentation requirements as Utility Methods. -->

#### `idg_notify_error`
Create a notification in BugSnag or fallback to custom error reporting if BugSnag is not available on the current install. See `IDG\Configuration\Error_Reporting::notifyError`.

**Arguments:**
- [string] `$error_type` The type of error to be notified about.
- [string] `$message` The message to include in the error.
- [array] `$data` The data which should be saved against the error.
- [string] `$type` The type of notification.

**Usage:**

```php
idg_notify_error( 'ErrorType', 'An error has occurred.', [ 'id' => 1 ], 'info' );
```

#### `idg_set_error_report_meta`
Add meta to a bugsnag error report, or fallback to custom error reporting if BugSnag is not available on the current install. See `IDG\Configuration\Error_Reporting::setMetaData`.

**Arguments:**
- [array] `$meta` An array of meta data to output.

**Usage:**

```php
idg_set_error_report_meta( [ 'id' => 1 ] );
```

---

## PHP Hooks
### Filters
#### `idg_error_report_can_use_bugsnag`
Allows to define whether bugsnag can be used at that given time, so it can be disabled for specific events. Returning false will cause local debug logs to be used.

**Constant:** `IDG\Configuration\Error_Reporting::CAN_USE_BUGSNAG_HOOK`

**Arguments:**
- [bool] `$can_use` Whether bugsnag can be used.

**Usage:**

```php
add_filter( 'idg_error_report_can_use_bugsnag', '__return_false' );
```

#### `idg_error_report_log_data_output`
Allows for adjustment of data that is to be output to the debuglog.

**Constant:** `IDG\Configuration\Error_Reporting::LOG_DATA_OUTPUT_HOOK`

**Arguments:**
- [array] `$output` The data being sent to the debug log.
- [string] `$called` The called method.
- [array] `$called_args` The arguments sent with the method call.

**Usage:**

```php
add_filter( 'idg_error_report_log_data_output', function( $data ) {
  if ( 'info' !== $data['type'] ) {
    $data['data']['user'] => [
      'user_id' => 1,
    ];
  }

  return $data;
}, 10, 1 );
```

---

### Actions
<!-- Actions have the same documentation requirements as Filters, exception being that the constant should be ACTION rather than FILTER. -->
