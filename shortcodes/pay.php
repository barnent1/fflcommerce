<?php
/**
 * Payment page shortcode
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
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop
 * @license             GNU General Public License v3
 * 
 */

function get_fflcommerce_pay($atts)
{
	return fflcommerce_shortcode_wrapper('fflcommerce_pay', $atts);
}

/**
 * Outputs the pay page - payment gateways can hook in here to show payment forms etc
 **/
function fflcommerce_pay()
{
	if (isset($_GET['pay_for_order']) && isset($_GET['order']) && isset($_GET['order_id'])) {
		// Pay for existing order
		$order_key = urldecode($_GET['order']);
		$order_id = (int)$_GET['order_id'];
		$order = new fflcommerce_order($order_id);

		fflcommerce::show_messages();

		if ($order->id == $order_id && $order->order_key == $order_key && $order->status == 'pending')
		{
			fflcommerce_pay_for_existing_order($order);
		}
	} else {
		// Pay for order after checkout step
		if (isset($_GET['order'])) {
			$order_id = $_GET['order'];
		} else {
			$order_id = 0;
		}
		if (isset($_GET['key'])) {
			$order_key = $_GET['key'];
		} else {
			$order_key = '';
		}

		if ($order_id > 0) {
			$order = new fflcommerce_order($order_id);

			if ($order->order_key == $order_key && $order->status == 'pending') {
				fflcommerce::show_messages();

				?>
				<ul class="order_details">
					<li class="order">
						<?php _e('Order:', 'fflcommerce'); ?>
						<strong><?php echo $order->get_order_number(); ?></strong>
					</li>
					<li class="date">
						<?php _e('Date:', 'fflcommerce'); ?>
						<strong><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)); ?></strong>
					</li>
					<li class="total">
						<?php _e('Total:', 'fflcommerce'); ?>
						<strong><?php echo fflcommerce_price($order->order_total); ?></strong>
					</li>
					<li class="method">
						<?php _e('Payment method:', 'fflcommerce'); ?>
						<strong><?php
							$gateways = fflcommerce_payment_gateways::payment_gateways();
							if (isset($gateways[$order->payment_method])) {
								echo $gateways[$order->payment_method]->title;
							} else {
								echo $order->payment_method;
							}
							?></strong>
					</li>
				</ul>

				<?php do_action('receipt_'.$order->payment_method, $order_id); ?>

				<div class="clear"></div>
			<?php
			}
		}
	}
}

function fflcommerce_pay_action()
{
	if (!is_fflcommerce_single_page(JIGOSHOP_PAY)) {
		return;
	}

	if (isset($_GET['pay_for_order']) && isset($_GET['order']) && isset($_GET['order_id'])) {
		// Pay for existing order
		$order_key = urldecode($_GET['order']);
		$order_id = (int)$_GET['order_id'];
		$order = new fflcommerce_order($order_id);

		if ($order->id == $order_id && $order->order_key == $order_key && $order->status == 'pending')
		{
			// Set customer location to order location
			if ($order->billing_country) {
				fflcommerce_customer::set_country($order->billing_country);
			}
			if ($order->billing_state) {
				fflcommerce_customer::set_state($order->billing_state);
			}
			if ($order->billing_postcode) {
				fflcommerce_customer::set_postcode($order->billing_postcode);
			}

			// Pay form was posted - process payment
			if (isset($_POST['pay']) && fflcommerce::verify_nonce('pay')) { // Update payment method
				if ($order->order_total > 0) {
					$payment_method = fflcommerce_clean($_POST['payment_method']);
					$data = (array)maybe_unserialize(get_post_meta($order_id, 'order_data', true));
					$data['payment_method'] = $payment_method;
					update_post_meta($order_id, 'order_data', $data);

					$available_gateways = fflcommerce_payment_gateways::get_available_payment_gateways();

					$result = $available_gateways[$payment_method]->process_payment($order_id);

					// Redirect to success/confirmation/payment page
					if ($result['result'] == 'success') {
						wp_safe_redirect($result['redirect']);
						exit;
					}
				} else { // No payment was required for order
					$order->payment_complete();
					// filter redirect page
					$checkout_redirect = apply_filters('fflcommerce_get_checkout_redirect_page_id', fflcommerce_get_page_id('thanks'));
					wp_safe_redirect(get_permalink($checkout_redirect));
					exit;
				}
			}
		} elseif ($order->status != 'pending') {
			fflcommerce::add_error(__('Your order has already been paid for. Please contact us if you need assistance.', 'fflcommerce'));
		} else {
			fflcommerce::add_error(__('Invalid order.', 'fflcommerce'));
		}
	} else {
		// Pay for order after checkout step
		if (isset($_GET['order'])) {
			$order_id = $_GET['order'];
		} else {
			$order_id = 0;
		}
		if (isset($_GET['key'])) {
			$order_key = $_GET['key'];
		} else {
			$order_key = '';
		}

		if ($order_id > 0) {
			$order = new fflcommerce_order($order_id);

			if ($order->order_key != $order_key || $order->status != 'pending') {
				wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id('myaccount'))));
				exit;
			}
		} else {
			wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id('myaccount'))));
			exit;
		}
	}
}
add_action('template_redirect', 'fflcommerce_pay_action');

/**
 * Outputs the payment page when a user comes to pay from a link (for an existing/past created order)
 */
function fflcommerce_pay_for_existing_order($pay_for_order)
{
	global $order;
	$order = $pay_for_order;

	fflcommerce_get_template('checkout/pay_for_order.php');
}
