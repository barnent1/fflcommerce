<?php
/**
 * FFL Commerce Emails
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */

add_action('admin_init', function(){
	fflcommerce_emails::register_mail('admin_order_status_pending_to_processing', __('Order Pending to Processing for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_pending_to_completed', __('Order Pending to Completed for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_pending_to_on-hold', __('Order Pending to On-Hold for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_pending_to_waiting-for-payment', __('Order Pending to Waiting for Payment for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_on-hold_to_processing', __('Order On-Hold to Processing for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_completed', __('Order Completed for admin'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('admin_order_status_refunded', __('Order Refunded for admin'), get_order_email_arguments_description());

	fflcommerce_emails::register_mail('customer_order_status_pending_to_processing', __('Order Pending to Processing for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_pending_to_completed', __('Order Pending to Completed for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_pending_to_on-hold', __('Order Pending to On-Hold for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_pending_to_waiting-for-payment', __('Order Pending to Waiting for Payment for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_on-hold_to_processing', __('Order On-Hold to Processing for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_completed', __('Order Completed for customer'), get_order_email_arguments_description());
	fflcommerce_emails::register_mail('customer_order_status_refunded', __('Order Refunded for customer'), get_order_email_arguments_description());

	fflcommerce_emails::register_mail('low_stock_notification', __('Low Stock Notification'), get_stock_email_arguments_description());
	fflcommerce_emails::register_mail('no_stock_notification', __('No Stock Notification'), get_stock_email_arguments_description());
	fflcommerce_emails::register_mail('product_on_backorder_notification', __('Backorder Notification'), array_merge(get_stock_email_arguments_description(), get_order_email_arguments_description(), array('amount' => __('Amount', 'fflcommerce'))));
	fflcommerce_emails::register_mail('send_customer_invoice', __('Send Customer Invoice'), get_order_email_arguments_description());
}, 999);

$fflcommerceOrderEmailGenerator = function($action) {
	return function ($order_id) use ($action) {
		$options = FFLCommerce_Base::get_options();
		$order = new fflcommerce_order($order_id);
		fflcommerce_emails::send_mail('admin_order_status_'.$action, get_order_email_arguments($order_id), $options->get('fflcommerce_email'));
		fflcommerce_emails::send_mail('customer_order_status_'.$action, get_order_email_arguments($order_id), $order->billing_email);
	};
};

add_action('order_status_pending_to_processing', $fflcommerceOrderEmailGenerator('pending_to_processing'));
add_action('order_status_pending_to_completed', $fflcommerceOrderEmailGenerator('pending_to_completed'));
add_action('order_status_pending_to_on-hold', $fflcommerceOrderEmailGenerator('pending_to_on-hold'));
add_action('order_status_pending_to_waiting-for-payment', $fflcommerceOrderEmailGenerator('pending_to_waiting-for-payment'));
add_action('order_status_on-hold_to_processing', $fflcommerceOrderEmailGenerator('on-hold_to_processing'));
add_action('order_status_completed', $fflcommerceOrderEmailGenerator('completed'));
add_action('order_status_refunded', $fflcommerceOrderEmailGenerator('refunded'));

$fflcommerceStockEmailGenerator = function($action){
	return function ($product) use ($action){
		$options = FFLCommerce_Base::get_options();
		fflcommerce_emails::send_mail($action, get_stock_email_arguments($product), $options->get('fflcommerce_email'));
	};
};
add_action('fflcommerce_low_stock_notification', $fflcommerceStockEmailGenerator('low_stock_notification'));
add_action('fflcommerce_no_stock_notification', $fflcommerceStockEmailGenerator('no_stock_notification'));

add_action('fflcommerce_product_on_backorder_notification', function ($order_id, $product, $amount){
	$options = FFLCommerce_Base::get_options();
	fflcommerce_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $options->get('fflcommerce_email'));
	if ($product->meta['backorders'][0] == 'notify') {
		$order = new fflcommerce_order($order_id);
		fflcommerce_emails::send_mail('product_on_backorder_notification', array_merge(get_order_email_arguments($order_id), get_stock_email_arguments($product), array('amount' => $amount)), $order->billing_email);
	}
}, 1, 3);

add_filter('downloadable_file_url', function($link){
	return '<a href="' .$link. '">' .$link. '</a>';
}, 10, 1);

function get_order_email_arguments($order_id)
{
	$options = FFLCommerce_Base::get_options();
	$order = new fflcommerce_order($order_id);
	$inc_tax = ($options->get('fflcommerce_calc_taxes') == 'no') || ($options->get('fflcommerce_prices_include_tax') == 'yes');
	$can_show_links = ($order->status == 'completed' || $order->status == 'processing');
	$statuses = $order->get_order_statuses_and_names();

	$variables = array(
		'blog_name' => get_bloginfo('name'),
		'order_number' => $order->get_order_number(),
		'order_date' => date_i18n(get_option('date_format')),
		'order_status' => $statuses[$order->status],
		'shop_name' => $options->get('fflcommerce_company_name'),
		'shop_address_1' => $options->get('fflcommerce_address_1'),
		'shop_address_2' => $options->get('fflcommerce_address_2'),
		'shop_tax_number' => $options->get('fflcommerce_tax_number'),
		'shop_phone' => $options->get('fflcommerce_company_phone'),
		'shop_email' => $options->get('fflcommerce_company_email'),
		'customer_note' => $order->customer_note,
		'order_items_table' => fflcommerce_get_order_items_table($order, $can_show_links, true, $inc_tax),
		'order_items' => $order->email_order_items_list($can_show_links, true, $inc_tax),
		'order_taxes' => fflcommerce_get_order_taxes_list($order),
		'subtotal' => $order->get_subtotal_to_display(),
		'shipping' => $order->get_shipping_to_display(),
		'shipping_cost' => fflcommerce_price($order->order_shipping),
		'shipping_method' => $order->shipping_service,
		'discount' => fflcommerce_price($order->order_discount),
		'total_tax' => fflcommerce_price($order->get_total_tax()),
		'total' => fflcommerce_price($order->order_total),
		'is_local_pickup' => $order->shipping_method == 'local_pickup' ? true : null,
		'checkout_url' => $order->status == 'pending' ? $order->get_checkout_payment_url() : null,
		'payment_method' => $order->payment_method_title,
		'is_bank_transfer' => $order->payment_method == 'bank_transfer' ? true : null,
		'is_cash_on_delivery' => $order->payment_method == 'cod' ? true : null,
		'is_cheque' => $order->payment_method == 'cheque' ? true : null,
		'bank_info' => str_replace(PHP_EOL, '', fflcommerce_bank_transfer::get_bank_details()),
		'cheque_info' => str_replace(PHP_EOL, '', $options->get('fflcommerce_cheque_description')),
		'billing_first_name' => $order->billing_first_name,
		'billing_last_name' => $order->billing_last_name,
		'billing_company' => $order->billing_company,
		'billing_euvatno' => $order->billing_euvatno,
		'billing_address_1' => $order->billing_address_1,
		'billing_address_2' => $order->billing_address_2,
		'billing_postcode' => $order->billing_postcode,
		'billing_city' => $order->billing_city,
		'billing_country' => fflcommerce_countries::get_country($order->billing_country),
		'billing_state' => strlen($order->billing_state) == 2 ? fflcommerce_countries::get_state($order->billing_country, $order->billing_state) : $order->billing_state,
		'billing_country_raw' => $order->billing_country,
		'billing state_raw' => $order->billing_state,
		'billing_email' => $order->billing_email,
		'billing_phone' => $order->billing_phone,
		'shipping_first_name' => $order->shipping_first_name,
		'shipping_last_name' => $order->shipping_last_name,
		'shipping_company' => $order->shipping_company,
		'shipping_address_1' => $order->shipping_address_1,
		'shipping_address_2' => $order->shipping_address_2,
		'shipping_postcode' => $order->shipping_postcode,
		'shipping_city' => $order->shipping_city,
		'shipping_country' => fflcommerce_countries::get_country($order->shipping_country),
		'shipping_state' => strlen($order->shipping_state) == 2 ? fflcommerce_countries::get_state($order->shipping_country, $order->shipping_state) : $order->shipping_state,
		'shipping_country_raw' => $order->shipping_country,
		'shipping_state_raw' => $order->shipping_state,
	);

	if ($options->get('fflcommerce_calc_taxes') == 'yes') {
		$variables['all_tax_classes'] = $variables['order_taxes'];
	} else {
		unset($variables['order_taxes']);
	}

	return apply_filters('fflcommerce_order_email_variables', $variables, $order_id);
}

function get_order_email_arguments_description()
{
	return apply_filters('fflcommerce_order_email_variables_description', array(
		'blog_name' => __('Blog Name', 'fflcommerce'),
		'order_number' => __('Order Number', 'fflcommerce'),
		'order_date' => __('Order Date', 'fflcommerce'),
		'order_status' => __('Order Status', 'fflcommerce'),
		'shop_name' => __('Shop Name', 'fflcommerce'),
		'shop_address_1' => __('Shop Address part 1', 'fflcommerce'),
		'shop_address_2' => __('Shop Address part 2', 'fflcommerce'),
		'shop_tax_number' => __('Shop TaxNumber', 'fflcommerce'),
		'shop_phone' => __('Shop_Phone', 'fflcommerce'),
		'shop_email' => __('Shop Email', 'fflcommerce'),
		'customer_note' => __('Customer Note', 'fflcommerce'),
		'order_items_table' => __('HTML table with ordered items', 'fflcommerce'),
		'order_items' => __('Ordered Items', 'fflcommerce'),
		'order_taxes' => __('Taxes of the order', 'fflcommerce'),
		'subtotal' => __('Subtotal', 'fflcommerce'),
		'shipping' => __('Shipping Price and Method', 'fflcommerce'),
		'shipping_cost' => __('Shipping Cost', 'fflcommerce'),
		'shipping_method' => __('Shipping Method', 'fflcommerce'),
		'discount' => __('Discount Price', 'fflcommerce'),
		'total_tax' => __('Total Tax', 'fflcommerce'),
		'total' => __('Total Price', 'fflcommerce'),
		'payment_method' => __('Payment Method Title', 'fflcommerce'),
		'is_bank_transfer' => __('Is payment method Bank Transfer?', 'fflcommerce'),
		'is_cash_on_delivery' => __('Is payment method Cash on Delivery?', 'fflcommerce'),
		'is_cheque' => __('Is payment method Cheque?', 'fflcommerce'),
		'is_local_pickup' => __('Is Local Pickup?', 'fflcommerce'),
		'bank_info' => __('Company bank transfer details', 'fflcommerce'),
		'cheque_info' => __('Company cheque details', 'fflcommerce'),
		'checkout_url' => __('If order is pending, show checkout url', 'fflcommerce'),
		'billing_first_name' => __('Billing First Name', 'fflcommerce'),
		'billing_last_name' => __('Billing Last Name', 'fflcommerce'),
		'billing_company' => __('Billing Company', 'fflcommerce'),
		'billing_euvatno' => __('Billing EU Vat number', 'fflcommerce'),
		'billing_address_1' => __('Billing Address part 1', 'fflcommerce'),
		'billing_address_2' => __('Billing Address part 2', 'fflcommerce'),
		'billing_postcode' => __('Billing Postcode', 'fflcommerce'),
		'billing_city' => __('Billing City', 'fflcommerce'),
		'billing_country' => __('Billing Country', 'fflcommerce'),
		'billing_state' => __('Billing State', 'fflcommerce'),
		'billing_country_raw' => __('Raw Billing Country', 'fflcommerce'),
		'billing state_raw' => __('Raw Billing State', 'fflcommerce'),
		'billing_email' => __('Billing Email', 'fflcommerce'),
		'billing_phone' => __('Billing Phone    ', 'fflcommerce'),
		'shipping_first_name' => __('Shipping First Name', 'fflcommerce'),
		'shipping_last_name' => __('Shipping Last Name', 'fflcommerce'),
		'shipping_company' => __('Shipping Company', 'fflcommerce'),
		'shipping_address_1' => __('Shipping Address part 1', 'fflcommerce'),
		'shipping_address_2' => __('Shipping_Address part 2', 'fflcommerce'),
		'shipping_postcode' => __('Shipping Postcode', 'fflcommerce'),
		'shipping_city' => __('Shipping City', 'fflcommerce'),
		'shipping_country' => __('Shipping Country', 'fflcommerce'),
		'shipping_state' => __('Shipping State', 'fflcommerce'),
		'shipping_country_raw' => __('Raw Shipping Country', 'fflcommerce'),
		'shipping_state_raw' => __('Raw Shipping State', 'fflcommerce'),
	));
}

/**
 * @param \fflcommerce_product $product
 * @return array
 */
function get_stock_email_arguments($product)
{
	$options = FFLCommerce_Base::get_options();
	return array(
		'blog_name' => get_bloginfo('name'),
		'shop_name' => $options->get('fflcommerce_company_name'),
		'shop_address_1' => $options->get('fflcommerce_address_1'),
		'shop_address_2' => $options->get('fflcommerce_address_2'),
		'shop_tax_number' => $options->get('fflcommerce_tax_number'),
		'shop_phone' => $options->get('fflcommerce_company_phone'),
		'shop_email' => $options->get('fflcommerce_company_email'),
		'product_id' => $product->id,
		'product_name' => $product->get_title(),
		'sku' => $product->sku,
	);
}

function get_stock_email_arguments_description()
{
	return array(
		'blog_name' => __('Blog Name', 'fflcommerce'),
		'shop_name' => __('Shop Name', 'fflcommerce'),
		'shop_address_1' => __('Shop Address part 1', 'fflcommerce'),
		'shop_address_2' => __('Shop Address part 2', 'fflcommerce'),
		'shop_tax_number' => __('Shop TaxNumber', 'fflcommerce'),
		'shop_phone' => __('Shop_Phone', 'fflcommerce'),
		'shop_email' => __('Shop Email', 'fflcommerce'),
		'product_id' => __('Product ID', 'fflcommerce'),
		'product_name' => __('Product Name', 'fflcommerce'),
		'sku' => __('SKU', 'fflcommerce'),
	);
}

function fflcommerce_send_customer_invoice($order_id)
{
	$order = new fflcommerce_order($order_id);
	fflcommerce_emails::send_mail('send_customer_invoice', get_order_email_arguments($order_id), $order->billing_email);
}

/**
 * @param fflcommerce_order $order
 * @param bool $show_links
 * @param bool $show_sku
 * @param bool $includes_tax
 * @return string
 */
function fflcommerce_get_order_items_table($order, $show_links = false, $show_sku = false, $includes_tax = false)
{
	if (\FFLCommerce_Base::get_options()->get('fflcommerce_enable_html_emails', 'no') == 'no') {
		return $order->email_order_items_list($show_links, $show_sku, $includes_tax);
	}

	$use_inc_tax = $includes_tax;
	if ($use_inc_tax) {
		foreach ($order->items as $item) {
			$use_inc_tax = ($item['cost_inc_tax'] >= 0);
			if (!$use_inc_tax) {
				break;
			}
		}
	}

	$path = locate_template(array('fflcommerce/emails/items.php'));
	if (empty($path)) {
		$path = FFLCOMMERCE_DIR.'/templates/emails/items.php';
	}

	ob_start();
	include($path);

	return ob_get_clean();
}

/**
 * @param fflcommerce_order $order
 * @return string
 */
function fflcommerce_get_order_taxes_list($order)
{
	$taxes = '';

	foreach ($order->get_tax_classes() as $tax_class) {
		$taxes .= sprintf(
			_x('%s (%s%%): %.4f', 'emails', 'fflcommerce'),
			$order->get_tax_class_for_display($tax_class),
			$order->get_tax_rate($tax_class),
			$order->get_tax_amount($tax_class)
		).PHP_EOL;
	}

	return $taxes;
}

add_action('fflcommerce_install_emails', 'fflcommerce_install_emails');

function fflcommerce_install_emails()
{
	$default_emails = array(
		'new_order_admin_notification',
		'customer_order_status_pending_to_processing',
		'customer_order_status_pending_to_on-hold',
		'customer_order_status_pending_to_waiting-for-payment',
		'customer_order_status_on-hold_to_processing',
		'customer_order_status_completed',
		'customer_order_status_refunded',
		'send_customer_invoice',
		'low_stock_notification',
		'no_stock_notification',
		'product_on_backorder_notification'
	);
	$invoice = '
		[is_cheque]
			<p>'._x('We are waiting for your cheque before we can start processing this order.', 'emails', 'fflcommerce').'</p>
			<p>[cheque_info]</p>
			<p>Total value: [total]</p>
		[else]
		[is_bank_transfer]
			<p>'._x('We are waiting for your payment before we can start processing this order.', 'emails', 'fflcommerce').'</p>
			<h4>'._x('Bank details', 'emails', 'fflcommerce').'</h4>
			[bank_info]
			<p>Total value: [total]</p>
		[else]
		[is_local_pickup]
		<h4>'._x('Your order is being prepared', 'emails', 'fflcommerce').'</h4>
		<p>'._x('We are preparing your order, you will receive another email when we will be ready and awaiting for you to pick it up.', 'emails', 'fflcommerce').'</p>
		[else]
		[is_cash_on_delivery]
		<h4>'._x('Order will be dispatched shortly', 'emails', 'fflcommerce').'</h4>
		<p>'._x('Your order is being processed and will be dispatched to you as soon as possible. Please prepare exact change to pay when package arrives.', 'emails', 'fflcommerce').'</p>
		[/is_cash_on_delivery]
		[/is_local_pickup]
		[/is_bank_transfer]
		[/is_cheque]
		<h4>'._x('Order [order_number] on [order_date]', 'emails', 'fflcommerce').'</h4>
		[order_items_table]
		<h4>'._x('Customer details', 'emails', 'fflcommerce').'</h4>
		<p>'._x('Email:', 'emails', 'fflcommerce').' <a href="mailto:[billing_email]">[billing_email]</a></p>
		<p>'._x('Phone:', 'emails', 'fflcommerce').' [billing_phone]</p>
		<table class="customer">
			<thead>
				<tr>
					<td><strong>'._x('Billing address', 'emails', 'fflcommerce').'</strong></td>
					<td><strong>'._x('Shipping address', 'emails', 'fflcommerce').'</strong></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						[billing_first_name] [billing_last_name]<br />
						[billing_address_1][billing_address_2], [value][/billing_address_2]<br />
						[billing_city], [billing_postcode]<br />
						[billing_state]<br />
						[billing_country]
					</td>
					<td>
						[shipping_first_name] [shipping_last_name]<br />
						[shipping_address_1][shipping_address_2], [value][/shipping_address_2]<br />
						[shipping_city], [shipping_postcode]<br />
						[shipping_state]<br />
						[shipping_country]
					</td>
				</tr>
			</tbody>
		</table>
		[customer_note]
		<h4>'._x('Customer note', 'emails', 'fflcommerce').'</h4>
		<p>[value]</p>
		[/customer_note]
	';

	$title = '';
	$message = '';
	$post_title = '';
	foreach ($default_emails as $email) {
		switch ($email) {
			case 'new_order_admin_notification':
				$post_title = __('New order admin notification', 'fflcommerce');
				$title = __('[[shop_name]] New Customer Order', 'fflcommerce');
				$message = __('<p>You have received an order from [billing_first_name] [billing_last_name].</p><p>Current order status: <strong>[order_status]</strong></p>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_pending_to_on-hold':
				$post_title = __('Customer order status pending to on-hold', 'fflcommerce');
				$title = __('[[shop_name]] Order Received', 'fflcommerce');
				$message = __('<p>Thank you, we have received your order.</p>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_pending_to_waiting-for-payment':
				$post_title = __('Customer order status pending to waiting for payment', 'fflcommerce');
				$title = __('[[shop_name]] Order Received - waiting for payment', 'fflcommerce');
				$message = __('<p>Thank you, we have received your order.</p>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_pending_to_processing' :
				$post_title = __('Customer order status pending to processing', 'fflcommerce');
				$title = __('[[shop_name]] Order Received', 'fflcommerce');
				$message = __('<p>Thank you, we are now processing your order.<br/>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_on-hold_to_processing' :
				$post_title = __('Customer order status on-hold to processing', 'fflcommerce');
				$title = __('[[shop_name]] Order Received', 'fflcommerce');
				$message = __('<p>Thank you, we are now processing your order.<br/>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_completed' :
				$post_title = __('Customer order status completed', 'fflcommerce');
				$title = __('[[shop_name]] Order Complete', 'fflcommerce');
				$message = __('<p>Your order is complete.<br/>', 'fflcommerce').$invoice;
				break;
			case 'customer_order_status_refunded' :
				$post_title = __('Customer order status refunded', 'fflcommerce');
				$title = __('[[shop_name]] Order Refunded', 'fflcommerce');
				$message = __('<p>Your order has been refunded.</p>', 'fflcommerce').$invoice;
				break;
			case 'send_customer_invoice' :
				$post_title = __('Send customer invoice', 'fflcommerce');
				$title = __('Invoice for Order: [order_number]', 'fflcommerce');
				$message = $invoice;
				break;
			case 'low_stock_notification' :
				$post_title = __('Low stock notification', 'fflcommerce');
				$title = __('[[shop_name]] Product low in stock', 'fflcommerce');
				$message = __('<p>#[product_id] [product_name] ([sku]) is low in stock.</p>', 'fflcommerce');
				break;
			case 'no_stock_notification' :
				$post_title = __('No stock notification', 'fflcommerce');
				$title = __('[[shop_name]] Product out of stock', 'fflcommerce');
				$message = __('<p>#[product_id] [product_name] ([sku]) is out of stock.</p>', 'fflcommerce');
				break;
			case 'product_on_backorder_notification' :
				$post_title = __('Product on backorder notification', 'fflcommerce');
				$title = __('[[shop_name]] Product Backorder on Order: [order_number].', 'fflcommerce');
				$message = __('<p>#[product_id] [product_name] ([sku]) was found to be on backorder.</p>', 'fflcommerce').$invoice;
				break;
		}
		$post_data = array(
			'post_content' => $message,
			'post_title' => $post_title,
			'post_status' => 'publish',
			'post_type' => 'shop_email',
			'post_author' => 1,
			'ping_status' => 'closed',
			'comment_status' => 'closed',
		);
		$post_id = wp_insert_post($post_data);
		update_post_meta($post_id, 'fflcommerce_email_subject', $title);
		if ($email == 'new_order_admin_notification') {
			fflcommerce_emails::set_actions($post_id, array(
				'admin_order_status_pending_to_processing',
				'admin_order_status_pending_to_completed',
				'admin_order_status_pending_to_on-hold',
				'admin_order_status_pending_to_waiting-for-payment',
			));
			update_post_meta($post_id, 'fflcommerce_email_actions', array(
				'admin_order_status_pending_to_processing',
				'admin_order_status_pending_to_completed',
				'admin_order_status_pending_to_on-hold',
				'admin_order_status_pending_to_waiting-for-payment',
			));
		} else {
			fflcommerce_emails::set_actions($post_id, array($email));
			update_post_meta($post_id, 'fflcommerce_email_actions', array($email));
		}
	}

	\FFLCommerce_Base::get_options()->set('fflcommerce_enable_html_emails', 'yes');
}
