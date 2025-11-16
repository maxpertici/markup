---
layout: default
title: MarkupSlot
nav_order: 5
---

# MarkupSlot Class
{: .no_toc }

Slot system for creating reusable named content placeholders.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupSlot` class allows you to define named slots in your HTML structures, similar to slot systems in Vue.js or Laravel Blade. A slot is a placeholder that can be filled later with dynamic content.

### Key Features

- **Named slots** - Identify each placeholder with a unique name
- **Optional wrappers** - Wrap slot content in an HTML template
- **Conditional preservation** - Control wrapper display even when slot is empty
- **Description** - Document the intended usage of each slot
- **Auto-registration** - Slots are automatically detected by the `Markup` class

### Use Case

```php
use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupSlot;

// Create a layout with slots
$layout = new Markup('<div class="page">%children%</div>');
$layout->children(
    new MarkupSlot('header', '<header>%slot%</header>', 'Page header'),
    new MarkupSlot('content', '<main>%slot%</main>', 'Main content'),
    new MarkupSlot('sidebar', '<aside>%slot%</aside>', 'Sidebar'),
    new MarkupSlot('footer', '<footer>%slot%</footer>', 'Page footer')
);

// Fill the slots
$layout->slot('header', [
    new Markup('<h1>%children%</h1>', children: ['My Site'])
]);

$layout->slot('content', [
    '<p>Page content</p>'
]);

// The 'sidebar' slot remains empty - its wrapper won't be displayed
// The 'footer' slot remains empty as well

echo $layout->render();
```

---

## Constructor

```php
public function __construct(
    string $name,
    string $wrapper = '',
    string $description = ''
)
```

Creates a new slot declaration that will be automatically registered by `Markup` when added as a child.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | `string` | **Required.** The unique name/identifier for the slot |
| `$wrapper` | `string` | **Optional.** HTML template with the `%slot%` placeholder |
| `$description` | `string` | **Optional.** Description of the slot's usage |

### Examples

```php
use MaxPertici\Markup\MarkupSlot;

// Simple slot without wrapper
$slot = new MarkupSlot('content');

// Slot with HTML wrapper
$header = new MarkupSlot(
    name: 'header',
    wrapper: '<header class="site-header">%slot%</header>'
);

// Complete slot with description
$sidebar = new MarkupSlot(
    name: 'sidebar',
    wrapper: '<aside class="sidebar" role="complementary">%slot%</aside>',
    description: 'Sidebar for widgets and secondary information'
);

// Slot with complex wrapper
$card = new MarkupSlot(
    name: 'card-content',
    wrapper: '<div class="card"><div class="card-body">%slot%</div></div>'
);
```

---

## Name Management

### name()

Sets or retrieves the slot name.

```php
name(?string $name = null): string|self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | `string\|null` | Optional. If null, acts as getter |

#### Return

- `string` - The slot name when used as getter
- `self` - The instance for chaining when used as setter

#### Examples

```php
// Create a slot
$slot = new MarkupSlot('header');

// Get the name
$name = $slot->name(); // 'header'

// Modify the name
$slot->name('main-header');

// Chaining
$slot->name('site-header')
     ->description('Main site header');
```

---

## Description Management

### description()

Sets or retrieves a description of the slot.

```php
description(?string $description = null): string|self
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$description` | `string\|null` | Optional. If null, acts as getter |

#### Return

- `string` - The description when used as getter
- `self` - The instance for chaining when used as setter

#### Examples

```php
$slot = new MarkupSlot('content');

// Set a description
$slot->description('Main content area of the page');

// Get the description
$desc = $slot->description();
echo $desc; // 'Main content area of the page'

// Chaining with constructor
$slot = new MarkupSlot('footer', '<footer>%slot%</footer>')
    ->description('Page footer with copyright information');
```

---

## Wrapper Management

### wrapper()

Sets or retrieves the slot's wrapper template.

```php
wrapper(?string $wrapper = null): string|self
```

The wrapper is an HTML template that wraps the slot content. Use the `%slot%` placeholder to indicate where the content should be inserted.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$wrapper` | `string\|null` | Optional. Template with `%slot%`. If null, acts as getter |

#### Return

- `string` - The wrapper when used as getter
- `self` - The instance for chaining when used as setter

#### Examples

```php
$slot = new MarkupSlot('content');

