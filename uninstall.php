<?php
/**
 * Uninstall Script
 *
 * Removes all traces of FFL Commerce from the wordpress database
 *
 * DISCLAIMER
 *
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

if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

global $wpdb, $wp_roles;

if ( !defined( "FFLCOMMERCE_OPTIONS" )) define( "FFLCOMMERCE_OPTIONS", 'fflcommerce_options' );
require_once( 'classes/abstract/fflcommerce_base.class.php' );
require_once( 'classes/abstract/fflcommerce_singleton.class.php' );
require_once( 'classes/fflcommerce_session.class.php' );
require_once( 'classes/fflcommerce.class.php' );
require_once( 'classes/fflcommerce_options.class.php' );

// Remove the widget cache entry
delete_transient( 'fflcommerce_widget_cache' );

// Roles
remove_role( 'customer' );
remove_role( 'shop_manager' );

$wp_roles->remove_cap( 'administrator', 'manage_fflcommerce' );
$wp_roles->remove_cap( 'administrator', 'manage_fflcommerce_orders' );
$wp_roles->remove_cap( 'administrator', 'manage_fflcommerce_coupons' );
$wp_roles->remove_cap( 'administrator', 'manage_fflcommerce_products' );
$wp_roles->remove_cap( 'administrator', 'view_fflcommerce_reports' );

// Pages
$page_ids = FFLCommerce_Base::get_options()->get( 'fflcommerce_page-ids' );
if ( !empty( $page_ids ) && is_array( $page_ids ) ) foreach ( $page_ids as $id ) wp_delete_post( $id, true );

// Tables
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."fflcommerce_attribute_taxonomies");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."fflcommerce_downloadable_product_permissions");
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."fflcommerce_termmeta");

// Order Status
$wpdb->query("DELETE FROM $wpdb->terms WHERE term_id IN (select term_id FROM $wpdb->term_taxonomy WHERE taxonomy IN ('product_type', 'shop_order_status'))");
$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = 'shop_order_status'");

// Delete options
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'fflcommerce_%'");
