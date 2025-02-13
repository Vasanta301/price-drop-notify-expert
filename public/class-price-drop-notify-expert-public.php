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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/public.css', array(), $this->version, 'all');

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/public.js', array('jquery', 'firebase-app', 'firebase-messaging'), $this->version, false);
		wp_enqueue_script('chart-js', '//cdn.jsdelivr.net/npm/chart.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name, 'pcne_ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'pcne_nonce' => wp_create_nonce('pcne_nonce') // Create nonce
		));
		wp_enqueue_script(
			'firebase-app',
			'https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js',
			[],
			'8.10.1',
			true
		);


		wp_enqueue_script(
			'firebase-messaging',
			'https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js',
			['firebase-app'],
			'8.10.1',
			true
		);
	}
	public function simple_popup_form_shortcode() {
		ob_start();

		// Check if we are on a product page
		if (is_product()) {
			global $product;

			// Get the product type
			$product_type = $product->get_type();

			// Initialize variable products array
			$variable_products = [];

			// If the product is variable, get variations
			$options = get_option('price_drop_notification_expert');
			$prefill_form_data = isset($options['form']['prefill_form_data']) ? checked($options['form']['prefill_form_data'], 1, false) : '';
			?>

			<button id="pcne-open-form-btn"
				class="button"><?= isset($options['form']['button_text']) ? $options['form']['button_text'] : __('Notify me on price drop', ''); ?></button>
			<div id="pcne-form-popup" class="pcne-popup-overlay">
				<div class="pcne-popup-content"><span id="pcne-close-form-btn">&times;</span>

					<?php if (isset($options['form']['title'])): ?>
						<h2><?php echo isset($options['form']['title']) ? $options['form']['title'] : __('Price Drop Notifiy Form', ''); ?>
						</h2>
					<?php endif; ?>

					<?php if (isset($options['form']['title'])): ?>
						<div>
							<p><?php echo isset($options['form']['description']) ? $options['form']['description'] : ''; ?></p>
						</div>
					<?php endif; ?>
					<form id="pcne-product-popup-form" <?php if ($product_type === 'variable') { ?>data-variable-data="<?php echo json_encode($variable_products); ?>" <?php } ?>
						data-product-id="<?php echo get_the_ID(); ?>">
						<?php $options = get_option('price_drop_notification_expert');
						$formfields = isset($options['form']['fields']) ? $options['form']['fields'] : [];
						?>
						<?php if (is_user_logged_in() && $prefill_form_data): ?>
							<label>
								<input type="checkbox" id="pcne-autofill-details"> Use my saved details
							</label>
						<?php endif; ?>
						<?php if (!empty($formfields)) { ?>
							<?php foreach ($formfields as $key => $field) {
								$field_key = $field['field_key'];
								$label = $field['label'];
								$required = !empty($field['required']);
								$type = !empty($field['type']) ? $field['type'] : 'text';
								$placeholder = 'Your ' . ucfirst($field_key);
								if (in_array($field['field_key'], ['additional_info'])) { ?>
									<?php
									$variable_products = [];
									if ($product_type === 'variable') {
										$available_variations = $product->get_available_variations();
										$attributes = $product->get_attributes();

										// Store variations by attributes
										foreach ($available_variations as $variation) {
											$variation_attributes = $variation['attributes'];
											$variation_id = $variation['variation_id'];
											$variation_stock = $variation['is_in_stock'] ? 'In Stock' : 'Out of Stock';

											// Store the variation by its attributes
											$variable_products[$variation_id] = [
												'attributes' => $variation_attributes,
												'stock' => $variation_stock,
											];
										}
										?>
										<div id="attribute-selects">
											<h3>Additional Attributes</h3>
											<?php foreach ($attributes as $attribute_name => $attribute):
												$terms = get_terms([
													'taxonomy' => $attribute_name,
													'hide_empty' => false,
												]);
												if (is_array($terms)) {
													?>
													<div class="row">
														<div class="col-25">
															<label
																for="<?php echo esc_attr($attribute_name); ?>"><?php echo esc_html(str_replace('pa_', '', $attribute['name'])); ?></label>
														</div>
														<div class="col-75">
															<select class="attribute-select" data-attribute="<?php echo esc_attr($attribute_name); ?>">
																<option value="">Select <?php echo esc_html(str_replace('pa_', '', $attribute['name'])); ?>
																</option>
																<?php
																// Get the terms for the attribute
							

																// Create options for each term
							
																foreach ($terms as $term): ?>
																	<option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?>
																	</option>
																<?php endforeach; ?>

															</select>
														</div>
													</div>
												<?php }
												?>
											<?php endforeach; ?>
										</div>
									<?php } ?>
								<?php } elseif ($field['field_key'] == 'preferred_method_of_notify') { ?>
									<?php $select_options = [
										'email' => 'Email',
										'sms' => 'SMS',
										'push' => 'Push Notification',
									]; ?>
									<div class="row">
										<div class="col-25">
											<label for="<?php echo esc_attr($field_key); ?>">
												<?php echo esc_html($label); ?>
												<?php if ($required): ?>
													<span style="color: red;">*</span>
												<?php endif; ?>
											</label>
										</div>
										<div class="col-75">
											<select name="<?php echo esc_attr($field_key); ?>" id="<?php echo esc_attr($field_key); ?>" <?php echo $required ? 'required' : ''; ?>>
												<option value=""><?php echo 'Select ' . esc_html($label); ?></option>
												<?php foreach ($select_options as $opt_value => $opt_label): ?>
													<option value="<?php echo esc_attr($opt_value); ?>"><?php echo esc_html($opt_label); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>

								<?php } else { ?>
									<div class="row">
										<div class="col-25">
											<label for="<?php echo esc_attr($field_key); ?>">
												<?php echo esc_html($label); ?>
												<?php if ($required): ?>
													<span style="color: red;">*</span>
												<?php endif; ?>
											</label>
										</div>
										<div class="col-75">
											<input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($field_key); ?>"
												id="<?php echo esc_attr($field_key); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"
												<?php echo $required ? 'required' : ''; ?>>
										</div>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						<div class="row">
							<div class="col-75">
								<button type="submit" class="button button-primary">Submit</button>
							</div>
						</div>
					</form>
					<div id="pcne-form-response"></div>
				</div>
			</div>
			<?php
		}

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
		$formData = array_map(function ($item) {
			if (is_array($item)) {
				return array_map(function ($sub_item) {
					if (is_array($sub_item)) {
						return array_map('sanitize_text_field', $sub_item);
					}
					return sanitize_text_field($sub_item);
				}, $item);
			}
			return sanitize_text_field($item);
		}, $_POST['formData']);

		// if (!isset($_POST['pcne_nonce']) || !wp_verify_nonce($_POST['pcne_nonce'], 'pcne_nonce')) {
		// 	wp_send_json(array("status" => "error", "message" => "Invalid nonce!"));
		// 	wp_die();
		// }

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
		$selected_attributes = isset($formData['selected_attributes']) ? json_encode($formData['selected_attributes']) : '';
		$preferred_method_of_notify = isset($formData['preferred_method_of_notify']) ? sanitize_text_field($formData['preferred_method_of_notify']) : '';
		$token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
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
					'variable_data' => $selected_attributes,
					'name' => $name,
					'email' => $email,
					'phone' => $phone,
					'preferred_method_of_notify' => $preferred_method_of_notify,
					'token' => $token,
					'submitted_at' => current_time('mysql'),
				],
				['id' => $existing_entry->id],
				['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
				['%d']
			);
		} else {
			// Insert a new record
			$data_update = $wpdb->insert(
				$table_name,
				[
					'user_id' => $user_id,
					'product_id' => $product_id,
					'variable_data' => $selected_attributes,
					'name' => $name,
					'email' => $email,
					'phone' => $phone,
					'preferred_method_of_notify' => $preferred_method_of_notify,
					'token' => $token,
					'submitted_at' => current_time('mysql'),
				],
				['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
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
		if ($data_update) {
			wp_send_json(array("status" => "success", "message" => "Thank you! We will contact you soon."));
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
	 * @return $response
	 */
	public function get_user_info() {

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
	public function display_price_history_stats_and_graph() {
		global $product;
		$filtered_price_history = process_notifications($product->get_ID());
		if (!empty($filtered_price_history)) {
			view_price_history_stats_and_graph($filtered_price_history);
		}
	}

	public function custom_trigger_action() {

		if (is_product()) {
			global $product;
			do_action('price_drop_notify_email_on_price_dropped', $product->get_ID());
		} else {
			error_log('price_drop_notify_push_notif_on_price_dropped was not called: No valid product.');
		}
	}
	public function serve_firebase_sw() {
		header("Service-Worker-Allowed: /");
		readfile(plugin_dir_path(__FILE__) . '/firebase-messaging-sw.js');
		exit;
	}

	// Create a URL endpoint for the service worker

	public function init_serve_firebase_sw() {
		if (isset($_GET['firebase-sw'])) {
			$this->serve_firebase_sw();
		}
	}
}
