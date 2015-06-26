<?php

namespace Jigoshop\Extension;

class CartFaviconCount
{
	public function __construct(){
		if(is_admin())
		{
			\Jigoshop_Base::get_options()->install_external_options_tab(__('Cart Favicon', 'fflcommerce_cart_favicon_count'), $this->adminSettings());
			add_action('admin_enqueue_scripts', array($this, 'adminScripts'), 9);
			add_action('init', array($this, 'adminStyles'));
		}
		if(\Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_enable') == 'yes' && (\Jigoshop_Base::get_options()->exists('fflcommerce_cart_favicon_count_url'))){
			add_action('wp_head', array($this, 'addFavicon'),1000);
			add_action('wp_enqueue_scripts', array($this, 'frontScripts'), 9);
		}
	}

	public function adminSettings(){
		return array(
			array(
				'name' => __('Cart Favicon', 'fflcommerce_cart_favicon_count'),
				'type' => 'title',
				'desc' => '',
				'id' => '',
			),
			array(
				'name' => __('Enable module', 'fflcommerce_cart_favicon_count'),
				'type' => 'checkbox',
				'desc' => '',
				'id' => 'fflcommerce_cart_favicon_count_enable',
				'choices' => array(
					'yes' => __('Yes', 'fflcommerce_cart_favicon_count'),
					'no' => __('No', 'fflcommerce_cart_favicon_count'),
				)
			),
			array(
				'name' => __('Upload Favicon', 'fflcommerce_cart_favicon_count'),
				'id' => 'fflcommerce_cart_favicon_count_url',
				'desc' => __('This module can not work properly if your site already has defined favicon.', 'fflcommerce_web_optimization_system'),
				'type' => 'user_defined',
				'std' => '',
				'display' => array($this, 'displayFileUpload'),
				'update' => array($this, 'saveFileUpload')
			),
			array(
				'name' => __('Position', 'fflcommerce_cart_favicon_count'),
				'type' => 'select',
				'desc' => '',
				'id' => 'fflcommerce_cart_favicon_count_position',
				'choices' => array(
					'down' => __('Right down', 'fflcommerce_cart_favicon_count'),
					'up' => __('Right up', 'fflcommerce_cart_favicon_count'),
					'left' => __('Left down', 'fflcommerce_cart_favicon_count'),
					'leftup' => __('Left up', 'fflcommerce_cart_favicon_count'),
				)
			),
			array(
				'name' => __('Background Color', 'fflcommerce_cart_favicon_count'),
				'id' => 'fflcommerce_cart_favicon_count_bg_color',
				'desc' => '',
				'type' => 'text',
				'std' => '',
				'class' => 'picker'
			),
			array(
				'name' => __('Text Color', 'fflcommerce_cart_favicon_count'),
				'id' => 'fflcommerce_cart_favicon_count_text_color',
				'desc' => '',
				'type' => 'text',
				'std' => '',
				'class' => 'picker'
			),
		);
	}

	public function addFavicon() {
		$favicon = \Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_url');
		echo '<link id="fflcommerce_favicon" rel="shortcut icon" href="'.$favicon.'" />';
	}

	public function frontScripts() {
		jrto_enqueue_script('frontend', 'favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/js/favicon.js', array('jquery'));
		jrto_localize_script('favicon', 'favicon_params', array(
			'favicon_count'	=> \fflcommerce_cart::$cart_contents_count,
			'favicon_url' => \Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_url'),
			'position' => \Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_position'),
			'bg_color' =>  \Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_bg_color'),
			'text_color' => \Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_text_color')
		));
	}

	public function adminScripts() {
		jrto_enqueue_script('admin', 'favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/vendor/js/colpick.js', array('jquery'));
		jrto_enqueue_script('admin', 'colpick', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/js/init-colpick.js', array('jquery'));
	}

	public function adminStyles() {
		jrto_enqueue_style('admin', 'favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/vendor/css/colpick.css');
		jrto_enqueue_style('admin', 'colpick', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/css/init-colpick.css');
	}

	public function displayFileUpload() {
		ob_start();
		echo '<table>';
		if(\Jigoshop_Base::get_options()->exists('fflcommerce_cart_favicon_count_url')){
			echo '<tr><td>'.__('Actual icon:', 'fflcommerce_cart_favicon_count').'</td><td><img src="'.\Jigoshop_Base::get_options()->get('fflcommerce_cart_favicon_count_url').'"/></td></tr>';
		}
		echo '<tr><td>'.__('Upload new icon:', 'fflcommerce_cart_favicon_count').'</td><td><input type="file" id="fflcommerce_cart_favicon_count_file" name="fflcommerce_cart_favicon_count_file" value="" /></td></tr>';
		echo '</table>';
		return ob_get_clean();
	}

	public function saveFileUpload() {
		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['fflcommerce_cart_favicon_count_file'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		if ( $movefile ) {
			return $movefile['url'];
		}
	}
}
