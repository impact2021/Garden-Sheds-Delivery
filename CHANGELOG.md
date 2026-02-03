# Changelog

All notable changes to the Garden Sheds Delivery plugin will be documented in this file.

## [2.0.1] - 2026-02-03

### Fixed
- **Product Checkbox Changes Not Updating Category State**: Fixed bug where changing individual product delivery checkboxes did not update the parent category checkbox to show indeterminate state
  - Category checkboxes now immediately update when product checkboxes are clicked
  - Product settings are now properly saved and persist after page reload
  - Indeterminate state correctly displays when some (but not all) products have a delivery option enabled

### Technical
- Added event listener for product checkbox changes in `includes/class-gsd-admin.php`
- Added null check for DOM element ID to prevent potential errors
- Updated version to 2.0.1 in `garden-sheds-delivery.php`

## [1.5.4] - 2026-02-03

### Added
- **Category-to-Product Delivery Inheritance**: Products now automatically inherit delivery options from their category
  - When a category has home delivery enabled, all products in that category default to home delivery checked
  - Same inheritance applies to Small Items and Contact for Delivery options
  - Individual products can still be unchecked to disable specific delivery methods
- **Indeterminate Checkbox State**: Category checkboxes show indeterminate state (solid square) when products have mixed settings
  - Checked: All products in category have the delivery option enabled
  - Unchecked: No products in category have the delivery option enabled
  - Indeterminate: Some but not all products have the delivery option enabled
- **Category Checkbox Control**: Clicking a category checkbox now updates all visible product checkboxes
  - Checking a category checkbox checks all product checkboxes of that type
  - Unchecking a category checkbox unchecks all product checkboxes of that type
  - Works in real-time when products are expanded and visible

### Changed
- Product delivery checkboxes now inherit default state from category settings on load
- Category checkbox states dynamically update based on product checkbox states after loading or saving

### Technical
- Modified: `includes/class-gsd-admin.php` - Enhanced `ajax_get_category_products()` to check category settings and inherit defaults
- Modified: `includes/class-gsd-admin.php` - Added JavaScript functions `updateCategoryCheckboxStates()` and `updateCheckboxState()` for indeterminate state
- Modified: `includes/class-gsd-admin.php` - Added category checkbox change handler to propagate changes to products
- Modified: `garden-sheds-delivery.php` - Updated version to 1.5.4

## [1.5.2] - 2026-02-03

### Added
- **Expandable Product Table in Admin**: Category table on settings page now expandable to show and manage individual products
  - Click arrow icon next to category name to expand and see all products in that category
  - Configure shipping options (Home Delivery, Small Items, Contact for Delivery) for individual products
  - Products load via AJAX for better performance
  - Separate "Save Product Settings" button for each category
  - Product names link to edit page for quick access
  - Visual loading states and smooth animations

### Changed
- Admin settings page table now includes expand/collapse functionality
- Product-level shipping settings can now be managed from main settings page
- Category-level settings still act as master control (category must be enabled for products to show option)

### Technical
- Added AJAX endpoints: `ajax_get_category_products()` and `ajax_save_product_shipping()`
- Modified: `includes/class-gsd-admin.php` - Added expandable table with product management
- Modified: `garden-sheds-delivery.php` - Updated version to 1.5.2

## [1.5.1] - 2026-02-03

### Added
- **GST Breakdown Display**: Home delivery costs now show GST amount in brackets
  - Format: "$6,371.50 (includes $831.07 GST)"
  - Applies to order details page, emails, and admin order pages
  - GST is calculated at 10% (Australian standard rate)
- **Depot Management Enhancements**: Improved depot location management
  - Added "Remove" button for depot locations with trash icon
  - Depots are now fully editable (add/remove/rename)
  - Ensured all 27 Mainfreight depots are created by default
  - Default depots are automatically created/updated on plugin load if missing

### Changed
- Updated depot creation logic to ensure Mainfreight depots are always present
- Improved default data creation to merge with existing data rather than skipping

### Technical
- Modified: `includes/class-gsd-order.php` - Added `format_price_with_gst()` method for GST breakdown display
- Modified: `includes/class-gsd-admin.php` - Added remove depot functionality with confirmation dialog
- Modified: `garden-sheds-delivery.php` - Enhanced `gsd_create_default_data()` to check and update depot lists
- Modified: `garden-sheds-delivery.php` - Added `gsd_create_default_data()` call on plugin init
- Modified: `garden-sheds-delivery.php` - Updated version to 1.5.1

## [1.3.3] - 2026-02-02

