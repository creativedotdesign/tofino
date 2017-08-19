<?php
namespace Tofino;

use \Tofino\Helpers as h;

ob_start(); // Redirect headers hack, Wordpress though eh?

/**
 * Menu item will allow us to load the page to display the table
 */
function add_menu_contact_form_data_table_page() {
  add_menu_page('Form Data', 'Form Data', 'manage_options', 'contact-form-data', false, 'dashicons-list-view');
  add_submenu_page('form-data', 'Contact Form', 'Contact Form', 'manage_options', 'contact-form-data', __NAMESPACE__ . '\\list_table_page', 'dashicons-email');
}

if (is_admin()) {
  add_action('admin_menu', __NAMESPACE__ . '\\add_menu_contact_form_data_table_page');
}

if (isset($_GET['action']) && $_GET['action'] == 'download_csv') {
  // Handle CSV Export
  add_action('admin_init', __NAMESPACE__ . '\\csv_export');
}

/**
 * Exports the data to CSV File
 */
function csv_export() {
  if (!is_super_admin()) {
    return;
  }

  $nonce = esc_attr($_REQUEST['_wpnonce']);

  if (!wp_verify_nonce($nonce, 'download_csv')) {
    die('Nope!');
  }

  $filename = 'contact-form-data-' . time() . '.csv';

  $header_row = array(
    0 => 'ID',
    1 => 'Date / Time',
    2 => 'Name',
    3 => 'Email',
    4 => 'Message',
  );

  $data_rows = [];

  $post_id  = h\get_id_by_slug('contact');

  // More complex but we get an ID and more control
  $dirty_data = h\get_complete_meta($post_id, 'contact_form');

  foreach ($dirty_data as $entry) {
    $entry    = (array)$entry;
    $response = maybe_unserialize($entry['meta_value']);

    $row = [];
    $row[0] = $entry['meta_id'];
    $row[1] = $response['name'];
    $row[2] = $response['message'];
    $row[3] = $response['email'];
    $row[4] = date('Y-m-d H:i:s', $response['date_time']);

    $data_rows[] = $row;
  }

  $fh = @fopen('php://output', 'w');
  fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Content-Description: File Transfer');
  header('Content-type: text/csv');
  header("Content-Disposition: attachment; filename={$filename}");
  header('Expires: 0');
  header('Pragma: public');

  // Push headers
  fputcsv($fh, $header_row);

  // Loop and push content
  foreach ($data_rows as $data_row) {
    fputcsv($fh, $data_row);
  }

  fclose($fh);
  die();
}

/**
 * Displat the Page and Data Table
 */
function list_table_page() {
  $contact_form_table = new ContactFormDataTable();
  $contact_form_table->prepare_items(); ?>
  <div class="wrap">
    <h2>Contact Form Data</h2><br>
    <a href="<?php echo admin_url('admin.php?page=contact-form-data'); ?>&amp;action=download_csv&amp;_wpnonce=<?php echo wp_create_nonce('download_csv'); ?>" class="button button-primary"><?php _e('Export to CSV', 'tofino');?></a>
    <form method="post">
      <?php $contact_form_table->display(); ?>
    </form>
  </div><?php
}

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ContactFormDataTable extends \WP_List_Table {

  private $post_slug = 'contact';

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
    $post_id  = h\get_id_by_slug($this->post_slug);

    $this->_column_headers = [$columns, $hidden, $sortable];
    $this->items           = $this->table_data($post_id);

    $this->process_bulk_action();
  }

  /**
   * Override the parent columns method. Defines the columns to use in your listing table
   *
   * @return Array
   */
  public function get_columns() {
    $columns = [
      //'id'        => 'ID',
      'cb'        => '<input type="checkbox" />',
      'name'      => __('Name', 'tofino'),
      'message'   => __('Message', 'tofino'),
      'email'     => __('Email', 'tofino'),
      'date_time' => __('Date / Time', 'tofino'),
    ];

    return $columns;
  }

  /**
   * Define which columns are hidden
   *
   * @return Array
   */
  public function get_hidden_columns() {
    return [];
  }

  /**
   * Define the sortable columns
   *
   * @return Array
   */
  public function get_sortable_columns() {
    return [
      'name'      => ['name', true],
      'email'     => ['email', true],
      //'id'        => ['id', true],
      'date_time' => ['date_time', true]
    ];
  }

  /**
   * Get the table data
   *
   * @return Array
   */
  private function table_data($post_id) {
    //$data = get_post_meta($post_id, 'contact_form'); // Keeping it clean

    // More complex but we get an ID and more control
    $dirty_data = h\get_complete_meta($post_id, 'contact_form');

    $data = [];
    foreach ($dirty_data as $entry) {
      $entry    = (array)$entry;
      $response = maybe_unserialize($entry['meta_value']);

      $data[] = [
        'id'        => $entry['meta_id'],
        'name'      => $response['name'],
        'message'   => $response['message'],
        'email'     => $response['email'],
        'date_time' => date('Y-m-d H:i:s', $response['date_time'])
      ];
    }

    if (isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
      usort($data, function ($a, $b) {
        $order_by = $_REQUEST['orderby'];
        $order    = $_REQUEST['order'];
        if ($order === 'asc') {
          return $a[$order_by] > $b[$order_by];
        } elseif ($order === 'desc') {
          return $a[$order_by] < $b[$order_by];
        }
      });
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
      //case 'id':
      case 'date_time':
      case 'name':
      case 'email':
      case 'message':
        return $item[$column_name];
      default:
        return print_r($item, true); // Show array for debug
    }
  }

  public function column_name($item) {
    $delete_nonce = wp_create_nonce('delete_item');

    $actions = [
      'delete' => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
    ];

    return $item['name'] . $this->row_actions($actions);
  }

  /**
  * Render the bulk edit checkbox
  *
  * @param array $item
  *
  * @return string
  */
  public function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="bulk-delete[]" value="%s" />',
      $item['id']
    );
  }

  /**
  * Returns an associative array containing the bulk action
  *
  * @return array
  */
  public function get_bulk_actions() {
    $actions = [
      'bulk-delete' => 'Delete'
    ];

    return $actions;
  }

 /**
  * Function for bulk deleting items
  */
  public function process_bulk_action() {
    // Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if (!wp_verify_nonce($nonce, 'delete_item')) {
        die('Nope!');
      } else {
        $this->delete_item(absint($_GET['id']));
        
        wp_redirect(esc_url(add_query_arg()));
        exit();
      }
    }

    // If the delete bulk action is triggered
    if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')) {
      $delete_ids = esc_sql($_POST['bulk-delete']);

      // loop over the array of record IDs and delete them
      foreach ($delete_ids as $id) {
        $this->delete_item($id);
      }

      wp_redirect(esc_url(add_query_arg()));
      exit;
    }
  }

  /**
  * Function for deleting the item
  */
  public static function delete_item($id) {
    global $wpdb;

    $wpdb->delete(
      "{$wpdb->prefix}postmeta",
      ['meta_id' => $id],
      ['%d']
    );
  }
}
