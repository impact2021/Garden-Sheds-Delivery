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

        // Add the shipping rate
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => 0, // Base cost is 0 (depot pickup is free)
            'package' => $package,
        );

        $this->add_rate($rate);
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
}
