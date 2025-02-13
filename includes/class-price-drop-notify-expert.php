<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wordpress.org/false
 * @since      1.0.0
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/includes
 * @author     False <basantasubedi301@gmail.com>
 */
class Price_Drop_Notify_Expert {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Price_Drop_Notify_Expert_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The current version of the plugin.
	 */
	protected $plugin_slug;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('PRICE_DROP_NOTIFY_EXPERT_VERSION')) {
			$this->version = PRICE_DROP_NOTIFY_EXPERT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'price-drop-notify-expert';
		$this->plugin_slug = 'pricedropnotifyexpert';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_endpoints_hooks();
		$this->define_admin_hooks();
		$this->define_front_hooks();
		$this->define_form_setting_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Price_Drop_Notify_Expert_Loader. Orchestrates the hooks of the plugin.
	 * - Price_Drop_Notify_Expert_i18n. Defines internationalization functionality.
	 * - Price_Drop_Notify_Expert_Admin. Defines all hooks for the admin area.
	 * - Price_Drop_Notify_Expert_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-price-drop-notify-expert-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-price-drop-notify-expert-i18n.php';

		/** 
		 * Utility Functions that can be called directly
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/price-drop-notify-expert-utilities.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-price-drop-notify-expert-admin.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-price-drop-notify-expert-setting-form.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-price-drop-notify-expert-endpoints.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-price-drop-notify-contact-table.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-price-drop-notify-expert-public.php';

		$this->loader = new Price_Drop_Notify_Expert_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Price_Drop_Notify_Expert_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Price_Drop_Notify_Expert_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Price_Drop_Notify_Expert_Admin($this->get_plugin_name(), $this->get_version());

		//Enqueue scripts
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		//Add Menu settings
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');


		//Check price changes
		$this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'track_price_changes');
	}
	/**
	 * Register all of the hooks related to the endpoint and actions
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_endpoints_hooks() {
		$plugin_endpoints = new Price_Drop_Notify_Expert_Endpoints($this->get_plugin_name(), version: $this->get_version());
		$this->loader->add_action('init', $plugin_endpoints, 'create_cpt_for_storing_notification_info');
		$this->loader->add_action('rest_api_init', $plugin_endpoints, 'register_custom_rest_api_endpoint');

		//Handle fields and save/update
		$this->loader->add_action('init', $plugin_endpoints, 'register_notification_meta_fields');

	}

	/**
	 * Register all of the hooks related to the endpoint and actions
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_form_setting_hooks() {
		$plugin_admin_form = new Price_Drop_Notify_Expert_Form_Setting($this->get_plugin_name(), version: $this->get_version());
		$this->loader->add_action('admin_menu', $plugin_admin_form, 'add_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin_form, 'register_form_settings');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_front_hooks() {

		$plugin_public = new Price_Drop_Notify_Expert_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_shortcode('pcne_popup_form', $plugin_public, 'simple_popup_form_shortcode');
		$this->loader->add_action('wp_ajax_handle_form_submission', $plugin_public, 'handle_form_submission');
		$this->loader->add_action('wp_ajax_handle_form_submission', $plugin_public, 'handle_form_submission');
		$this->loader->add_action('wp_ajax_get_user_info', $plugin_public, 'get_user_info');
		$this->loader->add_action('wp_ajax_nopriv_wp_ajax_get_user_info', $plugin_public, 'get_user_info');
		$this->loader->add_action('woocommerce_single_product_summary', $plugin_public, 'add_popup_form_to_product', 15);
		$this->loader->add_action('woocommerce_single_product_summary', $plugin_public, 'display_price_history_stats_and_graph', 15);
		$this->loader->add_action('init', $plugin_public, 'init_serve_firebase_sw');

		//Test to trigger email
		$this->loader->add_action('woocommerce_before_single_product', $plugin_public, 'custom_trigger_action', 10, 0);

	}

	/**
	 * All public action hook from plugin
	 * @return void
	 * @since 1.0.0
	 */
	private function define_public_hooks() {
		//Action Trigger Hook of plugin
		add_action('price_drop_notify_email_on_price_dropped', 'trigger_price_change_email_notification', 10, 1);
		add_action('price_drop_notify_push_notification', 'trigger_price_change_firebase_push_notification', 10, 1);
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Price_Drop_Notify_Expert_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
