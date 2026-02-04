# Category Checkbox Indeterminate State Fix

## Overview
This fix addresses the visual display of category checkboxes when there's a mix of products with and without specific shipping types.

## Problem Statement
Previously, when expanding a category and changing individual product settings:
- The category checkbox would show nothing (unchecked) when there was a mix
- This was confusing because it didn't indicate that SOME products had the shipping type
- There was an unwanted warning triangle (⚠) visual indicator
- The entire row was highlighted in yellow

## Solution
The JavaScript logic was already correct and properly setting the checkbox `indeterminate` property. The issue was with excessive visual styling.

### Changes Made
1. **Removed CSS warning triangle** (`assets/css/admin.css`)
   - Removed the `::after` pseudo-element that added a ⚠ symbol
   
2. **Removed yellow highlighting** (`assets/css/admin.css`)
   - Removed the `background-color: #fff8e5` styling for indeterminate rows
   
3. **Updated help text** (`includes/class-gsd-admin.php`)
   - Removed mention of yellow highlighting from user-facing instructions

### Current Behavior
Category checkboxes now display using the browser's native checkbox states:

| State | All Products | Some Products | No Products |
|-------|--------------|---------------|-------------|
| Visual | ✓ Checked | ☐̶ Hyphen | ☐ Empty |
| JavaScript | checked=true, indeterminate=false | checked=false, indeterminate=true | checked=false, indeterminate=false |

## Technical Implementation

### JavaScript Logic (Unchanged)
The `updateCheckboxState()` function in `class-gsd-admin.php`:

```javascript
if (checkedCount === 0) {
    // None checked
    categoryCheckbox.checked = false;
    categoryCheckbox.indeterminate = false;
} else if (checkedCount === productCheckboxes.length) {
    // All checked
    categoryCheckbox.checked = true;
    categoryCheckbox.indeterminate = false;
} else {
    // Some checked (indeterminate)
    categoryCheckbox.checked = false;
    categoryCheckbox.indeterminate = true;
}
```

### CSS Changes
**Before:**
```css
.gsd-category-row.has-indeterminate {
    background-color: #fff8e5 !important;
}

.gsd-category-row.has-indeterminate td:first-child::after {
    content: '⚠';
    display: inline-block;
    margin-left: 5px;
    color: #ffb900;
    /* ... */
}
```

**After:**
```css
/* Note: Indeterminate checkboxes use browser's native hyphen styling */
/* No additional visual indicators needed per user requirements */
```

## Testing
To verify the fix works correctly:

1. Navigate to **Shed Delivery** settings in WordPress admin
2. Expand a product category
3. Change some (but not all) products to have a shipping option
4. Observe that:
   - The category checkbox shows a hyphen (indeterminate)
   - There is NO warning triangle
   - There is NO yellow background
   - The state accurately reflects the mixed configuration

## Files Modified
- `assets/css/admin.css` - Removed visual indicators
- `includes/class-gsd-admin.php` - Updated help text

## Compatibility
- Browser Support: All modern browsers support the indeterminate checkbox state
- WordPress: Compatible with all WordPress versions supported by the plugin
- No breaking changes to functionality or data storage
