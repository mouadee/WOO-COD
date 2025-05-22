<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Admin Settings Page for Custom COD Checkout Plugin with minimal Tailwind UI
 */

// Register admin menu
add_action('admin_menu', 'ccc_register_admin_menu');
function ccc_register_admin_menu() {
    add_menu_page(
        'Custom COD Settings',       // Page title
        'Custom COD',                // Menu title
        'manage_options',            // Capability
        'custom-cod-settings',       // Menu slug
        'ccc_admin_settings_page'    // Callback function
    );
}


// Enqueue custom admin styles only on your plugin's page
if (!function_exists('ccc_enqueue_admin_assets')) {
    add_action('admin_enqueue_scripts', 'ccc_enqueue_admin_assets');
    function ccc_enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_custom-cod-settings') return; // Only on your plugin's page

        wp_enqueue_style('custom-admin-style', plugin_dir_url(__FILE__) . 'admin.css');
    }
}

 // Enqueue the Cairo font for admin only
    wp_enqueue_style('cairo-font', 'https://fonts.googleapis.com/css2?family=Cairo&display=swap', false);

// Handle form submission and image upload
add_action('admin_post_save_ccc_settings', 'ccc_save_settings');

function ccc_save_settings() {
    // Save other settings for "معلومات المنتج"
    if (isset($_POST['ccc_product_id'])) {
        update_option('ccc_product_id', sanitize_text_field($_POST['ccc_product_id']));
        update_option('ccc_product_title', sanitize_text_field($_POST['ccc_product_title']));
        update_option('ccc_product_price', sanitize_text_field($_POST['ccc_product_price']));
        update_option('ccc_product_compare_price', sanitize_text_field($_POST['ccc_product_compare_price']));
    }

    // Save checkbox value for "تمكين عرض" (bundle offer) correctly
    if (isset($_POST['ccc_enable_bundle_offer'])) {
    $value = $_POST['ccc_enable_bundle_offer'] == '1' ? 1 : 0;
} else {
    $value = 0; // unchecked
}
update_option('ccc_enable_bundle_offer', $value);


// If checkbox not submitted (because you saved from other tabs), do nothing and keep previous value

// Save form inputs from "حقول الفورم" tab
if (isset($_POST['ccc_form_inputs']) && is_array($_POST['ccc_form_inputs'])) {
    // Sanitize inputs array if needed before saving
    update_option('ccc_form_inputs', $_POST['ccc_form_inputs']);
}
    // Handle other settings for "اعدادات"
    if (isset($_POST['ccc_disable_right_click'])) {
        update_option('ccc_disable_right_click', $_POST['ccc_disable_right_click']);
    }

    if (isset($_POST['ccc_quantity_min'])) {
        update_option('ccc_quantity_min', intval($_POST['ccc_quantity_min']));
    }

    if (isset($_POST['ccc_quantity_max'])) {
        update_option('ccc_quantity_max', intval($_POST['ccc_quantity_max']));
    }

    // Redirect with success message
    $tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'product';
    wp_redirect(add_query_arg([
        'settings-updated' => 'true',
        'tab' => $tab,
    ], admin_url('admin.php?page=custom-cod-settings')));
    exit;

}


