<?php
/**
 * Admin Settings Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Admin {
    
    /**
     * Single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_gsd_get_category_products', array($this, 'ajax_get_category_products'));
        add_action('wp_ajax_gsd_save_product_shipping', array($this, 'ajax_save_product_shipping'));
        add_action('wp_ajax_gsd_inspect_product_meta', array($this, 'ajax_inspect_product_meta'));
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on our plugin pages
        if (strpos($hook, 'garden-sheds-delivery') === false) {
            return;
        }
        
        // Enqueue admin CSS
        wp_enqueue_style(
            'gsd-admin-css',
            GSD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            GSD_VERSION
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Shed Delivery', 'garden-sheds-delivery'),
            __('Shed Delivery', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery',
            array($this, 'settings_page'),
            'dashicons-store',
            56
        );
        
        // Add submenu for depot locations
        add_submenu_page(
            'garden-sheds-delivery',
            __('Depot Locations', 'garden-sheds-delivery'),
            __('Depot Locations', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery-depots',
            array($this, 'depots_page')
        );
        
        // Add submenu for documentation
        add_submenu_page(
            'garden-sheds-delivery',
            __('Documentation', 'garden-sheds-delivery'),
            __('Documentation', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery-docs',
            array($this, 'docs_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('gsd_settings', 'gsd_courier_companies');
        register_setting('gsd_settings', 'gsd_home_delivery_categories');
        register_setting('gsd_settings', 'gsd_default_home_delivery_cost');
        register_setting('gsd_settings', 'gsd_show_contact_for_delivery');
        register_setting('gsd_settings', 'gsd_express_delivery_categories');
        register_setting('gsd_settings', 'gsd_default_express_delivery_cost');
        register_setting('gsd_settings', 'gsd_contact_delivery_categories');
        register_setting('gsd_settings', 'gsd_main_freight_categories');
        register_setting('gsd_settings', 'gsd_pbt_categories');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['gsd_save_settings']) && check_admin_referer('gsd_save_settings')) {
            $this->save_delivery_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $selected_home_delivery = get_option('gsd_home_delivery_categories', array());
        $selected_express_delivery = get_option('gsd_express_delivery_categories', array());
        $selected_contact_delivery = get_option('gsd_contact_delivery_categories', array());
        $selected_main_freight = get_option('gsd_main_freight_categories', array());
        $selected_pbt = get_option('gsd_pbt_categories', array());
        
        // Ensure all selected values are arrays
        $selected_home_delivery = is_array($selected_home_delivery) ? $selected_home_delivery : array();
        $selected_express_delivery = is_array($selected_express_delivery) ? $selected_express_delivery : array();
        $selected_contact_delivery = is_array($selected_contact_delivery) ? $selected_contact_delivery : array();
        $selected_main_freight = is_array($selected_main_freight) ? $selected_main_freight : array();
        $selected_pbt = is_array($selected_pbt) ? $selected_pbt : array();
        
        $default_cost = get_option('gsd_default_home_delivery_cost', '150');
        $default_express_cost = get_option('gsd_default_express_delivery_cost', '15');
        
        // Get all product categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Shed Delivery Settings', 'garden-sheds-delivery'); ?></h1>
            
            <!-- Auto-save notification -->
            <div id="gsd-autosave-notification" style="display: none; position: fixed; top: 32px; right: 20px; z-index: 9999; background: #00a32a; color: white; padding: 12px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                <strong>✓ Settings saved</strong>
            </div>
            
            <!-- Auto-save error notification -->
            <div id="gsd-autosave-error" style="display: none; position: fixed; top: 32px; right: 20px; z-index: 9999; background: #dc3232; color: white; padding: 12px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                <strong>✗ <span id="gsd-error-message">Save failed</span></strong>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_settings'); ?>
                
                <h2><?php echo esc_html__('Delivery Options by Category', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Configure delivery options for each product category.', 'garden-sheds-delivery'); ?></p>
                
                <div class="gsd-mixed-state-notice">
                    <strong><?php echo esc_html__('About Mixed States:', 'garden-sheds-delivery'); ?></strong>
                    <p style="margin: 5px 0 0 0;">
                        <?php 
                        printf(
                            esc_html__('When you expand a category and uncheck individual products, the category checkbox will show a dash (–) instead of a checkmark. This indicates that some products in that category have the shipping option enabled, while others don\'t. The entire row will be highlighted in yellow to make this clear. Changes to individual products are auto-saved immediately.', 'garden-sheds-delivery')
                        );
                        ?>
                    </p>
                </div>
                
                <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                <table class="wp-list-table widefat fixed striped gsd-category-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th><?php echo esc_html__('Category', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Home Delivery', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Small Items', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Might be able to offer home delivery', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Main Freight', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('PBT', 'garden-sheds-delivery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category) : ?>
                        <tr class="gsd-category-row" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                            <td style="text-align: center;">
                                <button type="button" class="gsd-toggle-products button-link" data-category-id="<?php echo esc_attr($category->term_id); ?>" title="<?php echo esc_attr__('Show/hide products', 'garden-sheds-delivery'); ?>">
                                    <span class="dashicons dashicons-arrow-right"></span>
                                </button>
                            </td>
                            <td><strong><?php echo esc_html($category->name); ?></strong></td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_home_delivery_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_home_delivery)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_express_delivery_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_express_delivery)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_contact_delivery_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_contact_delivery)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_main_freight_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_main_freight)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_pbt_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_pbt)); ?> />
                            </td>
                        </tr>
                        <tr class="gsd-products-row" id="gsd-products-<?php echo esc_attr($category->term_id); ?>" style="display: none;">
                            <td colspan="7" style="padding: 0;">
                                <div class="gsd-products-container">
                                    <div class="gsd-loading" style="padding: 20px; text-align: center;">
                                        <span class="spinner is-active" style="float: none;"></span>
                                        <?php echo esc_html__('Loading products...', 'garden-sheds-delivery'); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                    <p><?php echo esc_html__('No product categories found.', 'garden-sheds-delivery'); ?></p>
                <?php endif; ?>
                
                <hr style="margin: 30px 0;" />
                
                <h2><?php echo esc_html__('Default Costs', 'garden-sheds-delivery'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Default Home Delivery Cost', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="gsd_default_home_delivery_cost" 
                                   value="<?php echo esc_attr($default_cost); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('Default cost for home delivery. This can be overridden per-product.', 'garden-sheds-delivery'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Default Small Items Delivery Cost', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="gsd_default_express_delivery_cost" 
                                   value="<?php echo esc_attr($default_express_cost); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('Default cost for small items delivery. This can be overridden per-product.', 'garden-sheds-delivery'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="gsd_save_settings" class="button-primary" value="<?php echo esc_attr__('Save Settings', 'garden-sheds-delivery'); ?>" />
                </p>
            </form>
            
            <!-- DEBUG PANEL -->
            <div id="gsd-debug-panel" style="margin-top: 40px; padding: 20px; background: #f9f9f9; border: 2px solid #d63638; border-radius: 8px;">
                <h2 style="margin-top: 0; color: #d63638;">🐛 Debug Panel</h2>
                <p style="color: #666; margin-bottom: 20px;">This panel helps diagnose why settings aren't being saved.</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Database State -->
                    <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd;">
                        <h3 style="margin-top: 0;">📊 Current Database State</h3>
                        <div style="font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto;">
                            <strong>Home Delivery Categories:</strong><br>
                            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
                                $debug_home = get_option('gsd_home_delivery_categories', array());
                                echo esc_html(print_r($debug_home, true)); 
                            ?></pre>
                            
                            <strong>Express Delivery Categories:</strong><br>
                            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
                                $debug_express = get_option('gsd_express_delivery_categories', array());
                                echo esc_html(print_r($debug_express, true)); 
                            ?></pre>
                            
                            <strong>Contact Delivery Categories:</strong><br>
                            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
                                $debug_contact = get_option('gsd_contact_delivery_categories', array());
                                echo esc_html(print_r($debug_contact, true)); 
                            ?></pre>
                            
                            <strong>Main Freight Categories:</strong><br>
                            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
                                $debug_mainfreight = get_option('gsd_main_freight_categories', array());
                                echo esc_html(print_r($debug_mainfreight, true)); 
                            ?></pre>
                            
                            <strong>PBT Categories:</strong><br>
                            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
                                $debug_pbt = get_option('gsd_pbt_categories', array());
                                echo esc_html(print_r($debug_pbt, true)); 
                            ?></pre>
                        </div>
                    </div>
                    
                    <!-- AJAX Activity Log -->
                    <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd;">
                        <h3 style="margin-top: 0;">📡 AJAX Activity Log</h3>
                        <div id="gsd-ajax-log" style="font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; background: #000; color: #0f0; padding: 10px; border-radius: 3px;">
                            <div style="color: #888;">Waiting for activity...</div>
                        </div>
                        <button type="button" id="gsd-clear-log" class="button" style="margin-top: 10px;">Clear Log</button>
                    </div>
                </div>
                
                <!-- Product Meta Inspector -->
                <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; margin-top: 20px;">
                    <h3 style="margin-top: 0;">🔍 Product Meta Inspector</h3>
                    <p>Enter a product ID to inspect its delivery settings:</p>
                    <input type="number" id="gsd-product-id-input" placeholder="Product ID" style="width: 150px; margin-right: 10px;">
                    <button type="button" id="gsd-inspect-product" class="button">Inspect Product</button>
                    <div id="gsd-product-meta-result" style="margin-top: 15px; font-family: monospace; font-size: 12px;"></div>
                </div>
                
                <!-- Test Save Button -->
                <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; margin-top: 20px;">
                    <h3 style="margin-top: 0;">🧪 Test Save Functionality</h3>
                    <?php
                    // Get the first available product for testing
                    $test_products = get_posts(array(
                        'post_type' => 'product',
                        'posts_per_page' => 1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'fields' => 'ids'
                    ));
                    $test_product_id = !empty($test_products) ? absint($test_products[0]) : 0;
                    ?>
                    <button type="button" id="gsd-test-save" class="button button-primary" <?php echo ($test_product_id === 0) ? 'disabled aria-describedby="gsd-test-save-warning"' : ''; ?>>Test AJAX Save</button>
                    <?php if ($test_product_id > 0): ?>
                        <p style="color: #666; margin: 10px 0 0 0; font-size: 12px;">Using product ID <?php echo absint($test_product_id); ?> for testing</p>
                    <?php else: ?>
                        <p id="gsd-test-save-warning" style="color: #d63638; margin: 10px 0 0 0; font-size: 12px;">⚠️ No products found. Please create a product first to test the save functionality.</p>
                    <?php endif; ?>
                    <div id="gsd-test-result" style="margin-top: 15px; font-family: monospace; font-size: 12px;"></div>
                </div>
            </div>
        </div>
        
        <style>
        .gsd-toggle-products {
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
            color: #2271b1;
        }
        .gsd-toggle-products:hover {
            color: #135e96;
        }
        .gsd-toggle-products .dashicons {
            transition: transform 0.2s;
        }
        .gsd-toggle-products.expanded .dashicons {
            transform: rotate(90deg);
        }
        .gsd-products-container {
            background: #f9f9f9;
            border-top: 1px solid #ddd;
        }
        .gsd-products-table {
            width: 100%;
            margin: 0;
        }
        .gsd-products-table th,
        .gsd-products-table td {
            padding: 8px;
            text-align: left;
        }
        .gsd-products-table thead {
            background: #e5e5e5;
        }
        .gsd-products-table tbody tr:nth-child(even) {
            background: #fff;
        }
        .gsd-product-name {
            font-weight: 500;
        }
        .gsd-product-save {
            text-align: right;
            padding: 10px 15px;
            background: #fff;
            border-top: 1px solid #ddd;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle product display
            $('.gsd-toggle-products').on('click', function() {
                var button = $(this);
                var categoryId = button.data('category-id');
                var productsRow = $('#gsd-products-' + categoryId);
                var isExpanded = button.hasClass('expanded');
                
                if (isExpanded) {
                    // Collapse
                    productsRow.slideUp(200);
                    button.removeClass('expanded');
                } else {
                    // Expand
                    button.addClass('expanded');
                    productsRow.slideDown(200);
                    
                    // Load products if not already loaded
                    if (!productsRow.hasClass('loaded')) {
                        loadCategoryProducts(categoryId);
                    }
                }
            });
            
            // Load products via AJAX
            function loadCategoryProducts(categoryId) {
                var container = $('#gsd-products-' + categoryId + ' .gsd-products-container');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gsd_get_category_products',
                        category_id: categoryId,
                        nonce: '<?php echo wp_create_nonce('gsd_get_category_products'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            container.html(response.data.html);
                            $('#gsd-products-' + categoryId).addClass('loaded');
                            // Update category checkbox states based on loaded products
                            updateCategoryCheckboxStates(categoryId);
                        } else {
                            container.html('<div class="notice notice-error" style="margin: 10px;"><p>' + 
                                (response.data.message || '<?php echo esc_js(__('Error loading products', 'garden-sheds-delivery')); ?>') + 
                                '</p></div>');
                        }
                    },
                    error: function() {
                        container.html('<div class="notice notice-error" style="margin: 10px;"><p><?php echo esc_js(__('Error loading products', 'garden-sheds-delivery')); ?></p></div>');
                    }
                });
            }
            
            // Auto-save individual product when checkbox changes
            var saveTimeouts = {}; // Track timeouts per category
            var AUTO_SAVE_DEBOUNCE_MS = 500;
            
            function autoSaveProductSettings(categoryId) {
                // Clear any pending save for this category
                if (saveTimeouts[categoryId]) {
                    clearTimeout(saveTimeouts[categoryId]);
                }
                
                // Debounce saves to avoid excessive AJAX calls
                saveTimeouts[categoryId] = setTimeout(function() {
                    var container = $('#gsd-products-' + categoryId + ' .gsd-products-container');
                    var productSettings = [];
                    
                    container.find('.gsd-product-row').each(function() {
                        var row = $(this);
                        var productId = row.data('product-id');
                        
                        productSettings.push({
                            product_id: productId,
                            home_delivery: row.find('.gsd-product-home-delivery').is(':checked') ? 1 : 0,
                            express_delivery: row.find('.gsd-product-express-delivery').is(':checked') ? 1 : 0,
                            contact_delivery: row.find('.gsd-product-contact-delivery').is(':checked') ? 1 : 0
                        });
                    });
                    
                    console.log('Saving product settings for category ' + categoryId + ':', productSettings);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'gsd_save_product_shipping',
                            products: productSettings,
                            nonce: '<?php echo wp_create_nonce('gsd_save_product_shipping'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success notification
                                var notification = $('#gsd-autosave-notification');
                                notification.fadeIn(200);
                                setTimeout(function() {
                                    notification.fadeOut(200);
                                }, 2000);
                                console.log('Product settings saved successfully for category ' + categoryId);
                                
                                // Log any warnings if present
                                if (response.data && response.data.errors && response.data.errors.length > 0) {
                                    console.warn('Save completed with warnings:', response.data.errors);
                                }
                            } else {
                                // Show error notification
                                var errorNotification = $('#gsd-autosave-error');
                                var errorMessage = response.data && response.data.message ? response.data.message : 'Unknown error';
                                $('#gsd-error-message').text(errorMessage);
                                errorNotification.fadeIn(200);
                                setTimeout(function() {
                                    errorNotification.fadeOut(200);
                                }, 4000);
                                console.error('Error saving product settings:', errorMessage);
                                if (response.data && response.data.errors) {
                                    console.error('Detailed errors:', response.data.errors);
                                }
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // Show error notification
                            var errorNotification = $('#gsd-autosave-error');
                            $('#gsd-error-message').text('Network error: ' + textStatus);
                            errorNotification.fadeIn(200);
                            setTimeout(function() {
                                errorNotification.fadeOut(200);
                            }, 4000);
                            console.error('AJAX error saving product settings:', textStatus, errorThrown);
                            console.error('Response:', jqXHR.responseText);
                        }
                    });
                    
                    // Clean up timeout reference
                    delete saveTimeouts[categoryId];
                }, AUTO_SAVE_DEBOUNCE_MS);
            }
            
            // Update category checkbox states based on product states
            function updateCategoryCheckboxStates(categoryId) {
                var categoryRow = $('.gsd-category-row[data-category-id="' + categoryId + '"]');
                var productsContainer = $('#gsd-products-' + categoryId + ' .gsd-products-container');
                
                if (!productsContainer.length || !categoryRow.length) {
                    return;
                }
                
                // Get all product checkboxes
                var homeCheckboxes = productsContainer.find('.gsd-product-home-delivery');
                var expressCheckboxes = productsContainer.find('.gsd-product-express-delivery');
                var contactCheckboxes = productsContainer.find('.gsd-product-contact-delivery');
                
                // Get category checkboxes
                var categoryHomeCheckbox = categoryRow.find('input[name="gsd_home_delivery_categories[]"]')[0];
                var categoryExpressCheckbox = categoryRow.find('input[name="gsd_express_delivery_categories[]"]')[0];
                var categoryContactCheckbox = categoryRow.find('input[name="gsd_contact_delivery_categories[]"]')[0];
                
                // Update each category checkbox based on product states
                updateCheckboxState(categoryHomeCheckbox, homeCheckboxes);
                updateCheckboxState(categoryExpressCheckbox, expressCheckboxes);
                updateCheckboxState(categoryContactCheckbox, contactCheckboxes);
                
                // Add visual indicator if any checkbox is indeterminate
                var hasIndeterminate = (categoryHomeCheckbox && categoryHomeCheckbox.indeterminate) ||
                                      (categoryExpressCheckbox && categoryExpressCheckbox.indeterminate) ||
                                      (categoryContactCheckbox && categoryContactCheckbox.indeterminate);
                
                if (hasIndeterminate) {
                    categoryRow.addClass('has-indeterminate');
                } else {
                    categoryRow.removeClass('has-indeterminate');
                }
            }
            
            // Helper to set checkbox to checked, unchecked, or indeterminate
            function updateCheckboxState(categoryCheckbox, productCheckboxes) {
                if (!categoryCheckbox || productCheckboxes.length === 0) {
                    return;
                }
                
                var checkedCount = 0;
                productCheckboxes.each(function() {
                    if ($(this).is(':checked')) {
                        checkedCount++;
                    }
                });
                
                if (checkedCount === 0) {
                    // None checked
                    categoryCheckbox.checked = false;
                    categoryCheckbox.indeterminate = false;
                } else if (checkedCount === productCheckboxes.length) {
                    // All checked
                    categoryCheckbox.checked = true;
                    categoryCheckbox.indeterminate = false;
                } else {
                    // Some checked (indeterminate)
                    categoryCheckbox.checked = false;
                    categoryCheckbox.indeterminate = true;
                }
            }
            
            // Handle category checkbox changes - update all product checkboxes when category checkbox is clicked
            $(document).on('change', '.gsd-category-row input[type="checkbox"]', function() {
                var checkbox = $(this);
                var categoryRow = checkbox.closest('.gsd-category-row');
                var categoryId = categoryRow.data('category-id');
                var productsRow = $('#gsd-products-' + categoryId);
                
                // Only update products if they are loaded and expanded
                if (!productsRow.hasClass('loaded')) {
                    return;
                }
                
                var isChecked = checkbox.is(':checked');
                var productsContainer = productsRow.find('.gsd-products-container');
                
                // Determine which type of checkbox this is
                var checkboxName = checkbox.attr('name');
                var productCheckboxClass = '';
                
                if (checkboxName && checkboxName.indexOf('gsd_home_delivery_categories') > -1) {
                    productCheckboxClass = '.gsd-product-home-delivery';
                } else if (checkboxName && checkboxName.indexOf('gsd_express_delivery_categories') > -1) {
                    productCheckboxClass = '.gsd-product-express-delivery';
                } else if (checkboxName && checkboxName.indexOf('gsd_contact_delivery_categories') > -1) {
                    productCheckboxClass = '.gsd-product-contact-delivery';
                }
                
                if (productCheckboxClass) {
                    // Update all product checkboxes of this type
                    productsContainer.find(productCheckboxClass).prop('checked', isChecked);
                    // Clear indeterminate state
                    checkbox[0].indeterminate = false;
                    
                    // Auto-save the updated product settings
                    autoSaveProductSettings(categoryId);
                }
            });
            
            // Handle product checkbox changes - update category checkbox state when product checkboxes are clicked
            $(document).on('change', '.gsd-product-home-delivery, .gsd-product-express-delivery, .gsd-product-contact-delivery', function() {
                var checkbox = $(this);
                var productRow = checkbox.closest('.gsd-product-row');
                var productsContainer = productRow.closest('.gsd-products-container');
                var productsRowDiv = productsContainer.closest('.gsd-products-row');
                
                // Extract category ID from the products row ID (format: gsd-products-{categoryId})
                var productsRowId = productsRowDiv.attr('id');
                if (!productsRowId) {
                    return;
                }
                var categoryId = productsRowId.replace('gsd-products-', '');
                
                // Update the category checkbox state based on all product checkboxes
                updateCategoryCheckboxStates(categoryId);
                
                // Auto-save the product settings
                autoSaveProductSettings(categoryId);
            });
            
            // ==================== DEBUG PANEL FUNCTIONALITY ====================
            
            var ajaxLog = [];
            
            function logToDebug(message, type) {
                type = type || 'info';
                var timestamp = new Date().toLocaleTimeString();
                var color = type === 'error' ? '#f00' : type === 'success' ? '#0f0' : type === 'warning' ? '#ff0' : '#0ff';
                
                ajaxLog.push({
                    time: timestamp,
                    message: message,
                    type: type
                });
                
                var logDiv = $('#gsd-ajax-log');
                var logEntry = $('<div>').css('color', color).html('[' + timestamp + '] ' + message);
                logDiv.append(logEntry);
                logDiv.scrollTop(logDiv[0].scrollHeight);
            }
            
            // Clear log button
            $('#gsd-clear-log').on('click', function() {
                $('#gsd-ajax-log').html('<div style="color: #888;">Log cleared...</div>');
                ajaxLog = [];
            });
            
            // Product meta inspector
            $('#gsd-inspect-product').on('click', function() {
                var productId = $('#gsd-product-id-input').val();
                if (!productId) {
                    alert('Please enter a product ID');
                    return;
                }
                
                $('#gsd-product-meta-result').html('<span class="spinner is-active" style="float: none;"></span> Loading...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gsd_inspect_product_meta',
                        product_id: productId,
                        nonce: '<?php echo wp_create_nonce('gsd_inspect_product_meta'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<div style="background: #f0f0f0; padding: 10px; border-radius: 3px;">';
                            html += '<strong>Product #' + productId + ' - ' + response.data.title + '</strong><br><br>';
                            html += '<strong>_gsd_home_delivery_available:</strong> ' + (response.data.home_delivery || '(empty)') + '<br>';
                            html += '<strong>_gsd_express_delivery_available:</strong> ' + (response.data.express_delivery || '(empty)') + '<br>';
                            html += '<strong>_gsd_contact_for_delivery:</strong> ' + (response.data.contact_delivery || '(empty)') + '<br>';
                            html += '<br><strong>Categories:</strong> ' + (response.data.categories || 'None') + '<br>';
                            html += '</div>';
                            $('#gsd-product-meta-result').html(html);
                        } else {
                            $('#gsd-product-meta-result').html('<div style="background: #fee; padding: 10px; border-radius: 3px; color: #c00;">Error: ' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#gsd-product-meta-result').html('<div style="background: #fee; padding: 10px; border-radius: 3px; color: #c00;">AJAX error occurred</div>');
                    }
                });
            });
            
            // Test save button
            $('#gsd-test-save').on('click', function() {
                $('#gsd-test-result').html('<span class="spinner is-active" style="float: none;"></span> Testing save...');
                logToDebug('Testing AJAX save with dummy data...', 'info');
                
                var testData = [{
                    product_id: <?php echo intval($test_product_id); ?>,
                    home_delivery: 1,
                    express_delivery: 0,
                    contact_delivery: 0
                }];
                
                logToDebug('Test data: ' + JSON.stringify(testData), 'info');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gsd_save_product_shipping',
                        products: testData,
                        nonce: '<?php echo wp_create_nonce('gsd_save_product_shipping'); ?>'
                    },
                    success: function(response) {
                        logToDebug('Save response: ' + JSON.stringify(response), response.success ? 'success' : 'error');
                        if (response.success) {
                            $('#gsd-test-result').html('<div style="background: #efe; padding: 10px; border-radius: 3px; color: #060;">✓ Test save successful! Response: ' + response.data.message + '</div>');
                        } else {
                            $('#gsd-test-result').html('<div style="background: #fee; padding: 10px; border-radius: 3px; color: #c00;">✗ Test save failed: ' + response.data.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        logToDebug('AJAX error: ' + error, 'error');
                        $('#gsd-test-result').html('<div style="background: #fee; padding: 10px; border-radius: 3px; color: #c00;">✗ AJAX error: ' + error + '</div>');
                    }
                });
            });
            
            // Intercept GSD AJAX calls to log them (using jQuery events instead of overriding $.ajax)
            $(document).ajaxSend(function(event, jqxhr, settings) {
                // Only log GSD-related AJAX calls
                if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('action=gsd_') !== -1) {
                    var actionMatch = settings.data.match(/action=gsd_([^&]+)/);
                    if (actionMatch) {
                        logToDebug('→ AJAX Request: gsd_' + actionMatch[1], 'info');
                    }
                }
            });
            
            $(document).ajaxComplete(function(event, jqxhr, settings) {
                // Only log GSD-related AJAX calls
                if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('action=gsd_') !== -1) {
                    var actionMatch = settings.data.match(/action=gsd_([^&]+)/);
                    if (actionMatch) {
                        try {
                            var response = JSON.parse(jqxhr.responseText);
                            if (response.success) {
                                logToDebug('✓ gsd_' + actionMatch[1] + ' succeeded', 'success');
                            } else {
                                logToDebug('✗ gsd_' + actionMatch[1] + ' failed: ' + (response.data ? response.data.message : 'Unknown error'), 'error');
                            }
                        } catch (e) {
                            // Response is not JSON, ignore
                        }
                    }
                }
            });
            
            // Initial log
            logToDebug('Debug panel initialized', 'success');
        });
        </script>
        <?php
    }

    /**
     * Depot locations page
     */
    public function depots_page() {
        if (isset($_POST['gsd_save_depots']) && check_admin_referer('gsd_save_depots')) {
            $this->save_couriers();
            echo '<div class="notice notice-success"><p>' . esc_html__('Depot locations saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $couriers = GSD_Courier::get_couriers();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Courier Depot Locations', 'garden-sheds-delivery'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_depots'); ?>
                
                <h2><?php echo esc_html__('Courier Companies and Depots', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Manage courier companies and their depot locations.', 'garden-sheds-delivery'); ?></p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php echo esc_html__('Enabled', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Courier Name', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Slug', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Depot Locations', 'garden-sheds-delivery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($couriers as $slug => $courier) : ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php 
                                $enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
                                ?>
                                <input type="checkbox" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][enabled]" 
                                       value="1"
                                       <?php checked($enabled, true); ?> />
                            </td>
                            <td>
                                <input type="text" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][name]" 
                                       value="<?php echo esc_attr($courier['name']); ?>" 
                                       class="regular-text" />
                            </td>
                            <td>
                                <code><?php echo esc_html($slug); ?></code>
                                <input type="hidden" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][slug]" 
                                       value="<?php echo esc_attr($slug); ?>" />
                            </td>
                            <td>
                                <div class="gsd-depots">
                                    <?php 
                                    $depots = isset($courier['depots']) ? $courier['depots'] : array();
                                    foreach ($depots as $index => $depot) : 
                                    ?>
                                    <div class="gsd-depot-row" style="margin-bottom: 10px;">
                                        <input type="text" 
                                               name="couriers[<?php echo esc_attr($slug); ?>][depots][<?php echo esc_attr($index); ?>][name]" 
                                               value="<?php echo esc_attr($depot['name']); ?>" 
                                               placeholder="<?php echo esc_attr__('Depot Name', 'garden-sheds-delivery'); ?>" 
                                               class="regular-text" />
                                        <input type="hidden" 
                                               name="couriers[<?php echo esc_attr($slug); ?>][depots][<?php echo esc_attr($index); ?>][id]" 
                                               value="<?php echo esc_attr($depot['id']); ?>" />
                                        <button type="button" class="button gsd-remove-depot" title="<?php echo esc_attr__('Remove depot', 'garden-sheds-delivery'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                    <button type="button" class="button gsd-add-depot" data-courier="<?php echo esc_attr($slug); ?>">
                                        <?php echo esc_html__('+ Add Depot', 'garden-sheds-delivery'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="gsd_save_depots" class="button-primary" value="<?php echo esc_attr__('Save Depot Locations', 'garden-sheds-delivery'); ?>" />
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.gsd-add-depot').on('click', function() {
                var courier = $(this).data('courier');
                var container = $(this).prev('.gsd-depot-row').length ? $(this).prevAll('.gsd-depot-row:first') : $(this).parent();
                var index = container.find('.gsd-depot-row').length;
                var depotId = courier + '_depot_' + (index + 1);
                
                var html = '<div class="gsd-depot-row" style="margin-bottom: 10px;">' +
                    '<input type="text" name="couriers[' + courier + '][depots][' + index + '][name]" ' +
                    'placeholder="<?php echo esc_js(__('Depot Name', 'garden-sheds-delivery')); ?>" class="regular-text" />' +
                    '<input type="hidden" name="couriers[' + courier + '][depots][' + index + '][id]" value="' + depotId + '" />' +
                    '<button type="button" class="button gsd-remove-depot" title="<?php echo esc_js(__('Remove depot', 'garden-sheds-delivery')); ?>">' +
                    '<span class="dashicons dashicons-trash"></span>' +
                    '</button>' +
                    '</div>';
                
                $(this).before(html);
            });

            // Remove depot functionality
            $(document).on('click', '.gsd-remove-depot', function() {
                if (confirm('<?php echo esc_js(__('Are you sure you want to remove this depot?', 'garden-sheds-delivery')); ?>')) {
                    $(this).closest('.gsd-depot-row').remove();
                }
            });
        });
        </script>

        <style>
        .gsd-depot-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .gsd-depots {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 3px;
        }
        .gsd-remove-depot {
            padding: 4px 8px;
            color: #a00;
            border-color: #a00;
        }
        .gsd-remove-depot:hover {
            color: #fff;
            background: #dc3232;
            border-color: #dc3232;
        }
        .gsd-remove-depot .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        </style>
        <?php
    }

    /**
     * Save couriers
     */
    private function save_couriers() {
        if (!isset($_POST['couriers']) || !is_array($_POST['couriers'])) {
            return;
        }

        $couriers = array();
        foreach ($_POST['couriers'] as $slug => $courier_data) {
            $slug = sanitize_key($slug);
            $couriers[$slug] = array(
                'name' => sanitize_text_field($courier_data['name']),
                'slug' => sanitize_key($courier_data['slug']),
                'enabled' => isset($courier_data['enabled']) && $courier_data['enabled'] === '1',
                'depots' => array(),
            );

            if (isset($courier_data['depots']) && is_array($courier_data['depots'])) {
                foreach ($courier_data['depots'] as $depot) {
                    if (!empty($depot['name'])) {
                        $couriers[$slug]['depots'][] = array(
                            'id' => sanitize_key($depot['id']),
                            'name' => sanitize_text_field($depot['name']),
                        );
                    }
                }
            }
        }

        GSD_Courier::update_couriers($couriers);
        
        // Clear WooCommerce shipping cache to ensure new depot settings are reflected
        $this->clear_shipping_cache();
    }

    /**
     * Save delivery settings
     */
    private function save_delivery_settings() {
        // Save home delivery categories
        $this->save_category_option('gsd_home_delivery_categories');

        // Save express delivery categories
        $this->save_category_option('gsd_express_delivery_categories');

        // Save contact delivery categories
        $this->save_category_option('gsd_contact_delivery_categories');

        // Save main freight categories
        $this->save_category_option('gsd_main_freight_categories');

        // Save PBT categories
        $this->save_category_option('gsd_pbt_categories');

        // Save default home delivery cost
        $cost = isset($_POST['gsd_default_home_delivery_cost']) 
            ? sanitize_text_field($_POST['gsd_default_home_delivery_cost']) 
            : '150';
        update_option('gsd_default_home_delivery_cost', $cost);

        // Save default express delivery cost
        $express_cost = isset($_POST['gsd_default_express_delivery_cost']) 
            ? sanitize_text_field($_POST['gsd_default_express_delivery_cost']) 
            : '15';
        update_option('gsd_default_express_delivery_cost', $express_cost);
        
        // Clear WooCommerce shipping cache to ensure new settings are reflected
        $this->clear_shipping_cache();
    }

    /**
     * Helper method to save category array options
     */
    private function save_category_option($option_name) {
        $categories = isset($_POST[$option_name]) && is_array($_POST[$option_name]) 
            ? array_map('intval', $_POST[$option_name]) 
            : array();
        update_option($option_name, $categories);
    }

    /**
     * Documentation page
     */
    public function docs_page() {
        $default_home_delivery_cost = get_option('gsd_default_home_delivery_cost', '150');
        $default_express_delivery_cost = get_option('gsd_default_express_delivery_cost', '15');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Garden Sheds Delivery Documentation', 'garden-sheds-delivery'); ?></h1>
            
            <!-- Home Delivery Fee Documentation -->
            <div class="notice notice-info" style="border-left-color: #2271b1; margin-top: 20px;">
                <h2 style="margin-top: 10px;"><?php echo esc_html__('📦 How Home Delivery Fees Work', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Understanding how shipping fees are calculated when customers select home delivery:', 'garden-sheds-delivery'); ?></p>
                
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>
                        <strong><?php echo esc_html__('Default Delivery Fee:', 'garden-sheds-delivery'); ?></strong>
                        <?php 
                        echo sprintf(
                            esc_html__('The global default home delivery cost is set to %s. This applies to all products unless overridden.', 'garden-sheds-delivery'),
                            '<strong>' . wc_price($default_home_delivery_cost) . '</strong>'
                        ); 
                        ?>
                        <br>
                        <em><?php echo sprintf(
                            esc_html__('→ You can change this in %sShed Delivery > Settings%s', 'garden-sheds-delivery'),
                            '<a href="' . esc_url(admin_url('admin.php?page=garden-sheds-delivery')) . '">',
                            '</a>'
                        ); ?></em>
                    </li>
                    
                    <li>
                        <strong><?php echo esc_html__('Product-Specific Pricing:', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('Each product can override the default cost with a custom home delivery price.', 'garden-sheds-delivery'); ?>
                        <br>
                        <em><?php echo esc_html__('→ Set this in the product "Delivery Options" tab when editing a product', 'garden-sheds-delivery'); ?></em>
                    </li>
                    
                    <li>
                        <strong><?php echo esc_html__('Category-Based Home Delivery:', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('Products in categories marked for "Home Delivery" automatically offer this option at checkout.', 'garden-sheds-delivery'); ?>
                        <br>
                        <em><?php echo sprintf(
                            esc_html__('→ Configure this in %sShed Delivery > Settings%s under "Delivery Options by Category"', 'garden-sheds-delivery'),
                            '<a href="' . esc_url(admin_url('admin.php?page=garden-sheds-delivery')) . '">',
                            '</a>'
                        ); ?></em>
                    </li>
                    
                    <li>
                        <strong><?php echo esc_html__('Checkout Process:', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('When customers select a shipping method at checkout, the shipping fee is automatically calculated based on their choice.', 'garden-sheds-delivery'); ?>
                    </li>
                    
                    <li>
                        <strong><?php echo esc_html__('Depot Pickup (Free):', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('If customers choose to pick up from a depot instead, no shipping fee is charged.', 'garden-sheds-delivery'); ?>
                    </li>
                </ol>
                
                <h3 style="margin-top: 15px;"><?php echo esc_html__('💡 Example:', 'garden-sheds-delivery'); ?></h3>
                <div style="background: #f0f6fc; padding: 15px; border-radius: 4px; margin: 10px 0;">
                    <p style="margin: 0;">
                        <strong><?php echo esc_html__('Product:', 'garden-sheds-delivery'); ?></strong> <?php echo esc_html__('Garden Shed Premium', 'garden-sheds-delivery'); ?><br>
                        <strong><?php echo esc_html__('Category:', 'garden-sheds-delivery'); ?></strong> <?php echo esc_html__('Garden Sheds (Home Delivery enabled)', 'garden-sheds-delivery'); ?><br>
                        <strong><?php echo esc_html__('Custom Price:', 'garden-sheds-delivery'); ?></strong> <?php echo esc_html__('Not set', 'garden-sheds-delivery'); ?><br>
                        <strong><?php echo esc_html__('Result:', 'garden-sheds-delivery'); ?></strong> 
                        <?php echo sprintf(
                            esc_html__('Customer sees "Home Delivery (+%s)" as a shipping option at checkout', 'garden-sheds-delivery'),
                            wc_price($default_home_delivery_cost)
                        ); ?>
                    </p>
                </div>
                
                <div style="background: #f0f6fc; padding: 15px; border-radius: 4px; margin: 10px 0;">
                    <p style="margin: 0;">
                        <strong><?php echo esc_html__('Product:', 'garden-sheds-delivery'); ?></strong> <?php echo esc_html__('Custom Garden Shed', 'garden-sheds-delivery'); ?><br>
                        <strong><?php echo esc_html__('Category:', 'garden-sheds-delivery'); ?></strong> <?php echo esc_html__('Garden Sheds (Home Delivery enabled)', 'garden-sheds-delivery'); ?><br>
                        <strong><?php echo esc_html__('Custom Price:', 'garden-sheds-delivery'); ?></strong> <?php echo wc_price(250); ?><br>
                        <strong><?php echo esc_html__('Result:', 'garden-sheds-delivery'); ?></strong> 
                        <?php echo sprintf(
                            esc_html__('Customer sees "Home Delivery (+%s)" as a shipping option at checkout (uses custom price)', 'garden-sheds-delivery'),
                            wc_price(250)
                        ); ?>
                    </p>
                </div>
                
                <h3 style="margin-top: 15px;"><?php echo esc_html__('🔧 Troubleshooting:', 'garden-sheds-delivery'); ?></h3>
                <ul style="margin-left: 20px; line-height: 1.8;">
                    <li>
                        <strong><?php echo esc_html__('Fee not showing?', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('Make sure the product\'s category is marked for "Home Delivery" in Shed Delivery > Settings', 'garden-sheds-delivery'); ?>
                    </li>
                    <li>
                        <strong><?php echo esc_html__('Wrong price?', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('Check if the product has a custom price set in the "Delivery Options" tab', 'garden-sheds-delivery'); ?>
                    </li>
                    <li>
                        <strong><?php echo esc_html__('Shipping options not showing?', 'garden-sheds-delivery'); ?></strong>
                        <?php echo esc_html__('Make sure the Garden Sheds Delivery shipping method is added to a shipping zone in WooCommerce > Settings > Shipping.', 'garden-sheds-delivery'); ?>
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Clear WooCommerce shipping cache
     * 
     * This ensures that when admin settings are changed, the shipping options
     * are recalculated even if items are already in the cart
     */
    private function clear_shipping_cache() {
        // Delete all shipping transients
        global $wpdb;
        
        $shipping_pattern = $wpdb->esc_like('_transient_shipping_') . '%';
        $timeout_pattern = $wpdb->esc_like('_transient_timeout_shipping_') . '%';
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $shipping_pattern
            )
        );
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeout_pattern
            )
        );
        
        // Clear WooCommerce cache
        if (function_exists('wc_delete_shop_order_transients')) {
            wc_delete_shop_order_transients();
        }
        
        // Clear object cache
        wp_cache_flush();
    }

    /**
     * AJAX handler to get products for a category
     */
    public function ajax_get_category_products() {
        check_ajax_referer('gsd_get_category_products', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Permission denied', 'garden-sheds-delivery')));
        }
        
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        if (!$category_id) {
            wp_send_json_error(array('message' => __('Invalid category', 'garden-sheds-delivery')));
        }
        
        // Get products in this category
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            ),
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        $products = get_posts($args);
        
        if (empty($products)) {
            wp_send_json_success(array(
                'html' => '<div style="padding: 15px; text-align: center; color: #666;">' . 
                          esc_html__('No products found in this category.', 'garden-sheds-delivery') . 
                          '</div>'
            ));
        }
        
        // Get category-level delivery settings
        $selected_home_delivery = get_option('gsd_home_delivery_categories', array());
        $selected_express_delivery = get_option('gsd_express_delivery_categories', array());
        $selected_contact_delivery = get_option('gsd_contact_delivery_categories', array());
        
        $selected_home_delivery = is_array($selected_home_delivery) ? $selected_home_delivery : array();
        $selected_express_delivery = is_array($selected_express_delivery) ? $selected_express_delivery : array();
        $selected_contact_delivery = is_array($selected_contact_delivery) ? $selected_contact_delivery : array();
        
        // Check if category has delivery options enabled
        $category_has_home_delivery = in_array($category_id, $selected_home_delivery);
        $category_has_express_delivery = in_array($category_id, $selected_express_delivery);
        $category_has_contact_delivery = in_array($category_id, $selected_contact_delivery);
        
        // Build products table HTML
        ob_start();
        ?>
        <table class="gsd-products-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Product', 'garden-sheds-delivery'); ?></th>
                    <th style="text-align: center; width: 120px;"><?php echo esc_html__('Home Delivery', 'garden-sheds-delivery'); ?></th>
                    <th style="text-align: center; width: 120px;"><?php echo esc_html__('Small Items', 'garden-sheds-delivery'); ?></th>
                    <th style="text-align: center; width: 150px;"><?php echo esc_html__('Contact for Delivery', 'garden-sheds-delivery'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) : 
                    // Get product meta, or use category default if not set
                    $home_delivery_meta = get_post_meta($product->ID, '_gsd_home_delivery_available', true);
                    $express_delivery_meta = get_post_meta($product->ID, '_gsd_express_delivery_available', true);
                    $contact_delivery_meta = get_post_meta($product->ID, '_gsd_contact_for_delivery', true);
                    
                    // If meta is empty, inherit from category; otherwise use saved value
                    $home_delivery = ($home_delivery_meta === '') ? $category_has_home_delivery : ($home_delivery_meta === 'yes');
                    $express_delivery = ($express_delivery_meta === '') ? $category_has_express_delivery : ($express_delivery_meta === 'yes');
                    $contact_delivery = ($contact_delivery_meta === '') ? $category_has_contact_delivery : ($contact_delivery_meta === 'yes');
                ?>
                <tr class="gsd-product-row" data-product-id="<?php echo esc_attr($product->ID); ?>">
                    <td class="gsd-product-name">
                        <a href="<?php echo esc_url(get_edit_post_link($product->ID)); ?>" target="_blank">
                            <?php echo esc_html($product->post_title); ?>
                        </a>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" class="gsd-product-home-delivery" <?php checked($home_delivery); ?> />
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" class="gsd-product-express-delivery" <?php checked($express_delivery); ?> />
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" class="gsd-product-contact-delivery" <?php checked($contact_delivery); ?> />
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX handler to save product shipping settings
     */
    public function ajax_save_product_shipping() {
        // Debug logging
        error_log('GSD: ajax_save_product_shipping called');
        error_log('GSD: POST data: ' . print_r($_POST, true));
        
        check_ajax_referer('gsd_save_product_shipping', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            error_log('GSD: Permission denied for user');
            wp_send_json_error(array('message' => __('Permission denied', 'garden-sheds-delivery')));
            return;
        }
        
        $products = isset($_POST['products']) ? (array) $_POST['products'] : array();
        
        if (empty($products)) {
            wp_send_json_error(array('message' => __('Invalid data - no products provided', 'garden-sheds-delivery')));
            return;
        }
        
        $saved_count = 0;
        $errors = array();
        
        foreach ($products as $product_data) {
            // Ensure product_data is an array
            if (!is_array($product_data)) {
                $errors[] = __('Invalid product data format', 'garden-sheds-delivery');
                continue;
            }
            
            // Sanitize and validate product ID
            $product_id = isset($product_data['product_id']) ? intval($product_data['product_id']) : 0;
            
            if (!$product_id) {
                $errors[] = __('Invalid product ID', 'garden-sheds-delivery');
                continue;
            }
            
            $post_type = get_post_type($product_id);
            if (!in_array($post_type, array('product', 'product_variation'), true)) {
                $errors[] = sprintf(__('Product ID %d is not a valid product', 'garden-sheds-delivery'), $product_id);
                continue;
            }
            
            // Get raw values from POST data
            $home_raw = isset($product_data['home_delivery']) ? $product_data['home_delivery'] : false;
            $express_raw = isset($product_data['express_delivery']) ? $product_data['express_delivery'] : false;
            $contact_raw = isset($product_data['contact_delivery']) ? $product_data['contact_delivery'] : false;
            
            // Convert to boolean (handles both boolean and string 'true'/'false', and integers 1/0)
            $home_delivery_bool = filter_var($home_raw, FILTER_VALIDATE_BOOLEAN);
            $express_delivery_bool = filter_var($express_raw, FILTER_VALIDATE_BOOLEAN);
            $contact_delivery_bool = filter_var($contact_raw, FILTER_VALIDATE_BOOLEAN);
            
            // Convert to 'yes' or 'no' for storage
            $home_delivery = $home_delivery_bool ? 'yes' : 'no';
            $express_delivery = $express_delivery_bool ? 'yes' : 'no';
            $contact_delivery = $contact_delivery_bool ? 'yes' : 'no';
            
            // Save all three delivery settings
            update_post_meta($product_id, '_gsd_home_delivery_available', $home_delivery);
            update_post_meta($product_id, '_gsd_express_delivery_available', $express_delivery);
            update_post_meta($product_id, '_gsd_contact_for_delivery', $contact_delivery);
            
            // Note: update_post_meta returns false for errors OR when value is unchanged.
            // We count this as successful since all three updates were attempted without exceptions.
            // This is acceptable because unchanged values aren't errors from a UX perspective.
            $saved_count++;
        }
        
        // Clear shipping cache
        $this->clear_shipping_cache();
        
        // Send response with detailed information
        if ($saved_count > 0) {
            $message = sprintf(
                _n('Successfully saved %d product', 'Successfully saved %d products', $saved_count, 'garden-sheds-delivery'),
                $saved_count
            );
            
            if (!empty($errors)) {
                $message .= ' ' . sprintf(
                    _n('(%d error)', '(%d errors)', count($errors), 'garden-sheds-delivery'),
                    count($errors)
                );
            }
            
            wp_send_json_success(array(
                'message' => $message,
                'saved_count' => $saved_count,
                'errors' => $errors
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to save any products', 'garden-sheds-delivery'),
                'errors' => $errors
            ));
        }
    }
}
