<?php
/**
 * Product Data
 *
 * Function for displaying the product data meta boxes
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
 * 
 */

/**
 * Change label for insert buttons
 */
add_filter( 'gettext', 'fflcommerce_change_insert_into_post', null, 2 );

function fflcommerce_change_insert_into_post( $translation, $original ) {

	// Check if the translation is correct
    if( ! isset( $_REQUEST['from'] ) || $original != 'Insert into Post' )
    	return $translation;

    // Modify text based on context
    switch ($_REQUEST['from']) {
    	case 'fflcommerce_variation':
    		return __('Attach to Variation', 'fflcommerce' );
    	break;
    	case 'fflcommerce_product':
    		return __('Attach to Product', 'fflcommerce' );
    	break;
    	default:
    		return $translation;
    }
}

/**
 * Product data box
 *
 * Displays the product data box, tabbed, with several panels covering price, stock etc
 *
 * @since 		1.0
 */
function fflcommerce_product_data_box() {

	global $post, $wpdb, $thepostid;
	add_action('admin_footer', 'fflcommerce_meta_scripts');
	wp_nonce_field( 'fflcommerce_save_data', 'fflcommerce_meta_nonce' );

	$thepostid = $post->ID;

	// Product Type
	$terms = get_the_terms( $thepostid, 'product_type' );
	$product_type = ($terms) ? current($terms)->slug : 'simple';
	$product_type_selector = apply_filters( 'fflcommerce_product_type_selector', array(
			'simple'		=> __('Simple', 'fflcommerce'),
			'downloadable'	=> __('Downloadable', 'fflcommerce'),
			'grouped'		=> __('Grouped', 'fflcommerce'),
			'virtual'		=> __('Virtual', 'fflcommerce'),
			'variable'		=> __('Variable', 'fflcommerce'),
			'external'		=> __('External / Affiliate', 'fflcommerce')
	));
	$product_type_select = '<div class="product-type-label">'.__('Product Type', 'fflcommerce').'</div><select id="product-type" name="product-type"><optgroup label="' . __('Product Type', 'fflcommerce') . '">';
	foreach ( $product_type_selector as $value => $label ) {
		$product_type_select .= '<option value="' . $value . '" ' . selected( $product_type, $value, false ) .'>' . $label . '</option>';
	}
	$product_type_select .= '</optgroup></select><div class="clear"></div>';
	?>

	<div class="panels">
		<span class="fflcommerce_product_data_type"><?php echo $product_type_select; ?></span>
		<ul class="product_data_tabs tabs" style="display:none;">
			<li class="general_tab active">
				<a href="#general"><?php _e('General', 'fflcommerce'); ?></a>
			</li>

			<li class="advanced_tab">
				<a href="#tax"><?php _e('Advanced', 'fflcommerce') ?></a>
			</li>

			<?php if (FFLCommerce_Base::get_options()->get('fflcommerce_manage_stock') == 'yes') : ?>
			<li class="inventory_tab">
				<a href="#inventory"><?php _e('Inventory', 'fflcommerce'); ?></a>
			</li>
			<?php endif; ?>

			<li class="attributes_tab">
				<a href="#attributes"><?php _e('Attributes', 'fflcommerce'); ?></a>
			</li>

			<li class="grouped_tab">
				<a href="#grouped"><?php _e('Grouping', 'fflcommerce') ?></a>
			</li>

			<li class="file_tab">
				<a href="#files"><?php _e('Download', 'fflcommerce') ?></a>
			</li>

			<?php do_action('fflcommerce_product_write_panel_tabs'); ?>
			<?php do_action('product_write_panel_tabs'); ?>
		</ul>

		<div id="general" class="panel fflcommerce_options_panel">
			<fieldset>
			<?php
				// Visibility
				$args = array(
					'id'            => 'product_visibility',
					'label'         => __('Visibility','fflcommerce'),
					'options'       => array(
						'visible'	    => __('Catalog & Search','fflcommerce'),
						'catalog'	    => __('Catalog Only','fflcommerce'),
						'search'	    => __('Search Only','fflcommerce'),
						'hidden'	    => __('Hidden','fflcommerce')
					),
					'selected'      => get_post_meta( $post->ID, 'visibility', true )
				);
				echo FFLCommerce_Forms::select( $args );

				// Featured
				$args = array(
					'id'            => 'featured',
					'label'         => __('Featured?','fflcommerce'),
					'desc'          => __('Enable this option to feature this product', 'fflcommerce'),
					'value'         => false
				);
				echo FFLCommerce_Forms::checkbox( $args );
			?>
			</fieldset>
			<fieldset>
			<?php
				// SKU
				if ( FFLCommerce_Base::get_options()->get('fflcommerce_enable_sku') !== 'no' ) {
					$args = array(
						'id'            => 'sku',
						'label'         => __('SKU','fflcommerce'),
						'placeholder'   => $post->ID,
					);
					echo FFLCommerce_Forms::input( $args );
				}
				//Brand
				if ( FFLCommerce_Base::get_options()->get('fflcommerce_enable_brand') !== 'no' ) {
					$args = array(
						'id'            => 'brand',
						'label'         => __('Brand','fflcommerce'),
					);
					echo FFLCommerce_Forms::input( $args );
				}
				//GTIN
				if ( FFLCommerce_Base::get_options()->get('fflcommerce_enable_gtin ') !== 'no' ) {
					$args = array(
						'id'            => 'gtin',
						'label'         => __('GTIN ','fflcommerce'),
					);
					echo FFLCommerce_Forms::input( $args );
				}
				//MPN
				if ( FFLCommerce_Base::get_options()->get('fflcommerce_enable_mpn') !== 'no' ) {
					$args = array(
						'id'            => 'mpn',
						'label'         => __('MPN','fflcommerce'),
					);
					echo FFLCommerce_Forms::input( $args );
				}
			?>
			</fieldset>

			<fieldset id="price_fieldset">
			<?php
				// Regular Price
				$args = array(
					'id'            => 'regular_price',
					'label'         => __('Regular Price','fflcommerce'),
					'after_label'   => ' ('.get_fflcommerce_currency_symbol().')',
					'type'          => 'number',
					'step'          => 'any',
					'placeholder'   => __('Price Not Announced','fflcommerce'),
				);
				echo FFLCommerce_Forms::input( $args );

				// Sale Price
				$args = array(
					'id'            => 'sale_price',
					'label'         => __('Sale Price','fflcommerce'),
					'after_label'   => ' ('.get_fflcommerce_currency_symbol(). __(' or %','fflcommerce') . ')',
					'desc'          => '<a href="#" class="sale_schedule">'.__('Schedule','fflcommerce').'</a>',
					'placeholder'   => __('15% or 19.99','fflcommerce'),
				);
				echo FFLCommerce_Forms::input( $args );

				// Sale Price date range
				// TODO: Convert this to a helper somehow?
				$field = array( 'id' => 'sale_price_dates', 'label' => __('On Sale Between', 'fflcommerce') );

				$sale_price_dates_from = get_post_meta($thepostid, 'sale_price_dates_from', true);
				$sale_price_dates_to = get_post_meta($thepostid, 'sale_price_dates_to', true);

				echo '<p class="form-field sale_price_dates_fields">'.__('Current time:', 'fflcommerce').' '.current_time('Y-m-d H:i').'</p>';
				echo '	<p class="form-field sale_price_dates_fields">
							<label for="' . esc_attr( $field['id'] ) . '_from">'.$field['label'].'</label>
							<input type="text" class="short date-pick" name="' . esc_attr( $field['id'] ) . '_from" id="' . esc_attr( $field['id'] ) . '_from" value="';
				if ($sale_price_dates_from) echo date('Y-m-d H:i', $sale_price_dates_from);
				echo '" placeholder="' . __('From', 'fflcommerce') . ' (' . date('Y-m-d H:i'). ')" maxlength="16" />
							<input type="text" class="short date-pick" name="' . esc_attr( $field['id'] ) . '_to" id="' . esc_attr( $field['id'] ) . '_to" value="';
				if ($sale_price_dates_to) echo date('Y-m-d H:i', $sale_price_dates_to);
				echo '" placeholder="' . __('To', 'fflcommerce') . ' (' . date('Y-m-d H:i'). ')" maxlength="16" />
							<a href="#" class="cancel_sale_schedule">'.__('Cancel', 'fflcommerce').'</a>
						</p>';
			?>
			<?php do_action( 'fflcommerce_product_pricing_options' ); /* allow extensions like sales flash pro to add pricing options */ ?>
			</fieldset>

			<fieldset>
			<?php
				// External products
			$args = array(
				'id' => 'external_url',
				'label' => __('Product URL', 'fflcommerce'),
				'placeholder' => __('The URL of the external product (eg. http://www.google.com)', 'fflcommerce'),
				'extras' => array()
			);
			echo FFLCommerce_Forms::input($args);
			?>
			</fieldset>
			<?php do_action('fflcommerce_product_general_panel'); ?>
		</div>
		<div id="tax" class="panel fflcommerce_options_panel">
			<fieldset id="tax_fieldset">
				<?php
				// Tax Status
				$status = get_post_meta($post->ID, 'tax_status', true);

				if (empty($status)) {
					$status = FFLCommerce_Base::get_options()->get('fflcommerce_tax_defaults_status', 'taxable');
				}

				$args = array(
					'id' => 'tax_status',
					'label' => __('Tax Status', 'fflcommerce'),
					'options' => array(
						'taxable' => __('Taxable', 'fflcommerce'),
						'shipping' => __('Shipping', 'fflcommerce'),
						'none' => __('None', 'fflcommerce')
					),
					'selected' => $status,
				);
				echo FFLCommerce_Forms::select($args);
        ?>
				<p class="form_field tax_classes_field">
					<label for="tax_classes"><?php _e('Tax Classes', 'fflcommerce'); ?></label>
            <span class="multiselect short">
            <?php
            $_tax = new fflcommerce_tax();
            $tax_classes = $_tax->get_tax_classes();
            $selections = get_post_meta($post->ID, 'tax_classes', true);

            if (!is_array($selections)) {
	            $selections = FFLCommerce_Base::get_options()->get('fflcommerce_tax_defaults_classes', array('*'));
            }

            $checked = checked(in_array('*', $selections), true, false);

            printf(
	            '<label %s><input type="checkbox" name="tax_classes[]" value="%s" %s/> %s</label>',
	            !empty($checked) ? 'class="selected"' : '', '*', $checked, __('Standard', 'fflcommerce')
            );

            if ($tax_classes) {
              foreach ($tax_classes as $tax_class) {
	              $checked = checked(in_array(sanitize_title($tax_class), $selections), true, false);
	              printf(
		              '<label %s><input type="checkbox" name="tax_classes[]" value="%s" %s/> %s</label>',
		              !empty($checked) ? 'class="selected"' : '', sanitize_title($tax_class), $checked, __($tax_class, 'fflcommerce')
	              );
              }
            }
            ?>
            </span>
            <span class="multiselect-controls">
						<a class="check-all" href="#"><?php _e('Check All', 'fflcommerce'); ?></a>&nbsp;|
						<a class="uncheck-all" href="#"><?php _e('Uncheck All', 'fflcommerce'); ?></a>
					</span>
				</p>
			</fieldset>

			<?php if( FFLCommerce_Base::get_options()->get('fflcommerce_enable_weight') !== 'no' || FFLCommerce_Base::get_options()->get('fflcommerce_enable_dimensions', true) !== 'no' ): ?>
			<fieldset id="form_fieldset">
			<?php
				// Weight
				if( FFLCommerce_Base::get_options()->get('fflcommerce_enable_weight') !== 'no' ) {
					$args = array(
						'id'            => 'weight',
						'label'         => __( 'Weight', 'fflcommerce' ),
						'after_label'   => ' ('.FFLCommerce_Base::get_options()->get('fflcommerce_weight_unit').')',
						'type'          => 'number',
						'step'          => 'any',
						'placeholder'   => '0.00',
					);
					echo FFLCommerce_Forms::input( $args );
				}

				// Dimensions
				if( FFLCommerce_Base::get_options()->get('fflcommerce_enable_dimensions', true) !== 'no' ) {
					echo '
					<p class="form-field dimensions_field">
						<label for"product_length">'. __('Dimensions', 'fflcommerce') . ' ('.FFLCommerce_Base::get_options()->get('fflcommerce_dimension_unit').')' . '</label>
						<input type="number" step="any" name="length" class="short" value="' . get_post_meta( $thepostid, 'length', true ) . '" placeholder="'. __('Length', 'fflcommerce') . '" />
						<input type="number" step="any" name="width" class="short" value="' . get_post_meta( $thepostid, 'width', true ) . '" placeholder="'. __('Width', 'fflcommerce') . '" />
						<input type="number" step="any" name="height" class="short" value="' . get_post_meta( $thepostid, 'height', true ) . '" placeholder="'. __('Height', 'fflcommerce') . '" />
					</p>
					';
				}
			?>
			</fieldset>
			<?php endif; ?>

			<fieldset>
			<?php
				// Customizable
				$args = array(
					'id'            => 'product_customize',
					'label'         => __('Can be personalized','fflcommerce'),
					'options'       => array(
						'no'	        => __('No','fflcommerce'),
						'yes'	        => __('Yes','fflcommerce'),
					),
					'selected'      => get_post_meta( $post->ID, 'customizable', true ),
				);
				echo FFLCommerce_Forms::select( $args );

				// Customizable length
				$args = array(
					'id'            => 'customized_length',
					'label'         => __('Personalized Characters','fflcommerce'),
					'type'          => 'number',
					'value'         => get_post_meta($post->ID, 'customized_length', true),
					'placeholder'   => __('Leave blank for unlimited', 'fflcommerce'),
				);
				echo FFLCommerce_Forms::input( $args );
			?>
			</fieldset>
			<?php do_action('fflcommerce_product_tax_panel'); ?>

		</div>

		<?php if (FFLCommerce_Base::get_options()->get('fflcommerce_manage_stock')=='yes') : ?>
		<div id="inventory" class="panel fflcommerce_options_panel">
			<fieldset>
			<?php
			// manage stock
			$args = array(
				'id'            => 'manage_stock',
				'label'         => __('Manage Stock?','fflcommerce'),
				'desc'          => __('Handle stock for me', 'fflcommerce'),
				'value'         => false
			);
			echo FFLCommerce_Forms::checkbox( $args );

			?>
			</fieldset>
			<fieldset>
			<?php
			// Stock Status
			// TODO: These values should be true/false
			$args = array(
				'id'            => 'stock_status',
				'label'         => __( 'Stock Status', 'fflcommerce' ),
				'options'       => array(
					'instock'		=> __('In Stock','fflcommerce'),
					'outofstock'	=> __('Out of Stock','fflcommerce')
				)
			);
			echo FFLCommerce_Forms::select( $args );

			echo '<div class="stock_fields">';

			// Stock
			// TODO: Missing default value of 0
			$args = array(
				'id'            => 'stock',
				'label'         => __('Stock Quantity','fflcommerce'),
				'type'          => 'number',
			);
			echo FFLCommerce_Forms::input( $args );

			// Backorders
			$args = array(
				'id'            => 'backorders',
				'label'         => __('Allow Backorders?','fflcommerce'),
				'options'       => array(
					'no'		    => __('Do not allow','fflcommerce'),
					'notify'	    => __('Allow, but notify customer','fflcommerce'),
					'yes'		    => __('Allow','fflcommerce')
				)
			);
			echo FFLCommerce_Forms::select( $args );

			echo '</div>';
			?>
			</fieldset>
			<?php do_action('fflcommerce_product_inventory_panel'); ?>
		</div>
		<?php endif; ?>

		<div id="attributes" class="panel">
			<?php do_action('fflcommerce_attributes_display'); ?>
		</div>

		<div id="grouped" class="panel fflcommerce_options_panel">
			<?php
			// Grouped Products
			// TODO: Needs refactoring & a bit of love
			$posts_in = (array) get_objects_in_term( get_term_by( 'slug', 'grouped', 'product_type' )->term_id, 'product_type' );
			$posts_in = array_unique($posts_in);

			if( (bool) $posts_in ) {

				$args = array(
					'post_type'	=> 'product',
					'post_status' => 'publish',
					'numberposts' => -1,
					'orderby' => 'title',
					'order' => 'asc',
					'post_parent' => 0,
					'include' => $posts_in,
				);

				$grouped_products = get_posts($args);

				$options = array( null => '&ndash; Pick a Product Group &ndash;' );

				if( $grouped_products ) foreach( $grouped_products as $product ) {
					if ($product->ID==$post->ID) continue;

					$options[$product->ID] = $product->post_title;
				}
				// Only echo the form if we have grouped products
				$args = array(
					'id'            => 'parent_id',
					'label'         => __( 'Product Group', 'fflcommerce' ),
					'options'       => $options,
					'selected'      => $post->post_parent,
				);
				echo FFLCommerce_Forms::select( $args );
			}

			// Ordering
			$args = array(
				'id'            => 'menu_order',
				'label'         => __('Sort Order', 'fflcommerce'),
				'type'          => 'number',
				'value'         => $post->menu_order,
			);
			echo FFLCommerce_Forms::input( $args );
			$args = array(
				'id' => 'variation_order',
				'label' => __('Variation Order','fflcommerce'),
				'options' => array(
					'asort' => __('By name ascending','fflcommerce'),
					'arsort' => __('By name descending','fflcommerce'),
					'ksort' => __('From first to last key','fflcommerce'),
					'krsort' => __('From last to first key','fflcommerce'),
					'shuffle' => __('Random','fflcommerce')
				),
				'selected' => get_post_meta( $post->ID, 'variation_order', true )
			);
			echo FFLCommerce_Forms::select( $args );
			?>
			<?php do_action('fflcommerce_product_grouped_panel'); ?>
		</div>

		<div id="files" class="panel fflcommerce_options_panel">
			<fieldset>
			<?php

			// DOWNLOADABLE OPTIONS
			// File URL
			// TODO: Refactor this into a helper
			$file_path = get_post_meta($post->ID, 'file_path', true);
			$field = array( 'id' => 'file_path', 'label' => __('File Path', 'fflcommerce') );
			echo '<p class="form-field"><label for="' . esc_attr( $field['id'] ) . '">'.$field['label'].':</label>
				<input type="text" class="file_path" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($file_path).'" placeholder="'.site_url().'" />
				<input type="button"  class="upload_file_button button" data-postid="'.esc_attr($post->ID).'" value="'.__('Upload a file', 'fflcommerce').'" />
			</p>';

			// Download Limit
			$args = array(
				'id'            => 'download_limit',
				'label'         => __( 'Download Limit', 'fflcommerce' ),
				'type'          => 'number',
				'desc'          => __( 'Leave blank for unlimited re-downloads', 'fflcommerce' ),
			);
			echo FFLCommerce_Forms::input( $args );

			do_action( 'additional_downloadable_product_type_options' );
			?>
			</fieldset>
			<?php do_action('fflcommerce_product_files_panel'); ?>
		</div>

		<?php do_action('fflcommerce_product_write_panels'); ?>
		<?php do_action('product_write_panels'); ?>
	</div>
	<?php
}