wp_enqueue_style('custom-cod-styles', plugin_dir_url(__FILE__) . 'admin.css');
// Enqueue Tailwind CSS and admin assets only on plugin admin page
add_action('admin_enqueue_scripts', 'ccc_enqueue_admin_assets');
function ccc_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_custom-cod-settings') return;

    // Tailwind CSS CDN
    wp_enqueue_style('tailwind-css', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');

    // Google Fonts Inter
    wp_enqueue_style('google-fonts-inter', 'https://fonts.googleapis.com/css2?family=Inter&display=swap', false);

    // Inline font style for Inter
    wp_add_inline_style('tailwind-css', "
        body, input, button, select, textarea {
            font-family: 'Inter', sans-serif !important;
        }
    ");
}

// Register settings for options API
add_action('admin_init', 'ccc_register_settings');
function ccc_register_settings() {
    // Product tab group
    register_setting('ccc_product_group', 'ccc_product_id');
    register_setting('ccc_product_group', 'ccc_product_title');
    register_setting('ccc_product_group', 'ccc_product_price');
    register_setting('ccc_product_group', 'ccc_product_compare_price');
    register_setting('ccc_product_group', 'ccc_enable_bundle_offer'); // Register the setting for the bundle offer

    // Inputs tab group (for the "حقول الفورم" tab)
    register_setting('ccc_inputs_group', 'ccc_form_inputs');

    // Options tab group (for the "اعدادات" tab)
    register_setting('ccc_options_group', 'ccc_disable_right_click');
    register_setting('ccc_options_group', 'ccc_quantity_min');
    register_setting('ccc_options_group', 'ccc_quantity_max');
}

// Helper to get active tab classes
function ccc_get_active_tab($tab) {
    $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'product';
    return $current_tab === $tab ? 'border-b-4 border-gray-500 text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600';
}

// Main admin page
function ccc_admin_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'product';

    // Display success or error message after saving
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        echo '<div class="updated notice is-dismissible"><p>تم حفظ الإعدادات بنجاح!</p></div>';
    } elseif (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'false') {
        echo '<div class="error notice is-dismissible"><p>Error saving settings.</p></div>';
    }
    ?> 
    
    <div id="#custom-cod-settings" class="wrap max-w-5xl mx-auto p-6 bg-white rounded-md shadow-md all_wrap_plugin">
        <h1 class="text-3xl font-bold mb-6 text-green-700">WOO COD فورم</h1>

        <!-- Tabs -->
        <nav class="flex border-b border-gray-200 mb-8 space-x-6 first_link_tab shadow-none">
            <a href="?page=custom-cod-settings&tab=product"
               class="<?= ccc_get_active_tab('product') ?> pb-2 shadow-none">
                معلومات المنتج
            </a>
            <a href="?page=custom-cod-settings&tab=inputs"
               class="<?= ccc_get_active_tab('inputs') ?> pb-2 first_link_tab shadow-none">
                حقول الفورم
            </a>
            <a href="?page=custom-cod-settings&tab=options"
               class="<?= ccc_get_active_tab('options') ?> pb-2 first_link_tab shadow-none">
                اعدادات
            </a>
        </nav>

        <form method="post" action="admin-post.php" class="space-y-6 the-whole-form">
            <input type="hidden" name="current_tab" value="<?= esc_attr($active_tab) ?>">
            <?php
            // Use settings fields for the active tab
            if ($active_tab === 'product') {
                settings_fields('ccc_product_group');
            } elseif ($active_tab === 'inputs') {
                settings_fields('ccc_inputs_group');   // Ensure this is the correct group for "حقول الفورم"
            } elseif ($active_tab === 'options') {
                settings_fields('ccc_options_group');  // Ensure this is the correct group for "اعدادات"
            }
            // Display form fields
            do_settings_sections('custom-cod-settings');

            // Call your tab callback functions here
            if ($active_tab === 'product') {
                ccc_product_tab();
            } elseif ($active_tab === 'inputs') {
                ccc_inputs_tab();
            } elseif ($active_tab === 'options') {
                ccc_options_tab();
            }

?>

            <?php
            submit_button('حفظ', 'bg-black hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-md save_buttn');
            ?>
            <input type="hidden" name="action" value="save_ccc_settings"> <!-- Action hook -->
        </form>

    </div>
    <?php
}


