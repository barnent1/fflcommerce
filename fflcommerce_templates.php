<?php
/**
 * Templates are in the 'templates' folder. FFL Commerce looks for theme
 * Overides in /theme/fflcommerce/ by default, but can be overwritten with FFLCOMMERCE_TEMPLATE_URL
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * @param $template
 * @return string
 */
function fflcommerce_template_loader($template)
{
	if (is_single() && get_post_type() == 'product') {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-product'));

		$template = locate_template(array(
			'single-product.php',
			FFLCOMMERCE_TEMPLATE_URL.'single-product.php'
		));

		if (!$template) {
			$template = FFLCOMMERCE_DIR.'/templates/single-product.php';
		}
	} elseif (is_tax('product_cat')) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-products', 'fflcommerce-product_cat'));

		global $posts;
		$templates = array();
		if (count($posts)) {
			$category = get_the_terms($posts[0]->ID, 'product_cat');
			$slug = $category[key($category)]->slug;
			$templates[] = 'taxonomy-product_cat-'.$slug.'.php';
			$templates[] = FFLCOMMERCE_TEMPLATE_URL.'taxonomy-product_cat-'.$slug.'.php';
		}
		$templates[] = 'taxonomy-product_cat.php';
		$templates[] = FFLCOMMERCE_TEMPLATE_URL.'taxonomy-product_cat.php';

		$template = locate_template($templates);

		if (!$template) {
			$template = FFLCOMMERCE_DIR.'/templates/taxonomy-product_cat.php';
		}
	} elseif (is_tax('product_tag')) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-products', 'fflcommerce-product_tag'));

		global $posts;
		$templates = array();
		if (count($posts)) {
			$tag = get_the_terms($posts[0]->ID, 'product_tag');
			$slug = $tag[key($tag)]->slug;
			$templates[] = 'taxonomy-product_tag-'.$slug.'.php';
			$templates[] = FFLCOMMERCE_TEMPLATE_URL.'taxonomy-product_tag-'.$slug.'.php';
		}
		$templates[] = 'taxonomy-product_tag.php';
		$templates[] = FFLCOMMERCE_TEMPLATE_URL.'taxonomy-product_tag.php';

		$template = locate_template($templates);

		if (!$template) {
			$template = FFLCOMMERCE_DIR.'/templates/taxonomy-product_tag.php';
		}
	} elseif (is_post_type_archive('product') || is_page(fflcommerce_get_page_id('shop'))) {
		fflcommerce_add_body_class(array('fflcommerce', 'fflcommerce-shop', 'fflcommerce-products'));

		$template = locate_template(array(
			'archive-product.php',
			FFLCOMMERCE_TEMPLATE_URL.'archive-product.php'
		));

		if (!$template) {
			$template = FFLCOMMERCE_DIR.'/templates/archive-product.php';
		}
	}

	return $template;
}

add_filter('template_include', 'fflcommerce_template_loader');

//################################################################################
// Get template part (for templates like loop)
//################################################################################

function fflcommerce_get_template_part($slug, $name = '')
{
	$filename = $slug.'-'.$name.'.php';
	if ($name == 'shop') {
		// load template if found. priority order = theme, 'fflcommerce' folder in theme
		if (!locate_template(array($filename, FFLCOMMERCE_TEMPLATE_URL.$filename), true, false)) {
			// if not found then load our default, always require template
			load_template(FFLCOMMERCE_DIR.'/templates/'.$filename, false);
		}

		return;
	}
	get_template_part(FFLCOMMERCE_TEMPLATE_URL.$slug, $name);
}

//################################################################################
// Returns the template to be used ( child-theme or theme or plugin )
//################################################################################

function fflcommerce_locate_template($template)
{
	$file = locate_template(array('fflcommerce/'.$template.'.php'), false, false);
	if (empty($file)) {
		$file = FFLCOMMERCE_DIR.'/templates/'.$template.'.php';
	}

	return $file;
}

function fflcommerce_return_template($template_name)
{
	$template = locate_template(array($template_name, FFLCOMMERCE_TEMPLATE_URL.$template_name), false);
	if (!$template) {
		$template = FFLCOMMERCE_DIR.'/templates/'.$template_name;
	}

	return $template;
}

//################################################################################
// Get the reviews template (comments)
//################################################################################

function fflcommerce_comments_template($template)
{
	if (get_post_type() !== 'product') {
		return $template;
	}

	return fflcommerce_return_template('single-product-reviews.php');
}

add_filter('comments_template', 'fflcommerce_comments_template');


//################################################################################
// Get other templates (e.g. product attributes)
//################################################################################

function fflcommerce_get_template($template_name, $require_once = true)
{
	$require_once = apply_filters('fflcommerce_get_template_once', $require_once, $template_name);
	load_template(fflcommerce_return_template($template_name), $require_once);
}
