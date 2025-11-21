---
layout: default
title: MarkupCollection
nav_order: 9
---

# MarkupCollection Class
{: .no_toc }

Powerful collection class for working with arrays of Markup elements.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupCollection` class provides a fluent, chainable interface for working with arrays of `Markup` instances. It's inspired by Laravel Collections and provides dozens of methods for transforming, filtering, and manipulating Markup elements.

### Key Features

- **Fluent interface** - Chain methods together
- **Laravel-inspired API** - Familiar methods for Laravel developers
- **Immutable transformations** - Most methods return new collections
- **Iterator support** - Use in foreach loops
- **Array access** - Access items with array syntax
- **Countable** - Use with count() function

### When to Use MarkupCollection

**Use `MarkupCollection` when:**
- You have multiple Markup elements to process
- You need to transform, filter, or sort elements
- You want to apply operations to groups of elements
- You're using `MarkupQueryBuilder` (returns collections automatically)

---

## Creation

### Constructor

```php
public function __construct(array $items = [])
```

Create a new collection from an array of items.

```php
use MaxPertici\Markup\MarkupCollection;
use MaxPertici\Markup\Markup;

$collection = new MarkupCollection([
    Markup::div('Item 1'),
    Markup::div('Item 2'),
    Markup::div('Item 3')
]);
```

---

### make()

Creates a new collection instance (static factory method).

```php
public static function make(array $items = []): self
```

```php
$collection = MarkupCollection::make([
    Markup::div('Item 1'),
    Markup::div('Item 2')
]);
```

---

## Transformation Methods

### map()

Applies a callback to each item and returns a new collection.

```php
map(callable $callback): self
```

```php
// Add a class to all elements
$enhanced = $collection->map(function($markup) {
    $markup->addClass('enhanced');
    return $markup;
});

// Extract data
$ids = $collection->map(fn($m) => $m->getAttribute('id'));
```

---

### filter()

Filters the collection using a callback.

```php
filter(callable $callback): self
```

```php
// Keep only active elements
$active = $collection->filter(fn($m) => $m->hasClass('active'));

// Filter by attribute value
$expensive = $collection->filter(function($m) {
    $price = (float)$m->getAttribute('data-price');
    return $price > 100;
});
```

---

### reject()

Filters by rejecting items that pass the callback (opposite of filter).

```php
reject(callable $callback): self
```

```php
// Remove hidden elements
$visible = $collection->reject(fn($m) => $m->hasClass('hidden'));

// Remove elements without images
$withImages = $collection->reject(function($m) {
    $images = $m->find()->tag('img')->count();
    return $images === 0;
});
```

---

### unique()

Returns unique items based on a callback or attribute.

```php
unique(callable|string|null $key = null): self
```

```php
// Unique by object identity (default)
$unique = $collection->unique();

// Unique by attribute value
$uniqueCategories = $collection->unique('data-category');

// Unique by callback
$unique = $collection->unique(fn($m) => $m->getAttribute('id'));
```

---

## Access Methods

### first()

Gets the first item that passes an optional callback.

```php
first(?callable $callback = null, $default = null): mixed
```

```php
// Get first item
$first = $collection->first();

// Get first matching item
$firstActive = $collection->first(fn($m) => $m->hasClass('active'));

// With default value
$first = $collection->first(null, Markup::div('Default'));
```

---

### last()

Gets the last item that passes an optional callback.

```php
last(?callable $callback = null, $default = null): mixed
```

```php
// Get last item
$last = $collection->last();

// Get last matching item
$lastActive = $collection->last(fn($m) => $m->hasClass('active'));
```

---

### nth()

Gets an item at a specific index (via array access).

```php
// Access by index
$second = $collection[1];
$third = $collection[2];
```

---

## Querying Methods

### where()

Gets all items where a method returns a truthy value.

```php
where(string $method, ...$args): self
```

```php
// Find elements with 'active' class
$active = $collection->where('hasClass', 'active');

// Find elements with specific attribute
$withRole = $collection->where('hasAttribute', 'role');

// With value check
$main = $collection->where('getAttribute', 'role')->first();
```

---

### pluck()

Extracts values by calling a method or accessing an attribute.

```php
pluck(string $key): array
```

```php
// Pluck IDs
$ids = $collection->pluck('id'); // Calls getAttribute('id')

// Pluck custom data
$prices = $collection->pluck('data-price');

// With methods
$slugs = $collection->pluck('slug'); // Calls slug() method
```

---

### groupBy()

Groups the collection by a callback or attribute.

```php
groupBy(callable|string $key): array
```

```php
// Group by category attribute
$grouped = $collection->groupBy('data-category');
// Result: ['electronics' => Collection, 'books' => Collection, ...]

// Group by callback
$grouped = $collection->groupBy(function($m) {
    $price = (float)$m->getAttribute('data-price');
    return $price > 100 ? 'expensive' : 'affordable';
});
```

---

### contains()

Checks if the collection contains an item.

```php
contains(callable|mixed $value): bool
```

```php
// Contains specific instance
$contains = $collection->contains($someMarkup);

