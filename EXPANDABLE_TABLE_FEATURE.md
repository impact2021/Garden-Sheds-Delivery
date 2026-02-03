# Expandable Product Table Feature

## Overview
The admin settings page now includes an expandable table that allows you to configure shipping options for individual products within each category.

## Location
**Admin Menu**: Shed Delivery → Shed Delivery Settings  
**URL**: `wp-admin/admin.php?page=garden-sheds-delivery`

## How to Use

### 1. View the Category Table
The main settings page displays all product categories with checkboxes for different shipping options:
- Home Delivery
- Small Items
- Might be able to offer home delivery
- Main Freight
- PBT

### 2. Expand a Category
- Click the **arrow icon** (►) next to any category name
- The arrow will rotate to point down (▼)
- A product list will appear below the category row

### 3. Manage Individual Products
When expanded, you'll see:
- A table listing all products in that category
- Each product has checkboxes for:
  - **Home Delivery**: Enable home delivery option for this product
  - **Small Items**: Enable small items delivery for this product
  - **Contact for Delivery**: Show "contact us" message for this product
- Product names are clickable links that open the product edit page in a new tab

### 4. Save Product Settings
- After making changes to products, click the **"Save Product Settings"** button at the bottom of the product list
- The button will show "Saving..." while processing
- After successful save, it will briefly show "Saved!" before returning to normal

### 5. Collapse a Category
- Click the arrow icon again to collapse the product list
- Your changes are saved and will persist

## Features

### Performance Optimizations
- **Lazy Loading**: Products are only loaded when you expand a category (not all at once)
- **AJAX Loading**: Products load asynchronously without refreshing the page
- **Caching**: Once loaded, products remain cached until you refresh the page

### Visual Feedback
- **Loading State**: Shows a spinner while products are loading
- **Smooth Animations**: Categories expand/collapse with smooth sliding animation
- **Button States**: Save button shows clear loading and success states
- **Styled Tables**: Professional WordPress admin table styling

### User Experience
- **Quick Access**: Product names link directly to edit page
- **Empty Categories**: Shows helpful message if category has no products
- **Error Handling**: Clear error messages if something goes wrong
- **Separate Saves**: Category settings and product settings save independently

## Important Notes

### Category-Level Controls
- Category-level checkboxes remain the **master control**
- If a category doesn't have "Home Delivery" checked, products in that category won't show the home delivery option at checkout, even if enabled at product level
- Category settings should be configured first, then fine-tune individual products as needed

### Backward Compatibility
- All existing product settings are preserved
- No database structure changes
- Works with existing WooCommerce product meta data
- Compatible with the product-level "Delivery Options" tab

## Technical Details

### AJAX Endpoints
- `gsd_get_category_products`: Loads products for a category
- `gsd_save_product_shipping`: Saves product shipping settings

### Security
- Nonce verification for all AJAX requests
- Capability checks (requires `manage_woocommerce` permission)
- Product ID validation (ensures product exists and is correct type)
- Input sanitization (only allows 'yes' or 'no' values)

### Meta Fields
The following product meta fields are managed:
- `_gsd_home_delivery_available`: 'yes' or 'no'
- `_gsd_express_delivery_available`: 'yes' or 'no'
- `_gsd_contact_for_delivery`: 'yes' or 'no'

## Example Use Case

**Scenario**: You have a "Garden Sheds" category where most products offer home delivery, but a few oversized items can only be picked up from a depot.

**Solution**:
1. Enable "Home Delivery" for the "Garden Sheds" category (category-level checkbox)
2. Click the arrow to expand "Garden Sheds" 
3. Find the oversized products in the list
4. Uncheck "Home Delivery" for those specific products
5. Click "Save Product Settings"

Now most products in the category will show home delivery, but the oversized items won't.

## Support

For issues or questions, refer to the plugin documentation or contact support.

---

**Version**: 1.5.2  
**Last Updated**: 2026-02-03
