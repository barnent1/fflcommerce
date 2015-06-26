<?php

require_once(FFLCOMMERCE_DIR.'/classes/fflcommerce_user.class.php');

function fflcommerce_admin_user_profile(WP_User $user){
	$customer = new fflcommerce_user($user->ID);

	wp_enqueue_script('admin', 'fflcommerce-select2', FFLCOMMERCE_URL.'/assets/js/select2.min.js', array('jquery'));
	wp_enqueue_style('admin', 'fflcommerce-select2', FFLCOMMERCE_URL.'/assets/css/select2.css');

	fflcommerce_render('admin/user-profile', array(
		'user' => $user,
		'customer' => $customer,
	));
}

function fflcommerce_admin_user_profile_update($id){
	$customer = new fflcommerce_user($id);
	@list($_POST['fflcommerce']['billing_country'], $_POST['fflcommerce']['billing_state']) = explode(':', $_POST['fflcommerce']['billing_country']);
	@list($_POST['fflcommerce']['shipping_country'], $_POST['fflcommerce']['shipping_state']) = explode(':', $_POST['fflcommerce']['shipping_country']);
	$customer->populate($_POST['fflcommerce']);
	$customer->save();
}

add_action('edit_user_profile', 'fflcommerce_admin_user_profile');
add_action('show_user_profile', 'fflcommerce_admin_user_profile');

add_action('personal_options_update', 'fflcommerce_admin_user_profile_update');
add_action('edit_user_profile_update', 'fflcommerce_admin_user_profile_update');
