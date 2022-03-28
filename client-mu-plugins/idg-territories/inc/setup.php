<?php

namespace IDG\Territories;

/**
 * Plugin Loader.
 *
 * @return void
 */
function setup() {
	new Territory_Taxonomy();
	new Territory_Loader();
	new Geolocation();
}
