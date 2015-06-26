<?php
/*
 * Plugin Name:         FFL Commerce
 * Plugin URI:          https://www.fflcommerce.com/
 * Description:         FFL Commerce, a WordPress eCommerce plugin for FFL Dealers.
 * Author:              Tampa Bay Tactical Supply, Inc.
 * Author URI:          https://www.tampabaytacticalsupply.com
 * Version:             1.0.0
 * Requires at least:   3.8
 * Tested up to:        4.2.2
 * Text Domain:         FFLCommerce
 * Domain Path:         /languages/
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              FFLCommerce
 * @copyright           Copyright © 2011-2015 Tampa Bay Tactical Supply and Jigoshop.
 * @license             GNU General Public License v3
 */

if (!defined('FFLCOMMERCE_VERSION')) {
	define('FFLCOMMERCE_VERSION', '1.0.0');
}
if (!defined('FFLCOMMERCE_DB_VERSION')) {
	define('FFLCOMMERCE_DB_VERSION', 1503180);
}
if (!defined('FFLCOMMERCE_OPTIONS')) {
	define('FFLCOMMERCE_OPTIONS', 'fflcommerce_options');
}
if (!defined('FFLCOMMERCE_TEMPLATE_URL')) {
	define('FFLCOMMERCE_TEMPLATE_URL', 'fflcommerce/');
}
if (!defined('FFLCOMMERCE_DIR')) {
	define('FFLCOMMERCE_DIR', dirname(__FILE__));
}
if (!defined('FFLCOMMERCE_URL')) {
	define('FFLCOMMERCE_URL', plugins_url('', __FILE__));
}
if (!defined('FFLCOMMERCE_LOG_DIR')) {
	$upload_dir = wp_upload_dir();
	define('FFLCOMMERCE_LOG_DIR', $upload_dir['basedir'].'/fflcommerce-logs/');
}

define('FFLCOMMERCE_REQUIRED_MEMORY', 64);
define('FFLCOMMERCE_REQUIRED_WP_MEMORY', 64);
define('FFLCOMMERCE_PHP_VERSION', '5.3');
define('FFLCOMMERCE_WORDPRESS_VERSION', '3.8');

if(!version_compare(PHP_VERSION, FFLCOMMERCE_PHP_VERSION, '>=')){
	function fflcommerce_required_version(){
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> FFL Commerce requires at least PHP %s! Your version is: %s. Please upgrade.', 'fflcommerce'), FFLCOMMERCE_PHP_VERSION, PHP_VERSION).
		'</p></div>';
	}
	add_action('admin_notices', 'fflcommerce_required_version');
	return;
}

include ABSPATH.WPINC.'/version.php';
/** @noinspection PhpUndefinedVariableInspection */
if(!version_compare($wp_version, FFLCOMMERCE_WORDPRESS_VERSION, '>=')){
	function fflcommerce_required_wordpress_version()
	{
		include ABSPATH.WPINC.'/version.php';
		/** @noinspection PhpUndefinedVariableInspection */
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> FFL Commerce requires at least WordPress %s! Your version is: %s. Please upgrade.', 'fflcommerce'), FFLCOMMERCE_WORDPRESS_VERSION, $wp_version).
			'</p></div>';
	}
	add_action('admin_notices', 'fflcommerce_required_wordpress_version');
	return;
}

$ini_memory_limit = trim(ini_get('memory_limit'));
preg_match('/^(\d+)(\w*)?$/', $ini_memory_limit, $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch ($memory[2]) {
		case 'M':
		case 'm':
			$memory_limit *= 1024;
		case 'K':
		case 'k':
			$memory_limit *= 1024;
	}
}
if($memory_limit < FFLCOMMERCE_REQUIRED_MEMORY*1024*1024){
	function fflcommerce_required_memory_warning()
	{
		$ini_memory_limit = ini_get('memory_limit');
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> FFL Commerce requires at least %sM of memory! Your system currently has: %s.', 'fflcommerce'), FFLCOMMERCE_REQUIRED_MEMORY, $ini_memory_limit).
			'</p></div>';
	}
	add_action('admin_notices', 'fflcommerce_required_memory_warning');
}

preg_match('/^(\d+)(\w*)?$/', trim(WP_MEMORY_LIMIT), $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch ($memory[2]) {
		case 'M':
		case 'm':
			$memory_limit *= 1024;
		case 'K':
		case 'k':
			$memory_limit *= 1024;
	}
}

if($memory_limit < FFLCOMMERCE_REQUIRED_WP_MEMORY*1024*1024){
	function fflcommerece_required_wp_memory_warning()
	{
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> FFL Commerce requires at least %sM of memory for WordPress! Your system currently has: %s. <a href="%s" target="_blank">How to change?</a>', 'fflcommerce'),
				FFLCOMMERCE_REQUIRED_MEMORY, WP_MEMORY_LIMIT, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP').
			'</p></div>';
	}
	add_action('admin_notices', 'fflcommerce_required_wp_memory_warning');
}

/**
 * Include core files and classes
 */
include_once('classes/abstract/fflcommerce_base.class.php');
include_once('classes/abstract/fflcommerce_singleton.class.php');
include_once('classes/fflcommerce_options.class.php');
include_once('classes/fflcommerce_session.class.php');

include_once('classes/fflcommerce_sanitize.class.php');
include_once('classes/fflcommerce_validation.class.php');
include_once('classes/fflcommerce_forms.class.php');
include_once('fflcommerce_taxonomy.php');

include_once('classes/fflcommerce_countries.class.php');
include_once('classes/fflcommerce_customer.class.php');
include_once('classes/fflcommerce_product.class.php');
include_once('classes/fflcommerce_product_variation.class.php');
include_once('classes/fflcommerce_order.class.php');
include_once('classes/fflcommerce_orders.class.php');
include_once('classes/fflcommerce_tax.class.php');
include_once('classes/fflcommerce_shipping.class.php');
include_once('classes/fflcommerce_coupons.class.php');
include_once('classes/fflcommerce_licence_validator.class.php');
include_once('classes/fflcommerce_emails.class.php');

include_once('gateways/gateways.class.php');
include_once('gateways/gateway.class.php');
include_once('gateways/bank_transfer.php');
include_once('gateways/cheque.php');
include_once('gateways/cod.php');
include_once('gateways/paypal.php');
include_once('gateways/futurepay.php');
include_once('gateways/worldpay.php');
include_once('gateways/no_payment.php');

include_once('shipping/shipping_method.class.php');
include_once('shipping/fflcommerce_calculable_shipping.php');
include_once('shipping/flat_rate.php');
include_once('shipping/free_shipping.php');
include_once('shipping/local_pickup.php');

include_once('classes/fflcommerce_query.class.php');
include_once('classes/fflcommerce_request_api.class.php');

include_once('classes/fflcommerce.class.php');
include_once('classes/fflcommerce_cart.class.php');
include_once('classes/fflcommerce_checkout.class.php');
include_once('classes/fflcommerce_cron.class.php');

include_once('shortcodes/init.php');
include_once('widgets/init.php');

include_once('fflcommerce_functions.php');
include_once('fflcommerce_templates.php');
include_once('fflcommerce_template_actions.php');
include_once('fflcommerce_emails.php');
include_once('fflcommerce_actions.php');

// Plugins
include_once('plugins/fflcommerce-cart-favicon-count/fflcommerce-cart-favicon-count.php');

/**
 * IIS compat fix/fallback
 **/
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
	}
}

// Load administration & check if we need to install
if (is_admin()) {
	include_once('admin/fflcommerce-admin.php');
	register_activation_hook(__FILE__, 'install_fflcommerce');
}


