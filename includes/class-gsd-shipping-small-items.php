<?php
/**
 * Small Items Delivery Shipping Method
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Small Items Delivery Shipping Method Class
 */
class GSD_Shipping_Small_Items extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = 'gsd_small_items';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Small Items Delivery', 'garden-sheds-delivery');
        $this->method_description = __('Small items delivery shipping option', 'garden-sheds-delivery');
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
                'default' => __('Small Items Delivery', 'garden-sheds-delivery'),
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
        // Check if cart has products with small items delivery enabled
        if (!$this->cart_has_small_items_delivery($package)) {
            return;
        }

        $express_delivery_price = $this->get_package_express_delivery_price($package);

        if ($express_delivery_price > 0) {
            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => $this->title,
                'cost' => 0, // Cost shown as separate fee in order summary
                'meta_data' => array(
                    'delivery_type' => 'express_delivery',
                    'express_delivery_price' => (float)$express_delivery_price
                ),
            );
            $this->add_rate($rate);
        }
    }

    /**
     * Check if cart has small items delivery option
     *
     * @param array $package Package information
     * @return bool
     */
    private function cart_has_small_items_delivery($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            if (GSD_Product_Settings::is_express_delivery_available($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get small items delivery price for package
     *
     * @param array $package Package information
     * @return float
     */
    private function get_package_express_delivery_price($package) {
        $max_price = 0;
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            
            if (GSD_Product_Settings::is_express_delivery_available($product_id)) {
                $price = GSD_Product_Settings::get_express_delivery_price($product_id);
                if ($price > $max_price) {
                    $max_price = $price;
                }
            }
        }
        return $max_price;
    }
}
