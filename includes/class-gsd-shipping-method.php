<?php
/**
 * Garden Sheds Delivery Shipping Method
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Garden Sheds Delivery Shipping Method Class
 */
class GSD_Shipping_Method extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = 'garden_sheds_delivery';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Garden Sheds Delivery', 'garden-sheds-delivery');
        $this->method_description = __('Delivery options for garden sheds with depot selection or home delivery', 'garden-sheds-delivery');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
        );

        $this->init();
    }

    /**
     * Initialize settings
     */
    private function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize form fields
     */
    public function init_form_fields() {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'garden-sheds-delivery'),
                'type' => 'checkbox',
                'label' => __('Enable this shipping method', 'garden-sheds-delivery'),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Method Title', 'garden-sheds-delivery'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'garden-sheds-delivery'),
                'default' => __('Shed Delivery', 'garden-sheds-delivery'),
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Calculate shipping
     *
     * @param array $package Package information
     */
    public function calculate_shipping($package = array()) {
        // Check if cart contains products requiring this delivery method
        if (!$this->cart_needs_shed_delivery($package)) {
            return;
        }

        // Get courier and home delivery info from cart
        $courier_slug = $this->get_package_courier($package);
        $has_home_delivery = $this->package_has_home_delivery($package);
        $home_delivery_price = $this->get_package_home_delivery_price($package);

        // Add depot pickup rates if courier is assigned
        if ($courier_slug) {
            $courier = GSD_Courier::get_courier($courier_slug);
            
            // Check if courier exists and is enabled
            if ($courier) {
                $is_enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
                
                if ($is_enabled) {
                    $depots = GSD_Courier::get_depots($courier_slug);
                    
                    if (!empty($depots) && is_array($depots)) {
                        foreach ($depots as $depot) {
                            // Ensure depot has required fields
                            if (!isset($depot['id']) || !isset($depot['name'])) {
                                continue;
                            }
                            
                            $rate = array(
                                'id' => $this->get_rate_id() . ':depot:' . $depot['id'],
                                'label' => sprintf(__('Pickup from %s', 'garden-sheds-delivery'), $depot['name']),
                                'cost' => '0', // Depot pickup is free - use string for WooCommerce compatibility
                                'meta_data' => array(
                                    'depot_id' => $depot['id'],
                                    'depot_name' => $depot['name'],
                                    'courier_name' => $courier['name'],
                                    'delivery_type' => 'depot'
                                ),
                            );
                            $this->add_rate($rate);
                        }
                    }
                }
            }
        }

        // Add home delivery rate if available
        if ($has_home_delivery && $home_delivery_price > 0) {
            // Ensure cost is a string for WooCommerce compatibility
            $delivery_cost = number_format((float)$home_delivery_price, 2, '.', '');
            
            $rate = array(
                'id' => $this->get_rate_id() . ':home_delivery',
                'label' => sprintf(__('Home Delivery (+%s)', 'garden-sheds-delivery'), wc_price($delivery_cost)),
                'cost' => $delivery_cost, // String format for WooCommerce
                'meta_data' => array(
                    'delivery_type' => 'home_delivery',
                    'home_delivery_price' => $delivery_cost
                ),
            );
            $this->add_rate($rate);
        }
    }

    /**
     * Check if cart contains products requiring shed delivery
     *
     * @param array $package Package information
     * @return bool
     */
    private function cart_needs_shed_delivery($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            $courier = GSD_Product_Settings::get_product_courier($product_id);
            if (!empty($courier)) {
                return true;
            }
            
            // Also check if product has home delivery available through category settings
            if (GSD_Product_Settings::is_home_delivery_available($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get courier for package items
     *
     * @param array $package Package information
     * @return string|null
     */
    private function get_package_courier($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            $courier = GSD_Product_Settings::get_product_courier($product_id);
            if (!empty($courier)) {
                return $courier;
            }
        }
        return null;
    }

    /**
     * Check if package has home delivery option
     *
     * @param array $package Package information
     * @return bool
     */
    private function package_has_home_delivery($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            if (GSD_Product_Settings::is_home_delivery_available($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get home delivery price for package
     *
     * @param array $package Package information
     * @return float
     */
    private function get_package_home_delivery_price($package) {
        $max_price = 0;
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            if (GSD_Product_Settings::is_home_delivery_available($product_id)) {
                $price = GSD_Product_Settings::get_home_delivery_price($product_id);
                if ($price > $max_price) {
                    $max_price = $price;
                }
            }
        }
        return $max_price;
    }
}
