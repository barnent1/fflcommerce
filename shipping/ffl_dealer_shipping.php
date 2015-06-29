<?php

/**
 * FFL Dealer Shipping
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
 * 
 */
add_filter('fflcommerce_shipping_methods', function($methods) {
    $methods[] = 'ffl_dealer_shipping';

    return $methods;
}, 10);

class ffl_dealer_shipping extends fflcommerce_shipping_method {

    public function __construct() {
        parent::__construct();

        $this->id = 'ffl_dealer_shipping';
        $this->enabled = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_enabled');
        $this->title = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_title');
        $this->availability = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_availability');
        $this->countries = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_countries');
        $this->type = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_type');
        $this->tax_status = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_tax_status');
        $this->cost = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_cost');
        $this->fee = FFLCommerce_Base::get_options()->get('fflcommerce_ffl_dealer_shipping_handling_fee');

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 9);
    }

    public function calculate_shipping() {
        /** @var \fflcommerce_tax $_tax */
        $_tax = $this->get_tax();
        $this->shipping_total = 0;
        $this->shipping_tax = 0;

        if ($this->type == 'order') { // Shipping for whole order
            $this->shipping_total = $this->cost + $this->get_fee($this->fee, fflcommerce_cart::$cart_contents_total);
            $this->shipping_total = ($this->shipping_total < 0 ? 0 : $this->shipping_total);

            if (FFLCommerce_Base::get_options()->get('fflcommerce_calc_taxes') == 'yes' && $this->tax_status == 'taxable') {
                $this->shipping_tax = $this->calculate_shipping_tax($this->shipping_total);
            }
        } else {
            if (sizeof(fflcommerce_cart::$cart_contents) > 0) {
                foreach (fflcommerce_cart::$cart_contents as $item_id => $values) {
                    /** @var fflcommerce_product $_product */
                    $_product = $values['data'];

                    if ($_product->exists() && $values['quantity'] > 0 && !$_product->is_type('downloadable')) {
                        $item_shipping_price = ($this->cost + $this->get_fee($this->fee, $_product->get_price())) * $values['quantity'];
                        $this->shipping_total = $this->shipping_total + $item_shipping_price;

                        //TODO: need to figure out how to handle per item shipping with discounts that apply to shipping as well
                        // * currently not working. Will need to fix
                        if ($_product->is_shipping_taxable() && $this->tax_status == 'taxable') {
                            $_tax->calculate_shipping_tax($item_shipping_price, $this->id, $_product->get_tax_classes());
                        }
                    }
                }

                $this->shipping_tax = $_tax->get_total_shipping_tax_amount();
            }
        }
    }

    public function admin_scripts() {
        jrto_enqueue_script('admin', 'ffl_dealer_shipping', FFLCOMMERCE_URL . '/assets/js/shipping/flat_rate/admin.js', array('jquery'));
    }

    /**
     * Default Option settings for WordPress Settings API using the FFLCommerce_Options class
     * These should be installed on the FFLCommerce_Options 'Shipping' tab
     */
    protected function get_default_options() {
        return array(
            array(
                'name' => __('FFL Dealer Shipping', 'fflcommerce'),
                'type' => 'title',
                'desc' => __('FFL Dealer Shipping allows you to ship gun purchases to an FFL Dealer in the purchasers state.', 'fflcommerce')
            ),
            array(
                'name' => __('Enable FFL Dealer Shipping', 'fflcommerce'),
                'desc' => '',
                'tip' => '',
                'id' => 'fflcommerce_ffl_dealer_shipping_enabled',
                'std' => 'yes',
                'type' => 'checkbox',
                'choices' => array(
                    'no' => __('No', 'fflcommerce'),
                    'yes' => __('Yes', 'fflcommerce')
                )
            ),
            array(
                'name' => __('Method Title', 'fflcommerce'),
                'desc' => '',
                'tip' => __('This controls the title which the user sees during checkout.', 'fflcommerce'),
                'id' => 'fflcommerce_ffl_dealer_shipping_title',
                'std' => __('FFL Dealer Shipping', 'fflcommerce'),
                'type' => 'text'
            ),
            array(
                'name' => __('Type', 'fflcommerce'),
                'desc' => '',
                'tip' => '',
                'id' => 'fflcommerce_ffl_dealer_shipping_type',
                'std' => 'order',
                'type' => 'radio',
                'choices' => array(
                    'order' => __('Per Order', 'fflcommerce'),
                    'item' => __('Per Item', 'fflcommerce')
                )
            ),
            array(
                'name' => __('Tax Status', 'fflcommerce'),
                'desc' => '',
                'tip' => '',
                'id' => 'fflcommerce_ffl_dealer_shipping_tax_status',
                'std' => 'taxable',
                'type' => 'radio',
                'choices' => array(
                    'taxable' => __('Taxable', 'fflcommerce'),
                    'none' => __('None', 'fflcommerce')
                )
            ),
            array(
                'name' => __('Cost', 'fflcommerce'),
                'desc' => '',
                'type' => 'decimal',
                'tip' => __('Cost excluding tax. Enter an amount, e.g. 2.50.', 'fflcommerce'),
                'id' => 'fflcommerce_ffl_dealer_shipping_cost',
                'std' => '0',
            ),
            array(
                'name' => __('Handling Fee', 'fflcommerce'),
                'desc' => '',
                'type' => 'text',
                'tip' => __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'fflcommerce'),
                'id' => 'fflcommerce_ffl_dealer_shipping_handling_fee',
                'std' => ''
            ),
            array(
                'name' => __('Method available for', 'fflcommerce'),
                'desc' => '',
                'tip' => '',
                'id' => 'fflcommerce_ffl_dealer_shipping_availability',
                'std' => 'all',
                'type' => 'select',
                'choices' => array(
                    'all' => __('All allowed countries', 'fflcommerce'),
                    'specific' => __('Specific Countries', 'fflcommerce')
                )
            ),
            array(
                'name' => __('Specific Countries', 'fflcommerce'),
                'desc' => '',
                'tip' => '',
                'id' => 'fflcommerce_ffl_dealer_shipping_countries',
                'std' => '',
                'type' => 'multi_select_countries'
            ),
        );
    }

}
