# Garden Sheds Delivery - WooCommerce Plugin

A WooCommerce plugin for managing courier delivery options for garden sheds with multiple depot locations and optional home delivery.

## Features

- **WooCommerce Shipping Integration**: Integrates with WooCommerce shipping system to replace standard shipping options
- **Multiple Courier Companies**: Support for multiple courier companies (Main Freight, PBT, etc.)
- **Depot Location Selection**: Each courier can have multiple depot locations that customers can choose from
- **Product-Level Courier Assignment**: Assign different products to different courier companies
- **Category-Based Home Delivery**: Automatically enable home delivery for entire product categories
- **Flexible Pricing**: Set global default home delivery cost or override per product
- **Optional Home Delivery**: Products can have an optional home delivery option with configurable pricing
- **Contact for Delivery**: Display a message indicating home delivery may be available upon request
- **Standalone Admin Menu**: Dedicated "Shed Delivery" menu in WordPress admin for easy access
- **Flexible Delivery Options**: Customers can choose between picking up from a depot, paying for home delivery, or contacting for more information

## Installation

1. Upload the plugin files to the `/wp-content/plugins/garden-sheds-delivery` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure WooCommerce is installed and activated
4. Go to **Shed Delivery** (standalone menu) to configure settings
5. Configure WooCommerce shipping zones to use the Garden Sheds Delivery method

## Quick Start

### 1. Configure Global Settings

Navigate to **Shed Delivery** in the WordPress admin sidebar:

1. **Configure Delivery Options by Category**: Use the table to select which delivery options apply to each product category:
   - Check **Home Delivery** for categories that should offer home delivery
   - Check **Might be able to offer home delivery** for categories where delivery may be arranged on request
   - Check **Main Freight** for categories using Main Freight courier
   - Check **PBT** for categories using PBT courier
2. **Set Default Home Delivery Cost**: Enter the default cost (e.g., $150)
3. Click **Save Settings**

### 2. Manage Depot Locations

Navigate to **Shed Delivery > Depot Locations**:

1. **Manage Depot Locations**: Add or edit depot locations for courier companies
2. Click **Save Depot Locations**

### 3. Set Up WooCommerce Shipping

1. Go to **WooCommerce > Settings > Shipping**
2. Select or create a shipping zone
3. Add **Garden Sheds Delivery** as a shipping method
4. Enable the method

### 4. Configure Products

1. Edit a product in WooCommerce
2. Go to the **Delivery Options** tab
3. Select the **Courier Company** 
4. (Optional) Override home delivery price or settings
5. Save the product

## Configuration

### Global Settings (Shed Delivery Menu)

#### Delivery Options by Category

The main settings page displays a comprehensive table where you can configure delivery options for each product category:

- **Category Column**: Lists all your WooCommerce product categories
- **Home Delivery Column**: Enable home delivery option for products in this category
- **Might be able to offer home delivery Column**: Show a message that delivery may be available upon request
- **Main Freight Column**: Assign this category to Main Freight courier
- **PBT Column**: Assign this category to PBT courier

#### Default Costs

- **Default Home Delivery Cost**: Set the default cost used across all products (can be overridden per product)

### Depot Locations (Shed Delivery > Depot Locations)

Manage courier companies and their depot locations on a dedicated page:

The plugin comes with two default courier companies:
- **Main Freight** with depot locations (Auckland, Wellington, Christchurch)
- **PBT** with depot locations (North Island Hub, South Island Hub)

You can add more depot locations by clicking the **+ Add Depot** button.

### Product Settings

Edit any product and go to the **Delivery Options** tab:

1. **Courier Company**: Select which courier delivers this product
2. **Home Delivery Available**: Manually enable (overrides category setting)
3. **Home Delivery Price**: Set custom price (leave empty to use default)
4. **Show "Contact Us"**: Display contact message instead of price

## Customer Experience

### Checkout Process (New Shipping Rates System)

When a customer adds a product with delivery options to their cart and proceeds to checkout:

1. **Multiple Shipping Options**: Instead of a single "Shed Delivery" method, customers see individual shipping rate options:
   - **Depot Pickup Options**: One radio button for each depot (e.g., "Pickup from Auckland Depot", "Pickup from Wellington Depot") - **Free**
   - **Home Delivery Option**: One radio button for home delivery (e.g., "Home Delivery (+$150.00)") - **Adds fee**

2. **Simple Selection**: Customer selects ONE shipping option
   - If they choose a depot, pickup is free
   - If they choose home delivery, the fee is automatically added to the order total

3. **Transparent Pricing**: The home delivery fee is clearly shown in the option label (e.g., "+$150.00")

4. **Standard WooCommerce UI**: The options appear in the standard shipping method section, not as custom fields below order notes

**Example of what customers see:**
```
Shipping Method:
○ Pickup from Auckland Depot (Free)
○ Pickup from Wellington Depot (Free)  
○ Pickup from Christchurch Depot (Free)
● Home Delivery (+$150.00)

Shipping: $150.00
```

### How This Works

- Each depot becomes a separate shipping rate with $0 cost
- Home delivery is a separate shipping rate with the configured cost
- WooCommerce automatically adds the selected rate's cost to the order total
- No separate dropdown or checkbox is needed

### Order Confirmation

The selected delivery method is displayed:
- On the order confirmation page
- In order confirmation emails  
- In the admin order details page

For depot pickup, it shows: "Pickup from [Depot Name]"  
For home delivery, it shows: "Home Delivery ($XXX.XX)"

### Important Notes

See [HOW_IT_WORKS.md](HOW_IT_WORKS.md) for detailed information about:
- How the new shipping rates system works
- Troubleshooting common issues
- Differences from the old dropdown/checkbox system

## Technical Details

### Plugin Structure

```
garden-sheds-delivery/
├── garden-sheds-delivery.php              # Main plugin file
├── includes/
│   ├── class-gsd-courier.php              # Courier management
│   ├── class-gsd-product-settings.php     # Product delivery settings
│   ├── class-gsd-checkout.php             # Checkout process
│   ├── class-gsd-order.php                # Order display
│   ├── class-gsd-admin.php                # Admin settings
│   └── class-gsd-shipping-method.php      # WooCommerce shipping integration
├── assets/
│   ├── css/
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
└── DOCS.md                                # Comprehensive documentation
```

### Data Storage

- **Global Settings**:
  - `gsd_courier_companies`: All courier companies and depots
  - `gsd_home_delivery_categories`: Category IDs with home delivery enabled
  - `gsd_default_home_delivery_cost`: Default delivery cost
  
- **Product Settings**: Stored as product meta data:
  - `_gsd_courier`: Assigned courier slug
  - `_gsd_home_delivery_available`: Whether home delivery is available (yes/no)
  - `_gsd_home_delivery_price`: Home delivery price (empty = use default)
  - `_gsd_contact_for_delivery`: Whether to show "contact us" message (yes/no)
  
- **Order Data**: Stored as order meta data:
  - `_gsd_depot`: Selected depot ID
  - `_gsd_depot_name`: Selected depot name
  - `_gsd_courier`: Courier company name
  - `_gsd_home_delivery`: Whether home delivery was selected (yes/no)
  - `_gsd_home_delivery_price`: Home delivery price charged
  - `_gsd_contact_for_delivery`: Whether "contact us" option was shown (yes/no)

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.2 or higher

## Documentation

See [DOCS.md](DOCS.md) for comprehensive documentation including:
- Detailed configuration guide
- Troubleshooting tips
- Developer API reference
- Hooks and filters

## Support

For issues or feature requests, please use the GitHub issue tracker.

## License

This plugin is provided as-is for the Impact 2021 project.
