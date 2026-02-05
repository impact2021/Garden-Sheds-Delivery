# Testing Guide for Category Checkbox Fix (v2.0.6)

## Overview

This guide provides step-by-step instructions to test the category checkbox fix implemented in version 2.0.6.

## Prerequisites

- WordPress installation with WooCommerce active
- Garden Sheds Delivery plugin v2.0.6 installed and activated
- At least one product category with multiple products
- Access to WordPress admin dashboard

## Test Scenarios

### Test 1: Check Category Checkbox (Collapsed State)

**Purpose**: Verify that checking a category checkbox updates all products even when the category is not expanded.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Locate a category with products (e.g., "Cedar Finger Joint")
3. Ensure the category is **collapsed** (not expanded with the arrow pointing right →)
4. Check the "Depot" checkbox for this category
5. Wait for the "✓ Settings saved" notification
6. Now click the arrow button to **expand** the category
7. Verify all products under this category now show **checked** for Depot

**Expected Result**: All products should be checked ✓

**Bug Behavior (v2.0.5)**: Products would remain unchecked ☐

---

### Test 2: Uncheck Category Checkbox (Collapsed State)

**Purpose**: Verify that unchecking a category checkbox updates all products when collapsed.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Locate a category that has the "Depot" checkbox checked
3. Ensure the category is **collapsed**
4. **Uncheck** the "Depot" checkbox for this category
5. Wait for the "✓ Settings saved" notification
6. Expand the category
7. Verify all products show **unchecked** for Depot

**Expected Result**: All products should be unchecked ☐

---

### Test 3: Check Category Checkbox (Expanded State)

**Purpose**: Verify that checking a category checkbox works correctly when expanded.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Locate a category with products
3. Click the arrow to **expand** the category
4. Verify products are currently unchecked
5. Check the "Depot" checkbox for the **category** (top row)
6. Observe the product checkboxes update immediately in the UI
7. Wait for the "✓ Settings saved" notification

**Expected Result**: 
- All product checkboxes update immediately to checked ✓
- Settings are saved to database

---

### Test 4: Indeterminate State (Some Products Checked)

**Purpose**: Verify the category checkbox shows a dash (indeterminate state) when some products are checked.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Expand a category with at least 3 products
3. Check the "Depot" checkbox for the category (all products now checked)
4. **Uncheck** 1 or 2 individual products (but not all)
5. Observe the category checkbox
6. Reload the page
7. Verify the category checkbox still shows the indeterminate state

**Expected Result**: 
- Category checkbox shows a dash (–) indicating mixed state
- After page reload, dash is still shown
- Tooltip shows "Some products in this category have Depot enabled"

---

### Test 5: Initial Page Load State

**Purpose**: Verify that category checkboxes correctly reflect actual product states on page load.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Expand a category
3. Manually check 5 out of 10 products for "Depot"
4. Wait for auto-save
5. **Reload the page** (F5 or Ctrl+R)
6. Observe the category checkbox (should show dash –)
7. Expand another category where ALL products are checked
8. Reload the page
9. Observe that category checkbox (should show checkmark ✓)

**Expected Result**: 
- Category with partial products: dash (–)
- Category with all products: checkmark (✓)
- Category with no products: empty (☐)

**Bug Behavior (v2.0.5)**: Category checkboxes showed incorrect state based on old database values

---

### Test 6: Multiple Categories Simultaneously

**Purpose**: Verify that changes to one category don't affect others.

**Steps**:
1. Go to **Shed Delivery > Settings**
2. Check "Depot" for Category A (collapsed)
3. Uncheck "Depot" for Category B (collapsed)
4. Check "Home Delivery" for Category C (collapsed)
5. Wait for all auto-saves to complete
6. Expand all three categories
7. Verify:
   - Category A: All products have Depot ✓
   - Category B: All products have Depot ☐
   - Category C: All products have Home Delivery ✓

**Expected Result**: Each category's products reflect only their category's settings

---

### Test 7: Different Delivery Options

**Purpose**: Verify the fix works for all delivery option types.

**Steps**:
For each option type (Home Delivery, Small Items, Might be able to offer home delivery, Depot):
1. Go to **Shed Delivery > Settings**
2. Select a category (collapsed)
3. Check the checkbox for this option type
4. Wait for save notification
5. Expand the category
6. Verify all products are checked for this option

**Expected Result**: Fix works for all four option types

---

### Test 8: Large Category (Performance Test)

**Purpose**: Verify the fix works efficiently with many products.

**Steps**:
1. Create or use a category with 50+ products
2. Check the category checkbox (collapsed)
3. Observe the save notification time
4. Expand the category
5. Verify all products are updated

**Expected Result**: 
- Save completes within 5 seconds
- All products correctly updated
- No timeout errors

---

## Debug Panel Verification

The plugin includes a debug panel at the bottom of the settings page. Use it to verify:

### Check AJAX Activity Log
1. Scroll to the bottom of the **Shed Delivery > Settings** page
2. Find the "🐛 Debug Panel"
3. Expand the "📡 AJAX Activity Log" section
4. Perform a category checkbox change
5. Verify you see:
   ```
   [timestamp] Category checkbox changed
   [timestamp] Category products updated via AJAX
   [timestamp] Updated 15 products
   ```

### Check Database State
1. In the debug panel, find "📊 Current Database State"
2. After making changes, this section shows what's stored in the database
3. Verify the options match your selections

### Check Indeterminate States Calculation
1. In the debug panel, find "🔍 Category Indeterminate States Calculation"
2. Expand any category
3. Uncheck some (but not all) products
4. Reload the page
5. Find your category in the debug output
6. Verify `is_indeterminate: true` for the option you partially checked

---

## Common Issues and Solutions

### Issue: Category checkbox doesn't update products
**Solution**: 
- Check browser console for JavaScript errors (F12)
- Verify nonce is valid (check AJAX log in debug panel)
- Check PHP error log for server-side issues

### Issue: Indeterminate state doesn't show
**Solution**:
- Clear browser cache
- Verify some (but not all) products are actually checked
- Check that JavaScript is running (look for console logs)

### Issue: Changes don't persist after reload
**Solution**:
- Check that AJAX save completed successfully
- Look for errors in the AJAX Activity Log
- Verify WordPress database permissions

---

## Regression Testing

After confirming the fix works, also verify that existing functionality still works:

1. **Individual Product Settings**
   - Go to Products > Edit Product
   - Click "Delivery Options" tab
   - Change settings
   - Verify they save correctly

2. **Checkout Flow**
   - Add product to cart
   - Go to checkout
   - Verify correct shipping options appear
   - Verify pricing is correct

3. **Depot Selection**
   - Select depot shipping
   - Verify depot dropdown appears
   - Select a depot
   - Complete order
   - Verify depot is saved in order

---

## Success Criteria

All tests pass if:
- ✅ Category checkboxes update all products when changed (collapsed or expanded)
- ✅ Initial page load shows correct checkbox states based on actual products
- ✅ Indeterminate state (dash) shows when appropriate
- ✅ Auto-save works correctly and quickly
- ✅ No JavaScript errors in console
- ✅ No PHP errors in error log
- ✅ All existing functionality still works

---

## Reporting Issues

If you find any issues:
1. Note which test scenario failed
2. Check debug panel for errors
3. Check browser console (F12) for JavaScript errors
4. Take a screenshot
5. Report with steps to reproduce
