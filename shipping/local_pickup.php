<?php
/**
 * Local pickup
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

add_filter('fflcommerce_shipping_methods', function($methods){
	$methods[] = 'local_pickup';

	return $methods;
}, 30);

class local_pickup extends fflcommerce_shipping_method
{
	private $shipping_label;

	public function __construct()
	{
		parent::__construct();

		$options = FFLCommerce_Base::get_options();
		$this->id = 'local_pickup';
		$this->enabled = $options->get('fflcommerce_local_pickup_enabled');
		$this->title = $options->get('fflcommerce_local_pickup_title');
		$this->fee = $options->get('fflcommerce_local_pickup_handling_fee');
		$this->availability = $options->get('fflcommerce_local_pickup_availability');
		$this->countries = $options->get('fflcommerce_local_pickup_countries');

		$session = fflcommerce_session::instance();
		if (isset($session->chosen_shipping_method_id) && $session->chosen_shipping_method_id == $this->id) {
			$this->chosen = true;
		}

		add_action('fflcommerce_settings_scripts', array($this, 'admin_scripts'));
	}

	public function calculate_shipping()
	{
		$this->shipping_total = $this->get_fee($this->fee, fflcommerce_cart::get_total(false));
		$this->shipping_tax = 0;
		$this->shipping_label = $this->title;
	}

	public function admin_scripts()
	{
		?>
		<script type="text/javascript">
			/*<![CDATA[*/
			jQuery(function($){
				$('select#fflcommerce_local_pickup_availability').change(function(){
					if($(this).val() == "specific"){
						$(this).parent().parent().next('tr').show();
					} else {
						$(this).parent().parent().next('tr').hide();
					}
				}).change();
			});
			/*]]>*/
		</script>
	<?php
	}

	/**
	 * Default Option settings for WordPress Settings API using the FFLCommerce_Options class
	 * These should be installed on the FFLCommerce_Options 'Shipping' tab
	 */
	protected function get_default_options()
	{
		return array(
			array('name' => __('Local pickup', 'fflcommerce'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable local pickup', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_local_pickup_enabled',
				'std' => 'no',
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
				'id' => 'fflcommerce_local_pickup_title',
				'std' => __('Local pickup', 'fflcommerce'),
				'type' => 'text'
			),
			array(
				'name' => __('Handling Fee', 'fflcommerce'),
				'desc' => '',
				'type' => 'text',
				'tip' => __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'fflcommerce'),
				'id' => 'fflcommerce_local_pickup_handling_fee',
				'std' => ''
			),
			array(
				'name' => __('Method available for', 'fflcommerce'),
				'desc' => '',
				'tip' => '',
				'id' => 'fflcommerce_local_pickup_availability',
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
				'id' => 'fflcommerce_local_pickup_countries',
				'std' => '',
				'type' => 'multi_select_countries'
			),
		);
	}
}
