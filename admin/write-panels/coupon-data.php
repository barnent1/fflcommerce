<?php
/**
 * Coupon Data
 *
 * Functions for displaying and editing the coupon data meta boxes
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Admin
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */


/**
 * Coupon data meta box
 *
 * Displays the meta box
 */
function fflcommerce_coupon_data_box( $post ) {
	global $fflcommerce;

	wp_nonce_field( 'fflcommerce_save_data', 'fflcommerce_meta_nonce' );

	$coupon_code  = '';
	$coupon_code .= "<p class='form-field'>";
	$coupon_code .= "<label>".__('Coupon Code','fflcommerce')."</label>";
	$coupon_code .= "<span><strong>".$post->post_name."</strong></span>";
	$coupon_code .= '<span class="description">'.__('Will not appear until coupon is saved.  This is the front end code for use on the Cart.','fflcommerce').'</span>';
	$coupon_code .= "</p>";

	// disable the permalink slug display
	?>
		<style type="text/css">#edit-slug-box { display:none }</style>

		<div id="coupon_options" class="panel fflcommerce_options_panel">

			<div class="options_group">

			<?php

			// The coupon code from the title after 'sanitize_title'
			echo $coupon_code;

			// Coupon Types
			$args = array(
				'id'            => 'type',
				'label'         => __( 'Coupon Type', 'fflcommerce' ),
				'options'       => JS_Coupons::get_coupon_types(),
			);
			echo FFLCommerce_Forms::select( $args );

			// Amount
			$args = array(
				'id'            => 'amount',
				'label'         => __( 'Coupon Amount', 'fflcommerce' ),
				'type'          => 'number',
				'min'           => 0,
				'desc'          => __('Enter an amount e.g. 9.99.','fflcommerce'),
				'tip'           => __('Amount this coupon is worth. If it is a percentange, just include the number without the percentage sign.','fflcommerce'),
				'placeholder'   => '0.00'
			);
			echo FFLCommerce_Forms::input( $args );

			// Date From
			$coupon_date_from = get_post_meta( $post->ID, 'date_from', true);
			$args = array(
				'id'            => 'date_from',
				'label'         => __('Date From','fflcommerce'),
				'desc'          => __('yyyy-mm-dd','fflcommerce'),
				'tip'           => __('Choose between which dates this coupon is enabled.  Leave empty for any date.','fflcommerce'),
				'class'         => 'short date-pick',
				'placeholder'   => __('Any date','fflcommerce'),
				'value'         => ($coupon_date_from <> '') ? date( 'Y-m-d', $coupon_date_from ) : ''
			);
			echo FFLCommerce_Forms::input( $args );

			// Date To
			$coupon_date_to = get_post_meta( $post->ID, 'date_to', true);
			$args = array(
				'id'            => 'date_to',
				'label'         => __('Date To','fflcommerce'),
				'desc'          => __('yyyy-mm-dd','fflcommerce'),
				'tip'           => __('Choose between which dates this coupon is enabled.  Leave empty for any date.','fflcommerce'),
				'class'         => 'short date-pick',
				'placeholder'   => __('Any date','fflcommerce'),
				'value'         => ($coupon_date_to <> '') ? date( 'Y-m-d', $coupon_date_to ) : ''
			);
			echo FFLCommerce_Forms::input( $args );

			// Usage limit
			$usage = get_post_meta( $post->ID, 'usage', true);
			$args = array(
				'id'            => 'usage_limit',
				'label'         => __( 'Usage Limit', 'fflcommerce' ),
				'type'          => 'number',
				'desc'          => sprintf(__('Times used: %s','fflcommerce'), !empty( $usage ) ? $usage : '0'),
				'tip'           => __('Control how many times this coupon may be used.','fflcommerce'),
				'placeholder'   => '0'
			);
			echo FFLCommerce_Forms::input( $args );

			// Individual use
			$args = array(
				'id'            => 'individual_use',
				'label'         => __('Individual Use','fflcommerce'),
				'desc'          => __('Prevent other coupons from being used while this one is applied to the Cart.','fflcommerce'),
				'value'         => false
			);
			echo FFLCommerce_Forms::checkbox( $args );

			// Free shipping
			$args = array(
				'id'            => 'free_shipping',
				'label'         => __('Free shipping','fflcommerce'),
				'desc'          => __('Show the Free Shipping method on the Checkout with this enabled.','fflcommerce'),
				'value'         => false
			);
			echo FFLCommerce_Forms::checkbox( $args );

		?>
			</div><div class="options_group">
		<?php

			// Order total minimum
			$args = array(
				'id'            => 'order_total_min',
				'label'         => __( 'Order total min', 'fflcommerce' ),
				'type'          => 'number',
				'desc'          => __('Set the required minimum subtotal for this coupon to be valid on an order.','fflcommerce'),
				'placeholder'   => __('No min','fflcommerce')
			);
			echo FFLCommerce_Forms::input( $args );

			// Order total maximum
			$args = array(
				'id'            => 'order_total_max',
				'label'         => __( 'Order total max', 'fflcommerce' ),
				'type'          => 'number',
				'desc'          => __('Set the required maximum subtotal for this coupon to be valid on an order.','fflcommerce'),
				'placeholder'   => __('No max','fflcommerce')
			);
			echo FFLCommerce_Forms::input( $args );

		?>
			</div><div class="options_group">
		<?php

			// Include product ID's
 			$selected = get_post_meta( $post->ID, 'include_products', true );
  			$selected = implode( ',', (array)$selected );
			$args = array(
				'id'            => 'include_products',
				'type'          => 'hidden',        /* use hidden input type for Select2 custom data loading */
				'class'         => 'long',
				'label'         => __( 'Include Products', 'fflcommerce' ),
				'desc'          => __('Control which products this coupon can apply to.','fflcommerce'),
				'value'         => $selected
			);
			echo FFLCommerce_Forms::input( $args );

			// Exclude product ID's
			$selected = get_post_meta( $post->ID, 'exclude_products', true );
			$selected = implode( ',', (array)$selected );
			$args = array(
				'id'            => 'exclude_products',
				'type'          => 'hidden',        /* use hidden input type for Select2 custom data loading */
				'class'         => 'long',
				'label'         => __( 'Exclude Products', 'fflcommerce' ),
				'desc'          => __('Control which products this coupon cannot be applied to.','fflcommerce'),
				'value'         => $selected
			);
			echo FFLCommerce_Forms::input( $args );

		?>
			</div><div class="options_group">
		<?php

			// Include Categories
			$categories = get_terms( 'product_cat', array( 'hide_empty' => false ));
			$coupon_cats = array();
			foreach ( $categories as $category )
				$coupon_cats[$category->term_id] = $category->name;
			$args = array(
				'id'            => 'include_categories',
				'label'         => __( 'Include Categories', 'fflcommerce' ),
				'desc'          => __('Control which product categories this coupon can apply to.','fflcommerce'),
				'multiple'      => true,
				'placeholder'   => __('Any category','fflcommerce'),
				'options'       => $coupon_cats
			);
			echo FFLCommerce_Forms::select( $args );

			// Exclude Categories
			$args = array(
				'id'            => 'exclude_categories',
				'label'         => __( 'Exclude Categories', 'fflcommerce' ),
				'desc'          => __('Control which product categories this coupon cannot be applied to.','fflcommerce'),
				'multiple'      => true,
				'placeholder'   => __('No exclusions','fflcommerce'),
				'options'       => $coupon_cats
			);
			echo FFLCommerce_Forms::select( $args );

		?>
			</div><div class="options_group">
		<?php

			// Payment methods
			$payment_methods = array();
			$available_gateways = fflcommerce_payment_gateways::get_available_payment_gateways();
			if ( ! empty( $available_gateways )) foreach ( $available_gateways as $id => $info )
				$payment_methods[$id] = $info->title;
			$args = array(
				'id'            => 'pay_methods',
				'label'         => __( 'Payment Methods', 'fflcommerce' ),
				'desc'          => __('Control which payment methods are allowed for this coupon to be effective.','fflcommerce'),
				'multiple'      => true,
				'placeholder'   => __('Any method','fflcommerce'),
				'options'       => $payment_methods
			);
			echo FFLCommerce_Forms::select( $args );

			// javascript for product includes and excludes -- need to move this
		?>
			<script type="text/javascript">
			/*<![CDATA[*/
				jQuery(document).ready(function($) {
					$('#date_from').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
					$('#date_to').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );

					// allow searching of products to use on a coupon
					$("#include_products").select2({
						minimumInputLength: 3,
						multiple: true,
						closeOnSelect: true,
						placeholder: "<?php _e('Any product','fflcommerce'); ?>",
						ajax: {
							url: "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
							dataType: 'json',
							quietMillis: 100,
							data: function(term, page) {
								return {
									term:       term,
									action:     'fflcommerce_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>'
								};
							},
							results: function( data, page ) {
								return { results: data };
							}
						},
						initSelection: function( element, callback ) {
							var stuff = {
								action:     'fflcommerce_json_search_products_and_variations',
								security:   '<?php echo wp_create_nonce( "search-products" ); ?>',
								term:       element.val()
							};
							var data = [];
							$.ajax({
								type: 		'GET',
								url:        "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
								dataType: 	"json",
								data: 		stuff,
								success: 	function( result ) {
									callback( result );
								}
							});
						}
					});

					// allow searching of products to exclude on a coupon
					$("#exclude_products").select2({
						minimumInputLength: 3,
						multiple: true,
						closeOnSelect: true,
						placeholder: "<?php _e( 'No exclusions', 'fflcommerce' ); ?>",
						ajax: {
							url: "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
							dataType: 'json',
							quietMillis: 100,
							data: function(term, page) {
								return {
									term:       term,
									action:     'fflcommerce_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>'
								};
							},
							results: function( data, page ) {
								return { results: data };
							}
						},
						initSelection: function( element, callback ) {
							var stuff = {
								action:     'fflcommerce_json_search_products_and_variations',
								security:   '<?php echo wp_create_nonce( "search-products" ); ?>',
								term:       element.val()
							};
							$.ajax({
								type: 		'GET',
								url:        "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
								dataType: 	"json",
								data: 		stuff,
								success: 	function( result ) {
									callback( result );
								}
							});
						}
					});
				});
			/*]]>*/
			</script>
		</div></div>
	<?php
}

