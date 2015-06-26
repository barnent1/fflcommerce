<?php
/**
 * Actions used in template files
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */

/* Content Wrappers */
add_action('fflcommerce_before_main_content', 'fflcommerce_output_content_wrapper', 10);
add_action('fflcommerce_after_main_content', 'fflcommerce_output_content_wrapper_end', 10);

/* Shop Messages */
add_action('fflcommerce_before_single_product', 'fflcommerce::show_messages', 10);
add_action('fflcommerce_before_shop_loop', 'fflcommerce::show_messages', 10);

/* Sale flashes */
add_action('fflcommerce_before_shop_loop_item_title', 'fflcommerce_show_product_sale_flash', 10, 2);
add_action('fflcommerce_before_single_product_summary_thumbnails', 'fflcommerce_show_product_sale_flash', 10, 2);

/* Breadcrumbs */
add_action('fflcommerce_before_main_content', 'fflcommerce_breadcrumb', 20, 0);

/* Sidebar */
add_action('fflcommerce_sidebar', 'fflcommerce_get_sidebar', 10);
add_action('fflcommerce_after_sidebar', 'fflcommerce_get_sidebar_end', 10);

/* Products Loop */
add_action('fflcommerce_after_shop_loop_item', 'fflcommerce_template_loop_add_to_cart', 10, 2);
add_action('fflcommerce_before_shop_loop_item_title', 'fflcommerce_template_loop_product_thumbnail', 10, 2);
add_action('fflcommerce_after_shop_loop_item_title', 'fflcommerce_template_loop_price', 10, 2);

/* Before Single Products Summary Div */
add_action('fflcommerce_before_single_product_summary', 'fflcommerce_show_product_images', 20);
add_action('fflcommerce_product_thumbnails', 'fflcommerce_show_product_thumbnails', 20);

/* After Single Products Summary Div */
add_action('fflcommerce_after_single_product_summary', 'fflcommerce_output_product_data_tabs', 10);
add_action('fflcommerce_after_single_product_summary', 'fflcommerce_output_related_products', 20);

/* Product Summary Box */
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_title', 5, 2);
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_price', 10, 2);
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_excerpt', 20, 2);
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_meta', 40, 2);
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_sharing', 50, 2);

/* Product Add to cart */
add_action('fflcommerce_template_single_summary', 'fflcommerce_template_single_add_to_cart', 30, 2);
add_action('simple_add_to_cart', 'fflcommerce_simple_add_to_cart');
add_action('virtual_add_to_cart', 'fflcommerce_simple_add_to_cart');
add_action('downloadable_add_to_cart', 'fflcommerce_downloadable_add_to_cart');
add_action('grouped_add_to_cart', 'fflcommerce_grouped_add_to_cart');
add_action('variable_add_to_cart', 'fflcommerce_variable_add_to_cart');
add_action('external_add_to_cart', 'fflcommerce_external_add_to_cart');

/* Product Add to Cart forms */
add_action('fflcommerce_add_to_cart_form', 'fflcommerce_add_to_cart_form_nonce', 10);

/* Pagination in loop-shop */
add_action('fflcommerce_pagination', 'fflcommerce_pagination', 10);

/* Product page tabs */
add_action('fflcommerce_product_tabs', 'fflcommerce_product_description_tab', 10);
add_action('fflcommerce_product_tabs', 'fflcommerce_product_attributes_tab', 20);
add_action('fflcommerce_product_tabs', 'fflcommerce_product_reviews_tab', 30);
add_action('fflcommerce_product_tabs', 'fflcommerce_product_customize_tab', 40);

add_action('fflcommerce_product_tab_panels', 'fflcommerce_product_description_panel', 10);
add_action('fflcommerce_product_tab_panels', 'fflcommerce_product_attributes_panel', 20);
add_action('fflcommerce_product_tab_panels', 'fflcommerce_product_reviews_panel', 30);
add_action('fflcommerce_product_tab_panels', 'fflcommerce_product_customize_panel', 40);

/* Checkout */
add_action('before_checkout_form', 'fflcommerce_checkout_login_form', 10);
add_action('fflcommerce_checkout_order_review', 'fflcommerce_order_review', 10);
add_action('fflcommerce_review_order_after_submit', 'fflcommerce_verify_checkout_states_for_countries_message');
add_action('fflcommerce_review_order_after_submit', 'fflcommerce_eu_b2b_vat_message');

/* Remove the singular class for jigoshop single product */
add_action('after_setup_theme', 'fflcommerce_body_classes_check');

function fflcommerce_body_classes_check()
{
	if (has_filter('body_class', 'twentyeleven_body_classes')) {
		add_filter('body_class', 'fflcommerce_body_classes');
	}
}
