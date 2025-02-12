<?php
/**
 * Price Change Notification Email Template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/price-change-notification.php.
 *
 * @package YourPlugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// If available, load WooCommerce's email header.
if (function_exists('wc_get_email_header')) {
    echo wc_get_email_header($email_heading);
} else {
    ?>
    <h1><?php echo esc_html($email_heading); ?></h1>
    <?php
}
?>

<p>Hi <?php echo esc_html($customer_name); ?>,</p>
<?php
$product = wc_get_product($product_id);
$product_name = $product ? $product->get_name() : 'Unknown Product';
?>
<p>The price for product "<?php echo esc_html($product_name); ?>" (ID: <?php echo esc_html($product_id); ?>) has been
    updated.</p>

<?php if (!empty($price_history)): ?>
    <p><strong>Latest Price Info:</strong></p>
    <p><?php echo nl2br(esc_html($price_history)); ?></p>
<?php endif; ?>
<?php if (!empty($variation_attributes)): ?>
    <p><strong>Selected Variant:</strong></p>
    <ul>
        <?php foreach ($variation_attributes as $attribute): ?>
            <li><?php echo esc_html($attribute); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<p><strong>Changed On:</strong> <?php echo esc_html($change_date); ?></p>

<p>Thanks for being with us!</p>

<?php
// Load WooCommerce's email footer if available.
if (function_exists('wc_get_email_footer')) {
    echo wc_get_email_footer();
}
?>