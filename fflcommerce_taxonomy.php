<?php
/**
 * FFL Commerce Taxonomy
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tammpa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Custom Post Types
 **/
function fflcommerce_post_type()
{
	$options = FFLCommerce_Base::get_options();

	$shop_page_id = fflcommerce_get_page_id('shop');
	$base_slug = ($shop_page_id && $base_page = get_post($shop_page_id)) ? get_page_uri($shop_page_id) : 'shop';
	$category_base = ($options->get('fflcommerce_prepend_shop_page_to_urls') == 'yes') ? trailingslashit($base_slug) : '';
	$category_slug = ($options->get('fflcommerce_product_category_slug')) ? $options->get('fflcommerce_product_category_slug') : _x('product-category', 'slug', 'fflcommerce');
	$tag_slug = ($options->get('fflcommerce_product_tag_slug')) ? $options->get('fflcommerce_product_tag_slug') : _x('product-tag', 'slug', 'fflcommerce');
	$product_base = ($options->get('fflcommerce_prepend_shop_page_to_product') == 'yes') ? trailingslashit($base_slug) : trailingslashit(_x('product', 'slug', 'fflcommerce'));

	if ($options->get('fflcommerce_prepend_shop_page_to_product') == 'yes' && $options->get('fflcommerce_prepend_shop_page_to_urls') == 'yes') {
		$product_base .= trailingslashit(_x('product', 'slug', 'fflcommerce'));
	}
	if ($options->get('fflcommerce_prepend_category_to_product') == 'yes') {
		$product_base .= trailingslashit('%product_cat%');
	}
	$product_base = untrailingslashit($product_base);

	register_post_type("product",
		array(
			'labels' => array(
				'name' => __('Products', 'fflcommerce'),
				'singular_name' => __('Product', 'fflcommerce'),
				'all_items' => __('All Products', 'fflcommerce'),
				'add_new' => __('Add New', 'fflcommerce'),
				'add_new_item' => __('Add New Product', 'fflcommerce'),
				'edit' => __('Edit', 'fflcommerce'),
				'edit_item' => __('Edit Product', 'fflcommerce'),
				'new_item' => __('New Product', 'fflcommerce'),
				'view' => __('View Product', 'fflcommerce'),
				'view_item' => __('View Product', 'fflcommerce'),
				'search_items' => __('Search Products', 'fflcommerce'),
				'not_found' => __('No Products found', 'fflcommerce'),
				'not_found_in_trash' => __('No Products found in trash', 'fflcommerce'),
				'parent' => __('Parent Product', 'fflcommerce')
			),
			'description' => __('This is where you can add new products to your store.', 'fflcommerce'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'product',
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Hierarchical causes a memory leak http://core.trac.wordpress.org/ticket/15459
			'rewrite' => array('slug' => $product_base, 'with_front' => false, 'feeds' => $base_slug),
			'query_var' => true,
			'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt',/*, 'page-attributes'*/),
			'has_archive' => $base_slug,
			'show_in_nav_menus' => false,
			'menu_position' => 56,
			'menu_icon' => 'dashicons-book',
		)
	);

	register_post_type("product_variation",
		array(
			'labels' => array(
				'name' => __('Variations', 'fflcommerce'),
				'singular_name' => __('Variation', 'fflcommerce'),
				'add_new' => __('Add Variation', 'fflcommerce'),
				'add_new_item' => __('Add New Variation', 'fflcommerce'),
				'edit' => __('Edit', 'fflcommerce'),
				'edit_item' => __('Edit Variation', 'fflcommerce'),
				'new_item' => __('New Variation', 'fflcommerce'),
				'view' => __('View Variation', 'fflcommerce'),
				'view_item' => __('View Variation', 'fflcommerce'),
				'search_items' => __('Search Variations', 'fflcommerce'),
				'not_found' => __('No Variations found', 'fflcommerce'),
				'not_found_in_trash' => __('No Variations found in trash', 'fflcommerce'),
				'parent' => __('Parent Variation', 'fflcommerce')
			),
			'public' => false,
			'show_ui' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'capability_type' => 'product',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'editor', 'custom-fields'),
			'show_in_menu' => 'edit.php?post_type=product'
		)
	);

	register_taxonomy('product_type',
		array('product'),
		array(
			'hierarchical' => false,
			'show_ui' => false,
			'query_var' => true,
			'show_in_nav_menus' => false,
		)
	);

	register_taxonomy('product_cat',
		array('product'),
		array(
			'hierarchical' => true,
			'update_count_callback' => '_update_post_term_count',
			'labels' => array(
				'menu_name' => __('Categories', 'fflcommerce'),
				'name' => __('Product Categories', 'fflcommerce'),
				'singular_name' => __('Product Category', 'fflcommerce'),
				'search_items' => __('Search Product Categories', 'fflcommerce'),
				'all_items' => __('All Product Categories', 'fflcommerce'),
				'parent_item' => __('Parent Product Category', 'fflcommerce'),
				'parent_item_colon' => __('Parent Product Category:', 'fflcommerce'),
				'edit_item' => __('Edit Product Category', 'fflcommerce'),
				'update_item' => __('Update Product Category', 'fflcommerce'),
				'add_new_item' => __('Add New Product Category', 'fflcommerce'),
				'new_item_name' => __('New Product Category Name', 'fflcommerce')
			),
			'capabilities' => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('slug' => $category_base.$category_slug, 'with_front' => false, 'hierarchical' => false),
		)
	);
	register_taxonomy_for_object_type('product_cat', 'product');

	register_taxonomy('product_tag',
		array('product'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'menu_name' => __('Tags', 'fflcommerce'),
				'name' => __('Product Tags', 'fflcommerce'),
				'singular_name' => __('Product Tag', 'fflcommerce'),
				'search_items' => __('Search Product Tags', 'fflcommerce'),
				'all_items' => __('All Product Tags', 'fflcommerce'),
				'parent_item' => __('Parent Product Tag', 'fflcommerce'),
				'parent_item_colon' => __('Parent Product Tag:', 'fflcommerce'),
				'edit_item' => __('Edit Product Tag', 'fflcommerce'),
				'update_item' => __('Update Product Tag', 'fflcommerce'),
				'add_new_item' => __('Add New Product Tag', 'fflcommerce'),
				'new_item_name' => __('New Product Tag Name', 'fflcommerce')
			),
			'capabilities' => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('slug' => $category_base.$tag_slug, 'with_front' => false),
		)
	);
	register_taxonomy_for_object_type('product_tag', 'product');

	$attribute_taxonomies = fflcommerce_product::getAttributeTaxonomies();
	if ($attribute_taxonomies) {
		foreach ($attribute_taxonomies as $tax) {
			$name = 'pa_'.sanitize_title($tax->attribute_name);
			$hierarchical = true;
			if ($name) {
				register_taxonomy($name,
					array('product'),
					array(
						'hierarchical' => $hierarchical,
						'labels' => array(
							'name' => $tax->attribute_name,
							'singular_name' => $tax->attribute_name,
							'search_items' => __('Search ', 'fflcommerce').$tax->attribute_name,
							'all_items' => __('All ', 'fflcommerce').$tax->attribute_name,
							'parent_item' => __('Parent ', 'fflcommerce').$tax->attribute_name,
							'parent_item_colon' => __('Parent ', 'fflcommerce').$tax->attribute_name.':',
							'edit_item' => __('Edit ', 'fflcommerce').$tax->attribute_name,
							'update_item' => __('Update ', 'fflcommerce').$tax->attribute_name,
							'add_new_item' => __('Add New ', 'fflcommerce').$tax->attribute_name,
							'new_item_name' => __('New ', 'fflcommerce').$tax->attribute_name
						),
						'capabilities' => array(
							'manage_terms' => 'manage_product_terms',
							'edit_terms' => 'edit_product_terms',
							'delete_terms' => 'delete_product_terms',
							'assign_terms' => 'assign_product_terms',
						),
						'show_ui' => false,
						'query_var' => true,
						'show_in_nav_menus' => false,
						'rewrite' => array('slug' => $category_base.sanitize_title($tax->attribute_name), 'with_front' => false, 'hierarchical' => $hierarchical),
					)
				);
			}
		}
	}

	register_post_type("shop_order",
		array(
			'labels' => array(
				'name' => __('Orders', 'fflcommerce'),
				'singular_name' => __('Order', 'fflcommerce'),
				'all_items' => __('All Orders', 'fflcommerce'),
				'add_new' => __('Add New', 'fflcommerce'),
				'add_new_item' => __('New Order', 'fflcommerce'),
				'edit' => __('Edit', 'fflcommerce'),
				'edit_item' => __('Edit Order', 'fflcommerce'),
				'new_item' => __('New Order', 'fflcommerce'),
				'view' => __('View Order', 'fflcommerce'),
				'view_item' => __('View Order', 'fflcommerce'),
				'search_items' => __('Search Orders', 'fflcommerce'),
				'not_found' => __('No Orders found', 'fflcommerce'),
				'not_found_in_trash' => __('No Orders found in trash', 'fflcommerce'),
				'parent' => __('Parent Orders', 'fflcommerce')
			),
			'description' => __('This is where store orders are stored.', 'fflcommerce'),
			'public' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'capability_type' => 'shop_order',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'comments'),
			'has_archive' => false,
			'menu_position' => 58,
			'menu_icon' => 'dashicons-clipboard',
		)
	);

	register_taxonomy('shop_order_status',
		array('shop_order'),
		array(
			'hierarchical' => true,
			'update_count_callback' => '_update_post_term_count',
			'labels' => array(
				'name' => __('Order statuses', 'fflcommerce'),
				'singular_name' => __('Order status', 'fflcommerce'),
				'search_items' => __('Search Order statuses', 'fflcommerce'),
				'all_items' => __('All  Order statuses', 'fflcommerce'),
				'parent_item' => __('Parent Order status', 'fflcommerce'),
				'parent_item_colon' => __('Parent Order status:', 'fflcommerce'),
				'edit_item' => __('Edit Order status', 'fflcommerce'),
				'update_item' => __('Update Order status', 'fflcommerce'),
				'add_new_item' => __('Add New Order status', 'fflcommerce'),
				'new_item_name' => __('New Order status Name', 'fflcommerce')
			),
			'public' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'query_var' => true,
			'rewrite' => false,
		)
	);

	register_post_type("shop_coupon",
		array(
			'labels' => array(
				'menu_name' => __('Coupons', 'fflcommerce'),
				'name' => __('Coupons', 'fflcommerce'),
				'singular_name' => __('Coupon', 'fflcommerce'),
				'add_new' => __('Add Coupon', 'fflcommerce'),
				'add_new_item' => __('Add New Coupon', 'fflcommerce'),
				'edit' => __('Edit', 'fflcommerce'),
				'edit_item' => __('Edit Coupon', 'fflcommerce'),
				'new_item' => __('New Coupon', 'fflcommerce'),
				'view' => __('View Coupons', 'fflcommerce'),
				'view_item' => __('View Coupon', 'fflcommerce'),
				'search_items' => __('Search Coupons', 'fflcommerce'),
				'not_found' => __('No Coupons found', 'fflcommerce'),
				'not_found_in_trash' => __('No Coupons found in trash', 'fflcommerce'),
				'parent' => __('Parent Coupon', 'fflcommerce')
			),
			'description' => __('This is where you can add new coupons that customers can use in your store.', 'fflcommerce'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'shop_coupon',
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'editor'),
			'show_in_nav_menus' => false,
			'show_in_menu' => 'fflcommerce'
		)
	);

	register_post_type("shop_email",
		array(
			'labels' => array(
				'menu_name' => __('Emails', 'fflcommerce'),
				'name' => __('Emails', 'fflcommerce'),
				'singular_name' => __('Emails', 'fflcommerce'),
				'add_new' => __('Add Email', 'fflcommerce'),
				'add_new_item' => __('Add New Email', 'fflcommerce'),
				'edit' => __('Edit', 'fflcommerce'),
				'edit_item' => __('Edit Email', 'fflcommerce'),
				'new_item' => __('New Email', 'fflcommerce'),
				'view' => __('View Email', 'fflcommerce'),
				'view_item' => __('View Email', 'fflcommerce'),
				'search_items' => __('Search Email', 'fflcommerce'),
				'not_found' => __('No Emils found', 'fflcommerce'),
				'not_found_in_trash' => __('No Emails found in trash', 'fflcommerce'),
				'parent' => __('Parent Email', 'fflcommerce')
			),
			'description' => __('This is where you can add new emails that customers can receive in your store.', 'fflcommerce'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'shop_email',
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array('title', 'editor'),
			'show_in_nav_menus' => false,
			'show_in_menu' => 'fflcommerce',
		)
	);

	if ($options->get('fflcommerce_update_rewrite_rules') == '1') { // Re-generate rewrite rules
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
		$options->set('fflcommerce_update_rewrite_rules', '0');
	}
}

