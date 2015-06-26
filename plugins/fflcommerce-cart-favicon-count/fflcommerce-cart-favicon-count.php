<?php
if(!defined('FFLCOMMERCE_CART_FAVICON_COUNT_DIR'))
{
	define('FFLCOMMERCE_CART_FAVICON_COUNT_DIR', dirname(__FILE__));
}
// Define plugin URL for assets
if(!defined('FFLCOMMERCE_CART_FAVICON_COUNT_URL'))
{
	define('FFLCOMMERCE_CART_FAVICON_COUNT_URL', plugins_url('', __FILE__));
}

function init_cart_favicon()
{
	if(class_exists('fflcommerce'))
	{
		load_plugin_textdomain('fflcommerce_cart_favicon_count', false, dirname(plugin_basename(__FILE__)).'/languages/');

		// Set up class loaders
		require_once(FFLCOMMERCE_CART_FAVICON_COUNT_DIR.'/src/fflcommerce/Extension/CartFaviconCount.php');
		new \fflcommerce\Extension\CartFaviconCount();
	}
}
add_action('fflcommerce_initialize_plugins', 'init_cart_favicon');
