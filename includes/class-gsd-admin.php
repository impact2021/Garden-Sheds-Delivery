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
        add_submenu_page(
            'woocommerce',
            __('Garden Sheds Delivery', 'garden-sheds-delivery'),
            __('Shed Delivery', 'garden-sheds-delivery'),
            'manage_woocommerce',
            'garden-sheds-delivery',
            array($this, 'settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('gsd_settings', 'gsd_courier_companies');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['gsd_save_couriers']) && check_admin_referer('gsd_save_couriers')) {
            $this->save_couriers();
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'garden-sheds-delivery') . '</p></div>';
        }

        $couriers = GSD_Courier::get_couriers();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Garden Sheds Delivery Settings', 'garden-sheds-delivery'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsd_save_couriers'); ?>
                
                <h2><?php echo esc_html__('Courier Companies', 'garden-sheds-delivery'); ?></h2>
                <p><?php echo esc_html__('Manage courier companies and their depot locations.', 'garden-sheds-delivery'); ?></p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Courier Name', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Slug', 'garden-sheds-delivery'); ?></th>
                            <th><?php echo esc_html__('Depot Locations', 'garden-sheds-delivery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($couriers as $slug => $courier) : ?>
                        <tr>
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
}
