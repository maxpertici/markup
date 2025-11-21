---
layout: default
title: MarkupQueryBuilder
nav_order: 8
---

# MarkupQueryBuilder Class
{: .no_toc }

Fluent query builder for searching Markup elements with chainable methods.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupQueryBuilder` class provides a fluent, chainable interface for building Markup search queries. It allows you to combine multiple search criteria and returns results as a `MarkupCollection` for easy manipulation.

### Key Features

- **Fluent interface** - Chain multiple criteria together
- **Multiple search methods** - Class, tag, attribute, slug, CSS selectors
- **Custom callbacks** - Add complex logic with `where()` 
- **OR logic support** - Use `orWhere()` for alternative criteria
- **Depth control** - Search deeply or only immediate children
- **Collection results** - Returns `MarkupCollection` for powerful transformations
- **Performance optimized** - Stops early with `first()`, optimizes single criteria

### When to Use MarkupQueryBuilder

**Use `MarkupQueryBuilder` (via `find()`) when:**
- You need a fluent, readable query interface
- You want to combine multiple search criteria
- You need to transform results with collections
- You want IDE autocompletion and type hints

**Use `MarkupFinder` directly when:**
- You need a single criterion search (`findByClass()`, `findByTag()`, etc.)
- You prefer explicit method names
- You're already familiar with the Finder API

---

## Access

The `MarkupQueryBuilder` is accessed through the `find()` method on any `Markup` instance:

```php
use MaxPertici\Markup\Markup;

$markup = Markup::div('content');
$query = $markup->find(); // Returns MarkupQueryBuilder
```

You can also instantiate it directly:

```php
use MaxPertici\Markup\MarkupQueryBuilder;

$query = new MarkupQueryBuilder($markup);
```

---

## Building Queries

### class()

Adds a CSS class criterion to the query.

```php
class(string $class): self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$class` | `string` | The CSS class to match |

#### Examples

```php
// Find all elements with 'active' class
$results = $markup->find()
    ->class('active')
    ->get();

// Chain multiple classes (AND logic)
$results = $markup->find()
    ->class('card')
    ->class('featured')
    ->get();
```

---

### tag()

Adds an HTML tag criterion to the query.

```php
tag(string $tag): self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag` | `string` | The HTML tag name to match (e.g., 'div', 'span') |

#### Examples

```php
// Find all div elements
$divs = $markup->find()
    ->tag('div')
    ->get();

// Combine with other criteria
$activeDivs = $markup->find()
    ->tag('div')
    ->class('active')
    ->get();
```

---

### attribute() / hasAttribute()

Adds an HTML attribute criterion to the query.

```php
attribute(string $name, ?string $value = null): self
hasAttribute(string $name, ?string $value = null): self // Alias
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | `string` | The attribute name |
| `$value` | `string\|null` | Optional. The attribute value. If null, checks only existence |

#### Examples

```php
// Find elements with any 'data-id' attribute
$results = $markup->find()
    ->attribute('data-id')
    ->get();

// Find elements with specific attribute value
$main = $markup->find()
    ->attribute('role', 'main')
    ->first();

// Using the alias
$required = $markup->find()
    ->hasAttribute('required')
    ->get();
```

---

### slug()

Adds a slug criterion to the query.

```php
slug(string $slug): self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$slug` | `string` | The slug identifier to match |

#### Examples

```php
// Find element with specific slug
$header = $markup->find()
    ->slug('main-header')
    ->first();

// Combine with other criteria
$activeHeader = $markup->find()
    ->slug('main-header')
    ->class('active')
    ->first();
```

---

### css()

Adds a CSS selector criterion to the query.

```php
css(string $selector): self
```

Supports the same CSS selectors as `MarkupFinder::css()`: basic selectors, combinations, descendant/child combinators, and `:has()` pseudo-class.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$selector` | `string` | The CSS selector string |

#### Examples

```php
// CSS selector alone
$links = $markup->find()
    ->css('nav a')
    ->get();

