<?php

namespace IDG\Products;

/**
 * Plugin loader.
 *
 * @return void
 */
function setup() {
	new Product_Post_Type();
	new Search();
	new Link_Wrapping();
	new Reviews();
	new Article();
	new Product();
	new Data_Layer();
	new API\Product();
}