/**
 * Categories ordering
 */

/**
 * Add a table to $wpdb to benefit from wordpress meta api
 */
function taxonomy_metadata_wpdbfix()
{
	global $wpdb;
	$wpdb->fflcommerce_termmeta = "{$wpdb->prefix}fflcommerce_termmeta";
}

add_action('init', 'taxonomy_metadata_wpdbfix');
add_action('switch_blog', 'taxonomy_metadata_wpdbfix');

/**
 * Add product_cat ordering to get_terms
 * It enables the support a 'menu_order' parameter to get_terms for the product_cat taxonomy.
 * By default it is 'ASC'. It accepts 'DESC' too
 * To disable it, set it ot false (or 0)
 *
 * @param $clauses
 * @param $taxonomies
 * @param $args
 * @return
 */
function fflcommerce_terms_clauses($clauses, $taxonomies, $args)
{
	global $wpdb;

	// wordpress should give us the taxonomies asked when calling the get_terms function
	if (!in_array('product_cat', (array)$taxonomies) || FFLCommerce_Base::get_options()->get('fflcommerce_enable_draggable_categories') != 'yes') {
		return $clauses;
	}

	// query order
	if (isset($args['menu_order']) && !$args['menu_order']) {
		return $clauses;
	} // menu_order is false so we do not add order clause

	// query fields
	if (strpos('COUNT(*)', $clauses['fields']) === false) {
		$clauses['fields'] .= ', tm.* ';
	}

	//query join
	$clauses['join'] .= " LEFT JOIN {$wpdb->fflcommerce_termmeta} AS tm ON (t.term_id = tm.fflcommerce_term_id AND tm.meta_key = 'order') ";

	// default to ASC
	if (!isset($args['menu_order']) || !in_array(strtoupper($args['menu_order']), array('ASC', 'DESC'))) {
		$args['menu_order'] = 'ASC';
	}

	$order = "ORDER BY CAST(tm.meta_value AS SIGNED) ".$args['menu_order'];

	if ($clauses['orderby']) {
		$clauses['orderby'] = str_replace('ORDER BY', $order.',', $clauses['orderby']);
	} else {
		$clauses['orderby'] = $order;
	}

	return $clauses;
}

