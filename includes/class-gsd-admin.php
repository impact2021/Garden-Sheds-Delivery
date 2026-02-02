<?php
/**
 * Admin Settings Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Shed Delivery', 'garden-sheds-delivery'),
            __('Shed Delivery', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery',
            array($this, 'settings_page'),
            'dashicons-store',
            56
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('gsd_settings', 'gsd_courier_companies');
        register_setting('gsd_settings', 'gsd_home_delivery_categories');
        register_setting('gsd_settings', 'gsd_default_home_delivery_cost');
        register_setting('gsd_settings', 'gsd_show_contact_for_delivery');
        register_setting('gsd_settings', 'gsd_express_delivery_categories');
        register_setting('gsd_settings', 'gsd_default_express_delivery_cost');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['gsd_save_couriers']) && check_admin_referer('gsd_save_couriers')) {
            $this->save_couriers();
            $this->save_delivery_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $couriers = GSD_Courier::get_couriers();
        $selected_categories = get_option('gsd_home_delivery_categories', array());
        $default_cost = get_option('gsd_default_home_delivery_cost', '150');
        $show_contact_option = get_option('gsd_show_contact_for_delivery', false);
        $express_delivery_categories = get_option('gsd_express_delivery_categories', array());
        $express_delivery_cost = get_option('gsd_default_express_delivery_cost', '15');
        
        // Get all product categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Shed Delivery Settings', 'garden-sheds-delivery'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_couriers'); ?>
                
                <h2><?php echo esc_html__('Home Delivery Options', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Configure which product categories should offer home delivery and set the default cost.', 'garden-sheds-delivery'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Default Home Delivery Cost', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="gsd_default_home_delivery_cost" 
                                   value="<?php echo esc_attr($default_cost); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('Default cost for home delivery. This can be overridden per-product.', 'garden-sheds-delivery'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Categories with Home Delivery', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                                <fieldset>
                                    <?php foreach ($categories as $category) : ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input type="checkbox" 
                                                   name="gsd_home_delivery_categories[]" 
                                                   value="<?php echo esc_attr($category->term_id); ?>"
                                                   <?php checked(in_array($category->term_id, $selected_categories)); ?> />
                                            <?php echo esc_html($category->name); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description">
                                    <?php echo esc_html__('Select categories where products should automatically have home delivery available.', 'garden-sheds-delivery'); ?>
                                </p>
                            <?php else : ?>
                                <p><?php echo esc_html__('No product categories found.', 'garden-sheds-delivery'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Show Contact for Delivery Option', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="gsd_show_contact_for_delivery" 
                                       value="1"
                                       <?php checked($show_contact_option, true); ?> />
                                <?php echo esc_html__('Show "Home delivery may be possible. Contact us to see if we can arrange for home delivery." message', 'garden-sheds-delivery'); ?>
                            </label>
                            <p class="description">
                                <?php echo esc_html__('When enabled, this message will be displayed on cart and checkout pages for products with delivery options.', 'garden-sheds-delivery'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <hr style="margin: 30px 0;" />
                
                <h2><?php echo esc_html__('Express Delivery Options', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Configure an alternative paid delivery option for specific product categories.', 'garden-sheds-delivery'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Default Express Delivery Cost', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   name="gsd_default_express_delivery_cost" 
                                   value="<?php echo esc_attr($express_delivery_cost); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('Default cost for express delivery option. This can be overridden per-product.', 'garden-sheds-delivery'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html__('Categories with Express Delivery', 'garden-sheds-delivery'); ?></label>
                        </th>
                        <td>
                            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                                <fieldset>
                                    <?php foreach ($categories as $category) : ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input type="checkbox" 
                                                   name="gsd_express_delivery_categories[]" 
                                                   value="<?php echo esc_attr($category->term_id); ?>"
                                                   <?php checked(in_array($category->term_id, $express_delivery_categories)); ?> />
                                            <?php echo esc_html($category->name); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description">
                                    <?php echo esc_html__('Select categories where products should have express delivery available.', 'garden-sheds-delivery'); ?>
                                </p>
                            <?php else : ?>
                                <p><?php echo esc_html__('No product categories found.', 'garden-sheds-delivery'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <hr style="margin: 30px 0;" />
                
                <h2><?php echo esc_html__('Courier Companies', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Manage courier companies and their depot locations.', 'garden-sheds-delivery'); ?></p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php echo esc_html__('Enabled', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Courier Name', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Slug', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Depot Locations', 'garden-sheds-delivery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($couriers as $slug => $courier) : ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php 
                                $enabled = isset($courier['enabled']) ? $courier['enabled'] : true;
                                ?>
                                <input type="checkbox" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][enabled]" 
                                       value="1"
                                       <?php checked($enabled, true); ?> />
                            </td>
                            <td>
                                <input type="text" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][name]" 
                                       value="<?php echo esc_attr($courier['name']); ?>" 
                                       class="regular-text" />
                            </td>
                            <td>
                                <code><?php echo esc_html($slug); ?></code>
                                <input type="hidden" 
                                       name="couriers[<?php echo esc_attr($slug); ?>][slug]" 
                                       value="<?php echo esc_attr($slug); ?>" />
                            </td>
                            <td>
                                <div class="gsd-depots">
                                    <?php 
                                    $depots = isset($courier['depots']) ? $courier['depots'] : array();
                                    foreach ($depots as $index => $depot) : 
                                    ?>
                                    <div class="gsd-depot-row" style="margin-bottom: 10px;">
                                        <input type="text" 
                                               name="couriers[<?php echo esc_attr($slug); ?>][depots][<?php echo esc_attr($index); ?>][name]" 
                                               value="<?php echo esc_attr($depot['name']); ?>" 
                                               placeholder="<?php echo esc_attr__('Depot Name', 'garden-sheds-delivery'); ?>" 
                                               class="regular-text" />
                                        <input type="hidden" 
                                               name="couriers[<?php echo esc_attr($slug); ?>][depots][<?php echo esc_attr($index); ?>][id]" 
                                               value="<?php echo esc_attr($depot['id']); ?>" />
                                    </div>
                                    <?php endforeach; ?>
                                    <button type="button" class="button gsd-add-depot" data-courier="<?php echo esc_attr($slug); ?>">
                                        <?php echo esc_html__('+ Add Depot', 'garden-sheds-delivery'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="gsd_save_couriers" class="button-primary" value="<?php echo esc_attr__('Save Settings', 'garden-sheds-delivery'); ?>" />
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.gsd-add-depot').on('click', function() {
                var courier = $(this).data('courier');
                var container = $(this).prev('.gsd-depot-row').length ? $(this).prevAll('.gsd-depot-row:first') : $(this).parent();
                var index = container.find('.gsd-depot-row').length;
                var depotId = courier + '_depot_' + (index + 1);
                
                var html = '<div class="gsd-depot-row" style="margin-bottom: 10px;">' +
                    '<input type="text" name="couriers[' + courier + '][depots][' + index + '][name]" ' +
                    'placeholder="<?php echo esc_js(__('Depot Name', 'garden-sheds-delivery')); ?>" class="regular-text" />' +
                    '<input type="hidden" name="couriers[' + courier + '][depots][' + index + '][id]" value="' + depotId + '" />' +
                    '</div>';
                
                $(this).before(html);
            });
        });
        </script>

        <style>
        .gsd-depot-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .gsd-depots {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 3px;
        }
        </style>
        <?php
    }

    /**
     * Save couriers
     */
    private function save_couriers() {
        if (!isset($_POST['couriers']) || !is_array($_POST['couriers'])) {
            return;
        }

        $couriers = array();
        foreach ($_POST['couriers'] as $slug => $courier_data) {
            $slug = sanitize_key($slug);
            $couriers[$slug] = array(
                'name' => sanitize_text_field($courier_data['name']),
                'slug' => sanitize_key($courier_data['slug']),
                'enabled' => isset($courier_data['enabled']) && $courier_data['enabled'] === '1',
                'depots' => array(),
            );

            if (isset($courier_data['depots']) && is_array($courier_data['depots'])) {
                foreach ($courier_data['depots'] as $depot) {
                    if (!empty($depot['name'])) {
                        $couriers[$slug]['depots'][] = array(
                            'id' => sanitize_key($depot['id']),
                            'name' => sanitize_text_field($depot['name']),
                        );
                    }
                }
            }
        }

        GSD_Courier::update_couriers($couriers);
    }

    /**
     * Save delivery settings
     */
    private function save_delivery_settings() {
        // Save home delivery categories
        $categories = isset($_POST['gsd_home_delivery_categories']) && is_array($_POST['gsd_home_delivery_categories']) 
            ? array_map('intval', $_POST['gsd_home_delivery_categories']) 
            : array();
        update_option('gsd_home_delivery_categories', $categories);

        // Save default home delivery cost
        $cost = isset($_POST['gsd_default_home_delivery_cost']) 
            ? sanitize_text_field($_POST['gsd_default_home_delivery_cost']) 
            : '150';
        update_option('gsd_default_home_delivery_cost', $cost);

        // Save show contact for delivery option
        $show_contact = isset($_POST['gsd_show_contact_for_delivery']) && $_POST['gsd_show_contact_for_delivery'] === '1';
        update_option('gsd_show_contact_for_delivery', $show_contact);

        // Save express delivery categories
        $express_categories = isset($_POST['gsd_express_delivery_categories']) && is_array($_POST['gsd_express_delivery_categories']) 
            ? array_map('intval', $_POST['gsd_express_delivery_categories']) 
            : array();
        update_option('gsd_express_delivery_categories', $express_categories);

        // Save default express delivery cost
        $express_cost = isset($_POST['gsd_default_express_delivery_cost']) 
            ? sanitize_text_field($_POST['gsd_default_express_delivery_cost']) 
            : '15';
        update_option('gsd_default_express_delivery_cost', $express_cost);
    }
}
