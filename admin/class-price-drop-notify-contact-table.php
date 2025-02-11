<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/false
 * @since      1.0.0
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/admin
 * @author     False <basantasubedi301@gmail.com>
 */
if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PCNE_Contacts_Table extends WP_List_Table {

	// Constructor
	public function __construct() {
		parent::__construct([
			'singular' => 'contact', // Singular label
			'plural' => 'contacts', // Plural label
			'ajax' => false, // No AJAX support
		]);
	}

	// Fetch data from the database
	public function get_contacts($per_page = 10, $page_number = 1, $search = '') {
		global $wpdb;

		$table_name = $wpdb->prefix . 'price_drop_notify_expert_contacts';

		$sql = "SELECT * FROM $table_name";

		// Add search filter
		if (!empty($search)) {
			$sql .= $wpdb->prepare(" WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		// Add pagination
		$sql .= " LIMIT $per_page";
		$sql .= " OFFSET " . ($page_number - 1) * $per_page;

		return $wpdb->get_results($sql, ARRAY_A);
	}

	// Get total number of records
	public function record_count($search = '') {
		global $wpdb;

		$table_name = $wpdb->prefix . 'price_drop_notify_expert_contacts';

		$sql = "SELECT COUNT(*) FROM $table_name";

		// Add search filter
		if (!empty($search)) {
			$sql .= $wpdb->prepare(" WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		return $wpdb->get_var($sql);
	}

	// Render checkbox column
	protected function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="contact_ids[]" value="%s" />', $item['id']
		);
	}

	// Render default columns
	protected function column_default($item, $column_name) {
		switch ($column_name) {
			case 'product_id':
				// Fetch the product name using the product ID
				$product_id = $item['product_id'];
				return $product_id ? get_the_title($product_id) : __('No Product', 'textdomain');
			case 'variable_data':
				// Fetch the product name using the product ID
				$item_Data = !empty($item['variable_data']) ? json_decode($item['variable_data']) : '';
				if (is_object($item_Data)) {
					$attributes = [];
					foreach ($item_Data as $key => $value) {
						$attributes[] = str_replace('pa_', '', $key) . ': ' . $value->label;
					}
					return implode(' | ', $attributes);
				}

			default:
				return isset($item[$column_name]) ? $item[$column_name] : '';
		}
	}

	// Define sortable columns
	public function get_columns() {
		return [
			'cb' => '<input type="checkbox" />',  // Checkbox for bulk actions
			'name' => __('Name', 'textdomain'),
			'email' => __('Email', 'textdomain'),
			'phone' => __('Phone', 'textdomain'),
			'variable_data' => __('Attributes', 'textdomain'),
			'product_id' => __('Product', 'textdomain'),
		];
	}

	public function get_hidden_columns() {
		return []; // Empty array for now, or you can specify which columns should be hidden
	}

	public function get_sortable_columns() {
		return [
			'id' => ['id', false],
			'name' => ['name', false],
		];
	}

	public function get_bulk_actions() {
		return [
			'delete' => __('Delete', 'price-drop-notify'),
		];
	}

	public function process_bulk_action() {
		if ('delete' === $this->current_action()) {
			if (isset($_POST['contact_ids']) && is_array($_POST['contact_ids'])) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'price_drop_notify_expert_contacts';
				$ids = implode(',', array_map('intval', $_POST['contact_ids']));
				$wpdb->query("DELETE FROM $table_name WHERE id IN ($ids)");
			}
		}
	}

	// Prepare items for display
	public function prepare_items() {
		// Ensure the column headers are set
		$this->_column_headers = [
			$this->get_columns(),  // Columns list
			$this->get_hidden_columns(),  // Hidden columns
			$this->get_sortable_columns(),  // Sortable columns
		];

		// Process bulk actions
		$this->process_bulk_action();

		// Pagination variables
		$per_page = $this->get_items_per_page('contacts_per_page', 10);
		$current_page = $this->get_pagenum();
		$search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

		// Fetch data
		$this->items = $this->get_contacts($per_page, $current_page, $search);

		// Set pagination arguments
		$total_items = $this->record_count($search);
		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => $per_page,
		]);
	}

	// Add search box
	public function search_box($text, $input_id) {
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
		</p>
		<?php
	}
}