function fflcommerce_admin_footer($text) {
	$screen = get_current_screen();

	if (strpos($screen->base, 'fflcommerce') === false && strpos($screen->parent_base, 'fflcommerce') === false && !in_array($screen->post_type, array('product', 'shop_order'))) {
		return $text;
	}

	return sprintf(
		'<a target="_blank" href="https://www.fflcommerce.com/support/">%s</a> | %s',
		__('Contact support', 'fflcommerce'),
		str_replace(
			array('[stars]','[link]','[/link]'),
			array(
				'<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/fflcommerce#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
				'<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/fflcommerce#postform" >',
				'</a>'
			),
			__('Add your [stars] on [link]wordpress.org[/link] and keep this plugin essentially free.', 'fflcommerce')
		)
	);
}
add_filter('admin_footer_text', 'fflcommerce_admin_footer');

/**
 * Adds FFL Commerce items to admin bar.
 */
function fflcommerce_admin_toolbar() {
	/** @var WP_Admin_Bar $wp_admin_bar */
	global $wp_admin_bar;
	$manage_products = current_user_can('manage_fflcommerce_products');
	$manage_orders = current_user_can('manage_fflcommerce_orders');
	$manage_jigoshop = current_user_can('manage_fflcommerce');
	$view_reports = current_user_can('view_fflcommerce_reports');
	$manege_emails = current_user_can('manage_fflcommerce_emails');

	if (!is_admin() && ($manage_fflcommerce || $manage_products || $manage_orders || $view_reports)) {
		$wp_admin_bar->add_node(array(
			'id' => 'fflcommerce',
			'title' => __('FFL Commerce', 'fflcommerce'),
			'href' => $manage_fflcommerce ? admin_url('admin.php?page=fflcommerce') : '',
			'parent' => false,
			'meta' => array(
				'class' => 'fflcommerce-toolbar'
			),
		));

		if ($manage_fflcommerce) {
			$wp_admin_bar->add_node(array(
				'id' => 'fflcommerce_dashboard',
				'title' => __('Dashboard', 'fflcommerce'),
				'parent' => 'fflcommerce',
				'href' => admin_url('admin.php?page=fflcommerce'),
			));
		}

		if ($manage_products) {
			$wp_admin_bar->add_node(array(
				'id' => 'fflcommerce_products',
				'title' => __('Products', 'fflcommerce'),
				'parent' => 'fflcommerce',
				'href' => admin_url('edit.php?post_type=product'),
			));
		}

		if ($manage_orders) {
			$wp_admin_bar->add_node(array(
				'id' => 'fflcommerce_orders',
				'title' => __('Orders', 'fflcommerce'),
				'parent' => 'fflcommerce',
				'href' => admin_url('edit.php?post_type=shop_order'),
			));
		}

		if ($manage_fflcommerce) {
			$wp_admin_bar->add_node(array(
				'id' => 'fflcommerce_settings',
				'title' => __('Settings', 'fflcommerce'),
				'parent' => 'fflcommerce',
				'href' => admin_url('admin.php?page=fflcommerce_settings'),
			));
		}

		if($manege_emails) {
			$wp_admin_bar->add_node(array(
				'id' => 'fflcommerce_emils',
				'title' => __('Emails', 'fflcommerce'),
				'parent' => 'fflcommerce',
				'href' => admin_url('edit.php?post_type=shop_email'),
			));
		}
	}
}

add_action('admin_bar_menu', 'fflcommerce_admin_toolbar', 35);

function fflcommerce_admin_bar_links($links)
{
	return array_merge(array(
		'<a href="'.admin_url('admin.php?page=fflcommerce_settings').'">'.__('Settings', 'fflcommerce').'</a>',
		'<a href="https://www.fflcommerce.com/documentation/">'.__('Docs', 'fflcommerce').'</a>',
		'<a href="https://www.fflcommerce.com/support/">'.__('Support', 'fflcommerce').'</a>',
	), $links);
}

function fflcommerce_admin_bar_edit($location, $term_id, $taxonomy)
{
	if (in_array($taxonomy, array('product_cat', 'product_tag')) && strpos($location, 'post_type=product') === false) {
		$location .= '&post_type=product';
	}

	return $location;
}
add_filter('get_edit_term_link', 'fflcommerce_admin_bar_edit', 10, 3);

/**
 * FFL Commerce Init
 */
add_action('init', 'fflcommerce_init', 0);
function fflcommerce_init()
{
	// Override default translations with custom .mo's found in wp-content/languages/fflcommerce first.
	load_textdomain('fflcommerce', WP_LANG_DIR.'/fflcommerce/fflcommerce-'.get_locale().'.mo');
	load_plugin_textdomain('fflcommerce', false, dirname(plugin_basename(__FILE__)).'/languages/');
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'fflcommerce_admin_bar_links');

	// instantiate options -after- loading text domains
	$options = FFLCommerce_Base::get_options();

	fflcommerce_post_type(); // register taxonomies
	new fflcommerce_cron(); // -after- text domains and Options instantiation allows settings translations
	fflcommerce_set_image_sizes(); // called -after- our Options are loaded

	// add Singletons here so that the taxonomies are loaded before calling them.
	fflcommerce_session::instance(); // Start sessions if they aren't already
	fflcommerce::instance(); // Utility functions, uses sessions
	fflcommerce_customer::instance(); // Customer class, sorts session data such as location

	// FFL Commerce will instantiate gateways and shipping methods on this same 'init' action hook
	// with a very low priority to ensure text domains are loaded first prior to installing any external options
	fflcommerce_shipping::instance(); // Shipping class. loads shipping methods
	fflcommerce_payment_gateways::instance(); // Payment gateways class. loads payment methods
	fflcommerce_cart::instance(); // Cart class, uses sessions

	add_filter( 'mce_external_plugins', 'fflcommerce_register_shortcode_editor' );
	add_filter( 'mce_buttons', 'fflcommerce_register_shortcode_buttons' );

	if (!is_admin()) {
		/* Catalog Filters */
		add_filter('loop-shop-query', create_function('', 'return array("orderby" => "'.$options->get('fflcommerce_catalog_sort_orderby').'","order" => "'.$options->get('fflcommerce_catalog_sort_direction').'");'));
		add_filter('loop_shop_columns', create_function('', 'return '.$options->get('fflcommerce_catalog_columns').';'));
		add_filter('loop_shop_per_page', create_function('', 'return '.$options->get('fflcommerce_catalog_per_page').';'));

		fflcommerce_catalog_query::instance(); // front end queries class
		fflcommerce_request_api::instance(); // front end request api for URL's
	}

	fflcommerce_roles_init();
	do_action('fflcommerce_initialize_plugins');
}

/**
 * Include template functions here with a low priority so they are pluggable by themes
 */
add_action('init', 'fflcommerce_load_template_functions', 999);
function fflcommerce_load_template_functions()
{
	include_once('fflcommerce_template_functions.php');
}


function fflcommerce_get_core_capabilities()
{
	$capabilities = array();

	$capabilities['core'] = array(
		'manage_fflcommerce',
		'view_fflcommerce_reports',
		'manage_fflcommerce_orders',
		'manage_fflcommerce_coupons',
		'manage_fflcommerce_products',
		'manage_fflcommerce_emails'
	);

	$capability_types = array('product', 'shop_order', 'shop_coupon', 'shop_email');
	foreach ($capability_types as $capability_type) {
		$capabilities[$capability_type] = array(
			// Post type
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",
			// Terms
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms"
		);
	}

	return $capabilities;
}