### Fixed
- **Small Item Delivery Fee Validation**: Fixed issue where small item delivery fee could be added without properly validating product eligibility
  - Added explicit product eligibility validation before adding delivery fees to checkout
  - Both home delivery and small item delivery now validate that products in the cart actually support the delivery type before adding the fee
  - This prevents delivery fees from being applied when products don't meet category-based eligibility requirements
  - Ensures delivery options and fees are only offered when products qualify

### Technical
- Modified: `includes/class-gsd-checkout.php` - Added `package_has_home_delivery()` and `package_has_express_delivery()` validation methods
- Modified: `includes/class-gsd-checkout.php` - Updated `add_delivery_fee()` to validate package contents before adding fees
- Modified: `garden-sheds-delivery.php` - Updated version to 1.3.3

## [1.3.2] - 2026-02-02

### Fixed
- **Delivery Options Visibility**: Admin category settings now properly control which delivery options appear in checkout
  - When a delivery option (Home Delivery, Small Items, or Contact for Delivery) is unchecked for a category in admin settings, it will no longer appear for products in that category, even if enabled at the product level
  - Admin category checkboxes now act as the master control for delivery option visibility
  - Depot pickup options always appear for products with assigned couriers (as intended)
  - This ensures that deselecting a delivery option in settings immediately hides it from checkout

### Changed
- Modified delivery availability logic to respect admin category settings as the primary control
- Product-level delivery settings now only apply when the product's category has that delivery option enabled in admin settings

### Technical
- Modified: `includes/class-gsd-product-settings.php` - Updated `is_home_delivery_available()`, `is_express_delivery_available()`, and `is_contact_for_delivery()` methods to check category settings first
- Modified: `garden-sheds-delivery.php` - Updated version to 1.3.2

## [1.3.1] - 2026-02-02

### Changed
- **Delivery Cost Display**: Home delivery and small item delivery costs now appear as separate line items in the order summary table
  - Delivery fees are now displayed as separate fees rather than being bundled into the shipping rate
  - This provides clearer cost breakdown for customers during checkout
  - The shipping method label no longer includes the delivery price (shown separately as a fee)
  
### Technical
- Modified: `includes/class-gsd-shipping-method.php` - Set shipping rate cost to 0 for delivery options
- Modified: `includes/class-gsd-checkout.php` - Added `add_delivery_fee()` method to add fees via WooCommerce cart fee system
- Modified: `garden-sheds-delivery.php` - Updated version to 1.3.1

## [1.3.0] - 2026-02-02

### Fixed
- **Home Delivery Fee Not Adding to Total**: Fixed issue where home delivery cost wasn't being properly added to order total
  - Changed cost parameter from numeric to string format for better WooCommerce compatibility
  - Ensured consistent cost formatting using `number_format()` for decimal precision
  - Depot pickup cost also updated to string '0' for consistency
- **Checkout Not Updating When Shipping Method Selected**: Added JavaScript to explicitly trigger checkout updates
  - Added event listeners for shipping method radio button changes
  - Forces WooCommerce to recalculate totals when customer selects different shipping option
  - Ensures immediate visual feedback when switching between depot pickup and home delivery

### Changed
- **Version**: Bumped to 1.3.0
- **Cost Format**: Shipping rate costs now use string format with 2 decimal places (e.g., '150.00' instead of 150)
- **JavaScript**: Enhanced frontend.js with explicit checkout update triggers

### Technical
- Modified: `includes/class-gsd-shipping-method.php` - Updated cost formatting for depot and home delivery rates
- Modified: `assets/js/frontend.js` - Added checkout update event handlers
- Modified: `garden-sheds-delivery.php` - Updated version numbers

## [1.2.1] - 2026-02-02

### Fixed
- **Shipping Options Not Appearing for Category-Based Home Delivery**: Fixed issue where products in categories with home delivery enabled would show "no shipping options" at checkout if the product didn't have a courier explicitly assigned
  - Updated `cart_needs_shed_delivery()` in shipping method to check for home delivery availability through category settings
  - Updated `cart_needs_delivery_selection()` in checkout for consistency
  - Improved validation logic to properly handle products with home delivery but no courier assigned
  - Products with only home delivery (no courier) now correctly show the shipping method and require home delivery selection
  - Products with both courier and home delivery continue to work as before (depot OR home delivery)

### Technical
- Modified: `includes/class-gsd-shipping-method.php` - Added category-based home delivery check
- Modified: `includes/class-gsd-checkout.php` - Enhanced validation and delivery selection logic

## [1.2.0] - 2026-02-02

### Added
- **WooCommerce Shipping Method Integration**: Plugin now registers as a proper WooCommerce shipping method
  - Fixes "No shipping options were found" error
  - Integrates with WooCommerce shipping zones
  - Replaces standard shipping for configured products
- **Category-Based Home Delivery**: New global setting to enable home delivery for entire product categories
  - Select categories in Shed Delivery settings
  - All products in selected categories automatically offer home delivery
  - Individual products can still override settings
