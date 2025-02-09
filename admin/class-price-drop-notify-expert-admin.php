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
class Price_Drop_Notify_Expert_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Drop_Notify_Expert_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Drop_Notify_Expert_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if (isset($_GET['page']) && $_GET['page'] === 'pricedropnotifexpert') {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Drop_Notify_Expert_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Drop_Notify_Expert_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script('wp-element'); // Includes React and ReactDOM
		wp_enqueue_script('wp-components'); // Optional: If you want to use WordPress components
		wp_enqueue_script('wp-i18n'); // Optional: For internationalization

		if (isset($_GET['page']) && $_GET['page'] === 'pricedropnotifexpert') {
			wp_enqueue_script('pcne-settings-page-menu-options',
				plugin_dir_url(__FILE__) . '/build/index.js',
				array('wp-element', 'wp-api-fetch'),
				'1.00',
				true);
			wp_localize_script('pcne-settings-page-menu-options', 'wpApiSettings', [
				'root' => esc_url(rest_url()),
				'nonce' => wp_create_nonce('wp_rest'),
			]);

		}
	}



	/**
	 * Register the admin menu and settings page
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Price Drop Notification Expert',
			'Price Drop Notification Expert',
			'manage_options',
			'pricedropnotifexpert',
			array($this, 'pcne_display_main_page_callback'),
			'dashicons-bell',
			25       // Position
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Notification Settings',
			'Contacts',
			'manage_options',
			'pcne-contacts',
			array($this, 'pcne_contacts_page_callback')
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Contacts',
			'Settings',
			'manage_options',
			'pricedropnotifexpert-settings',
			array($this, 'pcne_settings_page_callback')
		);

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function pcne_display_main_page_callback() {
		?>
		<div id="pricedropnotifexpert-root"></div>
		<?php
	}

	/**
	 * pcne_settings_page_callback
	 * @return void
	 */
	public function pcne_contacts_page_callback() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?> | Contacts</h1>
			<?php
			$contacts_table = new PCNE_Contacts_Table();
			$contacts_table->prepare_items();
			?>
			<div class="wrap">
				<form method="post">
					<?php $contacts_table->search_box('Search', 'search_id'); ?>
					<?php $contacts_table->display(); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the settings page for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function pcne_settings_page_callback() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?> Setting</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('pcne_settings_group');
				do_settings_sections('pricedropnotifexpert-settings');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {
		// Register settings group
		register_setting('pcne_settings_group', 'pricedropnotifexpert-settings');

		// Add settings section
		add_settings_section(
			'pcne_main_section',
			'Main Settings',
			array($this, 'settings_section_callback'),
			'pricedropnotifexpert-settings'
		);
		add_settings_field(
			'pcne_field_1',
			'Enable Notifications',
			array($this, 'settings_field_callback'),
			'pricedropnotifexpert-settings',
			'pcne_main_section'
		);
		add_settings_field(
			'pcne_field_2',
			'Firebase API',
			array($this, 'settings_field_callback2'),
			'pricedropnotifexpert-settings',
			'pcne_main_section'
		);
	}

	/**
	 * Section callback
	 *
	 * @since    1.0.0
	 */
	public function settings_section_callback() {
		echo '<p>Configure the notification settings below:</p>';
	}

	/**
	 * AX Notification Field
	 * @return void
	 */
	public function settings_field_callback() {
		$options = get_option('price_drop_notification_expert');
		$checked = isset($options['enable']) ? checked($options['enable'], 1, false) : '';
		echo '<input type="checkbox" name="price_drop_notification_expert[enable]" value="1" ' . $checked . ' />';
	}

	/**
	 * AX Notification Field 
	 * @return void
	 */
	public function settings_field_callback2() {
		$options = get_option('price_drop_notification_expert');
		echo "<input type='text' name='price_drop_notification_expert[fcm_server_key]' value='" . esc_attr($options['fcm_server_key']) . "' style='width: 400px;' />";
	}

	function track_price_changes($product_id) {
		$product = wc_get_product($product_id);
		if (!$product)
			return;
		//elog($product);
		if ($product->is_type('variable')) {
			$variations = $product->get_children();
			foreach ($variations as $variation_id) {
				$variation = wc_get_product($variation_id);
				$new_price = $variation->get_price();
				$this->save_price_history($product_id, $variation_id, $new_price);
			}
		} elseif ($product->is_type('simple') || $product->is_type('subscription')) {
			$new_price = $product->get_price();
			$this->save_price_history($product_id, null, $new_price);
		}
	}

	function save_price_history($product_id, $variation_id, $new_price) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'price_drop_notify_price_history';

		// Fetch existing price history
		$query = $wpdb->prepare(
			"SELECT price_history FROM $table_name 
			 WHERE product_id = %d AND variation_id " . ($variation_id !== null ? "= %d" : "IS NULL") . " 
			 LIMIT 1",
			$product_id, ($variation_id !== null ? $variation_id : null)
		);

		$existing_history = $wpdb->get_var($query);
		$price_history = $existing_history ? json_decode($existing_history, true) : [];

		// Check last recorded price
		$last_entry = !empty($price_history) ? end($price_history) : null;
		$last_price = $last_entry ? $last_entry['price'] : null;

		// Log for debugging
		elog('last_entry : ' . json_encode($last_entry));
		elog('last_price : ' . $last_price);
		elog('new_price : ' . $new_price);

		// Only update if price has changed OR if it's a new product
		if ($last_price !== $new_price || empty($existing_history)) {
			$price_history[] = [
				'price' => $new_price,
				'date' => current_time('mysql'),
			];

			if (empty($existing_history)) {
				// Insert a new record if the product is new
				$wpdb->insert(
					$table_name,
					[
						'product_id' => $product_id,
						'variation_id' => $variation_id,
						'price_history' => json_encode($price_history),
						'change_date' => current_time('mysql'),
					],
					['%d', '%d', '%s', '%s']
				);
			} else {
				// Update existing record
				$wpdb->update(
					$table_name,
					[
						'price_history' => json_encode($price_history),
						'change_date' => current_time('mysql'),
					],
					[
						'product_id' => $product_id,
						'variation_id' => $variation_id,
					],
					['%s', '%s'],
					['%d', '%d']
				);
			}
		}
	}


}