add_filter('terms_clauses', 'fflcommerce_terms_clauses', 10, 3);

/**
 * Reorder on category insertion
 *
 * @param int $term_id
 */
function fflcommerce_create_product_cat($term_id)
{
	$next_id = null;
	$term = get_term($term_id, 'product_cat');

	// gets the sibling terms
	$siblings = get_terms('product_cat', "parent={$term->parent}&menu_order=ASC&hide_empty=0");

	foreach ($siblings as $sibling) {
		if ($sibling->term_id == $term_id) {
			continue;
		}
		$next_id = $sibling->term_id; // first sibling term of the hierarchy level
		break;
	}

	// reorder
	fflcommerce_order_categories($term, $next_id);
}

add_action("create_product_cat", 'fflcommerce_create_product_cat');

/**
 * Delete terms metas on deletion
 *
 * @param int $term_id
 */
function fflcommerce_delete_product_cat($term_id)
{
	$term_id = (int)$term_id;

	if (!$term_id) {
		return;
	}

	global $wpdb;
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->fflcommerce_termmeta} WHERE `fflcommerce_term_id` = %d", $term_id));
}

add_action("delete_product_cat", 'fflcommerce_delete_product_cat');

/**
 * Move a category before the a  given element of its hierarchy level
 *
 * @param object $the_term
 * @param int $next_id the id of the next slibling element in save hierachy level
 * @param int $index
 * @param int $terms
 * @return int
 */
