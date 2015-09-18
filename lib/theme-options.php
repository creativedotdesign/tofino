<?php

namespace Tofino\Options;

//Remove layout option
add_filter( 'ot_show_new_layout', '__return_false' );

//Remove default text
add_filter( 'ot_header_version_text', '__return_false' );

//Remove logo
add_filter( 'ot_header_logo_link', '__return_false' );

//Set 'ot_theme_mode' filter to true.
add_filter( 'ot_theme_mode', '__return_true' );

//Remove default social media types
add_filter( 'ot_type_social_links_load_defaults', '__return_false' );

/**
 * Build the custom settings & update OptionTree.
 *
 * @return    void
 * @since     2.3.0
 */
function custom_theme_options() {

  /* OptionTree is not loaded yet */
  if ( ! function_exists( 'ot_settings_id' ) )
    return false;

  /**
   * Get a copy of the saved settings array.
   */
  $saved_settings = get_option( ot_settings_id(), array() );

  /**
   * Custom settings array that will eventually be
   * passes to the OptionTree Settings API Class.
   */

  $custom_settings = array(
    'contextual_help' => array(
      'content'       => array(),
    ),
    'sections' => array(
      array(
        'id'    => 'general_settings',
        'title' => __( 'General', 'tofino' )
      ),
      array(
        'id'    => 'menu_settings',
        'title' => __( 'Menu', 'tofino' )
      ),
    ),
    'settings' => array(
      array(
        'id'      => 'logo_admin_id',
        'label'   => __( 'Admin Login Logo', 'tofino' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'upload',
        'section' => 'general_settings',
        'class'   => 'ot-upload-attachment-id',
      ),
   	  array(
        'id'      => 'google_analytics',
        'label'   => __( 'Google Analytics UA Code', 'tofino' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'text',
        'section' => 'general_settings'
      ),
      array(
        'id'      => 'social_links',
        'label'   => __( 'Social Links', 'tofino' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'social-links',
        'section' => 'general_settings',
      ),
      array(
       'id'          => 'telephone_number',
       'label'       => __( 'Telephone Number', 'tofino' ),
       'desc'        => '',
       'std'         => '',
       'type'        => 'text',
       'section'     => 'general_settings',
     ),
     array(
       'id'          => 'email_address',
       'label'       => __( 'Email Address', 'tofino' ),
       'desc'        => '',
       'std'         => '',
       'type'        => 'text',
       'section'     => 'general_settings',
      ),
      array(
        'id'      => 'footer_text',
        'label'   => __( 'Footer Text', 'tofino' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'textarea-simple',
        'section' => 'general_settings',
        'rows'    => '3',
      ),
      array(
        'id'      => 'menu_sticky_checkbox',
        'label'   => __( 'Sticky Menu', 'tofino' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'menu_settings',
        'choices' => array(
          array(
            'value' => false,
            'label' => __( 'Disable Sticky Menu', 'option-tree-theme' ),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'      => 'menu_position_select',
        'label'   => __( 'Menu Position', 'option-tree-theme' ),
        'desc'    => '',
        'std'     => '',
        'type'    => 'select',
        'section' => 'menu_settings',
        'choices' => array(
          array(
            'value' => 'left',
            'label' => __( 'Left', 'option-tree-theme' ),
            'src'   => ''
          ),
          array(
            'value'  => 'Centre',
            'label'  => __( 'Centre', 'option-tree-theme' ),
            'src'    => ''
          ),
          array(
            'value' => 'Right',
            'label' => __( 'Right', 'option-tree-theme' ),
            'src'   => ''
          )
        )
      ),
    )
  );

  /* allow settings to be filtered before saving */
  $custom_settings = apply_filters( ot_settings_id() . '_args', $custom_settings );

  /* settings are not the same update the DB */
  if ( $saved_settings !== $custom_settings ) {
    update_option( ot_settings_id(), $custom_settings );
  }

  /* Lets OptionTree know the UI Builder is being overridden */
  global $ot_has_custom_theme_options;
  $ot_has_custom_theme_options = true;

}

/**
 * Initialize the custom Theme Options.
 */
add_action( 'admin_init',  __NAMESPACE__ . '\\custom_theme_options' );
