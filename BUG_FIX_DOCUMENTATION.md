# Critical Bug Fix: Product Shipping Settings Not Saving

## Problem Statement

Product-level shipping settings (home delivery, express delivery, contact delivery) were not being saved correctly. When a user:

1. Expanded a category to view products
2. Unchecked a product's delivery option (e.g., home delivery)
3. Reloaded the page

The unchecked option would revert to being checked again, as if the change was never saved.

## Root Cause Analysis

### The Bug

The bug was in the `ajax_save_product_shipping()` method in `/includes/class-gsd-admin.php`.

**Original (Broken) Code:**
```php
$home_delivery = !empty($product_data['home_delivery']) ? 'yes' : 'no';
```

### Why This Was Broken

When jQuery sends AJAX data with boolean values:

```javascript
home_delivery: false  // Checkbox unchecked
```

jQuery serializes this boolean `false` as the **string** `'false'`, not the boolean value.

In PHP, the expression `!empty('false')` evaluates to `true` because:
- The string `'false'` is not an empty string
- Any non-empty string is truthy in PHP
- Therefore `!empty('false')` returns `true`

**Result:** An unchecked checkbox (boolean `false` → string `'false'`) was being saved as `'yes'`!

### Proof of Bug

```php
// What happens with the old code:
$value = 'false';  // jQuery serializes boolean false as string 'false'
$result = !empty($value) ? 'yes' : 'no';
echo $result;  // Outputs: "yes" ❌ WRONG!
```

## The Fix

### Two-Part Solution

#### Part 1: JavaScript - Send Integers Instead of Booleans

**Changed from:**
```javascript
productSettings.push({
    product_id: productId,
    home_delivery: row.find('.gsd-product-home-delivery').is(':checked'),  // Returns boolean
    // ...
});
```

**Changed to:**
```javascript
productSettings.push({
    product_id: productId,
    home_delivery: row.find('.gsd-product-home-delivery').is(':checked') ? 1 : 0,  // Returns integer
    // ...
});
```

**Why:** Integers `1` and `0` are unambiguous and don't have string conversion issues.

#### Part 2: PHP - Use Proper Boolean Conversion

**Changed from:**
```php
$home_delivery = !empty($product_data['home_delivery']) ? 'yes' : 'no';
```

**Changed to:**
```php
$home_delivery_value = isset($product_data['home_delivery']) ? $product_data['home_delivery'] : false;
$home_delivery_bool = filter_var($home_delivery_value, FILTER_VALIDATE_BOOLEAN);
$home_delivery = $home_delivery_bool ? 'yes' : 'no';
```

**Why:** `filter_var()` with `FILTER_VALIDATE_BOOLEAN` correctly handles:
- Boolean `true`/`false`
- String `'true'`/`'false'`
- Integer `1`/`0`
- String `'1'`/`'0'`

### How filter_var() Fixes It

```php
filter_var('false', FILTER_VALIDATE_BOOLEAN) // Returns: false ✓
filter_var('true', FILTER_VALIDATE_BOOLEAN)  // Returns: true ✓
filter_var(0, FILTER_VALIDATE_BOOLEAN)       // Returns: false ✓
filter_var(1, FILTER_VALIDATE_BOOLEAN)       // Returns: true ✓
```

## Testing

Created comprehensive test scripts that verify:

1. **Boolean Conversion Test** (`/tmp/test_boolean_conversion.php`)
   - Tests all possible input types (boolean, string, integer, null, empty)
   - Confirms old logic fails on string `'false'`
   - Confirms new logic handles all cases correctly

2. **AJAX Flow Simulation** (`/tmp/test_ajax_flow.php`)
   - Simulates exact jQuery AJAX serialization
   - Shows that old logic would save all checkboxes as 'yes'
   - Shows that new logic correctly preserves checked/unchecked state

3. **Integer Approach Validation** (`/tmp/test_integer_approach.php`)
   - Validates the JavaScript change to send 1/0 instead of true/false
   - Confirms this works perfectly with filter_var()

All tests pass ✓

## Additional Improvements

### 1. Debug Panel Added

Added a comprehensive debug panel to `/wp-admin/admin.php?page=garden-sheds-delivery` that shows:

- **Current Database State**: Real-time view of what's saved in the database
- **AJAX Activity Log**: Console that tracks all AJAX requests and responses
- **Product Meta Inspector**: Tool to inspect specific product's delivery settings
- **Test Save Button**: Quick test to verify AJAX save is working

### 2. Enhanced Logging

Added detailed error logging to the save handler:
- Logs when the handler is called
- Logs all POST data received
- Logs each product being saved
- Logs success/failure counts

### 3. Better Error Messages

Enhanced error responses to include:
- Count of products successfully saved
- Count of products skipped due to validation errors
- Specific error messages for debugging

## Files Modified

- `/includes/class-gsd-admin.php`
  - Fixed `ajax_save_product_shipping()` method
  - Added `ajax_inspect_product_meta()` method
  - Enhanced JavaScript to send 1/0 instead of true/false
  - Added debug panel HTML
  - Added debug panel JavaScript functionality

## Impact

This fix ensures that:
- ✅ Checked checkboxes are saved as 'yes'
- ✅ Unchecked checkboxes are saved as 'no'
- ✅ Settings persist correctly across page reloads
- ✅ Product-level overrides work as intended
- ✅ Category-level defaults are properly inherited

## Prevention

To prevent similar issues in the future:
1. Always use `filter_var()` for boolean conversions from user input
2. Send integers (1/0) instead of booleans in AJAX to avoid serialization issues
3. Add comprehensive logging to AJAX handlers during development
4. Test both checked and unchecked states when working with checkboxes
