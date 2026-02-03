# Testing Guide for Version 1.5.4

## Overview
This guide provides step-by-step instructions to test the new delivery options inheritance feature.

## Prerequisites
- WordPress installation with WooCommerce active
- Garden Sheds Delivery plugin installed and activated
- At least one product category with multiple products
- Admin access to WordPress dashboard

## Test Scenarios

### Test 1: Inheritance on New Products
**Objective:** Verify that new products inherit delivery options from their category

**Steps:**
1. Navigate to **Shed Delivery > Settings** in WordPress admin
2. Find a product category in the table
3. Check the "Home Delivery" checkbox for that category
4. Click "Save Settings" at the bottom
5. Click the arrow icon next to the category name to expand products
6. **Expected Result:** All products should show "Home Delivery" checked by default

**Additional Verification:**
- Uncheck "Home Delivery" for one product
- Click "Save Product Settings"
- Collapse and re-expand the category
- **Expected Result:** The unchecked product should remain unchecked (setting was saved)
- **Expected Result:** Other products should still be checked

---

### Test 2: Indeterminate State Display
**Objective:** Verify category checkbox shows indeterminate state for mixed product settings

**Setup:**
1. Navigate to **Shed Delivery > Settings**
2. Find a category with at least 3 products
3. Expand the category

**Test Steps:**
1. Check "Home Delivery" for only 1 or 2 products (not all)
2. Click "Save Product Settings"
3. Look at the category's "Home Delivery" checkbox
4. **Expected Result:** Should show indeterminate state (appears as a solid square or dash)

**Additional Test:**
1. Check "Home Delivery" for ALL products
2. Click "Save Product Settings"
3. **Expected Result:** Category checkbox should now be fully checked (✓)

1. Uncheck "Home Delivery" for ALL products
2. Click "Save Product Settings"
3. **Expected Result:** Category checkbox should now be unchecked (☐)

---

### Test 3: Category Checkbox Control
**Objective:** Verify clicking category checkbox updates all product checkboxes

**Steps:**
1. Navigate to **Shed Delivery > Settings**
2. Find a category with multiple products
3. Expand the category (click arrow)
4. Wait for products to load
5. Click the category's "Small Items" checkbox to CHECK it
6. **Expected Result:** All product "Small Items" checkboxes should become checked
7. Click the category's "Small Items" checkbox to UNCHECK it
8. **Expected Result:** All product "Small Items" checkboxes should become unchecked

**Important Notes:**
- This only works when products are expanded and visible
- Changes are NOT automatically saved - you must click "Save Product Settings"
- The indeterminate state clears when you click the category checkbox

---

### Test 4: Multiple Delivery Types
**Objective:** Verify each delivery type works independently

**Steps:**
1. Expand a category with products
2. Set different delivery options for the same product:
   - Product A: Home Delivery ✓, Small Items ✓, Contact ☐
   - Product B: Home Delivery ☐, Small Items ✓, Contact ✓
   - Product C: Home Delivery ✓, Small Items ☐, Contact ✓
3. Click "Save Product Settings"
4. Check each category checkbox state:
   - **Home Delivery:** Should be indeterminate (2 of 3 checked)
   - **Small Items:** Should be indeterminate (2 of 3 checked)
   - **Contact for Delivery:** Should be indeterminate (2 of 3 checked)

---

### Test 5: Collapsed Category Behavior
**Objective:** Verify category checkbox changes don't affect products when collapsed

**Steps:**
1. Find a category but DO NOT expand it (keep it collapsed)
2. Change the category's "Home Delivery" checkbox
3. Click "Save Settings"
4. **Expected Result:** Category setting should save normally
5. Now expand the category
6. **Expected Result:** Product checkboxes should NOT have changed (they were not visible when category checkbox was changed)

**Why this matters:** We only propagate category changes to products when they're expanded and visible to prevent accidental bulk changes.

---

### Test 6: Persistence and Reload
**Objective:** Verify settings persist after page reload

**Steps:**
1. Configure specific delivery settings for products
2. Click "Save Product Settings"
3. Refresh the browser page (F5 or Ctrl+R)
4. Re-expand the same category
5. **Expected Result:** All product settings should be exactly as you left them

---

### Test 7: Empty Category
**Objective:** Verify system handles categories with no products

**Steps:**
1. Find or create a category with no products
2. Check delivery options for the category
3. Click "Save Settings"
4. Click the arrow to expand the category
5. **Expected Result:** Should show "No products found in this category" message
6. **Expected Result:** No JavaScript errors in browser console

---

### Test 8: Single Product Category
**Objective:** Verify correct behavior with only one product

**Steps:**
1. Find a category with exactly 1 product
2. Expand the category
3. If the product's "Home Delivery" is checked:
   - **Expected Result:** Category checkbox should be fully checked (not indeterminate)
4. If the product's "Home Delivery" is unchecked:
   - **Expected Result:** Category checkbox should be fully unchecked (not indeterminate)

**Note:** Indeterminate state should only appear when there are multiple products with different settings.

---

## Visual Indicators Reference

### Checkbox States
- **Checked (✓):** All products have this delivery option enabled
- **Unchecked (☐):** No products have this delivery option enabled
- **Indeterminate (▪):** Some products have this delivery option enabled, others don't

### Browser Compatibility
The indeterminate state appearance varies by browser:
- Chrome/Edge: Horizontal line (dash)
- Firefox: Horizontal line (dash)
- Safari: Horizontal line (dash)

---

## Common Issues and Solutions

### Issue: Products don't inherit category settings
**Solution:** This only applies to NEW products or products without saved settings. If a product already has an explicit setting saved, it will use that instead.

### Issue: Indeterminate state not showing
**Solution:** 
1. Make sure products are actually loaded (expanded)
2. Save product settings after making changes
3. The state updates automatically after saving

### Issue: Category checkbox doesn't update products
**Solution:**
1. Make sure the category is EXPANDED before clicking
2. The feature only works when products are visible
3. You still need to click "Save Product Settings" to persist changes

---

## Expected File Changes
After testing, the following should be affected:
- **WordPress Options Table:** Category-level delivery settings
- **Post Meta Table:** Individual product delivery settings
- **No database structure changes**

## Rollback
If issues occur:
1. Deactivate the plugin
2. Revert to version 1.5.3
3. Reactivate the plugin
4. All existing settings should be preserved

---

## Testing Checklist

- [ ] Test 1: Inheritance on New Products
- [ ] Test 2: Indeterminate State Display  
- [ ] Test 3: Category Checkbox Control
- [ ] Test 4: Multiple Delivery Types
- [ ] Test 5: Collapsed Category Behavior
- [ ] Test 6: Persistence and Reload
- [ ] Test 7: Empty Category
- [ ] Test 8: Single Product Category

**Sign-off:** ____________________ Date: ____________________