// Combine with other criteria
$results = $markup->find()
    ->css('.card')
    ->hasAttribute('data-featured')
    ->get();
```

**Note:** When using only a CSS selector, it's optimized to use `MarkupFinder::css()` directly.

---

### where()

Adds a custom callback criterion to the query.

```php
where(callable $callback): self
```

The callback receives a `Markup` instance and should return `true` if it matches.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `callable` | Function that receives `Markup $markup` and returns `bool` |

#### Examples

```php
// Custom logic
$expensive = $markup->find()
    ->where(function($m) {
        $price = $m->getAttribute('data-price');
        return $price && (float)$price > 100;
    })
    ->get();

// Complex conditions
$results = $markup->find()
    ->class('product')
    ->where(fn($m) => $m->hasAttribute('data-stock'))
    ->where(fn($m) => (int)$m->getAttribute('data-stock') > 0)
    ->get();
```

---

### orWhere()

Adds an OR callback criterion to the query.

```php
orWhere(callable $callback): self
```

All regular criteria are combined with AND logic. Use `orWhere()` to add alternative criteria.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `callable` | Function that receives `Markup $markup` and returns `bool` |

#### Examples

```php
// Match elements with EITHER 'error' OR 'warning' class
$alerts = $markup->find()
    ->where(fn($m) => $m->hasClass('error'))
    ->orWhere(fn($m) => $m->hasClass('warning'))
    ->get();

// Complex OR logic
$results = $markup->find()
    ->class('product')
    ->where(fn($m) => $m->hasClass('featured'))
    ->orWhere(fn($m) => (float)$m->getAttribute('data-discount') > 50)
    ->get();
```

---

## Search Depth Control

### deep()

Sets whether to search recursively (deeply) or not.

```php
deep(bool $deep = true): self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$deep` | `bool` | Whether to search deeply. Default: true |

#### Examples

```php
// Deep search (default)
$all = $markup->find()
    ->class('card')
    ->deep(true)
    ->get();

// Shallow search
$direct = $markup->find()
    ->class('card')
    ->deep(false)
    ->get();
```

---

### shallow()

Sets to search only immediate children (not recursive). Alias for `deep(false)`.

```php
shallow(): self
```

#### Examples

```php
// Only direct children
$directCards = $markup->find()
    ->class('card')
    ->shallow()
    ->get();
```

---

## Executing Queries

### get()

Executes the query and returns a `MarkupCollection` of results.

```php
get(): MarkupCollection
```

#### Return

- `MarkupCollection` - Collection of matching `Markup` instances

#### Examples

```php
// Basic execution
$results = $markup->find()
    ->class('card')
    ->get();

// Transform with collection methods
$titles = $markup->find()
    ->class('card')
    ->get()
    ->map(fn($card) => $card->find()->css('.title')->first())
    ->filter(fn($title) => $title !== null);
```

---

### all()

Executes the query and returns results as a plain array.

```php
all(): array
```

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Get array of results
$cards = $markup->find()
    ->class('card')
    ->all();

// Iterate
foreach ($cards as $card) {
    $card->addClass('enhanced');
}
```

---

### first()

Executes the query and returns the first result.

```php
first(): ?Markup
```

Optimized to stop searching after finding the first match.

#### Return

- `Markup|null` - The first matching instance or null

#### Examples

```php
// Get first match
$firstActive = $markup->find()
    ->class('active')
    ->first();

if ($firstActive) {
    echo $firstActive->getAttribute('id');
}

// With null coalescing
$header = $markup->find()
    ->slug('main-header')
    ->first() ?? Markup::div('Default header');
```

---

### exists()

Checks if any results exist.

```php
exists(): bool
```

#### Return

- `bool` - True if at least one match exists

#### Examples

```php
// Check existence
if ($markup->find()->class('error')->exists()) {
    echo 'Errors found!';
}

// Guard clause
if ($markup->find()->tag('img')->doesntExist()) {
    echo 'No images in this markup';
}
```

