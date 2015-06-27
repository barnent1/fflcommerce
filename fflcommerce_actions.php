<?php
/**
 * Various hooks FFL Commerce core uses
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFL Commerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 FFL Commerce and Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Various hooks FFL Commerce uses to do stuff. index:
 *
 *		- Add order item
 *		- When default permalinks are enabled, redirect shop page to post type archive url
 *		- Add to Cart
 *		- Clear cart
 *		- Restore an order via a link
 *		- Cancel a pending order
 *		- Download a file
 *		- Order Status completed - GIVE DOWNLOADABLE PRODUCT ACCESS TO CUSTOMER
 *
 **/

/**
 * Add order item
 *
 * Add order item via ajax
 *
 * @since 		1.0
 */
add_action('wp_ajax_fflcommerce_add_order_item', 'fflcommerce_add_order_item');

function fflcommerce_add_order_item() {

    $fflcommerce_options = FFLCommerce_Base::get_options();
	check_ajax_referer( 'add-order-item', 'security' );

	global $wpdb;

	$item_to_add = trim(stripslashes($_POST['item_to_add']));

	$post = '';

	// Find the item
	if (is_numeric($item_to_add)) :
		$post = get_post( $item_to_add );
	endif;

	if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
		$post_id = $wpdb->get_var($wpdb->prepare("
			SELECT post_id
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
			WHERE $wpdb->postmeta.meta_key = 'SKU'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_type = 'shop_product'
			AND $wpdb->postmeta.meta_value = %s
			LIMIT 1
		", $item_to_add ));
		$post = get_post( $post_id );
	endif;

	if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
		die();
	endif;

	if ($post->post_type=="product") :
		$_product = new fflcommerce_product( $post->ID );
	else :
		$_product = new fflcommerce_product_variation( $post->ID );
	endif;

	$loop = 0;
	?>
	<tr class="item">
		<?php do_action( 'fflcommerce_admin_order_item_before_prod_id', intval($_POST['item_no']) ) ?>
		<td class="product-id">#<?php echo $_product->id; ?></td>
		<td class="variation-id"><?php if (isset($_product->variation_id)) echo $_product->variation_id; else echo '-'; ?></td>
		<td class="product-sku"><?php if ($_product->sku) echo $_product->sku; ?></td>
		<td class="name"><a href="<?php echo esc_url( admin_url('post.php?post='. $_product->id .'&action=edit') ); ?>"><?php echo $_product->get_title(); ?></a></td>
		<td class="variation"><?php
			if (isset($_product->variation_data)) :
				echo fflcommerce_get_formatted_variation( $_product, array(), true );
			else :
				echo '-';
			endif;
		?></td>
		
		<?php do_action('fflcommerce_admin_order_item_values', $_product, array(), 0); ?>
		<td class="quantity"><input type="text" name="item_quantity[]" placeholder="<?php _e('Quantity e.g. 2', 'fflcommerce'); ?>" value="1" /></td>
        <td class="cost"><input type="text" name="item_cost[]" placeholder="<?php _e('Cost per unit ex. tax e.g. 2.99', 'fflcommerce'); ?>" value="<?php echo esc_attr( $fflcommerce_options->get('fflcommerce_prices_include_tax') == 'yes' ? $_product->get_price_excluding_tax() : $_product->get_price() ); ?>" /></td>
        <td class="tax"><input type="text" name="item_tax_rate[]" placeholder="<?php _e('Tax Rate e.g. 20.0000', 'fflcommerce'); ?>" value="<?php echo esc_attr( fflcommerce_tax::calculate_total_tax_rate($_product->get_tax_base_rate()) ); ?>" /></td>
		<td class="center">
			<input type="hidden" name="item_id[]" value="<?php echo esc_attr( $_product->id ); ?>" />
			<input type="hidden" name="item_name[]" value="<?php echo esc_attr( $_product->get_title() ); ?>" />
            <input type="hidden" name="item_variation_id[]" value="<?php if ($_product instanceof fflcommerce_product_variation) echo esc_attr( $_product->variation_id ); else echo ''; ?>" />
			<button type="button" class="remove_row button">&times;</button>
		</td>
	</tr>
	<?php

	// Quit out
	die();
}

/**
 * Save address and redirect to proper page.
 */
add_action('init', function(){
});

/**
 * When default permalinks are enabled, redirect shop page to post type archive url
 **/
add_action( 'init', 'fflcommerce_shop_page_archive_redirect' );

function fflcommerce_shop_page_archive_redirect() {
	if (FFLCommerce_Base::get_options()->get( 'permalink_structure' )=="") :
		if ( isset($_GET['page_id']) && $_GET['page_id'] == fflcommerce_get_page_id('shop') ) :
			wp_safe_redirect( get_post_type_archive_link('product') );
			exit;
		endif;
	endif;
}

/**
 * Remove from cart/update
 **/
add_action( 'init', 'fflcommerce_update_cart_action' );

function fflcommerce_update_cart_action() {

	// Remove from cart
	if ( isset($_GET['remove_item']) && $_GET['remove_item']  && fflcommerce::verify_nonce('cart')) :

		fflcommerce_cart::set_quantity( $_GET['remove_item'], 0 );

		// Re-calc price
		//fflcommerce_cart::calculate_totals();

		fflcommerce::add_message( __('Cart updated.', 'fflcommerce') );

		if ( isset($_SERVER['HTTP_REFERER'])) :
			wp_safe_redirect($_SERVER['HTTP_REFERER']);
			exit;
		endif;

	// Update Cart
	elseif (isset($_POST['update_cart']) && $_POST['update_cart']  && fflcommerce::verify_nonce('cart')) :

		$cart_totals = $_POST['cart'];

		if (sizeof(fflcommerce_cart::$cart_contents)>0) :
			foreach (fflcommerce_cart::$cart_contents as $cart_item_key => $values) :

				if ( isset( $cart_totals[$cart_item_key]['qty'] ) ) {
					fflcommerce_cart::set_quantity( $cart_item_key, apply_filters( 'fflcommerce_cart_item_quantity', $cart_totals[$cart_item_key]['qty'], $values['data'], $cart_item_key ) );
				}

			endforeach;
		endif;

		fflcommerce::add_message( __('Cart updated.', 'fflcommerce') );

	endif;

}

/**
 * Add to cart
 **/
add_action( 'init', 'fflcommerce_add_to_cart_action' );
if ( ! function_exists( 'fflcommerce_add_to_cart_action' )) { //make function pluggable
	function fflcommerce_add_to_cart_action( $url = false ) {

		if ( empty($_REQUEST['add-to-cart']) || !fflcommerce::verify_nonce('add_to_cart') )
			return false;

		$options = FFLCommerce_Base::get_options();

		$product_added = false;

		switch ( $_REQUEST['add-to-cart'] ) {

			case 'variation':

				// ensure we have a valid quantity, product and variation id, that is numeric, without any SQL injection attempts.
				$product_id   = (isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])) ? (int) $_REQUEST['product_id'] : -1;
				if ( $product_id < 0 ) {
					break;      // drop out and put up message, unable to add product.
				}
				if ( empty($_REQUEST['variation_id']) || !is_numeric($_REQUEST['variation_id']) ) {
					break;      // drop out and put up message, unable to add product.
				}
				$quantity     = (isset($_REQUEST['quantity']) && is_numeric($_REQUEST['quantity'])) ? (int) $_REQUEST['quantity'] : 1;

				$product_id   = apply_filters('fflcommerce_product_id_add_to_cart_filter', $product_id);
				$variation_id = apply_filters('fflcommerce_variation_id_add_to_cart_filter', (int) $_REQUEST['variation_id']);
				$attributes   = (array) maybe_unserialize(get_post_meta($product_id, 'product_attributes', true));
				$variations   = array();

				$all_variations_set = true;

				if ( get_post_meta( $product_id , 'customizable', true ) == 'yes' ) {
					// session personalization initially set to parent product until variation selected
					$custom_products = (array) fflcommerce_session::instance()->customized_products;
					// transfer it to the variation
					$custom_products[$variation_id] = $custom_products[$product_id];
					unset( $custom_products[$product_id] );
					fflcommerce_session::instance()->customized_products = $custom_products;
				}

				foreach ($attributes as $attribute) {

					if ( !$attribute['variation'] )
						continue;

					$attr_name = 'tax_' . sanitize_title($attribute['name']);
					if ( !empty($_REQUEST[$attr_name]) ) {
						$variations[$attr_name] = esc_attr($_REQUEST[$attr_name]);
					} else {
						$all_variations_set = false;
					}
				}

				// Add to cart validation
				$is_valid = apply_filters('fflcommerce_add_to_cart_validation', true, $product_id, $quantity);

				if ( $all_variations_set && $is_valid ) {
					if ( fflcommerce_cart::add_to_cart($product_id, $quantity, $variation_id, $variations) ) $product_added = true;
				}

			break;

			case 'group':

				if ( empty($_REQUEST['quantity']) || !is_array($_REQUEST['quantity']) )
					break; // do nothing

				foreach ( $_REQUEST['quantity'] as $product_id => $quantity ) {

					// Skip if no quantity
					if ( ! $quantity )
						continue;

					$quantity = (int) $quantity;

					// Add to cart validation
					$is_valid = apply_filters('fflcommerce_add_to_cart_validation', true, $product_id, $quantity);

					// Add to the cart if passsed validation
					if ( $is_valid ) {
						if ( fflcommerce_cart::add_to_cart($product_id, $quantity) ) $product_added = true;
					}
				}

			break;

			default:

				if ( !is_numeric($_REQUEST['add-to-cart']) )
					// Handle silently for now
					break;

				// Get product ID & quantity
				$product_id = apply_filters('fflcommerce_product_id_add_to_cart_filter', (int) $_REQUEST['add-to-cart']);
				$quantity   = (isset($_REQUEST['quantity'])) ? (int) $_REQUEST['quantity'] : 1;

				// Add to cart validation
				$is_valid   = apply_filters('fflcommerce_add_to_cart_validation', true, $product_id, $quantity);

				// Add to the cart if passed validation
				if ( $is_valid ) {
					if ( fflcommerce_cart::add_to_cart($product_id, $quantity) ) $product_added = true;
				}

			break;
		}

		if ( ! $product_added ) {
			fflcommerce::add_error( __('The Product could not be added to the cart.  Please try again.', 'fflcommerce') );
			wp_safe_redirect( remove_query_arg( array( 'add-to-cart', 'quantity', 'product_id', '_n' ), wp_get_referer() ), 301 ); exit;
		} else {
			switch ( $options->get('fflcommerce_redirect_add_to_cart', 'same_page') ) {
				case 'same_page':
					$message = __('Product successfully added to your cart.', 'fflcommerce');
					$button = __('View Cart &rarr;', 'fflcommerce');
					$message = '<a href="%s" class="button">' . $button . '</a> ' . $message;
					fflcommerce::add_message(sprintf( $message, fflcommerce_cart::get_cart_url()));
					break;

				case 'to_checkout':
						// Do nothing
					break;

				default:
					fflcommerce::add_message(__('Product successfully added to your cart.', 'fflcommerce'));
					break;
			}

			$url = apply_filters( 'add_to_cart_redirect', $url );
			if ( $url ) {
				wp_safe_redirect( $url, 301 ); exit;
			}
			else if ( $options->get('fflcommerce_redirect_add_to_cart', 'same_page') == 'to_checkout' && !fflcommerce::has_errors() ) {
				wp_safe_redirect(fflcommerce_cart::get_checkout_url(), 301); exit;
			}
			else if ($options->get('fflcommerce_redirect_add_to_cart', 'to_cart') == 'to_cart' && !fflcommerce::has_errors()) {
				wp_safe_redirect(fflcommerce_cart::get_cart_url(), 301); exit;
			}
			else if ( wp_get_referer() ) {
				wp_safe_redirect( remove_query_arg( array( 'add-to-cart', 'quantity', 'product_id' ), wp_get_referer() ), 301 ); exit;
			}
			else {
				wp_safe_redirect(home_url(), 301); exit;
			}
		}
	}
}

