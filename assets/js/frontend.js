/**
 * Garden Sheds Delivery - Frontend JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    // Update checkout when delivery options change
    var updateCheckout = function() {
        $('body').trigger('update_checkout');
    };

    // Depot selection change
    $(document.body).on('change', '#gsd_depot', function() {
        updateCheckout();
    });

    // Home delivery checkbox change
    $(document.body).on('change', '#gsd_home_delivery', function() {
        var isChecked = $(this).is(':checked');
        var $depotField = $('#gsd_depot');
        
        // Make depot optional if home delivery is selected
        if (isChecked) {
            $depotField.prop('required', false);
            $depotField.closest('.form-row').removeClass('validate-required');
        } else {
            $depotField.prop('required', true);
            $depotField.closest('.form-row').addClass('validate-required');
        }
        
        updateCheckout();
    });

    // Initialize on page load
    if ($('#gsd_home_delivery').is(':checked')) {
        $('#gsd_depot').prop('required', false);
        $('#gsd_depot').closest('.form-row').removeClass('validate-required');
    }
});