// Contains by callback
$hasActive = $collection->contains(fn($m) => $m->hasClass('active'));
```

---

## Sorting Methods

### sortBy()

Sorts the collection using a callback.

```php
sortBy(callable $callback, int $options = SORT_REGULAR, bool $descending = false): self
```

```php
// Sort by price (ascending)
$sorted = $collection->sortBy(fn($m) => (float)$m->getAttribute('data-price'));

// Sort by price (descending)
$sorted = $collection->sortBy(
    fn($m) => (float)$m->getAttribute('data-price'),
    SORT_NUMERIC,
    true
);

// Sort by title
$sorted = $collection->sortBy(function($m) {
    $title = $m->find()->css('.title')->first();
    return $title ? $title->text() : '';
});
```

---

### reverse()

Reverses the order of items.

```php
reverse(): self
```

```php
$reversed = $collection->reverse();
```

---

## Slicing Methods

### take()

Takes the first n items.

```php
take(int $limit): self
```

```php
// Take first 5
$first5 = $collection->take(5);

// Take last 3 (negative)
$last3 = $collection->take(-3);
```

---

### skip()

Skips the first n items.

```php
skip(int $offset): self
```

```php
// Skip first 3 items
$remaining = $collection->skip(3);
```

---

### slice()

Slices the collection.

```php
slice(int $offset, ?int $length = null): self
```

```php
// Get items 5-10
$slice = $collection->slice(5, 5);

// Get items from position 10 onwards
$tail = $collection->slice(10);
```

---

### chunk()

Chunks the collection into smaller collections.

```php
chunk(int $size): array
```

```php
// Split into groups of 3
$chunks = $collection->chunk(3);

// Process each chunk
foreach ($chunks as $chunk) {
    // Each $chunk is a MarkupCollection
    $chunk->each(fn($m) => $m->addClass('processed'));
}
```

---

## Iteration Methods

### each()

Executes a callback on each item.

```php
each(callable $callback): self
```

Returns the collection for chaining. Callback receives `($item, $index)`.

```php
// Add class to all
$collection->each(fn($m) => $m->addClass('processed'));

// With index
$collection->each(function($m, $index) {
    $m->setAttribute('data-index', (string)$index);
});

// Break early
$collection->each(function($m, $index) {
    if ($index >= 5) {
        return false; // Stop iteration
    }
    $m->addClass('top-5');
});
```

---

### tap()

Passes the collection to a callback and returns the collection.

```php
tap(callable $callback): self
```

Useful for side effects while chaining.

```php
$results = $collection
    ->filter(fn($m) => $m->hasClass('active'))
    ->tap(fn($c) => error_log("Found {$c->count()} active items"))
    ->take(10);
```

---

### pipe()

Applies a callback to the collection and returns the result.

```php
pipe(callable $callback): mixed
```

Useful for transforming to non-collection values.

```php
// Calculate total price
$total = $collection
    ->map(fn($m) => (float)$m->getAttribute('data-price'))
    ->pipe(fn($prices) => array_sum($prices->all()));

// Render all as HTML
$html = $collection
    ->pipe(fn($c) => $c->map(fn($m) => $m->render())->all())
    ->pipe(fn($html) => implode("\n", $html));
```

---

## State Methods

### isEmpty()

Checks if the collection is empty.

```php
isEmpty(): bool
```

```php
if ($collection->isEmpty()) {
    echo 'No items found';
}
```

---

### isNotEmpty()

Checks if the collection is not empty.

```php
isNotEmpty(): bool
```

```php
if ($collection->isNotEmpty()) {
    // Process items
}
```

---

### count()

Counts the items in the collection.

```php
count(): int
```

```php
$count = $collection->count();
echo "Found {$count} items";

// Also works with PHP count()
echo count($collection);
```

---

### random()

Gets a random item from the collection.

```php
random(): mixed
```

```php
$random = $collection->random();

if ($random) {
    echo $random->render();
}
```

---

## Export Methods

### all() / toArray()

Gets all items as an array.

```php
all(): array
toArray(): array // Alias
```

```php
$array = $collection->all();

foreach ($array as $markup) {
    echo $markup->render();
}
```

---

### values()

Gets all values (re-indexed from 0).

```php
values(): array
```

```php
// After filtering, keys might not be sequential
$filtered = $collection->filter(fn($m) => $m->hasClass('active'));

// Re-index
$reindexed = $filtered->values();
```

---

## Debugging Methods

### dump()

Dumps the collection for debugging and returns it.

```php
dump(): self
```

```php
$results = $collection
    ->filter(fn($m) => $m->hasClass('active'))
    ->dump() // Shows filtered results
    ->take(5);
```

---

### dd()

Dumps the collection and dies (dump and die).

```php
dd(): void
```

```php
$collection
    ->filter(fn($m) => $m->hasClass('active'))
    ->dd(); // Shows results and stops execution
```

---

## Practical Examples

### Filter and Transform

```php
use MaxPertici\Markup\Markup;

$page = Markup::div('');
// ... build page ...