function fflcommerce_ajax_update_order_review() {

	check_ajax_referer( 'update-order-review', 'security' );

	if (!defined('FFLCOMMERCE_CHECKOUT')) define('FFLCOMMERCE_CHECKOUT', true);

	fflcommerce_cart::get_cart();
	if (sizeof(fflcommerce_cart::$cart_contents)==0) :
		echo '<p class="error">'.__('Sorry, your session has expired.', 'fflcommerce').' <a href="'.home_url().'">'.__('Return to homepage &rarr;', 'fflcommerce').'</a></p>';
		exit;
	endif;

	do_action('fflcommerce_checkout_update_order_review', $_POST['post_data']);

    if (isset($_POST['shipping_method'])) :

		$shipping_method = explode(":", $_POST['shipping_method']);
	 	fflcommerce_session::instance()->chosen_shipping_method_id = $shipping_method[0];

		if (is_numeric($shipping_method[2])) :
			fflcommerce_session::instance()->selected_rate_id = $shipping_method[2];
		endif;

	endif;

	if (!empty($_POST['coupon_code'])) {
		fflcommerce_cart::add_discount( sanitize_title( $_POST['coupon_code'] ) );
		fflcommerce::show_messages();
	}

	if (isset($_POST['country']))   fflcommerce_customer::set_country( $_POST['country'] );
	if (isset($_POST['state']))     fflcommerce_customer::set_state( $_POST['state'] );
	if (isset($_POST['postcode']))  fflcommerce_customer::set_postcode( $_POST['postcode'] );
	if (isset($_POST['s_country'])) fflcommerce_customer::set_shipping_country( $_POST['s_country'] );
	if (isset($_POST['s_state']))   fflcommerce_customer::set_shipping_state( $_POST['s_state'] );
	if (isset($_POST['s_postcode']))fflcommerce_customer::set_shipping_postcode( $_POST['s_postcode'] );

	fflcommerce_cart::calculate_totals();

	do_action('fflcommerce_checkout_order_review');

	die();
}
add_action('wp_ajax_fflcommerce_update_order_review', 'fflcommerce_ajax_update_order_review');
add_action('wp_ajax_nopriv_fflcommerce_update_order_review', 'fflcommerce_ajax_update_order_review');

