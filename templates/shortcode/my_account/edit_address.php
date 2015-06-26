<?php
/**
 * @var $url string URL for the form.
 * @var $load_address string Address being edited.
 * @var $address array List of address fields.
 * @var $account_url string URL to My Account page.
 */
?>
<form id="address" method="post"
      action="<?php echo esc_url($url); ?>">
	<h3>
		<?php if ($load_address == 'billing'): { ?>
			<?php _e('Billing Address', 'fflcommerce'); ?>
		<?php } else: { ?>
			<?php _e('Shipping Address', 'fflcommerce'); ?>
		<?php } endif; ?>
	</h3>
	<?php foreach ($address as $field): { ?>
		<?php fflcommerce_customer::address_form_field($field); ?>
	<?php } endforeach; ?>
	<?php fflcommerce::nonce_field('edit_address'); ?>
	<input type="submit" class="button" name="save_address" value="<?php _e('Save Address', 'fflcommerce'); ?>" />
	<a class="button-alt" href="<?php echo $account_url; ?>"><?php _e('Go back to My Account', 'fflcommerce'); ?></a>
</form>
