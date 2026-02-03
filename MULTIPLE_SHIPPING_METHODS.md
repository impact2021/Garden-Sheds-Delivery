# Multiple Shipping Methods Update - Version 1.4.0

## Overview

Version 1.4.0 introduces 5 separate shipping methods instead of a single "Shed Delivery" method. This provides better organization in WooCommerce shipping zones and makes the shipping setup more intuitive.

## What Changed

### Before (v1.3.3)
- Single shipping method: "Garden Sheds Delivery"
- All shipping options (depot pickup, home delivery, small items) appeared as rates under this one method

### After (v1.4.0)
- **5 Separate Shipping Methods:**
  1. **Home Delivery** - For home delivery with pricing
  2. **Depot Pickup (PBT)** - Pickup from PBT depot locations
  3. **Depot Pickup (Mainfreight)** - Pickup from Mainfreight depot locations
  4. **Small Items Delivery** - For small items with pricing
  5. **Maybe We Can Arrange Home Delivery** - Contact option for potential home delivery

## Setup Instructions

### Step 1: Add Shipping Methods to Your Shipping Zone

1. Go to **WooCommerce > Settings > Shipping**
2. Select your shipping zone (or create one)
3. Click **Add shipping method**
4. Add all 5 methods:
   - Home Delivery
   - Depot Pickup (PBT)
   - Depot Pickup (Mainfreight)
   - Small Items Delivery
   - Maybe We Can Arrange Home Delivery

### Step 2: Configure Category Settings

The existing category checkboxes in **Shed Delivery > Settings** now control which shipping methods appear for each category:

- **Home Delivery checkbox** → Shows "Home Delivery" method
- **PBT checkbox** → Shows "Depot Pickup (PBT)" method
- **Main Freight checkbox** → Shows "Depot Pickup (Mainfreight)" method
- **Small Items checkbox** → Shows "Small Items Delivery" method
- **Might be able to offer home delivery checkbox** → Shows "Maybe We Can Arrange Home Delivery" method

### Example Configuration

If you want products in the "Garden Sheds" category to have:
- Home delivery option
- PBT depot pickup
- Mainfreight depot pickup

Then check these boxes for the "Garden Sheds" category:
- ✅ Home Delivery
- ✅ PBT
- ✅ Main Freight
- ⬜ Small Items
- ⬜ Might be able to offer home delivery

## Benefits

1. **Better Organization**: Each shipping method appears separately in the shipping zone list
2. **Clearer Admin UI**: Easier to enable/disable specific shipping options
3. **More Intuitive**: Matches standard WooCommerce shipping method patterns
4. **Flexible Control**: Category checkboxes control visibility of each method

## Backwards Compatibility

The old "Garden Sheds Delivery" method is still registered for backwards compatibility. Existing installations will continue to work without any changes required. However, we recommend migrating to the new methods for better organization.

## Migration Guide

If you're currently using the old "Garden Sheds Delivery" method:

1. Add all 5 new shipping methods to your shipping zones (as described above)
2. Test the checkout process to ensure all options appear correctly
3. Once verified, you can disable the old "Garden Sheds Delivery" method
4. The category settings will automatically control the new methods

## Technical Details

### New Shipping Method Classes

- `GSD_Shipping_Home_Delivery` (ID: `gsd_home_delivery`)
- `GSD_Shipping_Depot_PBT` (ID: `gsd_depot_pbt`)
- `GSD_Shipping_Depot_Mainfreight` (ID: `gsd_depot_mainfreight`)
- `GSD_Shipping_Small_Items` (ID: `gsd_small_items`)
- `GSD_Shipping_Contact_Delivery` (ID: `gsd_contact_delivery`)

### Category-Based Filtering

Each shipping method checks the admin settings to determine if it should appear:
- Products must be in categories that have the corresponding checkbox enabled
- If no products in the cart match the criteria, the shipping method won't appear at checkout

## Support

If you encounter any issues after updating:
1. Clear your WooCommerce cart cache
2. Verify category settings in **Shed Delivery > Settings**
3. Check that all 5 methods are added to your shipping zones
4. Ensure at least one category has checkboxes enabled
