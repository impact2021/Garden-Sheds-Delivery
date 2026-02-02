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
        // Add depot selection and home delivery fields to checkout
        add_action('woocommerce_after_order_notes', array($this, 'add_checkout_fields'));
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout_fields'));
        add_action('woocommerce_checkout_create_order', array($this, 'save_checkout_fields'));
        
        // Calculate home delivery fee
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_home_delivery_fee'));
        
        // Add custom fields to cart
        add_action('woocommerce_before_checkout_form', array($this, 'add_cart_delivery_fields'), 5);
        
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
     * Check if cart contains products requiring delivery selection
     */
    private function cart_needs_delivery_selection() {
        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
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
     * Get courier for cart items
     */
    private function get_cart_courier() {
        if (!WC()->cart) {
            return null;
        }

        $courier = null;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $product_courier = GSD_Product_Settings::get_product_courier($product_id);
            if (!empty($product_courier)) {
                $courier = $product_courier;
                break; // Use first found courier
            }
        }
        return $courier;
    }

    /**
     * Check if any product in cart has home delivery available
     */
    private function cart_has_home_delivery_option() {
        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (GSD_Product_Settings::is_home_delivery_available($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if any product in cart has "contact for delivery" option
     */
    private function cart_has_contact_for_delivery() {
        // Check global setting first
        $global_setting = get_option('gsd_show_contact_for_delivery', false);
        if ($global_setting) {
            // If global setting is enabled, show for any product with delivery selection
            return $this->cart_needs_delivery_selection();
        }

        // Otherwise check product-level setting
        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (GSD_Product_Settings::is_contact_for_delivery($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get home delivery price for cart
     */
    private function get_cart_home_delivery_price() {
        if (!WC()->cart) {
            return 0;
        }

        $max_price = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (GSD_Product_Settings::is_home_delivery_available($product_id)) {
                $price = GSD_Product_Settings::get_home_delivery_price($product_id);
                if ($price > $max_price) {
                    $max_price = $price;
                }
            }
        }
        return $max_price;
    }

    /**
     * Add delivery fields to cart page
     */
    public function add_cart_delivery_fields() {
        if (!$this->cart_needs_delivery_selection()) {
            return;
        }

        echo '<div class="gsd-delivery-selection" style="background: #f7f7f7; padding: 20px; margin-bottom: 20px; border-radius: 5px;">';
        echo '<h3>' . esc_html__('Delivery Options', 'garden-sheds-delivery') . '</h3>';
        
        // Depot selection
        $courier_slug = $this->get_cart_courier();
        if ($courier_slug) {
            $courier = GSD_Courier::get_courier($courier_slug);
            // Only show depot selection if courier is enabled
            $is_enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
            
            if ($is_enabled) {
                $depots = GSD_Courier::get_depots($courier_slug);
                
                if (!empty($depots)) {
                    echo '<p><strong>' . esc_html__('Select Depot Location:', 'garden-sheds-delivery') . '</strong></p>';
                    echo '<select name="gsd_depot" id="gsd_depot" class="input-text" style="width: 100%; max-width: 400px; padding: 8px;">';
                    echo '<option value="">' . esc_html__('-- Select Depot --', 'garden-sheds-delivery') . '</option>';
                    foreach ($depots as $depot) {
                        $selected = (isset($_POST['gsd_depot']) && $_POST['gsd_depot'] === $depot['id']) ? 'selected' : '';
                        echo '<option value="' . esc_attr($depot['id']) . '" ' . $selected . '>' . esc_html($depot['name']) . '</option>';
                    }
                    echo '</select>';
                }
            }
        }

        // Home delivery option
        if ($this->cart_has_home_delivery_option()) {
            $home_delivery_price = $this->get_cart_home_delivery_price();
            echo '<p style="margin-top: 15px;">';
            echo '<label>';
            $checked = (isset($_POST['gsd_home_delivery']) && $_POST['gsd_home_delivery'] === '1') ? 'checked' : '';
            echo '<input type="checkbox" name="gsd_home_delivery" id="gsd_home_delivery" value="1" ' . $checked . ' /> ';
            echo sprintf(
                esc_html__('Home Delivery (+%s)', 'garden-sheds-delivery'),
                wc_price($home_delivery_price)
            );
            echo '</label>';
            echo '</p>';
        }

        // Contact for delivery option
        if ($this->cart_has_contact_for_delivery()) {
            echo '<div class="gsd-contact-delivery-notice" style="margin-top: 15px; padding: 12px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 3px;">';
            echo '<p style="margin: 0; color: #333;">';
            echo '<strong>' . esc_html__('Note:', 'garden-sheds-delivery') . '</strong> ';
            echo esc_html__('Home delivery may be possible. Contact us to see if we can arrange for home delivery.', 'garden-sheds-delivery');
            echo '</p>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Add checkout fields
     */
    public function add_checkout_fields($checkout) {
        if (!$this->cart_needs_delivery_selection()) {
            return;
        }

        echo '<div class="gsd-checkout-fields">';
        
        // Depot selection
        $courier_slug = $this->get_cart_courier();
        if ($courier_slug) {
            $courier = GSD_Courier::get_courier($courier_slug);
            // Only show depot selection if courier is enabled
            $is_enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
            
            if ($is_enabled) {
                $depots = GSD_Courier::get_depots($courier_slug);
                
                if (!empty($depots)) {
                    $depot_options = array('' => __('-- Select Depot --', 'garden-sheds-delivery'));
                    foreach ($depots as $depot) {
                        $depot_options[$depot['id']] = $depot['name'];
                    }

                    woocommerce_form_field('gsd_depot', array(
                        'type' => 'select',
                        'class' => array('form-row-wide'),
                        'label' => sprintf(__('%s Depot Location', 'garden-sheds-delivery'), $courier['name']),
                        'required' => !$this->cart_has_home_delivery_option(),
                        'options' => $depot_options,
                    ), $checkout->get_value('gsd_depot'));
                }
            }
        }

        // Home delivery option
        if ($this->cart_has_home_delivery_option()) {
            $home_delivery_price = $this->get_cart_home_delivery_price();
            
            woocommerce_form_field('gsd_home_delivery', array(
                'type' => 'checkbox',
                'class' => array('form-row-wide'),
                'label' => sprintf(__('Home Delivery (+%s)', 'garden-sheds-delivery'), wc_price($home_delivery_price)),
                'required' => false,
            ), $checkout->get_value('gsd_home_delivery'));
        }

        // Contact for delivery notice
        if ($this->cart_has_contact_for_delivery()) {
            echo '<div class="gsd-contact-delivery-notice" style="margin-top: 15px; padding: 12px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 3px;">';
            echo '<p style="margin: 0; color: #333;">';
            echo '<strong>' . esc_html__('Note:', 'garden-sheds-delivery') . '</strong> ';
            echo esc_html__('Home delivery may be possible. Contact us to see if we can arrange for home delivery.', 'garden-sheds-delivery');
            echo '</p>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Validate checkout fields
     */
    public function validate_checkout_fields() {
        if (!$this->cart_needs_delivery_selection()) {
            return;
        }

        $home_delivery = isset($_POST['gsd_home_delivery']) && $_POST['gsd_home_delivery'] === '1';
        $courier_slug = $this->get_cart_courier();
        $has_home_delivery_option = $this->cart_has_home_delivery_option();
        
        // If there's a courier with depots available
        if ($courier_slug) {
            // Depot is required if home delivery is not selected
            if (!$home_delivery && empty($_POST['gsd_depot'])) {
                wc_add_notice(__('Please select a depot location or choose home delivery.', 'garden-sheds-delivery'), 'error');
            }
        } elseif ($has_home_delivery_option) {
            // No courier assigned but home delivery is available
            // Home delivery MUST be selected
            if (!$home_delivery) {
                wc_add_notice(__('Please select home delivery for this product.', 'garden-sheds-delivery'), 'error');
            }
        }
    }

    /**
     * Save checkout fields to order
     */
    public function save_checkout_fields($order) {
        if (isset($_POST['gsd_depot'])) {
            $depot_id = sanitize_text_field($_POST['gsd_depot']);
            $order->update_meta_data('_gsd_depot', $depot_id);
            
            // Find and save depot name
            $courier_slug = $this->get_cart_courier();
            if ($courier_slug) {
                $depots = GSD_Courier::get_depots($courier_slug);
                foreach ($depots as $depot) {
                    if ($depot['id'] === $depot_id) {
                        $order->update_meta_data('_gsd_depot_name', $depot['name']);
                        break;
                    }
                }
                
                $courier = GSD_Courier::get_courier($courier_slug);
                $order->update_meta_data('_gsd_courier', $courier['name']);
            }
        }

        $home_delivery = isset($_POST['gsd_home_delivery']) && $_POST['gsd_home_delivery'] === '1';
        $order->update_meta_data('_gsd_home_delivery', $home_delivery ? 'yes' : 'no');
        
        if ($home_delivery) {
            $price = $this->get_cart_home_delivery_price();
            $order->update_meta_data('_gsd_home_delivery_price', $price);
        }

        // Save "contact for delivery" flag if applicable
        if ($this->cart_has_contact_for_delivery()) {
            $order->update_meta_data('_gsd_contact_for_delivery', 'yes');
        }
    }

    /**
     * Add home delivery fee to cart
     */
    public function add_home_delivery_fee($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!$this->cart_has_home_delivery_option()) {
            return;
        }

        $home_delivery = isset($_POST['gsd_home_delivery']) && $_POST['gsd_home_delivery'] === '1';
        
        if ($home_delivery) {
            $price = $this->get_cart_home_delivery_price();
            $cart->add_fee(__('Home Delivery', 'garden-sheds-delivery'), $price);
        }
    }
}
