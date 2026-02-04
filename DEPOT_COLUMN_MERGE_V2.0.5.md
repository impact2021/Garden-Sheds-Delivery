# Depot Column Merge - Version 2.0.5

## Summary

This update addresses two critical issues identified in the admin interface:

1. **Category checkbox indeterminate state**: Category-level checkboxes now properly show an indeterminate state (dash/minus sign) when some products in that category have depot assignment while others don't.

2. **Merged depot columns**: Replaced the separate "Main Freight" and "PBT" columns with a single "Depot" column that applies to both depot courier types.

## Changes Made

### Admin Interface

#### Table Structure
- **Before**: Table had separate "Main Freight" and "PBT" columns
- **After**: Single "Depot" column that encompasses both depot courier types

#### Product Rows
- **Before**: Empty cells for Main Freight and PBT columns
- **After**: Depot checkbox for each product that allows setting/clearing depot assignment

### Backend Logic

#### Indeterminate State Calculation
- Added depot to the indeterminate states calculation
- Checks if products have `_gsd_courier` meta set to 'main_freight' or 'pbt'
- Category checkbox shows indeterminate (dash) when some but not all products have depot

#### Data Storage
- **New option**: `gsd_depot_categories` - stores category-level depot assignments
- **Legacy options preserved**: `gsd_main_freight_categories` and `gsd_pbt_categories` automatically updated for backwards compatibility
- **Product meta**: Uses existing `_gsd_courier` field to store depot assignment

#### AJAX Save Handler
- Updated to save depot checkbox state
- When checked: Sets `_gsd_courier` to 'main_freight' (if not already a depot courier)
- When unchecked: Clears `_gsd_courier` (if currently set to depot courier)

### JavaScript Updates

#### Page Load
- Applies indeterminate state to depot checkboxes based on PHP-calculated states
- Includes depot in all checkbox state management

#### Checkbox Interactions
- Category depot checkbox toggles all product depot checkboxes when clicked
- Product depot checkbox changes update category checkbox state (checked/unchecked/indeterminate)
- Auto-saves changes immediately after modification

#### State Tracking
- `updateCategoryCheckboxStates()` now handles depot checkboxes
- `updateCheckboxState()` properly sets indeterminate for depot
- `autoSaveProductSettings()` includes depot in save data

## Backwards Compatibility

### Settings Migration
- Existing categories in `gsd_main_freight_categories` or `gsd_pbt_categories` will be merged into depot
- When depot categories are saved, both legacy options are updated
- No data loss during migration

### Product Settings
- Existing products with `_gsd_courier` set to 'main_freight' or 'pbt' will show as having depot enabled
- Courier selection is preserved when depot checkbox is checked/unchecked

## Version Update

- **Previous version**: 2.0.4
- **New version**: 2.0.5

## Testing Recommendations

1. **Category-level depot checkbox**:
   - Check a category depot checkbox → all products should get depot
   - Uncheck it → all products should lose depot
   - Manually check some products → category should show indeterminate (dash)

2. **Product-level depot checkbox**:
   - Check individual product depot → `_gsd_courier` should be set to 'main_freight'
   - Uncheck it → `_gsd_courier` should be cleared
   - Changes should auto-save

3. **Indeterminate state**:
   - Expand a category with mixed depot assignments
   - Category depot checkbox should show a dash (—) instead of checkmark
   - Changing product checkboxes should update category state dynamically

4. **Backwards compatibility**:
   - Products previously assigned to Main Freight should still show depot enabled
   - Products previously assigned to PBT should still show depot enabled
   - Category settings should migrate from legacy options

## Files Modified

1. `garden-sheds-delivery.php` - Version number updated to 2.0.5
2. `includes/class-gsd-admin.php` - All depot column changes, indeterminate state logic, AJAX handlers, JavaScript

## Security

- No security vulnerabilities introduced
- All input properly sanitized
- AJAX requests include nonce verification
- User permissions checked before saving

## Notes

- The depot checkbox represents courier assignment to either Main Freight or PBT
- When depot is checked, the system defaults to 'main_freight' courier
- Users can still change specific courier in the product Delivery Options tab if needed
- The merge simplifies the UI while maintaining full functionality
