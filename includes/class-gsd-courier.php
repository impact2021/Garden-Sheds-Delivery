<?php
/**
 * Courier Management Class
 *
 * @package GardenShedsDelivery
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSD_Courier {
    
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
        // Hook for managing couriers in admin
    }

    /**
     * Get all courier companies
     */
    public static function get_couriers() {
        $couriers = get_option('gsd_courier_companies', array());
        return $couriers;
    }

    /**
     * Get a specific courier by slug
     */
    public static function get_courier($slug) {
        $couriers = self::get_couriers();
        return isset($couriers[$slug]) ? $couriers[$slug] : null;
    }

    /**
     * Get depots for a specific courier
     */
    public static function get_depots($courier_slug) {
        $courier = self::get_courier($courier_slug);
        return $courier && isset($courier['depots']) ? $courier['depots'] : array();
    }

    /**
     * Update courier companies
     */
    public static function update_couriers($couriers) {
        return update_option('gsd_courier_companies', $couriers);
    }

    /**
     * Add a new courier
     */
    public static function add_courier($name, $slug, $depots = array()) {
        $couriers = self::get_couriers();
        $couriers[$slug] = array(
            'name' => $name,
            'slug' => $slug,
            'depots' => $depots
        );
        return self::update_couriers($couriers);
    }

    /**
     * Delete a courier
     */
    public static function delete_courier($slug) {
        $couriers = self::get_couriers();
        if (isset($couriers[$slug])) {
            unset($couriers[$slug]);
            return self::update_couriers($couriers);
        }
        return false;
    }
}
