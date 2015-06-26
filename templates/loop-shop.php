<?php
/**
 * Loop shop template
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

<?php
global $columns, $per_page;

do_action('fflcommerce_before_shop_loop');

$loop = 0;

if ( ! isset( $columns ) || ! $columns ) $columns = apply_filters( 'loop_shop_columns', 3 );

//only start output buffering if there are products to list
if ( have_posts() ) {
	ob_start();
}

if ( have_posts()) : while ( have_posts() ) : the_post(); $_product = new fflcommerce_product( $post->ID ); $loop++;

	?>
	<li class="product <?php if ($loop%$columns==0) echo 'last'; if (($loop-1)%$columns==0) echo 'first'; ?>">

		<?php do_action('fflcommerce_before_shop_loop_item'); ?>

		<a href="<?php the_permalink(); ?>">

			<?php do_action('fflcommerce_before_shop_loop_item_title', $post, $_product); ?>

			<strong><?php the_title(); ?></strong>

			<?php do_action('fflcommerce_after_shop_loop_item_title', $post, $_product); ?>

		</a>

		<?php do_action('fflcommerce_after_shop_loop_item', $post, $_product); ?>

	</li><?php

	if ( $loop == $per_page ) break;

endwhile; endif;

if ( $loop == 0 ) :

	$content = '<p class="info">'.__('No products found which match your selection.', 'fflcommerce').'</p>';

else :

	$found_posts = ob_get_clean();

	$content = '<ul class="products">' . $found_posts . '</ul><div class="clear"></div>';

endif;

echo apply_filters( 'fflcommerce_loop_shop_content', $content );

do_action( 'fflcommerce_after_shop_loop' );
