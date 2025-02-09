<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpress.org/false
 * @since      1.0.0
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/public
 * @author     False <basantasubedi301@gmail.com>
 */
class Price_Drop_Notify_Expert_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/price-drop-notify-expert-public.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/price-drop-notify-expert-public.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name, 'pcne_ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'pcne_nonce' => wp_create_nonce('pcne_nonce') // Create nonce
		));
	}
	public function simple_popup_form_shortcode() {
		ob_start();
		?>
		<button id="pcne-open-form-btn" class="button">Notify me on price drop</button>

		<div id="pcne-form-popup" class="pcne-popup-overlay">
			<div class="pcne-popup-content">
				<span id="pcne-close-form-btn">&times;</span>
				<h2>Please fill out this form</h2>
				<form id="pcne-product-popup-form" data-product-id="<?php echo get_the_ID(); ?>">
					<?php if (is_user_logged_in()): ?>
						<label>
							<input type="checkbox" id="pcne-autofill-details"> Use my saved details
						</label>
					<?php endif; ?>
					<input type="text" name="name" id="name" placeholder="Your Name" required autocomplete="name">
					<input type="email" name="email" id="email" placeholder="Your Email" required autocomplete="email">
					<input type="tel" name="phone" id="phone" placeholder="Your Phone" required autocomplete="phone">
					<button type="submit" class="button button-primary">Submit</button>
				</form>
				<div id="pcne-form-response"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	/**
	 * Summary of add_popup_form_to_product
	 * @return void
	 */
	public function add_popup_form_to_product() {
		if (is_product()) {
			echo do_shortcode('[pcne_popup_form]');
		}
	}

	/**
	 * Summary of handle_ajax_form_submission
	 * @return void
	 */
	public function handle_form_submission() {
		$formData = array_map('sanitize_text_field', $_POST['formData']);
		if (!isset($_POST['pcne_nonce']) || !wp_verify_nonce($_POST['pcne_nonce'], 'pcne_nonce')) {
			wp_send_json(array("status" => "error", "message" => "Invalid nonce!"));
			wp_die();
		}

		if (!isset($formData['name']) || !isset($formData['email']) || !isset($formData['phone'])) {
			wp_send_json(array("status" => "error", "message" => "All fields are required!"));
			wp_die();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'price_drop_notify_expert_contacts';

		// Values
		$user_id = is_user_logged_in() ? get_current_user_id() : null;
		$product_id = isset($formData['product_id']) ? absint($formData['product_id']) : '';
		$name = isset($formData['name']) ? sanitize_text_field($formData['name']) : '';
		$email = isset($formData['email']) ? sanitize_email($formData['email']) : '';
		$phone = isset($formData['phone']) ? sanitize_text_field($formData['phone']) : '';

		// Check if a record with the same email or phone already exists
		$existing_entry = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE email = %s OR phone = %s",
				$email,
				$phone
			)
		);

		if ($existing_entry) {
			// Update the existing record
			$data_update = $wpdb->update(
				$table_name,
				[
					'user_id' => $user_id,
					'product_id' => $product_id,
					'name' => $name,
					'email' => $email,
					'phone' => $phone,
					'submitted_at' => current_time('mysql'),
				],
				['id' => $existing_entry->id],
				['%d', '%d', '%s', '%s', '%s', '%s'],
				['%d']
			);
		} else {
			// Insert a new record
			$data_update = $wpdb->insert(
				$table_name,
				[
					'user_id' => $user_id,
					'product_id' => $product_id,
					'name' => $name,
					'email' => $email,
					'phone' => $phone,
					'submitted_at' => current_time('mysql'),
				],
				['%d', '%d', '%s', '%s', '%s', '%s']
			);
		}

		if ($data_update === false) {
			wp_send_json(array("status" => "error", "message" => "Failed to add info. Please try again."));
			wp_die();
		}

		if (!is_email($formData['email'])) {
			wp_send_json(array("status" => "error", "message" => "Invalid email address!"));
			wp_die();
		}

		if (!is_email($email)) {
			wp_send_json(array("status" => "error", "message" => "Invalid email address!"));
			wp_die();
		}

		// Send email
		// $to = get_option('admin_email');
		// $subject = "New Inquiry from $name";
		// $message = "Name: $name\nEmail: $email\nPhone: $phone";
		// $headers = array('Content-Type: text/plain; charset=UTF-8');

		// if (wp_mail($to, $subject, $message, $headers)) {
		// 	wp_send_json(array("status" => "success", "message" => "Thank you! We will contact you soon."));
		// } else {
		// 	wp_send_json(array("status" => "error", "message" => "Email sending failed."));
		// }

		wp_die();
	}


	/**
	 * Get User info to pull information from the user profile if any
	 * @return never
	 */
	function get_user_info() {

		if (!is_user_logged_in()) {
			wp_send_json_error(['message' => 'User not logged in']);
		}

		if (!isset($_POST['pcne_nonce']) || !wp_verify_nonce($_POST['pcne_nonce'], 'pcne_nonce')) {
			wp_send_json(array("status" => "error", "message" => "Invalid nonce!"));
			wp_die();
		}

		$user_id = get_current_user_id();
		$user_info = get_userdata($user_id);

		$response = [
			'name' => $user_info->display_name,
			'email' => $user_info->user_email,
			'phone' => get_user_meta($user_id, 'billing_phone', true) ?: '',
		];

		wp_send_json_success($response);
	}

}
