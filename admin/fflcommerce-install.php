<?php
/**
 * Plugin install script which adds default pages, taxonomies, and database tables
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Admin
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 * 
 */

/**
 * Install FFL Commerce
 *
 * Calls each function to install bits, and clears the cron jobs and rewrite rules
 *
 * @since 		1.0
 */

function install_fflcommerce( $network_wide = false ) {
	/** @var $wpdb \wpdb */
	global $wpdb;

	if ( $network_wide ) {
		$old_blog = $wpdb->blogid;
		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			_install_fflcommerce();
		}
		switch_to_blog($old_blog);
		return;
	} else {
		_install_fflcommerce();
	}

}

function _install_fflcommerce(){
	fflcommerce_populate_options();
	fflcommerce_create_emails();

	if(!get_option('fflcommerce_db_version')){

		fflcommerce_tables_install(); /* we need tables installed first to eliminate installation errors */

		fflcommerce_create_pages();

		fflcommerce_post_type();
		fflcommerce_default_taxonomies();

		// Clear cron
		wp_clear_scheduled_hook('fflcommerce_cron_pending_orders');

		// Flush Rules
		flush_rewrite_rules(false);

		// Update version
		update_site_option("fflcommerce_db_version", FFLCOMMERCE_DB_VERSION);
	}
}

function fflcommerce_populate_options(){
	$defaults = array(
		'fflcommerce_default_country' => 'US',
		'fflcommerce_currency' => 'USD',
		'fflcommerce_allowed_countries' => 'all',
		'fflcommerce_specific_allowed_countries' => '',
		'fflcommerce_demo_store' => 'no',
		'fflcommerce_company_name' => '',
		'fflcommerce_tax_number' => '',
		'fflcommerce_address_1' => '',
		'fflcommerce_address_2' => '',
		'fflcommerce_company_phone' => '',
		'fflcommerce_company_email' => '',
		'fflcommerce_prepend_shop_page_to_urls' => 'no',
		'fflcommerce_prepend_shop_page_to_product' => 'no',
		'fflcommerce_prepend_category_to_product' => 'no',
		'fflcommerce_product_category_slug' => _x('product-category', 'slug', 'fflcommerce'),
		'fflcommerce_product_tag_slug' => _x('product-tag', 'slug', 'fflcommerce'),
		'fflcommerce_email' => get_option('admin_email'),
		'fflcommerce_cart_shows_shop_button' => 'yes',
		'fflcommerce_redirect_add_to_cart' => 'same_page',
		'fflcommerce_reset_pending_orders' => 'no',
		'fflcommerce_complete_processing_orders' => 'no',
		'fflcommerce_downloads_require_login' => 'no',
		'fflcommerce_disable_css' => 'no',
		'fflcommerce_frontend_with_theme_css' => 'no',
		'fflcommerce_disable_fancybox' => 'no',
		'fflcommerce_enable_postcode_validating' => 'no',
		'fflcommerce_verify_checkout_info_message' => 'yes',
		'fflcommerce_eu_vat_reduction_message' => 'yes',
		'fflcommerce_enable_guest_checkout' => 'yes',
		'fflcommerce_enable_guest_login' => 'yes',
		'fflcommerce_enable_signup_form' => 'yes',
		'fflcommerce_force_ssl_checkout' => 'no',
		'fflcommerce_sharethis' => '',
		'fflcommerce_ga_id' => '',
		'fflcommerce_ga_ecommerce_tracking_enabled' => 'no',
		'fflcommerce_catalog_product_button' => 'add',
		'fflcommerce_catalog_sort_orderby' => 'post_date',
		'fflcommerce_catalog_sort_direction' => 'asc',
		'fflcommerce_catalog_columns' => '3',
		'fflcommerce_catalog_per_page' => '12',
		'fflcommerce_currency_pos' => 'left',
		'fflcommerce_price_thousand_sep' => ',',
		'fflcommerce_price_decimal_sep' => '.',
		'fflcommerce_price_num_decimals' => '2',
		'fflcommerce_use_wordpress_tiny_crop' => 'no',
		'fflcommerce_use_wordpress_thumbnail_crop' => 'no',
		'fflcommerce_use_wordpress_catalog_crop' => 'no',
		'fflcommerce_use_wordpress_featured_crop' => 'no',
		'fflcommerce_shop_tiny_w' => 36,
		'fflcommerce_shop_tiny_h' => 36,
		'fflcommerce_shop_thumbnail_w' => 90,
		'fflcommerce_shop_thumbnail_h' => 90,
		'fflcommerce_shop_small_w' => 150,
		'fflcommerce_shop_small_h' => 150,
		'fflcommerce_shop_large_w' => 300,
		'fflcommerce_shop_large_h' => 300,
		'fflcommerce_enable_sku' => 'yes',
		'fflcommerce_enable_weight' => 'yes',
		'fflcommerce_weight_unit' => 'lb',
		'fflcommerce_enable_dimensions' => 'yes',
		'fflcommerce_dimension_unit' => 'in',
		'fflcommerce_product_thumbnail_columns' => '3',
		'fflcommerce_enable_related_products' => 'yes',
		'fflcommerce_manage_stock' => 'yes',
		'fflcommerce_show_stock' => 'yes',
		'fflcommerce_notify_low_stock' => 'yes',
		'fflcommerce_notify_low_stock_amount' => '2',
		'fflcommerce_notify_no_stock' => 'yes',
		'fflcommerce_notify_no_stock_amount' => '0',
		'fflcommerce_hide_no_stock_product' => 'no',
		'fflcommerce_calc_taxes' => 'yes',
		'fflcommerce_tax_after_coupon' => 'yes',
		'fflcommerce_prices_include_tax' => 'no',
		'fflcommerce_tax_classes' => sprintf(__('Reduced Rate%sZero Rate', 'fflcommerce'), PHP_EOL),
		'fflcommerce_tax_rates' => '',
		'fflcommerce_calc_shipping' => 'yes',
		'fflcommerce_enable_shipping_calc' => 'yes',
		'fflcommerce_ship_to_billing_address_only' => 'no',
		'fflcommerce_show_checkout_shipping_fields' => 'no',
		'fflcommerce_default_gateway' => 'check',
		'fflcommerce_error_disappear_time' => 8000,
		'fflcommerce_message_disappear_time' => 4000,
		'fflcommerce_enable_html_emails' => 'yes',
	);

	$options = FFLCommerce_Base::get_options();
	foreach($defaults as $option => $value){
		if(!$options->exists($option)){
			$options->add($option, $value);
		}
	}
}

