# Changelog

All notable changes to the Garden Sheds Delivery plugin will be documented in this file.

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
