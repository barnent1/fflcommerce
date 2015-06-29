<?php
/*
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

$options = FFLCommerce_Base::get_options(); ?>
<div id="order_review">
	<table class="shop_table">
		<thead>
		<tr>
			<th><?php _e('Product', 'fflcommerce'); ?></th>
			<th><?php _e('Qty', 'fflcommerce'); ?></th>
			<th><?php _e('Totals', 'fflcommerce'); ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<?php $price_label = fflcommerce_cart::show_retail_price() ? __('Retail Price', 'fflcommerce') : __('Subtotal', 'fflcommerce'); ?>
			<td colspan="2"><?php echo $price_label; ?></td>
			<td class="cart-row-subtotal"><?php echo fflcommerce_cart::get_cart_subtotal(true, false, true); ?></td>
		</tr>

		<?php fflcommerce_checkout::render_shipping_dropdown(); ?>

		<?php if (fflcommerce_cart::show_retail_price() && FFLCommerce_Base::get_options()->get('fflcommerce_prices_include_tax') == 'no') : ?>
			<tr>
				<td colspan="2"><?php _e('Subtotal', 'fflcommerce'); ?></td>
				<td><?php echo fflcommerce_cart::get_cart_subtotal(true, true); ?></td>
			</tr>
		<?php elseif (fflcommerce_cart::show_retail_price()): ?>
			<tr>
				<td colspan="2"><?php _e('Subtotal', 'fflcommerce'); ?></td>
				<?php
				$price = fflcommerce_cart::$cart_contents_total_ex_tax + fflcommerce_cart::$shipping_total;
				$price = fflcommerce_price($price, array('ex_tax_label' => 1));
				?>
				<td><?php echo $price; ?></td>
			</tr>
		<?php endif; ?>

		<?php if (fflcommerce_cart::tax_after_coupon()): ?>
			<tr class="discount">
				<td colspan="2"><?php _e('Discount', 'fflcommerce'); ?></td>
				<td>-<?php echo fflcommerce_cart::get_total_discount(); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($options->get('fflcommerce_calc_taxes') == 'yes'):
			foreach (fflcommerce_cart::get_applied_tax_classes() as $tax_class):
				if (fflcommerce_cart::get_tax_for_display($tax_class)) : ?>
					<tr>
						<td colspan="2"><?php echo fflcommerce_cart::get_tax_for_display($tax_class); ?></td>
						<td><?php echo fflcommerce_cart::get_tax_amount($tax_class) ?></td>
					</tr>
				<?php endif;
			endforeach;
		endif; ?>

		<?php do_action('fflcommerce_after_review_order_items'); ?>

		<?php if (!fflcommerce_cart::tax_after_coupon() && fflcommerce_cart::get_total_discount()) : ?>
			<tr class="discount">
				<td colspan="2"><?php _e('Discount', 'fflcommerce'); ?></td>
				<td>-<?php echo fflcommerce_cart::get_total_discount(); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2"><strong><?php _e('Grand Total', 'fflcommerce'); ?></strong></td>
			<td><strong><?php echo fflcommerce_cart::get_total(); ?></strong></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach (fflcommerce_cart::$cart_contents as $item_id => $values) :
			/** @var fflcommerce_product $product */
			$product = $values['data'];
			if ($product->exists() && $values['quantity'] > 0) :
				$variation = fflcommerce_cart::get_item_data($values);
				$customization = '';
				$custom_products = (array)fflcommerce_session::instance()->customized_products;
				$product_id = !empty($values['variation_id']) ? $values['variation_id'] : $values['product_id'];
				$custom = isset($custom_products[$product_id]) ? $custom_products[$product_id] : ''; ?>
				<tr>
					<td class="product-name">
						<?php echo $product->get_title().$variation;
						if (!empty($custom)) :
							$label = apply_filters('fflcommerce_customized_product_label', __(' Personal: ', 'fflcommerce')); ?>
							<dl class="customization">
								<dt class="customized_product_label">
									<?php echo $label; ?>
								</dt>
								<dd class="customized_product">
									<?php echo $custom; ?>
								</dd>
							</dl>
						<?php endif; ?>
					</td>
					<td><?php echo $values['quantity']; ?></td>
					<td>
						<?php
						echo fflcommerce_price($product->get_defined_price() * $values['quantity'], array('ex_tax_label' => Jigoshop_Base::get_options()->get('fflcommerce_show_prices_with_tax') == 'yes' ?  2 : 1));
						?>
					</td>
				</tr>
			<?php endif;
		endforeach;
		?>
		</tbody>
	</table>

	<?php $coupons = fflcommerce_cart::get_coupons();?>
	<table>
		<tr>
			<td colspan="6" class="actions">
				<?php if (JS_Coupons::has_coupons()): ?>
					<div class="coupon">
						<label for="coupon_code"><?php _e('Coupon', 'fflcommerce'); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" />
						<input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'fflcommerce'); ?>" />
					</div>
				<?php endif; ?>
			</td>
			<?php if (count($coupons)): ?>
				<td class="applied-coupons">
					<div>
						<span class="applied-coupons-label"><?php _e('Applied Coupons: ', 'fflcommerce'); ?></span>
						<?php foreach ($coupons as $code): ?>
							<a href="?unset_coupon=<?php echo $code; ?>" id="<?php echo $code; ?>" class="applied-coupons-values"><?php echo $code; ?>
								<span class="close">&times;</span>
							</a>
						<?php endforeach; ?>
					</div>
				</td>
			<?php endif; ?>
		</tr>
	</table>
</div>
