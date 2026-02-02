<?php
/**
 * Checkout Process Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Checkout {
    
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
        // Save shipping method data to order
        add_action('woocommerce_checkout_create_order', array($this, 'save_shipping_method_data'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (is_checkout() || is_cart()) {
            wp_enqueue_style('gsd-frontend', GSD_PLUGIN_URL . 'assets/css/frontend.css', array(), GSD_VERSION);
            wp_enqueue_script('gsd-frontend', GSD_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), GSD_VERSION, true);
        }
    }

    /**
     * Save shipping method data to order
     * 
     * Extracts delivery information from the selected shipping method rate
     * and saves it as order meta data. Handles both depot pickup and home delivery.
     *
     * @param WC_Order $order The order object being created
     */
    public function save_shipping_method_data($order) {
        // Get the chosen shipping methods
        $shipping_methods = $order->get_shipping_methods();
        
        foreach ($shipping_methods as $shipping_method) {
            $method_id = $shipping_method->get_method_id();
            
            // Check if this is our shipping method
            if ($method_id === 'garden_sheds_delivery') {
                $instance_id = $shipping_method->get_instance_id();
                $rate_id = $shipping_method->get_id(); // Full rate ID including any suffix
                
                // Extract meta data from the shipping method
                $meta_data = $shipping_method->get_meta_data();
                
                // Check if this is a depot pickup or home delivery
                if (strpos($rate_id, ':depot:') !== false) {
                    // Depot pickup
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'depot_id') {
                            $order->update_meta_data('_gsd_depot', $data['value']);
                        } elseif ($data['key'] === 'depot_name') {
                            $order->update_meta_data('_gsd_depot_name', $data['value']);
                        } elseif ($data['key'] === 'courier_name') {
                            $order->update_meta_data('_gsd_courier', $data['value']);
                        }
                    }
                    $order->update_meta_data('_gsd_home_delivery', 'no');
                } elseif (strpos($rate_id, ':home_delivery') !== false) {
                    // Home delivery
                    $order->update_meta_data('_gsd_home_delivery', 'yes');
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'home_delivery_price') {
                            $order->update_meta_data('_gsd_home_delivery_price', $data['value']);
                        }
                    }
                }
            }
        }
    }
}
