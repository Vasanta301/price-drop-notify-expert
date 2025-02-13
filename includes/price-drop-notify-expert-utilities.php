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

if (!function_exists("elog")) {
    function elog($var) {
        error_log(print_r($var, true));
    }
}

function get_woocommerce_product_types() {
    $product_types = WC()->product_factory->get_product_types();
    return $product_types;
}

// Process notifications for the product
function process_notifications($product_id) {

    // Get all active notifications
    $notifications = get_posts([
        'post_type' => 'pricedropnotifexpert',
        'meta_query' => [
            [
                'key' => 'enabled',
                'value' => '1',
                'compare' => '=',
            ],
        ],
        'posts_per_page' => -1
    ]);

    foreach ($notifications as $notification) {
        if (is_product_tracked($notification->ID, $product_id) == true) {
            $filtered_price_history = filter_price_history($notification->ID, $product_id);
            return $filtered_price_history;
        }
    }
}

/**
 * Filters the given price history array by date based on tracking type.
 *
 * Expected $price_history structure:
 * [
 *   'price_history' => [
 *       ['price' => '10.99', 'date' => '2025-01-01'],
 *       ['price' => '9.99',  'date' => '2025-02-15'],
 *       // ...
 *   ]
 * ]
 *
 * @param array  $notification_id Notification ID
 * @param string $product_id  Product ID.
 *
 * @return array Filtered price history in the same structure.
 */
function filter_price_history($notification_id, $product_id) {
    $track_products_by = get_post_meta($notification_id, 'track_products_by', false);
    $start_date = get_post_meta($notification_id, 'start_date', true);
    $end_date = get_post_meta($notification_id, 'end_date', true);
    $price_history = get_price_history_by_product_id($product_id);
    // If there's no history or the structure is unexpected, bail.
    if (empty($price_history) || !isset($price_history['price_history']) || !is_array($price_history['price_history'])) {
        return $price_history;
    }

    $filtered = [];

    foreach ($price_history as $entry) {
        // Convert entry date to a timestamp.
        $entry_timestamp = strtotime($entry['date']);

        // Depending on the tracking type, decide if this entry should be included.
        switch ($track_products_by) {
            case 'from-specific-date':
                // Keep if the entry date is on or after the start date.
                if (strtotime($start_date) <= $entry_timestamp) {
                    $filtered[] = $entry;
                }
                break;

            case 'at-specific-date':
                // Keep if the entry date matches the start date exactly (comparing Y-m-d parts).
                if (date('Y-m-d', $entry_timestamp) === $start_date) {
                    $filtered[] = $entry;
                }
                break;

            case 'between-dates':
                // Ensure both start and end dates are set.
                if (!empty($start_date) && !empty($end_date)) {
                    // Keep if entry is between start and end dates (inclusive).
                    if (strtotime($start_date) <= $entry_timestamp && $entry_timestamp <= strtotime($end_date)) {
                        $filtered[] = $entry;
                    }
                }
                break;

            case 'all':
            default:
                // For 'all', no filtering is applied (or you might choose to limit to changes from now on).
                $filtered[] = $entry;
                break;
        }
    }

    // Return the data maintaining the original structure.
    return ['price_history' => $filtered];
}

function get_price_history_by_product_id($product_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'price_drop_notify_price_history';

    $history = $wpdb->get_var($wpdb->prepare(
        "SELECT price_history FROM $table_name 
         WHERE product_id = %d 
         LIMIT 1",
        $product_id
    ));

    return $history ? json_decode($history, true) : [];
}

/**
 * Helper: Check if the product matches the notification criteria
 *
 * @param [type] $notification_id
 * @param [type] $product_id
 * @return boolean
 */
