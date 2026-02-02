<?php
/**
 * Order Display Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Order {
    
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
        // Display delivery info in order details
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_order_delivery_info'));
        add_action('woocommerce_email_after_order_table', array($this, 'display_order_delivery_info_email'), 10, 4);
        
        // Add to admin order page
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_admin_order_delivery_info'));
    }

    /**
     * Display delivery info on order details page
     */
    public function display_order_delivery_info($order) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }

        $courier = $order->get_meta('_gsd_courier');
        $depot_name = $order->get_meta('_gsd_depot_name');
        $home_delivery = $order->get_meta('_gsd_home_delivery');
        $express_delivery = $order->get_meta('_gsd_express_delivery');

        if (empty($courier) && empty($depot_name) && $home_delivery !== 'yes' && $express_delivery !== 'yes') {
            return;
        }

        echo '<section class="woocommerce-delivery-details">';
        echo '<h2 class="woocommerce-column__title">' . esc_html__('Delivery Information', 'garden-sheds-delivery') . '</h2>';
        echo '<table class="woocommerce-table woocommerce-table--delivery-details shop_table delivery_details">';
        
        if ($courier) {
            echo '<tr>';
            echo '<th>' . esc_html__('Courier:', 'garden-sheds-delivery') . '</th>';
            echo '<td>' . esc_html($courier) . '</td>';
            echo '</tr>';
        }

        if ($home_delivery === 'yes') {
            $home_delivery_price = $order->get_meta('_gsd_home_delivery_price');
            echo '<tr>';
            echo '<th>' . esc_html__('Delivery Method:', 'garden-sheds-delivery') . '</th>';
            echo '<td>' . esc_html__('Home Delivery', 'garden-sheds-delivery');
            if ($home_delivery_price) {
                echo ' (' . wc_price($home_delivery_price) . ')';
            }
            echo '</td>';
            echo '</tr>';
        } elseif ($express_delivery === 'yes') {
            $express_delivery_price = $order->get_meta('_gsd_express_delivery_price');
            echo '<tr>';
            echo '<th>' . esc_html__('Delivery Method:', 'garden-sheds-delivery') . '</th>';
            echo '<td>' . esc_html__('Small Item Delivery', 'garden-sheds-delivery');
            if ($express_delivery_price) {
                echo ' (' . wc_price($express_delivery_price) . ')';
            }
            echo '</td>';
            echo '</tr>';
        } elseif ($depot_name) {
            echo '<tr>';
            echo '<th>' . esc_html__('Pickup Depot:', 'garden-sheds-delivery') . '</th>';
            echo '<td>' . esc_html($depot_name) . '</td>';
            echo '</tr>';
        }

        // Show contact for delivery notice if applicable
        $contact_for_delivery = $order->get_meta('_gsd_contact_for_delivery');
        if ($contact_for_delivery === 'yes') {
            echo '<tr>';
            echo '<th>' . esc_html__('Note:', 'garden-sheds-delivery') . '</th>';
            echo '<td><em>' . esc_html__('Home delivery may be an option - please contact us to discuss.', 'garden-sheds-delivery') . '</em></td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</section>';
    }

    /**
     * Display delivery info in order emails
     */
    public function display_order_delivery_info_email($order, $sent_to_admin, $plain_text, $email) {
        if ($plain_text) {
            $this->display_order_delivery_info_plain($order);
        } else {
            $this->display_order_delivery_info($order);
        }
    }

    /**
     * Display delivery info in plain text format
     */
    private function display_order_delivery_info_plain($order) {
        $courier = $order->get_meta('_gsd_courier');
        $depot_name = $order->get_meta('_gsd_depot_name');
        $home_delivery = $order->get_meta('_gsd_home_delivery');
        $express_delivery = $order->get_meta('_gsd_express_delivery');

        if (empty($courier) && empty($depot_name) && $home_delivery !== 'yes' && $express_delivery !== 'yes') {
            return;
        }

        echo "\n" . strtoupper(__('Delivery Information', 'garden-sheds-delivery')) . "\n\n";

        if ($courier) {
            echo __('Courier:', 'garden-sheds-delivery') . ' ' . $courier . "\n";
        }

        if ($home_delivery === 'yes') {
            $home_delivery_price = $order->get_meta('_gsd_home_delivery_price');
            echo __('Delivery Method:', 'garden-sheds-delivery') . ' ' . __('Home Delivery', 'garden-sheds-delivery');
            if ($home_delivery_price) {
                echo ' (' . wc_price($home_delivery_price) . ')';
            }
            echo "\n";
        } elseif ($express_delivery === 'yes') {
            $express_delivery_price = $order->get_meta('_gsd_express_delivery_price');
            echo __('Delivery Method:', 'garden-sheds-delivery') . ' ' . __('Small Item Delivery', 'garden-sheds-delivery');
            if ($express_delivery_price) {
                echo ' (' . wc_price($express_delivery_price) . ')';
            }
            echo "\n";
        } elseif ($depot_name) {
            echo __('Pickup Depot:', 'garden-sheds-delivery') . ' ' . $depot_name . "\n";
        }

        // Show contact for delivery notice if applicable
        $contact_for_delivery = $order->get_meta('_gsd_contact_for_delivery');
        if ($contact_for_delivery === 'yes') {
            echo __('Note:', 'garden-sheds-delivery') . ' ' . __('Home delivery may be an option - please contact us to discuss.', 'garden-sheds-delivery') . "\n";
        }
    }

    /**
     * Display delivery info in admin order page
     */
    public function display_admin_order_delivery_info($order) {
        $courier = $order->get_meta('_gsd_courier');
        $depot_name = $order->get_meta('_gsd_depot_name');
        $home_delivery = $order->get_meta('_gsd_home_delivery');
        $express_delivery = $order->get_meta('_gsd_express_delivery');

        if (empty($courier) && empty($depot_name) && $home_delivery !== 'yes' && $express_delivery !== 'yes') {
            return;
        }

        echo '<div class="order_data_column" style="clear:both;">';
        echo '<h3>' . esc_html__('Delivery Information', 'garden-sheds-delivery') . '</h3>';
        echo '<div class="address">';

        if ($courier) {
            echo '<p><strong>' . esc_html__('Courier:', 'garden-sheds-delivery') . '</strong> ' . esc_html($courier) . '</p>';
        }

        if ($home_delivery === 'yes') {
            $home_delivery_price = $order->get_meta('_gsd_home_delivery_price');
            echo '<p><strong>' . esc_html__('Delivery Method:', 'garden-sheds-delivery') . '</strong> ' . esc_html__('Home Delivery', 'garden-sheds-delivery');
            if ($home_delivery_price) {
                echo ' (' . wc_price($home_delivery_price) . ')';
            }
            echo '</p>';
        } elseif ($express_delivery === 'yes') {
            $express_delivery_price = $order->get_meta('_gsd_express_delivery_price');
            echo '<p><strong>' . esc_html__('Delivery Method:', 'garden-sheds-delivery') . '</strong> ' . esc_html__('Small Item Delivery', 'garden-sheds-delivery');
            if ($express_delivery_price) {
                echo ' (' . wc_price($express_delivery_price) . ')';
            }
            echo '</p>';
        } elseif ($depot_name) {
            echo '<p><strong>' . esc_html__('Pickup Depot:', 'garden-sheds-delivery') . '</strong> ' . esc_html($depot_name) . '</p>';
        }

        // Show contact for delivery notice if applicable
        $contact_for_delivery = $order->get_meta('_gsd_contact_for_delivery');
        if ($contact_for_delivery === 'yes') {
            echo '<p style="padding: 10px; background: #e7f3ff; border-left: 3px solid #2196F3;">';
            echo '<strong>' . esc_html__('Note:', 'garden-sheds-delivery') . '</strong> ';
            echo '<em>' . esc_html__('Home delivery may be an option - customer should contact us to discuss.', 'garden-sheds-delivery') . '</em>';
            echo '</p>';
        }

        echo '</div>';
        echo '</div>';
    }
}
