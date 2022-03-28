<?php

namespace IDG\Golden_Taxonomy;

/**
 * Plugin setup.
 *
 * @return void
 */
function setup() {
	new Meta_Boxes();
	new Taxonomy();
	new Data_Layer();
}