function fflcommerce_order_categories($the_term, $next_id, $index = 0, $terms = null)
{
	if (!$terms) {
		$terms = get_terms('product_cat', 'menu_order=ASC&hide_empty=0&parent=0');
	}
	if (empty($terms)) {
		return $index;
	}

	$id = $the_term->term_id;
	$term_in_level = false; // flag: is our term to order in this level of terms

	foreach ($terms as $term) {
		if ($term->term_id == $id) { // our term to order, we skip
			$term_in_level = true;
			continue; // our term to order, we skip
		}
		// the nextid of our term to order, lets move our term here
		if (null !== $next_id && $term->term_id == $next_id) {
			$index++;
			$index = fflcommerce_set_category_order($id, $index, true);
		}

		// set order
		$index++;
		$index = fflcommerece_set_category_order($term->term_id, $index);

		// if that term has children we walk through them
		$children = get_terms('product_cat', "parent={$term->term_id}&menu_order=ASC&hide_empty=0");
		if (!empty($children)) {
			$index = fflcommerce_order_categories($the_term, $next_id, $index, $children);
		}
	}

	// no nextid meaning our term is in last position
	if ($term_in_level && null === $next_id) {
		$index = fflcommerce_set_category_order($id, $index + 1, true);
	}

	return $index;
}

