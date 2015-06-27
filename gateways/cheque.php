<?php
/**
 * Cheque Payment Gateway
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
 */

/**
 * Add the gateway to FFL Commerce
 **/
function add_cheque_gateway( $methods ) {
	$methods[] = 'fflcommerce_cheque';
	return $methods;
}
add_filter( 'fflcommerce_payment_gateways', 'add_cheque_gateway', 15 );


class fflcommerce_cheque extends fflcommerce_payment_gateway {

	public function __construct() {

		parent::__construct();

        $this->id				= 'cheque';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= FFLCommerce_Base::get_options()->get('fflcommerce_cheque_enabled');
		$this->title 			= FFLCommerce_Base::get_options()->get('fflcommerce_cheque_title');
		$this->description 		= FFLCommerce_Base::get_options()->get('fflcommerce_cheque_description');

    	add_action('thankyou_cheque', array(&$this, 'thankyou_page'));
    }

	/**
	* There are no payment fields for cheques, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	function thankyou_page() {
		if ($this->description) echo wpautop(wptexturize($this->description));
	}

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new fflcommerce_order( $order_id );

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('waiting-for-payment', __('Awaiting cheque payment', 'fflcommerce'));

		// Remove cart
		fflcommerce_cart::empty_cart();

		// Return thankyou redirect
		$checkout_redirect = apply_filters( 'fflcommerce_get_checkout_redirect_page_id', fflcommerce_get_page_id('thanks') );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink( $checkout_redirect )))
		);

	}

	/**
	 * Default Option settings for WordPress Settings API using the FFLCommerce_Options class
	 *
	 * These will be installed on the FFLCommerce_Options 'Payment Gateways' tab by the parent class 'fflcommerce_payment_gateway'
	 *
	 */
	protected function get_default_options() {

		$defaults = array();

		// Define the Section name for the FFLCommerce_Options
		$defaults[] = array( 'name' => __('Cheque Payment', 'fflcommerce'), 'type' => 'title', 'desc' => __('Allows cheque payments. Allows you to make test purchases without having to use the sandbox area of a payment gateway. Quite useful for demonstrating to clients and for testing order emails and the \'success\' pages etc.', 'fflcommerce') );

		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Cheque Payment','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_cheque_enabled',
			'std' 		=> 'yes',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'fflcommerce'),
				'yes'			=> __('Yes', 'fflcommerce')
			)
		);

		$defaults[] = array(
			'name'		=> __('Method Title','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.','fflcommerce'),
			'id' 		=> 'fflcommerce_cheque_title',
			'std' 		=> __('Cheque Payment','fflcommerce'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Customer Message','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Let the customer know the payee and where they should be sending the cheque to and that their order won\'t be shipping until you receive it.','fflcommerce'),
			'id' 		=> 'fflcommerce_cheque_description',
			'std' 		=> __('Please send your cheque to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'fflcommerce'),
			'type' 		=> 'longtext'
		);

		return $defaults;
	}

}
