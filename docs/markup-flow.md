---
layout: default
title: MarkupFlow
nav_order: 4
---

# MarkupFlow Class
{: .no_toc }

Flow multiple elements together to be rendered within a single wrapper.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupFlow` class allows you to flow multiple elements (strings, `Markup` instances, or callables) together to be rendered within a single children wrapper. This is particularly useful when you need to combine different types of content in a natural flow, such as mixing text with inline elements (like `<strong>` or `<a>` tags) within a paragraph.

### Key Features

- **Mixed content** - Combine strings, Markup instances, and callables
- **Single wrapper** - All items flow together within the same parent wrapper
- **Natural flow** - Perfect for mixing text and inline elements
- **Flexible composition** - Build complex nested structures
- **Chainable API** - Fluent method chaining for easy construction
- **Type-agnostic** - Works with any renderable content type

### When to Use MarkupFlow

**Use `MarkupFlow` when:**
- You need to combine text and Markup objects in the same list item or paragraph
- You have mixed content flowing together (text + inline elements)
- You want multiple elements to share a single wrapper (like a `<li>`)
- You're building complex nested structures with mixed content types
- You need fine-grained control over content flow

**Don't use `MarkupFlow` when:**
- You have simple string content (use arrays directly)
- Each item needs its own wrapper (use regular children)
- You're working with a single element type

---

## Constructor

```php
public function __construct(array $items = [])
```

Creates a new MarkupFlow with optional initial items.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$items` | `array` | Optional. Array of items to flow together (strings, Markup instances, or callables). Default: empty array |

### Examples

```php
use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupFlow;

// Empty flow
$flow = new MarkupFlow();

// Flow with initial items
$flow = new MarkupFlow([
    'Text content',
    new Markup('<strong>%children%</strong>', children: ['Bold text']),
    function() { echo 'From callback'; }
]);

// Flowing text and markup together
$link = new Markup('<a href="/about">%children%</a>', children: ['Learn more']);
$flow = new MarkupFlow([
    'Read our story and ',
    $link,
    ' about what we do.'
]);
```

---

## Managing Items

### items()

Retrieves all items in the flow.

```php
items(): array
```

#### Return

- `array` - Array of items in the flow

#### Examples

```php
$flow = new MarkupFlow([
    'Item 1',
    new Markup('<span>%children%</span>', children: ['Item 2'])
]);

$items = $flow->items();
// Returns: ['Item 1', Markup instance]

// Count items
$count = count($flow->items());

// Iterate over items
foreach ($flow->items() as $item) {
    // Process each item
}
```

---

### add()

Adds one or more items to the flow.

```php
add(mixed ...$items): self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `...$items` | `mixed` | Items to add to the flow (strings, Markup instances, or callables) |

#### Return

- `self` - Returns the instance for method chaining

#### Examples

```php
$flow = new MarkupFlow(['Initial item']);

// Add a single item
$flow->add('New item');

// Add multiple items
$flow->add(
    'Text',
    new Markup('<em>%children%</em>', children: ['Emphasized']),
    'More text'
);

// Chaining
$flow = new MarkupFlow()
    ->add('First')
    ->add('Second')
    ->add('Third');

// Add conditionally
$flow->add('Base content');
if ($showExtra) {
    $flow->add($extraContent);
}
```

---

### isEmpty()

Checks if the flow contains no items.

```php
isEmpty(): bool
```

#### Return

- `bool` - True if the flow has no items, false otherwise

#### Examples

```php
$empty = new MarkupFlow();
$empty->isEmpty(); // true

$filled = new MarkupFlow(['Content']);
$filled->isEmpty(); // false

// Conditional rendering
if (!$flow->isEmpty()) {
    // Render the flow
}

// Guard clause
if ($flow->isEmpty()) {
    return 'No content available';
}
```

---

### count()

Gets the number of items in the flow.

```php
count(): int
```

#### Return

- `int` - The number of items in the flow

#### Examples

```php
$flow = new MarkupFlow([
    'Item 1',
    'Item 2',
    'Item 3'
]);

$count = $flow->count(); // 3

// Display count
echo "Flow contains {$flow->count()} items";

// Conditional logic
if ($flow->count() > 5) {
    // Handle large flows
}

// Loop with count
for ($i = 0; $i < $flow->count(); $i++) {
    // Process items by index
}
```

---

## Usage with Markup

`MarkupFlow` is designed to work seamlessly with the `Markup` class's `children_wrapper` feature.

### Basic Integration

```php
use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupFlow;

