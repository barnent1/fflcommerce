<?php
/**
 * FFLCommerce_Options class contains all WordPress options used within FFL Commerce
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package     FFLCommerce
 * @category    Core
 * @author      Tampa Bay Tactical Supply, Inc.
 * @copyright   Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license     GNU General Public License v3
 */

/**
 *  ====================
 *
 * Supported Option Types:
 *      text                    - standard text input (display size 20 chars)
 *      midtext                 - same as text (display size 40 chars)
 *      longtext                - same as text (display size 80 chars)
 *      email                   - same as text (display size 40 chars)
 *      textarea                - same as text (display size 4 rows, 60 cols)
 *      codeblock               - intended for markup and embedded javascript for inclusion elsewhere
 *      natural                 - positive number only, leading 0 allowed (display size 20 chars)
 *      integer                 - integer, positive or negative, no decimals (display size 20 chars)
 *      decimal                 - positive or negative number, may contain decimal point (display size 20 chars)
 *      checkbox                - true or false option type
 *      multicheck              - option grouping allows multiple options for selection (horizontal or vertical display)
 *      select                  - standard select option with pre-defined choices
 *      radio                   - option grouping allowing one option for selection (horizontal or vertical display)
 *      range                   - range slider with min, max, and step values
 *      single_select_page      - select that lists all available WordPress pages with a 'None' choice as well
 *      single_select_country   - select allowing a single choice of all FFL Commerce defined countries
 *      multi_select_countries  - multicheck allowing multiple choices of all FFL Commerce defined countries
 *      user_defined            - a user installed option type, must provide display and option update callbacks
 *
 *  ====================
 *
 *  The Options array uses Tabs for display and each tab begins with a 'tab' option type
 *  Each Tab Heading may be optionally divided into sections defined by a 'title' option type
 *  A Payment Gateway for example, would install itself into a 'tab' and provide a section 'title' with options
 *  List each option sequentially for display under each 'title' or 'tab' option type
 *
 *  Each Option may have any or all of the following items: (for an option, 'id' is MANDATORY and should be unique)
		'tab'           => '',                      - calculated based on position in array
		'section'       => '',                      - calculated based on position in array
		'id'            => null,                    - required
		'type'          => '',                      - required
		'name'          => __( '', 'fflcommerce' ),    - used for Option title in Admin display
		'desc'          => __( '', 'fflcommerce' ),    - option descriptive information appears under the option in Admin
		'tip'           => __( '', 'fflcommerce' ),    - a pop-up tool tip providing help information
		'std'           => '',                      - required, default value for the option
		'choices'       => array(),                 - for selects, radios, etc.
		'class'         => '',                      - any special CSS classes to assign to the options display
		'display'       => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
		'update'        => null,        - call back function for 'user_defined' - array( $this, 'function_name' )
		'extra'         => null,                    - for display and verification - array( 'horizontal' )
 *
 *  ====================
 *
 * Example checkbox option definition:              // Choices should be defined with 'yes' and 'no'
		self::$default_options[] = array(
			'name'		=> __('FFL Commerce Checkbox Testing','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_checkbox_test',
			'type' 		=> 'checkbox',
			'std' 		=> 'yes',
			'choices'	=> array(
				'no'			=> __('No', 'fflcommerce'),
				'yes'			=> __('Yes', 'fflcommerce')
			)
		);
 *
 *  ====================
 *
 * Example range option definition:
		self::$default_options[] = array(
			'name'		=> __('FFL Commerce Range Testing','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_range_test',
			'type' 		=> 'range',
			'std' 		=> 100,
			'extra'		=> array(
				'min'			=> 50,
				'max'			=> 300,
				'step'			=> 5
			)
		);
 *
 *  ====================
 *
 * Example vertical multicheck option definition:
		self::$default_options[] = array(
			'name'		=> __('Display Sidebar on these pages:','fflcommerce'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'fflcommerce_multicheck_test',
			'type' 		=> 'multicheck',
			"std"		=> array('shop' => true,'category' => false,'single' => true,'cart' => false,'checkout' => true,'account' => true),
			"choices"	=> array(
				"shop"			=> "Shop",
				"category"		=> "Product Categories",
				"single"		=> "Single Products",
				"cart"			=> "Cart",
				"checkout"		=> "Checkout",
				"account"		=> "Account Pages",
			),
			'extra'		=> array( 'vertical' )
		);
 *
 */

class FFLCommerce_Options implements FFLCommerce_Options_Interface {
	private static $default_options;
	private static $current_options;
	private $bad_extensions = array();

	/**
	 * Instantiates a new Options object
	 *
	 * @return FFLCommerce_Options
	 * @since  1.3
	 */
	public function __construct(){
		self::$current_options = array();

		$options = get_option(FFLCOMMERCE_OPTIONS);
		if(is_array($options)){
			self::$current_options = $options;
		}
	}

	/**
	 * Updates the database with the current options
	 *
	 * At various times during a page load, options can be set, or added.
	 * We will flush them all out on the WordPress 'shutdown' action hook.
	 *
	 * If options don't exist (fresh install), they are created with default 'true' for WP autoload
	 *
	 * @since	1.3
	 */
	public function update_options(){
		update_option(FFLCOMMERCE_OPTIONS, self::$current_options);
	}

	/**
	 * Adds a named option to our collection
	 *
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set' to actually set an existing option
	 *
	 * @param string $name the name of the option to add
	 * @param mixed	$value the value to set if the option doesn't exist
	 * @since	1.3
	 */
	public function add_option($name, $value){
		$this->add($name, $value);
	}

