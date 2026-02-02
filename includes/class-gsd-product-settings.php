<?php
/**
 * Product Settings Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Product_Settings {
    
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
        // Add product data tabs
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_product_data_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_data'));
    }

    /**
     * Add custom product data tab
     */
    public function add_product_data_tab($tabs) {
        $tabs['gsd_delivery'] = array(
            'label' => __('Delivery Options', 'garden-sheds-delivery'),
            'target' => 'gsd_delivery_data',
            'class' => array('show_if_simple'),
            'priority' => 60,
        );
        return $tabs;
    }

    /**
     * Add custom product data panel
     */
    public function add_product_data_panel() {
        global $post;
        ?>
        <div id="gsd_delivery_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                // Courier selection
                $couriers = GSD_Courier::get_couriers();
                $courier_options = array('' => __('Select Courier', 'garden-sheds-delivery'));
                foreach ($couriers as $slug => $courier) {
                    $courier_options[$slug] = $courier['name'];
                }

                woocommerce_wp_select(array(
                    'id' => '_gsd_courier',
                    'label' => __('Courier Company', 'garden-sheds-delivery'),
                    'desc_tip' => true,
                    'description' => __('Select which courier company delivers this product.', 'garden-sheds-delivery'),
                    'options' => $courier_options,
                ));

                // Home delivery option
                woocommerce_wp_checkbox(array(
                    'id' => '_gsd_home_delivery_available',
                    'label' => __('Home Delivery Available', 'garden-sheds-delivery'),
                    'desc_tip' => true,
                    'description' => __('Enable optional home delivery for this product.', 'garden-sheds-delivery'),
                ));

                // Home delivery price
                woocommerce_wp_text_input(array(
                    'id' => '_gsd_home_delivery_price',
                    'label' => __('Home Delivery Price', 'garden-sheds-delivery') . ' (' . get_woocommerce_currency_symbol() . ')',
                    'desc_tip' => true,
                    'description' => __('Price for home delivery option.', 'garden-sheds-delivery'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.01',
                        'min' => '0',
                    ),
                    'value' => get_post_meta($post->ID, '_gsd_home_delivery_price', true) ?: '150',
                ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save product data
     */
    public function save_product_data($post_id) {
        $courier = isset($_POST['_gsd_courier']) ? sanitize_text_field($_POST['_gsd_courier']) : '';
        update_post_meta($post_id, '_gsd_courier', $courier);

        $home_delivery = isset($_POST['_gsd_home_delivery_available']) ? 'yes' : 'no';
        update_post_meta($post_id, '_gsd_home_delivery_available', $home_delivery);

        $home_delivery_price = isset($_POST['_gsd_home_delivery_price']) ? sanitize_text_field($_POST['_gsd_home_delivery_price']) : '150';
        update_post_meta($post_id, '_gsd_home_delivery_price', $home_delivery_price);
    }

    /**
     * Get product courier
     */
    public static function get_product_courier($product_id) {
        return get_post_meta($product_id, '_gsd_courier', true);
    }

    /**
     * Check if home delivery is available for product
     */
    public static function is_home_delivery_available($product_id) {
        return get_post_meta($product_id, '_gsd_home_delivery_available', true) === 'yes';
    }

    /**
     * Get home delivery price for product
     */
    public static function get_home_delivery_price($product_id) {
        $price = get_post_meta($product_id, '_gsd_home_delivery_price', true);
        return $price ? floatval($price) : 150.00;
    }
}
