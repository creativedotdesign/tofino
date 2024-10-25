<?php
class DisablePostType {

	private $is_disabled;

	// Constructor to initialize hooks
	public function __construct() {
		// Check the ACF option once and store the result
		$this->is_disabled = get_field('disable_post_type', 'option');

		// If the post type is disabled, run the appropriate actions
		if ($this->is_disabled) {
			add_action('admin_menu', [$this, 'remove_post_menu']);
			add_action('admin_bar_menu', [$this, 'remove_new_post_admin_bar'], 999);
			add_action('init', [$this, 'disable_post_type_rewrite']);
			add_action('template_redirect', [$this, 'redirect_single_post']);
		}
	}

	// Remove the "Posts" menu item from the admin dashboard
	public function remove_post_menu() {
		remove_menu_page('edit.php');
	}

	// Remove "New Post" link from the admin bar
	public function remove_new_post_admin_bar($wp_admin_bar) {
		$wp_admin_bar->remove_node('new-post');
	}

	// Disable the "post" post type on the front end and remove its rewrite rules
	public function disable_post_type_rewrite() {
		global $wp_post_types;

		if (isset($wp_post_types['post'])) {
			$wp_post_types['post']->public = false;
			$wp_post_types['post']->publicly_queryable = false;
			$wp_post_types['post']->query_var = false;
			$wp_post_types['post']->rewrite = false;
		}
	}

	// Redirect any attempt to access a single post
	public function redirect_single_post() {
		if (is_single() && get_post_type() === 'post') {
			wp_redirect(home_url(), 301);
			exit;
		}
	}
}

// Initialize the class
new DisablePostType();