---

### doesntExist()

Checks if no results exist.

```php
doesntExist(): bool
```

#### Return

- `bool` - True if no matches exist

#### Examples

```php
// Check non-existence
if ($markup->find()->class('ads')->doesntExist()) {
    echo 'No ads on this page';
}
```

---

### count()

Counts the number of matching elements.

```php
count(): int
```

#### Return

- `int` - The count of matches

#### Examples

```php
// Count results
$cardCount = $markup->find()
    ->class('card')
    ->count();

echo "Found {$cardCount} cards";

// Conditional logic
if ($markup->find()->class('error')->count() > 5) {
    echo 'Too many errors!';
}
```

---

## Practical Examples

### Basic Queries

```php
use MaxPertici\Markup\Markup;

$page = Markup::div('', ['page']);
// ... build page structure ...

// Find by class
$buttons = $page->find()->class('btn')->get();

// Find by tag
$images = $page->find()->tag('img')->get();

// Find by attribute
$navs = $page->find()->attribute('role', 'navigation')->get();

// Find by slug
$header = $page->find()->slug('site-header')->first();
```

---

### Combined Criteria

```php
// Multiple classes (AND logic)
$primaryButtons = $page->find()
    ->class('btn')
    ->class('btn-primary')
    ->get();

// Tag + class + attribute
$externalLinks = $page->find()
    ->tag('a')
    ->class('external')
    ->hasAttribute('target', '_blank')
    ->get();

// Complex combination
$activeProducts = $page->find()
    ->class('product')
    ->class('active')
    ->hasAttribute('data-stock')
    ->where(fn($m) => (int)$m->getAttribute('data-stock') > 0)
    ->get();
```

---

### CSS Selectors

```php
// Simple selector
$navLinks = $page->find()
    ->css('nav a')
    ->get();

// Complex selector
$activeCards = $page->find()
    ->css('.container > .card.active')
    ->get();

// With :has()
$sectionsWithImages = $page->find()
    ->css('section:has(img)')
    ->get();
```

---

### Collection Transformations

```php
// Map and filter
$prices = $page->find()
    ->class('product')
    ->get()
    ->map(fn($p) => (float)$p->getAttribute('data-price'))
    ->filter(fn($price) => $price > 50);

// Sort products by price
$sortedProducts = $page->find()
    ->class('product')
    ->get()
    ->sortBy(fn($p) => (float)$p->getAttribute('data-price'));

// Group by category
$grouped = $page->find()
    ->class('product')
    ->get()
    ->groupBy('data-category');
```

---

### Conditional Queries

```php
function findProducts(Markup $page, ?string $category = null, ?bool $inStock = null): MarkupCollection
{
    $query = $page->find()->class('product');
    
    if ($category) {
        $query->hasAttribute('data-category', $category);
    }
    
    if ($inStock !== null) {
        $query->where(function($m) use ($inStock) {
            $stock = (int)$m->getAttribute('data-stock');
            return $inStock ? $stock > 0 : $stock === 0;
        });
    }
    
    return $query->get();
}

// Usage
$electronics = findProducts($page, 'electronics', true);
$all = findProducts($page);
```

---

### Shallow vs Deep Search

```php
// Deep search (default) - finds all nested divs
$allDivs = $page->find()
    ->tag('div')
    ->get();

// Shallow search - only direct div children
$directDivs = $page->find()
    ->tag('div')
    ->shallow()
    ->get();

// Useful for layouts
$sections = $page->find()
    ->tag('section')
    ->shallow() // Only top-level sections
    ->get();
```

---

### Modifying Results

```php
// Add classes to all cards
$page->find()
    ->class('card')
    ->get()
    ->each(fn($card) => $card->addClass('enhanced'));

// Set attributes on external links
$page->find()
    ->tag('a')
    ->hasAttribute('target', '_blank')
    ->get()
    ->each(fn($link) => $link->setAttribute('rel', 'noopener noreferrer'));

// Process first 5 items
$page->find()
    ->class('item')
    ->get()
    ->take(5)
    ->each(fn($item) => $item->addClass('top-5'));
```

