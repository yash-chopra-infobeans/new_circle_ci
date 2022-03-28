<?php

namespace IDG\Third_Party;

/**
 * Plugin Loader.
 *
 * @return void
 */
function setup() {
	new \IDG\Third_Party\Settings();
	new \IDG\Third_Party\Loader();
	new \IDG\Third_Party\Base_Data_Layer();
	new \IDG\Third_Party\CMP();
	new \IDG\Third_Party\Ias();
	new \IDG\Third_Party\GPT\Ad_Slots();
	new \IDG\Third_Party\GTM();
	new \IDG\Third_Party\Permutive();
	new \IDG\Third_Party\Nativo();
	new \IDG\Third_Party\Outbrain();
	new \IDG\Third_Party\Subscribers();
	new \IDG\Third_Party\Bounce_X();
}
