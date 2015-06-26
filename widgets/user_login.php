<?php

/**
 * User Login Widget
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
class FFLCommerce_Widget_User_Login extends WP_Widget
{
	/**
	 * Constructor
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct()
	{
		$options = array(
			'classname' => 'widget_user_login',
			'description' => __('Displays a handy login form for users', 'fflcommerce')
		);

		parent::__construct('user-login', __('FFL Commerce: Login', 'fflcommerce'), $options);
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
		extract($args);

		// Print the widget wrapper
		echo $before_widget;

		if (is_user_logged_in()) {
			// Get current user instance
			global $current_user;

			// Print title
			$title = ($instance['title_user']) ? $instance['title_user'] : __('Hey %s!', 'fflcommerce');
			if ($title) {
				echo $before_title.sprintf($title, ucwords($current_user->display_name)).$after_title;
			}

			// Create the default set of links
			$links = apply_filters('fflcommerce_widget_logout_user_links', array(
				__('My Account', 'fflcommerce') => get_permalink(fflcommerce_get_page_id('myaccount')),
				__('Change Password', 'fflcommerce') => get_permalink(fflcommerce_get_page_id('change_password')),
				__('Logout', 'fflcommerce') => wp_logout_url(home_url()),
			));
		} else {
			// Print title
			$title = ($instance['title_guest']) ? $instance['title_guest'] : __('Login', 'fflcommerce');
			if ($title) {
				echo $before_title.$title.$after_title;
			}

			do_action('fflcommerce_widget_login_before_form');

			// Get redirect URI
			$redirect_to = apply_filters('fflcommerce_widget_login_redirect', get_permalink(fflcommerce_get_page_id('myaccount')));
			$fields = array();
			// Support for other plugins which uses GET parameters
			$fields = apply_filters('fflcommerce_get_hidden_fields', $fields);

			echo "<form action='".esc_url(wp_login_url($redirect_to))."' method='post' class='fflcommerce_login_widget'>";
			foreach ($fields as $key => $value) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}

			// Username
			echo "
			<p>
				<label for='log'>".__('Username', 'fflcommerce')."</label>
				<input type='text' name='log' id='log' class='input-text username' />
			</p>
			";

			// Password
			echo "
			<p>
				<label for='pwd'>".__('Password', 'fflcommerce')."</label>
				<input type='password' name='pwd' id='pwd' class='input-text password' />
			</p>
			";

			echo "
			<p>
				<input type='submit' name='submit' value='".__('Login', 'fflcommerce')."' class='input-submit' />
				<a class='forgot' href='".esc_url(wp_lostpassword_url($redirect_to))."'>".__('Forgot it?', 'fflcommerce')."</a>
			</p>
			";

			if (FFLCommerce_Base::get_options()->get('fflcommerce_enable_signup_form') == 'yes') {
				echo '<p class="register">';
				wp_register(__('New user?', 'fflcommerce').' ', '');
				echo '</p>';
			}

			echo "</form>";

			do_action('fflcommerce_widget_login_after_form');

			$links = apply_filters('fflcommerce_widget_login_user_links', array());
		}

		// Loop & print out the links
		if ($links) {
			echo "
			<nav role='navigation'>
				<ul class='pagenav'>";

			foreach ($links as $title => $href) {
				$href = esc_url($href);
				echo "<li><a title='Go to {$title}' href='{$href}'>{$title}</a></li>";
			}

			echo "
				</ul>
			</nav>";
		}

		// Print closing widget wrapper
		echo $after_widget;
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
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title_guest'] = strip_tags($new_instance['title_guest']);
		$instance['title_user'] = strip_tags($new_instance['title_user']);

		return $instance;
	}

	/**
	 * Form
	 * Displays the form for the wordpress admin
	 *
	 * @param  array  instance
	 * @return void
	 */
	public function form($instance)
	{
		// Get instance data
		$title_guest = isset($instance['title_guest']) ? esc_attr($instance['title_guest']) : null;
		$title_user = isset($instance['title_user']) ? esc_attr($instance['title_user']) : null;

		// Title for Guests
		echo "
		<p>
			<label for='{$this->get_field_id('title_guest')}'>".__('Title (Logged Out):', 'fflcommerce')."</label>
			<input class='widefat' id='{$this->get_field_id('title_guest')}' name='{$this->get_field_name('title_guest')}' type='text' value='{$title_guest}' />
		</p>
		";

		// Title for Users
		echo "
		<p>
			<label for='{$this->get_field_id('title_user')}'>".__('Title (Logged In):', 'fflcommerce')."</label>
			<input class='widefat' id='{$this->get_field_id('title_user')}' name='{$this->get_field_name('title_user')}' type='text' value='{$title_user}' />
		</p>
		";
	}
}
