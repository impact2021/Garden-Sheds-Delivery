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
        
        // Add submenu for depot locations
        add_submenu_page(
            'garden-sheds-delivery',
            __('Depot Locations', 'garden-sheds-delivery'),
            __('Depot Locations', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery-depots',
            array($this, 'depots_page')
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
        register_setting('gsd_settings', 'gsd_contact_delivery_categories');
        register_setting('gsd_settings', 'gsd_main_freight_categories');
        register_setting('gsd_settings', 'gsd_pbt_categories');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['gsd_save_settings']) && check_admin_referer('gsd_save_settings')) {
            $this->save_delivery_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $selected_home_delivery = get_option('gsd_home_delivery_categories', array());
        $selected_contact_delivery = get_option('gsd_contact_delivery_categories', array());
        $selected_main_freight = get_option('gsd_main_freight_categories', array());
        $selected_pbt = get_option('gsd_pbt_categories', array());
        
        // Ensure all selected values are arrays
        $selected_home_delivery = is_array($selected_home_delivery) ? $selected_home_delivery : array();
        $selected_contact_delivery = is_array($selected_contact_delivery) ? $selected_contact_delivery : array();
        $selected_main_freight = is_array($selected_main_freight) ? $selected_main_freight : array();
        $selected_pbt = is_array($selected_pbt) ? $selected_pbt : array();
        
        $default_cost = get_option('gsd_default_home_delivery_cost', '150');
        
        // Get all product categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Shed Delivery Settings', 'garden-sheds-delivery'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_settings'); ?>
                
                <h2><?php echo esc_html__('Delivery Options by Category', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Configure delivery options for each product category.', 'garden-sheds-delivery'); ?></p>
                
                <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Category', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Home Delivery', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Might be able to offer home delivery', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('Main Freight', 'garden-sheds-delivery'); ?></th>
                            <th style="text-align: center;"><?php echo esc_html__('PBT', 'garden-sheds-delivery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($category->name); ?></strong></td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_home_delivery_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_home_delivery)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_contact_delivery_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_contact_delivery)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_main_freight_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_main_freight)); ?> />
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="gsd_pbt_categories[]" 
                                       value="<?php echo esc_attr($category->term_id); ?>"
                                       <?php checked(in_array($category->term_id, $selected_pbt)); ?> />
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                    <p><?php echo esc_html__('No product categories found.', 'garden-sheds-delivery'); ?></p>
                <?php endif; ?>
                
                <hr style="margin: 30px 0;" />
                
                <h2><?php echo esc_html__('Default Costs', 'garden-sheds-delivery'); ?></h2>
                
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
                </table>
                
                <p class="submit">
                    <input type="submit" name="gsd_save_settings" class="button-primary" value="<?php echo esc_attr__('Save Settings', 'garden-sheds-delivery'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Depot locations page
     */
    public function depots_page() {
        if (isset($_POST['gsd_save_depots']) && check_admin_referer('gsd_save_depots')) {
            $this->save_couriers();
            echo '<div class="notice notice-success"><p>' . esc_html__('Depot locations saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $couriers = GSD_Courier::get_couriers();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Courier Depot Locations', 'garden-sheds-delivery'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_depots'); ?>
                
                <h2><?php echo esc_html__('Courier Companies and Depots', 'garden-sheds-delivery'); ?></h2>
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
                    <input type="submit" name="gsd_save_depots" class="button-primary" value="<?php echo esc_attr__('Save Depot Locations', 'garden-sheds-delivery'); ?>" />
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
        $this->save_category_option('gsd_home_delivery_categories');

        // Save contact delivery categories
        $this->save_category_option('gsd_contact_delivery_categories');

        // Save main freight categories
        $this->save_category_option('gsd_main_freight_categories');

        // Save PBT categories
        $this->save_category_option('gsd_pbt_categories');

        // Save default home delivery cost
        $cost = isset($_POST['gsd_default_home_delivery_cost']) 
            ? sanitize_text_field($_POST['gsd_default_home_delivery_cost']) 
            : '150';
        update_option('gsd_default_home_delivery_cost', $cost);
    }

    /**
     * Helper method to save category array options
     */
    private function save_category_option($option_name) {
        $categories = isset($_POST[$option_name]) && is_array($_POST[$option_name]) 
            ? array_map('intval', $_POST[$option_name]) 
            : array();
        update_option($option_name, $categories);
    }
}