// Create a list with flowing content
$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: [
        'Simple item',
        new MarkupFlow([
            'Complex item with ',
            new Markup('<strong>%children%</strong>', children: ['bold text']),
            ' flowing together.'
        ]),
        'Another simple item'
    ]
);

echo $list->render();
/*
<ul>
    <li>Simple item</li>
    <li>Complex item with <strong>bold text</strong> flowing together.</li>
    <li>Another simple item</li>
</ul>
*/
```

### Multiple Markup Instances Flowing Together

```php
$icon = new Markup('<i class="icon">%children%</i>', children: ['🏠']);
$text = new Markup('<span>%children%</span>', children: ['Home']);

$menuItem = new MarkupFlow([$icon, ' ', $text]);

$menu = new Markup(
    wrapper: '<ul class="menu">%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: [$menuItem]
);

echo $menu->render();
/*
<ul class="menu">
    <li><i class="icon">🏠</i> <span>Home</span></li>
</ul>
*/
```

---

## Practical Examples

### Navigation Menu with Icons

```php
function createMenuItem(string $icon, string $label, string $url): MarkupFlow
{
    $iconMarkup = new Markup(
        '<i class="menu-icon">%children%</i>',
        children: [$icon]
    );
    
    $labelMarkup = new Markup(
        '<span class="menu-label">%children%</span>',
        children: [$label]
    );
    
    $link = new Markup(
        '<a href="%url%" class="menu-link">%children%</a>',
        children: [new MarkupFlow([$iconMarkup, ' ', $labelMarkup])]
    );
    $link->setAttribute('href', $url);
    
    return new MarkupFlow([$link]);
}

// Build menu
$menu = new Markup(
    wrapper: '<nav><ul class="main-menu">%children%</ul></nav>',
    children_wrapper: '<li class="menu-item">%child%</li>',
    children: [
        createMenuItem('🏠', 'Home', '/'),
        createMenuItem('📝', 'Blog', '/blog'),
        createMenuItem('📧', 'Contact', '/contact')
    ]
);

echo $menu->render();
/*
<nav>
    <ul class="main-menu">
        <li class="menu-item"><a href="/" class="menu-link"><i class="menu-icon">🏠</i> <span class="menu-label">Home</span></a></li>
        <li class="menu-item"><a href="/blog" class="menu-link"><i class="menu-icon">📝</i> <span class="menu-label">Blog</span></a></li>
        <li class="menu-item"><a href="/contact" class="menu-link"><i class="menu-icon">📧</i> <span class="menu-label">Contact</span></a></li>
    </ul>
</nav>
*/
```

---

### Product List with Badges

```php
function createProduct(string $name, float $price, array $badges = []): MarkupFlow
{
    $flow = new MarkupFlow();
    
    // Product name
    $nameMarkup = new Markup(
        '<span class="product-name">%children%</span>',
        children: [$name]
    );
    $flow->add($nameMarkup);
    
    // Badges
    if (!empty($badges)) {
        foreach ($badges as $badge) {
            $badgeMarkup = new Markup(
                '<span class="badge">%children%</span>',
                children: [$badge]
            );
            $flow->add(' ', $badgeMarkup);
        }
    }
    
    // Price
    $priceMarkup = new Markup(
        '<span class="product-price">%children%</span>',
        children: ['$' . number_format($price, 2)]
    );
    $flow->add(' - ', $priceMarkup);
    
    return $flow;
}

// Build product list
$products = new Markup(
    wrapper: '<ul class="product-list">%children%</ul>',
    children_wrapper: '<li class="product-item">%child%</li>',
    children: [
        createProduct('Premium Widget', 99.99, ['New', 'Popular']),
        createProduct('Standard Widget', 49.99, ['Sale']),
        createProduct('Basic Widget', 29.99)
    ]
);

echo $products->render();
/*
<ul class="product-list">
    <li class="product-item">
        <span class="product-name">Premium Widget</span>
        <span class="badge">New</span>
        <span class="badge">Popular</span>
         - <span class="product-price">$99.99</span>
    </li>
    <li class="product-item">
        <span class="product-name">Standard Widget</span>
        <span class="badge">Sale</span>
         - <span class="product-price">$49.99</span>
    </li>
    <li class="product-item">
        <span class="product-name">Basic Widget</span>
         - <span class="product-price">$29.99</span>
    </li>
</ul>
*/
```

---

### Timeline with Mixed Content

```php
function createTimelineEvent(
    string $date,
    string $title,
    ?Markup $content = null
): MarkupFlow
{
    $flow = new MarkupFlow();
    
    // Date badge
    $dateMarkup = new Markup(
        '<span class="timeline-date">%children%</span>',
        children: [$date]
    );
    $flow->add($dateMarkup);
    
    // Title
    $titleMarkup = new Markup(
        '<h3 class="timeline-title">%children%</h3>',
        children: [$title]
    );
    $flow->add($titleMarkup);
    
    // Optional content
    if ($content) {
        $flow->add($content);
    }
    
    return $flow;
}

