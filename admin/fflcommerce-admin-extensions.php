<?php
if (!defined('ABSPATH')) {
	exit;
}

class FFLCommerce_Admin_Extensions
{
	/**
	 * Handles output of the reports page in admin.
	 */
	public static function output()
	{
		if (false === ($addons = get_transient('fflcommerce_extensions_data'))) {
			$addons_json = wp_remote_get('http://www.fflcommerce.com/products.json');

			if (!is_wp_error($addons_json)) {
				$addons = json_decode(wp_remote_retrieve_body($addons_json));

				if ($addons) {
					set_transient('fflcommerce_extensions_data', $addons, 7*24*3600); // One week
				}
			}
		}

		$template = fflcommerce_locate_template('admin/extensions');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}
}
