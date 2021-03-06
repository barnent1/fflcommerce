<?php
/**
 * Functions for the settings page in admin.
 *
 * The settings page contains options for the FFL Commerce plugin - this file contains functions to display
 * and save the list of options.
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
 * Defines a custom sort for the tax_rates array. The sort that is needed is that the array is sorted
 * by country, followed by state, followed by compound tax. The difference is that compound must be sorted based
 * on compound = no before compound = yes. Ultimately, the purpose of the sort is to make sure that country, state
 * are all consecutive in the array, and that within those groups, compound = 'yes' always appears last. This is
 * so that tax classes that are compounded will be executed last in comparison to those that aren't.
 * last.
 * <br>
 * <pre>
 * eg. country = 'CA', state = 'QC', compound = 'yes'<br>
 *     country = 'CA', state = 'QC', compound = 'no'<br>
 *
 * will be sorted to have <br>
 *     country = 'CA', state = 'QC', compound = 'no'<br>
 *     country = 'CA', state = 'QC', compound = 'yes' <br>
 * </pre>
 *
 * @param type $a the first object to compare with (our inner array)
 * @param type $b the second object to compare with (our inner array)
 * @return int the results of strcmp
 */
function csort_tax_rates($a, $b) {
	$str1 = '';
	$str2 = '';

	$str1 .= $a['country'] . $a['state'] . ($a['compound'] == 'no' ? 'a' : 'b');
	$str2 .= $b['country'] . $b['state'] . ($b['compound'] == 'no' ? 'a' : 'b');

	return strcmp($str1, $str2);
}

/**
 * Update options
 *
 * Updates the options on the FFL Commerce settings page.
 *
 * @since 		1.0
 * @usedby 		fflcommerce_settings()
 *
 * @param 		array $fflcommerce_options_settings List of options to go through and save
 */
function fflcommerce_update_options() {
    global $fflcommerce_options_settings;

	/* If the settings haven't been saved, don't continue at all ! */
    if ( empty($_POST['submitted']) ) return false;

	check_admin_referer( 'fflcommerce-update-settings', '_fflcommerce_csrf' );

	foreach ($fflcommerce_options_settings as $value) :

		$valueID   = !empty($value['id'])   ? $value['id']   : '';
		$valueType = !empty($value['type']) ? $value['type'] : '';

		if ( $valueType == 'tax_rates' ) {
			fflcommerce_update_taxes();
			continue;
		}

		if ( $valueType == 'coupons' ) {
			fflcommerce_update_coupons();
			continue;
		}

		if ( $valueType == 'checkbox' ) {
			update_option($valueID, isset ( $_POST[$valueID] ) ? 'yes' : 'no');
			continue;
		}

		if ( $valueType == 'multi_select_countries' ) {
			update_option($valueID, isset( $_POST[$valueID] ) ? $_POST[$valueID] : array());
			continue;
		}

		/* default back to standard image sizes if no value is entered */
		if ( $valueType == 'image_size' ) {

			$sizes = array(
				'fflcommerce_shop_tiny'      => 'fflcommerce_use_wordpress_tiny_crop',
				'fflcommerce_shop_thumbnail' => 'fflcommerce_use_wordpress_thumbnail_crop',
				'fflcommerce_shop_small'     => 'fflcommerce_use_wordpress_catalog_crop',
				'fflcommerce_shop_large'     => 'fflcommerce_use_wordpress_featured_crop'
			);

			$altSize = $sizes[$valueID];

			$dimensions = array( '_w', '_h' );
			foreach ( $dimensions as $v )
				!empty( $_POST[$valueID.$v] )
				? update_option( $valueID.$v, fflcommerce_clean($_POST[$valueID.$v]) )
				: update_option( $valueID.$v, $value['std'] );

			update_option($altSize, isset ( $_POST[$altSize] ) ? 'yes' : 'no');

			continue;

		}

		/* Price separators get a special treatment as they should allow a spaces (don't trim) */
		if ( $valueID == 'fflcommerce_price_thousand_sep' || $valueID == 'fflcommerce_price_decimal_sep' ) {
			isset($_POST[$valueID]) ? update_option($valueID, $_POST[$valueID]) : @delete_option($valueID);
			continue;
		}

		isset($valueID) && isset($_POST[$valueID])
			? update_option($valueID, fflcommerce_clean($_POST[$valueID]))
			: @delete_option($valueID);

	endforeach;

	add_action( 'fflcommerce_admin_settings_notices', 'fflcommerce_settings_updated_notice' );
	do_action ( 'fflcommerce_update_options' );

}

add_action('load-fflcommerce_page_fflcommerce_settings', 'fflcommerce_update_options');

function fflcommerce_update_taxes() {

	$taxFields = array(
		'tax_classes' => '',
		'tax_country' => '',
		'tax_rate'    => '',
		'tax_label'   => '',
		'tax_shipping'=> '',
		'tax_compound'=> ''
	);

	$tax_rates = array();

	/* Save each array key to a variable */
	foreach ($taxFields as $name => $val)
		if (isset($_POST[$name])) $taxFields[$name] = $_POST[$name];

	extract($taxFields);

	for ($i = 0; $i < sizeof($tax_classes); $i++) :

		if ( empty($tax_rate[$i]) )
			continue;

		$countries = $tax_country[$i];
		$label     = trim($tax_label[$i]);
		$rate      = number_format((float)fflcommerce_clean($tax_rate[$i]), 4);
		$class     = fflcommerce_clean($tax_classes[$i]);

		/* Checkboxes */
		$shipping = !empty($tax_shipping[$i]) ? 'yes' : 'no';
		$compound = !empty($tax_compound[$i]) ? 'yes' : 'no';

		/* Save the state & country separately from options eg US:OH */
		$states  = array();
		foreach ( $countries as $k => $countryCode ) :
			if (strstr($countryCode, ':')) :
				$cr = explode(':', $countryCode);
				$states[$cr[1]]  = $cr[0];
				unset($countries[$k]);
			endif;
		endforeach;

		/* Save individual state taxes, eg OH => US (State => Country) */
		foreach ( $states as $state => $country ) :

			$tax_rates[] = array(
				'country'      => $country,
				'label'        => $label,
				'state'        => $state,
				'rate'         => $rate,
				'shipping'     => $shipping,
				'class'        => $class,
				'compound'     => $compound,
				'is_all_states'=> false //determines if admin panel should show 'all_states'
			);

		endforeach;

		foreach ( $countries as $country ) :

			/* Countries with states */
			if ( fflcommerce_countries::country_has_states($country)) {

				foreach (array_keys(fflcommerce_countries::$states[$country]) as $st) :
					$tax_rates[] = array(
						'country'      => $country,
						'label'        => $label,
						'state'        => $st,
						'rate'         => $rate,
						'shipping'     => $shipping,
						'class'        => $class,
						'compound'     => $compound,
						'is_all_states'=> true
					);
				endforeach;

			/* This country has no states, eg AF */
			} else {

				 $tax_rates[] = array(
					'country'      => $country,
					'label'        => $label,
					'state'        => '*',
					'rate'         => $rate,
					'shipping'     => $shipping,
					'class'        => $class,
					'compound'     => $compound,
					'is_all_states'=> false
				);

			}

		endforeach;

	endfor;

	/* Remove duplicates. */
	$tax_rates = array_values(array_unique($tax_rates, SORT_REGULAR));
	usort($tax_rates, "csort_tax_rates");
	update_option('fflcommerce_tax_rates', $tax_rates);

}

