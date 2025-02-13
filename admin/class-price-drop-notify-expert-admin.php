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
		if (isset($_GET['page']) && in_array($_GET['page'], ['pricedropnotifexpert', 'pricedropnotifexpert-settings', 'pricedropnotifexpert-form'])) {
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

		//Jquery
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-accordion');

		//React and Dom
		wp_enqueue_script('wp-element'); // Includes React and ReactDOM
		wp_enqueue_script('wp-components'); // Optional: If you want to use WordPress components
		wp_enqueue_script('wp-i18n'); // Optional: For internationalization
		wp_enqueue_script('pcne-admin',
			plugin_dir_url(__FILE__) . 'js/admin.js',
			array('jquery'),
			'1.00',
			true);

		if (isset($_GET['page']) && in_array($_GET['page'], ['pricedropnotifexpert'])) {

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
			'Price Drop Notify Expert',
			'Price Drop Notify Expert',
			'manage_options',
			'pricedropnotifexpert',
			array($this, 'pcne_display_main_page_callback'),
			'dashicons-bell',
			25       // Position
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Contacts',
			'Contacts',
			'manage_options',
			'pcne-contacts',
			array($this, 'pcne_contacts_page_callback'),
			10
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Settings',
			'Settings',
			'manage_options',
			'pricedropnotifexpert-settings',
			array($this, 'pcne_settings_page_callback'),
			15
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
			<?php
			$contacts_table = new PCNE_Contacts_Table();
			$contacts_table->prepare_items();
			?>
			<div class="wrap">
				<div class="form-wrapper">
					<div class="form-container">
						<div class="header-section">
							<h2>Price Drop Notifiy Expert | <?php echo esc_html(get_admin_page_title()); ?></h2>
						</div>
						<form method="post">
							<?php $contacts_table->search_box('Search', 'search_id'); ?>
							<?php $contacts_table->display(); ?>
						</form>
					</div>
				</div>
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
			<div class="form-wrapper">
				<div class="header-section">
					<h2>Price Drop Notifiy Expert | <?php echo esc_html(get_admin_page_title()); ?></h2>
				</div>
				<div class="form-container">
					<div style="padding:10px;">
						<form method="post" action="options.php">
							<section>
								<?php
								settings_fields('pcne_settings_group');
								do_settings_sections('pricedropnotifexpert-settings');
								?>
							</section>
							<section>
								<?php
								submit_button();
								?>
							</section>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function register_settings() {
		// Register settings group
		register_setting('pcne_settings_group', 'price_drop_notification_expert_settings');

		// Add settings section
		add_settings_section(
			'pcne_main_section',
			'General',
			array($this, 'settings_section_callback'),
			'pricedropnotifexpert-settings'
		);
		add_settings_section(
			'pcne_firebase_configuration',
			'Firebase',
			array($this, 'firebase_settings_section_callback'),
			'pricedropnotifexpert-settings'
		);
		add_settings_field(
			'firebase_api_key',
			'API KEY',
			array($this, 'settings_field_callback2'),
			'pricedropnotifexpert-settings',
			'pcne_firebase_configuration'
		);
		add_settings_field(
			'firebase_projectId',
			'Project ID',
			array($this, 'settings_field_callback3'),
			'pricedropnotifexpert-settings',
			'pcne_firebase_configuration'
		);
		add_settings_field(
			'firebase_messaging_sender_id',
			'Messaging Sender ID',
			array($this, 'settings_field_callback4'),
			'pricedropnotifexpert-settings',
			'pcne_firebase_configuration'
		);
		add_settings_field(
			'firebase_app_id',
			'APP ID',
			array($this, 'settings_field_callback5'),
			'pricedropnotifexpert-settings',
			'pcne_firebase_configuration'
		);
		add_settings_field(
			'vapid_Key',
			'Vapid Key',
			array($this, 'settings_field_callback6'),
			'pricedropnotifexpert-settings',
			'pcne_firebase_configuration'
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
	 * Section callback
	 *
	 * @since    1.0.0
	 */
	public function firebase_settings_section_callback() {
		echo '<p>To setup push notification, please go and configure project at <a href="//console.firebase.google.com">Firebase admin console</a><p>';
	}

	/**
	 * AX Notification Field 
	 * @return void
	 */
	public function settings_field_callback2() {
		$options = get_option('price_drop_notification_expert_settings');
		?>
		<input type='text' name='price_drop_notification_expert_settings[firebase][apiKey]'
			value="<?php echo isset($options['firebase']['apiKey']) ? esc_attr($options['firebase']['apiKey']) : ''; ?>" />
		<?php
	}

	/**
	 * AX Notification Field 
	 * @return void
	 */
	public function settings_field_callback3() {
		$options = get_option('price_drop_notification_expert_settings');
		?>
		<input type='text' name='price_drop_notification_expert_settings[firebase][projectId]'
			value="<?php echo isset($options['firebase']['projectId']) ? esc_attr($options['firebase']['projectId']) : ''; ?>" />
		<?php
	}
	/**
	 * AX Notification Field 
	 * @return void
	 */
	public function settings_field_callback4() {
		$options = get_option('price_drop_notification_expert_settings');
		?>
		<input type='text' name='price_drop_notification_expert_settings[firebase][messagingSenderId]'
			value="<?php echo isset($options['firebase']['messagingSenderId']) ? esc_attr($options['firebase']['messagingSenderId']) : ''; ?>" />
		<?php
	}
	/**
	 * AX Notification Field 
	 * @return void
	 */
	public function settings_field_callback5() {
		$options = get_option('price_drop_notification_expert_settings');
		?>
		<input type='text' name='price_drop_notification_expert_settings[firebase][appId]'
			value="<?php echo isset($options['firebase']['appId']) ? esc_attr($options['firebase']['appId']) : ''; ?>" />
		<?php
	}
	public function settings_field_callback6() {
		$options = get_option('price_drop_notification_expert_settings');
		?>
		<input type='text' name='price_drop_notification_expert_settings[firebase][vapidKey]'
			value="<?php echo isset($options['firebase']['vapidKey']) ? esc_attr($options['firebase']['vapidKey']) : ''; ?>" />
		<?php
	}
	public function track_price_changes($product_id) {
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

	public function save_price_history($product_id, $variation_id, $new_price) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'price_drop_notify_price_history';

		// Fetch existing price history
		$query = $wpdb->prepare(
			"SELECT * FROM $table_name 
			 WHERE product_id = %d AND variation_id " . ($variation_id !== null ? "= %d" : "IS NULL") . " 
			 LIMIT 1",
			$product_id, ($variation_id !== null ? $variation_id : null)
		);

		$existing_history = $wpdb->get_row($query);
		//error_log('$existing_history : ' . print_r($existing_history, true));
		$price_history = $existing_history ? json_decode($existing_history->price_history, true) : [];
		//error_log('$price_history' . print_r($price_history, true));
		// Check last recorded price
		$last_entry = !empty($price_history) ? end($price_history) : null;
		$last_price = $last_entry ? $last_entry['price'] : null;
		$latest_price_history = [];
		//error_log('$existing_history->product_id : ' . print_r($existing_history->product_id, true));

		// Check if product_id is the same but variation_id is different
		if ($last_entry && $existing_history->product_id === $product_id && $existing_history->variation_id !== $variation_id) {
			$latest_price_history[] = [
				'product_id' => $product_id,
				'variation_id' => $variation_id,
				'price' => $new_price,
				'date' => current_time('mysql'),
			];
		} else if ($last_price !== $new_price || empty($existing_history)) {

			$latest_price_history = [
				'last_price' => $last_price,
				'price' => $new_price,
				'date' => current_time('mysql'),
			];
		}
		$price_history[] = $latest_price_history;
		$updated_price_history = json_encode($price_history);
		if (empty($existing_history)) {
			// Insert a new record if the product is new
			$wpdb->insert(
				$table_name,
				[
					'product_id' => $product_id,
					'variation_id' => $variation_id,
					'price_history' => $updated_price_history,
					'change_date' => current_time('mysql'),
				],
				['%d', '%d', '%s', '%s']
			);
		} else {
			// Update existing record
			$wpdb->update(
				$table_name,
				[
					'price_history' => $updated_price_history,
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
	//End of class
}
