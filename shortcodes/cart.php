<?php
/**
 * Cart shortcode
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Checkout
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 * 
 */
function get_fflcommerce_cart($atts) {
    return fflcommerce_shortcode_wrapper('fflcommerce_cart', $atts);
}

function fflcommerce_cart($atts)
{
	unset(fflcommerce_session::instance()->selected_rate_id);

	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && fflcommerce::verify_nonce('cart')) {
		$coupon_code = sanitize_title($_POST['coupon_code']);
		fflcommerce_cart::add_discount($coupon_code);
	} elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && fflcommerce::verify_nonce('cart')) { // Update Shipping
		unset(fflcommerce_session::instance()->chosen_shipping_method_id);
		$country = $_POST['calc_shipping_country'];
		$state = $_POST['calc_shipping_state'];
		$postcode = $_POST['calc_shipping_postcode'];

		if ($postcode && !fflcommerce_validation::is_postcode($postcode, $country)) {
			fflcommerce::add_error(__('Please enter a valid postcode/ZIP.', 'fflcommerce'));
			$postcode = '';
		} elseif ($postcode) {
			$postcode = fflcommerce_validation::format_postcode($postcode, $country);
		}

		if ($country) { // Update customer location
			fflcommerce_customer::set_location($country, $state, $postcode);
			fflcommerce_customer::set_shipping_location($country, $state, $postcode);

			fflcommerce::add_message(__('Shipping costs updated.', 'fflcommerce'));
		} else {
			fflcommerce_customer::set_shipping_location('', '', '');
			fflcommerce::add_message(__('Shipping costs updated.', 'fflcommerce'));
		}
	} elseif (isset($_POST['shipping_rates'])) {
		$rates_params = explode(":", $_POST['shipping_rates']);
		$available_methods = fflcommerce_shipping::get_available_shipping_methods();
		$shipping_method = $available_methods[$rates_params[0]];

		if ($rates_params[1] != null) {
			fflcommerce_session::instance()->selected_rate_id = $rates_params[1];
		}

		$shipping_method->choose(); // chooses the method selected by user.
	}

	// Re-Calc prices. This needs to happen every time the cart page is loaded and after checking post results.
	fflcommerce_cart::calculate_totals();

	$result = fflcommerce_cart::check_cart_item_stock();
	if (is_wp_error($result)) {
		fflcommerce::add_error($result->get_error_message());
	}

	fflcommerce_render('shortcode/cart', array(
		'cart' => fflcommerce_cart::get_cart(),
		'coupons' => fflcommerce_cart::get_coupons(),
	));
}
