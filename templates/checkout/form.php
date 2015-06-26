<?php
/**
 * Checkout form template
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
?>

<?php do_action('before_checkout_form');
// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'fflcommerce_get_checkout_url', fflcommerce_cart::get_checkout_url() ); ?>

<form name="checkout" method="post" class="checkout" action="<?php echo esc_url( $get_checkout_url ); ?>">

	<h3 id="order_review_heading"><?php _e('Your Order', 'fflcommerce'); ?></h3>

	<?php do_action('fflcommerce_checkout_order_review'); ?>

	<div class="col2-set" id="customer_details">
		<div class="col-1">

			<?php do_action('fflcommerce_checkout_billing'); ?>

		</div>
		<div class="col-2">

			<?php do_action('fflcommerce_checkout_shipping'); ?>

		</div>
	</div>

	<h3 id="payment_methods_heading"><?php _e('Payment Methods', 'fflcommerce'); ?></h3>

	<?php do_action('fflcommerce_checkout_payment_methods'); ?>

</form>

<?php do_action('after_checkout_form'); ?>