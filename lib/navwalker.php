<?php

namespace Tofino\Nav;

class NavWalker extends \Walker_Nav_Menu {

  /**
  * @see Walker::start_lvl()
  * @since 3.0.0
  *
  * @param string $output Passed by reference. Used to append additional content.
  * @param int $depth Depth of page. Used for padding.
  */
  public function start_lvl(&$output, $depth = 0, $args = array()) {
    parent::start_lvl($output, $depth, $args);
    $pos = strrpos($output, '">', -1);
    $output = substr_replace($output, ' dropdown-menu" role="menu">', $pos);
  }

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
   public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
     if (!$element) {
       return;
     }

    $id_field = $this->db_fields['id'];
    $this->has_children = !empty($children_elements[$element->$id_field]);
    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

/**
 * Filters
 * =======
 *
 *  It is more robust to rely on Wordpress' filter/hook framework than
 *  to subclass Walker_Nav_Menu class, which duplicates a lot of code.
 *
 *   This filter is used to customize the <li> classes.
 */
add_filter('nav_menu_css_class', function($classes, $item, $args) {
/*
 *  Append the dropdown class to the output class array.
 */
 $classes[] = 'nav-item';

  if ($args->walker->has_children) {
    $classes[] = 'dropdown';
  }

  if (in_array('current-menu-item', $classes)){
    $classes[] = 'active';
  }

  return $classes;
},10, 3); # $priority, $accepted_args
/*
 *   This filter is used to customize the <li> attributes.
 */
add_filter('nav_menu_link_attributes', function($atts, $item, $args) {

//print_r($atts);

  $atts['class'] = 'nav-link';

  if ($args->walker->has_children) {
  /*
   *  Append the data-toggle and dropdown attributes to the
   *  anchor element inside the list item dropdown.
   */
    $atts['data-toggle']  = 'dropdown';
    $atts['class'] = 'dropdown-toggle nav-link';
    $atts['aria-haspopup']  = 'true';
    0 === $args->depth and $args->link_after = ' <i class="caret"></i>';
  }

  return $atts;
},10, 3); # $priority, $accepted_args
/*
 *   This filter is used to customize the <li> final output.
 */
add_filter('walker_nav_menu_start_el',
  function($item_output, $item, $depth, $args) {

    $indent = str_repeat( "\t", $depth );

    $args->link_before = '';
    $args->link_after = '';
    /*
     *  Reset the before and after link strings previously used
     *  to append the glyphicon and caret to avoid side effects.
     */
    return $indent . $item_output;
}, 10, 4); # $priority, $accepted_args
