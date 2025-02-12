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
			'Form',
			'Form',
			'manage_options',
			'pricedropnotifexpert-form',
			array($this, 'pcne_form_page_callback')
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Contacts',
			'Contacts',
			'manage_options',
			'pcne-contacts',
			array($this, 'pcne_contacts_page_callback')
		);
		add_submenu_page(
			'pricedropnotifexpert',
			'Settings',
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

	function pcne_form_page_callback() {
		$formfields = array(
			array(
				'field_key' => 'name',
				'label' => 'Name',
				'required' => true,
				'show_label' => true,
				'order' => 1,
			),
			array(
				'field_key' => 'phone',
				'label' => 'Phone',
				'required' => true,
				'show_label' => true,
				'order' => 2,
			),
			array(
				'field_key' => 'email',
				'label' => 'Email',
				'required' => true,
				'show_label' => true,
				'order' => 3,
			),
			array(
				'field_key' => 'additional_info',
				'label' => 'Show Additional Info for Variable Product',
				'required' => false,  // Set to true since it's listed as a required field.
				'show_label' => true,
				'order' => 4,
			),
		);
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
								// Correct group name
								settings_fields('pcne_form_settings_group');
								do_settings_sections('pricedropnotifexpert-form');
								?>
							</section>

							<section>
								<h2>Field Configuration</h2>
								<h4>
									<?php _e('Form Configuration'); ?>
								</h4>
								<hr>
								<div id="sortable-fields">
									<?php
									$options = get_option('price_drop_notification_expert');
									if (isset($options['form']['fields'])) {
										$formfields = $options['form']['fields'];
									}
									foreach ($formfields as $field): ?>
										<div class="field-item" data-key="<?php echo htmlspecialchars($field['field_key']); ?>">

											<div class="accordion-container">
												<div class="field-header">
													<h3><?php echo htmlspecialchars($field['label']); ?></h3>
													<span style="cursor:pointer;">
														<span class="dashicons dashicons-insert"></span>
													</span>
												</div>
												<div class="accordion-content">
													<label>Field Key:</label>
													<input type="text"
														name="price_drop_notification_expert[form][fields][<?php echo htmlspecialchars($field['field_key']); ?>][field_key]"
														value="<?php echo isset($options['form']['fields'][$field['field_key']]['field_key']) ? $options['form']['fields'][$field['field_key']]['field_key'] : htmlspecialchars($field['field_key']); ?>"
														readonly />

													<label>Label:</label>
													<input type="text"
														name="price_drop_notification_expert[form][fields][<?php echo htmlspecialchars($field['field_key']); ?>][label]"
														value="<?php echo htmlspecialchars($field['label']); ?>" />

													<label>Required:</label>
													<input type="checkbox"
														name="price_drop_notification_expert[form][fields][<?php echo htmlspecialchars($field['field_key']); ?>][required]"
														value="1" <?php echo isset($field['required']) ? 'checked' : ''; ?> />

													<label>Show Label:</label>
													<input type="checkbox"
														name="price_drop_notification_expert[form][fields][<?php echo htmlspecialchars($field['field_key']); ?>][show_label]"
														value="1" <?php echo isset($field['show_label']) ? 'checked' : ''; ?> />


													<input type="hidden"
														name="price_drop_notification_expert[form][fields][<?php echo htmlspecialchars($field['field_key']); ?>][order]"
														class="field-order"
														value="<?php echo htmlspecialchars($field['order']); ?>" />
												</div> <!-- Close .accordion-content -->
											</div> <!-- Close .accordion-container -->
										</div> <!-- Close .field-item -->
									<?php endforeach; ?>
								</div>
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
	public function register_form_settings() {
		// Register settings group
		register_setting('pcne_form_settings_group', 'price_drop_notification_expert');

		// Add settings section
		add_settings_section(
			'pcne_form_section',
			'Main Settings',
			array($this, 'form_settings_section_callback'),
			'pricedropnotifexpert-form'
		);
		// Add fields
		add_settings_field(
			'form_field_button_text',
			'Button Text',
			array($this, 'form_field_button_callback'),
			'pricedropnotifexpert-form',
			'pcne_form_section'
		);
		// Add fields
		add_settings_field(
			'pcne_form_field_1',
			'Form Title',
			array($this, 'form_field_callback'),
			'pricedropnotifexpert-form',
			'pcne_form_section'
		);
		add_settings_field(
			'pcne_form_field_2',
			'Form Description',
			array($this, 'form_field_2_callback'),
			'pricedropnotifexpert-form',
			'pcne_form_section'
		);
		add_settings_field(
			'pcne_form_field_3',
			'Prefill Form Data',
			array($this, 'form_field_3_callback'),
			'pricedropnotifexpert-form',
			'pcne_form_section'
		);
	}
	/**
	 * Section callback
	 *
	 * @since    1.0.0
	 */
	public function form_settings_section_callback() {
		?>
		<h4>
			<?php _e('Form Basic Settings'); ?>
		</h4>
		<hr>
		<?php
	}

	/**
	 * AX Notification Field
	 * @return void
	 */
	public function form_field_button_callback() {
		$options = get_option('price_drop_notification_expert');
		?>
		<input id="form_field_button_text" type="text" name="price_drop_notification_expert[form][setting][button_text]"
			value="<?php echo isset($options['form']['setting']['button_text']) ? $options['form']['setting']['button_text'] : ''; ?>" />
		<?php
	}

	/**
	 * AX Notification Field
	 * @return void
	 */
	public function form_field_callback() {
		$options = get_option('price_drop_notification_expert');
		?>
		<input type="text" name="price_drop_notification_expert[form][setting][title]"
			value="<?php echo isset($options['form']['setting']['title']) ? $options['form']['setting']['title'] : ''; ?>" />
		<?php
	}


	/**
	 * AX Notification Field
	 * @return void
	 */
	public function form_field_2_callback() {
		$options = get_option('price_drop_notification_expert');
		?>
		<textarea type="text"
			name="price_drop_notification_expert[form][setting][description]"><?php echo isset($options['form']['setting']['description']) ? $options['form']['setting']['description'] : ''; ?></textarea>
		<?php
	}

	/**
	 * AX Notification Field
	 * @return void
	 */
	public function form_field_3_callback() {
		$options = get_option('price_drop_notification_expert');
		$checked = isset($options['form']['setting']['prefill_form_data']) ? checked($options['form']['setting']['prefill_form_data'], 1, false) : '';
		echo '<input type="checkbox" name="price_drop_notification_expert[form][setting][prefill_form_data]" value="1" ' . $checked . ' />';
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
		?>
		<input type='text' name='price_drop_notification_expert[fcm_server_key]'
			value="<?php echo isset($options['fcm_server_key']) ? esc_attr($options['fcm_server_key']) : ''; ?>" />
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
		$latest_price_history = [];
		// Check if product_id is the same but variation_id is different
		if ($last_entry && $last_entry['product_id'] === $product_id && $last_entry['variation_id'] !== $variation_id) {
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
			// Add new price entry to the history
			$price_history[] = $latest_price_history;

			// Encode the updated price history
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
	}



	// Helper: Send notification (placeholder function)

	private function process_notification($notification_id, $price_history) {
		$notification_type = get_post_meta($notification_id, 'type', true);

		if ($notification_type === 'info') {
			$product_id = $price_history['product_id'];
			$old_price = $price_history['price_history']['price'];
			$product = wc_get_product($product_id);
			$new_price = $product->get_price();
			$savings = $old_price - $new_price;

			if ($savings > 0) {
				// Save the message to display later
				$message = sprintf(
					'<div class="price-drop-alert">Price dropped! You save: %s</div>',
					wc_price($savings)
				);

				// Store in product meta
				update_post_meta($product_id, '_price_drop_message', $message);
			}
		}
	}
	private function send_notification($notification_id, $price_history) {
		$notification_type = get_post_meta($notification_id, 'type', true);
		switch ($notification_type) {
			case 'info':
				$product_id = $price_history['product_id'];
				$variation_id = $price_history['variation_id'];
				$price_history = $price_history['price_history'];
				$product = wc_get_product($product_id);
				$old_price = $price_history['price'];
				$new_price = $product->get_price();
				$savings = $old_price - $new_price;
				ob_Start();
				if ($savings > 0) {
					?>
					<p>You Save: <?php echo $savings; ?></p>
					<?php
					$content = ob_get_clean();
					// add_action('woocommerce_single_product_summary', function ($content) {
					// 	echo $content;
					// });

					// error_log($content);
				}
				break;
			case 'email':
				$product_id = $price_history['product_id'];
				$variation_id = $price_history['variation_id'];
				$price_history = $price_history['price_history'];
				$product = wc_get_product($product_id);
				$old_price = $price_history['price'];
				$new_price = $product->get_price();
				$savings = $old_price - $new_price;
				ob_Start();
				if ($savings > 0) {
					?>
					<p>You Save: <?php echo $savings; ?></p>
					<?php
					$content = ob_get_clean();
					wp_mail('basanta.subedi@webandapp.com.np', 'Test mail', $content);

					// error_log($content);
				}
				break;
			case 'push-notifications':
				# code...
				break;
			case 'message':
				# code...
				break;
			default:
				# code...
				break;
		}
		// Implement your notification logic here

	}

	function sssssssssssssss() {

	}
}
