# Visual Guide: Expandable Product Table

## Before (Collapsed State)

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│  Category                         │ Home Del │ Small │ Contact │ Main Fr │ PBT  │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ► Garden Sheds                    │    ☑     │   ☐   │   ☐     │   ☑     │  ☐   │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ► Outdoor Furniture               │    ☐     │   ☑   │   ☐     │   ☐     │  ☑   │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ► Tools & Equipment               │    ☑     │   ☑   │   ☑     │   ☑     │  ☐   │
└──────────────────────────────────────────────────────────────────────────────────┘
```

## After (Expanded State)

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│  Category                         │ Home Del │ Small │ Contact │ Main Fr │ PBT  │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ▼ Garden Sheds                    │    ☑     │   ☐   │   ☐     │   ☑     │  ☐   │
├──────────────────────────────────────────────────────────────────────────────────┤
│                      PRODUCTS IN THIS CATEGORY                                   │
│  ┌───────────────────────────────────────────────────────────────────────────┐  │
│  │ Product Name                        │ Home Del │ Small │ Contact           │  │
│  ├───────────────────────────────────────────────────────────────────────────┤  │
│  │ Premium Garden Shed 10x8            │    ☑     │   ☐   │   ☐               │  │
│  │ Deluxe Garden Shed 12x10            │    ☑     │   ☐   │   ☐               │  │
│  │ XL Workshop Shed 16x12 (oversized)  │    ☐     │   ☐   │   ☑               │  │
│  │ Standard Storage Shed 8x6           │    ☑     │   ☐   │   ☐               │  │
│  └───────────────────────────────────────────────────────────────────────────┘  │
│                            [ Save Product Settings ]                             │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ► Outdoor Furniture               │    ☐     │   ☑   │   ☐     │   ☐     │  ☑   │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ► Tools & Equipment               │    ☑     │   ☑   │   ☑     │   ☑     │  ☐   │
└──────────────────────────────────────────────────────────────────────────────────┘
```

## Key Interface Elements

### 1. Expand/Collapse Button
- **►** = Collapsed (click to expand)
- **▼** = Expanded (click to collapse)
- Smooth rotation animation on toggle

### 2. Product Table (When Expanded)
- Light background color (#f9f9f9) to distinguish from main table
- Product names are clickable links to edit page
- Checkboxes for each shipping option
- "Save Product Settings" button at bottom

### 3. Loading State
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ ▼ Garden Sheds                    │    ☑     │   ☐   │   ☐     │   ☑     │  ☐   │
├──────────────────────────────────────────────────────────────────────────────────┤
│                                                                                   │
│                            ⟳  Loading products...                                │
│                                                                                   │
└──────────────────────────────────────────────────────────────────────────────────┘
```

### 4. Empty Category State
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ ▼ Empty Category                  │    ☑     │   ☐   │   ☐     │   ☑     │  ☐   │
├──────────────────────────────────────────────────────────────────────────────────┤
│                                                                                   │
│                    No products found in this category.                           │
│                                                                                   │
└──────────────────────────────────────────────────────────────────────────────────┘
```

## Interaction Flow

1. **Initial State**: All categories collapsed with arrow pointing right (►)

2. **User Clicks Arrow**: 
   - Arrow rotates to point down (▼)
   - Product row slides down smoothly
   - Shows loading spinner
   - AJAX request fetches products

3. **Products Load**:
   - Loading spinner disappears
   - Product table appears with checkboxes
   - Product names become clickable links

4. **User Makes Changes**:
   - Toggle checkboxes as needed
   - Click "Save Product Settings" button

5. **Saving**:
   - Button text changes to "Saving..."
   - Button becomes disabled
   - AJAX request saves changes

6. **Save Complete**:
   - Button text changes to "Saved!"
   - After 2 seconds, returns to "Save Product Settings"
   - Button becomes enabled again

7. **Collapse** (Optional):
   - Click arrow again
   - Product row slides up smoothly
   - Arrow rotates to point right (►)

## Benefits

✓ **Efficiency**: Configure all products in a category without leaving the page
✓ **Clarity**: See category and product settings in one view
✓ **Performance**: Products load on-demand, not all at once
✓ **Flexibility**: Override category defaults for specific products
✓ **User-Friendly**: Familiar WordPress admin interface patterns
