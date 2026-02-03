/**
 * Garden Sheds Delivery - Frontend JavaScript
 * 
 * Handles shipping method selection and ensures checkout updates properly
 */

jQuery(document).ready(function($) {
    'use strict';

    /**
     * Show/hide depot dropdown based on selected shipping method
     */
    function toggleDepotDropdown() {
        // Hide all depot dropdowns first
        $('.gsd-depot-dropdown-wrapper').hide();
        
        // Get selected shipping method
        var selectedMethod = $('input[name^="shipping_method"]:checked').val();
        
        if (selectedMethod) {
            // Find and show the corresponding depot dropdown
            $('.gsd-depot-dropdown-wrapper').each(function() {
                var methodId = $(this).data('method-id');
                if (selectedMethod === methodId) {
                    $(this).show();
                }
            });
        }
    }

    /**
     * Save depot selection to session via AJAX
     */
    function saveDepotSelection(courier, depotId, depotName) {
        // Store in session storage for persistence
        try {
            sessionStorage.setItem('gsd_depot_' + courier, depotId);
            sessionStorage.setItem('gsd_depot_name_' + courier, depotName);
        } catch (e) {
            // Session storage not available - log warning but continue
            console.warn('Session storage unavailable for depot selection:', e);
        }
    }

    /**
     * Restore depot selection from session storage
     */
    function restoreDepotSelection() {
        $('.gsd-depot-select').each(function() {
            var courier = $(this).data('courier');
            try {
                var savedDepot = sessionStorage.getItem('gsd_depot_' + courier);
                if (savedDepot) {
                    $(this).val(savedDepot);
                }
            } catch (e) {
                // Session storage not available - log warning but continue
                console.warn('Session storage unavailable for depot restoration:', e);
            }
        });
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
