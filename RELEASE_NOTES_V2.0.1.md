# Version 2.0.1 Release Summary

**Release Date**: February 3, 2026  
**Previous Version**: 2.0  
**New Version**: 2.0.1

## Overview

This is a bug fix release that addresses a critical issue with the product delivery options checkbox functionality in the WordPress admin settings page.

## Bug Fix

### Issue: Delivery Option Checkbox State Not Updating

**Problem**: When managing delivery options for products within a category:
- Unchecking a single product's delivery option did not update the parent category checkbox to show an indeterminate state
- After saving, the product checkbox changes were lost and reverted to the category's state
- Users could not successfully override category-level settings for individual products

**Solution**: Added missing JavaScript event listener to handle product checkbox changes and update the parent category checkbox state in real-time.

**Impact**: Users can now successfully manage delivery options at the individual product level, and the UI correctly reflects mixed states within a category.

## Technical Changes

### Files Modified

1. **garden-sheds-delivery.php**
   - Updated plugin version from `2.0` to `2.0.1` in header
   - Updated `GSD_VERSION` constant from `2.0` to `2.0.1`

2. **includes/class-gsd-admin.php**
   - Added event listener for product checkbox changes (lines 483-499)
   - Includes null check for DOM element ID to prevent errors
   - Calls existing `updateCategoryCheckboxStates()` function to update UI

### Code Addition

```javascript
// Handle product checkbox changes - update category checkbox state when product checkboxes are clicked
$(document).on('change', '.gsd-product-home-delivery, .gsd-product-express-delivery, .gsd-product-contact-delivery', function() {
    var checkbox = $(this);
    var productRow = checkbox.closest('.gsd-product-row');
    var productsContainer = productRow.closest('.gsd-products-container');
    var productsRowDiv = productsContainer.closest('.gsd-products-row');
    
    // Extract category ID from the products row ID (format: gsd-products-{categoryId})
    var productsRowId = productsRowDiv.attr('id');
    if (!productsRowId) {
        return;
    }
    var categoryId = productsRowId.replace('gsd-products-', '');
    
    // Update the category checkbox state based on all product checkboxes
    updateCategoryCheckboxStates(categoryId);
});
```

## Testing

### Manual Testing Steps

To verify the fix:

1. Navigate to `/wp-admin/admin.php?page=garden-sheds-delivery`
2. Expand a category with multiple products (all should initially have matching delivery options)
3. Uncheck one product's "Home Delivery" checkbox
4. **Expected**: Category checkbox shows indeterminate state (dash icon)
5. Click "Save Product Settings"
6. Reload the page and expand the category again
7. **Expected**: The unchecked product remains unchecked

### Test Results

- ✅ Category checkbox correctly shows indeterminate state when some products are checked
- ✅ Category checkbox is unchecked when no products are checked
- ✅ Category checkbox is fully checked when all products are checked
- ✅ Product settings persist after save and page reload
- ✅ No JavaScript errors in browser console
- ✅ CodeQL security scan passed (no vulnerabilities detected)

## Upgrade Notes

### Backward Compatibility

This release is **100% backward compatible** with version 2.0:
- No database schema changes
- No changes to saved data structure
- No changes to API endpoints
- No changes to existing functionality
- Only adds missing UI event handling

### Installation

Standard WordPress plugin update process:
1. Deactivate the plugin (optional but recommended)
2. Replace plugin files with version 2.0.1
3. Reactivate the plugin
4. Clear browser cache to ensure JavaScript changes are loaded

**Note**: No special migration or configuration changes are required.

## Documentation

New documentation file added:
- `FIX_EXPLANATION_V2.0.1.md` - Detailed technical explanation of the fix

## Security

- ✅ CodeQL security analysis completed - No vulnerabilities found
- ✅ No changes to server-side data handling
- ✅ No changes to authentication or authorization
- ✅ JavaScript changes only affect UI state management
- ✅ Added defensive null checking for DOM operations

## Known Issues

None at this time.

## Credits

- Bug reported and fixed in response to user feedback
- Code review feedback incorporated to improve error handling

## Next Steps

Users experiencing the checkbox state issue should upgrade to version 2.0.1 immediately. The fix is minimal and surgical, with no risk to existing functionality.

---

For detailed technical explanation, see [FIX_EXPLANATION_V2.0.1.md](FIX_EXPLANATION_V2.0.1.md)
