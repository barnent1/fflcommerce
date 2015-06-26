<?php
/**
 * $fflcommerce_options_settings variable contains all the options used on the FFL Commerce settings page
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFLCommerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Admin
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 * 
 */





/***********************    NOTE:   AS OF FFL Commerce 1.3, THIS FILE IS NO LONGER IN USE       ************************/
_deprecated_file( __FILE__, '1.3', 'FFLCommerce_Base::get_options()');





/**
 * options_settings
 *
 * This variable contains all the options used on the FFL Commerce settings page
 *
 * @since 		1.0
 * @category 	Admin
 * @usedby 		fflcommerce_settings(), fflcommerce_default_options()
 */
global $fflcommerce_options_settings;

$fflcommerce_options_settings = apply_filters('fflcommerce_options_settings', array(

	array( 'type'        => 'tab', 'tabname'                                 => __('General', 'fflcommerce') ),

	array( 'name'        => __('General Options', 'fflcommerce'), 'type'        => 'title', 'desc' => '' ),

	array(
		'name'           => __('Send FFL Commerce emails from','fflcommerce'),
		'desc'           => '',
		'tip'            => __('The email used to send all FFL Commerce related emails, such as order confirmations and notices.','fflcommerce'),
		'id'             => 'fflcommerce_email',
		'css'            => 'width:250px;',
		'type'           => 'text',
		'std'            => get_option('admin_email')
	),

	array(
		'name'           => __('Base Country/Region','fflcommerce'),
		'desc'           => '',
		'tip'            => __('This is the base country for your business. Tax rates will be based on this country.','fflcommerce'),
		'id'             => 'fflcommerce_default_country',
		'css'            => '',
		'std'            => 'GB',
		'type'           => 'single_select_country'
	),

	array(
		'name'           => __('Allowed Countries','fflcommerce'),
		'desc'           => '',
		'tip'            => __('These are countries that you are willing to ship to.','fflcommerce'),
		'id'             => 'fflcommerce_allowed_countries',
		'css'            => 'min-width:100px;',
		'std'            => 'all',
		'type'           => 'select',
		'options'        => array(
			'all'        => __('All Countries', 'fflcommerce'),
			'specific'   => __('Specific Countries', 'fflcommerce')
		)
	),

	array(
		'name'           => __('Specific Countries','fflcommerce'),
		'desc'           => '',
		'tip'            => '',
		'id'             => 'fflcommerce_specific_allowed_countries',
		'css'            => '',
		'std'            => '',
		'type'           => 'multi_select_countries'
	),

	array(
		'name'           => __('After adding product to cart','fflcommerce'),
		'desc'           => '',
		'tip'            => __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.','fflcommerce'),
		'id'             => 'fflcommerce_redirect_add_to_cart',
		'css'            => 'min-width:100px;',
		'std'            => 'same_page',
		'type'           => 'select',
		'options'        => array(
			'same_page'  => __('Stay on the same page', 'fflcommerce'),
			'to_checkout'=> __('Redirect to Checkout', 'fflcommerce'),
			'to_cart'    => __('Redirect to Cart', 'fflcommerce'),
		)
	),

	array(
		'name'           => __('Downloads','fflcommerce'),
		'desc'           => __('Enforce login for downloads','fflcommerce'),
		'tip'            => __('If a guest purchases a download, the guest can still download a link without logging in. We recommend disabling guest purchases if you enable this option.','fflcommerce'),
		'id'             => 'fflcommerce_downloads_require_login',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	/* Styles and scripts */

	array(
		'name'           => __('Styles and scripts','fflcommerce'),
		'desc'           => __('Demo store banner','fflcommerce'),
		'tip'            => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.','fflcommerce'),
		'id'             => 'fflcommerce_demo_store',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Disable FFL Commerce frontend.css','fflcommerce'),
		'tip'            => __('Useful if you want to disable FFL Commerce styles and theme it yourself via your theme.','fflcommerce'),
		'id'             => 'fflcommerce_disable_css',
		'std'            => 'no',
		'type'           => 'checkbox'
	),

	array(
		'desc'           => __('Disable bundled PrettyPhoto','fflcommerce'),
		'tip'            => __('Useful if or one of your plugin already loads the PrettyPhoto script and css. But be careful, fflcommerce will still try to open product images using Fancybox.','fflcommerce'),
		'id'             => 'fflcommerce_disable_fancybox',
		'std'            => 'no',
		'type'           => 'checkbox'
	),

	/* Checkout page */

	array(
		'name'           => __('Checkout page','fflcommerce'),
		'desc'           => __('Allow guest purchases','fflcommerce'),
		'tip'            => __('Setting this to Yes will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.','fflcommerce'),
		'id'             => 'fflcommerce_enable_guest_checkout',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Show login form','fflcommerce'),
		'id'             => 'fflcommerce_enable_guest_login',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Allow registration','fflcommerce'),
		'id'             => 'fflcommerce_enable_signup_form',
		'std'            => 'yes',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Force SSL on checkout','fflcommerce'),
		'tip'            => __('Forcing SSL is recommended. This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.','fflcommerce'),
		'id'             => 'fflcommerce_force_ssl_checkout',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'name'           => __('Beta testing', 'fflcommerce'),
		'desc'           => __('Use beta versions','fflcommerce'),
		'tip'            => __('Only beta plugin updates will be shown for FFL Commerce. Beta updates will display normally in the Wordpress plugin manager.','fflcommerce'),
		'id'             => 'fflcommerce_use_beta_version',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Check for update now','fflcommerce'),
		'tip'            => __('Manually check if a beta update is available.','fflcommerce'),
		'id'             => 'fflcommerce_check_beta_now',
		'type'           => 'button',
		'href'           => is_multisite() ? admin_url().'network/' : '' . 'admin.php?page=fflcommerce_settings&amp;action=fflcommerce_beta_check&amp;_wpnonce='.wp_create_nonce('fflcommerce_check_beta_'.get_current_user_id().'_wpnonce')
	),

	array( 'name'        => __('Invoicing', 'fflcommerce'), 'type'              => 'title', 'desc' => '' ),

	array(
		'name'           => __('Company Name','fflcommerce'),
		'desc'           => '',
		'tip'            => __('Setting your company name will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_company_name',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Tax Registration Number','fflcommerce'),
		'desc'           => 'Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>',
		'tip'            => __('Setting your tax number will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_tax_number',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Address Line1','fflcommerce'),
		'desc'           => '',
		'tip'            => __('Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_address_line1',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Address Line2','fflcommerce'),
		'desc'           => '',
		'tip'            => __('If address line1 is not set, address line2 will not display even if you put a value in it. Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_address_line2',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Company Phone','fflcommerce'),
		'desc'           => '',
		'tip'            => __('Setting your company phone number will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_company_phone',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Company Email','fflcommerce'),
		'desc'           => '',
		'tip'            => __('Setting your company email will enable us to print it out on your invoice emails. Leave blank to disable.','fflcommerce'),
		'id'             => 'fflcommerce_company_email',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array( 'name'        => __('Integration', 'fflcommerce'), 'type'            => 'title', 'desc' => '' ),

	array(
		'name'           => __('ShareThis Publisher ID','fflcommerce'),
		'desc'           => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.",'fflcommerce'),
		'tip'            => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.','fflcommerce'),
		'id'             => 'fflcommerce_sharethis',
		'css'            => 'width:300px;',
		'type'           => 'text',
		'std'            => ''
	),

	array(
		'name'           => __('Google Analytics ID', 'fflcommerce'),
		'desc'           => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'fflcommerce'),
		'id'             => 'fflcommerce_ga_id',
		'type'           => 'text',
		'css'            => 'min-width:300px;',
	),

	array(
		'name'           => __('Enable Google eCommerce', 'fflcommerce'),
		'tip'            => __('Add Google Analytics eCommerce tracking code upon successful orders', 'fflcommerce'),
		'desc'           => __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612" target="_TOP">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'fflcommerce'),
		'id'             => 'fflcommerce_ga_ecommerce_tracking_enabled',
		'type'           => 'checkbox',
	),

	array( 'type'        => 'tabend'),

	array( 'type'        => 'tab', 'tabname'                                 => __('Pages', 'fflcommerce') ),

	array( 'name'        => __('Permalinks',      'fflcommerce'), 'type'        => 'title','desc' => '', 'id' => '' ),

	array(
		'name'           => __('Prepend options','fflcommerce'),
		'desc'           => __('Prepend shop categories / tags with base page','fflcommerce'),
		'tip'            => __('This will only apply to tags &amp; categories.<br/>Enabled: http://yoursite.com / product_category / YourCategory<br/>Disabled: http://yoursite.com / base_page / product_category / YourCategory', 'fflcommerce'),
		'id'             => 'fflcommerce_prepend_shop_page_to_urls',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Prepend product permalinks with shop base page','fflcommerce'),
		'id'             => 'fflcommerce_prepend_shop_page_to_product',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'desc'           => __('Prepend product permalinks with product category','fflcommerce'),
		'id'             => 'fflcommerce_prepend_category_to_product',
		'std'            => 'no',
		'type'           => 'checkbox',
	),

	array(
		'name'           => __('Slug variables','fflcommerce'),
		'desc'           => 'Product category slug',
		'tip'            => __('Slug displayed in product category URLs. Leave blank to use default "product-category"', 'fflcommerce'),
		'id'             => 'fflcommerce_product_category_slug',
		'std'            => 'product-category',
		'css'            => 'width:130px;',
		'type'           => 'text',
		'group'          => true
	),

	array(
		'desc'           => __('Product tag slug','fflcommerce'),
		'tip'            => __('Slug displayed in product tag URLs. Leave blank to use default "product-tag"', 'fflcommerce'),
		'id'             => 'fflcommerce_product_tag_slug',
		'std'            => 'product-tag',
		'css'            => 'width:130px;',
		'type'           => 'text',
		'group'          => true
	),

	array( 'name'        => __('Shop page configuration', 'fflcommerce'), 'type' => 'title', 'desc' => '' ),

	array(
		'name'           => __('Cart Page','fflcommerce'),
		'desc'           => __('Shortcode to place on page: <code>[fflcommerce_cart]</code>','fflcommerce'),
		'tip'            => '',
		'id'             => 'fflcommerce_cart_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Checkout Page','fflcommerce'),
		'desc'           => __('Shortcode to place on page: <code>[fflcommerce_checkout]</code>','fflcommerce'),
		'tip'            => '',
		'id'             => 'fflcommerce_checkout_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Pay Page','fflcommerce'),
		'desc'           => __('Shortcode to place on page: <code>[fflcommerce_pay]</code><br/>Default parent page: Checkout','fflcommerce'),
		'tip'            => '',
		'id'             => 'fflcommerce_pay_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('Thanks Page','fflcommerce'),
		'desc'           => __('Shortcode to place on page: <code>[fflcommerce_thankyou]</code><br/>Default parent page: Checkout','fflcommerce'),
		'tip'            => '',
		'id'             => 'fflcommerce_thanks_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'           => __('My Account Page','fflcommerce'),
		'desc'           => __('Shortcode to place on page: <code>[fflcommerce_my_account]</code>','fflcommerce'),
		'tip'            => '',
		'id'             => 'fflcommerce_myaccount_page_id',
		'css'            => 'min-width:50px;',
		'type'           => 'single_select_page',
		'std'            => ''
	),

	array(
		'name'          => __('Edit Address Page','fflcommerce'),
		'desc'          => __('Shortcode to place on page: <code>[fflcommerce_edit_address]</code><br/>Default parent page: My Account','fflcommerce'),
		'tip'           => '',
		'id'            => 'fflcommerce_edit_address_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('View Order Page','fflcommerce'),
		'desc'          => __('Shortcode to place on page: <code>[fflcommerce_view_order]</code><br/>Default parent page: My Account','fflcommerce'),
		'tip'           => '',
		'id'            => 'fflcommerce_view_order_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Change Password Page','fflcommerce'),
		'desc'          => __('Shortcode to place on page: <code>[fflcommerce_change_password]</code><br/>Default parent page: My Account','fflcommerce'),
		'tip'           => '',
		'id'            => 'fflcommerce_change_password_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Track Order Page','fflcommerce'),
		'desc'          => __('Shortcode to place on page: <code>[fflcommerce_order_tracking]</code>','fflcommerce'),
		'tip'           => '',
		'id'            => 'fflcommerce_track_order_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Terms Page', 'fflcommerce'),
		'desc'          => __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'fflcommerce'),
		'tip'           => '',
		'id'            => 'fflcommerce_terms_page_id',
		'css'           => 'min-width:50px;',
		'std'           => '',
		'type'          => 'single_select_page',
		'args'          => 'show_option_none=' . __('None', 'fflcommerce'),
	),

	array( 'type'       => 'tabend'),

	array( 'type'       => 'tab', 'tabname'                         => __('Catalog &amp; Pricing', 'fflcommerce') ),

	array( 'name'       => __('Catalog Options', 'fflcommerce'), 'type' => 'title','desc' => '', 'id' => '' ),


	array(
		'name'          => __('Catalog base page','fflcommerce'),
		'desc'          => '',
		'tip'           => __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.','fflcommerce'),
		'id'            => 'fflcommerce_shop_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),
	array(
		'name'          => __('Shop redirection page','fflcommerce'),
		'desc'          => '',
		'tip'           => __('This will point users to the page you set for buttons like `Return to shop` or `Continue Shopping`.','fflcommerce'),
		'id'            => 'fflcommerce_shop_redirect_page_id',
		'css'           => 'min-width:50px;',
		'type'          => 'single_select_page',
		'std'           => ''
	),

	array(
		'name'          => __('Sort products in catalog by','fflcommerce'),
		'desc'          => '',
		'tip'           => __('Determines the display sort order of products for the Shop, Categories, and Tag pages.','fflcommerce'),
		'id'            => 'fflcommerce_catalog_sort_orderby',
		'std'           => 'post_date',
		'type'          => 'radio',
		'options'       => array(
			'post_date' => __('Creation Date', 'fflcommerce'),
			'title'     => __('Product Title', 'fflcommerce'),
			'menu_order'=> __('Product Post Order', 'fflcommerce')
		)
	),

	array(
		'name'          => __('Catalog sort direction','fflcommerce'),
		'desc'          => '',
		'tip'           => __('Determines whether the catalog sort orderby is ascending or descending.','fflcommerce'),
		'id'            => 'fflcommerce_catalog_sort_direction',
		'std'           => 'asc',
		'type'          => 'radio',
		'options'       => array(
			'asc'       => __('Ascending', 'fflcommerce'),
			'desc'      => __('Descending', 'fflcommerce')
		)
	),

	array(
		'name'          => __('Catalog products display','fflcommerce'),
		'desc'          => __('Per row','fflcommerce'),
		'tip'           => __('Determines how many products to show on one display row for Shop, Category and Tag pages. Default = 3.','fflcommerce'),
		'id'            => 'fflcommerce_catalog_columns',
		'css'           => 'width:60px;',
		'std'           => '3',
		'type'          => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'         => true
	),

	array(
		'desc'          => __('Per page','fflcommerce'),
		'tip'           => __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation. Default = 12.','fflcommerce'),
		'id'            => 'fflcommerce_catalog_per_page',
		'css'           => 'width:60px;',
		'std'           => '12',
		'type'          => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'         => true
	),

	array( 'name'       => __('Pricing Options', 'fflcommerce'), 'type' => 'title','desc' => '', 'id' => '' ),

	array(
		'name'          => __('Currency', 'fflcommerce'),
		'desc'          => sprintf( __("This controls what currency prices are listed at in the catalog, and which currency PayPal, and other gateways, will take payments in. See the list of supported <a target='_new' href='%s'>PayPal currencies</a>.", 'fflcommerce'), 'https://www.paypal.com/cgi-bin/webscr?cmd=p/sell/mc/mc_intro-outside' ),
		'tip'           => '',
		'id'            => 'fflcommerce_currency',
		'css'           => 'min-width:200px;',
		'std'           => 'GBP',
		'type'          => 'select',
		'options'       => apply_filters('fflcommerce_currencies', array(
			'AED' => __('United Arab Emirates dirham (&#1583;&#46;&#1573;)', 'fflcommerce'),
			'AUD' => __('Australian Dollar (&#36;)'                        , 'fflcommerce'),
			'BRL' => __('Brazilian Real (&#82;&#36;)'                      , 'fflcommerce'),
			'CAD' => __('Canadian Dollar (&#36;)'                          , 'fflcommerce'),
			'CHF' => __('Swiss Franc (SFr.)'                               , 'fflcommerce'),
			'CNY' => __('Chinese yuan (&#165;)'                            , 'fflcommerce'),
			'CZK' => __('Czech Koruna (&#75;&#269;)'                       , 'fflcommerce'),
			'DKK' => __('Danish Krone (kr)'                                , 'fflcommerce'),
			'EUR' => __('Euro (&euro;)'                                    , 'fflcommerce'),
			'GBP' => __('Pounds Sterling (&pound;)'                        , 'fflcommerce'),
			'HKD' => __('Hong Kong Dollar (&#36;)'                         , 'fflcommerce'),
			'HRK' => __('Croatian Kuna (&#107;&#110;)'                     , 'fflcommerce'),
			'HUF' => __('Hungarian Forint (&#70;&#116;)'                   , 'fflcommerce'),
			'IDR' => __('Indonesia Rupiah (&#82;&#112;)'                   , 'fflcommerce'),
			'ILS' => __('Israeli Shekel (&#8362;)'                         , 'fflcommerce'),
			'INR' => __('Indian Rupee (&#8360;)'                           , 'fflcommerce'),
			'JPY' => __('Japanese Yen (&yen;)'                             , 'fflcommerce'),
			'MXN' => __('Mexican Peso (&#36;)'                             , 'fflcommerce'),
			'MYR' => __('Malaysian Ringgits (RM)'                          , 'fflcommerce'),
			'NGN' => __('Nigerian Naira (&#8358;)'                         , 'fflcommerce'),
			'NOK' => __('Norwegian Krone (kr)'                             , 'fflcommerce'),
			'NZD' => __('New Zealand Dollar (&#36;)'                       , 'fflcommerce'),
			'PHP' => __('Philippine Pesos (&#8369;)'                       , 'fflcommerce'),
			'PLN' => __('Polish Zloty (&#122;&#322;)'                      , 'fflcommerce'),
			'RON' => __('Romanian New Leu (&#108;&#101;&#105;)'            , 'fflcommerce'),
			'RUB' => __('Russian Ruble (&#1088;&#1091;&#1073;)'            , 'fflcommerce'),
			'SEK' => __('Swedish Krona (kr)'                               , 'fflcommerce'),
			'SGD' => __('Singapore Dollar (&#36;)'                         , 'fflcommerce'),
			'THB' => __('Thai Baht (&#3647;)'                              , 'fflcommerce'),
			'TRY' => __('Turkish Lira (&#8356;)'                           , 'fflcommerce'),
			'TWD' => __('Taiwan New Dollar (&#36;)'                        , 'fflcommerce'),
			'USD' => __('US Dollar (&#36;)'                                , 'fflcommerce'),
			'ZAR' => __('South African rand (R)'                           , 'fflcommerce')
			)
		)
	),

	array(
		'name' => __('Currency display', 'fflcommerce'),
		'desc' 		=> __("This controls the display of the currency symbol and currency code.", 'fflcommerce'),
		'tip' 		=> '',
		'id' 		=> 'fflcommerce_currency_pos',
		'css' 		=> 'min-width:200px;',
		'std' 		=> 'left',
		'type' 		=> 'select',
		'options' => array(
			'left'             => __(get_fflcommerce_currency_symbol() . '0'                                     . get_option('fflcommerce_price_decimal_sep'). '00'                                  , 'fflcommerce'),
			'left_space'       => __(get_fflcommerce_currency_symbol() . ' 0'                                    . get_option('fflcommerce_price_decimal_sep'). '00'                                  , 'fflcommerce'),
			'right'            => __('0'                            . get_option('fflcommerce_price_decimal_sep'). '00'                                    . get_fflcommerce_currency_symbol()        , 'fflcommerce'),
			'right_space'      => __('0'                            . get_option('fflcommerce_price_decimal_sep'). '00 '                                   . get_fflcommerce_currency_symbol()        , 'fflcommerce'),
			'left_code'        => __(get_option('fflcommerce_currency'). '0'                                     . get_option('fflcommerce_price_decimal_sep'). '00'                                  , 'fflcommerce'),
			'left_code_space'  => __(get_option('fflcommerce_currency'). ' 0'                                    . get_option('fflcommerce_price_decimal_sep'). '00'                                  , 'fflcommerce'),
			'right_code'       => __('0'                            . get_option('fflcommerce_price_decimal_sep'). '00'                                    . get_option('fflcommerce_currency')       , 'fflcommerce'),
			'right_code_space' => __('0'                            . get_option('fflcommerce_price_decimal_sep'). '00 '                                   . get_option('fflcommerce_currency')       , 'fflcommerce'),
			'symbol_code'      => __(get_fflcommerce_currency_symbol() . '0'                                     . get_option('fflcommerce_price_decimal_sep'). '00' . get_option('fflcommerce_currency'), 'fflcommerce'),
			'symbol_code_space'=> __(get_fflcommerce_currency_symbol() . ' 0'                                    . get_option('fflcommerce_price_decimal_sep'). '00 '. get_option('fflcommerce_currency'), 'fflcommerce'),
			'code_symbol'      => __(get_option('fflcommerce_currency'). '0'                                     . get_option('fflcommerce_price_decimal_sep'). '00' . get_fflcommerce_currency_symbol() , 'fflcommerce'),
			'code_symbol_space'=> __(get_option('fflcommerce_currency'). ' 0'                                    . get_option('fflcommerce_price_decimal_sep'). '00 '. get_fflcommerce_currency_symbol() , 'fflcommerce'),
		)
	),

	array(
		'name'         => __('Price Separators', 'fflcommerce'),
		'desc'         => __('Thousand separator', 'fflcommerce'),
		'id'           => 'fflcommerce_price_thousand_sep',
		'css'          => 'width:30px;',
		'std'          => ',',
		'type'         => 'text',
		'group'         => true
	),

	array(
		'desc'         => __('Decimal separator', 'fflcommerce'),
		'id'           => 'fflcommerce_price_decimal_sep',
		'css'          => 'width:30px;',
		'std'          => '.',
		'type'         => 'text',
		'group'         => true
	),

	array(
		'desc'         => __('Number of decimals', 'fflcommerce'),
		'id'           => 'fflcommerce_price_num_decimals',
		'css'          => 'width:30px;',
		'std'          => '2',
		'type'         => 'number',
		'restrict'      => array( 'min' => 0 ),
		'group'        => true
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Images', 'fflcommerce') ),

	array( 'name'      => __('Image Options', 'fflcommerce'), 'type'     => 'title', 'desc' => sprintf(__('<p>Changing any of these settings will affect the dimensions of images used in your Shop. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.</p>
																										<p>Crop: Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it). Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).</p>
																										<p><strong>Note:</strong> Your images may not display in the size you choose below. This is because they may still be affected by CSS styles, that is, your theme.', 'fflcommerce'), 'http://wordpress.org/extend/plugins/regenerate-thumbnails/'), 'id' => '' ),

	array(
		'name'         => __('Tiny Images','fflcommerce'),
		'desc'         => __('Cart, Checkout, Orders and Widgets','fflcommerce'),
		'id'           => 'fflcommerce_shop_tiny',
		'type'         => 'image_size',
		'std'          => 36,
		'placeholder'  => 36
	),

	array(
		'name'         => __('Thumbnail Images','fflcommerce'),
		'desc'         => __('Single Product page extra images.','fflcommerce'),
		'id'           => 'fflcommerce_shop_thumbnail',
		'type'         => 'image_size',
		'std'          => 90,
		'placeholder'  => 90
	),

	array(
		'name'         => __( 'Catalog Images', 'fflcommerce' ),
		'desc'         => __('Shop, Categories, Tags, and Related Products.', 'fflcommerce'),
		'id'           => 'fflcommerce_shop_small',
		'type'         => 'image_size',
		'std'          => 150,
		'placeholder'  => 150
	),

	array(
		'name'         => __('Large Images','fflcommerce'),
		'desc'         => __('Single Product pages','fflcommerce'),
		'id'           => 'fflcommerce_shop_large',
		'type'         => 'image_size',
		'std'          => 300,
		'placeholder'  => 300
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Coupons', 'fflcommerce') ),

	array( 'name'      => __('Coupon Information', 'fflcommerce'), 'type' => 'title', 'desc' => __('<div>Coupons allow you to give your customers special offers and discounts. </div>','fflcommerce') ),

	array(
		'name'         => __('Coupons','fflcommerce'),
		'desc'         => __('All fields are required.','fflcommerce'),
		'id'           => 'fflcommerce_coupons',
		'css'          => 'min-width:50px;',
		'type'         => 'coupons',
		'std'          => ''
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Products &amp; Inventory', 'fflcommerce') ),

	array( 'name'      => __('Product Options', 'fflcommerce'), 'type'   => 'title', 'desc' => '' ),

	array(
		'name'         => __('Product fields','fflcommerce'),
		'desc'         => __('Enable SKU','fflcommerce'),
		'tip'          => __('Turning off the SKU field will give products an SKU of their post id.','fflcommerce'),
		'id'           => 'fflcommerce_enable_sku',
		'std'          => 'no',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Enable weight','fflcommerce'),
		'tip'          => '',
		'id'           => 'fflcommerce_enable_weight',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Enable product dimensions','fflcommerce'),
		'tip'          => '',
		'id'           => 'fflcommerce_enable_dimensions',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'name'         => __('Weight Unit', 'fflcommerce'),
		'tip'          => __("This controls what unit you will define weights in.", 'fflcommerce'),
		'id'           => 'fflcommerce_weight_unit',
		'std'          => 'kg',
		'type'         => 'radio',
		'options'      => array(
			'kg'       => __('Kilograms', 'fflcommerce'),
			'lbs'      => __('Pounds', 'fflcommerce')
		)
	),

	array(
		'name'         => __('Dimensions Unit', 'fflcommerce'),
		'tip'          => __("This controls what unit you will define dimensions in.", 'fflcommerce'),
		'id'           => 'fflcommerce_dimension_unit',
		'std'          => 'cm',
		'type'         => 'radio',
		'options'      => array(
			'cm'       => __('centimeters', 'fflcommerce'),
			'in'       => __('inches', 'fflcommerce')
		)
	),

	array(
		'name'         => __('Show related products','fflcommerce'),
		'desc'         => '',
		'tip'          => __('To show or hide the related products section on a single product page.','fflcommerce'),
		'id'           => 'fflcommerce_enable_related_products',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array( 'name'      => __('Inventory Options', 'fflcommerce'), 'type' => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General inventory options','fflcommerce'),
		'desc'         => __('Enable product stock','fflcommerce'),
		'tip'          => __('If you are not managing stock, turn it off here to disable it in admin and on the front-end. You can manage stock on a per-item basis if you leave this option on.', 'fflcommerce'),
		'id'           => 'fflcommerce_manage_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Show stock amounts','fflcommerce'),
		'tip'          => __('Set to yes to allow customers to view the amount of stock available for a product.', 'fflcommerce'),
		'id'           => 'fflcommerce_show_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Hide out of stock products','fflcommerce'),
		'tip'          => 'When enabled: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.',
		'id'           => 'fflcommerce_hide_no_stock_product',
		'std'          => 'no',
		'type'         => 'checkbox'
	),

	array(
		'name'         => __('Notifications','fflcommerce'),
		'desc'         => __('Notify on low stock','fflcommerce'),
		'id'           => 'fflcommerce_notify_low_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Low stock threshold','fflcommerce'),
		'tip'          => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'fflcommerce'),
		'id'           => 'fflcommerce_notify_low_stock_amount',
		'css'          => 'width:50px;',
		'type'         => 'number',
		'restrict'     => array( 'min' => 0 ),
		'std'          => '2',
		'group'        => true
	),

	array(
		'desc'         => __('Notify on out of stock','fflcommerce'),
		'id'           => 'fflcommerce_notify_no_stock',
		'std'          => 'yes',
		'type'         => 'checkbox',
		'group'        => true
	),

	array(
		'desc'         => __('Out of stock threshold','fflcommerce'),
		'tip'          => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'fflcommerce'),
		'id'           => 'fflcommerce_notify_no_stock_amount',
		'css'          => 'width:50px;',
		'type'         => 'number',
		'restrict'     => array( 'min' => 0 ),
		'std'          => '0',
		'group'        => true
	),



	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Shipping', 'fflcommerce') ),

	array( 'name'      => __('Shipping Options', 'fflcommerce'), 'type'  => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General shipping settings','fflcommerce'),
		'desc'         => __('Calculate shipping','fflcommerce'),
		'tip'          => __('Only set this to no if you are not shipping items, or items have shipping costs included. If you are not calculating shipping then you can ignore all other tax options.', 'fflcommerce'),
		'id'           => 'fflcommerce_calc_shipping',
		'std'          => 'yes',
		'type'         => 'checkbox'
	),

	array(
		'desc'         => __('Enable shipping calculator on cart','fflcommerce'),
		'tip'          => '',
		'id'           => 'fflcommerce_enable_shipping_calc',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Only ship to billing address?','fflcommerce'),
		'tip'          => '',
		'id'           => 'fflcommerce_ship_to_billing_address_only',
		'std'          => 'no',
		'type'         => 'checkbox',
	),

	array( 'type'      => 'shipping_options'),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Tax', 'fflcommerce') ),

	array( 'name'      => __('Tax Options', 'fflcommerce'), 'type'       => 'title','desc' => '', 'id'                                                                                                                                                                                                                                                                                                                                                            => '' ),

	array(
		'name'         => __('General tax options','fflcommerce'),
		'desc'         => __('Enable tax calculation','fflcommerce'),
		'tip'          => __('Only disable this if you are exclusively selling non-taxable items. If you are not calculating taxes then you can ignore all other tax options.', 'fflcommerce'),
		'id'           => 'fflcommerce_calc_taxes',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Apply Taxes After Coupon','fflcommerce'),
		'tip'          => __('If yes, taxes get applied after coupons. When no, taxes get applied before coupons.','fflcommerce'),
		'id'           => 'fflcommerce_tax_after_coupon',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'desc'         => __('Catalog Prices include tax?','fflcommerce'),
		'tip'          => __('If prices include tax then tax calculations will work backwards.','fflcommerce'),
		'id'           => 'fflcommerce_prices_include_tax',
		'std'          => 'yes',
		'type'         => 'checkbox',
	),

	array(
		'name'         => __('Cart total displays','fflcommerce'),
		'desc'         => '',
		'tip'          => __('Should the subtotal be shown including or excluding tax on the frontend?','fflcommerce'),
		'id'           => 'fflcommerce_display_totals_tax',
		'std'          => 'excluding',
		'type'         => 'radio',
		'options'      => array(
			'including' => __('price including tax', 'fflcommerce'),
			'excluding' => __('price excluding tax', 'fflcommerce')
		)
	),

	array(
		'name'         => __('Additional Tax classes','fflcommerce'),
		'desc'         => __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.','fflcommerce'),
		'tip'          => __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.','fflcommerce'),
		'id'           => 'fflcommerce_tax_classes',
		'css'          => 'width:100%; height: 75px;',
		'type'         => 'textarea',
		'std'          => sprintf( __( 'Reduced Rate%sZero Rate', 'fflcommerce' ), PHP_EOL )
	),

	array(
		'name'         => __('Tax rates','fflcommerce'),
		'desc'         => __('All fields are required.','fflcommerce'),
		'tip'          => __('To avoid rounding errors, insert tax rates with 4 decimal places.','fflcommerce'),
		'id'           => 'fflcommerce_tax_rates',
		'css'          => 'min-width:50px;',
		'type'         => 'tax_rates',
		'std'          => ''
	),

	array( 'type'      => 'tabend'),

	array( 'type'      => 'tab', 'tabname'                            => __('Payment Gateways', 'fflcommerce') ),

	array( 'type'      => 'gateway_options'),

	array( 'type'      => 'tabend')

) );
