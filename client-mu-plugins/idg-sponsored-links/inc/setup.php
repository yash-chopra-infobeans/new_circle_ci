<?php

namespace IDG\Sponsored_Links;

/**
 * Plugin loader.
 *
 * @return void
 */
function setup() {
	new Sponsored_Link_Post_Type();
}