/**
 * Clear cart
 **/
add_action( 'wp_head', 'fflcommerce_clear_cart_on_return' );

function fflcommerce_clear_cart_on_return() {

	if (is_page(fflcommerce_get_page_id('thanks'))) :

		if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
		if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';
		if ($order_id > 0) :
			$order = new fflcommerce_order( $order_id );
			if ($order->order_key == $order_key) :
				fflcommerce_cart::empty_cart();
			endif;
		endif;

	endif;

}

/**
 * Clear the cart after payment - order will be processing or complete
 **/
add_action( 'init', 'fflcommerce_clear_cart_after_payment' );

function fflcommerce_clear_cart_after_payment( $url = false ) {

	if (isset( fflcommerce_session::instance()->order_awaiting_payment ) && fflcommerce_session::instance()->order_awaiting_payment > 0) :

		$order = new fflcommerce_order( fflcommerce_session::instance()->order_awaiting_payment );

		if ($order->id > 0 && ($order->status=='completed' || $order->status=='processing')) :

			fflcommerce_cart::empty_cart();

			unset( fflcommerce_session::instance()->order_awaiting_payment );

		endif;

	endif;

}


/**
 * Process the login form
 **/
add_action('init', 'fflcommerce_process_login');

function fflcommerce_process_login() {

	if (isset($_POST['login']) && $_POST['login']) :

		fflcommerce::verify_nonce('login');

		if ( !isset($_POST['username']) || empty($_POST['username']) ) fflcommerce::add_error( __('Username is required.', 'fflcommerce') );
		if ( !isset($_POST['password']) || empty($_POST['password']) ) fflcommerce::add_error( __('Password is required.', 'fflcommerce') );

		if ( ! fflcommerce::has_errors()) :

			$creds = array();
			$creds['user_login'] = $_POST['username'];
			$creds['user_password'] = $_POST['password'];
			$creds['remember'] = true;
			$secure_cookie = is_ssl() ? true : false;
			$user = wp_signon( $creds, $secure_cookie );
			if ( is_wp_error($user) ) :
				fflcommerce::add_error( $user->get_error_message() );
			else :
				if ( isset($_SERVER['HTTP_REFERER'])) {
					wp_safe_redirect($_SERVER['HTTP_REFERER']);
					exit;
				}
				wp_redirect(apply_filters('fflcommerce_get_myaccount_page_id', get_permalink(fflcommerce_get_page_id('myaccount'))));
				exit;
			endif;

		endif;

	endif;
}

