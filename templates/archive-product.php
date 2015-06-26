<?php
/**
 * Archive template
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
 */
?>

<?php get_header('shop'); ?>

<?php do_action('fflcommerce_before_main_content'); ?>

	<?php if (is_search()) : ?>
		<h1 class="page-title"><?php _e('Search Results:', 'fflcommerce'); ?> &ldquo;<?php the_search_query(); ?>&rdquo; <?php if (get_query_var('paged')) echo ' &mdash; Page '.get_query_var('paged'); ?></h1>
	<?php else : ?>
		<?php echo apply_filters( 'fflcommerce_products_list_title', '<h1 class="page-title">' . __( 'All Products', 'fflcommerce' ) . '</h1>' ); ?>
	<?php endif; ?>

	<?php
		$shop_page_id = fflcommerce_get_page_id('shop');
		$shop_page = get_post($shop_page_id);
		if(post_password_required($shop_page)):
			echo get_the_password_form($shop_page);
		else:
			echo apply_filters('the_content', $shop_page->post_content);
	?>

	<?php
	ob_start();
	fflcommerce_get_template_part( 'loop', 'shop' );
	$products_list_html = ob_get_clean();
	echo apply_filters( 'fflcommerce_products_list', $products_list_html );
	?>

	<?php do_action('fflcommerce_pagination'); ?>
<?php endif; ?>

<?php do_action('fflcommerce_after_main_content'); ?>

<?php do_action('fflcommerce_sidebar'); ?>
<?php do_action('fflcommerce_after_sidebar'); ?>

<?php get_footer('shop'); ?>