function fflcommerce_roles_init()
{
	global $wp_roles;

	if (class_exists('WP_Roles')) {
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
	}

	if (is_object($wp_roles)) {
		// Customer role
		add_role('customer', __('Customer', 'fflcommerce'), array(
			'read' => true,
			'edit_posts' => false,
			'delete_posts' => false
		));

		// Shop manager role
		add_role('shop_manager', __('Shop Manager', 'fflcommerce'), array(
			'read' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'edit_users' => true,
			'edit_posts' => true,
			'edit_pages' => true,
			'edit_published_posts' => true,
			'edit_published_pages' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_others_posts' => true,
			'edit_others_pages' => true,
			'publish_posts' => true,
			'publish_pages' => true,
			'delete_posts' => true,
			'delete_pages' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts' => true,
			'delete_others_pages' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'export' => true,
			'import' => true,
		));

		$capabilities = fflcommerce_get_core_capabilities();
		foreach ($capabilities as $cap_group) {
			foreach ($cap_group as $cap) {
				$wp_roles->add_cap('administrator', $cap);
				$wp_roles->add_cap('shop_manager', $cap);
			}
		}
	}
}

function fflcommerce_prepare_dashboard_title($title)
{
	$result = '<span>'.preg_replace('/ /', '</span> ', $title, 1);
	if (strpos($result, '</span>') === false) {
		$result .= '</span>';
	}

	return $result;
}

/**
 * Enqueues script.
 * Calls filter `jrto_enqueue_script`. If the filter returns empty value the script is omitted.
 * Available options:
 *   * version - Wordpress script version number
 *   * in_footer - is this script required to add to the footer?
 *   * page - list of pages to use the script
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param bool $src Source file.
 * @param array $dependencies List of dependencies to the script.
 * @param array $options List of options.
 */
function fflcommerce_add_script($handle, $src, array $dependencies = array(), array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_fflcommerce_page($page)) {
		$version = isset($options['version']) ? $options['version'] : false;
		$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
		wp_enqueue_script($handle, $src, $dependencies, $version, $footer);
	}
}

/**
 * Removes script from enqueued list.
 * Calls filter `fflcommerce_remove_script`. If the filter returns empty value the script is omitted.
 * Available options:
 *   * page - list of pages to use the script
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param array $options List of options.
 */
function fflcommerce_remove_script($handle, array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_fflcommerce_page($page)) {
		wp_deregister_script($handle);
	}
}

/**
 * Localizes script.
 * Calls filter `fflcommerce_localize_script`. If the filter returns empty value the script is omitted.
 *
 * @param string $handle Handle name.
 * @param string $object Object name.
 * @param array $values List of values to localize.
 */
function fflcommerce_localize_script($handle, $object, array $values)
{
	wp_localize_script($handle, $object, $values);
}

/**
 * Enqueues stylesheet.
 * Calls filter `fflcommerce_add_style`. If the filter returns empty value the style is omitted.
 * Available options:
 *   * version - Wordpress script version number
 *   * media - CSS media this script represents
 *   * page - list of pages to use the style
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param bool $src Source file.
 * @param array $dependencies List of dependencies to the stylesheet.
 * @param array $options List of options.
 */
function fflcommerce_add_style($handle, $src, array $dependencies = array(), array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_fflcommerce_page($page)) {
		$version = isset($options['version']) ? $options['version'] : false;
		$media = isset($options['media']) ? $options['media'] : 'all';
		wp_enqueue_style($handle, $src, $dependencies, $version, $media);
	}
}

/**S
 * Removes style from enqueued list.
 * Calls filter `fflcommerce_remove_style`. If the filter returns empty value the style is omitted.
 * Available options:
 *   * page - list of pages to use the style
 * Options could be extended by plugins.
 *
 * @param string $handle Handle name.
 * @param array $options List of options.
 */
function fflcommerce_remove_style($handle, array $options = array())
{
	$page = isset($options['page']) ? (array)$options['page'] : array('all');

	if (is_fflcommerce_page($page)) {
		wp_deregister_style($handle);
	}
}

/**
 * Checks if current page is one of given page types.
 *
 * @param string|array $pages List of page types to check.
 * @return bool Is current page one of provided?
 */
function is_fflcommerce_page($pages)
{
	$result = false;
	$pages = is_array($pages) ? $pages : array($pages);

	foreach ($pages as $page) {
		$result = $result || is_fflcommerce_single_page($page);
	}

	return $result;
}

// Define all Jigoshop page constants
define('FFLCOMMERCE_CART', 'cart');
define('FFLCOMMERCE_CHECKOUT', 'checkout');
define('FFLCOMMERCE_PAY', 'pay');
define('FFLCOMMERCE_THANK_YOU', 'thanks');
define('FFLCOMMERCE_MY_ACCOUNT', 'myaccount');
define('FFLCOMMERCE_EDIT_ADDRESS', 'edit_address');
define('FFLCOMMERCE_VIEW_ORDER', 'view_order');
define('FFLCOMMERCE_CHANGE_PASSWORD', 'change_password');
define('FFLCOMMERCE_PRODUCT', 'product');
define('FFLCOMMERCE_PRODUCT_CATEGORY', 'product_category');
define('FFLCOMMERCE_PRODUCT_LIST', 'product_list');
define('FFLCOMMERCE_PRODUCT_TAG', 'product_tag');
define('FFLCOMMERCE_ALL', 'all');

/**
 * Returns list of pages supported by is_fflcommerce_single_page() and is_fflcommerce_page().
 *
 * @return array List of supported pages.
 */
function fflcommerce_get_available_pages()
{
	return array(
		FFLCOMMERCE_CART,
		FFLCOMMERCE_PAY,
		FFLCOMMERCE_CHECKOUT,
		FFLCOMMERCE_THANK_YOU,
		FFLCOMMERCE_EDIT_ADDRESS,
		FFLCOMMERCE_MY_ACCOUNT,
		FFLCOMMERCE_VIEW_ORDER,
		FFLCOMMERCE_CHANGE_PASSWORD,
		FFLCOMMERCE_PRODUCT,
		FFLCOMMERCE_PRODUCT_CATEGORY,
		FFLCOMMERCE_PRODUCT_TAG,
		FFLCOMMERCE_PRODUCT_LIST,
		FFLCOMMERCE_ALL,
	);
}

/**
 * Checks if current page is of given page type.
 *
 * @param string $page Page type.
 * @return bool Is current page the one from name?
 */
function is_fflcommerce_single_page($page)
{
	switch ($page) {
		case FFLCOMMERCE_CART:
			return is_cart();
		case FFLCOMMERCE_CHECKOUT:
			return is_checkout();
		case FFLCOMMERCE_PAY:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_PAY));
		case FFLCOMMERCE_THANK_YOU:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_THANK_YOU));
		case FFLCOMMERCE_MY_ACCOUNT:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_MY_ACCOUNT));
		case FFLCOMMERCE_EDIT_ADDRESS:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_EDIT_ADDRESS));
		case FFLCOMMERCE_VIEW_ORDER:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_VIEW_ORDER));
		case FFLCOMMERCE_CHANGE_PASSWORD:
			return is_page(fflcommerce_get_page_id(FFLCOMMERCE_CHANGE_PASSWORD));
		case FFLCOMMERCE_PRODUCT:
			return is_product();
		case FFLCOMMERCE_PRODUCT_CATEGORY:
			return is_product_category();
		case FFLCOMMERCE_PRODUCT_LIST:
			return is_product_list();
		case FFLCOMMERCE_PRODUCT_TAG:
			return is_product_tag();
		case FFLCOMMERCE_ALL:
			return true;
		default:
			return fflcommerce_is_admin_page() == $page;
	}
}

/**
 * FFL Commerce Frontend Styles and Scripts
 */
