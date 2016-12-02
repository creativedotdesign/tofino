<?php
namespace Tofino\Nav;

class NavWalker extends \Walker_Nav_Menu {
  private $cpt; // Boolean, is current post a custom post type
  private $archive; // Stores the archive page for current URL

  public function __construct() {
    add_filter('nav_menu_css_class', [$this, 'cssClasses'], 10, 2);
    add_filter('nav_menu_link_attributes', [$this, 'filter_menu_link_atts'], 10, 3);
    add_filter('walker_nav_menu_start_el', [$this, 'filter_menu_item_output'], 10, 4);
    add_filter('nav_menu_item_id', '__return_null');
    $cpt           = get_post_type();
    $this->cpt     = in_array($cpt, get_post_types(['_builtin' => false]));
    $this->archive = get_post_type_archive_link($cpt);
  }

  /**
  * @see Walker::start_lvl()
  * @since 3.0.0
  *
  * @param string $output Passed by reference. Used to append additional content.
  * @param int $depth Depth of page. Used for padding.
  */
  // @codingStandardsIgnoreStart
  public function start_lvl(&$output, $depth = 0, $args = []) {
    parent::start_lvl($output, $depth, $args);
    $pos    = strrpos($output, '">', -1);
    $output = substr_replace($output, ' dropdown-menu" role="menu">', $pos);
  }
  // @codingStandardsIgnoreEnd

  /**
   * Traverse elements to create list from elements.
   *
   * Display one element if the element doesn't have any children otherwise,
   * display the element and its children. Will only traverse up to the max
   * depth and no ignore elements under that depth.
   *
   * This method shouldn't be called directly, use the walk() method instead.
   *
   * @see Walker::start_el()
   * @since 2.5.0
   *
   * @param object $element Data object
   * @param array $children_elements List of elements to continue traversing.
   * @param int $max_depth Max depth to traverse.
   * @param int $depth Depth of current element.
   * @param array $args
   * @param string $output Passed by reference. Used to append additional content.
   * @return null Null on failure with no changes to parameters.
   */

  // @codingStandardsIgnoreStart
  public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
    if (!$element) {
      return;
    }

    $id_field = $this->db_fields['id'];
    $this->has_children = !empty($children_elements[$element->$id_field]);

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
  // @codingStandardsIgnoreEnd

  public function cssClasses($classes, $item) {
    if ($this->has_children) {
      $classes[] = 'dropdown';
    }

    if ($item->menu_item_parent != 0) {
      $classes[] = 'dropdown-item';
    }

    if ($item->menu_item_parent == 0) {
      $classes[] = 'nav-item';
    }

    if (strcasecmp($item->attr_title, 'header') == 0) {
      $classes[] = 'dropdown-header';
    }

    $slug = sanitize_title($item->title);

    if ($this->cpt) {
      $classes = str_replace('current_page_parent', '', $classes);
      if (strcasecmp($this->archive, $item->url) === 0) {
        $classes[] = 'active';
      }
    }

    $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', '', $classes);
    $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);
    $classes[] = 'menu-' . $slug;
    $classes = array_unique($classes);

    return array_filter($classes, function ($element) {
      $element = trim($element);
      return !empty($element);
    });
  }

  /*
   * This filter is used to customize the <li> attributes.
   */
   // @codingStandardsIgnoreStart
  public function filter_menu_link_atts($atts, $item, $args) {

    if (is_object($args->walker)) { // Filter if custom walker
      $classes   = [];
      $classes[] = 'nav-link';

      if ($args->walker->has_children) {
        $atts['data-toggle']   = 'dropdown';
        $classes[]             = 'dropdown-toggle';
        $atts['aria-haspopup'] = 'true';
      }

      if (strcasecmp($item->attr_title, 'disabled') == 0) {
        $classes[] = 'disabled';
      }

      if ($item->current == 1) {
        $classes[] = 'active';
      }

      $atts['class'] = implode(' ', $classes);
    }

    return $atts;
  }
  // @codingStandardsIgnoreEnd

  /*
   * This filter is used to customize the <li> final output.
   */
   // @codingStandardsIgnoreStart
  public function filter_menu_item_output($item_output, $item, $depth, $args) {
    $indent = str_repeat("\t", $depth);

    if (strcasecmp($item->attr_title, 'divider') == 0 && $depth === 1) {
      $item_output .= $indent . '<li role="presentation" class="dropdown-divider">';
    } else if ((strcasecmp($item->attr_title, 'header') == 0 && $depth === 1) && $depth === 1) {
      $item_output = $indent . '<h6>' . esc_attr($item->title) . '</h6>';
    }

    $args->link_before = '';
    $args->link_after = '';

    return $indent . $item_output;
  }
  // @codingStandardsIgnoreEnd
}
