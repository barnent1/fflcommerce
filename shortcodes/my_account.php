<?php
/**
 * My Account shortcode
 * 
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Customer
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 * 
 */

function get_fflcommerce_my_account($attributes)
{
	return fflcommerce_shortcode_wrapper('fflcommerce_my_account', $attributes);
}

function fflcommerce_my_account($attributes)
{
	global $current_user;
	$options = FFLCommerce_Base::get_options();

	$attributes = shortcode_atts(array(
		'recent_orders' => 5
	), $attributes);

	$recent_orders = ('all' == $attributes['recent_orders']) ? -1 : $attributes['recent_orders'];
	get_currentuserinfo();

	fflcommerce_render('shortcode/my_account/my_account', array(
		'current_user' => $current_user,
		'options' => $options,
		'recent_orders' => $recent_orders,
	));
}

function get_fflcommerce_edit_address()
{
	return fflcommerce_shortcode_wrapper('fflcommerce_edit_address');
}

function fflcommerce_get_address_to_edit()
{
	$address = 'billing';
	if (isset($_GET['address']) && in_array($_GET['address'], array('billing', 'shipping'))) {
		$address = $_GET['address'];
	}

	return $address;
}

function fflcommerce_get_address_fields($load_address, $user_id)
{
	$address = array(
		array(
			'name' => $load_address.'_first_name',
			'label' => __('First Name', 'fflcommerce'),
			'placeholder' => __('First Name', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_first_name', true)
		),
		array(
			'name' => $load_address.'_last_name',
			'label' => __('Last Name', 'fflcommerce'),
			'placeholder' => __('Last Name', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-last columned'),
			'value' => get_user_meta($user_id, $load_address.'_last_name', true)
		),
		array(
			'name' => $load_address.'_company',
			'label' => __('Company', 'fflcommerce'),
			'placeholder' => __('Company', 'fflcommerce'),
			'class' => array('columned full-row clear'),
			'value' => get_user_meta($user_id, $load_address.'_company_name', true)
		),
		array(
			'name' => $load_address.'_address_1',
			'label' => __('Address', 'fflcommerce'),
			'placeholder' => __('Address 1', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_address_1', true)
		),
		array(
			'name' => $load_address.'_address_2',
			'label' => __('Address 2', 'fflcommerce'),
			'placeholder' => __('Address 2', 'fflcommerce'),
			'class' => array('form-row-last'),
			'label_class' => array('hidden'),
			'value' => get_user_meta($user_id, $load_address.'_address_2', true)
		),
		array(
			'name' => $load_address.'_city',
			'label' => __('City', 'fflcommerce'),
			'placeholder' => __('City', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_city', true)
		),
		array(
			'type' => 'postcode',
			'validate' => 'postcode',
			'format' => 'postcode',
			'name' => $load_address.'_postcode',
			'label' => __('Postcode', 'fflcommerce'),
			'placeholder' => __('Postcode', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-last'),
			'value' => get_user_meta($user_id, $load_address.'_postcode', true)
		),
		array(
			'type' => 'country',
			'name' => $load_address.'_country',
			'label' => __('Country', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-first'),
			'rel' => $load_address.'_state',
			'value' => get_user_meta($user_id, $load_address.'_country', true)
		),
		array(
			'type' => 'state',
			'name' => $load_address.'_state',
			'label' => __('State/Province', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-last'),
			'rel' => $load_address.'_country',
			'value' => get_user_meta($user_id, $load_address.'_state', true)
		),
		array(
			'name' => $load_address.'_email',
			'validate' => 'email',
			'label' => __('Email Address', 'fflcommerce'),
			'placeholder' => __('you@yourdomain.com', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-first'),
			'value' => get_user_meta($user_id, $load_address.'_email', true)
		),
		array(
			'name' => $load_address.'_phone',
			'validate' => 'phone',
			'label' => __('Phone', 'fflcommerce'),
			'placeholder' => __('Phone number', 'fflcommerce'),
			'required' => true,
			'class' => array('form-row-last'),
			'value' => get_user_meta($user_id, $load_address.'_phone', true)
		)
	);

	return apply_filters('fflcommerce_customer_account_address_fields', $address);
}

function fflcommerce_edit_address()
{
	$account_url = get_permalink(fflcommerce_get_page_id(FFLCOMMERCE_MY_ACCOUNT));
	$user_id = get_current_user_id();
	$load_address = fflcommerce_get_address_to_edit();
	$address = fflcommerce_get_address_fields($load_address, $user_id);

	if (isset($_POST['save_address']) && fflcommerce::verify_nonce(FFLCOMMERCE_EDIT_ADDRESS)) {
		if ($user_id > 0) {
			foreach ($address as &$field) {
				if (isset($_POST[$field['name']])) {
					$field['value'] = fflcommerce_clean($_POST[$field['name']]);
					update_user_meta($user_id, $field['name'], $field['value']);
				}
			}

			do_action('fflcommerce_user_edit_address', $user_id, $address);
		}
	}

	fflcommerce_render('shortcode/my_account/edit_address', array(
		'url' => add_query_arg('address', $load_address,
			apply_filters('fflcommerce_get_edit_address_page_id', get_permalink(fflcommerce_get_page_id(FFLCOMMERCE_EDIT_ADDRESS)))),
		'account_url' => $account_url,
		'load_address' => $load_address,
		'address' => $address,
	));
}

function get_fflcommerce_change_password()
{
	return fflcommerce_shortcode_wrapper('fflcommerce_change_password');
}

function fflcommerce_change_password()
{
	fflcommerce_render('shortcode/my_account/change_password', array());
}

function get_fflcommerce_view_order()
{
	return fflcommerce_shortcode_wrapper('fflcommerce_view_order');
}

function fflcommerce_view_order()
{
	$options = FFLCommerce_Base::get_options();
	$order = new fflcommerce_order($_GET['order']);

	fflcommerce_render('shortcode/my_account/view_order', array(
		'order' => $order,
		'options' => $options,
	));
}

add_action('template_redirect', function (){
	$isViewOrder = is_fflcommerce_single_page(FFLCOMMERCE_VIEW_ORDER);
	$isEditAddress = is_fflcommerce_single_page(FFLCOMMERCE_EDIT_ADDRESS);
	$isChangePassword = is_fflcommerce_single_page(FFLCOMMERCE_CHANGE_PASSWORD);

	if (($isViewOrder || $isEditAddress || $isChangePassword) && !is_user_logged_in()) {
		wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id(FFLCOMMERCE_MY_ACCOUNT))));
		exit;
	}

	if ($isViewOrder) {
		if (!isset($_GET['order'])) {
			wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id('myaccount'))));
			exit;
		}
		$order = new fflcommerce_order($_GET['order']);

		if ($order->user_id != get_current_user_id()) {
			wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id('myaccount'))));
			exit;
		}
	}

	if ($isChangePassword){
		$user_id = get_current_user_id();
		if ($_POST && $user_id > 0 && fflcommerce::verify_nonce('change_password')) {
			if ($_POST['password-1'] && $_POST['password-2']) {
				if ($_POST['password-1'] == $_POST['password-2']) {
					wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['password-1']));
					fflcommerce::add_message(__('Password changed successfully.', 'fflcommerce'));
					wp_safe_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id(FFLCOMMERCE_MY_ACCOUNT))));
					exit;
				} else {
					fflcommerce::add_error(__('Passwords do not match.', 'fflcommerce'));
				}
			} else {
				fflcommerce::add_error(__('Please enter your password.', 'fflcommerce'));
			}
		}
	}
});
