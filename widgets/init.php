<?php
/**
 * FFL Commerce Widgets
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
 */
require_once( 'best-sellers.php' );
require_once( 'cart.php' );
require_once( 'featured-products.php' );
require_once( 'layered_nav.php' );
require_once( 'price-filter.php' );
require_once( 'product-categories.php' );
require_once( 'product_search.php' );
require_once( 'product_tag_cloud.php' );
require_once( 'products_on_sale.php' );
require_once( 'random-products.php' );
require_once( 'recent_products.php' );
require_once( 'recent_reviews.php' );
require_once( 'recently_viewed.php' );
require_once( 'top-rated.php' );
require_once( 'user_login.php' );

function fflcommerce_register_widgets() {
	register_widget('FFLCommerce_Widget_Recent_Products');
	register_widget('FFLCommerce_Widget_Featured_Products');
	register_widget('FFLCommerce_Widget_Product_Categories');
	register_widget('FFLCommerce_Widget_Tag_Cloud');
	register_widget('FFLCommerce_Widget_Cart');
	register_widget('FFLCommerce_Widget_Layered_Nav');
	register_widget('FFLCommerce_Widget_Price_Filter');
	register_widget('FFLCommerce_Widget_Product_Search');
	register_widget('FFLCommerce_Widget_Products_On_Sale');
	register_widget('FFLCommerce_Widget_Top_Rated');
	register_widget('FFLCommerce_Widget_User_Login');
	register_widget('FFLCommerce_Widget_Recently_Viewed_Products');
	register_widget('FFLCommerce_Widget_Recent_Reviews');
	register_widget('FFLCommerce_Widget_Best_Sellers');
	register_widget('FFLCommerce_Widget_Random_Products');
}
add_action('widgets_init', 'fflcommerce_register_widgets');