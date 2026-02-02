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
        
        // Add custom display for shipping in cart and checkout totals
        add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'customize_shipping_label'), 10, 2);
        
        // Add delivery fee as separate line item in order summary
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_delivery_fee'));
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
     * and saves it as order meta data. Handles depot pickup, home delivery, and small item delivery.
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
                
                // Check if this is a depot pickup or home delivery or express delivery
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
                    $order->update_meta_data('_gsd_express_delivery', 'no');
                } elseif (strpos($rate_id, ':home_delivery') !== false) {
                    // Home delivery
                    $order->update_meta_data('_gsd_home_delivery', 'yes');
                    $order->update_meta_data('_gsd_express_delivery', 'no');
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'home_delivery_price') {
                            $order->update_meta_data('_gsd_home_delivery_price', $data['value']);
                        }
                    }
                } elseif (strpos($rate_id, ':express_delivery') !== false) {
                    // Small item delivery
                    $order->update_meta_data('_gsd_home_delivery', 'no');
                    $order->update_meta_data('_gsd_express_delivery', 'yes');
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'express_delivery_price') {
                            $order->update_meta_data('_gsd_express_delivery_price', $data['value']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Customize shipping method label in cart and checkout
     * 
     * Shows cost breakdown including GST for home delivery and small item delivery
     *
     * @param string $label The shipping method label
     * @param object $method The shipping method object
     * @return string Modified label
     */
    public function customize_shipping_label($label, $method) {
        // Only customize our shipping method
        if ($method->get_method_id() !== 'garden_sheds_delivery') {
            return $label;
        }

        $method_id = $method->get_id();
        
        // Check if this is home delivery
        if (strpos($method_id, ':home_delivery') !== false) {
            $label = $this->build_delivery_label(__('Home Delivery', 'garden-sheds-delivery'), $method);
        } elseif (strpos($method_id, ':express_delivery') !== false) {
            // Check if this is express/small item delivery
            $label = $this->build_delivery_label(__('Small Item Delivery', 'garden-sheds-delivery'), $method);
        } elseif (strpos($method_id, ':depot:') !== false) {
            // For depot pickup, just show the depot name
            $label = $method->get_label();
        }
        
        return $label;
    }

    /**
     * Build delivery label with cost and tax information
     *
     * @param string $base_label The base label text
     * @param object $method The shipping method object
     * @return string Formatted label with cost breakdown
     */
    private function build_delivery_label($base_label, $method) {
        $cost = $method->get_cost();
        
        if ($cost <= 0) {
            return $base_label;
        }
        
        // Get tax if applicable
        $taxes = $method->get_taxes();
        $tax_amount = 0;
        if (!empty($taxes) && is_array($taxes)) {
            $tax_amount = array_sum($taxes);
        }
        
        // Build label with cost breakdown
        $label = $base_label;
        
        if ($tax_amount > 0) {
            $total_with_tax = $cost + $tax_amount;
            $label .= sprintf(
                ' <span class="gsd-cost-breakdown">(%s <small>%s</small>)</span>',
                wc_price($total_with_tax),
                __('inc. GST', 'garden-sheds-delivery')
            );
        } else {
            $label .= sprintf(' (%s)', wc_price($cost));
        }
        
        return $label;
    }

    /**
     * Add delivery fee as separate line item in order summary
     * 
     * This hook adds the delivery cost as a separate fee line in the cart/checkout
     * when home delivery or small item delivery is selected
     */
    public function add_delivery_fee() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // Get chosen shipping methods from the session
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
        
        if (empty($chosen_methods)) {
            return;
        }

        // Check each chosen shipping method
        foreach ($chosen_methods as $chosen_method) {
            // Check if this is our shipping method with home delivery
            if (strpos($chosen_method, 'garden_sheds_delivery') !== false && strpos($chosen_method, ':home_delivery') !== false) {
                // Get the home delivery price from the cart
                $package = WC()->cart->get_shipping_packages()[0];
                $shipping_method = new GSD_Shipping_Method();
                $home_delivery_price = $shipping_method->get_package_home_delivery_price($package);
                
                if ($home_delivery_price > 0) {
                    WC()->cart->add_fee(__('Home Delivery', 'garden-sheds-delivery'), $home_delivery_price, true);
                }
                break;
            }
            // Check if this is our shipping method with small item delivery
            elseif (strpos($chosen_method, 'garden_sheds_delivery') !== false && strpos($chosen_method, ':express_delivery') !== false) {
                // Get the express delivery price from the cart
                $package = WC()->cart->get_shipping_packages()[0];
                $shipping_method = new GSD_Shipping_Method();
                $express_delivery_price = $shipping_method->get_package_express_delivery_price($package);
                
                if ($express_delivery_price > 0) {
                    WC()->cart->add_fee(__('Small Item Delivery', 'garden-sheds-delivery'), $express_delivery_price, true);
                }
                break;
            }
        }
    }
}