/**
 * Process ajax checkout form
 */
add_action('wp_ajax_fflcommerce-checkout', 'fflcommerce_process_ajax_checkout');
add_action('wp_ajax_nopriv_fflcommerce-checkout', 'fflcommerce_process_ajax_checkout');

function fflcommerce_process_ajax_checkout () {
	include_once FFLCOMMERCE_DIR.'/classes/fflcommerce_checkout.class.php';

	/** @var fflcommerce_checkout $checkout */
	$checkout = fflcommerce_checkout::instance();
	$result = $checkout->process_checkout();

	if($result === false){
		fflcommerce::show_messages();
		exit;
	}

	if(isset($result['result']) && $result['result'] == 'redirect'){
		echo json_encode(array('result' => 'success', 'redirect' => get_permalink($result['redirect'])));
		exit;
	}

	echo json_encode(apply_filters('fflcommerce_is_ajax_payment_successful', $result));
	exit;
}


/**
 * Cancel a pending order - hook into init function
 **/
add_action('init', 'fflcommerce_cancel_order');

function fflcommerce_cancel_order() {

	if ( isset($_GET['cancel_order']) && isset($_GET['order']) && isset($_GET['order_id']) ) :

		$order_key = urldecode( $_GET['order'] );
		$order_id = (int) $_GET['order_id'];

		$order = new fflcommerce_order( $order_id );

		if ($order->id == $order_id && $order->order_key == $order_key && $order->status=='pending' && fflcommerce::verify_nonce('cancel_order')) :

			// Cancel the order + restore stock
			$order->cancel_order( __('Order cancelled by customer.', 'fflcommerce') );

			// Message
			fflcommerce::add_message( __('Your order was cancelled.', 'fflcommerce') );

		elseif ($order->status!='pending') :

			fflcommerce::add_error( __('Your order is no longer pending and could not be cancelled. Please contact us if you need assistance.', 'fflcommerce') );

		else :

			fflcommerce::add_error( __('Invalid order.', 'fflcommerce') );

		endif;

		wp_safe_redirect(fflcommerce_cart::get_cart_url());
		exit;

	endif;
}


