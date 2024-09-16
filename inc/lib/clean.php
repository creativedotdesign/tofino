<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 3.0.0
 */

namespace Tofino\Clean;


// Remove default post from admin menu bar
function remove_default_post_type_menu_bar($wp_admin_bar)
{
  $wp_admin_bar->remove_node('new-post');
}
add_action('admin_bar_menu', __NAMESPACE__ . '\\remove_default_post_type_menu_bar', 999);


// Remove widgets from dashboard
function remove_widgets()
{
  remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
  remove_meta_box('dashboard_activity', 'dashboard', 'normal');
  remove_meta_box('dashboard_primary', 'dashboard', 'side');
  remove_meta_box('dashboard_secondary', 'dashboard', 'side');
  remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
  remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
  remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', __NAMESPACE__ . '\\remove_widgets', 999);


// Remove WP Logo from Admin Area
function admin_bar_remove_logo()
{
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', __NAMESPACE__ . '\\admin_bar_remove_logo', 0);


// Remove comments from admin menu
function remove_comments_admin_menus()
{
  remove_menu_page('edit-comments.php');
  remove_menu_page('options-discussion');
}
add_action('admin_menu', __NAMESPACE__ . '\\remove_comments_admin_menus');


// Remove comment support from post and pages
function remove_comment_support()
{
  remove_post_type_support('post', 'comments');
  remove_post_type_support('page', 'comments');
}
add_action('init', __NAMESPACE__ . '\\remove_comment_support', 100);


// Remove items from admin bar
function remove_admin_bar_items()
{
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_node('search');
  $wp_admin_bar->remove_node('customize');
  $wp_admin_bar->remove_menu('themes');

  if (!is_admin()) {
    $wp_admin_bar->remove_node('my-account');
  }
}
add_action('wp_before_admin_bar_render', __NAMESPACE__ . '\\remove_admin_bar_items');


// Remove script version
function remove_script_version($src)
{
  $parts = explode('?ver', $src);
  return $parts[0];
}
add_filter('script_loader_src', __NAMESPACE__ . '\\remove_script_version', 15, 1);
add_filter('style_loader_src', __NAMESPACE__ . '\\remove_script_version', 15, 1);


// Fully Disable Gutenberg editor.
add_filter('use_block_editor_for_post_type', '__return_false', 10);


// Don't load Gutenberg-related stylesheets.
function remove_block_css()
{
  wp_dequeue_style('wp-block-library'); // WordPress core
  wp_dequeue_style('wp-block-library-theme'); // WordPress core
  wp_dequeue_style('wc-block-style'); // WooCommerce
  wp_dequeue_style('storefront-gutenberg-blocks'); // Storefront theme
  wp_dequeue_style('classic-theme-styles'); // Classic Styles
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\remove_block_css', 100);


function remove_extra_markup()
{
  // Remove Post Formats
  remove_theme_support('post-formats');

  // Remove the REST API lines from the HTML Header
  remove_action('wp_head', 'rest_output_link_wp_head', 10);
  remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

  // Remove gunk in header
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'feed_links', 2);
  remove_action('wp_head', 'index_rel_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'start_post_rel_link', 10);
  remove_action('wp_head', 'parent_post_rel_link', 10);
  remove_action('wp_head', 'adjacent_posts_rel_link', 10);
  remove_action('wp_head', 'wp_shortlink_wp_head', 10);

  // Remove emojis
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('admin_print_styles', 'print_emoji_styles');

  // Remove the REST API endpoint.
  remove_action('rest_api_init', 'wp_oembed_register_route');

  // Turn off oEmbed auto discovery.
  add_filter('embed_oembed_discover', '__return_false');

  // Don't filter oEmbed results.
  remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

  // Remove oEmbed discovery links.
  remove_action('wp_head', 'wp_oembed_add_discovery_links');

  // Remove oEmbed-specific JavaScript from the front-end and back-end.
  remove_action('wp_head', 'wp_oembed_add_host_js');

  // Remove welcome dashboard panel
  remove_action('welcome_panel', 'wp_welcome_panel');

  // Remove admin footer text
  add_filter('admin_footer_text', '__return_false');

  // Clean things
  add_filter('emoji_svg_url', '__return_false');
  add_filter('xmlrpc_enabled', '__return_false');
  add_filter('enable_post_by_email_configuration', '__return_false');
  add_filter('nav_menu_item_id', '__return_false'); // Remove IDs from menu
}
add_action('after_setup_theme', __NAMESPACE__ . '\\remove_extra_markup');


// Defer scripts
function add_defer_attribute($tag, $handle)
{
  if (str_starts_with($handle, 'tofino') || $handle === 'form-builder') {
    return str_replace('script src', 'script type="module" src', $tag);
  } else if (!is_admin() && $GLOBALS['pagenow'] != 'wp-login.php') {
    return str_replace(' src', ' defer src', $tag);
  } else {
    return $tag;
  }
}
add_filter('script_loader_tag', __NAMESPACE__ . '\\add_defer_attribute', 10, 2);


// Clean nav classes
function clean_nav_classes($classes, $item)
{
  $new_classes = ['menu-item'];

  if ($item->current) {
    $new_classes[] = 'menu-item-current';
  }

  if (in_array('menu-item-has-children', $classes)) {
    $new_classes[] = 'menu-item-has-children';
  }

  if ($item->menu_item_parent == 0) {
    $new_classes[] = 'menu-item-top-level';
  }

  if ($item->menu_item_parent == 0 && in_array('current-menu-parent', $classes)) {
    $new_classes[] = 'menu-item-current-parent';
  }

  $custom_classes = get_post_meta($item->ID, '_menu_item_classes', true);

  if (!empty(array_filter($custom_classes))) {
    $classes = array_merge($new_classes, $custom_classes);
  } else {
    $classes = $new_classes;
  }

  return $classes;
}
add_filter('nav_menu_css_class', __NAMESPACE__ . '\\clean_nav_classes', 10, 2);


// Remove dashicons
function dequeue_dashicon()
{
  if (current_user_can('update_core')) {
    return;
  }

  wp_deregister_style('dashicons');
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\dequeue_dashicon');


// Disable embeds
function disable_embed()
{
  wp_dequeue_script('wp-embed');
}
add_action('wp_footer', __NAMESPACE__ . '\\disable_embed');


// Add/remove body_class() classes
function remove_body_classes($classes)
{
  // Add post/page slug if not present
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Remove unnecessary classes
  $home_id_class  = 'page-id-' . get_option('page_on_front');
  $remove_classes = ['page-template-default', $home_id_class];
  $classes        = array_diff($classes, $remove_classes);
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\remove_body_classes');


// Remove 'text/css' and 'text/javascript' from enqueued stylesheets and scripts
function cleaner_script_style_tags()
{
  add_theme_support('html5', ['script', 'style']);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\cleaner_script_style_tags');


// Remove Help Tabs
function remove_help_tabs()
{
  $screen = get_current_screen();
  $screen->remove_help_tabs();
}
add_action('admin_head', __NAMESPACE__ . '\\remove_help_tabs');


// Disable 404 redirect matches
function no_redirect_on_404($redirect_url)
{
  if (is_404()) {
    return false;
  }

  return $redirect_url;
}
add_filter('redirect_canonical', __NAMESPACE__ . '\\no_redirect_on_404');


// Remove new v5.9 global styles
function remove_global_css()
{
  remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
  remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}
add_filter('init', __NAMESPACE__ . '\\remove_global_css');


/**
 * Wordpress: Filter admin columns and remove yoast seo columns
 */
function yoast_seo_remove_columns($columns)
{
  if (in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    /* remove the Yoast SEO columns */
    unset($columns['wpseo-score']);
    unset($columns['wpseo-title']);
    unset($columns['wpseo-metadesc']);
    unset($columns['wpseo-focuskw']);
    unset($columns['wpseo-score-readability']);
    unset($columns['wpseo-links']);
    unset($columns['wpseo-linked']);
  }

  return $columns;
}
add_filter('manage_edit-post_columns', __NAMESPACE__ . '\\yoast_seo_remove_columns');
add_filter('manage_edit-page_columns', __NAMESPACE__ . '\\yoast_seo_remove_columns');


// Remove Yoast from Admin Bar
function remove_yoast_admin_bar()
{
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wpseo-menu');
}
add_action('wp_before_admin_bar_render', __NAMESPACE__ . '\\remove_yoast_admin_bar');


// Login Screen: Don't inform user which piece of credential was incorrect
function failed_login()
{
  return __('The login information you have entered is incorrect. Please try again.', 'tofino');
}
add_filter('login_errors', __NAMESPACE__ . '\\failed_login');


// Disable RSS feeds
function disable_rss_feeds()
{
  wp_die(__('No feeds available!'));
}
add_action('do_feed', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_rdf', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_rss', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_rss2', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_atom', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_rss2_comments', __NAMESPACE__ . '\\disable_rss_feeds', 1);
add_action('do_feed_atom_comments', __NAMESPACE__ . '\\disable_rss_feeds', 1);
