<?php

function fflcommerce_product_list($attributes)
{
	$options = FFLCommerce_Base::get_options();

	$attributes = shortcode_atts(array(
		'number' => $options->get('fflcommerce_catalog_per_page'),
		'order_by' => 'date',
		'order' => 'desc',
		'orientation' => 'rows',
		'taxonomy' => 'product_cat',
		'terms' => '',
		'thumbnails' => 'show',
		'sku' => 'hide',
	), $attributes);

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'posts_per_page' => $attributes['number'],
		'orderby' => $attributes['order_by'],
		'order' => $attributes['order'],
		'meta_query' => array(
			array(
				'key' => 'visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN',
			),
		),
	);

	if(!empty($attributes['taxonomy']) && !empty($attributes['terms'])){
		$args['tax_query'] = array(
			array(
				'taxonomy' => $attributes['taxonomy'],
				'terms' => $attributes['terms'],
				'field' => 'slug',
			),
		);
	}

	$query = new WP_Query($args);

	remove_action('fflcommerce_before_shop_loop_item_title', 'fflcommerce_template_loop_product_thumbnail', 10);
	if ($attributes['thumbnails'] === 'show') {
		add_action('fflcommerce_before_shop_loop_item', 'fflcommerce_product_thumbnail', 10, 2);
	}
	if ($attributes['sku'] === 'show') {
		add_action('fflcommerce_after_shop_loop_item_title', 'fflcommerce_product_sku', 9, 2);
	}

	$result = fflcommerce_render_result('shortcode/product_list', array(
		'orientation' => $attributes['orientation'],
		'products' => $query->get_posts(),
		'has_thumbnails' => $attributes['thumbnails'] === 'show'
	));

	if ($attributes['sku'] === 'show') {
		remove_action('fflcommerce_after_shop_loop_item_title', 'fflcommerce_product_sku', 9);
	}
	if ($attributes['thumbnails'] === 'show') {
		remove_action('fflcommerce_before_shop_loop_item', 'fflcommerce_product_thumbnail', 10);
	}
	add_action('fflcommerce_before_shop_loop_item_title', 'fflcommerce_template_loop_product_thumbnail', 10, 2);

	return $result;
}

add_shortcode('fflcommerce_product_list', 'fflcommerce_product_list');

function fflcommerce_product_sku($post, fflcommerce_product $product)
{
	echo '<span class="sku">'.__('SKU', 'fflcommerce').': '.$product->get_sku().'</span>';
}
function fflcommerce_product_thumbnail($post, fflcommerce_product $product)
{
	echo $product->get_image('shop_thumbnail');
}
