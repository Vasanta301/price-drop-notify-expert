<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/false
 * @since      1.0.0
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/includes
 * @author     False <basantasubedi301@gmail.com>
 */
class Price_Drop_Notify_Expert_Activator {

	public static function activate() {
		// Check if WooCommerce is active
		if (!self::is_woocommerce_active()) {
			deactivate_plugins(plugin_basename(__FILE__));
			return;
		}

		// Proceed with activation tasks
		self::create_table();
		self::create_price_history_table();

	}

	private static function is_woocommerce_active() {
		// Check if WooCommerce is active
		if (!function_exists('is_plugin_active')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active('woocommerce/woocommerce.php');
	}

	private static function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'price_drop_notify_expert_contacts';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL DEFAULT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	private static function create_price_history_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'price_drop_notify_price_history';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			product_id BIGINT UNSIGNED NOT NULL,
			variation_id BIGINT UNSIGNED DEFAULT NULL,
			price_history LONGTEXT NOT NULL, 
			change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX (product_id),
			INDEX (variation_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}
}