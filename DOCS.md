# Garden Sheds Delivery Plugin Documentation

## Table of Contents
1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Features](#features)
5. [Usage Guide](#usage-guide)
6. [Troubleshooting](#troubleshooting)
7. [Developer Reference](#developer-reference)

---

## Overview

The Garden Sheds Delivery plugin is a comprehensive WooCommerce extension that manages delivery options for garden sheds and similar products. It provides flexible delivery options including depot pickup and home delivery, with support for multiple courier companies.

### Key Features
- **Multiple Courier Companies**: Support for multiple courier companies (Main Freight, PBT, etc.)
- **Depot Location Selection**: Customers can choose from multiple depot locations for pickup
- **Home Delivery Options**: Optional paid home delivery service
- **Category-Based Settings**: Automatically enable home delivery for specific product categories
- **Flexible Pricing**: Set default delivery costs globally or override per product
- **WooCommerce Shipping Integration**: Seamlessly integrates with WooCommerce's shipping system

---

## Installation

### Requirements
- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.2 or higher

### Installation Steps

1. **Upload the Plugin**
   - Upload the `garden-sheds-delivery` folder to `/wp-content/plugins/`
   - Or install directly from the WordPress admin by uploading the ZIP file

2. **Activate the Plugin**
   - Go to **Plugins** in WordPress admin
   - Find "Garden Sheds Delivery" and click **Activate**

3. **Verify WooCommerce is Active**
   - The plugin requires WooCommerce to be installed and active
   - You'll see a notice if WooCommerce is not detected

4. **Initial Setup**
   - Upon activation, default courier companies are created automatically
   - Navigate to **Shed Delivery** in the admin menu to configure settings

---

## Configuration

### Accessing Settings

The plugin settings are accessible from the WordPress admin menu:
- **Main Menu**: Look for **Shed Delivery** in the sidebar (with a store icon)
- This is a top-level menu item for easy access

### Global Settings

#### Home Delivery Options

1. **Default Home Delivery Cost**
   - Set the default cost for home delivery across all products
   - Default value: $150
   - This can be overridden on individual products
   - Location: **Shed Delivery > Home Delivery Options**

2. **Categories with Home Delivery**
   - Select which product categories should automatically offer home delivery
   - Products in selected categories will have home delivery available by default
   - Individual products can still override this setting
   - Example: Select "Garden Sheds" category to enable home delivery for all sheds

#### Courier Companies

1. **Managing Couriers**
   - The plugin comes with two default couriers: Main Freight and PBT
   - Each courier can have multiple depot locations

2. **Adding Depot Locations**
   - Click **+ Add Depot** button for any courier
   - Enter the depot name (e.g., "Auckland Depot", "Wellington Depot")
   - Depot IDs are automatically generated

3. **Default Depots**
   - **Main Freight**: Auckland, Wellington, Christchurch
   - **PBT**: North Island Hub, South Island Hub

---

## Features

### 1. WooCommerce Shipping Integration

The plugin registers as a shipping method in WooCommerce, replacing standard shipping options for products with shed delivery configured.

**How it works:**
- When a customer adds a product configured for shed delivery, the "Shed Delivery" shipping method becomes available
- Other shipping methods are automatically hidden for these products
- This prevents the "No shipping options were found" error

**Setup:**
1. Go to **WooCommerce > Settings > Shipping**
2. Create or edit a shipping zone
3. Add "Garden Sheds Delivery" as a shipping method
4. Enable and configure the method

### 2. Category-Based Home Delivery

Automatically enable home delivery for entire product categories:

**Benefits:**
- No need to manually enable home delivery on each product
- Consistent delivery options across product categories
- Easy bulk management

**Configuration:**
1. Go to **Shed Delivery** in admin menu
2. Find **Categories with Home Delivery** section
3. Check the categories that should offer home delivery
4. Click **Save Settings**

**Example:**
- Select "Garden Sheds" category
- All products in this category now offer home delivery
- Individual products can still override with custom pricing

### 3. Flexible Delivery Pricing

Three levels of pricing control:

**Global Default**
- Set in **Shed Delivery > Default Home Delivery Cost**
- Applied to all products unless overridden
- Quick way to set a standard price

**Category-Based**
- Products in selected categories use the global default
- Ensures consistency within categories

**Product-Specific**
- Set custom price on individual products
- Overrides global default
- Found in **Product > Delivery Options** tab

### 4. Depot Selection

Customers choose their preferred depot during checkout:

**Features:**
- Dropdown selection of available depots
- Filtered by product's assigned courier
- Required unless home delivery is selected
- Depot information saved with order

**Customer Experience:**
1. Add product to cart
2. Proceed to checkout
3. Select depot from dropdown OR
4. Check "Home Delivery" option
5. Complete purchase

### 5. Product-Level Configuration

Each product can be individually configured:

**Product Settings** (Product Edit > Delivery Options tab):
- **Courier Company**: Select which courier delivers this product
- **Home Delivery Available**: Manually enable/disable home delivery
- **Home Delivery Price**: Set custom price (optional)
- **Contact for Delivery**: Show "contact us" message instead

**Note:** Products in selected categories automatically have home delivery available

---

## Usage Guide

### For Store Administrators

#### Setting Up a New Product

1. **Create/Edit Product**
   - Go to **Products > Add New** or edit existing

2. **Configure Delivery** (Delivery Options tab)
   - Select **Courier Company** (e.g., Main Freight)
   - Home delivery is automatically enabled if product is in a selected category
   - Optionally set custom **Home Delivery Price**
   - Or enable **"Contact Us" for Home Delivery** for quote-based delivery

3. **Save Product**

#### Managing Categories

1. **Enable Home Delivery for Category**
   - Go to **Shed Delivery** menu
   - Check categories under **Categories with Home Delivery**
   - All products in these categories will offer home delivery

2. **Set Default Cost**
   - Enter amount in **Default Home Delivery Cost** field
   - This applies to all products unless overridden

#### Managing Courier Depots

1. **Add New Depot**
   - Go to **Shed Delivery > Courier Companies**
   - Find the courier
   - Click **+ Add Depot**
   - Enter depot name
   - Click **Save Settings**

2. **Edit Depot**
   - Find depot in the list
   - Update the name
   - Click **Save Settings**

### For Customers

#### Checkout Process

1. **Add Product to Cart**
   - Product must have a courier assigned

2. **View Delivery Options**
   - Delivery options appear before checkout form
   - See available depots for the courier

3. **Choose Delivery Method**
   - **Option A**: Select a depot from dropdown (free pickup)
   - **Option B**: Check "Home Delivery" box (additional fee shown)

4. **Complete Checkout**
   - Selected delivery method is saved with order
   - Shown in order confirmation email

#### Order Confirmation

Delivery details are displayed:
- **Order Confirmation Page**: Shows depot or home delivery selection
- **Order Emails**: Includes delivery information
- **My Account > Orders**: Delivery details visible

---

## Troubleshooting

### Common Issues

#### "No shipping options were found"

**Cause:** The Garden Sheds Delivery shipping method is not added to a shipping zone.

**Solution:**
1. Go to **WooCommerce > Settings > Shipping**
2. Click on a shipping zone (or create one)
3. Click **Add shipping method**
4. Select **Garden Sheds Delivery**
5. Save changes

#### Home Delivery Not Showing

**Possible causes and solutions:**

1. **Category not selected**
   - Check **Shed Delivery** settings
   - Ensure product category is checked under **Categories with Home Delivery**

2. **Product setting disabled**
   - Edit the product
   - Go to **Delivery Options** tab
   - Ensure **Home Delivery Available** is checked

3. **Courier not assigned**
   - Edit the product
   - Select a **Courier Company** in **Delivery Options** tab

#### Depot Dropdown Empty

**Cause:** No depots configured for selected courier.

**Solution:**
1. Go to **Shed Delivery** menu
2. Find the courier company
3. Click **+ Add Depot** to add depot locations
4. Save settings

#### Wrong Price Showing

**Check the following:**
1. **Product-specific price** (overrides default)
   - Edit product > Delivery Options tab
   - Check **Home Delivery Price** field

2. **Global default**
   - Go to **Shed Delivery** settings
   - Check **Default Home Delivery Cost**

---

## Developer Reference

### Hooks and Filters

#### Actions

```php
// After plugin initialization
do_action('gsd_init');

// Before saving courier companies
do_action('gsd_before_save_couriers', $couriers);

// After saving courier companies
do_action('gsd_after_save_couriers', $couriers);
```

#### Filters

```php
// Filter courier companies
$couriers = apply_filters('gsd_courier_companies', $couriers);

// Filter home delivery categories
$categories = apply_filters('gsd_home_delivery_categories', $categories);

// Filter default home delivery cost
$cost = apply_filters('gsd_default_home_delivery_cost', $cost);

// Filter product home delivery availability
$is_available = apply_filters('gsd_is_home_delivery_available', $is_available, $product_id);

// Filter product home delivery price
$price = apply_filters('gsd_home_delivery_price', $price, $product_id);
```

### API Functions

#### Getting Courier Information

```php
// Get all couriers
$couriers = GSD_Courier::get_couriers();

// Get specific courier
$courier = GSD_Courier::get_courier('main_freight');

// Get courier depots
$depots = GSD_Courier::get_depots('main_freight');
```

#### Product Delivery Settings

```php
// Get product courier
$courier_slug = GSD_Product_Settings::get_product_courier($product_id);

// Check if home delivery available
$is_available = GSD_Product_Settings::is_home_delivery_available($product_id);

// Get home delivery price
$price = GSD_Product_Settings::get_home_delivery_price($product_id);

// Check if "contact for delivery" enabled
$contact = GSD_Product_Settings::is_contact_for_delivery($product_id);
```

### Database Schema

#### Options Table

| Option Name | Type | Description |
|-------------|------|-------------|
| `gsd_courier_companies` | array | All courier companies and depots |
| `gsd_home_delivery_categories` | array | Category IDs with home delivery |
| `gsd_default_home_delivery_cost` | string | Default delivery cost |

#### Post Meta (Products)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_gsd_courier` | string | Assigned courier slug |
| `_gsd_home_delivery_available` | string | 'yes' or 'no' |
| `_gsd_home_delivery_price` | string | Custom delivery price |
| `_gsd_contact_for_delivery` | string | 'yes' or 'no' |

#### Order Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `_gsd_depot` | string | Selected depot ID |
| `_gsd_depot_name` | string | Depot display name |
| `_gsd_courier` | string | Courier company name |
| `_gsd_home_delivery` | string | 'yes' or 'no' |
| `_gsd_home_delivery_price` | float | Charged delivery price |
| `_gsd_contact_for_delivery` | string | 'yes' or 'no' |

### File Structure

```
garden-sheds-delivery/
├── garden-sheds-delivery.php          # Main plugin file
├── includes/
│   ├── class-gsd-courier.php          # Courier management
│   ├── class-gsd-product-settings.php # Product delivery settings
│   ├── class-gsd-checkout.php         # Checkout process
│   ├── class-gsd-order.php           # Order display
│   ├── class-gsd-admin.php           # Admin settings
│   └── class-gsd-shipping-method.php # WooCommerce shipping integration
├── assets/
│   ├── css/
│   │   └── frontend.css              # Frontend styles
│   └── js/
│       └── frontend.js               # Frontend scripts
└── DOCS.md                           # This documentation file
```

---

## Support

For issues, feature requests, or questions:
1. Check this documentation first
2. Review the [troubleshooting section](#troubleshooting)
3. Submit an issue on the GitHub repository
4. Contact the plugin developer

## Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## License

This plugin is provided as-is for the Impact 2021 project.
