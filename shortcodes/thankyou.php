<?php
/**
 * Thank you shortcode
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

function get_fflcommerce_thankyou( $atts ) {
	return fflcommerce_shortcode_wrapper('fflcommerce_thankyou', $atts);
}

/**
 * Outputs the thankyou page
 **/
function fflcommerce_thankyou() {

	$thankyou_message = __('<p>Thank you. Your order has been processed successfully.</p>', 'fflcommerce');
	echo apply_filters( 'fflcommerce_thankyou_message', $thankyou_message );

	// Pay for order after checkout step
	if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
	if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';

	if ($order_id > 0) :

		$order = new fflcommerce_order( $order_id );

		if ($order->order_key == $order_key) :

			?>
			<?php do_action( 'fflcommerce_thankyou_before_order_details', $order->id ); ?>
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
						if (isset($gateways[$order->payment_method])) echo $gateways[$order->payment_method]->title;
						else echo $order->payment_method;
					?></strong>
				</li>
			</ul>
			<div class="clear"></div>
			<?php

			do_action( 'thankyou_' . $order->payment_method, $order_id );
			do_action( 'fflcommerce_thankyou', $order->id );

		endif;

	endif;

	echo '<p><a class="button" href="'.esc_url( fflcommerce_cart::get_shop_url() ).'">'.__('&larr; Continue Shopping', 'fflcommerce').'</a></p>';

}