function fflcommerce_update_coupons() {

	/* Only grabbing this so as not to override the 'usage' field for a coupon when saving settings */
	$original_coupons = get_option('fflcommerce_coupons');

	$couponFields = array(
		'coupon_code'         => '',
		'coupon_type'         => '',
		'coupon_amount'       => '',
		'usage_limit'         => '',
		'product_ids'         => '',
		'exclude_product_ids' => '',
		'exclude_categories'  => '',
		'coupon_category'     => '',
		'coupon_date_from'    => '',
		'coupon_date_to'      => '',
		'individual'          => '',
		'coupon_free_shipping'=> '',
		'coupon_pay_methods'  => '',
		'order_total_min'  => '',
		'order_total_max'  => '',
	);

	$coupons = array();


	/* Save each array key to a variable */
	foreach ($couponFields as $name => $val)
		if (isset($_POST[$name])) $couponFields[$name] = $_POST[$name];

	extract($couponFields);

	for ($i = 0; $i < sizeof($coupon_code); $i++) :

		if ( empty($coupon_code[$i]) || !is_numeric($coupon_amount[$i]) ) continue;

		$amount              = fflcommerce_clean($coupon_amount[$i]);
		$code                = fflcommerce_clean($coupon_code[$i]);
		$type                = fflcommerce_clean($coupon_type[$i]);
		$limit               = !empty($usage_limit[$i])                ? $usage_limit[$i]                                    : 0;
		$min_order           = !empty($order_total_min[$i])            ? $order_total_min[$i]                                : 0;
		$max_order           = !empty($order_total_max[$i])            ? $order_total_max[$i]                                : 0;
		$from_date           = !empty($coupon_date_from[$i])           ? strtotime($coupon_date_from[$i])                    : 0;
		$free_ship           = !empty($coupon_free_shipping[$i])       ? 'yes'                                               : 'no';
		$individual_use      = !empty($individual[$i])                 ? 'yes'                                               : 'no';
		$payments            = !empty($coupon_pay_methods[$i])         ? $coupon_pay_methods[$i]                             : array();
		$category            = !empty($coupon_category[$i])            ? $coupon_category[$i]                                : array();
		$products            = !empty($product_ids[$i])                ? $product_ids[$i]                                    : array();
		$ex_products         = !empty($exclude_product_ids[$i])        ? $exclude_product_ids[$i]                            : array();
		$ex_categories       = !empty($exclude_categories[$i])         ? $exclude_categories[$i]                             : array();
		$to_date             = !empty($coupon_date_to[$i])             ? strtotime($coupon_date_to[$i]) + (60 * 60 * 24 - 1) : 0;

		if ($code && $type && $amount)
			$coupons[$code] = array(
				'code'                => $code,
				'amount'              => $amount,
				'type'                => $type,
				'products'            => $products,
				'exclude_products'    => $ex_products,
				'exclude_categories'  => $ex_categories,
				'coupon_pay_methods'  => $payments,
				'coupon_category'     => $category,
				'date_from'           => $from_date,
				'date_to'             => $to_date,
				'individual_use'      => $individual_use,
				'coupon_free_shipping'=> $free_ship,
				'usage_limit'         => $limit,
				'order_total_min'     => $min_order,
				'order_total_max'     => $max_order,
				'usage'               => !empty($original_coupons[$code]['usage']) ? $original_coupons[$code]['usage'] : 0
			);
	endfor;

	update_option('fflcommerce_coupons', $coupons);

}

/**
 * Admin fields
 *
 * Loops though the FFL Commerce options array and outputs each field.
 *
 * @since 		1.0
 * @usedby 		fflcommerce_settings()
 *
 * @param 		array $options List of options to go through and save
 */
function fflcommerce_admin_fields($options) {
    ?>
    <h2 class="nav-tab-wrapper" id="fflcommerce-nav-tab-wrapper">
        <?php
        $counter = 1;
        foreach ($options as $value) {
            if ('tab' == $value['type']) :
                echo '<a href="#' . $value['type'] . $counter . '" class="nav-tab" style="font-size:14px; margin-right:0px">' . $value['tabname'] . '</a>' . "\n";
                $counter++;
            endif;
        }
        ?>
    </h2>
    <div>
        <!-- <p class="submit"><input name="save" type="submit" value="<?php _e('Save changes', 'fflcommerce') ?>" /></p> -->
		<?php fflcommerce_admin_option_display($options); ?>
        <p class="submit"><input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'fflcommerce') ?>" /></p>
    </div>
    <script type="text/javascript">
        jQuery(function($) {
            // Tabs
            jQuery('#fflcommerce-nav-tab-wrapper').show();
            jQuery('#fflcommerce-nav-tab-wrapper a:first').addClass('nav-tab-active');
            jQuery('div.panel:not(div.panel:first)').hide();
            jQuery('#fflcommerce-nav-tab-wrapper a').click(function(){
                jQuery('#fflcommerce-nav-tab-wrapper a').removeClass('nav-tab-active');
                jQuery(this).addClass('nav-tab-active');
                jQuery('div.panel').hide();
                jQuery( jQuery(this).attr('href') ).show();

                jQuery.cookie('fflcommerce_settings_tab_index', jQuery(this).index('#fflcommerce-nav-tab-wrapper a'))

                return false;
            });

    <?php if (isset($_COOKIE['fflcommerce_settings_tab_index']) && $_COOKIE['fflcommerce_settings_tab_index'] > 0) : ?>

                    jQuery('ul.tabs li:eq(<?php echo $_COOKIE['fflcommerce_settings_tab_index']; ?>) a').click();

    <?php endif; ?>

                // Countries
                jQuery('select#fflcommerce_allowed_countries').change(function(){
                    if (jQuery(this).val()=="specific") {
                        jQuery(this).parent().parent().next('tr.multi_select_countries').show();
                    } else {
                        jQuery(this).parent().parent().next('tr.multi_select_countries').hide();
                    }
                }).change();

                // permalink double save hack
                $.get('<?php echo admin_url('options-permalink.php') ?>');

            });
    </script>
    <?php
    flush_rewrite_rules();
}