/**
 * Download a file - hook into init function
 **/
add_action('init', 'fflcommerce_download_product');

function fflcommerce_download_product() {

	if ( isset($_GET['download_file']) && isset($_GET['order']) && isset($_GET['email']) ) :

		global $wpdb;

		$download_file = (int) urldecode($_GET['download_file']);
		$order = urldecode( $_GET['order'] );
		$email = urldecode( $_GET['email'] );

		if (!is_email($email)) :
			wp_die( __('Invalid email address.', 'fflcommerce') . ' <a href="'.home_url().'">' . __('Go to homepage &rarr;', 'fflcommerce') . '</a>' );
		endif;

		$download_result = $wpdb->get_row( $wpdb->prepare("
			SELECT downloads_remaining, user_id
			FROM ".$wpdb->prefix."fflcommerce_downloadable_product_permissions
			WHERE user_email = %s
			AND order_key = %s
			AND product_id = %s
		;", $email, $order, $download_file ) );

		if (!$download_result) :
			wp_die( __('Invalid downloads.', 'fflcommerce') . ' <a href="'.home_url().'">' . __('Go to homepage &rarr;', 'fflcommerce') . '</a>' );
			exit;
		endif;

		$order_id            = isset( $download_result->order_id ) ? $download_result->order_id : false;
		$user_id             = $download_result->user_id;
		$downloads_remaining = $download_result->downloads_remaining;

		if ( $user_id && FFLCommerce_Base::get_options()->get('fflcommerce_downloads_require_login') == 'yes' ):
			if ( !is_user_logged_in() ):
				wp_die( __('You must be logged in to download files.', 'fflcommerce') . ' <a href="'.wp_login_url(get_permalink(fflcommerce_get_page_id('myaccount'))).'">' . __('Login &rarr;', 'fflcommerce') . '</a>' );
				exit;
			else:
				$current_user = wp_get_current_user();
				if( $user_id != $current_user->ID ):
					wp_die( __('This is not your download link.', 'fflcommerce'));
					exit;
				endif;
			endif;
		endif;

		if ($order_id) :
			$order = new fflcommerce_order( $order_id );
			if ($order->status!='completed' && $order->status!='processing' && $order->status!='publish') :
				wp_die( __('Invalid order.', 'fflcommerce') . ' <a href="'.home_url().'">' . __('Go to homepage &rarr;', 'fflcommerce') . '</a>' );
				exit;
			endif;
		endif;

		if ($downloads_remaining == '0') :
            wp_die( sprintf(__('Sorry, you have reached your download limit for this file. <a href="%s">Go to homepage &rarr;</a>', 'fflcommerce'), home_url()) );
		else :
			if ($downloads_remaining>0) :
				$wpdb->update( $wpdb->prefix . "fflcommerce_downloadable_product_permissions", array(
					'downloads_remaining' => $downloads_remaining - 1,
				), array(
					'user_email'=> $email,
					'order_key' => $order,
					'product_id'=> $download_file
				), array( '%d' ), array( '%s', '%s', '%d' ) );
			endif;

			$file_path = get_post_meta($download_file, 'file_path', true);

			if (!$file_path) wp_die( sprintf(__('File not found. <a href="%s">Go to homepage &rarr;</a>', 'fflcommerce'), home_url()) );

			// Get URLS with https
			$site_url = site_url();
			$network_url = network_admin_url();
			if (is_ssl()) :
				$site_url = str_replace('https:', 'http:', $site_url);
				$network_url = str_replace('https:', 'http:', $network_url);
			endif;

			if (!is_multisite()) :
				$file_path = str_replace(trailingslashit($site_url), ABSPATH, $file_path);
			else :
				$upload_dir = wp_upload_dir();

				// Try to replace network url
				$file_path = str_replace(trailingslashit($network_url), ABSPATH, $file_path);

				// Now try to replace upload URL
				$file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_path);
			endif;

			$file_path = apply_filters('fflcommerce_download_file_path', $file_path, $download_file, $order, $email);

			// See if its local or remote
			if (strstr($file_path, 'http:') || strstr($file_path, 'https:') || strstr($file_path, 'ftp:')) :
				$remote_file = true;
			else :
				$remote_file = false;
				$file_path = realpath($file_path);
			endif;

			// Download the file
			$file_extension = strtolower(substr(strrchr($file_path,"."),1));

			switch ($file_extension) :
				case "pdf": $ctype="application/pdf"; break;
				case "exe": $ctype="application/octet-stream"; break;
				case "zip": $ctype="application/zip"; break;
				case "doc": $ctype="application/msword"; break;
				case "xls": $ctype="application/vnd.ms-excel"; break;
				case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
				case "gif": $ctype="image/gif"; break;
				case "png": $ctype="image/png"; break;
				case "jpe": case "jpeg": case "jpg": $ctype="image/jpg"; break;
				default: $ctype="application/force-download";
			endswitch;

            do_action( 'fflcommerce_before_download', $file_path, $order );

			@session_write_close();
			@set_time_limit(0);
			@set_magic_quotes_runtime(0);
			@ob_end_clean();
			if (ob_get_level()) @ob_end_clean();
			// required for IE, otherwise Content-Disposition may be ignored
			if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');

			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Robots: none");
			header("Content-Type: ".$ctype."");
			header("Content-Description: File Transfer");
			header("Content-Transfer-Encoding: binary");

			if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
				// workaround for IE filename bug with multiple periods / multiple dots in filename
				$iefilename = preg_replace('/\./', '%2e', basename($file_path), substr_count(basename($file_path), '.') - 1);
				header("Content-Disposition: attachment; filename=\"".$iefilename."\";");
			} else {
				header("Content-Disposition: attachment; filename=\"".basename($file_path)."\";");
			}

			header("Content-Length: ".@filesize($file_path));

			if ( $remote_file ) {
				header('Location: '.$file_path);
			} else {
				@readfile("$file_path") or wp_die( sprintf(__('File not found. <a href="%s">Go to homepage &rarr;</a>', 'fflcommerce'), home_url()) );
			}
			exit;
		endif;

	endif;
}


