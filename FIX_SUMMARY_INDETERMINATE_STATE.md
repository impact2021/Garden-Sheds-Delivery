# Indeterminate State Visual Improvements and Save Enhancements

## Summary

This update enhances the visual feedback and reliability of the category checkbox indeterminate state feature, along with improved error handling for save operations.

## Problem Statement

Users reported two main issues:
1. The indeterminate state (dash indicator) for category checkboxes when some products have shipping options and others don't was not visually obvious enough
2. Concerns about whether changes were actually being saved despite the "Saved" notification appearing

## What Was Changed

### 1. Enhanced Visual Indicators (New)

**Added Admin CSS File** (`assets/css/admin.css`):
- **Row Highlighting**: Category rows with mixed/indeterminate states now have a yellow background (`#fff8e5`)
- **Warning Icon**: A warning icon (⚠) appears next to the expand button for categories with mixed states
- **Larger Checkboxes**: Increased checkbox size to 18x18px for better visibility
- **Smooth Animations**: Added slide-in animation for success/error notifications

**Key CSS Features**:
```css
/* Highlight rows with indeterminate checkboxes */
.gsd-category-row.has-indeterminate {
    background-color: #fff8e5 !important;
}

/* Add warning icon for mixed state categories */
.gsd-category-row.has-indeterminate td:first-child::after {
    content: '⚠';
    color: #ffb900;
}
```

### 2. Explanatory Notice (New)

**Added User-Friendly Notice** (`includes/class-gsd-admin.php`, line 155-160):
- Blue informational box explaining what the dash/indeterminate state means
- Clarifies that changes are auto-saved immediately
- Helps users understand the yellow highlighting and warning icon

### 3. Enhanced Error Handling

**Improved AJAX Save Handler** (`includes/class-gsd-admin.php`, `ajax_save_product_shipping` method):
- **Detailed Error Tracking**: Now tracks and reports individual product validation failures
- **Save Counter**: Reports how many products were successfully processed
- **Error Messages**: Returns specific error messages for validation failures (invalid ID, wrong post type, etc.)
- **Better Validation**: Enhanced validation with descriptive error messages

Before:
```php
foreach ($products as $product_data) {
    // ... save without tracking validation failures
}
wp_send_json_success(array('message' => __('Settings saved successfully')));
```

After:
```php
$saved_count = 0;
$errors = array();

foreach ($products as $product_data) {
    // Validate product data and ID
    if (!$product_id) {
        $errors[] = __('Invalid product ID');
        continue;
    }
    
    // ... save all three meta fields
    // Count as saved since validation passed and updates attempted
    $saved_count++;
}

// Return detailed response
wp_send_json_success(array(
    'message' => $message,
    'saved_count' => $saved_count,
    'errors' => $errors
));
```

Note: The save count reflects successfully validated products, not individual meta update results. This is because `update_post_meta()` returns false for both errors and unchanged values, making it impractical to distinguish between the two.

### 4. Error Notifications (New)

**Added Error Notification UI** (`includes/class-gsd-admin.php`, line 147-150):
- Red notification box for failed saves
- Displays specific error messages
- Stays visible for 4 seconds (vs 2 seconds for success)

**Enhanced JavaScript Error Handling** (`includes/class-gsd-admin.php`, lines 410-445):
- Shows error notification on failed AJAX requests
- Logs detailed error information to console
- Displays network errors to users
- Shows warning count if some products saved but others failed

### 5. Visual State Tracking

**Enhanced JavaScript** (`includes/class-gsd-admin.php`, `updateCategoryCheckboxStates` function):
- Automatically adds `has-indeterminate` class to category rows
- Removes class when all products have same state
- Triggers visual indicators immediately when checkbox states change

```javascript
// Add visual indicator if any checkbox is indeterminate
var hasIndeterminate = (categoryHomeCheckbox && categoryHomeCheckbox.indeterminate) ||
                      (categoryExpressCheckbox && categoryExpressCheckbox.indeterminate) ||
                      (categoryContactCheckbox && categoryContactCheckbox.indeterminate);

if (hasIndeterminate) {
    categoryRow.addClass('has-indeterminate');
} else {
    categoryRow.removeClass('has-indeterminate');
}
```

### 6. Asset Loading