// Create timeline
$eventContent = new Markup(
    '<div class="timeline-content">%children%</div>',
    children: [
        new Markup('<p>%children%</p>', children: ['Event description here.']),
        new Markup('<a href="/details">%children%</a>', children: ['Learn more →'])
    ]
);

$timeline = new Markup(
    wrapper: '<div class="timeline">%children%</div>',
    children_wrapper: '<div class="timeline-item">%child%</div>',
    children: [
        createTimelineEvent('2024-01', 'Project Launch', $eventContent),
        createTimelineEvent('2024-02', 'Version 2.0 Released'),
        createTimelineEvent('2024-03', 'Major Update')
    ]
);

echo $timeline->render();
```

---

### Breadcrumb Navigation

```php
class Breadcrumb
{
    private Markup $markup;
    
    public function __construct()
    {
        $this->markup = new Markup(
            wrapper: '<nav aria-label="breadcrumb"><ol class="breadcrumb">%children%</ol></nav>',
            children_wrapper: '<li class="breadcrumb-item">%child%</li>'
        );
    }
    
    public function addItem(string $label, ?string $url = null): self
    {
        if ($url) {
            $link = new Markup(
                '<a href="%url%">%children%</a>',
                children: [$label]
            );
            $link->setAttribute('href', $url);
            $flow = new MarkupFlow([$link]);
        } else {
            $flow = new MarkupFlow([
                new Markup(
                    '<span class="active">%children%</span>',
                    children: [$label]
                )
            ]);
        }
        
        $this->markup->children($flow);
        return $this;
    }
    
    public function addSeparator(string $separator = '/'): self
    {
        $sep = new Markup(
            '<span class="breadcrumb-separator">%children%</span>',
            children: [' ' . $separator . ' ']
        );
        
        // Get last child and convert to flow if needed
        $children = $this->markup->getChildren();
        if (!empty($children)) {
            $lastChild = array_pop($children);
            
            if ($lastChild instanceof MarkupFlow) {
                $lastChild->add($sep);
            } else {
                $lastChild = new MarkupFlow([$lastChild, $sep]);
            }
            
            $children[] = $lastChild;
            $this->markup->setChildren($children);
        }
        
        return $this;
    }
    
    public function render(): string
    {
        return $this->markup->render();
    }
}

// Usage
$breadcrumb = new Breadcrumb();
$breadcrumb->addItem('Home', '/')
           ->addSeparator()
           ->addItem('Products', '/products')
           ->addSeparator()
           ->addItem('Electronics', '/products/electronics')
           ->addSeparator()
           ->addItem('Current Product');

echo $breadcrumb->render();
/*
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Home</a><span class="breadcrumb-separator"> / </span></li>
        <li class="breadcrumb-item"><a href="/products">Products</a><span class="breadcrumb-separator"> / </span></li>
        <li class="breadcrumb-item"><a href="/products/electronics">Electronics</a><span class="breadcrumb-separator"> / </span></li>
        <li class="breadcrumb-item"><span class="active">Current Product</span></li>
    </ol>
</nav>
*/
```

---

### Tag Cloud with Counts

```php
function createTagCloud(array $tags): Markup
{
    $tagFlows = [];
    
    foreach ($tags as $tag => $count) {
        $tagLink = new Markup(
            '<a href="/tag/%slug%" class="tag-link">%children%</a>',
            children: [$tag]
        );
        $tagLink->setAttribute('href', '/tag/' . sanitize_title($tag));
        
        $countBadge = new Markup(
            '<span class="tag-count">%children%</span>',
            children: [(string)$count]
        );
        
        $tagFlows[] = new MarkupFlow([$tagLink, ' ', $countBadge]);
    }
    
    return new Markup(
        wrapper: '<div class="tag-cloud">%children%</div>',
        children_wrapper: '<span class="tag-item">%child%</span>',
        children: $tagFlows
    );
}

// Usage
$tags = [
    'PHP' => 42,
    'JavaScript' => 38,
    'HTML' => 56,
    'CSS' => 45,
    'WordPress' => 33
];