add_action('init', 'fflcommerce_frontend_scripts', 1);
function fflcommerce_frontend_scripts()
{
	$options = FFLCommerce_Base::get_options();
	$frontend_css = FFLCOMMERCE_URL.'/assets/css/frontend.css';
	$theme_css = file_exists(get_stylesheet_directory().'/fflcommerce/style.css')
		? get_stylesheet_directory_uri().'/fflcommerce/style.css'
		: $frontend_css;

	if ($options->get('fflcommerce_disable_css') == 'no') {
		if ($options->get('fflcommerce_frontend_with_theme_css') == 'yes' && $frontend_css != $theme_css) {
			jrto_enqueue_style('frontend', 'fflcommerce_theme_styles', $frontend_css);
		}
		jrto_enqueue_style('frontend', 'fflcommerce_styles', $theme_css);
	}

	wp_enqueue_script('jquery');
	wp_register_script('jquery-blockui', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', array('jquery'), '2.66.0');
	wp_enqueue_script('jquery-blockui');
	jrto_enqueue_script('frontend', 'fflcommerce_global', FFLCOMMERCE_URL.'/assets/js/global.js', array('jquery'), array('in_footer' => true));

	if ($options->get('fflcommerce_disable_fancybox') == 'no') {
		jrto_enqueue_script('frontend', 'prettyPhoto', FFLCOMMERCE_URL.'/assets/js/jquery.prettyPhoto.js', array('jquery'), array('in_footer' => true));
		jrto_enqueue_style('frontend', 'prettyPhoto', FFLCOMMERCE_URL.'/assets/css/prettyPhoto.css');
	}

	jrto_enqueue_script('frontend', 'fflcommerce-cart', FFLCOMMERCE_URL.'/assets/js/cart.js', array('jquery'), array('in_footer' => true, 'page' => FFLCOMMERCE_CART));
	jrto_enqueue_script('frontend', 'fflcommerce-checkout', FFLCOMMERCE_URL.'/assets/js/checkout.js', array('jquery', 'jquery-blockui'), array('in_footer' => true, 'page' => array(FFLCOMMERCE_CHECKOUT, FFLCOMMERCE_PAY)));
	jrto_enqueue_script('frontend', 'fflcommerce-validation', FFLCOMMERCE_URL.'/assets/js/validation.js', array(), array('in_footer' => true, 'page' => FFLCOMMERCE_CHECKOUT));
	jrto_enqueue_script('frontend', 'fflcommerce-payment', FFLCOMMERCE_URL.'/assets/js/pay.js', array('jquery'), array('page' => FFLCOMMERCE_PAY));
	jrto_enqueue_script('frontend', 'fflcommerce-single-product', FFLCOMMERCE_URL.'/assets/js/single-product.js', array('jquery'), array('in_footer' => true, 'page' => FFLCOMMERCE_PRODUCT));
	jrto_enqueue_script('frontend', 'fflcommerce-countries', FFLCOMMERCE_URL.'/assets/js/countries.js', array(), array(
		'in_footer' => true,
		'page' => array(FFLCOMMERCE_CHECKOUT, FFLCOMMERCE_CART, FFLCOMMERCE_EDIT_ADDRESS)
	));


	/* Script.js variables */
	// TODO: clean this up, a lot aren't even used anymore, do away with it
	$fflcommerce_params = array(
		'ajax_url' => admin_url('admin-ajax.php', 'fflcommerce'),
		'assets_url' => FFLCOMMERCE_URL,
		'validate_postcode' => $options->get('fflcommerce_enable_postcode_validating', 'no'),
		'checkout_url' => admin_url('admin-ajax.php?action=fflcommerce-checkout', 'fflcommerce'),
		'currency_symbol' => get_fflcommerce_currency_symbol(),
		'get_variation_nonce' => wp_create_nonce("get-variation"),
		'load_fancybox' => $options->get('fflcommerce_disable_fancybox') == 'no',
		'option_guest_checkout' => $options->get('fflcommerce_enable_guest_checkout'),
		'select_state_text' => __('Select a state&hellip;', 'fflcommerce'),
		'state_text' => __('state', 'fflcommerce'),
		'ratings_message' => __('Please select a star to rate your review.', 'fflcommerce'),
		'update_order_review_nonce' => wp_create_nonce("update-order-review"),
		'billing_state' => fflcommerce_customer::get_state(),
		'shipping_state' => fflcommerce_customer::get_shipping_state(),
		'is_checkout' => (is_page(fflcommerce_get_page_id('checkout')) || is_page(fflcommerce_get_page_id('pay'))),
		'error_hide_time' => FFLCommerce_Base::get_options()->get('fflcommerce_error_disappear_time', 8000),
		'message_hide_time' => FFLCommerce_Base::get_options()->get('fflcommerce_message_disappear_time', 4000),
	);

	if (isset(fflcommerce_session::instance()->min_price)) {
		$fflcommerce_params['min_price'] = $_GET['min_price'];
	}

	if (isset(fflcommerce_session::instance()->max_price)) {
		$fflcommerce_params['max_price'] = $_GET['max_price'];
	}

	$fflcommerce_params = apply_filters('fflcommerce_params', $fflcommerce_params);
	jrto_localize_script('fflcommerce_global', 'fflcommerce_params', $fflcommerce_params);
}

/**
 * Add post thumbnail support to WordPress if needed
 */
add_action('after_setup_theme', 'fflcommerce_check_thumbnail_support', 99);
function fflcommerce_check_thumbnail_support()
{
	if (!current_theme_supports('post-thumbnails')) {
		add_theme_support('post-thumbnails');
		remove_post_type_support('post', 'thumbnail');
		remove_post_type_support('page', 'thumbnail');
	} else {
		add_post_type_support('product', 'thumbnail');
	}
}

add_action('current_screen', 'fflcommerce_admin_styles');
function fflcommerce_admin_styles()
{
	/* Our setting icons */
	jrto_enqueue_style('admin', 'fflcommerce_admin_icons_style', FFLCOMMERCE_URL.'/assets/css/admin-icons.css');

	global $current_screen;
	if ($current_screen === null || (!fflcommerce_is_admin_page() && $current_screen->base !== 'user-edit')) {
		return;
	}

	jrto_enqueue_style('admin', 'fflcommerce-select2', FFLCOMMERCE_URL.'/assets/css/select2.css');

	if (fflcommerce_is_admin_page()) {
		wp_enqueue_style('thickbox');
		jrto_enqueue_style('admin', 'fflcommerce_admin_styles', FFLCOMMERCE_URL.'/assets/css/admin.css');
		jrto_enqueue_style('admin', 'fflcommerce-jquery-ui', FFLCOMMERCE_URL.'/assets/css/jquery-ui.css');
		jrto_enqueue_style('admin', 'prettyPhoto', FFLCOMMERCE_URL.'/assets/css/prettyPhoto.css');
	}
}

add_action('admin_enqueue_scripts', 'fflcommerce_admin_scripts', 1);
function fflcommerce_admin_scripts()
{
	global $current_screen;
	if (!fflcommerce_is_admin_page() && $current_screen->base !== 'user-edit') {
		return;
	}

	jrto_enqueue_script('admin', 'fflcommerce-select2', FFLCOMMERCE_URL.'/assets/js/select2.min.js', array('jquery'));
	jrto_enqueue_script('admin', 'fflcommerce-editor-shortcodes', FFLCOMMERCE_URL.'/assets/js/editor-shortcodes.js', array('jquery'));
	wp_register_script('jquery-blockui', '//cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.66.0-2013.10.09/jquery.blockUI.min.js', array('jquery'));

	if (fflcommerce_is_admin_page()) {
		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-blockui');
		jrto_enqueue_script('admin', 'fflcommerce_datetimepicker', FFLCOMMERCE_URL.'/assets/js/jquery-ui-timepicker-addon.min.js', array('jquery', 'jquery-ui-datepicker'));
		jrto_enqueue_script('admin', 'fflcommerce_media', FFLCOMMERCE_URL.'/assets/js/media.js', array('jquery', 'media-editor'));
		jrto_enqueue_script('admin', 'fflcommerce_backend', FFLCOMMERCE_URL.'/assets/js/backend.js', array('jquery'), array('version' => FFLCOMMERCE_VERSION));
		if($current_screen->base == 'edit-tags' && FFLCommerce_Base::get_options()->get('fflcommerce_enable_draggable_categories') == 'yes') {
			jrto_enqueue_script('admin', 'fflcommerce_draggable_categories', FFLCOMMERCE_URL.'/assets/js/draggable_categories.js', array('jquery'), array('version' => FFLCOMMERCE_VERSION));
		}
		jrto_enqueue_script('admin', 'jquery_flot', FFLCOMMERCE_URL.'/assets/js/admin/jquery.flot.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_pie', FFLCOMMERCE_URL.'/assets/js/admin/jquery.flot.pie.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_resize', FFLCOMMERCE_URL.'/assets/js/admin/jquery.flot.resize.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_stack', FFLCOMMERCE_URL.'/assets/js/admin/jquery.flot.stack.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery_flot_time', FFLCOMMERCE_URL.'/assets/js/admin/jquery.flot.time.min.js', array('jquery'), array(
				'version' => '0.8.1',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'fflcommerce_reports', FFLCOMMERCE_URL.'/assets/js/admin/reports.js', array('jquery'), array(
				'version' => FFLCOMMERCE_VERSION,
				'page' => array('fflcommerce_page_fflcommerce_reports', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery.tiptip', FFLCOMMERCE_URL.'/assets/js/admin/jquery.tipTip.min.js', array('jquery'), array(
				'version' => '1.3',
				'page' => array('fflcommerce_page_fflcommerce_reports', 'fflcommerce_page_fflcommerce_system_info', 'toplevel_page_fflcommerce')
			)
		);
		jrto_enqueue_script('admin', 'jquery.zeroclipboard', FFLCOMMERCE_URL.'/assets/js/admin/jquery.zeroclipboard.min.js', array('jquery'), array(
				'version' => '0.2.0',
				'page' => array('fflcommerce_page_fflcommerce_system_info', 'toplevel_page_fflcommerce')
			)
		);

		jrto_localize_script('fflcommerce_backend', 'fflcommerce_params', array(
			'ajax_url' => admin_url('admin-ajax.php', 'fflcommerce'),
			'search_products_nonce' => wp_create_nonce("search-products"),
		));

		$pagenow = fflcommerce_is_admin_page();
		/**
		 * Disable autosaves on the order and coupon pages. Prevents the javascript alert when modifying.
		 * `wp_deregister_script( 'autosave' )` would produce errors, so we use a filter instead.
		 */
		if ($pagenow == 'shop_order' || $pagenow == 'shop_coupon') {
			add_filter('script_loader_src', 'fflcommerce_disable_autosave', 10, 2);
		}
	}
}

function fflcommerce_register_shortcode_editor( $plugin_array ) {
	$plugin_array['fflcommerceShortcodes'] = FFLCOMMERCE_URL.'/assets/js/editor-shortcodes.js';
	return $plugin_array;
}

function fflcommerce_register_shortcode_buttons( $buttons ) {

	array_push( $buttons, "jrto_enqueue_cart" );
	array_push( $buttons, "fflcommerce_show_product" );
	array_push( $buttons, "fflcommerce_show_category" );
	array_push( $buttons, "fflcommerce_show_featured_products" );
	array_push( $buttons, "fflcommerce_show_selected_products" );
	array_push( $buttons, "fflcommerce_product_search" );
	array_push( $buttons, "fflcommerce_recent_products" );
	array_push( $buttons, "fflcommerce_sale_products" );

	return $buttons;
}

/**
 *  Load required CSS files when frontend styles
 */
add_action('init', 'fflcommerce_check_required_css', 99);
function fflcommerce_check_required_css()
{
	global $wp_styles;

	if (empty($wp_styles->registered['fflcommerce_styles'])) {
		jrto_enqueue_style('frontend', 'fflcommerce-jquery-ui', FFLCOMMERCE_URL.'/assets/css/jquery-ui.css');
		jrto_enqueue_style('frontend', 'fflcommerce-select2', FFLCOMMERCE_URL.'/assets/css/select2.css');
		jrto_enqueue_style('frontend', 'prettyPhoto', FFLCOMMERCE_URL.'/assets/css/prettyPhoto.css');
	}
}


//### Functions #########################################################

/**
 * Set FFL Commerce Product Image Sizes for WordPress based on Admin->FFLCommerce->Settings->Images
 */
function fflcommerce_set_image_sizes()
{
	$options = FFLCommerce_Base::get_options();

	$sizes = array(
		'shop_tiny' => 'tiny',
		'shop_thumbnail' => 'thumbnail',
		'shop_small' => 'catalog',
		'shop_large' => 'featured'
	);

	foreach ($sizes as $size => $altSize) {
		add_image_size(
			$size,
			$options->get('fflcommerce_'.$size.'_w'),
			$options->get('fflcommerce_'.$size.'_h'),
			($options->get('fflcommerce_use_wordpress_'.$altSize.'_crop', 'no') == 'yes')
		);
	}

	add_image_size('admin_product_list', 32, 32, $options->get('fflcommerce_use_wordpress_tiny_crop', 'no') == 'yes' ? true : false);
}

/**
 * Get FFL Commerce Product Image Size based on Admin->FFLCommerce->Settings->Images
 *
 * @param string $size - one of the 4 defined FFL Commerce image sizes
 * @return array - an array containing the width and height of the required size
 * @since 0.9.9
 */
function fflcommerce_get_image_size($size)
{
	$options = FFLCommerce_Base::get_options();
	if (is_array($size)) {
		return $size;
	}

	switch ($size) {
		case 'admin_product_list':
			$image_size = array(32, 32);
			break;
		case 'shop_tiny':
			$image_size = array($options->get('fflcommerce_shop_tiny_w'), $options->get('fflcommerce_shop_tiny_h'));
			break;
		case 'shop_thumbnail':
			$image_size = array($options->get('fflcommerce_shop_thumbnail_w'), $options->get('fflcommerce_shop_thumbnail_h'));
			break;
		case 'shop_small':
			$image_size = array($options->get('fflcommerce_shop_small_w'), $options->get('fflcommerce_shop_small_h'));
			break;
		case 'shop_large':
			$image_size = array($options->get('fflcommerce_shop_large_w'), $options->get('fflcommerce_shop_large_h'));
			break;
		default:
			$image_size = array($options->get('fflcommerce_shop_small_w'), $options->get('fflcommerce_shop_small_h'));
			break;
	}

	return $image_size;
}

function fflcommerce_is_admin_page()
{
	global $current_screen;

	if ($current_screen == null) {
		return false;
	}

	if ($current_screen->post_type == 'product' || $current_screen->post_type == 'shop_order' || $current_screen->post_type == 'shop_coupon' || $current_screen->post_type == 'shop_email') {
		return $current_screen->post_type;
	}

	if (strstr($current_screen->id, 'fflcommerce')) {
		return $current_screen->id;
	}

	return false;
}

function fflcommerce_disable_autosave($src, $handle)
{
	if ('autosave' != $handle) {
		return $src;
	}

	return '';
}

/**
 * Adds a demo store banner to the site
 */
function fflcommerce_demo_store()
{
	if (FFLCommerce_Base::get_options()->get('fflcommerce_demo_store') == 'yes' && is_fflcommerce()){
		$bannner_text = apply_filters('fflcommerce_demo_banner_text', __('This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'fflcommerce'));
		echo '<p class="demo_store">'.$bannner_text.'</p>';
	}
}
add_action('wp_footer', 'fflcommerce_demo_store');

/**
 * Adds social sharing code to footer
 */
function fflcommerce_sharethis()
{
	$option = FFLCommerce_Base::get_options();
	if (is_single() && $option->get('fflcommerce_sharethis')) {
		if (is_ssl()) {
			$sharethis = 'https://ws.sharethis.com/button/buttons.js';
		} else {
			$sharethis = 'http://w.sharethis.com/button/buttons.js';
		}

		echo '<script type="text/javascript">var switchTo5x=true;</script><script type="text/javascript" src="'.$sharethis.'"></script><script type="text/javascript">stLight.options({publisher:"'.$option->get('fflcommerce_sharethis').'"});</script>';
	}
}
add_action('wp_footer', 'fflcommerce_sharethis');

/**
 * FFL Commerce Mail 'from' name on emails
 * We will add a filter to WordPress to get this as the site name when emails are sent
 */
function fflcommerce_mail_from_name()
{
	return esc_attr(get_bloginfo('name'));
}

/**
 * Allow product_cat in the permalinks for products.
 *
 * @param string $permalink The existing permalink URL.
 * @param WP_Post $post
 * @return string
 */
function fflcommerce_product_cat_filter_post_link($permalink, $post)
{
	if ($post->post_type !== 'product') {
		return $permalink;
	}

	// Abort early if the placeholder rewrite tag isn't in the generated URL
	if (false === strpos($permalink, '%product_cat%')) {
		return $permalink;
	}

	// Get the custom taxonomy terms in use by this post
	$terms = get_the_terms($post->ID, 'product_cat');

	if (empty($terms)) {
		// If no terms are assigned to this post, use a string instead
		$permalink = str_replace('%product_cat%', _x('product', 'slug', 'fflcommerce'), $permalink);
	} else {
		// Replace the placeholder rewrite tag with the first term's slug
		$first_term = apply_filters('fflcommerce_product_cat_permalink_terms', array_shift($terms), $terms);
		$permalink = str_replace('%product_cat%', $first_term->slug, $permalink);
	}

	return $permalink;
}
add_filter('post_type_link', 'fflcommerce_product_cat_filter_post_link', 10, 2);

/**
 * Helper function to locate proper template and set up environment based on passed array.
 *
 * @param string $template Template name.
 * @param array $variables Template variables
 */
function fflcommerce_render($template, array $variables) {
	$file = fflcommerce_locate_template($template);
	extract($variables);
	/** @noinspection PhpIncludeInspection */
	require($file);
}

/**
 * Helper function to locate proper template and set up environment based on passed array.
 * Returns value of rendered template as a string.
 *
 * @param string $template Template name.
 * @param array $variables Template variables
 * @return string
 */
function fflcommerce_render_result($template, array $variables) {
	ob_start();
	fflcommerce_render($template, $variables);
	return ob_get_clean();
}

/**
 * Evaluates to true only on the Shop page, not Product categories and tags
 * Note:is used to replace is_page( fflcommerce_get_page_id( 'shop' ) )
 *
 * @return bool
 * @since 0.9.9
 */
function is_shop()
{
	return is_post_type_archive('product') || is_page(fflcommerce_get_page_id('shop'));
}

/**
 * Evaluates to true only on the Category Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_category()
{
	return is_tax('product_cat');
}

/**
 * Evaluates to true only on the Tag Pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_tag()
{
	return is_tax('product_tag');
}

/**
 * Evaluates to true only on the Single Product Page
 *
 * @return bool
 * @since 0.9.9
 */
function is_product()
{
	return is_singular(array('product'));
}

/**
 * Evaluates to true only on Shop, Product Category, and Product Tag pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_product_list()
{
	$is_list = false;
	$is_list |= is_shop();
	$is_list |= is_product_tag();
	$is_list |= is_product_category();

	return $is_list;
}

/**
 * Evaluates to true for all Jigoshop pages
 *
 * @return bool
 * @since 0.9.9
 */
function is_fflcommerce()
{
	$is_fflc = false;
	$is_fflc |= is_content_wrapped();
	$is_fflc |= is_account();
	$is_fflc |= is_cart();
	$is_fflc |= is_checkout();
	$is_fflc |= is_order_tracker();

	return $is_fflc;
}

/**
 * Evaluates to true only on the Shop, Category, Tag and Single Product Pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_content_wrapped()
{
	$is_wrapped = false;
	$is_wrapped |= is_product_list();
	$is_wrapped |= is_product();

	return $is_wrapped;
}

/**
 * FFL Commerce page IDs
 * returns -1 if no page is found
 */
if (!function_exists('fflcommerce_get_page_id')) {
	function fflcommerce_get_page_id($page)
	{
		$fflcommerce_options = FFLCommerce_Base::get_options();
		$page = apply_filters('fflcommerce_get_'.$page.'_page_id', $fflcommerce_options->get('fflcommerce_'.$page.'_page_id'));

		return ($page) ? $page : -1;
	}
}

/**
 * Evaluates to true only on the Order Tracking page
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_order_tracker()
{
	return is_page(fflcommerce_get_page_id('track_order'));
}

/**
 * Evaluates to true only on the Cart page
 *
 * @return bool
 * @since 0.9.8
 */
function is_cart()
{
	return is_page(fflcommerce_get_page_id('cart'));
}

/**
 * Evaluates to true only on the Checkout or Pay pages
 *
 * @return bool
 * @since 0.9.8
 */
function is_checkout()
{
	return is_page(fflcommerce_get_page_id('checkout')) | is_page(fflcommerce_get_page_id('pay'));
}

/**
 * Evaluates to true only on the main Account or any sub-account pages
 *
 * @return bool
 * @since 0.9.9.1
 */
function is_account()
{
	$is_account = false;
	$is_account |= is_page(fflcommerce_get_page_id('myaccount'));
	$is_account |= is_page(fflcommerce_get_page_id('edit_address'));
	$is_account |= is_page(fflcommerce_get_page_id('change_password'));
	$is_account |= is_page(fflcommerce_get_page_id('view_order'));

	return $is_account;
}

if (!function_exists('is_ajax')) {
	function is_ajax()
	{
		if (defined('DOING_AJAX')) {
			return true;
		}

		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
}

function fflcommerce_force_ssl()
{
	if (is_checkout() && !is_ssl()) {
		wp_safe_redirect(str_replace('http:', 'https:', get_permalink(fflcommerce_get_page_id('checkout'))), 301);
		exit;
	}
}

if (!is_admin() && FFLCommerce_Base::get_options()->get('fflcommerce_force_ssl_checkout') == 'yes') {
	add_action('wp', 'fflcommerce_force_ssl');
}

function fflcommerce_force_ssl_images($content)
{
	if (is_ssl()) {
		if (is_array($content)) {
			$content = array_map('fflcommerce_force_ssl_images', $content);
		} else {
			$content = str_replace('http:', 'https:', $content);
		}
	}

	return $content;
}

add_filter('post_thumbnail_html', 'fflcommerce_force_ssl_images');
add_filter('widget_text', 'fflcommerce_force_ssl_images');
add_filter('wp_get_attachment_url', 'fflcommerce_force_ssl_images');
add_filter('wp_get_attachment_image_attributes', 'fflcommerce_force_ssl_images');
add_filter('wp_get_attachment_url', 'fflcommerce_force_ssl_images');

function fflcommerce_force_ssl_urls($url)
{
	if (is_ssl()) {
		$url = str_replace('http:', 'https:', $url);
	}

	return $url;
}

add_filter('option_siteurl', 'fflcommerce_force_ssl_urls');
add_filter('option_home', 'fflcommerce_force_ssl_urls');
add_filter('option_url', 'fflcommerce_force_ssl_urls');
add_filter('option_wpurl', 'fflcommerce_force_ssl_urls');
add_filter('option_stylesheet_url', 'fflcommerce_force_ssl_urls');
add_filter('option_template_url', 'fflcommerce_force_ssl_urls');
add_filter('script_loader_src', 'fflcommerce_force_ssl_urls');
add_filter('style_loader_src', 'fflcommerce_force_ssl_urls');

function get_fflcommerce_currency_symbol()
{
	$options = FFLCommerce_Base::get_options();
	$currency = $options->get('fflcommerce_currency', 'USD');
	$symbols = fflcommerce::currency_symbols();
	$currency_symbol = $symbols[$currency];

	return apply_filters('fflcommerce_currency_symbol', $currency_symbol, $currency);
}

function fflcommerce_price($price, $args = array())
{
	$options = FFLCommerce_Base::get_options();
	$ex_tax_label = 0;
	$with_currency = true;

	extract(shortcode_atts(array(
		'ex_tax_label' => 0, // 0 for no label, 1 for ex. tax, 2 for inc. tax
		'with_currency' => true
	), $args));

	if ($ex_tax_label === 1) {
		$tax_label = __(' <small>(ex. tax)</small>', 'fflcommerce');
	} else {
		if ($ex_tax_label === 2) {
			$tax_label = __(' <small>(inc. tax)</small>', 'fflcommerce');
		} else {
			$tax_label = '';
		}
	}

	$price = number_format(
		(double)$price,
		(int)$options->get('fflcommerce_price_num_decimals'),
		$options->get('fflcommerce_price_decimal_sep'),
		$options->get('fflcommerce_price_thousand_sep')
	);

	$return = $price;

	if ($with_currency) {
		$currency_pos = $options->get('fflcommerce_currency_pos');
		$currency_symbol = get_fflcommerce_currency_symbol();
		$currency_code = $options->get('fflcommerce_currency');

		switch ($currency_pos) {
			case 'left':
				$return = $currency_symbol.$price;
				break;
			case 'left_space':
				$return = $currency_symbol.' '.$price;
				break;
			case 'right':
				$return = $price.$currency_symbol;
				break;
			case 'right_space':
				$return = $price.' '.$currency_symbol;
				break;
			case 'left_code':
				$return = $currency_code.$price;
				break;
			case 'left_code_space':
				$return = $currency_code.' '.$price;
				break;
			case 'right_code':
				$return = $price.$currency_code;
				break;
			case 'right_code_space':
				$return = $price.' '.$currency_code;
				break;
			case 'code_symbol':
				$return = $currency_code.$price.$currency_symbol;
				break;
			case 'code_symbol_space':
				$return = $currency_code.' '.$price.' '.$currency_symbol;
				break;
			case 'symbol_code':
				$return = $currency_symbol.$price.$currency_code;
				break;
			case 'symbol_code_space':
				$return = $currency_symbol.' '.$price.' '.$currency_code;
				break;
		}

		// only show tax label (ex. tax) if we are going to show the price with currency as well. Otherwise we just want the formatted price
		if ($options->get('fflcommerce_calc_taxes') == 'yes') {
			$return .= $tax_label;
		}
	}

	return apply_filters('fflcommerce_price_display_filter', $return);
}

/** Show variation info if set
 *
 * @param fflcommerce_product $product
 * @param array $variation_data
 * @param bool $flat
 * @return string
 */
function fflcommerce_get_formatted_variation(fflcommerce_product $product, $variation_data = array(), $flat = false)
{
	$return = '';
	if (!is_array($variation_data)) {
		$variation_data = array();
	}

	if ($product instanceof fflcommerce_product_variation) {
		$variation_data = array_merge(array_filter($variation_data), array_filter($product->variation_data));

		if (!$flat) {
			$return = '<dl class="variation">';
		}

		$variation_list = array();
		$added = array();

		foreach ($variation_data as $name => $value) {
			if (empty($value)) {
				continue;
			}

			$name = str_replace('tax_', '', $name);

			if (in_array($name, $added)) {
				continue;
			}

			$added[] = $name;

			if (taxonomy_exists('pa_'.$name)) {
				$terms = get_terms('pa_'.$name, array('orderby' => 'slug', 'hide_empty' => '0'));
				foreach ($terms as $term) {
					if ($term->slug == $value) {
						$value = $term->name;
					}
				}
				$name = get_taxonomy('pa_'.$name)->labels->name;
				$name = $product->attribute_label('pa_'.$name);
			}

			// TODO: if it is a custom text attribute, 'pa_' taxonomies are not created and we
			// have no way to get the 'label' as submitted on the Edit Product->Attributes tab.
			// (don't ask me why not, I don't know, but it seems that we should be creating taxonomies)
			// this function really requires the product passed to it for: $product->attribute_label( $name )
			if ($flat) {
				$variation_list[] = $name.': '.$value;
			} else {
				$variation_list[] = '<dt>'.$name.':</dt><dd>'.$value.'</dd>';
			}
		}

		if ($flat) {
			$return .= implode(', ', $variation_list);
		} else {
			$return .= implode('', $variation_list);
		}

		if (!$flat) {
			$return .= '</dl>';
		}
	}

	return $return;
}

// Remove pingbacks/trackbacks from Comments Feed
// betterwp.net/wordpress-tips/remove-pingbackstrackbacks-from-comments-feed/
add_filter('request', 'fflcommerce_filter_request');
function fflcommerce_filter_request($qv)
{
	if (isset($qv['feed']) && !empty($qv['withcomments'])) {
		add_filter('comment_feed_where', 'fflcommerce_comment_feed_where');
	}

	return $qv;
}

function fflcommerce_comment_feed_where($cwhere)
{
	$cwhere .= " AND comment_type != 'fflcommerce' ";

	return $cwhere;
}

function fflcommerce_let_to_num($v)
{
	$l = substr($v, -1);
	$ret = substr($v, 0, -1);
	switch (strtoupper($l)) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}

	return $ret;
}

function fflcommerce_clean($var)
{
	return strip_tags(stripslashes(trim($var)));
}

// Returns a float value
function fflcommerce_sanitize_num($var)
{
	return strip_tags(stripslashes(floatval(preg_replace('/^[^[\-\+]0-9\.]/', '', $var))));
}

// Author: Sergey Biryukov
// Plugin URI: http://wordpress.org/extend/plugins/allow-cyrillic-usernames/
add_filter('sanitize_user', 'fflcommerce_sanitize_user', 10, 3);
function fflcommerce_sanitize_user($username, $raw_username, $strict)
{
	$username = wp_strip_all_tags($raw_username);
	$username = remove_accents($username);
	$username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
	$username = preg_replace('/&.+?;/', '', $username); // Kill entities

	if ($strict) {
		$username = preg_replace('|[^a-z?-?0-9 _.\-@]|iu', '', $username);
	}

	$username = trim($username);
	$username = preg_replace('|\s+|', ' ', $username);

	return $username;
}

add_action('wp_head', 'fflcommerce_head_version');
function fflcommerce_head_version()
{
	echo '<!-- FFL Commerce Version: '.FFLCOMMERCE_VERSION.' -->'."\n";
}

global $fflcommerce_body_classes;
add_action('wp_head', 'fflcommerce_page_body_classes');
function fflcommerce_page_body_classes()
{
	global $fflcommerce_body_classes;
	$fflcommerce_body_classes = (array)$fflcommerce_body_classes;

	if (is_order_tracker()) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-tracker'));
	}

	if (is_checkout()) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-checkout'));
	}

	if (is_cart()) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-cart'));
	}

	if (is_page(fflcommerce_get_page_id('thanks'))) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-thanks'));
	}
	if (is_page(jigoshop_get_page_id('pay'))) {

		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-pay'));
	}

	if (is_account()) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-myaccount'));
	}
}

