<?php

namespace IDG\Asset_Manager;

/**
 * Plugin loader.
 *
 * @return void
 */
function setup() {
	new Loader();
	new Search();
	new Meta_Fields();
	new Scripts_And_Styles();
	new Menus();
	new Video();
}