$cloud = createTagCloud($tags);
echo $cloud->render();
/*
<div class="tag-cloud">
    <span class="tag-item"><a href="/tag/php" class="tag-link">PHP</a> <span class="tag-count">42</span></span>
    <span class="tag-item"><a href="/tag/javascript" class="tag-link">JavaScript</a> <span class="tag-count">38</span></span>
    <span class="tag-item"><a href="/tag/html" class="tag-link">HTML</a> <span class="tag-count">56</span></span>
    <span class="tag-item"><a href="/tag/css" class="tag-link">CSS</a> <span class="tag-count">45</span></span>
    <span class="tag-item"><a href="/tag/wordpress" class="tag-link">WordPress</a> <span class="tag-count">33</span></span>
</div>
*/
```

---

## Advanced Techniques

### Dynamic Flow Building

```php
function buildComplexItem(array $data): MarkupFlow
{
    $flow = new MarkupFlow();
    
    // Always add title
    if (isset($data['title'])) {
        $title = new Markup(
            '<h4>%children%</h4>',
            children: [$data['title']]
        );
        $flow->add($title);
    }
    
    // Conditionally add description
    if (!empty($data['description'])) {
        $desc = new Markup(
            '<p class="description">%children%</p>',
            children: [$data['description']]
        );
        $flow->add($desc);
    }
    
    // Add metadata if available
    if (!empty($data['meta'])) {
        $metaItems = [];
        foreach ($data['meta'] as $key => $value) {
            $metaItems[] = new Markup(
                '<span class="meta-item">%children%</span>',
                children: ["{$key}: {$value}"]
            );
        }
        $flow->add(new MarkupFlow($metaItems));
    }
    
    // Add actions
    if (!empty($data['actions'])) {
        foreach ($data['actions'] as $action) {
            $button = new Markup(
                '<button>%children%</button>',
                children: [$action['label']]
            );
            $button->addClass('btn', 'btn-sm')
                   ->setAttribute('data-action', $action['id']);
            $flow->add($button);
        }
    }
    
    return $flow;
}

// Usage
$items = [
    [
        'title' => 'Item One',
        'description' => 'First item description',
        'meta' => ['Author' => 'John', 'Date' => '2024-01-15'],
        'actions' => [
            ['id' => 'edit', 'label' => 'Edit'],
            ['id' => 'delete', 'label' => 'Delete']
        ]
    ],
    [
        'title' => 'Item Two',
        'actions' => [
            ['id' => 'view', 'label' => 'View']
        ]
    ]
];

$list = new Markup(
    wrapper: '<div class="items">%children%</div>',
    children_wrapper: '<div class="item">%child%</div>',
    children: array_map('buildComplexItem', $items)
);

echo $list->render();
```

---

### Nested Flows

```php
// Flows within flows for complex layouts
$header = new MarkupFlow([
    new Markup('<img src="/logo.png" alt="Logo" />'),
    new Markup('<h1>%children%</h1>', children: ['Site Title'])
]);

$nav = new MarkupFlow([
    new Markup('<nav>%children%</nav>', children: [
        new Markup('<a href="/">%children%</a>', children: ['Home']),
        new Markup('<a href="/about">%children%</a>', children: ['About'])
    ])
]);

$topBar = new MarkupFlow([
    $header,
    $nav
]);

$page = new Markup(
    wrapper: '<div class="page">%children%</div>',
    children_wrapper: '<div class="section">%child%</div>',
    children: [$topBar]
);
```

---

### Conditional Content Assembly

```php
function assembleContent(
    string $text,
    bool $includeBadge = false,
    bool $includeIcon = false,
    bool $includeLink = false
): MarkupFlow
{
    $flow = new MarkupFlow();
    
    // Icon first if requested
    if ($includeIcon) {
        $icon = new Markup('<i class="icon">%children%</i>', children: ['★']);
        $flow->add($icon, ' ');
    }
    
    // Main text
    $flow->add($text);
    
    // Badge if requested
    if ($includeBadge) {
        $badge = new Markup(
            '<span class="badge">%children%</span>',
            children: ['NEW']
        );
        $flow->add(' ', $badge);
    }
    
    // Wrap in link if requested
    if ($includeLink) {
        $link = new Markup(
            '<a href="/details">%children%</a>',
            children: $flow->items()
        );
        return new MarkupFlow([$link]);
    }
    
    return $flow;
}

// Usage - different variations
$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: [
        assembleContent('Basic text'),
        assembleContent('With badge', includeBadge: true),
        assembleContent('With icon', includeIcon: true),
        assembleContent('Complete', true, true, true)
    ]
);
```

---

## Best Practices

### 1. Use Flows for Mixed Content Types

```php
// ✅ Good - flow when mixing types
$flow = new MarkupFlow([
    'Text',
    new Markup('<strong>%children%</strong>', children: ['Bold']),
    'More text'
]);