// Set a simple wrapper
$slot->wrapper('<div class="content">%slot%</div>');

// Wrapper with classes and attributes
$slot->wrapper('<section class="hero" id="hero-section">%slot%</section>');

// Wrapper with nested structure
$slot->wrapper('
    <div class="card">
        <div class="card-header">
            <h3>Title</h3>
        </div>
        <div class="card-body">
            %slot%
        </div>
    </div>
');

// Get the wrapper
$template = $slot->wrapper();
echo $template; // '<div class="content">%slot%</div>'

// Chaining
$slot->wrapper('<main>%slot%</main>')
     ->preserve(true);
```

---

## Wrapper Preservation

### preserve()

Enables wrapper preservation even when the slot is empty.

```php
preserve(bool $preserve = true): self
```

By default, if a slot is not filled with content, its wrapper is not displayed. The `preserve()` method allows you to force the display of the wrapper even when empty.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$preserve` | `bool` | Optional. True to preserve, false to not preserve. Default: true |

#### Return

- `self` - The instance for chaining

#### Examples

```php
// Slot with preservation
$sidebar = new MarkupSlot('sidebar', '<aside class="sidebar">%slot%</aside>');
$sidebar->preserve();

// Even if the slot remains empty, the following HTML will be generated:
// <aside class="sidebar"></aside>

// Disable preservation
$sidebar->preserve(false);

// Empty slot without preservation = no HTML generated

// Use case: CSS Grid structure
$grid = new Markup('<div class="grid">%children%</div>');
$grid->children(
    // These slots will always be present to maintain the CSS Grid structure
    (new MarkupSlot('col-1', '<div class="col">%slot%</div>'))->preserve(),
    (new MarkupSlot('col-2', '<div class="col">%slot%</div>'))->preserve(),
    (new MarkupSlot('col-3', '<div class="col">%slot%</div>'))->preserve()
);
```

### isPreserved()

Checks if the wrapper will be preserved even when the slot is empty.

```php
isPreserved(): bool
```

#### Return

- `bool` - True if the wrapper is preserved, false otherwise

#### Examples

```php
$slot = new MarkupSlot('content', '<div>%slot%</div>');

// By default, not preserved
var_dump($slot->isPreserved()); // false

// Enable preservation
$slot->preserve();
var_dump($slot->isPreserved()); // true

// Conditional usage
if (!$slot->isPreserved()) {
    $slot->preserve(true);
}
```

---

## Data Export

### toArray()

Returns slot information as an associative array.

```php
toArray(): array
```

#### Return

- `array` - Associative array with keys:
  - `name` (string) - The slot name
  - `description` (string) - The slot description
  - `wrapper` (string) - The wrapper template
  - `preserve` (bool) - The preservation state

#### Examples

```php
$slot = new MarkupSlot(
    name: 'header',
    wrapper: '<header>%slot%</header>',
    description: 'Page header'
);
$slot->preserve();

$data = $slot->toArray();
print_r($data);

/*
Array
(
    [name] => header
    [description] => Page header
    [wrapper] => <header>%slot%</header>
    [preserve] => 1
)
*/

// Usage for serialization
$json = json_encode($slot->toArray());

// Usage for debugging
$info = $slot->toArray();
echo "Slot '{$info['name']}': {$info['description']}";
```

---

## Practical Examples

### Complete Blog Layout

```php
use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupSlot;

// Create the main layout
$blogLayout = new Markup('<div class="blog-layout">%children%</div>');

// Define all slots
$blogLayout->children(
    // Header with logo and navigation
    new MarkupSlot(
        name: 'header',
        wrapper: '<header class="site-header">%slot%</header>',
        description: 'Site header with logo and menu'
    ),
    
    // Main content area
    new MarkupSlot(
        name: 'content',
        wrapper: '<main class="main-content" role="main">%slot%</main>',
        description: 'Main page content'
    ),
    
    // Sidebar (preserved to maintain grid)
    (new MarkupSlot(
        name: 'sidebar',
        wrapper: '<aside class="sidebar" role="complementary">%slot%</aside>',
        description: 'Widgets and secondary information'
    ))->preserve(),
    
    // Footer
    new MarkupSlot(
        name: 'footer',
        wrapper: '<footer class="site-footer">%slot%</footer>',
        description: 'Page footer with copyright and links'
    )
);

// Fill the header
$blogLayout->slot('header', [
    new Markup('<h1 class="logo">%children%</h1>', children: ['My Blog']),
    new Markup('<nav>%children%</nav>', children: [
        '<a href="/">Home</a>',
        '<a href="/articles">Articles</a>',
        '<a href="/contact">Contact</a>'
    ])
]);

// Fill the content
$blogLayout->slot('content', [
    new Markup('<article>%children%</article>', children: [
        new Markup('<h2>%children%</h2>', children: ['Article Title']),
        new Markup('<p>%children%</p>', children: ['Article content...'])
    ])
]);

// Fill the sidebar
$blogLayout->slot('sidebar', [
    new Markup('<div class="widget">%children%</div>', children: [
        new Markup('<h3>%children%</h3>', children: ['Recent Articles']),
        new Markup('<ul>%children%</ul>', children: [
            '<li>Article 1</li>',
            '<li>Article 2</li>',
            '<li>Article 3</li>'
        ])
    ])
]);

// Fill the footer
$blogLayout->slot('footer', [
    '<p>&copy; 2024 My Blog. All rights reserved.</p>'
]);

echo $blogLayout->render();
```

### Card Component with Multiple Slots

```php
function createCardComponent(): Markup
{
    $card = new Markup('<div class="card">%children%</div>');
    
    // Define slots for different card parts
    $card->children(
        // Optional image at top
        new MarkupSlot(
            name: 'image',
            wrapper: '<div class="card-img-top">%slot%</div>',
            description: 'Main card image'
        ),
        
        // Card header (always preserved)
        (new MarkupSlot(
            name: 'header',
            wrapper: '<div class="card-header">%slot%</div>',
            description: 'Card title or header'
        ))->preserve(),
        
        // Main body
        new MarkupSlot(
            name: 'body',
            wrapper: '<div class="card-body">%slot%</div>',
            description: 'Main card content'
        ),
        
        // Actions/buttons at bottom
        new MarkupSlot(
            name: 'actions',
            wrapper: '<div class="card-footer">%slot%</div>',
            description: 'Action buttons'
        )
    );
    
    return $card;
}

// Use the component
$productCard = createCardComponent();

$productCard
    ->slot('image', [
        new Markup('<img %attributes% />')
            ->setAttribute('src', '/product.jpg')
            ->setAttribute('alt', 'Product')
    ])
    ->slot('header', [
        new Markup('<h3>%children%</h3>', children: ['Product Name'])
    ])
    ->slot('body', [
        new Markup('<p>%children%</p>', children: ['Product description...']),
        new Markup('<p class="price">%children%</p>', children: ['$29.99'])
    ])
    ->slot('actions', [
        new Markup('<button class="btn btn-primary">%children%</button>', children: ['Buy'])
    ]);

echo $productCard->render();
```

### Conditional Slot with Variations

```php
function createResponsiveLayout(bool $hasSidebar = true, bool $hasAds = false): Markup
{
    $layout = new Markup('<div class="responsive-layout">%children%</div>');
    
    // Header always present
    $layout->children(
        (new MarkupSlot(
            name: 'header',
            wrapper: '<header>%slot%</header>'
        ))->preserve()
    );
    
    // Main content always present
    $layout->children(
        (new MarkupSlot(
            name: 'content',
            wrapper: '<main>%slot%</main>'
        ))->preserve()
    );
    
    // Conditional sidebar
    if ($hasSidebar) {
        $layout->children(
            new MarkupSlot(
                name: 'sidebar',
                wrapper: '<aside class="sidebar">%slot%</aside>',
                description: 'Main sidebar'
            )
        );
    }
    
    // Conditional ad zone
    if ($hasAds) {
        $layout->children(
            new MarkupSlot(
                name: 'ads',
                wrapper: '<div class="ad-zone">%slot%</div>',
                description: 'Advertising zone'
            )
        );
    }
    
    // Footer always present
    $layout->children(
        (new MarkupSlot(
            name: 'footer',
            wrapper: '<footer>%slot%</footer>'
        ))->preserve()
    );
    
    return $layout;
}

// Layout with sidebar but without ads
$layout = createResponsiveLayout(hasSidebar: true, hasAds: false);

$layout
    ->slot('header', ['<h1>My Site</h1>'])
    ->slot('content', ['<p>Main content</p>'])
    ->slot('sidebar', ['<div>Sidebar widget</div>'])
    ->slot('footer', ['<p>© 2024</p>']);

echo $layout->render();
```

### Reusable Sections System

```php
class PageBuilder
{
    private Markup $page;
    
    public function __construct()
    {
        $this->page = new Markup('<div class="page-builder">%children%</div>');
    }
    
    public function addSection(
        string $name,
        string $cssClass = '',
        bool $fullWidth = false,
        bool $preserve = false
    ): self {
        $wrapperClass = $fullWidth ? 'section section-full' : 'section section-contained';
        if ($cssClass) {
            $wrapperClass .= ' ' . $cssClass;
        }
        
        $slot = new MarkupSlot(
            name: $name,
            wrapper: "<section class=\"{$wrapperClass}\">%slot%</section>",
            description: "Section: {$name}"
        );
        
        if ($preserve) {
            $slot->preserve();
        }
        
        $this->page->children($slot);
        
        return $this;
    }
    
    public function fillSection(string $name, array $content): self
    {
        $this->page->slot($name, $content);
        return $this;
    }
    
    public function render(): string
    {
        return $this->page->render();
    }
    
    public function getSlotsInfo(): array
    {
        return $this->page->getSlotsInfo();
    }
}

// Usage
$builder = new PageBuilder();

$builder
    ->addSection('hero', 'bg-primary text-white', fullWidth: true, preserve: true)
    ->addSection('features', 'py-5')
    ->addSection('testimonials', 'bg-light')
    ->addSection('cta', 'bg-dark text-white', fullWidth: true);

// Fill the sections
$builder
    ->fillSection('hero', [
        new Markup('<h1>%children%</h1>', children: ['Welcome']),
        new Markup('<p>%children%</p>', children: ['Discover our services'])
    ])
    ->fillSection('features', [
        new Markup('<div class="row">%children%</div>', children: [
            '<div class="col">Feature 1</div>',
            '<div class="col">Feature 2</div>',
            '<div class="col">Feature 3</div>'
        ])
    ])
    ->fillSection('cta', [
        new Markup('<button>%children%</button>', children: ['Get Started Now'])
    ]);

// Display slot information
print_r($builder->getSlotsInfo());

// Render the page
echo $builder->render();
```

---

## Integration with Markup

`MarkupSlot` instances are automatically detected and registered by the `Markup` class when added as children. Here's how `Markup` interacts with slots:

### Automatic Detection

```php
$markup = new Markup('<div>%children%</div>');

// MarkupSlots are automatically detected
$markup->children(
    new MarkupSlot('header', '<header>%slot%</header>'),
    'Normal text', // Remains a normal child
    new MarkupSlot('footer', '<footer>%slot%</footer>')
);

// Check registered slots
$slots = $markup->slots(); // Returns both MarkupSlots
```

### Associated Markup Methods

```php
// Fill a slot
$markup->slot('header', ['Header content']);

// Check existence
if ($markup->hasSlot('sidebar')) {
    // The slot exists
}

// Check if filled
if ($markup->isSlotFilled('header')) {
    // The slot contains content
}

// Get a specific slot
$headerSlot = $markup->getSlot('header');
if ($headerSlot) {
    echo $headerSlot->description();
}

// List of slot names
$names = $markup->slotNames();
// ['header', 'footer']

// Filled slots only
$filled = $markup->filledSlotNames();
// ['header'] if only header has been filled

// Detailed information
$info = $markup->getSlotsInfo();
/*
[
    'header' => [
        'name' => 'header',
        'description' => '',
        'wrapper' => '<header>%slot%</header>',
        'preserve' => false,
        'filled' => true,
        'items_count' => 1
    ],
    ...
]
*/
```

---

## Best Practices

### Slot Naming

```php
// ✅ Good - descriptive and clear names
new MarkupSlot('main-navigation', ...)
new MarkupSlot('hero-section', ...)
new MarkupSlot('article-content', ...)

// ❌ Avoid - vague or generic names
new MarkupSlot('slot1', ...)
new MarkupSlot('div', ...)
new MarkupSlot('content', ...) // Too vague in some contexts
```

### Using Descriptions

```php
// ✅ Good - useful descriptions for documentation
new MarkupSlot(
    name: 'product-gallery',
    wrapper: '<div class="gallery">%slot%</div>',
    description: 'Product image gallery. Accepts multiple images.'
);

// ✅ Good - technical description for developers
new MarkupSlot(
    name: 'async-content',
    wrapper: '<div class="lazy-load" data-src="%slot%"></div>',
    description: 'Lazy-loaded content. Resource URL expected.'
);
```

### Appropriate Preservation

```php
// ✅ Preserve to maintain CSS Grid/Flex structure
$grid = new Markup('<div class="css-grid">%children%</div>');
$grid->children(
    (new MarkupSlot('col-1', '<div class="grid-col">%slot%</div>'))->preserve(),
    (new MarkupSlot('col-2', '<div class="grid-col">%slot%</div>'))->preserve()
);

// ✅ Don't preserve for optional content
$page = new Markup('<div>%children%</div>');
$page->children(
    new MarkupSlot('optional-banner', '<div class="banner">%slot%</div>')
    // If empty, the banner doesn't display
);

// ❌ Avoid preserving everything without reason
// This can create unnecessary empty elements in the DOM
```

### Organization and Reusability

```php
// ✅ Good - create reusable components
class ComponentFactory
{
    public static function createCard(): Markup
    {
        $card = new Markup('<div class="card">%children%</div>');
        $card->children(
            new MarkupSlot('image', '<div class="card-img">%slot%</div>'),
            new MarkupSlot('title', '<h3 class="card-title">%slot%</h3>'),
            new MarkupSlot('body', '<div class="card-body">%slot%</div>'),
            new MarkupSlot('actions', '<div class="card-actions">%slot%</div>')
        );
        return $card;
    }
}

// Usage
$card = ComponentFactory::createCard();
$card
    ->slot('title', ['My Title'])
    ->slot('body', ['My content']);
```

---

## Troubleshooting

### Problem: Slot Doesn't Display

```php
// ❌ Slot doesn't appear in render
$markup = new Markup('<div>%children%</div>');
$slot = new MarkupSlot('header', '<header>%slot%</header>');
// Slot created but never added to markup

// ✅ Solution: add slot as child
$markup->children($slot);
$markup->slot('header', ['Content']);
```

### Problem: %slot% Placeholder Not Replaced

```php
// ❌ Wrong placeholder
new MarkupSlot('content', '<div>%content%</div>') // Uses %content%

// ✅ Correct placeholder
new MarkupSlot('content', '<div>%slot%</div>') // Uses %slot%
```

### Problem: Wrapper Doesn't Disappear When Slot is Empty

```php
// Wrapper displays even when empty
$slot = (new MarkupSlot('sidebar', '<aside>%slot%</aside>'))->preserve();

// ✅ Solution: disable preservation
$slot->preserve(false);
// or don't call preserve() at all
```

### Problem: Checking if a Slot Exists vs Is Filled

```php
$markup = new Markup('<div>%children%</div>');
$markup->children(
    new MarkupSlot('header', '<header>%slot%</header>')
);

// Slot exists but is not filled
var_dump($markup->hasSlot('header'));      // true
var_dump($markup->isSlotFilled('header')); // false

// After filling
$markup->slot('header', ['Content']);
var_dump($markup->isSlotFilled('header')); // true
```

---

## Performance Notes

### Slot Creation

Slots are lightweight objects with no significant performance impact:

```php
// Creating 100 slots
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    new MarkupSlot("slot-{$i}", "<div>%slot%</div>");
}
$time = microtime(true) - $start;
// ~0.001s on modern machine
```

### Recommendations

1. **Create reusable slots** - Define them once, use them multiple times
2. **Avoid excessive dynamic slots** - Don't create slots on the fly in loops
3. **Use preserve() judiciously** - Only when necessary for structure

---

## See Also

- [Markup Class](markup.html) - Main class and slot management
- [MarkupFactory](markup-factory.html) - Quick creation methods
- [MarkupFinder](markup-finder.html) - Search and query
- [Getting Started Guide](getting-start.html) - Introduction and first steps

