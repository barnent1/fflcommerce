<?php
/**
 * FFL Commerce Upgrade API
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply,Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */
/**
 * Run FFL Commerce Upgrade functions.
 */
function fflcommerce_upgrade()
{
	// Get the db version
	$fflcommerce_db_version = get_site_option('fflcommerce_db_version');
	if ($fflcommerce_db_version == FFLCOMMERCE_DB_VERSION) {
		return;
	}
	if ($fflcommerce_db_version < 1307110) {
		fflcommerce_upgrade_1_8_0();
	}
	if ($fflcommerce_db_version < 1407060) {
		fflcommerce_upgrade_1_10_0();
	}
	if ($fflcommerce_db_version < 1408200) {
		fflcommerce_upgrade_1_10_3();
	}
	if ($fflcommerce_db_version < 1409050) {
		fflcommerce_upgrade_1_10_6();
	}
	if ($fflcommerce_db_version < 1411270) {
		fflcommerce_upgrade_1_13_3();
	}
	if ($fflcommerce_db_version < 1503040) {
		fflcommerce_upgrade_1_16_0();
	}
	if ($fflcommerce_db_version < 1503180) {
		fflcommmerce_upgrade_1_16_1();
	}
	// Update the db option
	update_site_option('fflcommerce_db_version', FFLCOMMERCE_DB_VERSION);
}

/**
 * Execute changes made in FFL Commerce 1.8
 *
 * @since 1.8
 */
function fflcommerce_upgrade_1_8_0()
{
	FFLCommerce_Base::get_options()->add('fflcommerce_complete_processing_orders', 'no');
}

function fflcommerce_upgrade_1_10_0()
{
	/** @var $wpdb wpdb */
	global $wpdb;
	$data = $wpdb->get_results("SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE 'billing-%' OR meta_key LIKE 'shipping-%'", ARRAY_A);
	if (!empty($data)) {
		$query = "REPLACE INTO {$wpdb->usermeta} VALUES ";
		foreach ($data as $item) {
			$key = str_replace(array('billing-', 'shipping-'), array(
				'billing_',
				'shipping_'
			), $item['meta_key']);
			$query .= "({$item['umeta_id']}, {$item['user_id']}, '{$key}', '{$item['meta_value']}'),";
		}
		unset($data);
		$query = rtrim($query, ',');
		$wpdb->query($query);
	}
	$options = FFLCommerce_Base::get_options();
	$options->add('fflcommerce_address_1', $options->get('fflcommerce_address_line1'));
	$options->add('fflcommerce_address_2', $options->get('fflcommerce_address_line2'));
	$options->delete('fflcommerce_address_line1');
	$options->delete('fflcommerce_address_line2');
	// Set default customer country
	$options->add('fflcommerce_default_country_for_customer', $options->get('fflcommerce_default_country'));
}

function fflcommerce_upgrade_1_10_3()
{
	$options = FFLCommerce_Base::get_options();
	$options->add('fflcommerce_country_base_tax', 'billing_country');
}

function fflcommerce_upgrade_1_10_6()
{
	/** @var WP_Rewrite $wp_rewrite */
	global $wp_rewrite;
	$wp_rewrite->flush_rules(true);
}

function fflcommerce_upgrade_1_13_3()
{
	$args = array(
		'post_type' => 'shop_email',
		'post_status' => 'publish',
	);

	$emails_array = get_posts($args);
	if (empty($emails_array)) {
		do_action('fflcommerce_install_emails');
	}
}

function fflcommerce_upgrade_1_16_0()
{
	wp_insert_term('waiting-for-payment', 'shop_order_status');
}

function fflcommerce_upgrade_1_16_1()
{
	$options = FFLCommerce_Base::get_options();
	$options->add('fflcommerce_enable_html_emails', 'no');
	$options->update_options();

	// Remove unnecessary Shop Cache experiment
	@unlink(FFLCOMMERCE_DIR.'/fflcommerce-shop-cache.php');
}
