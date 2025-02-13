<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/false
 * @since      1.0.0
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/endpoints
 */

class Price_Drop_Notify_Expert_Form_Setting {

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
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	public function add_admin_menu() {
		add_submenu_page(
			'pricedropnotifexpert',
			'Form',
			'Form',
			'manage_options',
			'pricedropnotifexpert-form',
			array($this, 'pcne_form_page_callback'),
			5
		);
	}
	function pcne_form_page_callback() {
		$default_fields = array(
			array(
				'field_key' => 'name',
				'label' => 'Name',
				'required' => true,
				'show_label' => true,
				'order' => 1,
				'type' => 'text',
			),
			array(
				'field_key' => 'phone',
				'label' => 'Phone',
				'required' => true,
				'show_label' => true,
				'order' => 2,
				'type' => 'tel',
			),
			array(
				'field_key' => 'email',
				'label' => 'Email',
				'required' => true,
				'show_label' => true,
				'order' => 3,
				'type' => 'email',
			),
			array(
				'field_key' => 'preferred_method_of_notify',
				'label' => 'Preferred Method of Notify',
				'required' => true,
				'show_label' => true,
				'order' => 4,
			),
			array(
				'field_key' => 'additional_info',
				'label' => 'Additional Info for Variable Product',
				'required' => false,
				'show_label' => true,
				'order' => 5,
				'type' => 'textarea',
			),
		);
		$options = get_option('price_drop_notification_expert');
		$formfields = isset($options['form']['fields']) && is_array($options['form']['fields'])
			? $options['form']['fields']
			: $default_fields;

		// (Optional) If your saved configuration is stored as an associative array (keyed by field_key),
// convert it to a numeric array.
		if (isset($formfields['name'])) {
			$temp = array();
			foreach ($formfields as $key => $field) {
				$temp[] = $field;
			}
			$formfields = $temp;
		}

		// Sort the fields by order.
		usort($formfields, function ($a, $b) {
			return intval($a['order']) - intval($b['order']);
		});
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
									$formfields = isset($options['form']['fields']) ? $options['form']['fields'] : $formfields;
									foreach ($formfields as $field):
										?>
										<div class="field-item"
											data-key="<?php echo isset($options['form']['fields'][$field['field_key']]['field_key']) ? $options['form']['fields'][$field['field_key']]['field_key'] : htmlspecialchars($field['field_key']); ?>">

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
														value="<?php echo isset($options['form']['fields'][$field['field_key']]['order']) ? $options['form']['fields'][$field['field_key']]['order'] : $field['order']; ?>" />
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
}