// Product tab content
function ccc_product_tab() {
    $product_id = get_option('ccc_product_id', 0);
    $title = get_option('ccc_product_title', '');
    $price = get_option('ccc_product_price', '');
    $compare_price = get_option('ccc_product_compare_price', '');

    $products = wc_get_products(['limit' => 100]);

    // Get the current setting for enabling the bundle offer
    $enable_bundle_offer = (int) get_option('ccc_enable_bundle_offer', 0);


    // Get the current product image ID and URL
    $product_image_id = get_post_thumbnail_id($product_id); // Get image ID
    $product_image_url = wp_get_attachment_url($product_image_id); // Get image URL
    ?>    

    <h2 class="text-xl font-semibold mb-4 text-green-700">إختر منتج</h2>
    <select name="ccc_product_id" onchange="this.form.submit()"
            class="custom-input mb-6">
        <option value="">Select a product</option>
        <?php foreach ($products as $product):
            $selected = $product->get_id() == $product_id ? 'selected' : '';
            ?>
            <option value="<?= esc_attr($product->get_id()) ?>" <?= $selected ?>>
                <?= esc_html($product->get_name()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($product_id): ?>
        <?php
        if (!$title) $title = get_the_title($product_id);
        if (!$price) $price = wc_get_product($product_id)->get_price();
        if (!$compare_price) $compare_price = wc_get_product($product_id)->get_regular_price();
        ?>

        <h2 class="text-xl font-semibold mb-4 text-green-700">تعديل معلومات المنتج</h2>

        <label class="block mb-2 font-semibold text-green-600">عنوان المنتج</label>
        <input type="text" name="ccc_product_title" value="<?= esc_attr($title) ?>"
               class="custom-input mb-4">

        <label class="block mb-2 font-semibold text-green-600">السعر</label>
        <input type="text" name="ccc_product_price" value="<?= esc_attr($price) ?>"
               class="custom-input mb-4">

        <label class="block mb-2 font-semibold text-green-600">السعر المخفض</label>
        <input type="text" name="ccc_product_compare_price" value="<?= esc_attr($compare_price) ?>"
               class="custom-input mb-4">

        <!-- Custom Product Image Upload Field -->
<label class="block mb-2 font-semibold text-green-600">صورة المنتج</label>

<?php
$enable_bundle_offer = get_option('ccc_enable_bundle_offer', 0);
?>
<label class="inline-flex items-center mt-4">
    <input type="checkbox" name="ccc_enable_bundle_offer" value="1" <?php checked(1, $enable_bundle_offer); ?>>
    <span class="ml-2 text-green-900 font-medium">تمكين عرض "اشترِ 2، احصل على 1 مجانًا"</span>
</label>

<!-- Display Product Image -->
<?php if ($product_image_url): ?>
    <div class="mt-4">
        <img src="<?= esc_url($product_image_url) ?>" alt="<?= esc_attr($title) ?>" class="w-32 h-32 object-contain border border-gray-200 rounded-md">
    </div>
<?php endif; ?>
    <?php endif; ?>
<?php }




// Inputs tab content
function ccc_inputs_tab() {
    $inputs = get_option('ccc_form_inputs');

    if (!$inputs || !is_array($inputs)) {
        $inputs = [
            ['label' => 'الإسم الكامل', 'name' => 'full_name', 'required' => true],
            ['label' => 'رقم الهاتف', 'name' => 'phone', 'required' => true],
            ['label' => 'العنوان', 'name' => 'address', 'required' => true],
            ['label' => 'المدينة', 'name' => 'city', 'required' => true],
        ];
    }

    $inputs_json = json_encode($inputs);
    ?>

    <h2 class="text-xl font-semibold mb-6 text-green-700">إدارة مدخلات النموذج</h2>

    <div id="ccc-inputs-app" class="bg-gray-50 rounded-md p-6 shadow max-w-4xl div_last_btn">
        <table class="min-w-full divide-y divide-gray-300 bg-white rounded-md overflow-hidden">
            <thead class="bg-gray-200 text-green-800 font-semibold">
                <tr>
                    <th class="px-6 py-3 text-right w-2/5">الإسم</th>
                    <th class="px-6 py-3 text-right w-2/5">اسم الحقل</th>
                    <th class="px-6 py-3 text-center w-1/5">مطلوب</th>
                    <th class="px-6 py-3 text-center w-1/5">اعدادات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300 tbody_form_inputs">
                <tr v-for="(input, index) in inputs" :key="index" class="hover:bg-gray-100">
                    <td class="px-6 py-4">
                        <input type="text" v-model="input.label" 
                               :name="'ccc_form_inputs[' + index + '][label]'" 
                               required 
                               placeholder="Label"
                               class="w-full rounded-full border border-gray-300 px-4 py-1 text-gray-900 font-medium focus:outline-none focus:ring-2 custom-input-style">
                    </td>
                    <td class="px-6 py-4">
                        <input type="text" v-model="input.name" 
                               :name="'ccc_form_inputs[' + index + '][name]'" 
                               required 
                               placeholder="Field Name"
                               class="w-full rounded-full border border-gray-300 px-4 py-1 text-gray-900 font-medium focus:outline-none focus:ring-2 custom-input-style">
                    </td>
                    <td class="px-6 py-4 text-center">
                        <input type="checkbox" v-model="input.required" 
                               :name="'ccc_form_inputs[' + index + '][required]'"
                               class="w-5 h-5 cursor-pointer text-black-600 focus:ring-gray-500">
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" @click="removeInput(index)" 
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded focus:outline-none">
                            حذف
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" @click="addInput" 
                class="mt-5 bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded focus:outline-none">
            + أضف حقل جديد
        </button>
    </div>

    <!-- Load Vue.js CDN only for inputs tab -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>

    <script>
    new Vue({
        el: '#ccc-inputs-app',
        data: {
            inputs: <?php echo $inputs_json; ?>
        },
        methods: {
            addInput() {
                this.inputs.push({ label: '', name: '', required: false });
            },
            removeInput(index) {
                if (confirm('هل أنت متأكد من حذف هذا الحقل؟')) {
                    this.inputs.splice(index, 1);
                }
            }
        }
    });
    </script>

    <?php
}

// Options tab content
function ccc_options_tab() {
    $disable_right_click = get_option('ccc_disable_right_click', false);
    $min_qty = get_option('ccc_quantity_min', 1);  // Default 1
    $max_qty = get_option('ccc_quantity_max', 99); // Default 99
    ?>

    <h2 class="text-xl font-semibold mb-4 text-green-700">خيارات النموذج</h2>
    <label class="inline-flex items-center cursor-pointer">
        <input type="checkbox" name="ccc_disable_right_click" value="1" <?php checked(1, $disable_right_click); ?>
               class="form-checkbox h-5 w-5 text-green-600">
        <span class="ml-2 text-green-900 font-medium">تعطيل النقر بزر الفأرة الأيمن على الفورم</span>
    </label>

    <div class="mt-6">
    <label class="block text-green-700 font-semibold mb-2" for="ccc_quantity_min">الحد الأدنى للكمية</label>
    <div class="relative flex items-center break">
        <button type="button" class="quantity-btn left-btn" onclick="decrement('ccc_quantity_min')">-</button>
        <input type="number" id="ccc_quantity_min" name="ccc_quantity_min" value="<?= esc_attr($min_qty) ?>" min="1" class="quantity-input">
        <button type="button" class="quantity-btn right-btn" onclick="increment('ccc_quantity_min')">+</button>
    </div>
</div>

<div class="mt-4">
    <label class="block text-green-700 font-semibold mb-2" for="ccc_quantity_max">الكمية القصوى
</label>
    <div class="relative flex items-center break">
        <button type="button" class="quantity-btn left-btn" onclick="decrement('ccc_quantity_max')">-</button>
        <input type="number" id="ccc_quantity_max" name="ccc_quantity_max" value="<?= esc_attr($max_qty) ?>" min="1" class="quantity-input">
        <button type="button" class="quantity-btn right-btn" onclick="increment('ccc_quantity_max')">+</button>
    </div>
</div>

<script>
    function increment(fieldId) {
        let inputField = document.getElementById(fieldId);
        inputField.value = parseInt(inputField.value) + 1;
    }

    function decrement(fieldId) {
        let inputField = document.getElementById(fieldId);
        if (parseInt(inputField.value) > 1) {
            inputField.value = parseInt(inputField.value) - 1;
        }
    }
</script>



    <?php
}
?>