/**
 * Default options
 *
 * Sets up the default options used on the settings page
 *
 * @deprecated -- no longer required for FFL Commerce 1.2 (-JAP-)
 *
 * @since 		1.0
 */
function fflcommerce_default_options() {
	global $fflcommerce_options_settings;

	foreach ($fflcommerce_options_settings as $value) :

        if (isset($value['std'])) :

				if ($value['type']=='image_size') :

					update_option( $value['id'].'_w', $value['std'] );
					update_option( $value['id'].'_h', $value['std'] );

				else :

					update_option( $value['id'], $value['std'] );

				endif;

		endif;

    endforeach;

    update_option( 'fflcommerce_shop_slug', 'shop' );
}

/**
 * Create pages
 *
 * Creates pages that the plugin relies on, storing page id's in options.
 *
 * @since 		0.9.9.1
 */
function fflcommerce_create_pages() {

    $fflcommerce_options = FFLCommerce_Base::get_options();

	// start out with basic page parameters, modify as we go
	$page_data = array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => '',
		'post_title'     => __('Shop', 'fflcommerce'),
		'post_content'   => '',
		'comment_status' => 'closed'
	);
	fflcommerce_create_single_page( 'shop', 'fflcommerce_shop_page_id', $page_data );

	$shop_page = $fflcommerce_options->get( 'fflcommerce_shop_page_id' );
	$fflcommerce_options->set( 'fflcommerce_shop_redirect_page_id' , $shop_page );

	$page_data['post_title']   = __('Cart', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_cart]';
	fflcommerce_create_single_page( 'cart', 'fflcommerce_cart_page_id', $page_data );

	$page_data['post_title']   = __('Track your order', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_order_tracking]';
	fflcommerce_create_single_page( 'order-tracking', 'fflcommerce_track_order_page_id', $page_data );

	$page_data['post_title']   = __('My Account', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_my_account]';
	fflcommerce_create_single_page( 'my-account', 'fflcommerce_myaccount_page_id', $page_data );

	$page_data['post_title']   = __('Edit My Address', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_edit_address]';
	$page_data['post_parent']  = fflcommerce_get_page_id('myaccount');
	fflcommerce_create_single_page( 'edit-address', 'fflcommerce_edit_address_page_id', $page_data );

	$page_data['post_title']   = __('Change Password', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_change_password]';
	$page_data['post_parent']  = fflcommerce_get_page_id('myaccount');
	fflcommerce_create_single_page( 'change-password', 'fflcommerce_change_password_page_id', $page_data );

	$page_data['post_title']   = __('View Order', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_view_order]';
	$page_data['post_parent']  = fflcommerce_get_page_id('myaccount');
	fflcommerce_create_single_page( 'view-order', 'fflcommerce_view_order_page_id', $page_data );

	$page_data['post_title']   = __('Checkout', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_checkout]';
	unset( $page_data['post_parent'] );
	fflcommerce_create_single_page( 'checkout', 'fflcommerce_checkout_page_id', $page_data );

	$page_data['post_title']   = __('Checkout &rarr; Pay', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_pay]';
	$page_data['post_parent']  = fflcommerce_get_page_id('checkout');
	fflcommerce_create_single_page( 'pay', 'fflcommerce_pay_page_id', $page_data );

	$page_data['post_title']   = __('Thank you', 'fflcommerce');
	$page_data['post_content'] = '[fflcommerce_thankyou]';
	$page_data['post_parent']  = fflcommerce_get_page_id('checkout');
	fflcommerce_create_single_page( 'thanks', 'fflcommerce_thanks_page_id', $page_data );

}