/**
 * Order Status completed - GIVE DOWNLOADABLE PRODUCT ACCESS TO CUSTOMER
 **/
add_action('order_status_completed', 'fflcommerce_downloadable_product_permissions');

function fflcommerce_downloadable_product_permissions( $order_id ) {

	global $wpdb;

	$order = new fflcommerce_order( $order_id );

	if (sizeof($order->items)>0) foreach ($order->items as $item) :

		// if ($item['id']>0) :

			// @todo: Bit of a hack could be improved as id is null/0
			if ( (bool) $item['variation_id'] ) {
				$_product = new fflcommerce_product_variation( $item['variation_id'] );
				$product_id = $_product->variation_id;
			} else {
				$_product = new fflcommerce_product( $item['id'] );
				$product_id = $_product->ID;
			}

			if ( $_product->exists && $_product->is_type('downloadable') ) :

				$user_email = $order->billing_email;

				if ($order->user_id>0) :
					$user_info = get_userdata($order->user_id);
					if ($user_info->user_email) :
						$user_email = $user_info->user_email;
					endif;
				else :
					$order->user_id = 0;
				endif;

				$limit = trim(get_post_meta($_product->id, 'download_limit', true));

				if (!empty($limit)) :
					$limit = (int) $limit;
				else :
					$limit = '';
				endif;

				// Downloadable product - give access to the customer
				$wpdb->insert( $wpdb->prefix . 'fflcommerce_downloadable_product_permissions', array(
					'product_id'         => $product_id,
					'user_id'            => $order->user_id,
					'user_email'         => $user_email,
					'order_key'          => $order->order_key,
					'downloads_remaining'=> $limit
				), array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				) );

			endif;

		// endif;

	endforeach;
}

/**
 * Displays Google Analytics tracking code in the header as the LAST item before closing </head> tag
 *
 * @return  void
 */
add_action('wp_head', 'fflcommerce_ga_tracking', 9999);
function fflcommerce_ga_tracking()
{
	$options = FFLCommerce_Base::get_options();

	// If admin don't track.
	if (is_admin()) {
		return;
	}

	// Don't track the shop owners roaming
	if (current_user_can('manage_fflcommerce')) {
		return;
	}

	$tracking_id = $options->get('fflcommerce_ga_id');

	if (!$tracking_id) {
		return;
	}

	$user_id = '';
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();
	}
	?>
	<script type="text/javascript">
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'fflcommerceGA');
		fflcommerceGA('create', '<?php echo $tracking_id; ?>', { 'userId': '<?php echo $user_id; ?>' });
		fflcommerceGA('send', 'pageview');
	</script>
<?php
}

/**
 * Displays Google Analytics eCommerce tracking code on thank you page
 *
 * @return  void
 */