function fflcommerce_add_body_class($class = array())
{
	global $fflcommerce_body_classes;
	$fflcommerce_body_classes = (array)$fflcommerce_body_classes;
	$fflcommerce_body_classes = array_unique(array_merge($class, $fflcommerce_body_classes));
}

add_filter('body_class', 'fflcommerce_body_class');
function fflcommerce_body_class($classes)
{
	global $fflcommerce_body_classes;
	$fflcommerce_body_classes = (array)$fflcommerce_body_classes;
	$classes = array_unique(array_merge($classes, $fflcommerce_body_classes));
	return $classes;
}

//### Extra Review Field in comments #########################################################

function fflcommerce_add_comment_rating($comment_id)
{
	if (isset($_POST['rating'])) {
		if (!$_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0) {
			$_POST['rating'] = 5;
		}

		add_comment_meta($comment_id, 'rating', $_POST['rating'], true);
	}
}
add_action('comment_post', 'fflcommerce_add_comment_rating', 1);

function fflcommerce_check_comment_rating($comment_data)
{
	// If posting a comment (not trackback etc) and not logged in
	if (isset($_POST['rating']) && !fflcommerce::verify_nonce('comment_rating')) {
		wp_die(__('You have taken too long. Please go back and refresh the page.', 'fflcommerce'));
	} else if (isset($_POST['rating']) && empty($_POST['rating']) && $comment_data['comment_type'] == '') {
		wp_die(__('Please rate the product.', "fflcommerce"));
		exit;
	}

	return $comment_data;
}
add_filter('preprocess_comment', 'fflcommerce_check_comment_rating', 0);