**Added Admin Asset Enqueue** (`includes/class-gsd-admin.php`, `enqueue_admin_assets` method):
- Registers admin CSS file
- Only loads on plugin admin pages (performance optimization)
- Uses plugin version for cache busting

## User Experience Improvements

### Before
- ✓ Indeterminate checkbox showed dash (already working)
- ✓ Changes saved via AJAX (already working)
- ✗ No visual distinction beyond the dash
- ✗ No explanation of what the dash means
- ✗ No error feedback if save fails
- ✗ Not obvious at a glance which categories have mixed states

### After
- ✓ Indeterminate checkbox shows dash
- ✓ Changes save via AJAX
- ✓ **Entire row highlighted in yellow**
- ✓ **Warning icon (⚠) next to expand button**
- ✓ **Clear explanation at top of page**
- ✓ **Red error notification if save fails**
- ✓ **Detailed error logging in console**
- ✓ **Immediately obvious which categories have mixed states**

## Technical Details

### Files Modified

1. **`includes/class-gsd-admin.php`**:
   - Added `enqueue_admin_assets()` method
   - Enhanced `ajax_save_product_shipping()` with error tracking
   - Updated `updateCategoryCheckboxStates()` JavaScript function
   - Improved AJAX error handling in JavaScript
   - Added error notification HTML
   - Added explanatory notice HTML

2. **`assets/css/admin.css`** (NEW FILE):
   - 168 lines of CSS
   - Responsive design (mobile-friendly)
   - Accessibility-focused styling
   - WordPress admin theme integration

### No Breaking Changes

- ✅ All existing functionality preserved
- ✅ No database changes
- ✅ No API changes
- ✅ Backward compatible
- ✅ Progressive enhancement (CSS gracefully degrades)

### Browser Compatibility

- ✅ All modern browsers (indeterminate is standard HTML5)
- ✅ Mobile responsive
- ✅ WordPress admin theme compatible
- ✅ Dashicons for icons

## Testing Recommendations

### Visual Testing
1. Go to **Shed Delivery > Settings**
2. Read the blue info box explaining mixed states
3. Expand a category with multiple products
4. Uncheck ONE product's shipping option
5. **Verify**: Row turns yellow
6. **Verify**: Warning icon (⚠) appears
7. **Verify**: Category checkbox shows dash (–)
8. **Verify**: Green "Settings saved" notification appears
9. Collapse and re-expand the category
10. **Verify**: Row still yellow, checkbox still shows dash

### Error Testing
1. Disable WordPress debug logging temporarily
2. Manually trigger an AJAX error (e.g., corrupt nonce)
3. **Verify**: Red error notification appears
4. **Verify**: Error message is descriptive
5. Check browser console for detailed error logs

### Save Persistence Testing
1. Uncheck a product
2. Wait for save notification
3. Refresh the page
4. Expand the category
5. **Verify**: Product still unchecked
6. **Verify**: Category still shows indeterminate state
7. **Verify**: Row still highlighted yellow

## Security Considerations

- ✅ No new security vulnerabilities introduced
- ✅ Nonce validation unchanged
- ✅ Permission checks unchanged  
- ✅ CSS-only visual changes (no XSS risk)
- ✅ Error messages sanitized
- ✅ No sensitive data exposed in errors

## Performance Impact

- ✅ **Minimal**: Single CSS file (~4KB)
- ✅ **Optimized**: Only loads on plugin admin pages
- ✅ **Cached**: Version-based cache busting
- ✅ **No AJAX overhead**: Error handling doesn't add requests
- ✅ **No DOM overhead**: Class toggles are lightweight

## Summary of User Benefits

1. **Clearer Visual Feedback**: Yellow highlighting makes mixed states impossible to miss
2. **Better Understanding**: Explanatory notice reduces confusion
3. **Error Awareness**: Failed saves now show error messages instead of false success
4. **Improved Confidence**: Detailed error logging helps debugging
5. **Professional UX**: Polished visual design matches WordPress admin standards

## Conclusion

These changes address the user's concerns about visual clarity and save reliability. The indeterminate state is now:
- **Visually prominent** (yellow row + warning icon)
- **Well explained** (info box)
- **Reliable** (enhanced error handling)
- **Trustworthy** (detailed feedback)

All changes are non-breaking and enhance the existing functionality without requiring any user configuration or database changes.