	/**
	 * Adds a named option
	 * Will do nothing if option already exists to match WordPress behaviour
	 * Use 'set' to actually set an existing option
	 *
	 * @param   string  the name of the option to add
	 * @param   mixed  the value to set if the option doesn't exist
	 * @since  1.12
	 */
	public function add($name, $value)
	{
		$this->get_current_options();
		if(!isset(self::$current_options[$name])){
			self::$current_options[$name] = $value;
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}
		}
	}

	/**
	 * Return the FFL Commerce current options
	 *
	 * @return array the entire current options array is returned
	 * @since	1.3
	 */
	public function get_current_options(){
		if(empty(self::$current_options)){
			if(empty(self::$default_options)){
				$this->set_default_options();
			}
			$this->set_current_options(self::$default_options);
		}

		return self::$current_options;
	}

	/**
	 * Sets the FFL Commerece default options
	 *
	 * This will create the default options array. Extensions may install options of the same format into this.
	 *
	 * @param   none
	 * @return  Void
	 *
	 * @since	1.3
	 *
	 */
	private function set_default_options(){
		$symbols = fflcommerce::currency_symbols();
		$countries = fflcommerce::currency_countries();

		$currencies = array();
		foreach($countries as $key => $country){
			$currencies[$key] = $country.' ('.$symbols[$key].')';
		}
		$currencies = apply_filters('fflcommerce_currencies', $currencies);

		$cSymbol = '';
		if(function_exists('get_fflcommerce_currency_symbol')){
			$cSymbol = get_fflcommerce_currency_symbol();
		}

		$cCode = $this->get('fflcommerce_currency') ? $this->get('fflcommerce_currency') : 'GBP';
		$cSep = $this->get('fflcommerce_price_decimal_sep') ? $this->get('fflcommerce_price_decimal_sep') : '.';

		self::$default_options = array(
			// Shop tab
			array('type' => 'tab', 'name' => __('Shop', 'fflcommerce')),
			array('name' => __('Shop Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Base Country/Region', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This is the base country for your business. Tax rates will be based on this country.', 'fflcommerce'),
				'id' => 'fflcommerce_default_country',
				'type' => 'single_select_country',
			),
			array(
				'name' => __('Default Country/Region for customer', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This is the country for your clients with new accounts.', 'fflcommerce'),
				'id' => 'fflcommerce_default_country_for_customer',
				'std' => $this->get('fflcommerce_default_country'),
				'type' => 'single_select_country',
				'options' => array(
					'add_empty' => true,
				),
			),
			array(
				'name' => __('Currency', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This controls what currency the prices are listed with in the Catalog, and which currency PayPal, and other gateways, will take payments in.', 'fflcommerce'),
				'id' => 'fflcommerce_currency',
				'type' => 'select',
				'choices' => $currencies,
			),
			array(
				'name' => __('Allowed Countries', 'fflcommerce'),
				'desc' => '',
				'tip' => __('These are countries that you are willing to ship to.', 'fflcommerce'),
				'id' => 'fflcommerce_allowed_countries',
				'type' => 'select',
				'choices' => array(
					'all' => __('All Countries', 'fflcommerce'),
					'specific' => __('Specific Countries', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Specific Countries', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_specific_allowed_countries',
				'type' => 'multi_select_countries',
			),
			array(
				'name' => __('Demo store', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.', 'fflcommerce'),
				'id' => 'fflcommerce_demo_store',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array('name' => __('Invoicing', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Company Name', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Setting your company name will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_company_name',
				'type' => 'text',
			),
			array(
				'name' => __('Tax Registration Number', 'fflcommerce'),
				'desc' => __('Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>', 'fflcommerce'),
				'tip' => __('Setting your tax number will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_number',
				'type' => 'text',
			),
			array(
				'name' => __('Address Line1', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_address_1',
				'type' => 'longtext',
			),
			array(
				'name' => __('Address Line2', 'fflcommerce'),
				'desc' => '',
				'tip' => __('If address line1 is not set, address line2 will not display even if you put a value in it. Setting your address will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_address_2',
				'type' => 'longtext',
			),
			array(
				'name' => __('Company Phone', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Setting your company phone number will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_company_phone',
				'type' => 'text',
			),
			array(
				'name' => __('Company Email', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Setting your company email will enable us to print it out on your invoice emails. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_company_email',
				'type' => 'email',
			),
			array('name' => __('Permalinks', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Prepend shop categories and tags with base page', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This will only apply to tags &amp; categories.<br/>Enabled: http://yoursite.com / product_category / YourCategory<br/>Disabled: http://yoursite.com / base_page / product_category / YourCategory', 'fflcommerce'),
				'id' => 'fflcommerce_prepend_shop_page_to_urls',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Prepend product permalinks with shop base page', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_prepend_shop_page_to_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Prepend product permalinks with product category', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_prepend_category_to_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Product category slug', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Slug displayed in product category URLs. Leave blank to use default "product-category"', 'fflcommerce'),
				'id' => 'fflcommerce_product_category_slug',
				'type' => 'text',
			),
			array(
				'name' => __('Product tag slug', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Slug displayed in product tag URLs. Leave blank to use default "product-tag"', 'fflcommerce'),
				'id' => 'fflcommerce_product_tag_slug',
				'type' => 'text',
			),
			// General tab
			array('type' => 'tab', 'name' => __('General', 'fflcommerce')),
			array('name' => __('General Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Cart shows "Return to Shop" button', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Enabling this setting will display a "Return to Shop" button on the Cart page along with the "Continue to Checkout" button.', 'fflcommerce'),
				'id' => 'fflcommerce_cart_shows_shop_button',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('After adding product to cart', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Define what should happen when a user clicks on &#34;Add to Cart&#34; on any product or page.', 'fflcommerce'),
				'id' => 'fflcommerce_redirect_add_to_cart',
				'type' => 'radio',
				'extra' => array('vertical'),
				'choices' => array(
					'same_page' => __('Stay on the same page', 'fflcommerce'),
					'to_checkout' => __('Redirect to Checkout', 'fflcommerce'),
					'to_cart' => __('Redirect to Cart', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Cart status after login', 'fflcommerce'),
				'desc' => __('Current cart <b>always</b> will be loaded if customer logs in checkout page.', 'fflcommerce'),
				'tip' => __("Define what should happen with shopping cart if customer added items to shopping cart as guest and than he logs in to your shop.", 'fflcommerce'),
				'id' => 'fflcommerce_cart_after_login',
				'type' => 'select',
				'choices' => array(
					'load_saved' => __('Load saved cart', 'fflcommerce'),
					'load_current' => __('Load current cart', 'fflcommerce'),
					'merge' => __('Merge saved and current carts', 'fflcommerce'),
				)
			),
			array(
				'name' => __('Reset pending Orders', 'fflcommerce'),
				'desc' => __("Change all 'Pending' Orders older than one month to 'On Hold'", 'fflcommerce'),
				'tip' => __("For customers that have not completed the Checkout process or haven't paid for an Order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!", 'fflcommerce'),
				'id' => 'fflcommerce_reset_pending_orders',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Complete processing Orders', 'fflcommerce'),
				'desc' => __("Change all 'Processing' Orders older than one month to 'Completed'", 'fflcommerce'),
				'tip' => __("For orders that have been completed but the status is still set to 'processing'.  This will move them to a 'completed' status without sending an email out to all the customers.", 'fflcommerce'),
				'id' => 'fflcommerce_complete_processing_orders',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Enforce login for downloads', 'fflcommerce'),
				'desc' => '',
				'tip' => __('If a guest purchases a download, the guest can still download a link without logging in. We recommend disabling guest purchases if you enable this option.', 'fflcommerce'),
				'id' => 'fflcommerce_downloads_require_login',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Disable FFL Commerce frontend.css', 'fflcommerce'),
				'desc' => __('(The next option below will have no effect if this one is disabled)', 'fflcommerce'),
				'tip' => __('Useful if you want to disable FFL Commerce styles and theme it yourself via your theme.', 'fflcommerce'),
				'id' => 'fflcommerce_disable_css',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Include extra theme styles with FFL Commerce frontend.css', 'fflcommerce'),
				'desc' => '',
				'tip' => __("With this option <em>on</em>, FFL Commerce's default frontend.css will still load, and any extra bits found in 'theme/fflcommerce/style.css' for over-rides will also be loaded.", 'fflcommerce'),
				'id' => 'fflcommerce_frontend_with_theme_css',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Disable bundled Lightbox', 'fflcommerce'),
				'desc' => __('Product galleries and images as well as the Add Review form will open in a lightbox.', 'fflcommerce'),
				'tip' => __('Useful if your theme or other plugin already loads our Lightbox script and css (prettyPhoto), or you want to use a different one.', 'fflcommerce'),
				'id' => 'fflcommerce_disable_fancybox',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Use custom product category order', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This option allows to make custom product category order, by drag and drop method.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_draggable_categories',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array('name' => __('FFL Commerce messages', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Message disappear time', 'fflcommerce'),
				'desc' => __('How long message is displayed before disappearing (in ms). Set to 0 to keep it displayed.', 'fflcommerce'),
				'id' => 'fflcommerce_message_disappear_time',
				'type' => 'natural',
			),
			array(
				'name' => __('Error disappear time', 'fflcommerce'),
				'desc' => __('How long error is displayed before disappearing (in ms). Set to 0 to keep it displayed.', 'fflcommerce'),
				'id' => 'fflcommerce_error_disappear_time',
				'type' => 'natural',
			),
			array('name' => __('Email Details', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('FFL Commerce email address', 'fflcommerce'),
				'desc' => '',
				'tip' => __('The email address used to send all FFL Commerce related emails, such as order confirmations and notices.  This may be different than your Company email address on "Shop Tab -> Invoicing".', 'fflcommerce'),
				'id' => 'fflcommerce_email',
				'type' => 'email',
			),
			array(
				'name' => __('Email from name', 'fflcommerce'),
				'desc' => '',
				'tip' => __('', 'fflcommerce'),
				'id' => 'fflcommerce_email_from_name',
				'type' => 'text',
			),
			array(
				'name' => __('Email footer', 'fflcommerce'),
				'desc' => '',
				'tip' => __('The email footer used in all FFL Commerce emails.', 'fflcommerce'),
				'id' => 'fflcommerce_email_footer',
				'type' => 'textarea',
			),
			array(
				'name' => __('Use HTML emails', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This option enables HTML email templates.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_html_emails',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Generate default emails', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_email_generete_defaults',
				'type' => 'user_defined',
				'display' => array($this, 'generate_defaults_emails'),
			),
			array('name' => __('Checkout page', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Validate postal/zip codes', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Enabling this setting will force proper postcodes to be entered by a customer for a country.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_postcode_validating',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Show verify information message', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Enabling this setting will display a message at the bottom of the Checkout asking customers to verify all their informatioin is correctly entered before placing their Order.  This is useful in particular for Countries that have states to ensure the correct shipping state is selected.', 'fflcommerce'),
				'id' => 'fflcommerce_verify_checkout_info_message',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Show EU VAT reduction message', 'fflcommerce'),
				'desc' => __('This will only apply to EU Union based Shops.', 'fflcommerce'),
				'tip' => __('Enabling this setting will display a message at the bottom of the Checkout informing the customer that EU VAT will not be removed until the Order is placed and only if they have provided a valid EU VAT Number.', 'fflcommerce'),
				'id' => 'fflcommerce_eu_vat_reduction_message',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Allow guest purchases', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Enabling this setting will allow users to checkout without registering or signing up. Otherwise, users must be signed in or must sign up to checkout.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_guest_checkout',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Show login form', 'fflcommerce'),
				'desc' => '',
				'id' => 'fflcommerce_enable_guest_login',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Allow registration', 'fflcommerce'),
				'desc' => '',
				'id' => 'fflcommerce_enable_signup_form',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Force SSL on checkout', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This will load your checkout page with https://. An SSL certificate is <strong>required</strong> if you choose yes. Contact your hosting provider for more information on SSL Certs.', 'fflcommerce'),
				'id' => 'fflcommerce_force_ssl_checkout',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array('name' => __('Integration', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('ShareThis Publisher ID', 'fflcommerce'),
				'desc' => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.", 'fflcommerce'),
				'tip' => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.', 'fflcommerce'),
				'id' => 'fflcommerce_sharethis',
				'type' => 'text',
			),
			array(
				'name' => __('Google Analytics ID', 'fflcommerce'),
				'desc' => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'fflcommerce'),
				'id' => 'fflcommerce_ga_id',
				'type' => 'text',
			),
			array(
				'name' => __('Enable eCommerce Tracking', 'fflcommerce'),
				'tip' => __('Add Google Analytics eCommerce tracking code upon successful orders', 'fflcommerce'),
				'desc' => __('<a href="//support.google.com/analytics/bin/answer.py?hl=en&answer=1009612">Learn how to enable</a> eCommerce tracking for your Google Analytics account.', 'fflcommerce'),
				'id' => 'fflcommerce_ga_ecommerce_tracking_enabled',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			// Pages tab
			array('type' => 'tab', 'name' => __('Pages', 'fflcommerce')),
			array('name' => __('Page configurations', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Cart Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_cart]</code>', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_cart_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Checkout Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_checkout]</code>', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_checkout_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Pay Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_pay]</code><br/>Default parent page: Checkout', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_pay_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Thanks Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_thankyou]</code><br/>Default parent page: Checkout', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_thanks_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('My Account Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_my_account]</code>', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_myaccount_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Edit Address Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_edit_address]</code><br/>Default parent page: My Account', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_edit_address_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('View Order Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_view_order]</code><br/>Default parent page: My Account', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_view_order_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Change Password Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_change_password]</code><br/>Default parent page: My Account', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_change_password_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Track Order Page', 'fflcommerce'),
				'desc' => __('Shortcode to place on page: <code>[fflcommerce_order_tracking]</code>', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_track_order_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Terms Page', 'fflcommerce'),
				'desc' => __('If you define a &#34;Terms&#34; page the customer will be asked to accept it before allowing them to place their order.', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_terms_page_id',
				'type' => 'single_select_page',
				'extra' => 'show_option_none='.__('None', 'fflcommerce'),
			),
			// Catalog & Pricing tab
			array('type' => 'tab', 'name' => __('Catalog &amp; Pricing', 'fflcommerce')),
			array('name' => __('Catalog Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Catalog base page', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This sets the base page of your shop. You should not change this value once you have launched your site otherwise you risk breaking urls of other sites pointing to yours, etc.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Shop redirection page', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This will point users to the page you set for buttons like `Return to shop` or `Continue Shopping`.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_redirect_page_id',
				'type' => 'single_select_page',
			),
			array(
				'name' => __('Catalog product buttons show', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This will determine the type of button and the action it will use when clicked on the Shop and Category product listings.  You can also set it to use no button.', 'fflcommerce'),
				'id' => 'fflcommerce_catalog_product_button',
				'type' => 'radio',
				'choices' => array(
					'add' => __('Add to Cart', 'fflcommerce'),
					'view' => __('View Product', 'fflcommerce'),
					'none' => __('No Button', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Sort products in catalog by', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Determines the display sort order of products for the Shop, Categories, and Tag pages.', 'fflcommerce'),
				'id' => 'fflcommerce_catalog_sort_orderby',
				'type' => 'radio',
				'choices' => array(
					'post_date' => __('Creation Date', 'fflcommerce'),
					'title' => __('Product Title', 'fflcommerce'),
					'menu_order' => __('Product Post Order', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Catalog sort direction', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Determines whether the catalog sort orderby is ascending or descending.', 'fflcommerce'),
				'id' => 'fflcommerce_catalog_sort_direction',
				'type' => 'radio',
				'choices' => array(
					'asc' => __('Ascending', 'fflcommerce'),
					'desc' => __('Descending', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Catalog products per row', 'fflcommerce'),
				'desc' => __('Default = 3', 'fflcommerce'),
				'tip' => __('Determines how many products to show on one display row for Shop, Category and Tag pages.', 'fflcommerce'),
				'id' => 'fflcommerce_catalog_columns',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1,
				),
			),
			array(
				'name' => __('Catalog products per page', 'fflcommerce'),
				'desc' => __('Default = 12', 'fflcommerce'),
				'tip' => __('Determines how many products to display on Shop, Category and Tag pages before needing next and previous page navigation.', 'fflcommerce'),
				'id' => 'fflcommerce_catalog_per_page',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 100,
					'step' => 1,
				),
			),
			array('name' => __('Pricing Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Show prices with tax', 'fflcommerce'),
				'desc' => __("This controls the display of the product price in cart and checkout page.", 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_show_prices_with_tax',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Currency display', 'fflcommerce'),
				'desc' => __("This controls the display of the currency symbol and currency code.", 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_currency_pos',
				'type' => 'select',
				'choices' => array(
					'left' => sprintf('%1$s0%2$s00', $cSymbol, $cSep),// symbol.'0'.separator.'00'
					'left_space' => sprintf('%1$s0 %2$s00', $cSymbol, $cSep),// symbol.' 0'.separator.'00'
					'right' => sprintf('0%2$s00%1$s', $cSymbol, $cSep),// '0'.separator.'00'.symbol
					'right_space' => sprintf('0%2$s00 %1$s', $cSymbol, $cSep),// '0'.separator.'00 '.symbol
					'left_code' => sprintf('%1$s0%2$s00', $cCode, $cSep),// code.'0'.separator.'00'
					'left_code_space' => sprintf('%1$s 0%2$s00', $cCode, $cSep),// code.' 0'.separator.'00'
					'right_code' => sprintf('0%2$s00%1$s', $cCode, $cSep),// '0'.separator.'00'.code
					'right_code_space' => sprintf('0%2$s00 %1$s', $cCode, $cSep),// '0'.separator.'00 '.code
					'symbol_code' => sprintf('%1$s0%2$s00%3$s', $cSymbol, $cSep, $cCode),// symbol.'0'.separator.'00'.code
					'symbol_code_space' => sprintf('%1$s 0%2$s00 %3$s', $cSymbol, $cSep, $cCode),// symbol.' 0'.separator.'00 '.code
					'code_symbol' => sprintf('%3$s0%2$s00%1$s', $cSymbol, $cSep, $cCode),// code.'0'.separator.'00'.symbol
					'code_symbol_space' => sprintf('%3$s 0%2$s00 %1$s', $cSymbol, $cSep, $cCode),// code.' 0'.separator.'00 '.symbol
				)
			),
			array(
				'name' => __('Thousand separator', 'fflcommerce'),
				'desc' => __('This sets the thousand separator of displayed prices.', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_price_thousand_sep',
				'type' => 'text',
			),
			array(
				'name' => __('Decimal separator', 'fflcommerce'),
				'desc' => __('This sets the decimal separator of displayed prices.', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_price_decimal_sep',
				'type' => 'text',
			),
			array(
				'name' => __('Number of decimals', 'fflcommerce'),
				'desc' => __('This sets the number of decimal points shown in displayed prices.', 'fflcommerce'),
				'tip' => '',
				'id' => 'fflcommerce_price_num_decimals',
				'type' => 'natural',
			),
			// Images tab
			array('type' => 'tab', 'name' => __('Images', 'fflcommerce')),
			array(
				'name' => __('Image Options', 'fflcommerce'),
				'type' => 'title',
				'desc' => sprintf(__('<p>Changing any of these settings will affect the dimensions of images used in your Shop. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.</p><p>Crop: Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it). Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).</p><p><strong>Note:</strong> Your images may not display in the size you choose below. This is because they may still be affected by CSS styles in your theme.', 'fflcommerce'), 'https://wordpress.org/plugins/regenerate-thumbnails/')
			),
			array('name' => __('Cropping Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Crop Tiny images', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'fflcommerce'),
				'id' => 'fflcommerce_use_wordpress_tiny_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Crop Thumbnail images', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'fflcommerce'),
				'id' => 'fflcommerce_use_wordpress_thumbnail_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Crop Catalog images', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'fflcommerce'),
				'id' => 'fflcommerce_use_wordpress_catalog_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Crop Large images', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Use No to set the image size by resizing the image proportionally (that is, without distorting it).<br />Use Yes to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'fflcommerce'),
				'id' => 'fflcommerce_use_wordpress_featured_crop',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array('name' => __('Image Sizes', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Tiny Image Width', 'fflcommerce'),
				'desc' => __('Default = 36px', 'fflcommerce'),
				'tip' => __('Set the width of the small image used in the Cart, Checkout, Orders and Widgets.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_tiny_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Tiny Image Height', 'fflcommerce'),
				'desc' => __('Default = 36px', 'fflcommerce'),
				'tip' => __('Set the height of the small image used in the Cart, Checkout, Orders and Widgets.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_tiny_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Thumbnail Image Width', 'fflcommerce'),
				'desc' => __('Default = 90px', 'fflcommerce'),
				'tip' => __('Set the width of the thumbnail image for Single Product page extra images.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_thumbnail_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Thumbnail Image Height', 'fflcommerce'),
				'desc' => __('Default = 90px', 'fflcommerce'),
				'tip' => __('Set the height of the thumbnail image for Single Product page extra images.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_thumbnail_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Catalog Image Width', 'fflcommerce'),
				'desc' => __('Default = 150px', 'fflcommerce'),
				'tip' => __('Set the width of the catalog image for Shop, Categories, Tags, and Related Products.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_small_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Catalog Image Height', 'fflcommerce'),
				'desc' => __('Default = 150px', 'fflcommerce'),
				'tip' => __('Set the height of the catalog image for Shop, Categories, Tags, and Related Products.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_small_h',
				'type' => 'natural',
			),
			array(
				'name' => __('Large Image Width', 'fflcommerce'),
				'desc' => __('Default = 300px', 'fflcommerce'),
				'tip' => __('Set the width of the Single Product page large or Featured image.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_large_w',
				'type' => 'natural',
			),
			array(
				'name' => __('Large Image Height', 'fflcommerce'),
				'desc' => __('Default = 300px', 'fflcommerce'),
				'tip' => __('Set the height of the Single Product page large or Featured image.', 'fflcommerce'),
				'id' => 'fflcommerce_shop_large_h',
				'type' => 'natural',
			),
			// Products & Inventory tab
			array('type' => 'tab', 'name' => __('Products & Inventory', 'fflcommerce')),
			array('name' => __('Product Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable SKU field', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Turning off the SKU field will give products an SKU of their post id.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_sku',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Enable weight field', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_enable_weight',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Weight Unit', 'fflcommerce'),
				'desc' => '',
				'tip' => __("This controls what unit you will define weights in.", 'fflcommerce'),
				'id' => 'fflcommerce_weight_unit',
				'type' => 'radio',
				'choices' => array(
					'kg' => __('Kilograms', 'fflcommerce'),
					'lbs' => __('Pounds', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Enable product dimensions', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_enable_dimensions',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Dimensions Unit', 'fflcommerce'),
				'desc' => '',
				'tip' => __('This controls what unit you will define dimensions in.', 'fflcommerce'),
				'id' => 'fflcommerce_dimension_unit',
				'type' => 'radio',
				'choices' => array(
					'cm' => __('centimeters', 'fflcommerce'),
					'in' => __('inches', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Product thumbnail images per row', 'fflcommerce'),
				'desc' => __('Default = 3', 'fflcommerce'),
				'tip' => __('Determines how many extra product thumbnail images attached to a product to show on one row for the Single Product page.', 'fflcommerce'),
				'id' => 'fflcommerce_product_thumbnail_columns',
				'type' => 'number',
				'extra' => array(
					'min' => 1,
					'max' => 10,
					'step' => 1,
				),
			),
			array(
				'name' => __('Show related products', 'fflcommerce'),
				'desc' => '',
				'tip' => __('To show or hide the related products section on a single product page.', 'fflcommerce'),
				'id' => 'fflcommerce_enable_related_products',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array('name' => __('Inventory Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Manage stock', 'fflcommerce'),
				'desc' => __('If you are not managing stock, turn it off here to disable it in admin and on the front-end.', 'fflcommerce'),
				'tip' => __('You can manage stock on a per-item basis if you leave this option on.', 'fflcommerce'),
				'id' => 'fflcommerce_manage_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Show stock amounts', 'fflcommerce'),
				'desc' => '',
				'tip' => __('Set to yes to allow customers to view the amount of stock available for a product.', 'fflcommerce'),
				'id' => 'fflcommerce_show_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Notify on low stock', 'fflcommerce'),
				'desc' => '',
				'id' => 'fflcommerce_notify_low_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Low stock threshold', 'fflcommerce'),
				'desc' => '',
				'tip' => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'fflcommerce'),
				'id' => 'fflcommerce_notify_low_stock_amount',
				'type' => 'natural',
				'std' => '2',
			),
			array(
				'name' => __('Notify on out of stock', 'fflcommerce'),
				'desc' => '',
				'id' => 'fflcommerce_notify_no_stock',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Out of stock threshold', 'fflcommerce'),
				'desc' => '',
				'tip' => __('You will receive a notification as soon this threshold is hit (if notifications are turned on).', 'fflcommerce'),
				'id' => 'fflcommerce_notify_no_stock_amount',
				'type' => 'natural',
			),
			array(
				'name' => __('Hide out of stock products', 'fflcommerce'),
				'desc' => '',
				'tip' => __('For Yes: When the Out of Stock Threshold (above) is reached, the product visibility will be set to hidden so that it will not appear on the Catalog or Shop product lists.', 'fflcommerce'),
				'id' => 'fflcommerce_hide_no_stock_product',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			// Tax tab
			array('type' => 'tab', 'name' => __('Tax', 'fflcommerce')),
			array('name' => __('Tax Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Calculate Taxes', 'fflcommerce'),
				'desc' => __('Only turn this off if you are exclusively selling non-taxable items.', 'fflcommerce'),
				'tip' => __('If you are not calculating taxes then you can ignore all other tax options.', 'fflcommerce'),
				'id' => 'fflcommerce_calc_taxes',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Apply Taxes After Coupon', 'fflcommerce'),
				'desc' => __('This will have no effect if Calculate Taxes is turned off.', 'fflcommerce'),
				'tip' => __('If yes, taxes get applied after coupons. When no, taxes get applied before coupons.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_after_coupon',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Catalog Prices include tax?', 'fflcommerce'),
				'desc' => __('This will only apply to the Shop, Category and Product pages.', 'fflcommerce'),
				'tip' => __('This will have no effect on the Cart, Checkout, Emails, or final Orders; prices are always shown with tax out.', 'fflcommerce'),
				'id' => 'fflcommerce_prices_include_tax',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Country to base taxes on', 'fflcommerce'),
				'desc' => __('This option defines whether to use billing or shipping address to calculate taxes.', 'fflcommerce'),
				'id' => 'fflcommerce_country_base_tax',
				'type' => 'select',
				'choices' => array(
					'billing_country' => __('Billing', 'fflcommerce'),
					'shipping_country' => __('Shipping', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Additional Tax classes', 'fflcommerce'),
				'desc' => __('List 1 per line. This is in addition to the default <em>Standard Rate</em>.', 'fflcommerce'),
				'tip' => __('List product and shipping tax classes here, e.g. Zero Tax, Reduced Rate.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_classes',
				'type' => 'textarea',
			),
			array(
				'name' => __('Tax rates', 'fflcommerce'),
				'desc' => '',
				'tip' => __('To avoid rounding errors, insert tax rates with 4 decimal places.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_rates',
				'type' => 'tax_rates',
			),
			array('name' => __('Default options for new products', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Tax status', 'fflcommerce'),
				'tip' => __('Whether new products should be taxable by default.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_defaults_status',
				'type' => 'select',
				'std' => 'taxable',
				'choices' => array(
					'taxable' => __('Taxable', 'fflcommerce'),
					'shipping' => __('Shipping', 'fflcommerce'),
					'none' => __('None', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Tax classes', 'fflcommerce'),
				'tip' => __('List of tax classes added by default to new products.', 'fflcommerce'),
				'id' => 'fflcommerce_tax_defaults_classes',
				'type' => 'user_defined',
				'display' => array($this, 'display_default_tax_classes'),
				'update' => array($this, 'update_default_tax_classes'),
			),
			// Shipping tab
			array('type' => 'tab', 'name' => __('Shipping', 'fflcommerce')),
			array('name' => __('Shipping Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable Shipping', 'fflcommerce'),
				'desc' => __('Only turn this off if you are <strong>not</strong> shipping items, or items have shipping costs included.', 'fflcommerce'),
				'tip' => __('If turned off, this will also remove shipping address fields on the Checkout.', 'fflcommerce'),
				'id' => 'fflcommerce_calc_shipping',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Enable shipping calculator on cart', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_enable_shipping_calc',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Only ship to billing address?', 'fflcommerce'),
				'desc' => '',
				'tip' => __('When activated, Shipping address fields will not appear on the Checkout.', 'fflcommerce'),
				'id' => 'fflcommerce_ship_to_billing_address_only',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Checkout always shows Shipping fields?', 'fflcommerce'),
				'desc' => __('This will have no effect if "Only ship to billing address" is activated.', 'fflcommerce'),
				'tip' => __('When activated, Shipping address fields will appear by default on the Checkout.', 'fflcommerce'),
				'id' => 'fflcommerce_show_checkout_shipping_fields',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'fflcommerce'),
					'yes' => __('Yes', 'fflcommerce'),
				),
			),
			array(
				'name' => __('Available Shipping Methods', 'fflcommerce'),
				'type' => 'title',
				'desc' => __('Please enable all of the Shipping Methods you wish to make available to your customers.', 'fflcommerce'),
			),
			// Payment Gateways tab
			array('type' => 'tab', 'name' => __('Payment Gateways', 'fflcommerce')),
			array('name' => __('Gateway Options', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Default Gateway', 'fflcommerce'),
				'desc' => __('Only enabled gateways will appear in this list.', 'fflcommerce'),
				'tip' => __('This will determine which gateway appears first in the Payment Methods list on the Checkout.', 'fflcommerce'),
				'id' => 'fflcommerce_default_gateway',
				'type' => 'default_gateway',
				'choices' => apply_filters('fflcommerce_available_payment_gateways', array()),
			),
			array(
				'name' => __('Available gateways', 'fflcommerce'),
				'type' => 'title',
				'desc' => __('Please enable all of the Payment Gateways you wish to make available to your customers.', 'fflcommerce'),
			),
		);
	}

	/**
	 * Returns a named FFL Commerce option
	 *
	 * @param   string  the name of the option to retrieve
	 * @param   mixed  the value to return if the option doesn't exist
	 * @return  mixed  the value of the option, null if no $default and option doesn't exist
	 * @since  1.12
	 */
	public function get($name, $default = null)
	{
		if(isset(self::$current_options[$name])){
			return apply_filters('fflcommerce_get_option', self::$current_options[$name], $name, $default);
		} elseif(($old_option = get_option($name)) !== false){
			return apply_filters('fflcommerce_get_option', $old_option, $name, $default);
		} elseif(isset($default)){
			return apply_filters('fflcommerce_get_option', $default, $name, $default);
		} else {
			return null;
		}
	}

	/**
	 * Sets the entire FFL Commerce current options
	 *
	 * @param array $options an array containing all the current FFL Commerce option => value pairs to use
	 * @since 1.3
	 */
	private function set_current_options($options){
		self::$current_options = $options;
		if(!has_action('shutdown', array($this, 'update_options'))){
			add_action('shutdown', array($this, 'update_options'));
		}
	}

	/**
	 * Returns a named FFL Commerce option
	 *
	 * @param string $name the name of the option to retrieve
	 * @param mixed $default the value to return if the option doesn't exist
	 * @return mixed the value of the option, null if no $default and doesn't exist
	 * @since  1.3
	 */
	public function get_option($name, $default = null){
		return $this->get($name, $default);
	}

	/**
	 * Sets a named FFL Commerce option
	 *
	 * @param string $name the name of the option to set
	 * @param	mixed	$value the value to set
	 * @since	1.3
	 */
	public function set_option($name, $value){
		$this->set($name, $value);
	}

	/**
	 * Sets a named FFL Commerce option
	 *
	 * @param   string  the name of the option to set
	 * @param  mixed  the value to set
	 * @since  1.12
	 */
	public function set($name, $value)
	{
		$this->get_current_options();

		if(isset($name)){
			self::$current_options[$name] = $value;
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}
		}
	}

	/**
	 * Deletes a named FFL Commerce option
	 *
	 * @param string $name the name of the option to delete
	 * @return bool true for successful completion if option found, false otherwise
	 * @since	1.3
	 */
	public function delete_option($name){
		return $this->delete($name);
	}

	/**
	 * Deletes a named FFL Commerce option
	 *
	 * @param   string  the name of the option to delete
	 * @return  bool  true for successful completion if option found, false otherwise
	 * @since  1.12
	 */
	public function delete($name)
	{
		$this->get_current_options();
		if(isset($name)){
			unset(self::$current_options[$name]);
			if(!has_action('shutdown', array($this, 'update_options'))){
				add_action('shutdown', array($this, 'update_options'));
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines whether an Option exists
	 *
	 * @param string $name Option name.
	 * @return bool true for successful completion if option found, false otherwise
	 * @since 1.3
	 */
	public function exists_option($name){
		return $this->exists($name);
	}

	/**
	 * Determines whether an Option exists
	 *
	 * @param $name string the name of option to check for existence
	 * @return  bool  true for successful completion if option found, false otherwise
	 * @since  1.12
	 */
	public function exists($name)
	{
		$this->get_current_options();
		if(isset(self::$current_options[$name])){
			return true;
		}

		return false;
	}

	/**
	 * Install additional Tab's to FFL Commerce Options
	 * Extensions would use this to add a new Tab for their own options
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for FFL Commerce language translations to function properly
	 *
	 * @param	string $tab The name of the Tab ('tab'), eg. 'My Extension'
	 * @param	array	$options The array of options to install onto this tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_tab($tab, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($tab)){
			return;
		}

		$our_options = $this->get_default_options();
		$our_options[] = array('type' => 'tab', 'name' => $tab);

		if(!empty($options)){
			foreach($options as $option){
				if(isset($option['id']) && !$this->exists($option['id'])){
					$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
				}
				$our_options[] = $option;
			}
		}

		self::$default_options = $our_options;
	}

	/**
	 * Return the FFL Commerce default options
	 *
	 * @return  array  the entire default options array is returned
	 * @since  1.3
	 */
	public function get_default_options(){
		if(empty(self::$default_options)){
			$this->set_default_options();
		}

		return self::$default_options;
	}

	/**
	 * Install additional default options for parsing onto a specific Tab
	 * Shipping methods, Payment gateways and Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for FFL Commerce language translations to function properly
	 *
	 * @param	string $tab The name of the Tab ('tab') to install onto
	 * @param	array	$options The array of options to install at the end of the current options on this Tab
	 *
	 * @since	1.3
	 */
	public function install_external_options_onto_tab($tab, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($tab)){
			return;
		}

		$our_options = $this->get_default_options();
		$first_index = -1;
		$second_index = -1;
		foreach($our_options as $index => $option){
			if($option['type'] <> 'tab'){
				continue;
			}
			if($option['name'] == $tab){
				$first_index = $index;
				continue;
			}
			if($first_index >= 0){
				$second_index = $index;
				break;
			}
		}

		if($second_index < 0){
			$second_index = count($our_options);
		}

		/*** get the start of the array ***/
		$start = array_slice($our_options, 0, $second_index);
		/*** get the end of the array ***/
		$end = array_slice($our_options, $second_index);
		/*** add the new elements to the array ***/
		foreach($options as $option){
			if(isset($option['id']) && !$this->exists($option['id'])){
				$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
			}
			$start[] = $option;
		}

		/*** glue them back together ***/
		self::$default_options = array_merge($start, $end);
	}

	/**
	 * Install additional default options for parsing after a specific option ID
	 * Extensions would use this
	 *
	 * NOTE: External code should not call this function any earlier than the WordPress 'init'
	 *       action hook in order for FFL Commerce language translations to function properly
	 *
	 * @param	string $insert_after_id	The name of the ID  to install -after-
	 * @param	array	$options The array of options to install
	 * @since	1.3
	 */
	public function install_external_options_after_id($insert_after_id, $options){
		// only proceed with function if we have options to add
		if(empty($options)){
			return;
		}
		if(empty($insert_after_id)){
			return;
		}

		$our_options = $this->get_default_options();
		$first_index = -1;
		foreach($our_options as $index => $option){
			if(!isset($option['id']) || $option['id'] <> $insert_after_id){
				continue;
			}
			$first_index = $index;
			break;
		}

		/*** get the start of the array ***/
		$start = array_slice($our_options, 0, $first_index + 1);
		/*** get the end of the array ***/
		$end = array_slice($our_options, $first_index + 1);
		/*** add the new elements to the array ***/
		foreach($options as $option){
			if(isset($option['id']) && !$this->exists($option['id'])){
				$this->add($option['id'], isset($option['std']) ? $option['std'] : '');
			}
			$start[] = $option;
		}

		/*** glue them back together ***/
		self::$default_options = array_merge($start, $end);
	}

	public function generate_defaults_emails(){
		return '<button type="button" onClick="parent.location=\'admin.php?page=fflcommerce_settings&tab=general&install_emails=1\'">'.__('Generate Defaults', 'fflcommerce').'</button>';
	}

	public function display_default_tax_classes()
	{
		$tax = new fflcommerce_tax();
		$classes = $tax->get_tax_classes();
		$defaults = FFLCommerce_Base::get_options()->get('fflcommerce_tax_defaults_classes', array('*'));

		ob_start();
		//We don't want any notices here.
		$old_status = error_reporting(0);
		echo FFLCommerce_Forms::checkbox(array(
			'id' => 'fflcommerce_tax_defaults_class_standard',
			'name' => 'fflcommerce_tax_defaults_classes[*]',
			'label' => __('Standard', 'fflcommerce'),
			'value' => in_array('*', $defaults),
		));

		foreach ($classes as $class) {
			$value = sanitize_title($class);
			echo FFLCommerce_Forms::checkbox(array(
				'id' => 'fflcommerce_tax_defaults_class_'.$value,
				'name' => 'fflcommerce_tax_defaults_classes['.$value.']',
				'label' => __($class, 'fflcommerce'),
				'value' => in_array($value, $defaults),
			));
		}
		error_reporting($old_status);

		return ob_get_clean();
	}

	public function update_default_tax_classes()
	{
		if (!isset($_POST['fflcommerce_tax_defaults_classes'])) {
			return array();
		}

		$classes = array();
		foreach ($_POST['fflcommerce_tax_defaults_classes'] as $class => $value) {
			$classes[] = $class;
		}

		return $classes;
	}

	public function fflcommerce_deprecated_options(){
		echo '<div class="error"><p>'.sprintf(__('The following items, from one or more extensions, have tried to add FFL Commerce Settings in a manner that is no longer supported as of FFL Commerce 1.3. (%s)', 'fflcommerce').'</p></div>', implode(', ', $this->bad_extensions));
	}
}
