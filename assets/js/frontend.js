/**
 * Garden Sheds Delivery - Frontend JavaScript
 * 
 * Handles shipping method selection and ensures checkout updates properly
 */

jQuery(document).ready(function($) {
    'use strict';

    // Check if sessionStorage is available
    var hasSessionStorage = (function() {
        try {
            var test = '__storage_test__';
            sessionStorage.setItem(test, test);
            sessionStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    })();

    /**
     * Show/hide depot dropdown based on selected shipping method
     */
    function toggleDepotDropdown() {
        // Get selected shipping method first
        var selectedMethod = $('input[name^="shipping_method"]:checked').val();
        
        // Toggle each depot dropdown wrapper
        $('.gsd-depot-dropdown-wrapper').each(function() {
            var $wrapper = $(this);
            var methodId = $wrapper.data('method-id');
            
            // Show if this dropdown matches the selected method, otherwise hide
            if (selectedMethod && selectedMethod === methodId) {
                $wrapper.show();
            } else {
                $wrapper.hide();
            }
        });
    }

    /**
     * Save depot selection to session storage
     */
    function saveDepotSelection(courier, depotId, depotName) {
        if (hasSessionStorage) {
            sessionStorage.setItem('gsd_depot_' + courier, depotId);
            sessionStorage.setItem('gsd_depot_name_' + courier, depotName);
        }
    }

    /**
     * Restore depot selection from session storage
     */
    function restoreDepotSelection() {
        if (hasSessionStorage) {
            $('.gsd-depot-select').each(function() {
                var courier = $(this).data('courier');
                var savedDepot = sessionStorage.getItem('gsd_depot_' + courier);
                if (savedDepot) {
                    $(this).val(savedDepot);
                }
            });
        }
    }

    // Toggle depot dropdown when shipping method changes
    $(document.body).on('change', 'input[name^="shipping_method"]', function() {
        toggleDepotDropdown();
        // Trigger checkout update to recalculate totals
        $(document.body).trigger('update_checkout');
    });

    // Save depot selection when changed
    $(document.body).on('change', '.gsd-depot-select', function() {
        var courier = $(this).data('courier');
        var depotId = $(this).val();
        var depotName = $(this).find('option:selected').data('name');
        
        saveDepotSelection(courier, depotId, depotName);
        
        // Trigger checkout update
        $(document.body).trigger('update_checkout');
    });

    // Initialize on page load
    toggleDepotDropdown();
    restoreDepotSelection();

    // Re-initialize after AJAX checkout update
    $(document.body).on('updated_checkout', function() {
        toggleDepotDropdown();
        restoreDepotSelection();
    });
});