function is_product_tracked($notification_id, $product_id) {
    // Check direct product selection
    $tracked = 'false';
    $track_products_by = get_post_meta($notification_id, 'track_products_by', false);
    $selected_products = maybe_unserialize(get_post_meta($notification_id, 'selected_products', true));
    $selected_categories = maybe_unserialize(get_post_meta($notification_id, 'selected_categories', true));
    if ($track_products_by[0] == 'products') {
        if (empty($selected_products))
            $tracked = 'false';
        if (!empty($selected_products)) {
            foreach ($selected_products as $key => $product) {
                if ($product['value'] == $product_id) {
                    $tracked = 'true';
                    break;
                }
            }
        }

    } elseif ($track_products_by[0] == 'categories') {
        if (empty($selected_categories))
            $tracked = 'true';

        if (!empty($selected_categories)) {
            $product_categories = wp_get_post_terms(
                $product_id,
                'product_cat',
                ['fields' => 'ids']
            );
            if (is_array($product_categories) && is_array($selected_categories)) {
                foreach ($selected_categories as $key => $category) {
                    if (in_array($category['value'], $product_categories)) {
                        $tracked = 'true';
                    }
                }
            }
        }
    }

    return $tracked;
}

// Function to display price history on product page
function view_price_history_stats_and_graph($price_history) {
    global $product;

    if (empty($price_history))
        return;

    // Process price history data
    $prices = [];
    $dates = [];
    $min_price = PHP_INT_MAX;
    $max_price = 0;
    $current_price = get_product_price($product->get_ID());
    if (!empty($price_history) && count($price_history) > 0) {
        foreach ($price_history as $key => $entry) {
            $price = (float) $entry['price'];
            $date = date('M j, Y', strtotime($entry['date']));

            $prices[] = $price;
            $dates[] = $date;

            if ($price < $min_price)
                $min_price = $price;
            if ($price > $max_price)
                $max_price = $price;
        }
        ?>
        <div class="price-history-stats">
            <h3><?php echo __('Price History Statistics', 'your-text-domain') ?></h3>
            <div class="stats-grid">
                <div><strong><?php echo __('Current Price', 'your-text-domain'); ?></strong>
                    <?php echo wc_price($current_price) ?></div>';
                <div><strong><?php echo __('Highest Price', 'your-text-domain'); ?></strong> <?php echo wc_price($max_price) ?>
                </div>
                <div><strong><?php echo __('Lowest Price', 'your-text-domain'); ?></strong> <?php echo wc_price($min_price) ?>
                </div>
            </div>
        </div>
        <div class="price-history-graph">
            <h3><?php _e('Price History Graph', 'your-text-domain'); ?></h3>
            <canvas id="priceHistoryChart" style="width:100%; height:400px;"></canvas>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('priceHistoryChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [{
                            label: '<?php _e('Price History', 'your-text-domain'); ?>',
                            data: <?php echo json_encode($prices); ?>,
                            borderColor: '#4CAF50',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function (value) {
                                        return '<?php echo htmlspecialchars_decode(get_woocommerce_currency(), ENT_QUOTES); ?>' + ' ' + value;
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return '<?php _e('Price', 'your-text-domain'); ?>: ' +
                                            '<?php echo htmlspecialchars_decode(get_woocommerce_currency(), ENT_QUOTES); ?>' + ' ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
        <?php
    }
}

function get_custom_email_template($template_name, $args = []) {
    // Remove the "includes" directory from the path
    $template_path = PRICE_DROP_NOTIFY_ROOT_PATH . 'templates/emails/';

    // Make sure the template file exists in your plugin
    if (!file_exists($template_path . $template_name)) {
        return 'Error: Template not found!';
    }

    // Load the template from your plugin
    return wc_get_template_html(
        $template_name,
        $args,
        '',  // Remove the custom subdirectory prefix
        $template_path // Directly provide the correct path
    );
}


function get_most_recent_price_history($price_history_json) {
    $price_history = json_decode($price_history_json, true);

    if (!empty($price_history) && is_array($price_history)) {
        $latest_entry = end($price_history); // Get the last price entry
        return [
            'price' => $latest_entry['price'],
            'date' => $latest_entry['date'],
        ];
    }

    return null; // Return null if no valid history
}

/**
 * Trigger email notifications when a product’s price changes.
 *
 * @param int $product_id The ID of the product whose price was changed.
 */
function trigger_price_change_email_notification($product_id) {
    global $wpdb;

    $price_history_table = $wpdb->prefix . 'price_drop_notify_price_history';
    $contacts_table = $wpdb->prefix . 'price_drop_notify_expert_contacts';

    // Get all price history records for the product
    $price_history_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $price_history_table WHERE product_id = %d",
        $product_id
    ));

    if (!$price_history_rows) {
        return; // No price history found
    }

    // Get most recent price history
    $latest_history = end($price_history_rows);
    $price_history = json_decode($latest_history->price_history, true);
    $most_recent = end($price_history);

    // Get subscribers
    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $contacts_table WHERE product_id = %d",
        $product_id
    ));

    if (empty($contacts)) {
        return; // No subscribers
    }

    $mailer = WC()->mailer();
    $subject = sprintf('Price Change Alert for Product #%d', $product_id);
    $email_heading = 'Price Change Notification';

    foreach ($contacts as $contact) {
        $email_content = [
            'email_heading' => $email_heading,
            'product_id' => $product_id,
            'price_history' => $most_recent['price'],
            'change_date' => $most_recent['date'],
            'customer_name' => sanitize_text_field($contact->name),
            'variation_attributes' => [],
        ];

        // Add variation data if exists
        if (!empty($contact->variable_data)) {
            $variation_data = json_decode($contact->variable_data, true);

            if (JSON_ERROR_NONE === json_last_error()) {
                $attributes = [];
                foreach ($variation_data as $taxonomy => $values) {
                    // Clean attribute name (remove 'pa_' prefix)
                    $clean_name = ucfirst(str_replace('pa_', '', $taxonomy));
                    if (isset($values['label'])) {
                        $attributes[] = sprintf(
                            '%s: %s',
                            $clean_name,
                            sanitize_text_field($values['label'])
                        );
                    }
                }
                $email_content['variation_attributes'] = $attributes;
            }
        }

        // Build email message
        $message = get_custom_email_template('price-change-notification.php', $email_content);

        // Send email
        $mailer->send(
            sanitize_email($contact->email),
            $subject,
            $message,
            '',
            ''
        );
    }
}

