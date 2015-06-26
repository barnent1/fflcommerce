<?php
/**
 * Product reviews template
 *
 * DISCLAIMER
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

<?php if ( comments_open() ) : ?><div id="reviews"><?php

	echo '<div id="comments">';

	$count = $wpdb->get_var( $wpdb->prepare("
		SELECT COUNT(meta_value) FROM $wpdb->commentmeta
		LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
		WHERE meta_key = 'rating'
		AND comment_post_ID = %d
		AND comment_approved = '1'
		AND meta_value > 0
	", $post->ID ) );

	$rating = $wpdb->get_var( $wpdb->prepare("
		SELECT SUM(meta_value) FROM $wpdb->commentmeta
		LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
		WHERE meta_key = 'rating'
		AND comment_post_ID = %d
		AND comment_approved = '1'
	", $post->ID ) );

	if ( $count>0 ) :

		$average = number_format($rating / $count, 2);

		echo '<div class="hreview-aggregate">';

		echo '<div class="star-rating" title="'.sprintf(__('Rated %s out of 5', 'fflcommerce'),$average).'"><span style="width:'.($average*16).'px"><span class="rating">'.apply_filters('fflcommerce_single_product_reviews_rating', $average).'</span> '.__('out of 5', 'fflcommerce').'</span></div>';

		echo '<h2>'.sprintf( _n('%s review for %s', '%s reviews for %s', $count, 'fflcommerce'), '<span class="count">'.apply_filters('fflcommerce_single_product_reviews_count', $count).'</span>', '<span class="item fn">'.wptexturize($post->post_title).'</span>').'</h2>';

		echo '</div>';

	else :
		echo '<h2>'.__('Reviews', 'fflcommerce').'</h2>';
	endif;

	$title_reply = '';

	if ( have_comments() ) :

		echo '<ol class="commentlist">';

		wp_list_comments( array( 'callback' => 'fflcommerce_comments' ) );

		echo '</ol>';

		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Previous', 'fflcommerce' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'fflcommerce' ) ); ?></div>
			</div>
		<?php endif;

		echo '<p class="add_review"><a href="#review_form" class="inline show_review_form button">'.__('Add Review', 'fflcommerce').'</a></p>';

		$title_reply = __('Add a review', 'fflcommerce');

	else :

		$title_reply = __('Be the first to review ', 'fflcommerce').'&ldquo;'.$post->post_title.'&rdquo;';

		echo '<p>'.__('There are no reviews yet, would you like to <a href="#review_form" class="inline show_review_form">submit yours</a>?', 'fflcommerce').'</p>';

	endif;

	$commenter = wp_get_current_commenter();

	echo '</div><div id="review_form_wrapper"><div id="review_form">';

	comment_form(array(
		'title_reply'         => $title_reply,
		'comment_notes_before'=> '',
		'comment_notes_after' => '',
		'fields'              => array(
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . '</label> ' . '<span class="required">*</span>' .
			            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
			'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . '</label> ' . '<span class="required">*</span>' .
			            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
		),
		'label_submit' => __('Submit Review', 'fflcommerce'),
		'logged_in_as' => '',
		'comment_field' => '
			<p class="comment-form-rating"><label for="rating">' . __('Rating', 'fflcommerce') .'</label><select name="rating" id="rating">
				<option value="">'.__('Rate...','fflcommerce').'</option>
				<option value="5">'.__('Perfect','fflcommerce').'</option>
				<option value="4">'.__('Good','fflcommerce').'</option>
				<option value="3">'.__('Average','fflcommerce').'</option>
				<option value="2">'.__('Not that bad','fflcommerce').'</option>
				<option value="1">'.__('Very Poor','fflcommerce').'</option>
			</select></p>
			<p class="comment-form-comment"><label for="comment">' . _x( 'Your Review', 'noun', 'fflcommerce' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>'
			. fflcommerce::nonce_field('comment_rating', true, false)
	));

	echo '</div></div>';


?><div class="clear"></div></div>
<?php endif; ?>