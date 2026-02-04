# Indeterminate State on Page Load - Implementation Summary

## Issue Description
When the admin settings page loads, category checkboxes would show either checked or unchecked based on saved settings. However, if SOME (but not all) products in a category have a delivery option enabled, the checkbox should display an **indeterminate state** (shown as a hyphen/dash) instead of being fully checked or unchecked.

### Before the Fix
- Category checkbox state was determined solely by the `gsd_home_delivery_categories` option
- Indeterminate state was only set AFTER user expanded a category and the products loaded via AJAX
- On initial page load, no visual indication that the category had mixed product settings

### After the Fix
- Category checkbox state is calculated based on actual product settings
- Indeterminate state is set immediately on page load
- User sees the correct state even before expanding categories

## Implementation Details

### 1. Server-Side Logic (PHP)
Added code in `class-gsd-admin.php` after line 138 to calculate indeterminate states:

```php
// Calculate indeterminate states for each category
$indeterminate_states = array();

// Initialize all categories with false states
foreach ($categories as $category) {
    $indeterminate_states[$category->term_id] = array(
        'home_delivery' => false,
        'express_delivery' => false,
        'contact_delivery' => false,
    );
}

// Get all products with their categories in a single query
$all_products = get_posts(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'fields' => 'ids',
));

// Prime meta cache for all products to avoid N+1 queries
if (!empty($all_products)) {
    update_meta_cache('post', $all_products);
}

// Group products by category
$products_by_category = array();
foreach ($all_products as $product_id) {
    $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    foreach ($product_categories as $cat_id) {
        if (!isset($products_by_category[$cat_id])) {
            $products_by_category[$cat_id] = array();
        }
        $products_by_category[$cat_id][] = $product_id;
    }
}

// Calculate indeterminate states
foreach ($categories as $category) {
    if (!isset($products_by_category[$category->term_id])) {
        continue;
    }
    
    $category_products = $products_by_category[$category->term_id];
    
    // Check each delivery option
    foreach (array('home_delivery', 'express_delivery', 'contact_delivery') as $option) {
        $checked_count = 0;
        
        foreach ($category_products as $product_id) {
            $value = get_post_meta($product_id, 'gsd_' . $option, true);
            if ($value === '1' || $value === 1) {
                $checked_count++;
            }
        }
        
        // If some (but not all) products have this option, it's indeterminate
        if ($checked_count > 0 && $checked_count < count($category_products)) {
            $indeterminate_states[$category->term_id][$option] = true;
        }
    }
}
```

### 2. Client-Side Logic (JavaScript)
Added initialization code in the `jQuery(document).ready` section:

```javascript
// Set indeterminate states on page load
var indeterminateStates = <?php echo json_encode($indeterminate_states); ?>;

$.each(indeterminateStates, function(categoryId, states) {
    var categoryRow = $('.gsd-category-row[data-category-id="' + categoryId + '"]');
    
    if (states.home_delivery) {
        var homeCheckbox = categoryRow.find('input[name="gsd_home_delivery_categories[]"]')[0];
        if (homeCheckbox) {
            homeCheckbox.indeterminate = true;
        }
    }
    
    if (states.express_delivery) {
        var expressCheckbox = categoryRow.find('input[name="gsd_express_delivery_categories[]"]')[0];
        if (expressCheckbox) {
            expressCheckbox.indeterminate = true;
        }
    }
    
    if (states.contact_delivery) {
        var contactCheckbox = categoryRow.find('input[name="gsd_contact_delivery_categories[]"]')[0];
        if (contactCheckbox) {
            contactCheckbox.indeterminate = true;
        }
    }
    
    // Add visual indicator if any checkbox is indeterminate
    if (states.home_delivery || states.express_delivery || states.contact_delivery) {
        categoryRow.addClass('has-indeterminate');
    }
});
```

## Performance Optimizations

### Problem: N+1 Query Issues
The initial implementation could have caused performance issues by querying products for each category separately.

### Solutions Implemented:
1. **Single Product Query**: Fetch all products in one query instead of per-category
2. **Meta Cache Priming**: Use `update_meta_cache()` to load all meta values at once
3. **In-Memory Grouping**: Group products by category in PHP instead of multiple database queries
4. **Strict Comparison**: Use `===` for type safety and avoid unexpected behavior

## Example Scenario

### Scenario: "Cedar Finger Joint" Category
- Category has 10 products
- 7 products have "Home Delivery" enabled
- 3 products do NOT have "Home Delivery" enabled

**Before the fix:**
- Page loads with checkbox unchecked (or checked, depending on category setting)
- User has no idea that some products have different settings
- User must expand category to see the mixed state

**After the fix:**
- Page loads with checkbox showing hyphen/dash (indeterminate state)
- User immediately knows that products have mixed settings
- Visual indicator shows this is a "mixed state" category

## Compatibility

### Existing Functionality Preserved
- Dynamic updates when user expands categories still work
- Auto-save functionality unchanged
- Category checkbox click behavior unchanged
- Product checkbox changes still update category state correctly

### Browser Support
The `indeterminate` property is supported by all modern browsers:
- Chrome/Edge
- Firefox
- Safari
- Opera

## Files Modified
- `includes/class-gsd-admin.php`: Added indeterminate state calculation and initialization

## Security
- No new security vulnerabilities introduced
- CodeQL analysis passed
- Input sanitization maintained
- No direct SQL queries added (using WordPress APIs)
