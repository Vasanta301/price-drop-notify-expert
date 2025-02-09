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

class Price_Drop_Notify_Expert_Endpoints {

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

	/**
	 * Create Custom Post Type | To 0store notificationq0
	 * @return void
	 * @since 1.0.0 
	 */
	public static function create_cpt_for_storing_notification_info() {
		register_post_type('pricedropnotifexpert', [
			'labels' => [
				'name' => 'Price Drop Notifications',
				'singular_name' => 'Price Drop Notification',
			],
			'public' => true,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'show_in_rest' => true,
			'supports' => ['title', 'editor'],
		]);
	}

	/**
	 * Register Custom Rest API endpoints
	 * @return void
	 * @since 1.0.0
	 */
	public function register_custom_rest_api_endpoint() {
		// Register a custom endpoint
		register_rest_route('pricedropnotifexpert/v1', '/notifications/', [
			'methods' => 'GET',
			'callback' => [$this, 'get_pricedropnotifexpert_posts'],
			'permission_callback' => '__return_true',
		]);
		register_rest_route('pricedropnotifexpert/v1', '/notification/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_pricedropnotifexpert_posts'],
			'permission_callback' => '__return_true',
		]);

		// Endpoint for creating a post
		register_rest_route('pricedropnotifexpert/v1', '/notification/', [
			'methods' => 'POST',
			'callback' => [$this, 'create_update_pricedropnotifexpert'],
			'permission_callback' => '__return_true',
		]);

		// Endpoint for creating a post
		register_rest_route('pricedropnotifexpert/v1', '/notification/(?P<id>\d+)', [
			'methods' => 'PUT',
			'callback' => [$this, 'create_update_pricedropnotifexpert'],
			'permission_callback' => '__return_true',
		]);

		// Endpoint for deleting a post
		register_rest_route('pricedropnotifexpert/v1', '/notification/(?P<id>\d+)', [
			'methods' => 'DELETE',
			'callback' => [$this, 'delete_pricedropnotifexpert_post'],
			'permission_callback' => '__return_true',
		]);
	}

	/**
	 * Callback to get Notification/Notifications
	 * @param mixed $data
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_pricedropnotifexpert_posts($data) {
		if (isset($data['id'])) {
			$args = [
				'post_type' => 'pricedropnotifexpert',
				'p' => $data['id'],
			];
		} else {
			$args = [
				'post_type' => 'pricedropnotifexpert',
				'posts_per_page' => -1,
			];
		}

		$query = new WP_Query($args);
		$posts = [];

		while ($query->have_posts()) {
			$query->the_post();

			$posts[] = [
				'id' => get_the_ID(),
				'title' => get_the_title(),
				'content' => get_the_content(),
				'enabled' => get_post_meta(get_the_ID(), 'enabled') ? get_post_meta(get_the_ID(), 'enabled') : 0,
				'tracking_type' => get_post_meta(get_the_ID(), 'tracking_type'),
				'start_date' => get_post_meta(get_the_ID(), 'start_date'),
				'end_date' => get_post_meta(get_the_ID(), 'end_date'),
				'type' => get_post_meta(get_the_ID(), 'type'),
				'selected_categories' => get_post_meta(get_the_ID(), 'selected_categories') ? get_post_meta(get_the_ID(), 'selected_categories')[0] : [],
				'selected_products' => get_post_meta(get_the_ID(), 'selected_products') ? get_post_meta(get_the_ID(), 'selected_products')[0] : [],
			];
		}
		//elog($posts);
		wp_reset_postdata();

		return rest_ensure_response($posts);
	}


	/**
	 * Callback to create a new post
	 * @param mixed $data
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_update_pricedropnotifexpert($data) {
		//elog($data['title']);
		$post_id = isset($data['id']) ? intval($data['id']) : '';
		$post_data = [
			'post_title' => sanitize_text_field($data['title']),
			'post_content' => sanitize_textarea_field($data['content']),
			'post_status' => 'publish',
			'post_type' => 'pricedropnotifexpert',
		];

		// Ensure the post exists

		$response = [];

		if (!$post_id) {
			// Insert a new post
			$post_id = wp_insert_post($post_data);
			$response['message'] = 'New Notification Created. Start Editing.';
		} else {
			$post_data['ID'] = $post_id;
			// Update existing post
			wp_update_post($post_data);
			$response['message'] = 'Notification Updated Successfully.';
		}
		//elog($data['selected_products']);
		$post = get_post($post_id);
		if ($post) {
			// Update meta fields
			update_post_meta($post_id, 'tracking_type', $data['tracking_type']);
			update_post_meta($post_id, 'start_date', $data['start_date']);
			update_post_meta($post_id, 'end_date', $data['end_date']);
			update_post_meta($post_id, 'type', sanitize_text_field($data['type']));
			update_post_meta($post_id, 'enabled', $data['enabled'] ? 1 : 0);
			update_post_meta($post_id, 'selected_categories', $data['selected_categories']);
			update_post_meta($post_id, 'selected_products', $data['selected_products']);
		}
		//elog($post);
		$response['success'] = true;
		$response['id'] = $post_id;

		return new WP_REST_Response($response, 200);
	}

	/**
	 * Callback to delete a Notification and it's data
	 * @param mixed $data
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_pricedropnotifexpert_post($data) {
		$post_id = $data['id'];

		// Ensure the post exists
		$post = get_post($post_id);
		if (!$post) {
			return new WP_Error('not_found', 'Post not found', ['status' => 404]);
		}

		// Delete the post
		wp_delete_post($post_id, true);

		return rest_ensure_response(['message' => 'Post deleted successfully']);
	}

	/**
	 * Register Meta Field to store Notification meta infos
	 * @return void
	 * @since 1.0.0
	 */
	function register_notification_meta_fields() {
		$meta_fields = [
			'start_date' => 'string',
			'end_date' => 'string',
			'type' => 'string',
			'enabled' => 'boolean',
		];

		foreach ($meta_fields as $key => $type) {
			register_post_meta('notification', $key, [
				'type' => $type,
				'single' => true,
				'show_in_rest' => true, // Enables REST API access
			]);
		}
	}
}
