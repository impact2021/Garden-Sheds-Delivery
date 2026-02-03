# Fix Explanation - Version 2.0.1

## Issue Description

When using the Garden Sheds Delivery admin settings page at `/wp-admin/admin.php?page=garden-sheds-delivery`:

1. **Expected Behavior**: When a category is expanded and one product's delivery option is deselected, the category checkbox should display an "indeterminate" state (a dash instead of a checkmark) to indicate that not all products in the category have that option enabled.

2. **Actual Behavior Before Fix**: 
   - The category checkbox remained fully checked even when individual products were deselected
   - After clicking "Save product settings", the page would reload and the product checkbox changes were reverted back to the category's state
   - Individual product settings were not being saved properly

## Root Cause Analysis

The issue was caused by **missing event listener** for product checkbox changes in the JavaScript code in `class-gsd-admin.php`.

### What Was Present:
- ✅ Event listener for **category** checkbox changes (line 449)
- ✅ Function `updateCategoryCheckboxStates()` to calculate and set the correct state (checked/unchecked/indeterminate) based on product checkboxes
- ✅ The function was called after AJAX operations (loading products and saving settings)

### What Was Missing:
- ❌ Event listener for **product** checkbox changes
- ❌ Real-time update of category checkbox state when individual product checkboxes were clicked

### The Flow Problem:

**Before Fix:**
1. User clicks category checkbox → All products update ✅
2. User clicks product checkbox → **Nothing happens to category checkbox** ❌
3. User saves → Category checkbox state doesn't reflect actual product states ❌

**After Fix:**
1. User clicks category checkbox → All products update ✅
2. User clicks product checkbox → Category checkbox immediately updates to show correct state (checked/indeterminate/unchecked) ✅
3. User saves → Settings are preserved correctly ✅

## The Fix

### Code Changes in `includes/class-gsd-admin.php`

Added a new event listener after line 481:

```javascript
// Handle product checkbox changes - update category checkbox state when product checkboxes are clicked
$(document).on('change', '.gsd-product-home-delivery, .gsd-product-express-delivery, .gsd-product-contact-delivery', function() {
    var checkbox = $(this);
    var productRow = checkbox.closest('.gsd-product-row');
    var productsContainer = productRow.closest('.gsd-products-container');
    var productsRowDiv = productsContainer.closest('.gsd-products-row');
    
    // Extract category ID from the products row ID (format: gsd-products-{categoryId})
    var categoryId = productsRowDiv.attr('id').replace('gsd-products-', '');
    
    // Update the category checkbox state based on all product checkboxes
    updateCategoryCheckboxStates(categoryId);
});
```

### How It Works:

1. **Listens** for changes on any product checkbox (home delivery, express delivery, or contact delivery)
2. **Traverses** the DOM to find the parent category ID
3. **Calls** `updateCategoryCheckboxStates(categoryId)` which:
   - Counts how many products have each delivery option checked
   - Sets the category checkbox to:
     - **Unchecked** if no products are checked
     - **Checked** if all products are checked
     - **Indeterminate** if some (but not all) products are checked

### Version Updates

Updated version from `2.0` to `2.0.1` in:
- `garden-sheds-delivery.php` (Plugin header comment block)
- `garden-sheds-delivery.php` (GSD_VERSION constant definition)

## Impact

### User Experience Improvements:
- ✅ Category checkboxes now accurately reflect the state of their products in real-time
- ✅ Users can now successfully deselect individual products from delivery options
- ✅ The indeterminate state correctly shows when some (but not all) products have an option enabled
- ✅ Product settings are properly saved and persist after page reload

### Technical Benefits:
- ✅ Minimal code change (14 lines added)
- ✅ Leverages existing `updateCategoryCheckboxStates()` function
- ✅ Consistent with existing code patterns
- ✅ No breaking changes to existing functionality
- ✅ No changes to database schema or AJAX endpoints

## Testing Recommendations

To verify the fix works correctly:

1. **Test Indeterminate State:**
   - Navigate to `/wp-admin/admin.php?page=garden-sheds-delivery`
   - Expand a category with multiple products
   - Ensure all products have "Home Delivery" checked
   - Uncheck one product's "Home Delivery" checkbox
   - ✅ Verify the category checkbox shows indeterminate state (dash)

2. **Test Save Functionality:**
   - Make changes to individual product checkboxes
   - Click "Save product settings"
   - Reload the page
   - ✅ Verify all changes are preserved

3. **Test All Unchecked:**
   - Uncheck all products in a category
   - ✅ Verify category checkbox becomes unchecked

4. **Test All Checked:**
   - Check all products in a category
   - ✅ Verify category checkbox becomes fully checked (no indeterminate state)

5. **Test Category Click:**
   - Click category checkbox
   - ✅ Verify all products update accordingly

## Files Modified

- `garden-sheds-delivery.php` - Version bump to 2.0.1
- `includes/class-gsd-admin.php` - Added product checkbox change event listener

## Backward Compatibility

This fix is fully backward compatible:
- No database changes
- No changes to saved data structure
- No changes to API endpoints
- Only adds missing UI functionality
