<?php

/**
 * FFL Commerce Payment Gateway class
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
abstract class fflcommerce_payment_gateway
{
	var $id;
	var $title;
	var $chosen;
	var $has_fields;
	var $countries;
	var $availability;
	var $enabled;
	var $icon;
	var $description;

	public function __construct()
	{
		FFLCommerce_Base::get_options()->install_external_options_onto_tab(__('Payment Gateways', 'fflcommerce'), $this->get_default_options());
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param $order_id int Order ID to process.
	 * @return array
	 */
	public abstract function process_payment($order_id);

	/**
	 * Default Option settings for WordPress Settings API using the FFLCommerce_Options class
	 * These should be installed on the FFLCommerce_Options 'Payment Gateways' tab
	 */
	protected function get_default_options()
	{
		return array();
	}

	public function is_available()
	{
		if ($this->enabled == "yes") {
			return true;
		}

		return false;
	}

	public function set_current()
	{
		$this->chosen = true;
	}

	public function icon()
	{
		if ($this->icon) {
			return '<img src="'.fflcommerce::force_ssl($this->icon).'" alt="'.$this->title.'" />';
		}

		return '';
	}

	public function admin_options()
	{
	}

	public function validate_fields()
	{
		return true;
	}

	/**
	 * provides functionality to tell checkout if
	 * the gateway should be processed or not. If false, the gateway will not be
	 * processed, otherwise the gateway will be processed.
	 *
	 * @param $subtotal
	 * @param $shipping_total
	 * @param int $discount
	 * @return boolean defaults to needs_payment from cart class. If overridden, the gateway will provide
	 * details as to when it should or shouldn't be processed.
	 * @since 1.2
	 */
	public function process_gateway($subtotal, $shipping_total, $discount = 0)
	{
		// default to cart needs_payment() to keep the same functionality that FFL Commerce offers today
		// if overridden, the gateway will provide the details when to skip or not
		return fflcommerce_cart::needs_payment();
	}
}