/**
 * Garden Sheds Delivery - Frontend JavaScript
 * 
 * Handles shipping method selection and ensures checkout updates properly
 */

jQuery(document).ready(function($) {
    'use strict';

    // Ensure checkout updates when shipping method changes
    $(document.body).on('change', 'input[name^="shipping_method"]', function() {
        // Trigger checkout update to recalculate totals
        $(document.body).trigger('update_checkout');
    });
    
    // Additional fallback for radio button changes
    $(document).on('change', 'input[type="radio"][name^="shipping_method"]', function() {
        // Force WooCommerce to update the order total
        $(document.body).trigger('update_checkout');
    });
});