add_action('fflcommerce_attributes_display', 'attributes_display');
function attributes_display() { ?>

	<div class="toolbar">

		<button type="button" class="button button-secondary add_attribute"><?php _e('Add Attribute', 'fflcommerce'); ?></button>
		<select name="attribute_taxonomy" class="attribute_taxonomy">
			<option value="" data-type="custom"><?php _e('Custom product attribute', 'fflcommerce'); ?></option>
			<?php
				global $post;
				$attribute_taxonomies = fflcommerce_product::getAttributeTaxonomies();
				if ( $attribute_taxonomies ) :
			    	foreach ($attribute_taxonomies as $tax) :
						$label = ($tax->attribute_label) ? $tax->attribute_label : $tax->attribute_name;
						$attributes = (array) get_post_meta($post->ID, 'product_attributes', true);
						echo '<option value="'.esc_attr( sanitize_title($tax->attribute_name) ).'" data-type="'.esc_attr( $tax->attribute_type ).'">'.esc_attr( $label ).'</option>';
			    	endforeach;
			    endif;
			?>
		</select>

	</div>
	<div class="fflcommerce_attributes_wrapper">

		<?php do_action('fflcommerce_display_attribute'); ?>

	</div>
	<div class="clear"></div>
<?php
}

add_action('fflcommerce_display_attribute', 'display_attribute');
function display_attribute() {

	global $post;
	// TODO: This needs refactoring

	// This is getting all the taxonomies
	$attribute_taxonomies = fflcommerce_product::getAttributeTaxonomies();

	// Sneaky way of doing sort by desc
	$attribute_taxonomies = array_reverse($attribute_taxonomies);

	// This is whats applied to the product
	$attributes = get_post_meta($post->ID, 'product_attributes', true);

	$i = -1;
	foreach ($attribute_taxonomies as $tax) :

		$i++;
		$attribute = array();

		$attribute_taxonomy_name = sanitize_title($tax->attribute_name);
		if (isset($attributes[$attribute_taxonomy_name])) $attribute = $attributes[$attribute_taxonomy_name];
		$position = (isset($attribute['position'])) ? $attribute['position'] : -1;

		if ( $position >= 0 ) {
			$allterms = wp_get_object_terms( $post->ID, 'pa_'.$attribute_taxonomy_name, array( 'orderby' => 'slug' ) );
		} else {
			$allterms = array();
		}
		$has_terms = ! ( is_wp_error( $allterms ) || empty( $allterms ) );

		$term_slugs = array();
		if ( ! is_wp_error($allterms) && ! empty($allterms) ) :
			foreach ($allterms as $term) :
				$term_slugs[] = $term->slug;
			endforeach;
		endif;
		?>

		<div class="postbox attribute <?php if ( $has_terms ) echo 'closed'; ?> <?php echo esc_attr( $attribute_taxonomy_name ); ?>" data-attribute-name="<?php echo esc_attr( $attribute_taxonomy_name ); ?>" rel="<?php echo $position; ?>"  <?php if ( !$has_terms ) echo 'style="display:none"'; ?>>
			<button type="button" class="hide_row button"><?php _e('Remove', 'fflcommerce'); ?></button>
			<div class="handlediv" title="<?php _e('Click to toggle', 'fflcommerce') ?>"><br></div>
			<h3 class="handle">
			<?php $label = ($tax->attribute_label) ? $tax->attribute_label : $tax->attribute_name;
			echo esc_attr ( $label ); ?>
			</h3>

			<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( sanitize_title ( $tax->attribute_name ) ); ?>" />
			<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
			<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
			<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />

			<div class="inside">
				<table>
					<tr>
						<td class="options">
							<input type="text" class="attribute-name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $label ); ?>" disabled="disabled" />

							<div>
								<label>
									<input type="checkbox" <?php checked(boolval( isset($attribute['visible']) ? $attribute['visible'] : 1 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /><?php _e('Display on product page', 'fflcommerce'); ?>
								</label>

								<?php if ($tax->attribute_type!="select") : // always disable variation for select elements ?>
								<label class="attribute_is_variable">
									<input type="checkbox" <?php checked(boolval( isset($attribute['variation']) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /><?php _e('Is for variations', 'fflcommerce'); ?>
								</label>
								<?php endif; ?>
							</div>
						</td>
						<td class="value">
							<?php if ($tax->attribute_type=="select") : ?>
								<select name="attribute_values[<?php echo $i ?>]">
									<option value=""><?php _e('Choose an option&hellip;', 'fflcommerce'); ?></option>
									<?php
									if (taxonomy_exists('pa_'.$attribute_taxonomy_name)) :
										$terms = get_terms( 'pa_'.$attribute_taxonomy_name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );
										if ($terms) :
											foreach ($terms as $term) :
												printf('<option value="%s" %s>%s</option>'
													, $term->name
													, selected(in_array($term->slug, $term_slugs), true, false)
													, $term->name);
											endforeach;
										endif;
									endif;
									?>
								</select>

							<?php elseif ($tax->attribute_type=="multiselect") : ?>

								<div class="multiselect">
									<?php
									if (taxonomy_exists('pa_'.$attribute_taxonomy_name)) :
										$terms = get_terms( 'pa_'.$attribute_taxonomy_name, array( 'orderby' => 'slug', 'hide_empty' => '0' ) );
										if ($terms) :
											foreach ($terms as $term) :
												$checked = checked(in_array($term->slug, $term_slugs), true, false);
												printf('<label %s><input type="checkbox" name="attribute_values[%d][]" value="%s" %s/> %s</label>'
													, !empty($checked) ? 'class="selected"' : ''
													, $i
													, $term->slug
													, $checked
													, $term->name);
											endforeach;
										endif;
									endif;
									?>
								</div>
								<div class="multiselect-controls">
									<a class="check-all" href="#"><?php _e('Check All','fflcommerce'); ?></a>&nbsp;|
									<a class="uncheck-all" href="#"><?php _e('Uncheck All','fflcommerce');?></a>&nbsp;|
									<a class="toggle" href="#"><?php _e('Toggle','fflcommerce');?></a>&nbsp;|
									<a class="show-all" href="#"><?php _e('Show All','fflcommerce'); ?></a>
								</div>

							<?php elseif ($tax->attribute_type=="text") : ?>
								<textarea name="attribute_values[<?php echo esc_attr( $i ); ?>]"><?php
									if ($allterms) :
										$prettynames = array();
										foreach ($allterms as $term) :
											$prettynames[] = $term->name;
										endforeach;
										echo esc_textarea( implode(',', $prettynames) );
									endif;
								?></textarea>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php endforeach; ?>
	<?php
	// Custom Attributes
	if ( ! empty( $attributes )) foreach ($attributes as $attribute) :
		if ($attribute['is_taxonomy']) continue;

		$i++;

		$position = (isset($attribute['position'])) ? $attribute['position'] : 0;

		?>
		<div class="postbox attribute closed <?php echo sanitize_title($attribute['name']); ?>" rel="<?php echo isset($attribute['position']) ? $attribute['position'] : 0; ?>">
			<button type="button" class="hide_row button"><?php _e('Remove', 'fflcommerce'); ?></button>
			<div class="handlediv" title="<?php _e('Click to toggle', 'fflcommerce') ?>"><br></div>
			<h3 class="handle"><?php echo esc_attr( $attribute['name'] ); ?></h3>

			<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
			<input type="hidden" name="attribute_enabled[<?php echo $i; ?>]" value="1" />
			<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />

			<div class="inside">
				<table>
					<tr>
						<td class="options">
							<input type="text" class="attribute-name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>" />

							<div>
								<label>
									<input type="checkbox" <?php checked(boolval( isset($attribute['visible']) ? $attribute['visible'] : 0 ), true); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /><?php _e('Display on product page', 'fflcommerce'); ?>
								</label>

								<label class="attribute_is_variable">
									<input type="checkbox" <?php checked(boolval( isset($attribute['variation']) ? $attribute['variation'] : 0 ), true); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /><?php _e('Is for variations', 'fflcommerce'); ?>
								</label>
							</div>
						</td>

						<td class="value">
							<textarea name="attribute_values[<?php echo esc_attr( $i ); ?>]" cols="5" rows="2"><?php echo esc_textarea( apply_filters('fflcommerce_product_attribute_value_custom_edit',$attribute['value'], $attribute) ); ?></textarea>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php endforeach; ?>
<?php }
