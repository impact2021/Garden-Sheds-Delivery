# Visual Changes - Depot Column Merge

## Before (Version 2.0.4)

### Admin Table Header
```
| Category | Home Delivery | Small Items | Might be able to offer home delivery | Main Freight | PBT |
```

### Category Row Example
```
| Cedar Finger Joint | ☐ | ☑ | ☑ | ☐ | ☑ |
```
**Issue**: When expanded, even though some products have Main Freight or PBT enabled, the category checkbox shows as empty (☐) with no indication of mixed states.

### Product Rows (when category expanded)
```
|     Astor Shed        | ☑ | ☑ | ☑ | (empty) | (empty) |
|     Bentley Shed      | ☑ | ☑ | ☑ | (empty) | (empty) |
|     Bristol Shed      | ☐ | ☑ | ☑ | (empty) | (empty) |
```
**Issue**: No checkboxes for Main Freight or PBT columns - just empty cells.

---

## After (Version 2.0.5)

### Admin Table Header
```
| Category | Home Delivery | Small Items | Might be able to offer home delivery | Depot |
```
**Change**: Single "Depot" column replaces "Main Freight" and "PBT"

### Category Row Example - All products have depot
```
| Cedar Finger Joint | ☐ | ☑ | ☑ | ☑ |
```

### Category Row Example - Some products have depot (indeterminate)
```
| Cedar Finger Joint | ☐ | ☑ | ☑ | ⊟ |
```
**Fix**: Category checkbox shows indeterminate state (dash/minus) when some products have depot

### Category Row Example - No products have depot
```
| Cedar Finger Joint | ☐ | ☑ | ☑ | ☐ |
```

### Product Rows (when category expanded)
```
|     Astor Shed        | ☑ | ☑ | ☑ | ☑ |
|     Bentley Shed      | ☑ | ☑ | ☑ | ☑ |
|     Bristol Shed      | ☐ | ☑ | ☑ | ☐ |
```
**Fix**: Depot column now has checkboxes that can be toggled for each product

---

## Checkbox States Explained

### Checked (☑)
- All products in the category have this option enabled
- Or individual product has this option enabled

### Unchecked (☐)
- No products in the category have this option enabled
- Or individual product has this option disabled

### Indeterminate (⊟)
- **NEW**: Some products in the category have this option, others don't
- Only shown at category level
- Indicates mixed state that requires attention

---

## User Workflow

### Setting depot for entire category:
1. Click category depot checkbox → All products get depot
2. Auto-saves immediately
3. Checkbox changes from ☐ to ☑

### Setting depot for individual products:
1. Click expand arrow next to category
2. Check/uncheck individual product depot checkboxes
3. Auto-saves immediately
4. Category checkbox updates to show state:
   - All checked → ☑
   - None checked → ☐
   - Some checked → ⊟ (indeterminate)

### Visual indicator of mixed states:
- **Before**: No way to know without expanding category
- **After**: Indeterminate checkbox (⊟) shows at a glance which categories need attention

---

## Technical Notes

- Depot checkbox represents assignment to either Main Freight OR PBT courier
- When depot is checked, system sets courier to 'main_freight' by default
- Users can still select specific courier (Main Freight vs PBT) in product's Delivery Options tab
- Indeterminate state is purely visual - clicking it will check all products
- Changes auto-save within 500ms (debounced to avoid excessive AJAX calls)