/**
 * Set the sort order of a category
 *
 * @param int $term_id
 * @param int $index
 * @param bool $recursive
 * @return int
 */
function fflcommerce_set_category_order($term_id, $index, $recursive = false)
{
	$term_id = (int)$term_id;
	$index = (int)$index;

	update_metadata('fflcommerce_term', $term_id, 'order', $index);

	if (!$recursive) {
		return $index;
	}

	$children = get_terms('product_cat', "parent=$term_id&menu_order=ASC&hide_empty=0");

	foreach ($children as $term) {
		$index++;
		$index = fflcommerce_set_category_order($term->term_id, $index, true);
	}

	return $index;
}

/**
 * Properly sets the WP Nav Menus items classes for FFL Commerce queried objects
 *
 * @param $menu_items
 * @param array $args
 * @return
 * @TODO set parent items classes when the shop page is not at the nav menu root
 */
function fflcommerce_nav_menu_items_classes($menu_items, $args)
{
	$options = FFLCommerce_Base::get_options();
	$shop_page_id = (int)fflcommerce_get_page_id('shop');

	// only add nav menu classes if the queried object is the Shop page or derivative (Product, Category, Tag)
	if (empty($shop_page_id) || !is_content_wrapped()) {
		return $menu_items;
	}

	$home_page_id = (int)$options->get('page_for_posts');

	foreach ((array)$menu_items as $key => $menu_item) {
		$classes = (array)$menu_item->classes;

		// unset classes set by WP on the home page item
		// shouldn't need a content wrap check as we can't get here without it  -JAP-
		if (is_content_wrapped() && $home_page_id == $menu_item->object_id) {
			$menu_items[$key]->current = false;
			unset($classes[array_search('current_page_parent', $classes)]);
			unset($classes[array_search('current-menu-item', $classes)]);
		}

		if (is_shop() && $shop_page_id == $menu_item->object_id) { // is products archive
			$menu_items[$key]->current = true;
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';
		} elseif ((is_product() || is_product_category() || is_product_tag()) && $shop_page_id == $menu_item->object_id) { // is another fflcommerce object
			$classes[] = 'current_page_parent';
			$classes[] = 'current_menu_parent';
		}

		$menu_items[$key]->classes = array_unique($classes);
	}

	return $menu_items;
}

add_filter('wp_nav_menu_objects', 'fflcommerce_nav_menu_items_classes', 20, 2);