// Find all products, filter, and enhance
$premiumProducts = $page->find()
    ->class('product')
    ->get()
    ->filter(function($product) {
        $price = (float)$product->getAttribute('data-price');
        return $price > 100;
    })
    ->each(fn($p) => $p->addClass('premium'))
    ->sortBy(fn($p) => (float)$p->getAttribute('data-price'), SORT_NUMERIC, true);
```

---

### Group and Process

```php
// Group products by category
$grouped = $page->find()
    ->class('product')
    ->get()
    ->groupBy('data-category');

// Process each group
foreach ($grouped as $category => $products) {
    echo "Category: {$category}\n";
    echo "Count: {$products->count()}\n";
    
    // Get top 3 by price
    $top3 = $products
        ->sortBy(fn($p) => (float)$p->getAttribute('data-price'), SORT_NUMERIC, true)
        ->take(3);
}
```

---

### Statistical Analysis

```php
$products = $page->find()->class('product')->get();

// Calculate average price
$average = $products
    ->map(fn($p) => (float)$p->getAttribute('data-price'))
    ->pipe(fn($prices) => array_sum($prices->all()) / $prices->count());

// Find price range
$prices = $products->map(fn($p) => (float)$p->getAttribute('data-price'))->all();
$min = min($prices);
$max = max($prices);

echo "Price range: \${$min} - \${$max}";
echo "Average: \${$average}";
```

---

### Pagination

```php
function paginateProducts(MarkupCollection $products, int $page, int $perPage): MarkupCollection
{
    return $products
        ->skip(($page - 1) * $perPage)
        ->take($perPage);
}

// Usage
$allProducts = $page->find()->class('product')->get();
$page1 = paginateProducts($allProducts, 1, 10); // First 10
$page2 = paginateProducts($allProducts, 2, 10); // Next 10
```

---

### Building a Grid

```php
// Split products into rows of 3
$products = $page->find()->class('product')->get();
$rows = $products->chunk(3);

$grid = Markup::div('', ['product-grid']);

foreach ($rows as $row) {
    $rowMarkup = Markup::div('', ['row']);
    
    $row->each(function($product) use ($rowMarkup) {
        $col = Markup::div('', ['col'])
            ->children($product);
        $rowMarkup->children($col);
    });
    
    $grid->children($rowMarkup);
}

echo $grid->render();
```

---

### Data Extraction

```php
// Extract all image URLs from a page
$imageUrls = $page->find()
    ->tag('img')
    ->get()
    ->pluck('src')
    ->filter(fn($url) => !empty($url));

// Extract all link URLs
$links = $page->find()
    ->tag('a')
    ->get()
    ->map(fn($link) => [
        'url' => $link->getAttribute('href'),
        'text' => $link->text()
    ])
    ->filter(fn($link) => !empty($link['url']));
```

---

### Unique Elements

```php
// Find unique elements by ID
$uniqueById = $collection->unique('id');

// Find unique by slug
$uniqueBySl

ug = $collection->unique(fn($m) => $m->slug());

// Remove duplicate products
$uniqueProducts = $products->unique(function($product) {
    return $product->getAttribute('data-sku');
});
```

---

## Performance Tips

### Chain Efficiently

```php
// ✅ Good - single iteration
$results = $collection
    ->filter(fn($m) => $m->hasClass('product'))
    ->map(fn($m) => $m->addClass('enhanced'))
    ->take(10);

// ❌ Bad - multiple iterations
$filtered = $collection->filter(fn($m) => $m->hasClass('product'));
$mapped = $filtered->map(fn($m) => $m->addClass('enhanced'));
$results = $mapped->take(10);
```

### Use Lazy Evaluation

```php
// ✅ Good - stops early
$first = $collection->first(fn($m) => $m->hasClass('error'));

// ❌ Bad - processes everything
$first = $collection->filter(fn($m) => $m->hasClass('error'))->first();
```

---

## Best Practices

### 1. Use Method Chaining

```php
// ✅ Fluent and readable
$result = $collection
    ->filter(fn($m) => $m->hasClass('active'))
    ->sortBy(fn($m) => $m->getAttribute('priority'))
    ->take(5);
```

### 2. Leverage tap() for Debugging

```php
$results = $collection
    ->filter(fn($m) => $m->hasClass('product'))
    ->tap(fn($c) => error_log("After filter: {$c->count()}"))
    ->sortBy(fn($m) => (float)$m->getAttribute('data-price'))
    ->tap(fn($c) => error_log("After sort: {$c->count()}"))
    ->take(10);
```

### 3. Use pipe() for Aggregations

```php
// ✅ Clear intent
$total = $collection->pipe(function($c) {
    return array_sum($c->map(fn($m) => (float)$m->getAttribute('data-price'))->all());
});
```

---

## See Also

- [MarkupQueryBuilder](markup-query-builder.html) - Query builder that returns collections
- [Markup Class](markup.html) - Main markup building class
- [MarkupFinder](markup-finder.html) - Direct search methods
- [Getting Started](getting-started.html) - Introduction and first steps