function fflcommerce_create_emails(){
	$args = array(
		'post_type' => 'shop_email',
		'post_status' => 'publish',
	);

	$emails_array = get_posts($args);
	if(empty($emails_array)){
		do_action('fflcommerce_install_emails');
	}
}

/**
 * Install a single FFL Commerce Page
 *
 * @param string $page_slug - is the slug for the page to create (shop|cart|thank-you|etc)
 * @param string $page_option - the database options entry for page ID storage
 * @param array $page_data - preset default parameters for creating the page - this will finish the slug
 *
 * @since 1.3
 */
function fflcommerce_create_single_page( $page_slug, $page_option, $page_data ) {

	global $wpdb;
    $fflcommerce_options = FFLCommerce_Base::get_options();

	$slug    = esc_sql( _x( $page_slug, 'page_slug', 'fflcommerce' ) );
	$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1", $slug ) );

	if ( ! $page_id ) {
		$page_data['post_name'] = $slug;
		$page_id = wp_insert_post( $page_data );
	}

	$fflcommerce_options->set( $page_option, $page_id );

	$ids = $fflcommerce_options->get( 'fflcommerce_page-ids' );
	$ids[] = $page_id;

	$fflcommerce_options->set( 'fflcommerce_page-ids', $ids );

}

/**
 * Table Install
 *
 * Sets up the database tables which the plugin needs to function.
 *
 * @since 		1.0
 */
function fflcommerce_tables_install() {
	global $wpdb;

	if((!defined('DIEONDBERROR'))&&(is_multisite())){define('DIEONDBERROR',true);}
	$wpdb->show_errors();

    $collate = '';
    if($wpdb->has_cap( 'collation' )) {
		if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "fflcommerce_attribute_taxonomies" ." (
        `attribute_id` 			mediumint(9) NOT NULL AUTO_INCREMENT,
        `attribute_name`		varchar(200) NOT NULL,
		`attribute_label`		longtext NULL,
        `attribute_type`		varchar(200) NOT NULL,
        PRIMARY KEY id (`attribute_id`)) $collate;";
    if ( $wpdb->query($sql) === false ) {
		$wpdb->print_error();
		wp_die(__('We were not able to create a FFL Commerce database table during installation! (fflcommerce_attribute_taxonomies)','fflcommerce'));
	}

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "fflcommerce_downloadable_product_permissions" ." (
        `product_id` 			mediumint(9) NOT NULL,
        `user_email`			varchar(200) NOT NULL,
        `user_id`				mediumint(9) NULL,
        `order_key`				varchar(200) NOT NULL,
        `downloads_remaining`	varchar(9) NULL,
        PRIMARY KEY id (`product_id`, `order_key`)) $collate;";
    if ( $wpdb->query($sql) === false ) {
		$wpdb->print_error();
		wp_die(__('We were not able to create a FFL Commerce database table during installation! (fflcommerce_downloadable_product_permissions)','fflcommerce'));
	}

    $sql = "CREATE TABLE IF NOT EXISTS ". $wpdb->prefix . "fflcommerce_termmeta" ." (
		`meta_id` 				bigint(20) NOT NULL AUTO_INCREMENT,
      	`fflcommerce_term_id` 		bigint(20) NOT NULL,
      	`meta_key` 				varchar(255) NULL,
      	`meta_value` 			longtext NULL,
      	PRIMARY KEY id (`meta_id`)) $collate;";
    if ( $wpdb->query($sql) === false ) {
		$wpdb->print_error();
		wp_die(__('We were not able to create a fflcommerce database table during installation! (fflcommerce_termmeta)','fflcommerce'));
	}

    $wpdb->hide_errors();

}

/**
 * Default taxonomies
 *
 * Adds the default terms for taxonomies - product types and order statuses. Modify at your own risk.
 *
 * @since 		1.0
 */
function fflcommerce_default_taxonomies() {

	$product_types = array(
		'simple',
		'external',
		'grouped',
		'configurable',
		'downloadable',
		'virtual'
	);

	foreach($product_types as $type) {
		if (!$type_id = get_term_by( 'slug', sanitize_title($type), 'product_type')) {
			wp_insert_term($type, 'product_type');
		}
	}

	$order_status = array(
		'new',
		'pending',
		'on-hold',
		'waiting-for-payment',
		'processing',
		'completed',
		'refunded',
		'cancelled'
	);

	foreach($order_status as $status) {
		if (!$status_id = get_term_by( 'slug', sanitize_title($status), 'shop_order_status')) {
			wp_insert_term($status, 'shop_order_status');
		}
	}

}