add_action('fflcommerce_thankyou', 'fflcommerce_ga_ecommerce_tracking');
function fflcommerce_ga_ecommerce_tracking($order_id)
{
	$options = FFLCommerce_Base::get_options();

	// Skip if disabled
	if ($options->get('fflcommerce_ga_ecommerce_tracking_enabled') != 'yes') {
		return;
	}

	// Don't track the shop owners roaming
	if (current_user_can('manage_fflcommerce')) {
		return;
	}

	$tracking_id = $options->get('fflcommerce_ga_id');

	if (!$tracking_id) {
		return;
	}

	// Get the order and output tracking code
	$order = new fflcommerce_order($order_id);
	?>
	<script type="text/javascript">
		fflcommerceGA('require', 'ecommerce');

		fflcommerceGA('ecommerce:addTransaction', {
			'id': '<?php echo $order->get_order_number(); ?>', // Transaction ID. Required.
			'affiliation': '<?php bloginfo('name'); ?>', // Affiliation or store name.
			'revenue': '<?php echo $order->order_total; ?>', // Grand Total.
			'shipping': '<?php echo $order->order_shipping; ?>', // Shipping.
			'tax': '<?php echo $order->get_total_tax(); ?>' // Tax.
		});

		<?php foreach($order->items as $item): $_product = $order->get_product_from_item($item); ?>
		fflcommerceGA('ecommerce:addItem', {
			'id': '<?php echo $order->get_order_number(); ?>', // Transaction ID. Required.
			'name': '<?php echo $item['name']; ?>', // Product name. Required.
			'sku': '<?php echo $_product->sku; ?>', // SKU/code.
			'category': '<?php if (isset($_product->variation_data)) echo fflcommerce_get_formatted_variation( $_product, $item['variation'], true ); ?>', // Category or variation.
			'price': '<?php echo ($item['cost']/$item['qty']); ?>', // Unit price.
			'quantity': '<?php echo $item['qty']; ?>' // Quantity.
		});
		<?php endforeach; ?>

		fflcommerceGA('ecommerce:send');
	</script>
	<?php
}

/**
 * FFL Commerce Dropdown categories
 *
 * @see http://core.trac.wordpress.org/ticket/13258
 * @param bool $show_counts
 * @param bool $hierarchal
 * @internal
 */
function fflcommerce_product_dropdown_categories( $show_counts = true, $hierarchal = true ) {
	global $wp_query;

	$r = array();
	$r['pad_counts'] = 1;
	$r['hierarchal'] = $hierarchal;
	$r['hide_empty'] = 1;
	$r['show_count'] = $show_counts;
	$r['selected']   = isset( $wp_query->query['product_cat'] ) ? $wp_query->query['product_cat'] : '';

	$terms = get_terms( 'product_cat', $r );
	if ( ! $terms ) return;

	$output  = "<select name='product_cat' id='dropdown_product_cat'>";

	$output .= '<option value="" ' .  selected( isset( $_GET['product_cat'] ) ? esc_attr( $_GET['product_cat'] ) : '', '', false ) . '>'.__('View all categories', 'fflcommerce').'</option>';
	$output .= fflcommerce_walk_category_dropdown_tree( $terms, 0, $r );
	$output .="</select>";

	echo $output;
}

/**
 * Walk the Product Categories.
 */
function fflcommerce_walk_category_dropdown_tree() {

	$args = func_get_args();

	// the user's options are the third parameter
	if ( empty( $args[2]['walker'] ) || !is_a( $args[2]['walker'], 'Walker' ) )
		$walker = new FFLCommerce_Walker_CategoryDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array( array( $walker, 'walk' ), $args );

}

/**
 * Create HTML dropdown list of Product Categories.
 */
class FFLCommerce_Walker_CategoryDropdown extends Walker_CategoryDropdown
{
	var $tree_type = 'category';
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug');

	function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0)
	{

		$pad = str_repeat('&nbsp;', $depth * 3);
		$cat_name = apply_filters('list_product_cats', $category->name, $category);

		if (!isset($args['value'])) {
			$args['value'] = ($category->taxonomy == 'product_cat' ? 'slug' : 'id');
		}

		$value = $args['value'] == 'slug' ? $category->slug : $category->term_id;

		$output .= "\t<option class=\"level-$depth\" value=\"".esc_attr($value)."\"";
		if ($value == $args['selected'] || (is_array($args['selected']) && in_array($value, $args['selected']))) {
			$output .= ' selected="selected"';
		}

		$output .= '>';
		$output .= $pad.$cat_name;
		if ($args['show_count']) {
			$output .= '&nbsp;&nbsp;('.$category->count.')';
		}

		$output .= "</option>\n";
	}
}

/**
 * Search for products and variations and return json
 *
 * For use with select2 product lookup implementations
 *
 */
function fflcommerce_json_search_products_and_variations() {

	fflcommerce_json_search_products( '', array( 'product', 'product_variation' ));

}
add_action( 'wp_ajax_fflcommerce_json_search_products_and_variations', 'fflcommerce_json_search_products_and_variations' );

/**
 * Search for products and return json
 *
 * For use with select2 product lookup implementations
 *
 */