/* Big ol' switch function for displaying different settings */
function fflcommerce_admin_option_display($options) {

	if ( empty($options) )
		return false;

	$counter = 1;
	foreach ($options as $value) :

		switch ($value['type']) :

		case 'string':
			?><tr>
				<th scope="row"><?php echo $value['name']; ?></th>
				<td><?php echo $value['desc']; ?></td>
			  </tr><?php
			break;

		case 'tab':
			?><div id="<?php echo $value['type'] . $counter; ?>" class="panel">
			  <table class="form-table"><?php
			break;

		case 'title':
			?><thead>
				<tr>
					<th scope="col" colspan="2">
						<h3 class="title"><?php echo $value['name'] ?></h3>
						<?php if ( !empty($value['desc']) ) : ?>
						<p><?php echo $value['desc']; ?></p>
						<?php endif; ?>
					</th>
				</tr>
			  </thead><?php
			break;

		case 'button':
			?><tr>
				<th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php if (!empty($value['tip'])) : ?>
					<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99" ></a>
					<?php endif; ?>
					<?php if ( !empty( $value['name'] ) ) : ?>
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					<?php endif; ?>
				</th>
				<td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<a  id="<?php echo esc_attr( $value['id'] ); ?>"
						class="button <?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
						style="<?php if ( !empty($value['css']) ) echo esc_attr( $value['css'] ); ?>"
						href="<?php if ( !empty($value['href']) ) echo esc_attr ( $value['href'] ); ?>"
					><?php if (!empty($value['desc'])) echo $value['desc']; ?></a>
				</td>
			  </tr><?php
			break;

		case 'checkbox':
			?><tr>
				<th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php if (!empty($value['tip'])) : ?>
					<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99" ></a>
					<?php endif; ?>
					<?php if ( !empty( $value['name'] ) ) : ?>
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					<?php endif; ?>
				</th>
				<td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<input
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="checkbox"
					class="fflcommerce-input fflcommerce-checkbox <?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
					style="<?php if ( !empty($value['css']) ) echo esc_attr( $value['css'] ); ?>"
					name="<?php echo esc_attr( $value['id'] ); ?>"
					<?php if (get_option($value['id']) !== false && get_option($value['id']) !== null)
					echo checked(get_option($value['id']), 'yes', false);
					else if ( isset($value['std'])) echo checked( $value['std'], 'yes', false ); ?> />
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php if (!empty($value['desc'])) echo $value['desc']; ?></label>
				</td>
			  </tr><?php
			break;

		case 'text'   :
		case 'number' :
			?><tr>
				<th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php if (!empty($value['tip'])) : ?>
					<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
					<?php endif; ?>
					<?php if ( !empty( $value['name'] ) ) : ?>
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					<?php endif; ?>
				</th>

				<td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<input name="<?php echo esc_attr( $value['id'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="<?php echo $value['type']; ?>"
						<?php if ($value['type'] == 'number' && !empty($value['restrict']) && is_array($value['restrict']) ): ?>
						min="<?php echo isset($value['restrict']['min']) ? $value['restrict']['min'] : ''; ?>"
						max="<?php echo isset($value['restrict']['max']) ? $value['restrict']['max'] : ''; ?>"
						step="<?php echo isset($value['restrict']['step']) ? $value['restrict']['step'] : 'any'; ?>"
						<?php endif; ?>
						class="regular-text <?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
						style="<?php if ( !empty($value['css']) ) echo esc_attr( $value['css'] ); ?>"
						placeholder="<?php if(!empty($value['placeholder'])) echo esc_attr ( $value['placeholder'] ); ?>"
						value="<?php if (get_option($value['id']) !== false && get_option($value['id']) !== null)
							echo esc_attr( get_option($value['id']) );
							else if ( isset($value['std'])) echo esc_attr( $value['std'] ); ?>" />
					<?php if ( !empty($value['desc']) && (!empty( $value['name'] ) && empty( $value['group'] )) ) : ?>
							<br /><small><?php echo $value['desc'] ?></small>
					<?php elseif ( !empty($value['desc']) ) : ?>
						<?php echo $value['desc'] ?>
					<?php endif; ?>
				</td>
			  </tr><?php
			break;

		case 'select':
			?><tr>
				<th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php if (!empty($value['tip'])) : ?>
					<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
					<?php endif; ?>
					<?php if ( !empty( $value['name'] ) ) : ?>
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					<?php endif; ?>
				</th>
				<td>
					<select name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							style="<?php if ( isset($value['css'])) echo esc_attr( $value['css'] ); ?>"
							class="<?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
							<?php if ( !empty($value['multiple']) ) echo 'multiple="multiple"'; ?>
							<?php if ( !empty( $value['class'] ) && $value['class'] == 'chzn-select' && !empty( $value['placeholder'] ) ) : ?>
							data-placeholder="<?php _e( esc_attr( $value['placeholder'] ) ); ?>"
							<?php endif; ?>
					>

					<?php $selected = get_option($value['id']); $selected = !empty( $selected ) ? $selected : $value['std']; ?>
					<?php foreach ($value['options'] as $key => $val) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"
						<?php if ( (!is_array($selected) && $selected == $key) || ( is_array($selected) && in_array($key, $selected) ) ) : ?>
								selected="selected"
						<?php endif; ?>
						>
							<?php echo ucfirst($val); ?>
						</option>
					<?php endforeach; ?>
					</select>
					<?php if ( !empty($value['desc']) && (!empty( $value['name'] ) && empty( $value['group'] )) ) : ?>
						<br /><small><?php echo $value['desc'] ?></small>
					<?php elseif ( !empty($value['desc']) ) : ?>
						<?php echo $value['desc'] ?>
					<?php endif; ?>
				</td>
			  </tr><?php
			break;

		case 'radio':
			?><tr>
				<th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php if (!empty($value['tip'])) : ?>
					<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
					<?php endif; ?>
					<?php echo $value['name'] ?>
				</th>
				<td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
					<?php foreach ($value['options'] as $key => $val) : ?>
					<label class="radio">
					<input type="radio"
						   name="<?php echo esc_attr( $value['id'] ); ?>"
						   id="<?php echo esc_attr( $key ); ?>"
						   value="<?php echo esc_attr( $key ); ?>"
						   class="<?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
						   <?php if (get_option($value['id']) == $key) { ?> checked="checked" <?php } ?>>
					<?php echo esc_attr( ucfirst( $val ) ); ?>
					</label><br />
					<?php endforeach; ?>
				</td>
			  </tr><?php
			break;

		case 'image_size' :

			$sizes = array(
				'fflcommerce_shop_tiny'      => 'fflcommerce_use_wordpress_tiny_crop',
				'fflcommerce_shop_thumbnail' => 'fflcommerce_use_wordpress_thumbnail_crop',
				'fflcommerce_shop_small'     => 'fflcommerce_use_wordpress_catalog_crop',
				'fflcommerce_shop_large'     => 'fflcommerce_use_wordpress_featured_crop'
			);

			$altSize = $sizes[$value['id']];

			?><tr>
				<th scope="row"><?php echo $value['name'] ?></label></th>
				<td valign="top" style="line-height:25px;height:25px;">

					<input name="<?php echo esc_attr( $value['id'] ); ?>_w"
						   id="<?php echo esc_attr( $value['id'] ); ?>_w"
						   type="number"
						   min="0"
						   style="width:60px;"
						   placeholder=<?php if (!empty($value['placeholder'])) echo $value['placeholder']; ?>
						   value="<?php if ( $size = get_option( $value['id'].'_w') ) echo $size; else echo $value['std']; ?>"
					/>

					<label for="<?php echo esc_attr( $value['id'] ); ?>_h">x</label>

					<input name="<?php echo esc_attr( $value['id'] ); ?>_h"
						   id="<?php echo esc_attr( $value['id'] ); ?>_h"
						   type="number"
						   min="0"
						   style="width:60px;"
						   placeholder=<?php if (!empty($value['placeholder'])) echo $value['placeholder']; ?>
						   value="<?php if ( $size = get_option( $value['id'].'_h') ) echo $size; else echo $value['std']; ?>"
					/>

					<input
					id="<?php echo esc_attr( $altSize ); ?>"
					type="checkbox"
					class="fflcommerce-input fflcommerce-checkbox"
					name="<?php echo esc_attr( $altSize ); ?>"
					<?php if (get_option($altSize) !== false && get_option($altSize) !== null)
					echo checked(get_option($altSize), 'yes', false); ?> />
					<label for="<?php echo esc_attr( $altSize ); ?>"> <?php echo __('Crop', 'fflcommerce'); ?></label>
					<br /><small><?php echo $value['desc'] ?></small>
				</td>
			</tr><?php
			break;

		case 'textarea':
			?><tr>
					<th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
					<td>
						<textarea <?php if (isset($value['args'])) echo $value['args'] . ' '; ?>
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								class="large-text <?php if(!empty($value['class'])) echo esc_attr ( $value['class'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								placeholder="<?php if(!empty($value['placeholder'])) echo esc_attr ( $value['placeholder'] ); ?>"
						><?php echo esc_textarea( ( get_option($value['id'])) ? stripslashes(get_option($value['id'])) : $value['std'] ); ?></textarea>
						<br /><small><?php echo $value['desc'] ?></small>
					</td>
				</tr><?php
			break;

		case 'tabend':
			?></table></div><?php
			$counter = $counter + 1;
			break;

		case 'single_select_page' :

			$args = array(
				'name'        => $value['id'],
				'id'          => $value['id'] . '" style="width: 200px;',
				'sort_column' => 'menu_order',
				'sort_order'  => 'ASC',
				'selected'    => (int) get_option($value['id'])
			);

			if ( !empty($value['args']) ) $args = wp_parse_args($value['args'], $args);
			?><tr class="single_select_page">
				<th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
				<td>
					<?php wp_dropdown_pages($args); ?>
					<br /><small><?php echo $value['desc'] ?></small>
				</td>
			</tr><?php
			break;
			case 'single_select_country' :
				$countries = fflcommerce_countries::$countries;
				$country_setting = (string) get_option($value['id']);
				if (strstr($country_setting, ':')) :
					$country = current(explode(':', $country_setting));
					$state = end(explode(':', $country_setting));
				else :
					$country = $country_setting;
					$state = '*';
				endif;
				?><tr class="multi_select_countries">
					<th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
					<td>
						<select id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" title="Country" style="width: 150px;">
						<?php
							$show_all = ($value['id'] != 'fflcommerce_default_country');
							echo fflcommerce_countries::country_dropdown_options($country, $state, false, $show_all);
						?>
						</select>
					</td>
				</tr><?php
				if (!$show_all && fflcommerce_countries::country_has_states($country) && $state == '*') fflcommerce_countries::base_country_notice();
			break;
		case 'multi_select_countries' :
			$countries = fflcommerce_countries::$countries;
			asort($countries);
			$selections = (array) get_option($value['id']);
			?><tr class="multi_select_countries">
					<th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label><?php echo $value['name'] ?></label></th>
					<td>
						<div class="multi_select_countries">
							<ul><?php
						if ($countries)
							foreach ($countries as $key => $val) :
							?><li><label>
								<input type="checkbox"
									   name="<?php echo esc_attr( $value['id'] ) . '[]'; ?>"
									   value="<?php echo esc_attr( $key ); ?>"
									   <?php if (in_array($key, $selections)) : ?>
									   checked="checked"
									   <?php endif; ?>
								/>
								<?php echo $val; ?>
								</label></li><?php
							endforeach;
						  ?></ul>
						</div>
					</td>
				</tr><?php
				break;
		case 'coupons' :
			_deprecated_argument( 'fflcommerce_admin_option_display', '1.3', 'The coupons type has no alternative. Use the new custom post Coupons Menu item under FFL Commerce.' );
			$coupons = new fflcommerce_coupons();
			$coupon_codes = $coupons->get_coupons();
		?>
		<style>
table{max-width:100%;background-color:transparent;border-collapse:collapse;border-spacing:0;}
.table{width:100%;margin-bottom:18px;}
.table th,.table td{padding:8px;line-height:18px;text-align:left;vertical-align:top;border-top:1px solid #dddddd;}
.table thead th{vertical-align:bottom;}
.table caption+thead tr:first-child th,.table caption+thead tr:first-child td,.table colgroup+thead tr:first-child th,.table colgroup+thead tr:first-child td,.table thead:first-child tr:first-child th,.table thead:first-child tr:first-child td{border-top:0;}
.table tbody+tbody{border-top:2px solid #dddddd;}
.table-condensed th,.table-condensed td{padding:4px 5px;}
.coupon-table th,.coupon-table td{padding:8px;line-height:18px;text-align:left;vertical-align:top;border-top:0px;}
</style>
			<tr><td><a href="#" class="add button" id="add_coupon"><?php _e('+ Add Coupon', 'fflcommerce'); ?></a></td></tr>

			<table class="coupons table">
				<thead>
					<tr>
						<th>Coupon</th>
						<th>Type</th>
						<th>Amount</th>
						<th>Usage</th>
						<th>Controls</th>
					</tr>
				</thead>
				<tbody>

				<?php
				/* Payment methods. */
				$payment_methods = array();
				$available_gateways = fflcommerce_payment_gateways::get_available_payment_gateways();
				if ( !empty($available_gateways) )
					foreach ( $available_gateways as $id => $info )
						$payment_methods[$id] = $info->title;

				/* Coupon types. */
				$discount_types = fflcommerce_coupons::get_coupon_types();

				/* Product categories. */
				$categories = get_terms('product_cat', array('hide_empty' => false));
				$coupon_cats = array();
				foreach($categories as $category)
					$coupon_cats[$category->term_id] = $category->name;


				$i = -1;
				if ($coupon_codes && is_array($coupon_codes) && sizeof($coupon_codes) > 0)
					foreach ($coupon_codes as $coupon) : $i++; ?>
				<tr>
				<td style="width:500px;">
				<table class="coupon-table form-table" id="coupons_table_<?php echo $i; ?>">
				<?php echo $coupon['code']; ?>

				<tbody class="couponDisplay" id="coupons_rows_<?php echo $i; ?>">
				<?php

					$selected_type = '';
					foreach ($discount_types as $type => $label)
						if ( $coupon['type'] == $type )
							$selected_type = $type;

					$options3 = array (

						array(
							'name'           => __('Code','fflcommerce'),
							'tip'            => __('The coupon code a customer enters on the cart or checkout page.','fflcommerce'),
							'id'             => 'coupon_code[' . esc_attr( $i ) . ']',
							'css'            => 'width:150px;',
							'class'          => 'coupon_code',
							'type'           => 'text',
							'std'            => esc_attr( $coupon['code'] )
						),
						array(
							'name'           => __('Type','fflcommerce'),
							'tip'            => __('Cart - Applies to whole cart<br/>Product - Applies to individual products only. You must specify individual products.','fflcommerce'),
							'id'             => 'coupon_type[' . esc_attr( $i ) . ']',
							'css'            => 'width:200px;',
							'type'           => 'select',
							'std'            => $selected_type,
							'options'        => $discount_types
						),
						array(
							'name'           => __('Amount','fflcommerce'),
							'tip'            => __('Amount this coupon is worth. If it is a percentange, just include the number without the percentage sign.','fflcommerce'),
							'id'             => 'coupon_amount[' . esc_attr( $i ) . ']',
							'css'            => 'width:60px;',
							'type'           => 'number',
							'restrict'       => array( 'min' => 0 ),
							'std'            => esc_attr( $coupon['amount'] )
						),
						array(
							'name'           => __('Usage limit','fflcommerce'),
							'desc'           => __(sprintf('Times used: %s', !empty($coupon['usage']) ? $coupon['usage'] : '0'), 'fflcommerce'),
							'placeholder'    => __('No limit','fflcommerce'),
							'tip'            => __('Control how many times this coupon may be used.','fflcommerce'),
							'id'             => 'usage_limit[' . esc_attr( $i ) . ']',
							'css'            => 'width:60px;',
							'type'           => 'number',
							'restrict'       => array( 'min' => 0 ),
							'std'            => !empty($coupon['usage_limit']) ? $coupon['usage_limit'] : ''
						),
						array(
							'name'           => __('Order subtotal','fflcommerce'),
							'placeholder'    => __('No min','fflcommerce'),
							'desc'           => __('Min', 'fflcommerce'),
							'tip'            => __('Set the required subtotal for this coupon to be valid on an order.','fflcommerce'),
							'id'             => 'order_total_min[' . esc_attr( $i ) . ']',
							'css'            => 'width:60px;',
							'type'           => 'number',
							'restrict'       => array( 'min' => 0 ),
							'std'            => !empty($coupon['order_total_min']) ? $coupon['order_total_min'] : '',
							'group'          => true
						),
						array(
							'desc'           => __('Max', 'fflcommerce'),
							'placeholder'    => __('No max','fflcommerce'),
							'id'             => 'order_total_max[' . esc_attr( $i ) . ']',
							'css'            => 'width:60px;',
							'type'           => 'number',
							'restrict'       => array( 'min' => 0 ),
							'std'            => !empty($coupon['order_total_max']) ? $coupon['order_total_max'] : '',
							'group'          => true
						),
						array(
							'name'           => __('Payment methods','fflcommerce'),
							'tip'            => __('Which payment methods are allowed for this coupon to be effective?','fflcommerce'),
							'id'             => 'coupon_pay_methods[' . esc_attr( $i ) . '][]',
							'css'            => 'width:200px;',
							'class'          => 'chzn-select',
							'type'           => 'select',
							'placeholder'    => 'Any method',
							'multiple'       => true,
							'std'            => !empty($coupon['coupon_pay_methods']) ? $coupon['coupon_pay_methods'] : '',
							'options'        => $payment_methods
						),
					);

					fflcommerce_admin_option_display($options3);

				?>

					<tr>
						<th scope="row">
							<a href="#" tip="<?php _e('Control which products this coupon can apply to.', 'fflcommerce'); ?>" class="tips" tabindex="99"></a>
							<label for="product_ids_<?php echo esc_attr( $i ); ?>"><?php _e('Products', 'fflcommerce'); ?></label>
						</th>

						<td>
							<select id="product_ids_<?php echo esc_attr( $i ); ?>" style="width:200px;" name="product_ids[<?php echo esc_attr( $i ); ?>][]" style="width:100px" class="ajax_chosen_select_products_and_variations" multiple="multiple" data-placeholder="<?php _e('Any product', 'fflcommerce'); ?>">
								<?php
									$product_ids = $coupon['products'];
									if ($product_ids) {
										foreach ($product_ids as $product_id) {
											$title = get_the_title($product_id);
											$sku   = get_post_meta($product_id, '_sku', true);
											if (!$title) continue;

											if (isset($sku) && $sku) $sku = ' (SKU: ' . $sku . ')';

											echo '<option value="'.$product_id.'" selected="selected">'. $title . $sku .'</option>';
										}
									}
								?>
							</select> <?php _e('Include', 'fflcommerce'); ?>
						</td>
					  </tr>

					<tr>
						<th scope="row"></th>
						<td style="padding-top:0px;">
							<select id="exclude_product_ids_<?php echo esc_attr( $i ); ?>" style="width:200px;" name="exclude_product_ids[<?php echo esc_attr( $i ); ?>][]" style="width:100px" class="ajax_chosen_select_products_and_variations" multiple="multiple" data-placeholder="<?php _e('Any product', 'fflcommerce'); ?>">
								<?php
									if ( !empty ( $coupon['exclude_products'] ) )
										foreach ($coupon['exclude_products'] as $product_id) {
											$title = get_the_title($product_id);
											$sku   = get_post_meta($product_id, '_sku', true);
											if (!$title) continue;

											if (isset($sku) && $sku) $sku = ' (SKU: ' . $sku . ')';

											echo '<option value="'.$product_id.'" selected="selected">'. $title . $sku .'</option>';
										}
								?>
							</select> <?php _e('Exclude', 'fflcommerce'); ?>
						</td>
					  </tr>

					<?php
					$options2 = array(
						array(
							'name'           => __('Categories','fflcommerce'),
							'desc'           => __('Include','fflcommerce'),
							'tip'            => __('Control which categories this coupon can apply to.','fflcommerce'),
							'id'             => 'coupon_category[' . esc_attr( $i ) . '][]',
							'type'           => 'select',
							'multiple'       => true,
							'std'            => !empty($coupon['coupon_category']) ? $coupon['coupon_category'] : '',
							'options'        => $coupon_cats,
							'class'          => 'chzn-select',
							'css'            => 'width:200px;',
							'placeholder'    => 'Any category',
							'group'          => true
						),
						array(
							'desc'           => __('Exclude','fflcommerce'),
							'id'             => 'exclude_categories[' . esc_attr( $i ) . '][]',
							'type'           => 'select',
							'multiple'       => true,
							'std'            => !empty($coupon['exclude_categories']) ? $coupon['exclude_categories'] : '',
							'options'        => $coupon_cats,
							'class'          => 'chzn-select',
							'css'            => 'width:200px;',
							'placeholder'    => 'Any category',
							'group'          => true
						),
						array(
							'name'           => __('Dates allowed','fflcommerce'),
							'desc'           => __('From','fflcommerce'),
							'placeholder'    => __('Any date','fflcommerce'),
							'tip'            => __('Choose between which dates this coupon is enabled.','fflcommerce'),
							'id'             => 'coupon_date_from[' . esc_attr( $i ) . ']',
							'css'            => 'width:150px;',
							'type'           => 'text',
							'class'          => 'date-pick',
							'std'            => !empty($coupon['date_from']) ? date('Y-m-d', $coupon['date_from']) : '',
							'group'          => true
						),
						array(
							'desc'           => __('To','fflcommerce'),
							'placeholder'    => __('Any date','fflcommerce'),
							'id'             => 'coupon_date_to[' . esc_attr( $i ) . ']',
							'css'            => 'width:150px;',
							'type'           => 'text',
							'class'          => 'date-pick',
							'std'            => !empty($coupon['date_to']) ? date('Y-m-d', $coupon['date_to']) : '',
							'group'          => true
						),
						array(
							'name'           => __('Misc. settings','fflcommerce'),
							'desc'           => 'Prevent other coupons',
							'tip'            => __('Prevent other coupons from being used while this one is applied to a cart.','fflcommerce'),
							'id'             => 'individual[' . esc_attr( $i ) . ']',
							'type'           => 'checkbox',
							'std'            => (isset($coupon['individual_use']) && $coupon['individual_use'] == 'yes') ? 'yes' : 'no'
						),
						array(
							'desc'           => 'Free shipping',
							'tip'            => __('Show the Free Shipping method on checkout with this enabled.','fflcommerce'),
							'id'             => 'coupon_free_shipping[' . esc_attr( $i ) . ']',
							'type'           => 'checkbox',
							'std'            => (isset($coupon['coupon_free_shipping']) && $coupon['coupon_free_shipping'] == 'yes') ? 'yes' : 'no'
						),

					);

					fflcommerce_admin_option_display($options2); ?>
					</tbody>
					</table>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(function() {
							jQuery("select#product_ids_<?php echo esc_attr( $i ); ?>").ajaxChosen({
								method: 	'GET',
								url: 		'<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>',
								dataType: 	'json',
								afterTypeDelay: 100,
								data:		{
									action: 		'fflcommerce_json_search_products_and_variations',
									security: 		'<?php echo wp_create_nonce("search-products"); ?>'
								}
							}, function (data) {

								var terms = {};

								jQuery.each(data, function (i, val) {
									terms[i] = val;
								});

								return terms;
							});
							jQuery("select#exclude_product_ids_<?php echo esc_attr( $i ); ?>").ajaxChosen({
								method: 	'GET',
								url: 		'<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>',
								dataType: 	'json',
								afterTypeDelay: 100,
								data:		{
									action: 		'fflcommerce_json_search_products_and_variations',
									security: 		'<?php echo wp_create_nonce("search-products"); ?>'
								}
							}, function (data) {

								var terms = {};

								jQuery.each(data, function (i, val) {
									terms[i] = val;
								});

								return terms;
							});
							jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
						});
						/* ]]> */
					</script>
				</td>
				<td><?php echo $discount_types[$selected_type]; ?></td>
				<td><?php echo !empty($coupon['amount']) ? $coupon['amount'] : ''; ?></td>
				<td><?php echo !empty($coupon['usage']) ? $coupon['usage'] : '0' ?></td>
				<td>
					<a class="toggleCoupon" href="#coupons_rows_<?php echo $i; ?>"><?php _e('Show', 'fflcommerce'); ?></a> /
					<a href="#" id="remove_coupon_<?php echo esc_attr( $i ); ?>" class="remove_coupon" title="<?php _e('Delete this Coupon', 'fflcommerce'); ?>"><?php _e('Delete', 'fflcommerce'); ?></a>
				</td>
				</tr>
					<?php endforeach; ?>
			<script type="text/javascript">

			jQuery('.couponDisplay').hide();

			/* <![CDATA[ */
			jQuery(function() {
				function toggle_coupons() {
					jQuery('a.toggleCoupon').click(function(e) {
						e.preventDefault();
						jQuery(this).text(jQuery(this).text() == '<?php _e('Show', 'fflcommerce'); ?>' ? '<?php _e('Hide', 'fflcommerce'); ?>' : '<?php _e('Show', 'fflcommerce'); ?>');

						var id = jQuery(this).attr('href').substr(1);
						jQuery('#' + id).toggle('slow');
					});
				}

				toggle_coupons();

				jQuery('#add_coupon').live('click', function(e){
					e.preventDefault();
					var size = jQuery('.couponDisplay').size();
					var new_coupon = '\
					<table class="coupon-table form-table" id="coupons_table_' + size + '">\
					<tbody class="couponDisplay" id="coupons_rows_[' + size + ']">\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('The coupon code a customer enters on the cart or checkout page.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="coupon_code[' + size + ']"><?php _e('Code', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input name="coupon_code[' + size + ']" id="coupon_code[' + size + ']" type="text" class="regular-text coupon_code"\
								style="width:150px;" placeholder="" value="" />\
								<br />\
								<small></small>\
							</td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Cart - Applies to whole cart<br/>Product - Applies to individual products only. You must specify individual products.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="coupon_type[' + size + ']"><?php _e('Type', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<select name="coupon_type[' + size + ']" id="coupon_type[' + size + ']" style="width:150px;">\
									<option value="fixed_cart"><?php _e('Cart Discount', 'fflcommerce'); ?></option>\
									<option value="percent"><?php _e('Cart % Discount', 'fflcommerce'); ?></option>\
									<option value="fixed_product"><?php _e('Product Discount', 'fflcommerce'); ?></option>\
									<option value="percent_product"><?php _e('Product % Discount', 'fflcommerce'); ?></option>\
									</select>\
								<br />\
								<small></small>\
							</td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Amount this coupon is worth. If it is a percentange, just include the number without the percentage sign.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="coupon_amount[' + size + ']"><?php _e('Amount', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input name="coupon_amount[' + size + ']" id="coupon_amount[' + size + ']" type="number" min="0"\
								max="" class="regular-text " style="width:60px;" value=""\
								/>\
								<br />\
								<small></small>\
							</td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Control how many times this coupon may be used.', 'fflcommerce'); ?>" class="tips"\
								tabindex="99"></a>\
								<label for="usage_limit[' + size + ']"><?php _e('Usage limit', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input name="usage_limit[' + size + ']" id="usage_limit[' + size + ']" type="number" min="0"\
								max="" class="regular-text " style="width:60px;" placeholder="<?php _e('No limit', 'fflcommerce'); ?>"\
								value="" />\
							</td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Set the required subtotal for this coupon to be valid on an order.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="order_total_min[' + size + ']"><?php _e('Order subtotal', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input name="order_total_min[' + size + ']" id="order_total_min[' + size + ']" type="number"\
								min="0" max="" class="regular-text " style="width:60px;" placeholder="<?php _e('No min', 'fflcommerce'); ?>"\
								value="" /><?php _e('Min', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row" style="padding-top:0px;"></th>\
							<td style="padding-top:0px;">\
								<input name="order_total_max[' + size + ']" id="order_total_max[' + size + ']" type="number"\
								min="0" max="" class="regular-text " style="width:60px;" placeholder="<?php _e('No max', 'fflcommerce'); ?>"\
								value="" /><?php _e('Max', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Which payment methods are allowed for this coupon to be effective?', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="coupon_pay_methods[' + size + '][]"><?php _e('Payment methods', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<select name="coupon_pay_methods[' + size + '][]" id="coupon_pay_methods[' + size + '][]" style="width:200px;"\
								class="chzn-select" multiple="multiple">\
									<?php foreach($payment_methods as $id => $label) echo '<option value="' . $id . '">' . $label . '</option>'; ?>\
								</select>\
								<br />\
								<small></small>\
							</td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Control which products this coupon can apply to.', 'fflcommerce'); ?>" class="tips"\
								tabindex="99"></a>\
								<label for="product_ids_' + size + '"><?php _e('Products', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<select id="product_ids_' + size + '" style="width:200px;" name="product_ids[' + size + '][]"\
								style="width:100px" class="ajax_chosen_select_products_and_variations"\
								multiple="multiple" data-placeholder="<?php _e('Any product', 'fflcommerce'); ?>"></select><?php _e('Include', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row"></th>\
							<td style="padding-top:0px;">\
								<select id="exclude_product_ids_' + size + '" style="width:200px;" name="exclude_product_ids[' + size + '][]"\
								style="width:100px" class="ajax_chosen_select_products_and_variations"\
								multiple="multiple" data-placeholder="<?php _e('Any product', 'fflcommerce'); ?>"></select><?php _e('Exclude', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Control which categories this coupon can apply to.', 'fflcommerce'); ?>" class="tips"\
								tabindex="99"></a>\
								<label for="coupon_category[' + size + '][]"><?php _e('Categories', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<select name="coupon_category[' + size + '][]" id="coupon_category_' + size + '" style="width:200px;"\
								class="chzn-select" multiple="multiple">\
								   <?php $categories = get_terms('product_cat', array('hide_empty' => false)); foreach($categories as $category) echo '<option value="' . $category->term_id . '">' . $category->name . '</option>'; ?>\
								</select><?php _e('Include', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<label for="exclude_categories[' + size + '][]"></label>\
							</th>\
							<td>\
								<select name="exclude_categories[' + size + '][]" id="exclude_categories_' + size + '" style="width:200px;"\
								class="chzn-select" multiple="multiple">\
									<?php $categories = get_terms('product_cat', array('hide_empty' => false)); foreach($categories as $category) echo '<option value="' . $category->term_id . '">' . $category->name . '</option>'; ?>\
								</select><?php _e('Exclude', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Choose between which dates this coupon is enabled.', 'fflcommerce'); ?>" class="tips"\
								tabindex="99"></a>\
								<label for="coupon_date_from[' + size + ']"><?php _e('Dates allowed', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input name="coupon_date_from[' + size + ']" id="coupon_date_from[' + size + ']" type="text"\
								class="regular-text date-pick" style="width:150px;" placeholder="<?php _e('Any date', 'fflcommerce'); ?>"\
								value="" /><?php _e('From', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row" style="padding-top:0px;"></th>\
							<td style="padding-top:0px;">\
								<input name="coupon_date_to[' + size + ']" id="coupon_date_to[' + size + ']" type="text" class="regular-text date-pick"\
								style="width:150px;" placeholder="<?php _e('Any date', 'fflcommerce'); ?>" value="" /><?php _e('To', 'fflcommerce'); ?></td>\
						</tr>\
						<tr>\
							<th scope="row">\
								<a href="#" tip="<?php _e('Prevent other coupons from being used while this one is applied to a cart.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
								<label for="individual[' + size + ']"><?php _e('Misc. settings', 'fflcommerce'); ?></label>\
							</th>\
							<td>\
								<input id="individual[' + size + ']" type="checkbox" class="fflcommerce-input fflcommerce-checkbox "\
								style="" name="individual[' + size + ']" />\
								<label for="individual[' + size + ']"><?php _e('Prevent other coupons', 'fflcommerce'); ?></label>\
							</td>\
						</tr>\
						<tr>\
							<th scope="row" style="padding-top:0px;">\
								<a href="#" tip="<?php _e('Show the Free Shipping method on checkout with this enabled.', 'fflcommerce'); ?>"\
								class="tips" tabindex="99"></a>\
							</th>\
							<td style="padding-top:0px;">\
								<input id="coupon_free_shipping[' + size + ']" type="checkbox" class="fflcommerce-input fflcommerce-checkbox "\
								style="" name="coupon_free_shipping[' + size + ']" />\
								<label for="coupon_free_shipping[' + size + ']"><?php _e('Free shipping', 'fflcommerce'); ?></label>\
							</td>\
						</tr>\
					</tbody>\
					</table>\
					';
					/* Add the table */
					jQuery('.coupons.table').before(new_coupon);
					jQuery('#coupons_table_' + size).hide().fadeIn('slow');

					jQuery("select#product_ids_" + size).ajaxChosen({
						method: 	'GET',
						url: 		'<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>',
						dataType: 	'json',
						afterTypeDelay: 100,
						data:		{
							action: 		'fflcommerce_json_search_products_and_variations',
							security: 		'<?php echo wp_create_nonce("search-products"); ?>'
						}
					}, function (data) {

						var terms = {};

						jQuery.each(data, function (i, val) {
							terms[i] = val;
						});

						return terms;
					});
					jQuery("select#exclude_product_ids_" + size).ajaxChosen({
						method: 	'GET',
						url: 		'<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>',
						dataType: 	'json',
						afterTypeDelay: 100,
						data:		{
							action: 		'fflcommerce_json_search_products_and_variations',
							security: 		'<?php echo wp_create_nonce("search-products"); ?>'
						}
					}, function (data) {

						var terms = {};

						jQuery.each(data, function (i, val) {
							terms[i] = val;
						});

						return terms;
					});
					jQuery('a[href="#coupons_rows_'+size+'"]').click(function(e) {
						e.preventDefault();
						jQuery('#coupons_rows_'+size).toggle('slow', function() {
							// Stuff later?
						});
					});
					jQuery(".chzn-select").chosen();
					jQuery(".tips").tooltip();
					jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
					return false;
				});
				jQuery('a.remove_coupon').live('click', function(){
					var answer = confirm("<?php _e('Delete this coupon?', 'fflcommerce'); ?>")
					if (answer) {
						jQuery('input', jQuery(this).parent().parent().children()).val('');
						jQuery(this).parent().parent().fadeOut();
					}
					return false;
				});
			});
		/* ]]> */
		</script>
						<?php
						break;
						case 'tax_rates' :
							$_tax = new fflcommerce_tax();
							$tax_classes = $_tax->get_tax_classes();
							$tax_rates = get_option('fflcommerce_tax_rates');
							$applied_all_states = array();
							?><tr>
					<th><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label><?php echo $value['name'] ?></label></th>
					<td id="tax_rates">
						<div class="taxrows">
			<?php
			$i = -1;
			if ($tax_rates && is_array($tax_rates) && sizeof($tax_rates) > 0) :

				function array_find($needle,$haystack){
					foreach($haystack as $key => $val):
						if( $needle == array( "label" => $val['label'], "compound" => $val['compound'], 'rate' => $val['rate'], 'shipping' => $val['shipping'], 'is_all_states' => $val['is_all_states'], 'class' => $val['class'] ) ):
							return $key;
						endif;
					endforeach;
					return false;
				}

				function array_compare($tax_rates) {
					$after = array();
					foreach($tax_rates as $key => $val):
						$first_two = array("label" => $val['label'], "compound" => $val['compound'], 'rate' => $val['rate'], 'shipping' => $val['shipping'], 'is_all_states' => $val['is_all_states'], 'class' => $val['class'] );
						$found = array_find($first_two,$after);
						if($found!==false):
							$combined  = $after[$found]["state"];
							$combined2 = $after[$found]["country"];
							$combined = !is_array($combined) ? array($combined) : $combined;
							$combined2 = !is_array($combined2) ? array($combined2) : $combined2;
							$after[$found] = array_merge($first_two,array( "state" => array_merge($combined,array($val['state'])), "country" => array_merge($combined2,array($val['country'])) ));
						else:
							$after = array_merge($after,array(array_merge($first_two,array("state" => $val['state'], "country" => $val['country']))));
						endif;
					endforeach;
					return $after;
				}

				$tax_rates = array_compare($tax_rates);

				foreach ($tax_rates as $rate) :

					if ( $rate['is_all_states'] && in_array(get_all_states_key($rate), $applied_all_states) )
						continue;

					$i++;// increment counter after check for all states having been applied

					echo '<p class="taxrow">
					<select name="tax_classes[' . esc_attr( $i ) . ']" title="Tax Classes">
						<option value="*">' . __('Standard Rate', 'fflcommerce') . '</option>';

					if ($tax_classes)
						foreach ($tax_classes as $class) :
							echo '<option value="' . sanitize_title($class) . '"';

							if ($rate['class'] == sanitize_title($class))
								echo 'selected="selected"';

							echo '>' . $class . '</option>';
						endforeach;

					echo '</select>

					<input type="text"
						   class="text" value="' . esc_attr( $rate['label']  ) . '"
						   name="tax_label[' . esc_attr( $i ) . ']"
						   title="' . __('Online Label', 'fflcommerce') . '"
						   placeholder="' . __('Online Label', 'fflcommerce') . '"
						   maxlength="15" />';

					echo '<select name="tax_country[' . esc_attr( $i ) . '][]" title="Country" multiple="multiple" style="width:250px;">';

					if ($rate['is_all_states']) :
						if (is_array($applied_all_states) && !in_array(get_all_states_key($rate), $applied_all_states)) :
							$applied_all_states[] = get_all_states_key($rate);
							fflcommerce_countries::country_dropdown_options($rate['country'], '*'); //all-states
						else :
							continue;
						endif;
					else :
						fflcommerce_countries::country_dropdown_options($rate['country'], $rate['state']);
					endif;

					echo '</select>

					<input type="text"
						   class="text"
						   value="' . esc_attr( $rate['rate']  ) . '"
						   name="tax_rate[' . esc_attr( $i ) . ']"
						   title="' . __('Rate', 'fflcommerce') . '"
						   placeholder="' . __('Rate', 'fflcommerce') . '"
						   maxlength="8" />%

					<label><input type="checkbox" name="tax_shipping[' . esc_attr( $i ) . ']" ';

					if (isset($rate['shipping']) && $rate['shipping'] == 'yes')
						echo 'checked="checked"';

					echo ' /> ' . __('Apply to shipping', 'fflcommerce') . '</label>

					<label><input type="checkbox" name="tax_compound[' . esc_attr( $i ) . ']" ';

					if (isset($rate['compound']) && $rate['compound'] == 'yes')
						echo 'checked="checked"';

					echo ' /> ' . __('Compound', 'fflcommerce') . '</label>

					<a href="#" class="remove button">&times;</a></p>';
				endforeach;
			endif;
			?>
						</div>
						<p><a href="#" class="add button"><?php _e('+ Add Tax Rule', 'fflcommerce'); ?></a></p>
					</td>
				</tr>
				<script type="text/javascript">
					/* <![CDATA[ */
					jQuery(function() {
						jQuery('#tax_rates a.add').live('click', function(){
							var size = jQuery('.taxrows .taxrow').size();

							// Add the row
							jQuery('<p class="taxrow"> \
								<select name="tax_classes[' + size + ']" title="Tax Classes"> \
									<option value="*"><?php _e('Standard Rate', 'fflcommerce'); ?></option><?php
			$tax_classes = $_tax->get_tax_classes();
			if ($tax_classes)
				foreach ($tax_classes as $class) :
					echo '<option value="' . sanitize_title($class) . '">' . $class . '</option>';
				endforeach;
			?></select><input type="text" class="text" name="tax_label[' + size + ']" title="<?php _e('Online Label', 'fflcommerce'); ?>" placeholder="<?php _e('Online Label', 'fflcommerce'); ?>" maxlength="15" />\
									</select><select name="tax_country[' + size + '][]" title="Country" multiple="multiple"><?php
			fflcommerce_countries::country_dropdown_options('', '', true);
			?></select><input type="text" class="text" name="tax_rate[' + size + ']" title="<?php _e('Rate', 'fflcommerce'); ?>" placeholder="<?php _e('Rate', 'fflcommerce'); ?>" maxlength="8" />%\
									<label><input type="checkbox" name="tax_shipping[' + size + ']" /> <?php _e('Apply to shipping', 'fflcommerce'); ?></label>\
									<label><input type="checkbox" name="tax_compound[' + size + ']" /> <?php _e('Compound', 'fflcommerce'); ?></label><a href="#" class="remove button">&times;</a>\
							</p>').appendTo('#tax_rates div.taxrows');
												return false;
											});
											jQuery('#tax_rates a.remove').live('click', function(){
												var answer = confirm("<?php _e('Delete this rule?', 'fflcommerce'); ?>");
												if (answer) {
													jQuery('input', jQuery(this).parent()).val('');
													jQuery(this).parent().hide();
												}
												return false;
											});
										});
										/* ]]> */
				</script>
			<?php
			break;
		case "shipping_options" :

			foreach (fflcommerce_shipping::get_all_methods() as $method) :

				$method->admin_options();

			endforeach;

			break;
		case "gateway_options" :

			foreach (fflcommerce_payment_gateways::payment_gateways() as $gateway) :

				$gateway->admin_options();

			endforeach;

			break;
	endswitch;
	endforeach;
}

/**
 * When all states are selected, filter based on country and tax class. This method
 * creates the array key for such a filter.
 *
 * @param array $tax_rate the tax rates array
 * @return string country code and tax class concatenated
 */
function get_all_states_key($tax_rate) {
    return $tax_rate['country'] . $tax_rate['class'];
}

/**
 * Prints an updated notice
 */
function fflcommerce_settings_updated_notice() {
    echo '<div id="message" class="updated"><p><strong>' . __('Your settings have been saved.', 'fflcommerce') . '</strong></p></div>';
}

/**
 * Settings page
 *
 * Handles the display of the settings page in admin.
 *
 * @since 		1.0
 * @usedby 		fflcommerce_admin_menu2()
 */
function fflcommerce_settings() {
    global $fflcommerce_options_settings;
    ?>
    <script type="text/javascript" src="<?php echo fflcommerce::assets_url(); ?>/assets/js/bootstrap-tooltip.min.js"></script>
    <div class="wrap fflcommerce">
        <div class="icon32 icon32-fflcommerce-settings" id="icon-fflcommerce"><br/></div>
        <?php do_action( 'fflcommerce_admin_settings_notices' ); ?>
        <form method="post" id="mainform" action="">
        	<?php wp_nonce_field( 'fflcommerce-update-settings', '_fflcommerce_csrf' ); ?>
    		<?php fflcommerce_admin_fields($fflcommerce_options_settings); ?>
            <input name="submitted" type="hidden" value="yes" />
        </form>
    </div>
    <?php
}