function trigger_price_change_firebase_push_notification($product_id) {
    global $wpdb;

    $server_key = 'AIzaSyDNCTIga_URLSJWzHlFXGArE8CVwGCVFk0';
    $url = 'https://fcm.googleapis.com/fcm/send';

    $price_history_table = $wpdb->prefix . 'price_drop_notify_price_history';
    $contacts_table = $wpdb->prefix . 'price_drop_notify_expert_contacts';

    // Get all price history records for the product
    $price_history_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $price_history_table WHERE product_id = %d",
        $product_id
    ));

    if (!$price_history_rows) {
        return; // No price history found
    }

    // Get most recent price history
    $latest_history = end($price_history_rows);
    $price_history = json_decode($latest_history->price_history, true);
    $most_recent = end($price_history);

    // Get subscribers
    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $contacts_table WHERE product_id = %d",
        $product_id
    ));

    if (empty($contacts)) {
        return; // No subscribers
    }

    foreach ($contacts as $contact) {
        if (!isset($contact->token) || empty($contact))
            return;
        $data = [];
        // Add variation data if exists
        if (!empty($contact->variable_data)) {
            $variation_data = json_decode($contact->variable_data, true);

            if (JSON_ERROR_NONE === json_last_error()) {
                $attributes = [];
                foreach ($variation_data as $taxonomy => $values) {
                    // Clean attribute name (remove 'pa_' prefix)
                    $clean_name = ucfirst(str_replace('pa_', '', $taxonomy));
                    if (isset($values['label'])) {
                        $attributes[] = sprintf(
                            '%s: %s',
                            $clean_name,
                            sanitize_text_field($values['label'])
                        );
                    }
                }
                $data['variation_attributes'] = $attributes;
            }
        }

        $fields = [
            'to' => $contact->token, // Single device token
            'notification' => [
                'title' => '🔥 Price Drop Alert!',
                'body' => "New price: ",
                'icon' => 'https://example.com/icon.png', // Optional: Change to your app's icon
                'click_action' => 'https://example.com' // URL to open on click
            ],
            'data' => $data,
        ];

        $headers = [
            'Authorization: key=' . $server_key,
            'Content-Type: application/json',
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $response = curl_exec($ch);
        curl_close($ch);
        error_log('firebase reponse : ' . print_r($response, true));
        return $response;
    }
}