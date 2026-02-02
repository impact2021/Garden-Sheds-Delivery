# Testing & Validation Guide

## How to Test the Garden Sheds Delivery Plugin

### Prerequisites
1. WordPress installation with WooCommerce active
2. At least one WooCommerce product created

### Step 1: Activate the Plugin

1. Upload the plugin to `/wp-content/plugins/garden-sheds-delivery/`
2. Go to WordPress Admin > Plugins
3. Activate "Garden Sheds Delivery"
4. The plugin will automatically create default courier companies (Main Freight and PBT) with sample depot locations

### Step 2: Configure Courier Companies (Optional)

1. Go to **WooCommerce > Shed Delivery**
2. Review the default courier companies:
   - **Main Freight**: Auckland Depot, Wellington Depot, Christchurch Depot
   - **PBT**: North Island Hub, South Island Hub
3. You can add more depots by clicking **+ Add Depot**
4. Click **Save Settings**

### Step 3: Configure Product Delivery Options

1. Go to **Products** and edit a product (or create a new one)
2. Click the **Delivery Options** tab
3. Configure the following:
   - **Courier Company**: Select either "Main Freight" or "PBT"
   - **Home Delivery Available**: Check this box to enable optional home delivery
   - **Home Delivery Price**: Set the price (default is 150)
4. Update/Publish the product

### Step 4: Test Customer Checkout Flow

#### Scenario A: Depot Pickup Only
1. Add a product (with courier assigned but NO home delivery) to cart
2. Go to checkout
3. **Expected**: You should see a depot selection dropdown with locations from the assigned courier
4. **Expected**: Depot selection is required
5. Select a depot and complete the order
6. **Expected**: Order confirmation shows the selected depot

#### Scenario B: Home Delivery Option Available
1. Add a product (with courier assigned AND home delivery available) to cart
2. Go to checkout
3. **Expected**: You should see:
   - Depot selection dropdown
   - Home delivery checkbox with price (e.g., "Home Delivery (+$150.00)")
4. Test Option 1: Select a depot (don't check home delivery)
   - **Expected**: Order total does NOT include delivery fee
   - **Expected**: Order shows depot pickup
5. Test Option 2: Check home delivery box
   - **Expected**: Depot becomes optional
   - **Expected**: Order total increases by delivery fee
   - **Expected**: Order shows home delivery method

#### Scenario C: Multiple Products from Different Couriers
1. Create two products:
   - Product A: Assigned to "Main Freight"
   - Product B: Assigned to "PBT"
2. Add Product A to cart
3. **Expected**: Depot options show Main Freight locations
4. Note: The plugin currently uses the first product's courier for cart-wide delivery settings

### Step 5: Verify Order Details

After placing an order with delivery options:

1. **Customer Order Confirmation Page**:
   - Check for "Delivery Information" section
   - Should show: Courier name, Delivery method (Home Delivery or Depot name)

2. **Order Confirmation Email**:
   - Should include delivery information in both HTML and plain text versions

3. **Admin Order Page**:
   - Go to WooCommerce > Orders
   - Open the order
   - Check for "Delivery Information" section in the order details
   - Should show courier, delivery method, and price if applicable

### Expected Data Flow

```
Product Configuration → Cart/Checkout → Order Meta → Display
     ↓                       ↓              ↓           ↓
_gsd_courier           Depot Select    _gsd_depot    Order Page
_gsd_home_delivery_    Home Delivery   _gsd_home_    Email
  available             Checkbox         delivery     Admin
_gsd_home_delivery_                    _gsd_courier
  price
```

### Edge Cases to Test

1. **No Courier Assigned**: Product without courier assignment should not show delivery options
2. **Empty Cart**: No delivery options should appear
3. **Validation**: Try to checkout without selecting depot (when home delivery not checked) - should show error
4. **Price Calculation**: Verify home delivery fee is correctly added to order total
5. **Multiple Quantities**: Order multiple quantities of same product - delivery fee should apply once

### Common Issues & Solutions

**Issue**: Delivery options not showing on checkout
- **Solution**: Ensure product has courier assigned in Delivery Options tab

**Issue**: Depot dropdown is empty
- **Solution**: Check that the assigned courier has depot locations in WooCommerce > Shed Delivery

**Issue**: Home delivery price not updating
- **Solution**: Clear cart and re-add product after changing home delivery price

**Issue**: JavaScript not working (depot not becoming optional)
- **Solution**: Check browser console for errors, ensure jQuery is loaded

## Code Quality Checks

### PHP Validation
The plugin follows WordPress coding standards:
- All data is sanitized on input
- All output is escaped
- Uses WordPress nonces for form security
- Follows singleton pattern for class instances

### Security Features
- Admin-only access to settings page
- Nonce validation on all form submissions
- Input sanitization and validation
- Output escaping
- Capability checks (`manage_woocommerce`)

### Performance Considerations
- Minimal database queries
- Efficient option storage for courier data
- Conditional script loading (only on cart/checkout)
- No heavy external dependencies

## Success Criteria

✅ Plugin activates without errors
✅ Default courier companies are created
✅ Product delivery options appear in admin
✅ Depot selection appears on checkout
✅ Home delivery option works correctly
✅ Order meta is saved properly
✅ Delivery info displays in order confirmation
✅ Admin can manage couriers and depots
✅ Pricing calculation is accurate
✅ Validation prevents incomplete orders
