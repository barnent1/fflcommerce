<?php
/**
 * Cash on delivery Payment Gateway
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

/**
 * Add the gateway to FFL Commerce
 **/
function add_cod_gateway( $methods ) {
	$methods[] = 'fflcommerce_cod';
	return $methods;
}
add_filter( 'fflcommerce_payment_gateways', 'add_cod_gateway', 30 );


class fflcommerce_cod extends fflcommerce_payment_gateway {

	public function __construct() {

        parent::__construct();

        $this->id				= 'cod';
        $this->icon 			= '';
        $this->has_fields 		= false;

		$this->enabled			= FFLCommerce_Base::get_options()->get('fflcommerce_cod_enabled');
		$this->title 			= FFLCommerce_Base::get_options()->get('fflcommerce_cod_title');
		$this->description 		= FFLCommerce_Base::get_options()->get('fflcommerce_cod_description');

    	add_action('thankyou_cod', array(&$this, 'thankyou_page'));
    }

	/**
	* There are no payment fields for cods, but we want to show the description if set.
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

		$status = FFLCommerce_Base::get_options()->get('fflcommerce_cod_status', 'processing');
		$order->update_status($status, __('Waiting for cash delivery.', 'fflcommerce'));

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
		$defaults[] = array( 'name' => __('Cash on Delivery', 'fflcommerce'), 'type' => 'title', 'desc' => __('Allows cash payments. Good for offline stores or having customers pay at the time of receiving the product.', 'fflcommerce') );

		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Cash on Delivery','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_cod_enabled',
			'std' 		=> 'no',
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
			'id' 		=> 'fflcommerce_cod_title',
			'std' 		=> __('Cash on Delivery','fflcommerce'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Customer Message','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_cod_description',
			'std' 		=> __('Please pay to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'fflcommerce'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Order status','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('This allow to choose status which should be set to order, after completing checkout.', 'fflcommerce'),
			'id' 		=> 'fflcommerce_cod_status',
			'type' 		=> 'select',
			'choices'   => array(
				'processing' => __('Processing', 'fflcommerce'),
				'on-hold' => __('On-hold', 'fflcommerce'),
			)
		);

		return $defaults;
	}

}