//### Comments #########################################################

function fflcommerce_comments($comment)
{
	$GLOBALS['comment'] = $comment; ?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">
		<?php echo get_avatar($comment, $size = '60'); ?>
		<div class="comment-text">
			<?php if ($rating = get_comment_meta($comment->comment_ID, 'rating', true)): ?>
				<div class="star-rating" title="<?php echo esc_attr($rating); ?>">
					<span style="width:<?php echo $rating * 16; ?>px"><?php echo $rating; ?> <?php _e('out of 5', 'fflcommerce'); ?></span>
				</div>
			<?php endif; ?>
			<?php if ($comment->comment_approved == '0'): ?>
				<p class="meta"><em><?php _e('Your comment is awaiting approval', 'fflcommerce'); ?></em></p>
			<?php else : ?>
				<p class="meta">
					<?php _e('Rating by', 'fflcommerce'); ?> <strong class="reviewer vcard"><span
							class="fn"><?php comment_author(); ?></span></strong> <?php _e('on', 'fflcommerce'); ?> <?php echo date_i18n(get_option('date_format'), strtotime(get_comment_date('Y-m-d'))); ?>
					:
				</p>
			<?php endif; ?>
			<div class="description"><?php comment_text(); ?></div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
</li>
<?php
}

//### Exclude order comments from front end #########################################################
add_filter('comments_clauses', 'fflcommerce_exclude_order_admin_comments', 10, 1);
function fflcommerce_exclude_order_admin_comments($clauses)
{
	global $wpdb, $typenow, $pagenow;

	// NOTE: bit of a hack, tests if we're in the admin & its an ajax call
	if (is_admin() && ($typenow == 'shop_order' || $pagenow == 'admin-ajax.php') && current_user_can('manage_fflcommerce')) {
		return $clauses; // Don't hide when viewing orders in admin
	}
	if (!$clauses['join']) {
		$clauses['join'] = '';
	}
	if (!strstr($clauses['join'], "JOIN $wpdb->posts")) {
		$clauses['join'] .= " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";
	}
	if ($clauses['where']) {
		$clauses['where'] .= ' AND ';
	}

	$clauses['where'] .= " $wpdb->posts.post_type NOT IN ('shop_order') ";

	return $clauses;
}

