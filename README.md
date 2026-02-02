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

1. **Set Default Home Delivery Cost**: Enter the default cost (e.g., $150)
2. **Select Categories**: Check product categories that should offer home delivery
3. **Manage Depots**: Add or edit depot locations for courier companies
4. Click **Save Settings**

### 2. Set Up WooCommerce Shipping

1. Go to **WooCommerce > Settings > Shipping**
2. Select or create a shipping zone
3. Add **Garden Sheds Delivery** as a shipping method
4. Enable the method

### 3. Configure Products

1. Edit a product in WooCommerce
2. Go to the **Delivery Options** tab
3. Select the **Courier Company** 
4. (Optional) Override home delivery price or settings
5. Save the product

## Configuration

### Global Settings (Shed Delivery Menu)

#### Home Delivery Options

- **Default Home Delivery Cost**: Set the default cost used across all products (can be overridden per product)
- **Categories with Home Delivery**: Select which product categories should automatically offer home delivery

#### Courier Companies

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

### Checkout Process

When a customer adds a product with delivery options to their cart:

1. **Shipping Method**: "Shed Delivery" appears as the shipping option
2. **Depot Selection**: Dropdown to select preferred depot location (filtered by courier)
3. **Home Delivery Option**: Checkbox to opt for home delivery (if enabled)
4. **Contact Notice**: Message about contacting for delivery (if configured)
5. **Pricing**: Home delivery fee automatically added to order total
6. **Validation**: System validates depot selection or home delivery choice

### Order Confirmation

The selected delivery method and depot location are displayed:
- On the order confirmation page
- In order confirmation emails
- In the admin order details page

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