---

### Validation Pipeline

```php
function validatePage(Markup $page): array
{
    $errors = [];
    
    // Check for images without alt
    $imagesWithoutAlt = $page->find()
        ->tag('img')
        ->where(fn($img) => !$img->hasAttribute('alt'))
        ->count();
    
    if ($imagesWithoutAlt > 0) {
        $errors[] = "{$imagesWithoutAlt} images missing alt attribute";
    }
    
    // Check for empty headings
    $emptyHeadings = $page->find()
        ->where(function($m) {
            $wrapper = $m->getWrapper();
            return preg_match('/^<h[1-6]/', $wrapper) && $m->isEmpty();
        })
        ->count();
    
    if ($emptyHeadings > 0) {
        $errors[] = "{$emptyHeadings} empty headings found";
    }
    
    return $errors;
}
```

---

## Performance Considerations

### Single Criterion Optimization

```php
// ✅ Optimized - single CSS criterion uses MarkupFinder::css() directly
$results = $markup->find()->css('.card')->get();

// ✅ Also optimized - MarkupFinder methods are faster for single criteria
$finder = new \MaxPertici\Markup\MarkupFinder($markup);
$results = $finder->findByClass('card');
```

### Use first() When Possible

```php
// ✅ Fast - stops at first match
$active = $markup->find()->class('active')->first();

// ❌ Slower - searches everything
$active = $markup->find()->class('active')->get()->first();
```

### Shallow Search for Performance

```php
// ✅ Fast - only searches direct children
$sections = $markup->find()->tag('section')->shallow()->get();

// ❌ Slower - searches entire tree
$sections = $markup->find()->tag('section')->get();
```

### Reuse Collections

```php
// ✅ Good - query once, use multiple times
$cards = $markup->find()->class('card')->get();
$featuredCards = $cards->filter(fn($c) => $c->hasClass('featured'));
$regularCards = $cards->reject(fn($c) => $c->hasClass('featured'));

// ❌ Bad - queries twice
$featuredCards = $markup->find()->class('card')->where(fn($c) => $c->hasClass('featured'))->get();
$regularCards = $markup->find()->class('card')->where(fn($c) => !$c->hasClass('featured'))->get();
```

---

## Best Practices

### 1. Chain Criteria for Readability

```php
// ✅ Good - fluent and readable
$results = $markup->find()
    ->class('product')
    ->hasAttribute('data-stock')
    ->where(fn($m) => (int)$m->getAttribute('data-stock') > 0)
    ->get();

// ❌ Less readable
$results = $markup->find()->class('product')->hasAttribute('data-stock')->where(fn($m) => (int)$m->getAttribute('data-stock') > 0)->get();
```

### 2. Use exists() for Checks

```php
// ✅ Good - semantic and clear
if ($markup->find()->class('error')->exists()) {
    // Handle errors
}

// ❌ Less clear
if ($markup->find()->class('error')->count() > 0) {
    // Handle errors
}
```

### 3. Leverage Collections

```php
// ✅ Good - use collection power
$highPrices = $markup->find()
    ->class('product')
    ->get()
    ->map(fn($p) => (float)$p->getAttribute('data-price'))
    ->filter(fn($price) => $price > 100)
    ->values();

// ❌ Manual array operations
$products = $markup->find()->class('product')->all();
$highPrices = [];
foreach ($products as $p) {
    $price = (float)$p->getAttribute('data-price');
    if ($price > 100) {
        $highPrices[] = $price;
    }
}
```

---

## See Also

- [Markup Class](markup.html) - Main markup building class
- [MarkupCollection](markup-collection.html) - Collection methods for results
- [MarkupFinder](markup-finder.html) - Direct finder methods
- [Getting Started](getting-started.html) - Introduction and first steps