/**
 * Support for Import/Export
 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
 */
function fflcommerce_import_start()
{
	global $wpdb;
	$fflcommerce_options = FFLCommerce_Base::get_options();

	$id = (int)$_POST['import_id'];
	$file = get_attached_file($id);

	$parser = new WXR_Parser();
	$import_data = $parser->parse($file);

	if (isset($import_data['posts'])) {
		$posts = $import_data['posts'];

		if ($posts && sizeof($posts) > 0) foreach ($posts as $post) {
			if ($post['post_type'] == 'product') {
				if ($post['terms'] && sizeof($post['terms']) > 0) {
					foreach ($post['terms'] as $term) {
						$domain = $term['domain'];
						if (strstr($domain, 'pa_')) {
							// Make sure it exists!
							if (!taxonomy_exists($domain)) {
								$nicename = sanitize_title(str_replace('pa_', '', $domain));

								$exists_in_db = $wpdb->get_var($wpdb->prepare("SELECT attribute_id FROM ".$wpdb->prefix."fflcommerce_attribute_taxonomies WHERE attribute_name = %s;", $nicename));

								// Create the taxonomy
								if (!$exists_in_db) {
									$wpdb->insert($wpdb->prefix."fflcommerce_attribute_taxonomies", array('attribute_name' => $nicename, 'attribute_type' => 'select'), array('%s', '%s'));
								}

								// Register the taxonomy now so that the import works!
								register_taxonomy($domain,
									array('product'),
									array(
										'hierarchical' => true,
										'labels' => array(
											'name' => $nicename,
											'singular_name' => $nicename,
											'search_items' => __('Search ', 'fflcommerce').$nicename,
											'all_items' => __('All ', 'fflcommerce').$nicename,
											'parent_item' => __('Parent ', 'fflcommerce').$nicename,
											'parent_item_colon' => __('Parent ', 'fflcommerce').$nicename.':',
											'edit_item' => __('Edit ', 'fflcommerce').$nicename,
											'update_item' => __('Update ', 'fflcommerce').$nicename,
											'add_new_item' => __('Add New ', 'fflcommerce').$nicename,
											'new_item_name' => __('New ', 'fflcommerce').$nicename
										),
										'show_ui' => false,
										'query_var' => true,
										'rewrite' => array('slug' => sanitize_title($nicename), 'with_front' => false, 'hierarchical' => true),
									)
								);

								$fflcommerce_options->set('fflcommerce_update_rewrite_rules', '1');
							}
						}
					}
				}
			}
		}
	}
}
add_action('import_start', 'fflcommerce_import_start');

if (!function_exists('fflcommerce_log')) {
	/**
	 * Logs to the debug log when you enable wordpress debug mode.
	 *
	 * @param string $from_class is the name of the php file that you are logging from.
	 * defaults to FFL Commerce if non is supplied.
	 * @param mixed $message this can be a regular string, array or object
	 */
	function fflcommerce_log($message, $from_class = 'fflcommerce')
	{
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log($from_class.': '.print_r($message, true));
			} else {
				error_log($from_class.': '.$message);
			}
		}
	}
}
