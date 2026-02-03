<?php
/**
 * Plugin Name: Garden Sheds Delivery
 * Plugin URI: https://github.com/impact2021/Garden-Sheds-Delivery
 * Description: Manage courier delivery options for garden sheds with multiple depot locations
 * Version: 2.0.1
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
define('GSD_VERSION', '2.0.1');
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
    
    // Ensure default depot data exists
    gsd_create_default_data();
    
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
    
    // Create or update with full default data
    $default_data = array(
        'main_freight' => array(
            'name' => 'Main Freight',
            'slug' => 'main_freight',
            'enabled' => true,
            'depots' => array(
                array('id' => 'mf_depot_1', 'name' => 'Kaitaia'),
                array('id' => 'mf_depot_2', 'name' => 'Whangarei'),
                array('id' => 'mf_depot_3', 'name' => 'Auckland'),
                array('id' => 'mf_depot_4', 'name' => 'Hobsonville'),
                array('id' => 'mf_depot_5', 'name' => 'Hamilton'),
                array('id' => 'mf_depot_6', 'name' => 'Thames'),
                array('id' => 'mf_depot_7', 'name' => 'Tauranga'),
                array('id' => 'mf_depot_8', 'name' => 'Rotorua'),
                array('id' => 'mf_depot_9', 'name' => 'Taupo'),
                array('id' => 'mf_depot_10', 'name' => 'Gisborne'),
                array('id' => 'mf_depot_11', 'name' => 'Napier'),
                array('id' => 'mf_depot_12', 'name' => 'New Plymouth'),
                array('id' => 'mf_depot_13', 'name' => 'Palmerston North'),
                array('id' => 'mf_depot_14', 'name' => 'Masterton'),
                array('id' => 'mf_depot_15', 'name' => 'Wellington'),
                array('id' => 'mf_depot_16', 'name' => 'Nelson'),
                array('id' => 'mf_depot_17', 'name' => 'Blenheim'),
                array('id' => 'mf_depot_18', 'name' => 'Westport'),
                array('id' => 'mf_depot_19', 'name' => 'Greymouth'),
                array('id' => 'mf_depot_20', 'name' => 'Christchurch'),
                array('id' => 'mf_depot_21', 'name' => 'Ashburton'),
                array('id' => 'mf_depot_22', 'name' => 'Timaru'),
                array('id' => 'mf_depot_23', 'name' => 'Cromwell'),
                array('id' => 'mf_depot_24', 'name' => 'Oamaru'),
                array('id' => 'mf_depot_25', 'name' => 'Dunedin'),
                array('id' => 'mf_depot_26', 'name' => 'Gore'),
                array('id' => 'mf_depot_27', 'name' => 'Invercargill'),
            )
        ),
        'pbt' => array(
            'name' => 'PBT',
            'slug' => 'pbt',
            'enabled' => false,
            'depots' => array(
                array('id' => 'pbt_depot_1', 'name' => 'North Island Hub'),
                array('id' => 'pbt_depot_2', 'name' => 'South Island Hub'),
            )
        )
    );

    // Merge with existing data to preserve any custom couriers
    if (!empty($existing_couriers)) {
        // Always update Main Freight to ensure all depots are present
        $existing_couriers['main_freight'] = $default_data['main_freight'];
        
        // Update PBT - add if missing, otherwise preserve existing settings
        if (!isset($existing_couriers['pbt'])) {
            // Add PBT with disabled default if it doesn't exist
            $existing_couriers['pbt'] = $default_data['pbt'];
        } else {
            // PBT exists - set enabled to false only if the key is missing (preserve user's choice)
            if (!isset($existing_couriers['pbt']['enabled'])) {
                $existing_couriers['pbt']['enabled'] = false;
            }
        }
        
        update_option('gsd_courier_companies', $existing_couriers);
    } else {
        update_option('gsd_courier_companies', $default_data);
    }
}
