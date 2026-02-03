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
        
        // Add depot dropdown after shipping method
        add_action('woocommerce_after_shipping_rate', array($this, 'add_depot_dropdown'), 10, 2);
        
        // Validate depot selection
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_depot_selection'), 10, 2);
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
            
            // Check if this is one of our shipping methods (old or new)
            if ($method_id === 'garden_sheds_delivery' || 
                $method_id === 'gsd_home_delivery' || 
                $method_id === 'gsd_depot_pbt' || 
                $method_id === 'gsd_depot_mainfreight' || 
                $method_id === 'gsd_small_items' || 
                $method_id === 'gsd_contact_delivery') {
                
                $instance_id = $shipping_method->get_instance_id();
                $rate_id = $shipping_method->get_id(); // Full rate ID including any suffix
                
                // Extract meta data from the shipping method
                $meta_data = $shipping_method->get_meta_data();
                
                // Check if this is a depot pickup or home delivery or express delivery
                if ($method_id === 'gsd_depot_pbt' || $method_id === 'gsd_depot_mainfreight') {
                    // New depot pickup methods - get selected depot from POST
                    $courier_slug = '';
                    $courier_name = '';
                    $depots = array();
                    
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'courier_slug') {
                            $courier_slug = $data['value'];
                        } elseif ($data['key'] === 'courier_name') {
                            $courier_name = $data['value'];
                        } elseif ($data['key'] === 'depots') {
                            $depots = $data['value'];
                        }
                    }
                    
                    // Validate courier slug before using in POST key
                    $allowed_couriers = array('main_freight', 'pbt');
                    if (!in_array($courier_slug, $allowed_couriers, true)) {
                        continue;
                    }
                    
                    // Get selected depot from POST data
                    $selected_depot_id = isset($_POST['gsd_depot_' . $courier_slug]) ? sanitize_text_field($_POST['gsd_depot_' . $courier_slug]) : '';
                    $selected_depot_name = '';
                    
                    // Find depot name from ID
                    if (!empty($selected_depot_id) && !empty($depots)) {
                        foreach ($depots as $depot) {
                            if (isset($depot['id']) && $depot['id'] === $selected_depot_id) {
                                $selected_depot_name = $depot['name'];
                                break;
                            }
                        }
                    }
                    
                    // Save to session for next page load
                    WC()->session->set('gsd_selected_depot_' . $courier_slug, $selected_depot_id);
                    
                    if (!empty($selected_depot_id) && !empty($selected_depot_name)) {
                        $order->update_meta_data('_gsd_depot', $selected_depot_id);
                        $order->update_meta_data('_gsd_depot_name', $selected_depot_name);
                        $order->update_meta_data('_gsd_courier', $courier_name);
                    }
                    
                    $order->update_meta_data('_gsd_home_delivery', 'no');
                    $order->update_meta_data('_gsd_express_delivery', 'no');
                } elseif (strpos($rate_id, ':depot:') !== false) {
                    // Old depot pickup format
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
                } elseif (strpos($rate_id, ':home_delivery') !== false || $method_id === 'gsd_home_delivery') {
                    // Home delivery
                    $order->update_meta_data('_gsd_home_delivery', 'yes');
                    $order->update_meta_data('_gsd_express_delivery', 'no');
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'home_delivery_price') {
                            $order->update_meta_data('_gsd_home_delivery_price', $data['value']);
                        }
                    }
                } elseif (strpos($rate_id, ':express_delivery') !== false || $method_id === 'gsd_small_items') {
                    // Small item delivery
                    $order->update_meta_data('_gsd_home_delivery', 'no');
                    $order->update_meta_data('_gsd_express_delivery', 'yes');
                    foreach ($meta_data as $meta) {
                        $data = $meta->get_data();
                        if ($data['key'] === 'express_delivery_price') {
                            $order->update_meta_data('_gsd_express_delivery_price', $data['value']);
                        }
                    }
                } elseif ($method_id === 'gsd_contact_delivery') {
                    // Contact for delivery
                    $order->update_meta_data('_gsd_contact_delivery', 'yes');
                    $order->update_meta_data('_gsd_home_delivery', 'no');
                    $order->update_meta_data('_gsd_express_delivery', 'no');
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
        $method_id = $method->get_method_id();
        
        // Only customize our shipping methods
        if ($method_id !== 'garden_sheds_delivery' && 
            $method_id !== 'gsd_home_delivery' && 
            $method_id !== 'gsd_depot_pbt' && 
            $method_id !== 'gsd_depot_mainfreight' && 
            $method_id !== 'gsd_small_items' && 
            $method_id !== 'gsd_contact_delivery') {
            return $label;
        }

        $rate_id = $method->get_id();
        
        // Check if this is home delivery
        if (strpos($rate_id, ':home_delivery') !== false || $method_id === 'gsd_home_delivery') {
            $label = $this->build_delivery_label(__('Home Delivery', 'garden-sheds-delivery'), $method);
        } elseif (strpos($rate_id, ':express_delivery') !== false || $method_id === 'gsd_small_items') {
            // Check if this is express/small item delivery
            $label = $this->build_delivery_label(__('Small Item Delivery', 'garden-sheds-delivery'), $method);
        } elseif (strpos($rate_id, ':depot:') !== false || $method_id === 'gsd_depot_pbt' || $method_id === 'gsd_depot_mainfreight') {
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

        // Get the shipping package once
        $packages = WC()->cart->get_shipping_packages();
        if (empty($packages)) {
            return;
        }
        $package = $packages[0];

        // Check each chosen shipping method
        foreach ($chosen_methods as $chosen_method) {
            // Check if this is home delivery (old or new method)
            if ((strpos($chosen_method, 'garden_sheds_delivery') !== false && strpos($chosen_method, ':home_delivery') !== false) ||
                strpos($chosen_method, 'gsd_home_delivery') !== false) {
                // Validate that products in package actually support home delivery
                if ($this->package_has_home_delivery($package)) {
                    $home_delivery_price = $this->get_delivery_price_from_package($package, 'home');
                    
                    if ($home_delivery_price > 0) {
                        WC()->cart->add_fee(__('Home Delivery', 'garden-sheds-delivery'), $home_delivery_price, true);
                    }
                }
                break;
            }
            // Check if this is small item delivery (old or new method)
            elseif ((strpos($chosen_method, 'garden_sheds_delivery') !== false && strpos($chosen_method, ':express_delivery') !== false) ||
                    strpos($chosen_method, 'gsd_small_items') !== false) {
                // Validate that products in package actually support small item delivery
                if ($this->package_has_express_delivery($package)) {
                    $express_delivery_price = $this->get_delivery_price_from_package($package, 'express');
                    
                    if ($express_delivery_price > 0) {
                        WC()->cart->add_fee(__('Small Item Delivery', 'garden-sheds-delivery'), $express_delivery_price, true);
                    }
                }
                break;
            }
        }
    }

    /**
     * Get delivery price from package
     * 
     * Helper method to retrieve delivery price for a given package
     *
     * @param array $package The shipping package
     * @param string $type Type of delivery: 'home' or 'express'
     * @return float The delivery price
     */
    private function get_delivery_price_from_package($package, $type) {
        static $shipping_method = null;
        
        // Create shipping method instance only once
        if ($shipping_method === null) {
            $shipping_method = new GSD_Shipping_Method();
        }
        
        if ($type === 'home') {
            return $shipping_method->get_package_home_delivery_price($package);
        } elseif ($type === 'express') {
            return $shipping_method->get_package_express_delivery_price($package);
        }
        
        return 0;
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
     * Check if package has express/small item delivery option
     *
     * @param array $package Package information
     * @return bool
     */
    private function package_has_express_delivery($package) {
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            if (GSD_Product_Settings::is_express_delivery_available($product_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add depot dropdown after shipping rate
     *
     * @param WC_Shipping_Rate $method The shipping method
     * @param int $index The index of the shipping method
     */
    public function add_depot_dropdown($method, $index) {
        $method_id = $method->get_method_id();
        
        // Only add dropdown for our depot pickup methods
        if ($method_id !== 'gsd_depot_mainfreight' && $method_id !== 'gsd_depot_pbt') {
            return;
        }
        
        // Get depot information from meta data
        $meta_data = $method->get_meta_data();
        $depots = array();
        $courier_slug = '';
        
        foreach ($meta_data as $key => $value) {
            if ($key === 'depots') {
                $depots = $value;
            }
            if ($key === 'courier_slug') {
                $courier_slug = $value;
            }
        }
        
        if (empty($depots) || !is_array($depots)) {
            return;
        }
        
        // Get selected depot from session
        $selected_depot = WC()->session->get('gsd_selected_depot_' . $courier_slug, '');
        
        ?>
        <div class="gsd-depot-dropdown-wrapper" data-method-id="<?php echo esc_attr($method->get_id()); ?>">
            <label for="gsd_depot_<?php echo esc_attr($courier_slug); ?>">
                <?php esc_html_e('Select depot location:', 'garden-sheds-delivery'); ?>
            </label>
            <select name="gsd_depot_<?php echo esc_attr($courier_slug); ?>" 
                    id="gsd_depot_<?php echo esc_attr($courier_slug); ?>" 
                    class="gsd-depot-select" 
                    data-courier="<?php echo esc_attr($courier_slug); ?>">
                <option value=""><?php esc_html_e('-- Select a depot --', 'garden-sheds-delivery'); ?></option>
                <?php foreach ($depots as $depot) : ?>
                    <?php if (isset($depot['id']) && isset($depot['name'])) : ?>
                        <option value="<?php echo esc_attr($depot['id']); ?>" 
                                data-name="<?php echo esc_attr($depot['name']); ?>"
                                <?php selected($selected_depot, $depot['id']); ?>>
                            <?php echo esc_html($depot['name']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Validate depot selection during checkout
     *
     * @param array $data Posted data
     * @param WP_Error $errors Errors object
     */
    public function validate_depot_selection($data, $errors) {
        // Get chosen shipping methods
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
        
        if (empty($chosen_methods)) {
            return;
        }
        
        foreach ($chosen_methods as $chosen_method) {
            // Check if this is a depot pickup method
            if (strpos($chosen_method, 'gsd_depot_mainfreight') !== false) {
                $selected_depot = isset($_POST['gsd_depot_main_freight']) ? sanitize_text_field($_POST['gsd_depot_main_freight']) : '';
                
                if (empty($selected_depot)) {
                    $errors->add('shipping', __('Please select a Mainfreight depot location.', 'garden-sheds-delivery'));
                }
            } elseif (strpos($chosen_method, 'gsd_depot_pbt') !== false) {
                $selected_depot = isset($_POST['gsd_depot_pbt']) ? sanitize_text_field($_POST['gsd_depot_pbt']) : '';
                
                if (empty($selected_depot)) {
                    $errors->add('shipping', __('Please select a PBT depot location.', 'garden-sheds-delivery'));
                }
            }
        }
    }
}
