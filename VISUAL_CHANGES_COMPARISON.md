# Visual Changes: Before vs After

## Category Checkbox States - Visual Comparison

### BEFORE (with unwanted indicators)
```
┌─────────────────────────────────────────────────────────────┐
│ Category Settings Table                                      │
├─────────────────────────────────────────────────────────────┤
│ ⚠ [–] Garden Sheds        │ Home Delivery │ Express │ ... │  ← Yellow background
│                            │   [–]         │  [✓]    │ ... │     Warning triangle
│                            │ (hyphen)      │ (check) │ ... │     appears here
└─────────────────────────────────────────────────────────────┘
```

**Issues:**
- ⚠ Warning triangle suggests an error (it's not)
- Yellow background (#fff8e5) highlights the entire row
- Looks alarming when it's just indicating a mixed state

### AFTER (clean, simple)
```
┌─────────────────────────────────────────────────────────────┐
│ Category Settings Table                                      │
├─────────────────────────────────────────────────────────────┤
│   [–] Garden Sheds        │ Home Delivery │ Express │ ... │  ← Clean, no highlighting
│                            │   [–]         │  [✓]    │ ... │     No warning symbol
│                            │ (hyphen)      │ (check) │ ... │
└─────────────────────────────────────────────────────────────┘
```

**Benefits:**
- Clean, professional appearance
- Browser's native hyphen indicates mixed state clearly
- No confusing warning symbols
- Consistent with standard checkbox behavior

## Checkbox State Reference

| All Products Have It | Some Have It | None Have It |
|---------------------|--------------|--------------|
| [✓] Checked        | [–] Hyphen   | [ ] Empty   |
| checked=true       | indeterminate=true | checked=false |
| No indicators      | No indicators      | No indicators |

## CSS Changes Summary

### Removed:
```css
/* Yellow background on mixed state rows */
.gsd-category-row.has-indeterminate {
    background-color: #fff8e5 !important;
}

/* Warning triangle symbol */
.gsd-category-row.has-indeterminate td:first-child::after {
    content: '⚠';
    display: inline-block;
    margin-left: 5px;
    color: #ffb900;
    font-size: 16px;
    vertical-align: middle;
    cursor: help;
}
```

### Kept:
```css
/* Browser native indeterminate styling (hyphen display) */
.gsd-category-row input[type="checkbox"]:indeterminate {
    opacity: 1;
}
```

## User Experience Impact

### Before:
1. User expands category
2. User unchecks some products
3. **Entire row turns yellow** ← Alarming
4. **Warning triangle appears** ← Suggests error
5. Checkbox shows hyphen (correct, but overshadowed by warnings)

### After:
1. User expands category
2. User unchecks some products
3. Checkbox shows hyphen ← Clear indication
4. Row stays normal (no yellow) ← Professional
5. No warning symbols ← Not confusing

## Technical Notes

- The `indeterminate` property is a standard HTML checkbox feature
- All modern browsers display it as a hyphen/dash
- JavaScript logic unchanged - already working correctly
- Only visual presentation was modified