// ❌ Not needed - simple array works fine
$items = ['Item 1', 'Item 2', 'Item 3']; // Use this directly
```

### 2. Keep Flows Focused

```php
// ✅ Good - focused flow for a single purpose
$menuItem = new MarkupFlow([
    $icon,
    $label
]);

// ❌ Too complex - consider separate flows or restructuring
$everything = new MarkupFlow([
    $header,
    $subheader,
    $content,
    $metadata,
    $footer
]);
```

### 3. Leverage Chainable Methods

```php
// ✅ Good - fluent chaining
$flow = new MarkupFlow()
    ->add($icon)
    ->add(' ')
    ->add($label);

// ✅ Also good - constructor with array
$flow = new MarkupFlow([$icon, ' ', $label]);
```

### 4. Check Before Processing

```php
// ✅ Good - check if flow has content
if (!$flow->isEmpty()) {
    $list->children($flow);
}

// ✅ Good - count-based logic
if ($flow->count() > 3) {
    // Handle large flows differently
}
```

---

## Common Patterns

### Pattern: Icon + Text

```php
function iconText(string $icon, string $text): MarkupFlow
{
    return new MarkupFlow([
        new Markup('<i>%children%</i>', children: [$icon]),
        ' ',
        $text
    ]);
}

$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: [
        iconText('📧', 'Email us'),
        iconText('📞', 'Call us'),
        iconText('💬', 'Chat with us')
    ]
);
```

### Pattern: Label + Value Pairs

```php
function labelValue(string $label, string $value): MarkupFlow
{
    return new MarkupFlow([
        new Markup('<dt>%children%</dt>', children: [$label]),
        new Markup('<dd>%children%</dd>', children: [$value])
    ]);
}

$dl = new Markup(
    wrapper: '<dl>%children%</dl>',
    children: [
        labelValue('Name', 'John Doe'),
        labelValue('Email', 'john@example.com'),
        labelValue('Phone', '+1 234 567 8900')
    ]
);
```

### Pattern: Prefix + Content + Suffix

```php
function wrapContent(
    string $content,
    ?string $prefix = null,
    ?string $suffix = null
): MarkupFlow
{
    $flow = new MarkupFlow();
    
    if ($prefix) {
        $flow->add(new Markup('<span class="prefix">%children%</span>', children: [$prefix]));
    }
    
    $flow->add($content);
    
    if ($suffix) {
        $flow->add(new Markup('<span class="suffix">%children%</span>', children: [$suffix]));
    }
    
    return $flow;
}
```

---

## Performance Notes

### Memory Efficiency

```php
// MarkupFlow is lightweight - just wraps an array
$flow = new MarkupFlow(); // ~200 bytes

// Adding items doesn't create additional wrappers
$flow->add('Item 1', 'Item 2', 'Item 3'); // ~200 bytes + item sizes
```

### Rendering Performance

```php
// MarkupFlow has no rendering overhead
// Items are processed directly during parent rendering

// This:
$flow = new MarkupFlow([$item1, $item2]);
$markup->children($flow);

// Is just as fast as:
$markup->children($item1, $item2);

// The difference is in how items flow together in the output
```

---

## Troubleshooting

### Problem: Flow Not Rendering

```php
// ❌ Wrong: Adding flow to wrong place
$flow = new MarkupFlow(['Content']);
// Forgot to add to markup

// ✅ Correct: Add flow as child
$markup->children($flow);
```

### Problem: Items Not Flowing Together

```php
// ❌ Wrong: Adding items separately
$markup->children('Item 1', 'Item 2'); // Each gets own wrapper

// ✅ Correct: Flow items together
$flow = new MarkupFlow(['Item 1', 'Item 2']);
$markup->children($flow); // Flow together in one wrapper
```

### Problem: Empty Wrappers Rendering

```php
// ❌ Wrong: Always adding flow
$flow = new MarkupFlow();
$markup->children($flow); // Renders empty wrapper

// ✅ Correct: Check before adding
if (!$flow->isEmpty()) {
    $markup->children($flow);
}
```

---

## See Also

- [Markup Class](markup.html) - Main markup building class
- [MarkupFactory](markup-factory.html) - Quick creation methods  
- [MarkupSlot](markup-slot.html) - Slot system for named placeholders
- [Getting Started](getting-started.html) - Introduction and first steps