/**
 * Coupon Data Save
 *
 * Function for processing and storing all coupon data.
 */
add_action( 'fflcommerce_process_shop_coupon_meta', 'fflcommerce_process_shop_coupon_meta', 1, 2 );

function fflcommerce_process_shop_coupon_meta( $post_id, $post ) {

	global $wpdb, $fflcommerce_errors;

	$type = fflcommerce_clean( $_POST['type'] );
	$amount = abs( fflcommerce_clean( $_POST['amount'] ));

	if ( !empty( $_POST['date_from'] )) {
		$coupon_date_from = strtotime( fflcommerce_clean( $_POST['date_from'] ));
	} else {
		$coupon_date_from = '';
	}

	if ( !empty( $_POST['date_to'] )) {
		$coupon_date_to = strtotime( fflcommerce_clean( $_POST['date_to'] )) + (60 * 60 * 24 - 1);
	} else {
		$coupon_date_to = '';
	}

	$usage_limit = ( isset( $_POST['usage_limit'] ) && $_POST['usage_limit'] > 0 ) ? (int) fflcommerce_clean( $_POST['usage_limit'] ) : '';
	$individual = isset( $_POST['individual_use'] );
	$free_shipping = isset( $_POST['free_shipping'] );

	$minimum_amount = fflcommerce_clean( $_POST['order_total_min'] );
	$maximum_amount = fflcommerce_clean( $_POST['order_total_max'] );

	if ( isset( $_POST['include_products'] )) {
		$include_products = fflcommerce_clean( $_POST['include_products'] );
		if ( $include_products == 'Array' ) $include_products = '';
		$include_products = $include_products <> '' ? explode( ',', $include_products ) : array();
	} else {
		$include_products = array();
	}

	if ( isset( $_POST['exclude_products'] )) {
		$exclude_products = fflcommerce_clean( $_POST['exclude_products'] );
		if ( $exclude_products == 'Array' ) $exclude_products = '';
		$exclude_products = $exclude_products <> '' ? explode( ',', $exclude_products ) : array();
	} else {
		$exclude_products = array();
	}

	if ( isset( $_POST['include_categories'] )) {
		$include_categories = $_POST['include_categories'];
	} else {
		$include_categories = array();
	}

	if ( isset( $_POST['exclude_categories'] )) {
		$exclude_categories = $_POST['exclude_categories'];
	} else {
		$exclude_categories = array();
	}

	if ( isset( $_POST['pay_methods'] )) {
		$pay_methods = $_POST['pay_methods'];
	} else {
		$pay_methods = array();
	}

	update_post_meta( $post_id, 'type',                 $type );
	update_post_meta( $post_id, 'amount',               $amount );
	update_post_meta( $post_id, 'date_from',            $coupon_date_from );
	update_post_meta( $post_id, 'date_to',              $coupon_date_to );
	update_post_meta( $post_id, 'usage_limit',          $usage_limit );
	update_post_meta( $post_id, 'individual_use',       $individual );
	update_post_meta( $post_id, 'free_shipping',        $free_shipping );
	update_post_meta( $post_id, 'order_total_min',      $minimum_amount );
	update_post_meta( $post_id, 'order_total_max',      $maximum_amount );
	update_post_meta( $post_id, 'include_products',     $include_products );
	update_post_meta( $post_id, 'exclude_products',     $exclude_products );
	update_post_meta( $post_id, 'include_categories',   $include_categories );
	update_post_meta( $post_id, 'exclude_categories',   $exclude_categories );
	update_post_meta( $post_id, 'pay_methods',          $pay_methods );

}
