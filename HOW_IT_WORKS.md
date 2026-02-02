# How the Garden Sheds Delivery System Works

## Important: New Shipping Rates System

As of version 1.2.x, the delivery options are now presented as **WooCommerce shipping rates** instead of separate dropdown and checkbox fields.

## What Changed

### Old System (Deprecated)
- Single shipping method: "Shed Delivery"
- Depot dropdown field below order notes
- Home delivery checkbox below order notes
- Fees added via cart calculations

### New System (Current)
- Multiple shipping rate options appear in the standard WooCommerce shipping section
- Each depot is a separate rate option: "Pickup from [Depot Name]"
- Home delivery is a separate rate option: "Home Delivery (+$150.00)"
- Fees are built into the shipping rate cost
- **Customer selects ONE shipping option**

## How Customers See It

When a customer adds a shed product to their cart and goes to checkout, they will see shipping options like:

```
○ Pickup from Auckland Depot (Free)
○ Pickup from Wellington Depot (Free)
○ Pickup from Christchurch Depot (Free)
○ Home Delivery (+$150.00)
```

They simply select their preferred option. The cost is automatically added to their order total.

## How It Works Technically

### 1. Products Must Be Configured

For shipping rates to appear, products must have either:
- A courier assigned (in the product's Delivery Options tab), OR
- Home delivery enabled (in the product's Delivery Options tab)

### 2. Shipping Method Must Be in a Shipping Zone

The "Garden Sheds Delivery" shipping method must be added to at least one WooCommerce shipping zone for it to appear at checkout.

To configure:
1. Go to WooCommerce > Settings > Shipping
2. Click on a shipping zone (or create one)
3. Add "Garden Sheds Delivery" as a shipping method

### 3. Rates Are Generated Dynamically

When `calculate_shipping()` is called:
- If the cart contains a product with a courier assigned, rates are created for each depot
- If the cart contains a product with home delivery enabled, a home delivery rate is created
- Each rate includes the appropriate cost (0 for depot pickup, configured price for home delivery)

### 4. Order Data Is Saved

When the order is created, the `save_shipping_method_data()` method extracts information from the selected shipping rate and saves it to the order as meta data:

**For Depot Pickup:**
- `_gsd_depot` - Depot ID
- `_gsd_depot_name` - Depot name
- `_gsd_courier` - Courier company name
- `_gsd_home_delivery` - 'no'

**For Home Delivery:**
- `_gsd_home_delivery` - 'yes'
- `_gsd_home_delivery_price` - Price charged

## Troubleshooting

### "No shipping rates appear at checkout"

**Possible causes:**
1. Shipping method not added to a shipping zone
2. Product doesn't have courier assigned or home delivery enabled
3. Courier is disabled in admin settings
4. Courier has no depots configured

**Solutions:**
1. Add "Garden Sheds Delivery" to a shipping zone
2. Edit product > Delivery Options tab > assign courier or enable home delivery
3. Check WooCommerce > Shed Delivery > Depot Locations > ensure courier is enabled
4. Check WooCommerce > Shed Delivery > Depot Locations > add depots

### "Home delivery fee not being added to total"

**This should not happen** if you're using the new system correctly. The home delivery rate includes the cost in the 'cost' parameter, which WooCommerce automatically adds to the order total.

**If this is happening:**
1. Verify you're seeing "Home Delivery (+$XXX)" as a shipping RATE option (not a checkbox)
2. Verify the shipping rate is selected (should be a radio button)
3. Check that the home delivery price is configured (default $150 or custom per product)
4. Clear your browser cache and WooCommerce transients

### "I want a dropdown to select depot instead of separate radio buttons"

This is **by design**. The new system uses WooCommerce's standard shipping rate interface, which provides:
- Better user experience (one selection instead of two)
- Standard WooCommerce UI (familiar to customers)
- Automatic cost calculation
- Better mobile responsiveness

If you have many depots (10+), consider:
- Grouping depots by region (North Island, South Island)
- Creating multiple courier companies by region
- Using fewer depot locations

### "Can I go back to the old dropdown/checkbox system?"

This would require custom development to restore the removed checkout fields code. The new system is recommended for:
- Consistency with WooCommerce standards
- Clearer pricing transparency
- Simplified checkout process
- Easier maintenance

## For Developers

### Key Files

- `includes/class-gsd-shipping-method.php` - Creates shipping rates
- `includes/class-gsd-checkout.php` - Saves shipping data to orders
- `includes/class-gsd-product-settings.php` - Product configuration
- `includes/class-gsd-courier.php` - Courier and depot management

### Shipping Rate Structure

Depot rates:
```php
array(
    'id' => 'garden_sheds_delivery:1:depot:mf_depot_1',
    'label' => 'Pickup from Auckland Depot',
    'cost' => 0,
    'meta_data' => array(
        'depot_id' => 'mf_depot_1',
        'depot_name' => 'Auckland Depot',
        'courier_name' => 'Main Freight',
        'delivery_type' => 'depot'
    )
)
```

Home delivery rate:
```php
array(
    'id' => 'garden_sheds_delivery:1:home_delivery',
    'label' => 'Home Delivery (+$150.00)',
    'cost' => 150.00,
    'meta_data' => array(
        'delivery_type' => 'home_delivery',
        'home_delivery_price' => 150.00
    )
)
```

### Hooks and Filters

The plugin uses standard WooCommerce hooks:
- `woocommerce_shipping_methods` - Registers the shipping method
- `woocommerce_checkout_create_order` - Saves delivery data to order

## Summary

The new shipping rates system provides a cleaner, more standard WooCommerce experience. Instead of separate depot dropdown and home delivery checkbox fields, customers now see all options as shipping rates and select one. The fee is automatically included in the shipping cost, making pricing transparent and checkout simpler.
