<?php

if (!defined('ABSPATH')) {
	exit;
}

class FFLCommerce_Admin_Reports
{
	/**
	 * Handles output of the reports page in admin.
	 */
	public static function output()
	{
		$reports = self::get_reports();
		$first_tab = array_keys($reports);
		$current_tab = !empty($_GET['tab']) ? sanitize_title($_GET['tab']) : $first_tab[0];
		/** @noinspection PhpUnusedLocalVariableInspection */
		$current_report = isset($_GET['report']) ? sanitize_title($_GET['report']) : current(array_keys($reports[$current_tab]['reports']));

		include_once('reports/admin-report.class.php');
		$template = fflcommerce_locate_template('admin/reports/layout');
		/** @noinspection PhpIncludeInspection */
		include_once($template);
	}

	/**
	 * Returns the definitions for the reports to show in admin.
	 *
	 * @return array
	 */
	public static function get_reports()
	{
		$reports = array(
			'orders' => array(
				'title' => __('Orders', 'fflcommerce'),
				'reports' => array(
					'sales_by_date' => array(
						'title' => __('Sales by date', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'sales_by_product' => array(
						'title' => __('Sales by product', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'sales_by_category' => array(
						'title' => __('Sales by category', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'coupon_usage' => array(
						'title' => __('Coupons by date', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
				),
			),
			'customers' => array(
				'title' => __('Customers', 'fflcommerce'),
				'reports' => array(
					'customers' => array(
						'title' => __('Customers vs. Guests', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'customer_list' => array(
						'title' => __('Customer List', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
				),
			),
			'stock' => array(
				'title' => __('Stock', 'fflcommerce'),
				'reports' => array(
					'low_in_stock' => array(
						'title' => __('Low in stock', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'out_of_stock' => array(
						'title' => __('Out of stock', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
					'most_stocked' => array(
						'title' => __('Most Stocked', 'fflcommerce'),
						'description' => '',
						'hide_title' => true,
						'callback' => array(__CLASS__, 'get_report'),
					),
				),
			),
		);

		$reports = apply_filters('fflcommerce_admin_reports', $reports);
		$reports = apply_filters('fflcommerce_reports_charts', $reports); // Backwards compat

		foreach ($reports as $key => $report_group) {
			if (isset($reports[$key]['charts'])) {
				$reports[$key]['reports'] = $reports[$key]['charts'];
			}

			foreach ($reports[$key]['reports'] as $report_key => $report) {
				if (isset($reports[$key]['reports'][$report_key]['function'])) {
					$reports[$key]['reports'][$report_key]['callback'] = $reports[$key]['reports'][$report_key]['function'];
				}
			}
		}

		return $reports;
	}

	public static function get_report($name)
	{
		$name = sanitize_title(str_replace('_', '-', $name));
		$class = 'fflcommerce_Report_'.str_replace('-', '_', $name);

		/** @noinspection PhpIncludeInspection */
		include_once('reports/'.$name.'.class.php');

		if (!class_exists($class)) {
			return;
		}

		/** @var FFLCommerce_Admin_Report $report */
		$report = new $class();
		$report->output();
	}
}
