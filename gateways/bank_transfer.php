<?php
/**
 * Bank Transfer Payment Gateway
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
function add_bank_transfer_gateway( $methods ) {
	$methods[] = 'fflcommerce_bank_transfer';
	return $methods;
}
add_filter( 'fflcommerce_payment_gateways', 'add_bank_transfer_gateway', 20 );


class fflcommerce_bank_transfer extends fflcommerce_payment_gateway {

	public function __construct() {

        parent::__construct();

        $this->id				= 'bank_transfer';
        $this->icon 			= '';
        $this->has_fields 		= false;
		$this->enabled			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_enabled');
		$this->title 			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_title');
		$this->description 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_description');
		$this->bank_name 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_bank_name');
		$this->acc_number 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_acc_number');
		$this->sort_code 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_sort_code');
		$this->account_holder 	= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_account_holder');
		$this->iban 			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_iban');
		$this->bic 				= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_bic');
		$this->additional 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_additional');

    	add_action( 'thankyou_bank_transfer', array(&$this, 'thankyou_page') );
    }

	/**
	 * Format Bank information to display in emails
	 **/
	public static function get_bank_details() {

		$title 			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_title');
		$description 	= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_description');
		$bank_name 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_bank_name');
		$acc_number 	= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_acc_number');
		$account_holder = FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_account_holder');
		$sort_code 		= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_sort_code');
		$iban 			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_iban');
		$bic 			= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_bic');
		$additional 	= FFLCommerce_Base::get_options()->get('fflcommerce_bank_transfer_additional');

		$bank_info = null;
		if ($description) $bank_info .= wpautop(wptexturize($description)) . PHP_EOL;
		if ($bank_name) $bank_info .= __('Bank Name', 'fflcommerce').": \t" . wptexturize($bank_name) . PHP_EOL;
		if ($acc_number) $bank_info .= __('Account Number', 'fflcommerce').":\t " .wptexturize($acc_number) . PHP_EOL;
		if ($account_holder) $bank_info .= __('Account Holder', 'fflcommerce').":\t " .wptexturize($account_holder) . PHP_EOL;
		if ($sort_code) $bank_info .= __('Sort Code', 'fflcommerce').":\t" . wptexturize($sort_code) . PHP_EOL;
		if ($iban) $bank_info .= __('IBAN', 'fflcommerce').": \t\t" .wptexturize($iban) . PHP_EOL;
		if ($bic) $bank_info .= __('BIC', 'fflcommerce').": \t\t " .wptexturize($bic) . PHP_EOL;
		if ($additional) $bank_info .= wpautop(__('Additional Information', 'fflcommerce').": " . PHP_EOL . wpautop(wptexturize($additional)));

		if ($bank_info)
			return wpautop($bank_info);

	}

	/**
	* There are no payment fields for Bank Transfers, we need to show bank details instead.
	**/
	function payment_fields() {
		$bank_info = null;
		if ($this->bank_name) $bank_info .= '<strong>'.__('Bank Name', 'fflcommerce').'</strong>: ' . wptexturize($this->bank_name) . '<br />';
		if ($this->acc_number) $bank_info .= '<strong>'.__('Account Number', 'fflcommerce').'</strong>: '.wptexturize($this->acc_number) . '<br />';
		if ($this->account_holder) $bank_info .= '<strong>'.__('Account Holder', 'fflcommerce').'</strong>: '. wptexturize($this->account_holder) . '<br />';
		if ($this->sort_code) $bank_info .= '<strong>'.__('Sort Code', 'fflcommerce').'</strong>: '. wptexturize($this->sort_code) . '<br />';
		if ($this->iban) $bank_info .= '<strong>'.__('IBAN', 'fflcommerce').'</strong>: '.wptexturize($this->iban) . '<br />';
		if ($this->bic) $bank_info .= '<strong>'.__('BIC', 'fflcommerce').'</strong>: '.wptexturize($this->bic) . '<br />';
		if ($this->description) echo wpautop(wptexturize($this->description));
		if (!empty($bank_info)) echo wpautop($bank_info);
		if ($this->additional) echo wpautop('<strong>'.__('Additional Information', 'fflcommerce').'</strong>:');
		if ($this->additional) echo wpautop(wptexturize($this->additional));
	}

	function thankyou_page() {
		$bank_info = null;
		if ($this->bank_name) $bank_info .= '<strong>'.__('Bank Name', 'fflcommerce').'</strong>: ' . wptexturize($this->bank_name) . '<br />';
		if ($this->acc_number) $bank_info .= '<strong>'.__('Account Number', 'fflcommerce').'</strong>: '.wptexturize($this->acc_number) . '<br />';
		if ($this->account_holder) $bank_info .= '<strong>'.__('Account Holder', 'fflcommerce').'</strong>: '. wptexturize($this->account_holder) . '<br />';
		if ($this->sort_code) $bank_info .= '<strong>'.__('Sort Code', 'fflcommerce').'</strong>: '. wptexturize($this->sort_code) . '<br />';
		if ($this->iban) $bank_info .= '<strong>'.__('IBAN', 'fflcommerce').'</strong>: '.wptexturize($this->iban) . '<br />';
		if ($this->bic) $bank_info .= '<strong>'.__('BIC', 'fflcommerce').'</strong>: '.wptexturize($this->bic) . '<br />';

		if ($this->description) echo wpautop(wptexturize($this->description));
		if ($bank_info) echo wpautop($bank_info);
		if ($this->additional) echo wpautop('<strong>'.__('Additional Information', 'fflcommerce').'</strong>:');
		if ($this->additional) echo wpautop(wptexturize($this->additional));
	}

	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {

		$order = new fflcommerce_order( $order_id );
		$order->update_status('waiting-for-payment', __('Awaiting Bank Transfer', 'fflcommerce'));
		fflcommerce_cart::empty_cart();
		$checkout_redirect = apply_filters( 'fflcommerce_get_checkout_redirect_page_id', fflcommerce_get_page_id('thanks') );
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( $checkout_redirect ) ) )
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
		$defaults[] = array( 'name' => __('Bank Transfer', 'fflcommerce'), 'type' => 'title', 'desc' => __('Accept Bank Transfers as a method of payment. There is no automated process associated with this, you must manually process an order when you receive payment.', 'fflcommerce') );

		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Bank Transfer','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_bank_transfer_enabled',
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
			'id' 		=> 'fflcommerce_bank_transfer_title',
			'std' 		=> __('Bank Transfer Payment','fflcommerce'),
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Customer Message','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Let the customer know that their order won\'t be shipping until you receive payment.','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_description',
			'std' 		=> __('Please use the details below to transfer the payment for your order, once payment is received your order will be processed.','fflcommerce'),
			'type' 		=> 'longtext'
		);

		$defaults[] = array(
			'name'		=> __('Bank Name','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Your bank name for reference. e.g. HSBC','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_bank_name',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Account Number','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Your Bank Account number.','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_acc_number',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Account Holder','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('The account name your account is registered to.','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_account_holder',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Sort Code','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Your branch Sort Code.','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_sort_code',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('IBAN','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Your IBAN number. (for International transfers)','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_iban',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('BIC Code','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Your Branch Identification Code. (BIC Number)','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_bic',
			'std' 		=> '',
			'type' 		=> 'text'
		);

		$defaults[] = array(
			'name'		=> __('Additional Info','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> __('Additional information you want to display to your customer.','fflcommerce'),
			'id' 		=> 'fflcommerce_bank_transfer_additional',
			'std' 		=> '',
			'type' 		=> 'longtext'
		);

		return $defaults;
	}

}
