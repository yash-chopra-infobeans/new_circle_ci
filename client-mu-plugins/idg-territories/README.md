# IDG Territories Plugin

This plugin handles the creation and management of the territory taxonomy.

## Description

A territory is defined as a term under the territory taxonomy. To retrieve country and currency data the [Rinvex Country](https://github.com/rinvex/countries) package is being used. 

> Rinvex Country is a simple and lightweight package for retrieving country details with flexibility. A whole bunch of data including name, demonym, capital, iso codes, dialling codes, geo data, currencies, flags, emoji, and other attributes for all 250 countries worldwide at your fingertips.

When adding or editing a territory term, the user is given the option to chose a country and currency. The `iso 3166 1 alpha2` country code is stored as the term slug and the `iso 4217` currency code is stored as term meta under the key `default_currency`. This is all the information needed to access the country and currency data using the `Rinvex Country` package. 

## Territory Loader

Use the `Territory_Loader` to retrieve territories:

```php
use IDG\Territories\Territory_Loader;

// All territories
$territories = Territory_Loader::territories();

// A single territory
$territory = Territory_Loader::territory( get_term( $term_id ) );
```

## Territory Decorator

The `Territory_Loader` will return an array or single instance(s) of `IDG\Territories\Territory`. `IDG\Territories\Territory` is a decorator class wrapping `Rinvex\Country\Country` with extra methods and variables. Aswell as being able to access all variables and methods of `Rinvex\Country\Country` (see https://github.com/rinvex/countries#advanced-usage) there are these additional variables and methods:

Name | Type | Description
--- | --- | --- 
`term` | `object` | The associated term to the territory.
`default_currency` | `string` | The default iso 4217 currency code.
`get_default_currency` | `method` |  Returns the default currency data as an array.
`get_term_name` | `method` |  Gets the user inputted term name.

## Geolocation

To geolocate there are a two helper methods available.

```
use IDG\Territories\Geolocation;

// Country Code
$country_code = Geolocation::get_country_code();

// Territory
$territory = Geolocation::get_territory();
```






