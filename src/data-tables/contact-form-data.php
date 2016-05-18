<?php
namespace Tofino;

/**
 * Menu item will allow us to load the page to display the table
 */
function add_menu_contact_form_data_table_page() {
  add_menu_page('Form Data', 'Form Data', 'manage_options', 'form-data', false, 'dashicons-list-view');
  add_submenu_page('form-data', 'Contact Forms', 'Contact Forms', 'manage_options', 'contact-form-data', __NAMESPACE__ . '\\list_table_page', 'dashicons-email');
}
if (is_admin()) {
  add_action('admin_menu', __NAMESPACE__ . '\\add_menu_contact_form_data_table_page');
}

function list_table_page() {
  $contact_form_table = new ContactFormDataTable();
  $contact_form_table->prepare_items(); ?>
  <div class="wrap">
    <h2>Contact Form Data</h2>
    <?php $contact_form_table->display(); ?>
  </div><?php
}

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ContactFormDataTable extends \WP_List_Table {

  /** Class constructor */
  public function __construct() {
    parent::__construct([
      'singular' => __('Contact Form', 'tofino'), // Singular
      'plural'   => __('Contact Forms', 'tofino'), // Plural
      'ajax'     => false // Ajax?
    ]);
  }

  /**
   * Prepare the items for the table to process
   *
   * @return Void
   */
  public function prepare_items() {
    $columns  = $this->get_columns();
    $hidden   = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();
    $post_id  = \Tofino\Helpers\get_id_by_slug('contact');

    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->items           = $this->table_data($post_id);
  }

  /**
   * Override the parent columns method. Defines the columns to use in your listing table
   *
   * @return Array
   */
  public function get_columns() {
    $columns = [
      'id'        => 'ID',
      'date_time' => 'Date / Time',
      'name'      => 'Name',
      'email'     => 'Email',
      'message'   => 'Message'
    ];

    return $columns;
  }

  /**
   * Define which columns are hidden
   *
   * @return Array
   */
  public function get_hidden_columns() {
    return array();
  }

  /**
   * Define the sortable columns
   *
   * @return Array
   */
  public function get_sortable_columns() {
    return array('name' => array('name', false));
  }

  /**
   * Get the table data
   *
   * @return Array
   */
  private function table_data($post_id) {
    //$data = get_post_meta($post_id, 'contact_form'); // Keeping is clean

    // More complex but we get an ID and more control
    $dirty_data = \Tofino\Helpers\get_complete_meta($post_id, 'contact_form');

    $data = [];
    foreach ($dirty_data as $entry) {
      $entry    = (array)$entry;
      $response = maybe_unserialize($entry['meta_value']);

      $data[] = [
        'id'        => $entry['meta_id'],
        'date_time' => date('Y-m-d H:i:s', $response['date_time']),
        'name'      => $response['name'],
        'email'     => $response['email'],
        'message'   => $response['message']
      ];
    }

    return $data;
  }

  // Used to display the value of the id column
  public function column_id($item) {
    return $item['id'];
  }

  /**
   * Define what data to show on each column of the table
   *
   * @param  Array $item        Data
   * @param  String $column_name - Current column name
   *
   * @return Mixed
   */
  public function column_default($item, $column_name) {
    switch ($column_name) {
      case 'id':
      case 'date_time':
      case 'name':
      case 'email':
      case 'message':
        return $item[$column_name];
      default:
        return print_r($item, true); // Show array for debug
    }
  }
}
