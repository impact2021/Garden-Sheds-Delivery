<?php
/**
 * Plugin Name: Garden Sheds Delivery
 * Plugin URI: https://github.com/impact2021/Garden-Sheds-Delivery
 * Description: Manage courier delivery options for garden sheds with multiple depot locations
 * Version: 1.4.0
 * Author: Impact 2021
 * Author URI: https://github.com/impact2021
 * Text Domain: garden-sheds-delivery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('GSD_VERSION', '1.4.0');
define('GSD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GSD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GSD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
if (!function_exists('gsd_is_woocommerce_active')) {
    function gsd_is_woocommerce_active() {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}

/**
 * Initialize the plugin
 */
function gsd_init() {
    if (!gsd_is_woocommerce_active()) {
        add_action('admin_notices', 'gsd_woocommerce_missing_notice');
        return;
    }

    // Include required files
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-courier.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-product-settings.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-checkout.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-order.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-admin.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-method.php';
    
    // Include new shipping method classes
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-home-delivery.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-depot-pbt.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-depot-mainfreight.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-small-items.php';
    require_once GSD_PLUGIN_DIR . 'includes/class-gsd-shipping-contact-delivery.php';

    // Initialize classes
    GSD_Courier::instance();
    GSD_Product_Settings::instance();
    GSD_Checkout::instance();
    GSD_Order::instance();
    
    if (is_admin()) {
        GSD_Admin::instance();
    }
    
    // Register shipping method
    add_filter('woocommerce_shipping_methods', 'gsd_register_shipping_method');
}
add_action('plugins_loaded', 'gsd_init');

/**
 * Register shipping method
 */
function gsd_register_shipping_method($methods) {
    // Keep old method for backwards compatibility
    $methods['garden_sheds_delivery'] = 'GSD_Shipping_Method';
    
    // Register new shipping methods
    $methods['gsd_home_delivery'] = 'GSD_Shipping_Home_Delivery';
    $methods['gsd_depot_pbt'] = 'GSD_Shipping_Depot_PBT';
    $methods['gsd_depot_mainfreight'] = 'GSD_Shipping_Depot_Mainfreight';
    $methods['gsd_small_items'] = 'GSD_Shipping_Small_Items';
    $methods['gsd_contact_delivery'] = 'GSD_Shipping_Contact_Delivery';
    
    return $methods;
}

/**
 * Display WooCommerce missing notice
 */
function gsd_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>' . esc_html__('Garden Sheds Delivery', 'garden-sheds-delivery') . '</strong> ' . esc_html__('requires WooCommerce to be installed and active.', 'garden-sheds-delivery') . '</p></div>';
}

/**
 * Plugin activation
 */
function gsd_activate() {
    if (!gsd_is_woocommerce_active()) {
        deactivate_plugins(GSD_PLUGIN_BASENAME);
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'garden-sheds-delivery'));
    }
    
    // Create default courier companies and depots
    gsd_create_default_data();
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'gsd_activate');

/**
 * Plugin deactivation
 */
function gsd_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gsd_deactivate');

/**
 * Create default courier companies and depot locations
 */
function gsd_create_default_data() {
    // Check if data already exists
    $existing_couriers = get_option('gsd_courier_companies', array());
    if (!empty($existing_couriers)) {
        return;
    }

    $default_data = array(
        'main_freight' => array(
            'name' => 'Main Freight',
            'slug' => 'main_freight',
            'enabled' => true,
            'depots' => array(
                array('id' => 'mf_depot_1', 'name' => 'Auckland Depot'),
                array('id' => 'mf_depot_2', 'name' => 'Wellington Depot'),
                array('id' => 'mf_depot_3', 'name' => 'Christchurch Depot'),
            )
        ),
        'pbt' => array(
            'name' => 'PBT',
            'slug' => 'pbt',
            'enabled' => true,
            'depots' => array(
                array('id' => 'pbt_depot_1', 'name' => 'North Island Hub'),
                array('id' => 'pbt_depot_2', 'name' => 'South Island Hub'),
            )
        )
    );

    update_option('gsd_courier_companies', $default_data);
}
