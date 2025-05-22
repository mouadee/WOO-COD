<?php
/**
 * Plugin Name: Custom COD Checkout Form
 * Description: Custom Cash on Delivery checkout form like screenshot.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: custom-cod-checkout
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function ccc_enqueue_assets() {
    wp_enqueue_style('ccc-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('ccc-script', plugin_dir_url(__FILE__) . 'script.js', ['jquery'], null, true);
    // Pass AJAX url
    wp_localize_script('ccc-script', 'ccc_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'ccc_enqueue_assets');

// Hook to add admin menu
add_action('admin_menu', 'ccc_register_admin_menu');

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin-settings.php';
}

add_action('wp_enqueue_scripts', 'ccc_disable_right_click_script');
function ccc_disable_right_click_script() {
    $disable_right_click = get_option('ccc_disable_right_click', false);
    if ($disable_right_click) {
        wp_enqueue_script('jquery'); // Make sure jQuery is loaded

        wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                $('#ccc-checkout-form').on('contextmenu', function(e) {
                    e.preventDefault();
                });
            });
        ");
    }
}

// Shortcode to display the form
function ccc_display_checkout_form() {
    // Get saved product info from options
    $product_id = get_option('ccc_product_id', 0);
    $title = get_option('ccc_product_title', '');
    $price = get_option('ccc_product_price', '');
    $compare_price = get_option('ccc_product_compare_price', '');

    // If any of the above are empty, fallback to actual product data
    if ($product_id) {
        if (empty($title)) $title = get_the_title($product_id);
        if (empty($price)) $price = wc_get_product($product_id)->get_price();
        if (empty($compare_price)) $compare_price = wc_get_product($product_id)->get_regular_price();
    }

    // Get saved inputs, fallback to default if none
    $inputs = get_option('ccc_form_inputs');
    if (!$inputs || !is_array($inputs)) {
        $inputs = [
            ['label' => 'الإسم الكامل', 'name' => 'full_name', 'required' => true],
            ['label' => 'رقم الهاتف', 'name' => 'phone', 'required' => true],
            ['label' => 'العنوان', 'name' => 'address', 'required' => true],
            ['label' => 'المدينة', 'name' => 'city', 'required' => true],
        ];
    }

    // Get min and max quantity from options
    $min_qty = get_option('ccc_quantity_min', 1);  // Default 1 if not set
    $max_qty = get_option('ccc_quantity_max', 99); // Default 99 if not set

    ob_start();
    ?>
    <form id="ccc-checkout-form" class="ccc-rtl" method="post">
        <h3>أدخل معلوماتك للطلب</h3>

      <div class="price-container">
        <span id="product-price" class="price-new"><?= esc_html($price) ?> <small>درهم</small></span>
        <?php if (!empty($compare_price)) : ?>
            <span class="price-old"> ~<?= esc_html($compare_price) ?>~ </span>
        <?php endif; ?>
    </div>

        <div class="product-info">
            <img src="<?php echo plugin_dir_url(__FILE__) . 'fastgrow-seeds.png'; ?>" alt="<?= esc_attr($title) ?>" />
            <span><?= esc_html($title) ?></span>
        </div>

        <h4>معلومات التوصيل</h4>

        <?php
        // Loop through inputs and output input fields
        foreach ($inputs as $input) {
            $required = !empty($input['required']) ? 'required' : '';
            // Use 'tel' type for phone field, else text
            $type = ($input['name'] === 'phone') ? 'tel' : 'text';

            // Sanitize label and name for safe HTML output
            $label = esc_attr($input['label']);
            $name = esc_attr($input['name']);

            echo "<input type='{$type}' name='{$name}' placeholder='{$label}' {$required} class='w-full border border-gray-300 rounded-md px-4 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-green-500'>";
        }
        ?>

        <!-- Quantity Selector with min/max values -->
        <div class="quantity-selector">
            <button type="button" id="ccc-minus">-</button>
            <input type="number" name="quantity" id="ccc-quantity" value="1" min="<?= esc_attr($min_qty) ?>" max="<?= esc_attr($max_qty) ?>" readonly class="w-20 border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="button" id="ccc-plus">+</button>
        </div>
        <?php
  
// Get the original price
$original_price = get_option('ccc_product_price', 0);  // Original product price
$discounted_price = $original_price * 2;  // The price after the offer

// Check if the bundle offer is enabled
$enable_bundle_offer = get_option('ccc_enable_bundle_offer', 0); // Default: Disabled

if ($enable_bundle_offer) {
    ?>
    <!-- Simple gray rectangle for the offer -->
    <div id="offer-container" class="plus-icon w-6 h-6 inline-block mr-2 cursor-pointer">
         <svg class="plus-icon w-6 h-6 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16M4 12h16"></path>
    </svg>
        <p class="text-lg text-gray-800">اشترِ 2، احصل على 1 مجانًا!</p>
    </div>

    <?php
}


// Handle the AJAX request for adding the bundle to the cart
function ccc_handle_bundle_offer() {
    // Check if the request is valid
    if (isset($_POST['quantity'])) {
        $quantity = intval($_POST['quantity']);

        // Define the bundle product (3 items for the price of 2)
        $product_id = 123; // Replace with actual product ID for the bundle
        
        // Add the bundle to the cart
        WC()->cart->add_to_cart($product_id, $quantity);

        // Return success message
        wp_send_json_success('تم إضافة العرض إلى السلة!');
    } else {
        wp_send_json_error('حدث خطأ أثناء إضافة العرض.');
    }

    wp_die();
}
add_action('wp_ajax_add_bundle_offer', 'ccc_handle_bundle_offer');
add_action('wp_ajax_nopriv_add_bundle_offer', 'ccc_handle_bundle_offer');

        ?>

        <button type="submit" id="ccc-submit" class="bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-md">أطلب الآن</button>

        <p class="delivery-note">
            من المقدّر أن يتم تسليم هذا الطلب هذا
            <span id="delivery-day-1" class="green-text"></span>
            او
            <span id="delivery-day-2" class="green-text"></span>.
        </p>

        <div id="ccc-message"></div>
    </form>
    <?php
    return ob_get_clean();
}




add_shortcode('custom_cod_checkout', 'ccc_display_checkout_form');

function enqueue_custom_styles() {
    wp_enqueue_style('custom-cod-checkout-style', plugin_dir_url(__FILE__) . '/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

// Handle AJAX form submission
function ccc_handle_order() {
    // Sanitize input
    $name = sanitize_text_field($_POST['full_name']);
    $phone = sanitize_text_field($_POST['phone']);
    $address = sanitize_text_field($_POST['address']);
    $city = sanitize_text_field($_POST['city']);
    $quantity = intval($_POST['quantity']);
    $final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : 0;

    if (!$name || !$phone || !$address || !$city || $quantity < 1) {
        wp_send_json_error('يرجى ملء جميع الحقول بشكل صحيح.');
    }

    // Create WooCommerce order
    if (!class_exists('WC_Order')) {
        wp_send_json_error('WooCommerce غير مثبت أو غير نشط.');
    }

    $product_id = 123; // Replace with your actual product ID

    $order = wc_create_order();
    for ($i = 0; $i < $quantity; $i++) {
        $order->add_product(wc_get_product($product_id), 1);
    }
    $order->set_address([
        'first_name' => $name,
        'phone' => $phone,
        'address_1' => $address,
        'city' => $city,
        'country' => 'MA', // Change country code if needed
    ], 'billing');

    // Set payment method to COD
    $order->set_payment_method('cod');
    $order->calculate_totals();
    $order->update_status('processing', 'Order created via custom COD checkout form.');

    // Return success message
    wp_send_json_success('تم استلام طلبك بنجاح. شكراً لك!');
}
add_action('wp_ajax_ccc_handle_order', 'ccc_handle_order');
add_action('wp_ajax_nopriv_ccc_handle_order', 'ccc_handle_order');
