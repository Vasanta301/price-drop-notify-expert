<?php

/**
 * Utility functions for the Price Drop Notify Expert plugin.
 *
 * @package    Price_Drop_Notify_Expert
 * @subpackage Price_Drop_Notify_Expert/includes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if a product price has dropped.
 *
 * @param int $product_id The ID of the product.
 * @param float $old_price The old price of the product.
 * @param float $new_price The new price of the product.
 * @return bool True if the price has dropped, false otherwise.
 */
function has_price_dropped($product_id, $old_price, $new_price) {
    return $new_price < $old_price;
}

/**
 * Send a notification email when a product price drops.
 *
 * @param string $email The recipient email address.
 * @param string $product_name The name of the product.
 * @param float $old_price The old price of the product.
 * @param float $new_price The new price of the product.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function send_price_drop_notification($email, $product_name, $old_price, $new_price) {
    $subject = 'Price Drop Alert: ' . $product_name;
    $message = sprintf(
        'The price of "%s" has dropped from %s to %s.',
        $product_name,
        wc_price($old_price),
        wc_price($new_price)
    );

    return wp_mail($email, $subject, $message);
}

/**
 * Format a price with currency symbol.
 *
 * @param float $price The price to format.
 * @return string The formatted price.
 */
function format_price($price) {
    return wc_price($price);
}

/**
 * Get the current price of a product.
 *
 * @param int $product_id The ID of the product.
 * @return float The current price of the product.
 */
function get_product_price($product_id) {
    $product = wc_get_product($product_id);
    return $product ? $product->get_price() : 0;
}

/**
 * Log a message to the WordPress debug log.
 *
 * @param mixed $message The message to log.
 */
function log_price_drop_message($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

/**
 * Get Price history 
 * @param mixed $product_id
 * @param mixed $variation_id
 */
function get_price_history($product_id, $variation_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'price_drop_notify_price_history';

    $history = $wpdb->get_var($wpdb->prepare(
        "SELECT price_history FROM $table_name 
			 WHERE product_id = %d AND (variation_id = %d OR variation_id IS NULL) 
			 LIMIT 1",
        $product_id, $variation_id
    ));

    return $history ? json_decode($history, true) : [];
}

if (!function_exists('elog')) {
    function elog($data) {
        if (!$data) {
            return;
        }
        return error_log(print_r($data, true));
    }
}

function get_woocommerce_product_types() {
    $product_types = WC()->product_factory->get_product_types();
    return $product_types;
}