<?php
/**
 * WordPress Cron Tasks
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


class fflcommerce_cron extends FFLCommerce_Base {


	function __construct () {

		$this->fflcommerce_schedule_events();
		add_action( 'fflcommerce_cron_pending_orders', array( $this, 'fflcommerce_update_pending_orders' ) );
		add_action( 'fflcommerce_cron_processing_orders', array( $this, 'fflcommerce_complete_processing_orders' ));

	}

	function fflcommerce_schedule_events() {
		if ( ! wp_next_scheduled( 'fflcommerce_cron_pending_orders' ) ) {
			wp_schedule_event( time(), 'daily', 'fflcommerce_cron_pending_orders' );
		}

		if ( ! wp_next_scheduled( 'fflcommerce_cron_processing_orders' ) ) {
			wp_schedule_event( time(), 'daily', 'fflcommerce_cron_processing_orders' );
		}
	}


	function fflcommerce_update_pending_orders() {

		if ( self::get_options()->get( 'fflcommerce_reset_pending_orders' ) == 'yes' ) {

			add_filter( 'posts_where', array( $this, 'orders_filter_when' ));

			$orders = get_posts( array(

				'post_status'       => 'publish',
				'post_type'         => 'shop_order',
				'shop_order_status' => 'pending',
				'suppress_filters'  => false,
				'fields'            => 'ids',

			));

			remove_filter( 'posts_where', array( $this, 'orders_filter_when' ));
			fflcommerce_emails::suppress_next_action();

			foreach ( $orders as $index => $order_id ) {
				$order = new fflcommerce_order( $order_id );
				$order->update_status( 'on-hold', __('Archived due to order being in pending state for a month or longer.', 'fflcommerce'));
			}
		}

	}

	function fflcommerce_complete_processing_orders() {

		if ( self::get_options()->get( 'fflcommerce_complete_processing_orders' ) == 'yes' ) {

			add_filter( 'posts_where', array( $this, 'orders_filter_when' ));

			$orders = get_posts( array(
				'post_status'       => 'publish',
				'post_type'         => 'shop_order',
				'shop_order_status' => 'processing',
				'suppress_filters'  => false,
				'fields'            => 'ids',
			));

			remove_filter( 'posts_where', array( $this, 'orders_filter_when' ));
			fflcommerce_emails::suppress_next_action();

			foreach ( $orders as $index => $order_id ) {
				$order = new fflcommerce_order( $order_id );
				$order->update_status( 'completed', __('Completed due to order being in processing state for a month or longer.', 'fflcommerce'));
			}
		}
	}

	function orders_filter_when( $when = '' ) {
		$when .= " AND post_date < '" . date('Y-m-d', strtotime('-30 days')) . "'";
		return $when;

	}
}
