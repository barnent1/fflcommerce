<?php
/**
 * @var $user WP_User User instance.
 * @var $customer fflcommerce_user Customer data.
 */
?>
<h2><?php _e('FFL Commerce profile', 'fflcommerce'); ?></h2>
<table class="form-table" style="width: 50%; float: left;">
	<caption><?php _e('Billing address', 'fflcommerce'); ?></caption>
	<tbody>
	<tr>
		<th scope="row"><?php _e('First name', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_first_name]" value="<?php echo $customer->getBillingFirstName(); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Last name', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_last_name]" value="<?php echo $customer->getBillingLastName(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Company', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_company]" value="<?php echo $customer->getBillingCompany(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 1', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_address_1]" value="<?php echo $customer->getBillingAddress1(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 2', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_address_2]" value="<?php echo $customer->getBillingAddress2(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('City', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_city]" value="<?php echo $customer->getBillingCity(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Postcode', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_postcode]" value="<?php echo $customer->getBillingPostcode(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Country', 'fflcommerce'); ?></th>
		<td><?php fflcommerce_render('admin/user-profile/country_dropdown', array(
			'country' => $customer->getBillingCountry(),
			'state' => $customer->getBillingState(),
			'name' => 'billing_country',
		)); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Email', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_email]" value="<?php echo $customer->getBillingEmail(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Phone', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[billing_phone]" value="<?php echo $customer->getBillingPhone(); ?>" class="regular-text" /> </td>
	</tr>
	</tbody>
</table>
<table class="form-table" style="width: 50%; float: left; clear: none;">
	<caption><?php _e('Shipping address', 'fflcommerce'); ?></caption>
	<tbody>
	<tr>
		<th scope="row"><?php _e('First name', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_first_name]" value="<?php echo $customer->getShippingFirstName(); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Last name', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_last_name]" value="<?php echo $customer->getShippingLastName(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Company', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_company]" value="<?php echo $customer->getShippingCompany(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 1', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_address_1]" value="<?php echo $customer->getShippingAddress1(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 2', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_address_2]" value="<?php echo $customer->getShippingAddress2(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('City', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_city]" value="<?php echo $customer->getShippingCity(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Postcode', 'fflcommerce'); ?></th>
		<td><input type="text" name="fflcommerce[shipping_postcode]" value="<?php echo $customer->getShippingPostcode(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Country', 'fflcommerce'); ?></th>
		<td><?php fflcommerce_render('admin/user-profile/country_dropdown', array(
				'country' => $customer->getShippingCountry(),
				'state' => $customer->getShippingState(),
				'name' => 'shipping_country',
			)); ?></td>
	</tr>
	</tbody>
</table>
<span style="clear: both; display: block;"></span>
