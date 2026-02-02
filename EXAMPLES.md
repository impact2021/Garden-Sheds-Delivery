# Usage Examples

## Example 1: Basic Setup with Main Freight

### Product Configuration
```
Product: "Garden Shed 10x8"
Courier Company: Main Freight
Home Delivery Available: Yes
Home Delivery Price: $150.00
```

### Customer Experience
When the customer goes to checkout, they see:

```
┌─────────────────────────────────────────────────┐
│ Delivery Options                                │
├─────────────────────────────────────────────────┤
│ Select Depot Location:                          │
│ [Auckland Depot                          ▼]     │
│                                                  │
│ ☐ Home Delivery (+$150.00)                     │
└─────────────────────────────────────────────────┘
```

**Option 1: Pick up from depot**
- Customer selects "Auckland Depot"
- Order total: $X (product price only)

**Option 2: Home delivery**
- Customer checks "Home Delivery"
- Order total: $X + $150.00

## Example 2: PBT Courier with Depot Only

### Product Configuration
```
Product: "Garden Shed 12x10"
Courier Company: PBT
Home Delivery Available: No
```

### Customer Experience
```
┌─────────────────────────────────────────────────┐
│ Delivery Options                                │
├─────────────────────────────────────────────────┤
│ Select Depot Location:                          │
│ [-- Select Depot --                      ▼]     │
│   North Island Hub                               │
│   South Island Hub                               │
└─────────────────────────────────────────────────┘
```

Customer must select a depot (home delivery not available).

## Example 3: Order Confirmation Display

### After Order is Placed
```
┌─────────────────────────────────────────────────┐
│ Order Details                                    │
├─────────────────────────────────────────────────┤
│ Order #12345                                     │
│                                                  │
│ Items:                                           │
│ - Garden Shed 10x8  x1      $2,499.00           │
│                                                  │
│ Subtotal:                   $2,499.00           │
│ Home Delivery:              $150.00             │
│ Total:                      $2,649.00           │
│                                                  │
│ ┌─────────────────────────────────────────────┐ │
│ │ Delivery Information                        │ │
│ ├─────────────────────────────────────────────┤ │
│ │ Courier:         Main Freight               │ │
│ │ Delivery Method: Home Delivery ($150.00)    │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

## Example 4: Admin Order View

### WooCommerce Admin Order Page
```
┌─────────────────────────────────────────────────┐
│ Order #12345                                     │
├─────────────────────────────────────────────────┤
│ General                                          │
│ Date: February 2, 2026                          │
│ Status: Processing                               │
│                                                  │
│ Billing                    Shipping              │
│ John Smith                 John Smith            │
│ 123 Main St                123 Main St           │
│ Auckland 1010              Auckland 1010         │
│                                                  │
│ Delivery Information                             │
│ Courier: Main Freight                            │
│ Delivery Method: Home Delivery ($150.00)         │
└─────────────────────────────────────────────────┘
```

## Example 5: Admin Courier Management

### WooCommerce > Shed Delivery Settings
```
┌──────────────────────────────────────────────────────────────┐
│ Garden Sheds Delivery Settings                               │
├──────────────────────────────────────────────────────────────┤
│ Courier Companies                                            │
│                                                              │
│ ┌──────────────┬───────────┬─────────────────────────────┐ │
│ │ Courier Name │ Slug      │ Depot Locations             │ │
│ ├──────────────┼───────────┼─────────────────────────────┤ │
│ │ Main Freight │ main_     │ [Auckland Depot      ]      │ │
│ │              │ freight   │ [Wellington Depot    ]      │ │
│ │              │           │ [Christchurch Depot  ]      │ │
│ │              │           │ [+ Add Depot]               │ │
│ ├──────────────┼───────────┼─────────────────────────────┤ │
│ │ PBT          │ pbt       │ [North Island Hub    ]      │ │
│ │              │           │ [South Island Hub    ]      │ │
│ │              │           │ [+ Add Depot]               │ │
│ └──────────────┴───────────┴─────────────────────────────┘ │
│                                                              │
│ [Save Settings]                                              │
└──────────────────────────────────────────────────────────────┘
```

## Example 6: Product Edit Screen

### Products > Edit Product > Delivery Options Tab
```
┌─────────────────────────────────────────────────┐
│ General │ Inventory │ Shipping │ Delivery Options│
├─────────────────────────────────────────────────┤
│                                                  │
│ Courier Company                                  │
│ [Main Freight                             ▼]    │
│                                                  │
│ ☑ Home Delivery Available                      │
│                                                  │
│ Home Delivery Price ($)                          │
│ [150                                      ]      │
│                                                  │
└─────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
┌─────────────────┐
│   Admin Panel   │
│  (Configure)    │
└────────┬────────┘
         │
         ├─> Courier Companies & Depots (stored in options)
         │
         ├─> Product Settings (stored as post meta)
         │   - Courier assignment
         │   - Home delivery available
         │   - Home delivery price
         │
         ▼
┌─────────────────┐
│  Product Page   │
│  (with courier  │
│   assigned)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Add to Cart   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Checkout Page  │
│                 │
│  1. Show depot  │
│     selection   │
│     (filtered   │
│     by courier) │
│                 │
│  2. Show home   │
│     delivery    │
│     option      │
│     (if enabled)│
└────────┬────────┘
         │
         ├─> Validate: depot OR home delivery required
         │
         ▼
┌─────────────────┐
│  Create Order   │
│  Save meta:     │
│  - Depot ID     │
│  - Courier      │
│  - Home del.    │
│  - Price        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Display on:     │
│ - Order page    │
│ - Admin panel   │
│ - Emails        │
└─────────────────┘
```

## Real-World Scenarios

### Scenario 1: Small Town Customer
**Customer**: Lives in Christchurch
**Product**: Medium shed delivered by Main Freight
**Choice**: Picks up from Christchurch Depot (saves $150)
**Result**: Lower cost, customer arranges own transport

### Scenario 2: Busy Professional
**Customer**: Lives in central Auckland
**Product**: Large shed delivered by PBT
**Choice**: Opts for home delivery ($150)
**Result**: Convenience, shed delivered to home

### Scenario 3: Rural Customer
**Customer**: Lives in rural area near Hamilton
**Product**: Any shed
**Choice**: Selects Auckland Depot as closest option
**Result**: Drives to depot for pickup, saves delivery fee

## Integration Points

The plugin integrates with WooCommerce at these points:

1. **Product Editor**: Adds "Delivery Options" tab
2. **Checkout Page**: Adds delivery selection fields
3. **Cart Calculation**: Adds home delivery fee
4. **Order Processing**: Saves delivery preferences
5. **Order Display**: Shows delivery information
6. **Admin Panel**: Courier management interface
7. **Email Templates**: Includes delivery details

## API Reference (for developers)

### Get Product Courier
```php
$courier_slug = GSD_Product_Settings::get_product_courier($product_id);
```

### Check Home Delivery Availability
```php
$is_available = GSD_Product_Settings::is_home_delivery_available($product_id);
```

### Get Home Delivery Price
```php
$price = GSD_Product_Settings::get_home_delivery_price($product_id);
```

### Get All Couriers
```php
$couriers = GSD_Courier::get_couriers();
```

### Get Depots for a Courier
```php
$depots = GSD_Courier::get_depots('main_freight');
```
