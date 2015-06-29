<?php
/**
 * Checkout shortcode
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

function get_fflcommerce_checkout( $atts ) {
	return fflcommerce_shortcode_wrapper('fflcommerce_checkout', $atts);
}

function fflcommerce_checkout( $atts ) {
	if (!defined('FFLCOMMERCE_CHECKOUT')) define('FFLCOMMERCE_CHECKOUT', true);

	$non_js_checkout = (isset($_POST['update_totals']) && $_POST['update_totals']) ? true : false;

	$result = fflcommerce_cart::check_cart_item_stock();

	if (is_wp_error($result)) fflcommerce::add_error( $result->get_error_message() );

	if ( ! fflcommerce::has_errors() && $non_js_checkout) fflcommerce::add_message( __('The order totals have been updated. Please confirm your order by pressing the Place Order button at the bottom of the page.', 'fflcommerce') );

	fflcommerce::show_messages();

	fflcommerce_get_template('checkout/form.php', false);

}

function fflcommerce_process_checkout()
{
	if (!is_checkout() || is_fflcommerce_single_page(FFLCOMMERCE_PAY)) {
		return;
	}

	if (count(fflcommerce_cart::get_cart()) == 0) {
		wp_safe_redirect(get_permalink(fflcommerce_get_page_id('cart')));
		exit;
	}

	/** @var fflcommerce_checkout $_checkout */
	$_checkout = fflcommerce_checkout::instance();
	$result = $_checkout->process_checkout();

	if(isset($result['result']) && $result['result'] === 'success'){
		wp_safe_redirect(apply_filters('fflcommerce_is_ajax_payment_successful', $result['redirect']));
		exit;
	}

	if(isset($result['redirect'])){
		wp_safe_redirect(get_permalink($result['redirect']));
		exit;
	}
}
add_action('template_redirect', 'fflcommerce_process_checkout');
