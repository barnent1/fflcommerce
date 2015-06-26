<?php

/**
 * Product Categories Widget
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
class FFLCommerce_Widget_Product_Categories extends WP_Widget
{
	/**
	 * Constructor
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct()
	{
		$options = array(
			'classname' => 'fflcommerce_product_categories',
			'description' => __('A list or dropdown of product categories', 'fflcommerce'),
		);

		// Create the widget
		parent::__construct('fflcommerce_product_categories', __('FFL Commerce: Product Categories', 'fflcommerce'), $options);

		// Flush cache after every save
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
	}

	/**
	 * Widget
	 * Display the widget in the sidebar
	 * Save output to the cache if empty
	 *
	 * @param  array  sidebar arguments
	 * @param  array  instance
	 */
	public function widget($args, $instance)
	{
		// Otherwise Start buffering and output the Widget
		ob_start();
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Product Categories', 'fflcommerce'),
			$instance,
			$this->id_base
		);

		// Get options
		$count = (bool)isset($instance['count']) ? $instance['count'] : false;
		$is_hierarchial = (bool)isset($instance['hierarchical']) ? $instance['hierarchical'] : false;
		$is_dropdown = (bool)isset($instance['dropdown']) ? $instance['dropdown'] : false;

		// Print the widget wrapper & title
		echo $before_widget;
		if ($title) {
			echo $before_title.$title.$after_title;
		}

		// Define options for the list
		$args = array(
			'orderby' => 'name',
			'show_count' => $count,
			'hierarchical' => $is_hierarchial,
			'taxonomy' => 'product_cat',
			'title_li' => null,
		);

		if (is_product()) {
			global $post;
			$categories = get_the_terms($post->ID, 'product_cat');
			if (!empty($categories)) {
				foreach ($categories as $id => $cat) {
					$args['current_category'] = apply_filters('fflcommerce_product_cat_widget_terms', $cat->term_id, $categories);
					break; // we can only take the first one
				}
			}
		}

		// Output as dropdown or unordered list
		if ($is_dropdown) {
			// Set up arguments
			$args['name'] = 'dropdown_product_cat';

			// Print dropdown
			// wp_dropdown_categories($args); Commented out due to wordpress bug 13258 not supporting custom taxonomies
			// See: http://core.trac.wordpress.org/ticket/13258

			fflcommerce_product_dropdown_categories($args['show_count'], $args['hierarchical']);

			// TODO: Move this javascript to its own file (plugins.js?)
			?>
			<script type='text/javascript'>
				/* <![CDATA[ */
				var dropdown = document.getElementById("dropdown_product_cat");
				function onCatChange(){
					if(dropdown.options[dropdown.selectedIndex].value !== ''){
						location.href = "<?php echo home_url(); ?>/?product_cat=" + dropdown.options[dropdown.selectedIndex].value;
					} else {
						location.href = "<?php echo get_permalink(fflcommerce_get_page_id('shop')); ?>"
					}
				}
				dropdown.onchange = onCatChange;
				/* ]]> */
			</script>
		<?php
		} else {
			// Print list of categories
			echo '<ul>';
			wp_list_categories(apply_filters('widget_product_categories_args', $args));
			echo '</ul>';
		}

		// Print closing widget wrapper
		echo $after_widget;

		// Flush output buffer and save to transient cache
		$result = ob_get_flush();
		$cache[$this->id] = $result;
		set_transient('fflcommerce_widget_cache', $cache, 3600 * 3); // 3 hours ahead
	}

	/**
	 * Update
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param  array  new instance
	 * @param  array  old instance
	 * @return  array  instance
	 */
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = (bool)isset($new_instance['count']) ? $new_instance['count'] : false;
		$instance['hierarchical'] = (bool)isset($new_instance['hierarchical']) ? $new_instance['hierarchical'] : false;
		$instance['dropdown'] = (bool)isset($new_instance['dropdown']) ? $new_instance['dropdown'] : false;

		// Flush the cache
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Flush Widget Cache
	 * Flushes the cached output
	 */
	public function flush_widget_cache()
	{
		delete_transient('fflcommerce_widget_cache');
	}

	/**
	 * Form
	 * Displays the form for the wordpress admin
	 *
	 * @param  array  instance
	 * @return void
	 */
	function form($instance)
	{
		// Get values from instance
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$dropdown = (bool)isset($instance['dropdown']) ? $instance['dropdown'] : false;
		$count = (bool)isset($instance['count']) ? $instance['count'] : false;
		$hierarchical = (bool)isset($instance['hierarchical']) ? $instance['hierarchical'] : false;

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id('title')}'>".__('Title:', 'fflcommerce')."</label>
			<input class='widefat' id='{$this->get_field_id('title')}' name='{$this->get_field_name('title')}' type='text' value='{$title}' />
		</p>";

		// As a dropdown?
		echo "
		<p>
			<input type='checkbox' class='checkbox' id='{$this->get_field_id('dropdown')}' name='{$this->get_field_name('dropdown')}'".($dropdown ? 'checked' : null)." />
			<label for='{$this->get_field_id('dropdown')}'>".__('Show as dropdown', 'fflcommerce')."</label>
			<br/>";

		// Show product count?
		echo "
			<input type='checkbox' class='checkbox' id='{$this->get_field_id('count')}' name='{$this->get_field_name('count')}'".($count ? 'checked' : null)." />
			<label for='{$this->get_field_id('count')}'>".__('Show product counts', 'fflcommerce')."</label>
			<br/>";

		// Is hierarchical?
		echo "
			<input type='checkbox' class='checkbox' id='{$this->get_field_id('hierarchical')}' name='{$this->get_field_name('hierarchical')}'".($hierarchical ? 'checked' : null)." />
			<label for='{$this->get_field_id('hierarchical')}'>".__('Show hierarchy', 'fflcommerce')."</label>
		</p>";
	}
}
