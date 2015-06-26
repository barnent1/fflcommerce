<?php
/**
 * Product taxonomy template
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

<?php $term = get_term_by( 'slug', get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy']); ?>

<?php echo apply_filters( 'fflcommerce_product_taxonomy_header', '<h1 class="page-title">' . wptexturize( $term->name ) . '</h1>' ); ?>

<?php echo apply_filters( 'fflcommerce_product_taxonomy_description', wpautop(wptexturize($term->description)) ); ?>

<?php fflcommerce_get_template_part( 'loop', 'shop' ); ?>

<?php do_action('fflcommerce_pagination'); ?>

<?php do_action('fflcommerce_after_main_content'); ?>

<?php do_action('fflcommerce_sidebar'); ?>
<?php do_action('fflcommerce_after_sidebar'); ?>

<?php get_footer('shop'); ?>
