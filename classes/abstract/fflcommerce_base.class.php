<?php
/**
 * Abstract Class that should be extended by most FFL Commerce classes providing useful methods
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

include_once (dirname(dirname(__FILE__)) . '/fflcommerce_options_interface.php');

abstract class FFLCommerce_Base {

    private static $fflcommerce_options;

	/**
	 * Wrapper to WordPress add_action() function
	 * adds the necessary class address on the function passed for WordPress to use
	 *
	 * @param string $tag - the action hook name
	 * @param callback $function_to_add - the function name to add to the action hook
	 * @param int $priority - the priority of the function to add to the action hook
	 * @param int $accepted_args - the number of arguments to pass to the function to add
	 *
	 * @since 0.9.9.2
	 */
	protected function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return add_action( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
	}


	/**
	 * Wrapper to WordPress add_filter() function
	 * adds the necessary class address on the function passed for WordPress to use
	 *
	 * @param string $tag - the filter hook name
	 * @param callback $function_to_add - the function name to add to the filter hook
	 * @param int $priority - the priority of the function to add to the filter hook
	 * @param int $accepted_args - the number of arguments to pass to the filter to add
	 *
	 * @since 0.9.9.2
	 */
	protected function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return add_filter( $tag, array( $this, $function_to_add ), $priority, $accepted_args );
	}

    /**
     * Allow FFL Commerce options to be injected into the class. Any implementation of
     * FFLCommerce_Options_Interface can be injected
     *
     * @param FFLCommerce_Options_Interface $fflcommerce_options the options to use on the classes
     */
    protected static function set_options(FFLCommerce_Options_Interface $fflcommerce_options) {
        self::$fflcommerce_options = $fflcommerce_options;
    }

    /**
     * helper function for any files that do not inherit fflcommerce_base, they can access fflcommerce_options
     * @return FFLCommerce_Options_Interface the options that have been set, or null if they haven't been set yet
     */
    public static function get_options() {

        // default options to FFLCommerce_Options if they haven't been set
        if (self::$fflcommerce_options == null) :
            self::$fflcommerce_options = new FFLCommerce_Options();
        endif;

        return self::$fflcommerce_options;

    }

}