function fflcommerce_json_search_products( $x = '', $post_types = array( 'product' )) {

	check_ajax_referer( 'search-products', 'security' );

	$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] )));

	if ( empty( $term )) die();

	if ( strpos( $term, ',' ) !== false ) {

		$term = (array) explode( ',', $term );
		$args = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			'post__in'      => $term,
			'fields'        => 'ids'
		);
		$products = get_posts( $args );

	} elseif ( is_numeric( $term )) {

		$args = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			'post__in'      => array(0, $term),
			'fields'        => 'ids'
		);
		$products = get_posts( $args );

	} else {

		$args = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			's'             => $term,
			'fields'        => 'ids'
		);

		$args2 = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			'meta_query'    => array(
				array(
				'key'       => 'sku',
				'value'     => $term,
				'compare'   => 'LIKE'
				)
			),
			'fields'        => 'ids'
		);
		$products = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ));

	}

	$found_products = array();

	if ( ! empty( $products )) foreach ( $products as $product_id ) {

		$SKU = get_post_meta( $product_id, 'sku', true );

		if ( isset( $SKU ) && $SKU ) $SKU = ' (SKU: ' . $SKU . ')';
		else $SKU = ' (ID: ' . $product_id . ')';

		$found_products[] = array( 'id' => $product_id, 'text' => html_entity_decode( get_the_title( $product_id ), ENT_COMPAT, 'UTF-8' ) . $SKU );

	}

	echo json_encode( $found_products );

	die();
}
add_action( 'wp_ajax_fflcommerce_json_search_products', 'fflcommerce_json_search_products' );


/**
 * AJAX validate postcode
 */
function fflcommerce_validate_postcode()
{
	check_ajax_referer('update-order-review', 'security');

	$postcode = (string)urldecode(stripslashes(strip_tags($_GET['postcode'])));
	if (empty($postcode)) {
		echo '0';
		exit;
	}

	$country = (string)urldecode(stripslashes(strip_tags($_GET['country'])));
	if (empty($country)) {
		echo '0';
		exit;
	}

	echo fflcommerce_validation::is_postcode($postcode, $country) ? '1' : '0';
	exit;
}
add_action( 'wp_ajax_fflcommerce_validate_postcode', 'fflcommerce_validate_postcode' );
add_action( 'wp_ajax_nopriv_fflcommerce_validate_postcode', 'fflcommerce_validate_postcode');

/**
 * Action for limiting WordPress feed from using order notes.
 */
add_action('comment_feed_where', function($where){
	return $where." AND comment_type <> 'order_note'";
});


function fflcommerce_ajax_update_item_quantity()
{
	/** @var fflcommerce_cart $cart */
	$cart = fflcommerce_cart::instance();
	$cart->set_quantity($_POST['item'], (int)$_POST['qty']);
	$items = $cart->get_cart();

	$price = -1;
	if (isset($items[$_POST['item']])) {
		$item = $items[$_POST['item']];
		/** @var fflcommerce_product $product */
		$product = $item['data'];
		$price = apply_filters('fflcommerce_product_subtotal_display_in_cart', fflcommerce_price($product->get_defined_price() * $item['quantity']), $item['product_id'], $item);
	}

	if (fflcommerce_cart::show_retail_price()) {
		$subtotal = fflcommerce_cart::get_cart_subtotal(true, false, true);
	}	else if (fflcommerce_cart::show_retail_price() && FFLCommerce_Base::get_options()->get('fflcommerce_prices_include_tax') == 'no') {
		$subtotal = fflcommerce_cart::get_cart_subtotal(true, true);
	} else {
		$subtotal = fflcommerce_cart::$cart_contents_total_ex_tax + fflcommerce_cart::$shipping_total;
		$subtotal = fflcommerce_price($subtotal, array('ex_tax_label' => 1));
	}

	$tax = array();
	foreach (fflcommerce_cart::get_applied_tax_classes() as $tax_class) {
		if (fflcommerce_cart::get_tax_for_display($tax_class)) {
			$tax[$tax_class] = fflcommerce_cart::get_tax_amount($tax_class);
		}
	}

	$shipping = fflcommerce_cart::get_cart_shipping_total(true, true).'<small>'.fflcommerce_cart::get_cart_shipping_title().'</small>';
	$discount = '-'.fflcommerce_cart::get_total_discount();
	$total = fflcommerce_cart::get_total();

	echo json_encode(array(
		'success' => true,
		'item_price' => $price,
		'subtotal' => $subtotal,
		'shipping' => $shipping,
		'discount' => $discount,
		'tax' => $tax,
		'total' => $total,
	));
	exit;
}
add_action('wp_ajax_fflcommerce_update_item_quantity', 'fflcommerce_ajax_update_item_quantity');
add_action('wp_ajax_nopriv_fflcommerce_update_item_quantity', 'fflcommerce_ajax_update_item_quantity');
