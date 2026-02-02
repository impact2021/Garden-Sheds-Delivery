# Garden Sheds Delivery - WooCommerce Plugin

A WooCommerce plugin for managing courier delivery options for garden sheds with multiple depot locations and optional home delivery.

## Features

- **Multiple Courier Companies**: Support for multiple courier companies (Main Freight, PBT, etc.)
- **Depot Location Selection**: Each courier can have multiple depot locations that customers can choose from
- **Product-Level Courier Assignment**: Assign different products to different courier companies
- **Optional Home Delivery**: Products can have an optional home delivery option with configurable pricing
- **Contact for Delivery**: Display a message indicating home delivery may be available upon request
- **Flexible Delivery Options**: Customers can choose between picking up from a depot, paying for home delivery, or contacting for more information

## Installation

1. Upload the plugin files to the `/wp-content/plugins/garden-sheds-delivery` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure WooCommerce is installed and activated
4. Go to WooCommerce > Shed Delivery to configure courier companies and depot locations

## Configuration

### Setting Up Courier Companies

1. Navigate to **WooCommerce > Shed Delivery** in the WordPress admin
2. The plugin comes with two default courier companies:
   - **Main Freight** with depot locations (Auckland, Wellington, Christchurch)
   - **PBT** with depot locations (North Island Hub, South Island Hub)
3. You can add more depot locations by clicking the **+ Add Depot** button
4. Save your settings

### Configuring Product Delivery Options

1. Edit a product in WooCommerce
2. Go to the **Delivery Options** tab
3. Select the **Courier Company** that will deliver this product
4. Configure delivery availability:
   - Check **Home Delivery Available** if customers should have the option to pay for immediate home delivery with a set price
   - Set the **Home Delivery Price** (defaults to $150)
   - OR check **Show "Contact Us" for Home Delivery** to display a message that home delivery may be available after contacting the store
5. Save the product

## Customer Experience

### Checkout Process

When a customer adds a product with delivery options to their cart:

1. **Depot Selection**: On the checkout page, they will see a dropdown to select their preferred depot location (filtered by the product's assigned courier)
2. **Home Delivery Option** (if enabled): They can check a box to opt for home delivery instead
3. **Contact for Delivery Notice** (if enabled): A message displays: "Home delivery may be an option - please contact us after completing your order"
4. **Pricing**: If home delivery is selected, the delivery fee is automatically added to the order total
5. **Validation**: The system validates that either a depot is selected OR home delivery is chosen

### Order Confirmation

The selected delivery method and depot location are displayed:
- On the order confirmation page
- In order confirmation emails
- In the admin order details page

## Technical Details

### Plugin Structure

```
garden-sheds-delivery/
├── garden-sheds-delivery.php    # Main plugin file
├── includes/
│   ├── class-gsd-courier.php           # Courier management
│   ├── class-gsd-product-settings.php  # Product delivery settings
│   ├── class-gsd-checkout.php          # Checkout process
│   ├── class-gsd-order.php             # Order display
│   └── class-gsd-admin.php             # Admin settings
└── assets/
    ├── css/
    │   └── frontend.css
    └── js/
        └── frontend.js
```

### Data Storage

- **Courier Companies**: Stored in WordPress options table as `gsd_courier_companies`
- **Product Settings**: Stored as product meta data:
  - `_gsd_courier`: Assigned courier slug
  - `_gsd_home_delivery_available`: Whether home delivery is available (yes/no)
  - `_gsd_home_delivery_price`: Home delivery price
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

## Support

For issues or feature requests, please use the GitHub issue tracker.

## License

This plugin is provided as-is for the Impact 2021 project.
