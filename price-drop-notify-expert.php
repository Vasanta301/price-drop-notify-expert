<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/false
 * @since             1.0.0
 * @package           Price_Drop_Notify_Expert
 *
 * @wordpress-plugin
 * Plugin Name:       Price Drop Notify Expert
 * Plugin URI:        https://wordpress.org/price-drop-notify-expert
 * Description:       Want to notify user when there is change in price either price drop, festive offer, clearance sell ? This plugin is for you. This plugin is an extension to Woocommerce. 
 * Version:           1.0.0
 * Author:            False
 * Author URI:        https://wordpress.org/false/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       price-drop-notify-expert
 * Domain Path:       /languages
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PRICE_DROP_NOTIFY_EXPERT_VERSION', '1.0.0');
define('PRICE_DROP_NOTIFY_ROOT_PATH', plugin_dir_path(__FILE__));
define('PRICE_DROP_NOTIFY_URI_PATH', plugin_dir_url(__FILE__));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-price-drop-notify-expert-activator.php
 */
function activate_price_drop_notify_expert() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-price-drop-notify-expert-activator.php';
	Price_Drop_Notify_Expert_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-price-drop-notify-expert-deactivator.php
 */
function deactivate_price_drop_notify_expert() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-price-drop-notify-expert-deactivator.php';
	Price_Drop_Notify_Expert_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_price_drop_notify_expert');
register_deactivation_hook(__FILE__, 'deactivate_price_drop_notify_expert');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-price-drop-notify-expert.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_price_drop_notify_expert() {

	$plugin = new Price_Drop_Notify_Expert();
	$plugin->run();

}
run_price_drop_notify_expert();
