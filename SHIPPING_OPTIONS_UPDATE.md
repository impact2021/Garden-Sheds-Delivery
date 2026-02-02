# Shipping Options Update - Implementation Summary

## Overview
This document describes the changes made to convert the home delivery option from a checkbox appearing below the order notes section to proper selectable WooCommerce shipping rates.

## Changes Made

### 1. Shipping Method (class-gsd-shipping-method.php)

#### Before
- Single shipping rate with label "Shed Delivery" and $0 cost
- Depot selection and home delivery were handled separately via checkout fields

#### After
- Multiple shipping rates are created dynamically:
  - **One rate per depot**: "Pickup from [Depot Name]" with $0 cost
  - **One home delivery rate**: "Home Delivery (+$X.XX)" with configured price
- Each rate includes meta data for later retrieval

#### How It Works
```php
// For each depot
Rate ID: garden_sheds_delivery:depot:main_freight_depot_1
Label: "Pickup from Auckland Depot"
Cost: $0
Meta: depot_id, depot_name, courier_name, delivery_type

// For home delivery
Rate ID: garden_sheds_delivery:home_delivery
Label: "Home Delivery (+$150.00)"
Cost: $150.00
Meta: delivery_type, home_delivery_price
```

### 2. Checkout Process (class-gsd-checkout.php)

#### Removed
- `add_checkout_fields()` - Previously added depot dropdown and home delivery checkbox after order notes
- `add_cart_delivery_fields()` - Previously added similar fields on cart page
- `validate_checkout_fields()` - Previously validated depot/home delivery selection
- `add_home_delivery_fee()` - Previously added fee to cart when checkbox was checked
- All helper methods for cart analysis

#### Added
- `save_shipping_method_data()` - Extracts meta data from selected shipping rate and saves to order

#### How It Works
1. Customer selects a shipping rate at checkout (depot or home delivery)
2. WooCommerce automatically applies the cost from the selected rate
3. On order creation, our hook extracts the meta data from the shipping rate
4. Meta data is saved to order for display and processing

### 3. Admin Documentation (class-gsd-admin.php)

#### Added
- New submenu page "Documentation" under Shed Delivery menu
- `docs_page()` method to display documentation

#### Removed
- Documentation section from depot locations page (moved to new page)

## Customer Experience

### Before
1. Add product to cart
2. Go to checkout
3. See shipping method: "Shed Delivery"
4. Scroll down below order notes
5. Find depot dropdown
6. Find home delivery checkbox (optional)
7. Complete checkout

### After
1. Add product to cart
2. Go to checkout
3. See multiple shipping options:
   - ○ Pickup from Auckland Depot (Free)
   - ○ Pickup from Wellington Depot (Free)
   - ○ Pickup from Christchurch Depot (Free)
   - ○ Home Delivery (+$150.00)
4. Select one shipping option
5. Complete checkout

## Benefits

### User Experience
- **Clearer**: Shipping options are in the standard WooCommerce shipping section
- **Simpler**: Single selection instead of depot dropdown + checkbox
- **Consistent**: Follows WooCommerce conventions

### Technical
- **Less code**: Removed ~300 lines of custom checkout field handling
- **More robust**: Uses WooCommerce's built-in shipping rate system
- **Better validation**: WooCommerce handles shipping method validation
- **Automatic pricing**: Shipping cost is part of the rate, no need for cart fee manipulation

## Compatibility

### Order Meta Data
The same order meta fields are still saved:
- `_gsd_depot` - Depot ID (for depot pickup)
- `_gsd_depot_name` - Depot name (for depot pickup)
- `_gsd_courier` - Courier name (for depot pickup)
- `_gsd_home_delivery` - 'yes' or 'no'
- `_gsd_home_delivery_price` - Price (for home delivery)

This means:
- ✅ Order display pages still work
- ✅ Order emails still work
- ✅ Admin order pages still work
- ✅ Existing orders are unaffected

### Backward Compatibility
- Existing orders retain their delivery information
- No database migration needed
- All existing order display logic continues to work

## Testing Recommendations

### Scenario 1: Depot Pickup
1. Add product with courier assigned to cart
2. Go to checkout
3. Verify multiple depot options appear as shipping rates
4. Select a depot option
5. Complete order
6. Verify order shows correct depot information

### Scenario 2: Home Delivery
1. Add product with home delivery enabled to cart
2. Go to checkout
3. Verify "Home Delivery (+$X.XX)" appears as shipping rate
4. Select home delivery option
5. Verify price is added to order total
6. Complete order
7. Verify order shows home delivery information

### Scenario 3: Mixed Options
1. Add product with both depot and home delivery options to cart
2. Go to checkout
3. Verify both types of shipping rates appear
4. Test selecting each option
5. Verify correct information is saved

## Edge Cases Handled

1. **Product with courier but no home delivery**: Only depot rates appear
2. **Product with home delivery but no courier**: Only home delivery rate appears
3. **Multiple products**: Uses first product's courier and highest home delivery price
4. **Disabled courier**: No rates appear for that courier
5. **No depots configured**: No depot rates appear

## Known Limitations

1. **Multiple couriers in cart**: Uses the first product's courier (same as before)
2. **Per-depot pricing**: All depot pickups are free (same as before)
3. **Quantity-based pricing**: Home delivery fee applies per order, not per item (same as before)

## Future Enhancements (Optional)

1. Add per-depot pricing capability
2. Handle multiple couriers in a single cart
3. Add express delivery as additional shipping rates
4. Allow customers to add delivery notes specific to shipping method