- **Global Default Home Delivery Cost**: Set default cost in settings
  - Applied to all products unless overridden
  - Shown in product settings as reference
  - Simplifies bulk pricing management
- **Standalone Admin Menu**: "Shed Delivery" now appears as its own top-level menu item
  - No longer under WooCommerce submenu
  - Easier to find and access
  - Custom store icon for visibility
- **Comprehensive Documentation**: New DOCS.md file with complete documentation
  - Installation guide
  - Configuration instructions
  - Troubleshooting section
  - Developer API reference
  - Hooks and filters documentation

### Changed
- Admin menu moved from WooCommerce submenu to standalone top-level menu
- Settings page title changed from "Garden Sheds Delivery Settings" to "Shed Delivery Settings"
- Product settings now show default cost in description
- Home delivery price field can be left empty to use global default
- Enhanced product settings with better help text
- Updated README.md with new features and quick start guide

### Technical
- New file: `includes/class-gsd-shipping-method.php` - WooCommerce shipping method
- New file: `DOCS.md` - Comprehensive documentation
- Modified: `class-gsd-admin.php` - Added category and cost settings
- Modified: `class-gsd-product-settings.php` - Category-based delivery logic
- Modified: `garden-sheds-delivery.php` - Shipping method registration

## [1.1.0] - 2026-02-02

### Added
- **Contact for Delivery Option**: New product setting to display "Home delivery may be an option - please contact us after completing your order" message
- Product-level checkbox: "Show 'Contact Us' for Home Delivery"
- Informational notice displayed on checkout and cart pages when this option is enabled
- Notice also shown in order confirmation, emails, and admin order details
- Styled notice box with blue accent for visibility
- Order meta storage for `_gsd_contact_for_delivery` flag

### Changed
- Updated product settings tab description for clarity between immediate home delivery and contact-required options
- Enhanced CSS with styling for contact delivery notice
- Order display now shows contact notice when applicable

## [1.0.0] - 2026-02-02

### Added
- Initial release of Garden Sheds Delivery plugin
- Multiple courier company support (Main Freight, PBT)
- Depot location management for each courier company
- Product-level courier assignment
- Optional home delivery feature with configurable pricing
- Depot selection on checkout page
- Home delivery checkbox with dynamic pricing
- Order meta storage for delivery preferences
- Delivery information display on:
  - Order confirmation page
  - Order confirmation emails (HTML and plain text)
  - Admin order details page
- Admin settings page for managing courier companies and depots
- Default courier companies created on activation:
  - Main Freight (Auckland, Wellington, Christchurch depots)
  - PBT (North Island Hub, South Island Hub)
- Frontend CSS styling for delivery options
- Frontend JavaScript for dynamic checkout updates
- Validation to ensure depot or home delivery is selected
- Automatic home delivery fee calculation
- WooCommerce integration hooks

### Features
- **Flexible Delivery Options**: Customers can choose between depot pickup or home delivery
- **Dynamic Depot Filtering**: Depot locations filtered based on product's assigned courier
- **Optional Home Delivery**: Per-product configuration for home delivery availability
- **Price Transparency**: Clear display of home delivery fees during checkout
- **Admin Management**: Easy-to-use interface for managing couriers and depots
- **Order Tracking**: Complete delivery information saved with each order
- **Email Integration**: Delivery details included in all order emails
- **Mobile Responsive**: Works on all device sizes

### Technical Details
- WordPress 5.0+ compatibility
- WooCommerce 3.0+ compatibility
- PHP 7.2+ compatibility
- Follows WordPress coding standards
- Secure data handling with sanitization and validation
- Singleton pattern for class instances
- Efficient option storage for courier data
- Conditional asset loading for performance

### Files
- `garden-sheds-delivery.php` - Main plugin file
- `includes/class-gsd-courier.php` - Courier management
- `includes/class-gsd-product-settings.php` - Product delivery settings
- `includes/class-gsd-checkout.php` - Checkout process
- `includes/class-gsd-order.php` - Order display
- `includes/class-gsd-admin.php` - Admin interface
- `assets/css/frontend.css` - Frontend styles
- `assets/js/frontend.js` - Frontend scripts
- `README.md` - Plugin documentation
- `TESTING.md` - Testing guide
- `EXAMPLES.md` - Usage examples
- `CHANGELOG.md` - Version history

## Future Enhancements (Potential)
- Multiple products with different couriers in same cart
- Depot availability checking
- Delivery scheduling
- Email notifications to depot
- Tracking integration
- Custom depot opening hours
- Distance-based delivery pricing
- Multi-currency support
- Translation support (i18n)
- REST API endpoints
