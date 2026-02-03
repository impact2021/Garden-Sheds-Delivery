<?php
/**
 * Depot Pickup (Mainfreight) Shipping Method
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Depot Pickup (Mainfreight) Shipping Method Class
 */
class GSD_Shipping_Depot_Mainfreight extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = 'gsd_depot_mainfreight';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Depot Pickup', 'garden-sheds-delivery');
        $this->method_description = __('Pickup from depot locations', 'garden-sheds-delivery');
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
                'default' => __('Depot Pickup', 'garden-sheds-delivery'),
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
        // Check if cart has products eligible for Mainfreight depot pickup
        if (!$this->cart_has_mainfreight_depot($package)) {
            return;
        }

        $courier = GSD_Courier::get_courier('main_freight');
        
        // Check if courier exists and is enabled
        if (!$courier) {
            return;
        }
        
        $is_enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
        if (!$is_enabled) {
            return;
        }
        
        $depots = GSD_Courier::get_depots('main_freight');
        
        if (!empty($depots) && is_array($depots)) {
            // Add a single rate for depot pickup with all depots in meta data
            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => __('Depot Pickup', 'garden-sheds-delivery'),
                'cost' => 0, // Depot pickup is free
                'meta_data' => array(
                    'depots' => $depots,
                    'courier_name' => $courier['name'],
                    'courier_slug' => 'main_freight',
                    'delivery_type' => 'depot'
                ),
            );
            $this->add_rate($rate);
        }
    }

    /**
     * Check if cart has products eligible for Mainfreight depot pickup
     *
     * @param array $package Package information
     * @return bool
     */
    private function cart_has_mainfreight_depot($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            
            // Check if product is in Mainfreight category
            $mainfreight_categories = get_option('gsd_main_freight_categories', array());
            if (!empty($mainfreight_categories)) {
                $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
                foreach ($product_categories as $cat_id) {
                    if (in_array($cat_id, $mainfreight_categories)) {
                        return true;
                    }
                }
            }
            
            // Also check if product has Mainfreight courier assigned
            $courier = GSD_Product_Settings::get_product_courier($product_id);
            if ($courier === 'main_freight') {
                return true;
            }
        }
        return false;
    }
}
