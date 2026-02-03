<?php
/**
 * Contact for Home Delivery Shipping Method
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact for Home Delivery Shipping Method Class
 */
class GSD_Shipping_Contact_Delivery extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct($instance_id = 0) {
        $this->id = 'gsd_contact_delivery';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Home dleivery may be possible - please contact us.', 'garden-sheds-delivery');
        $this->method_description = __('Contact us to arrange possible home delivery', 'garden-sheds-delivery');
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
                'default' => __('Home dleivery may be possible - please contact us.', 'garden-sheds-delivery'),
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
        // Check if cart has products with contact for delivery enabled
        if (!$this->cart_has_contact_delivery($package)) {
            return;
        }

        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => 0, // Free - customer to contact for pricing
            'meta_data' => array(
                'delivery_type' => 'contact_delivery',
            ),
        );
        $this->add_rate($rate);
    }

    /**
     * Check if cart has contact for delivery option
     *
     * @param array $package Package information
     * @return bool
     */
    private function cart_has_contact_delivery($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            if (GSD_Product_Settings::is_contact_for_delivery($product_id)) {
                return true;
            }
        }
        return false;
    }
}
