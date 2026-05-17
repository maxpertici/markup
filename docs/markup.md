---
layout: default
title: Markup
nav_order: 3
---

# Markup Class
{: .no_toc }

The main class for programmatically building and manipulating HTML structures.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `Markup` class is the core of the library. It provides a fluent and chainable API for building complex HTML structures with support for:

- **HTML Wrappers** - Templates with placeholders for content
- **CSS Classes** - Dynamic class management
- **HTML Attributes** - Complete attribute control
- **Children** - Composition of nested elements
- **Slots** - Named placeholder system
- **Search** - Query the generated DOM tree
- **Dual rendering mode** - Buffer or streaming

## Constructor

```php
public function __construct(
    string $wrapper = '',
    array $wrapperClass = [],
    array $wrapperAttributes = [],
    string $childrenWrapper = '',
    array $children = []
)
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$wrapper` | `string` | HTML template with `%children%` placeholder |
| `$wrapperClass` | `array` | Array of CSS classes |
| `$wrapperAttributes` | `array` | Associative array of HTML attributes |
| `$childrenWrapper` | `string` | HTML template with `%child%` placeholder to wrap each child |
| `$children` | `array` | Array of initial children |

### Examples

```php
use MaxPertici\Markup\Markup;

// Simple creation
$div = new Markup('<div>%children%</div>');

// With classes and attributes
$card = new Markup(
    wrapper: '<div class="%classes%" %attributes%>%children%</div>',
    wrapperClass: ['card', 'shadow'],
    wrapperAttributes: ['id' => 'main-card', 'data-type' => 'product']
);

// With children wrapper
$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    childrenWrapper: '<li>%child%</li>',
    children: ['Item 1', 'Item 2', 'Item 3']
);
```

---

## Metadata

### slug()

Sets or retrieves the element's slug identifier.

```php
slug(?string $slug = null): self|string|null
```

**Examples:**

```php
// Set a slug
$markup->slug('hero-section');

// Get the slug
$slug = $markup->slug(); // 'hero-section'

// Chainable
$markup->slug('header')->addClass('primary');
```

### description()

Sets or retrieves an element description.

```php
description(?string $description = null): self|string|null
```

**Examples:**

```php
$markup->description('Main section with call-to-action');
$desc = $markup->description(); // Retrieves the description
```

---

## CSS Class Management

### addClass()

Adds one or more CSS classes to the wrapper.

```php
addClass(string|array ...$classes): self
```

**Examples:**

```php
// Single class
$markup->addClass('active');

// Multiple classes
$markup->addClass('btn', 'btn-primary', 'btn-lg');

// Array of classes
$markup->addClass(['container', 'mx-auto']);

// Mixed
$markup->addClass('card', ['shadow', 'rounded']);
```

### removeClass()

Removes one or more CSS classes.

```php
removeClass(string|array ...$classes): self
```

**Examples:**

```php
$markup->removeClass('active');
$markup->removeClass('btn-primary', 'btn-lg');
$markup->removeClass(['shadow', 'rounded']);
```

### hasClass()

Checks if a class exists.

```php
hasClass(string $class): bool
```

**Examples:**

```php
if ($markup->hasClass('active')) {
    // Class exists
}
```

### classes()

Retrieves or replaces all classes.

```php
classes(?array $classes = null): self|array
```

**Examples:**

```php
// Get classes
$classes = $markup->classes();

// Replace all classes
$markup->classes(['new-class', 'another-class']);
```

---

## HTML Attribute Management

### setAttribute()

Sets or removes an HTML attribute.

```php
setAttribute(string $name, ?string $value): self
```

**Examples:**

```php
// Set an attribute
$markup->setAttribute('id', 'main-content');
$markup->setAttribute('data-user-id', '123');
$markup->setAttribute('role', 'navigation');
$markup->setAttribute('required', '');

// Remove an attribute (null value)
$markup->setAttribute('disabled', null);
```

### removeAttribute()

Removes an HTML attribute.

```php
removeAttribute(string $name): self
```

**Examples:**

```php
$markup->removeAttribute('disabled');
$markup->removeAttribute('data-temp');
```

### hasAttribute()

Checks if an attribute exists.

```php
hasAttribute(string $name): bool
```

**Examples:**

```php
if ($markup->hasAttribute('disabled')) {
    // Attribute exists
}
```

### getAttribute()

Retrieves an attribute value.

```php
getAttribute(string $name): ?string
```

**Examples:**

```php
$id = $markup->getAttribute('id');
$role = $markup->getAttribute('role');
```

### attributes()

Retrieves or replaces all attributes.

```php
attributes(?array $attributes = null): self|array
```

**Examples:**

```php
// Get attributes
$attrs = $markup->attributes();

// Replace all attributes
$markup->attributes([
    'id' => 'new-id',
    'class' => 'new-class',
    'data-value' => 'test'
]);
```

---

## Children Management

### children() / append()

Adds children to the element.

```php
children(mixed ...$children): self
append(mixed ...$children): self // Alias
```

Children can be:
- Strings (HTML)
- `Markup` instances
- `MarkupSlot` instances
- Callables (anonymous functions)
- Arrays of elements

**Examples:**

```php
// Strings
$markup->children('Simple text');

// Markup instances
$markup->children(
    new Markup('<h1>%children%</h1>', children: ['Title']),
    new Markup('<p>%children%</p>', children: ['Paragraph'])
);

// Mixed
$markup->children(
    'Text',
    new Markup('<div>%children%</div>'),
    function() { echo 'From callback'; }
);

// Arrays
$markup->children(['Item 1', 'Item 2', 'Item 3']);

// Using append() alias
$markup->append('More content');
```

### getChildren()

Retrieves the children array.

```php
getChildren(): array
```

**Examples:**

```php
$children = $markup->getChildren();
$count = count($children);
```

### setChildren()

Completely replaces the children array.

```php
setChildren(array $children): self
```

**Examples:**

```php
$markup->setChildren([
    'New child 1',
    new Markup('<div>%children%</div>', children: ['New child 2'])
]);
```

### orderChildren()

Reorganizes children with a callback function.

```php
orderChildren(callable $callback): self
```

**Examples:**

```php
// Reverse order
$markup->orderChildren(fn($children) => array_reverse($children));

// Sort by custom criteria
$markup->orderChildren(function($children) {
    usort($children, function($a, $b) {
        // Custom sorting logic
        return 0;
    });
    return $children;
});
```

---

## Slot System

Slots allow you to define named locations for content, similar to Vue.js or Laravel Blade.

### slot()

Adds content to a named slot.

```php
slot(string $name, mixed $items): self
```

**Examples:**

```php
// Add content to a slot
$markup->slot('header', [
    new Markup('<h1>%children%</h1>', children: ['My Site'])
]);

$markup->slot('content', [
    '<p>Main content</p>',
    new Markup('<div>%children%</div>', children: ['More content'])
]);
```

### slots()

Retrieves declared slots.

```php
slots(?array $names = null): array
```

**Examples:**

```php
// All slots
$allSlots = $markup->slots();

// Specific slots
$selectedSlots = $markup->slots(['header', 'footer']);
```

### getSlot()

Retrieves a specific slot by name.

```php
getSlot(string $name): ?MarkupSlot
```

**Examples:**

```php
$headerSlot = $markup->getSlot('header');
if ($headerSlot) {
    // Slot exists
}
```

### hasSlot()

Checks if a slot has been declared.

```php
hasSlot(string $name): bool
```

**Examples:**

```php
if ($markup->hasSlot('sidebar')) {
    // Slot exists
}
```

### isSlotFilled()

Checks if a slot has been filled with content.

```php
isSlotFilled(string $name): bool
```

**Examples:**

```php
if ($markup->isSlotFilled('header')) {
    // Slot contains content
}
```

### slotNames()

Retrieves the names of all declared slots.

```php
slotNames(): array
```

**Examples:**

```php
$names = $markup->slotNames();
// ['header', 'content', 'footer', 'sidebar']
```

### filledSlotNames()

Retrieves the names of slots that have been filled.

```php
filledSlotNames(): array
```

**Examples:**

```php
$filled = $markup->filledSlotNames();
// ['header', 'content'] - filled slots only
```

### getSlotsInfo()

Retrieves detailed information about all slots.

```php
getSlotsInfo(): array
```

**Examples:**

```php
$info = $markup->getSlotsInfo();
/*
[
    'header' => [
        'name' => 'header',
        'description' => 'Page header',
        'wrapper' => '<header>%slot%</header>',
        'preserve' => false,
        'filled' => true,
        'items_count' => 2
    ],
    ...
]
*/
```

---

## Conditional and Iterative Methods

### when()

Executes a callback if a condition is true.

```php
when(bool $condition, callable $callback): self
```

**Examples:**

```php
$isAdmin = true;

$markup->when($isAdmin, function($m) {
    $m->children('<div class="admin-panel">Admin Panel</div>');
});

// Chaining
$markup
    ->children('Content visible to all')
    ->when($isAdmin, fn($m) => $m->children('Admin content'))
    ->when($isLoggedIn, fn($m) => $m->addClass('authenticated'));
```

### each()

Iterates over an array and executes a callback for each element.

```php
each(array $items, callable $callback): self
```

**Examples:**

```php
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com']
];

$list = new Markup('<ul>%children%</ul>');
$list->each($users, function($user, $index, $markup) {
    $markup->children(
        new Markup(
            '<li>%children%</li>',
            children: [$user['name'] . ' - ' . $user['email']]
        )
    );
});
```

---

## Search and Query

### find()

Creates a `MarkupQueryBuilder` instance for building fluent search queries.

```php
find(): MarkupQueryBuilder
```

The query builder provides a chainable interface for searching elements. It returns a `MarkupCollection` of results.

**Examples:**

```php
// Search by class
$elements = $markup->find()->class('active')->get();

// Search by tag
$divs = $markup->find()->tag('div')->get();

// Search by slug
$header = $markup->find()->slug('main-header')->first();

// Search by attribute
$withRole = $markup->find()->attribute('role', 'navigation')->get();

// CSS selector
$navLinks = $markup->find()->css('nav a')->get();
$activeItems = $markup->find()->css('li.active')->get();

// Multiple criteria (AND logic)
$results = $markup->find()
    ->class('card')
    ->hasAttribute('data-id')
    ->get();

// Custom search with where
$results = $markup->find()
    ->where(fn($m) => $m->hasClass('card') && $m->hasAttribute('data-id'))
    ->get();

// Get first result
$first = $markup->find()->class('active')->first();

// Count results
$count = $markup->find()->tag('div')->count();

// Check existence
$exists = $markup->find()->class('error')->exists();

// Collection transformations
$prices = $markup->find()
    ->class('product')
    ->get()
    ->map(fn($el) => $el->getAttribute('price'))
    ->filter(fn($price) => (float)$price > 50);
```

**Note:** For direct access to `MarkupFinder` methods (like `findByClass()`, `findByTag()`, etc.), you can create a `MarkupFinder` instance directly:

```php
$finder = new \MaxPertici\Markup\MarkupFinder($markup);
$results = $finder->findByClass('active');
```

See [MarkupQueryBuilder](markup-query-builder.html) for the query builder API and [MarkupFinder](markup-finder.html) for the finder API.

---

## Text Extraction

### text()

Extracts plain text content from the markup and its children, ignoring all HTML tags.

```php
text(bool $recursive = true, bool $includeSlots = true, bool $executeCallables = true): string
```

This method recursively traverses the markup tree and extracts all text content while:
- Including string children as-is
- Recursively extracting text from nested Markup instances
- Processing MarkupFlow items
- Optionally including slot content
- Optionally executing callables to capture their output

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$recursive` | `bool` | `true` | Extract text from nested Markup children |
| `$includeSlots` | `bool` | `true` | Include content from filled slots |
| `$executeCallables` | `bool` | `true` | Execute callables and capture their output |

**Examples:**

```php
// Simple text extraction
$markup = new Markup(
    wrapper: '<div>%children%</div>',
    children: ['Hello ', 'World!']
);
echo $markup->text(); // "Hello World!"

// With nested Markup
$link = new Markup('<a href="#">%children%</a>', children: ['Click here']);
$paragraph = new Markup(
    '<p>%children%</p>',
    children: ['Text before link, ', $link, ', text after.']
);
echo $paragraph->text(); // "Text before link, Click here, text after."

// With complex structure
$article = new Markup(
    '<article>%children%</article>',
    children: [
        new Markup('<h2>%children%</h2>', children: ['Title']),
        new Markup('<p>%children%</p>', children: [
            'This is ',
            new Markup('<strong>%children%</strong>', children: ['important']),
            ' content.'
        ])
    ]
);
echo $article->text(); // "TitleThis is important content."

// Non-recursive extraction (only direct children)
$text = $markup->text(recursive: false);

// Exclude slots
$text = $markup->text(includeSlots: false);

// Don't execute callables
$text = $markup->text(executeCallables: false);
```

### Practical Use Cases

**Meta Description Generation:**
```php
$content = new Markup(/* ... */);
$description = substr($content->text(), 0, 160);
echo '<meta name="description" content="' . htmlspecialchars($description) . '">';
```

**Word Count:**
```php
$wordCount = str_word_count($markup->text());
echo "Reading time: " . ceil($wordCount / 200) . " minutes";
```

**Search Indexing:**
```php
$searchableText = strtolower($markup->text());
$index[$documentId] = $searchableText;
```

**Content Validation:**
```php
if (str_contains($markup->text(), $requiredKeyword)) {
    echo "Content contains required keyword";
}
```

**SEO Analysis:**
```php
$text = $markup->text();
$titleText = $title->text();
if (str_contains($text, $titleText)) {
    echo "Title keyword appears in content ✓";
}
```

---

## Rendering Methods

### render()

Generates and returns the HTML as a string.

```php
render(): string
```

**Examples:**

```php
$html = $markup->render();
echo $html;

// Or directly
echo $markup->render();

// Store for later use
$cached = $markup->render();
file_put_contents('cached.html', $cached);
```

**Use when:**
- You need the HTML in a variable
- You want to manipulate the HTML before display
- You need to test or validate the HTML
- You're returning HTML from a function

### print()

Directly outputs the HTML (streaming mode).

```php
print(): void
```

**Examples:**

```php
// Direct output
$markup->print();

// In a template
<?php $header->print(); ?>
```

**Use when:**
- Better performance for large documents
- Reduced memory usage
- Direct output in templates
- Streaming responses

---

## Internal Accessors

### getWrapper()

Retrieves the element's wrapper template.

```php
getWrapper(): string
```

**Examples:**

```php
$wrapper = $markup->getWrapper();
// '<div class="%classes%" %attributes%>%children%</div>'
```

This method is primarily used internally and by `MarkupFinder` to inspect elements.

---

## Template Placeholders

### In the Wrapper

| Placeholder | Description | Example |
|------------|-------------|---------|
| `%children%` | Children location | `<div>%children%</div>` |
| `%classes%` | CSS classes | `<div class="%classes%">` |
| `%attributes%` | HTML attributes | `<div %attributes%>` |

### In the Children Wrapper

| Placeholder | Description | Example |
|------------|-------------|---------|
| `%child%` | Each child individually | `<li>%child%</li>` |

### In the Slot Wrapper

| Placeholder | Description | Example |
|------------|-------------|---------|
| `%slot%` | Slot content | `<header>%slot%</header>` |

---

## Practical Examples

### Reusable Card Component

```php
function createCard(string $title, string $content, ?string $imageUrl = null): Markup
{
    $card = new Markup(
        wrapper: '<div class="%classes%">%children%</div>',
        wrapperClass: ['card', 'shadow-lg']
    );
    
    if ($imageUrl) {
        $img = new Markup('<img %attributes% />');
        $img->setAttribute('src', $imageUrl)
            ->setAttribute('alt', $title)
            ->addClass('card-img-top');
        $card->children($img);
    }
    
    $body = new Markup(
        wrapper: '<div class="card-body">%children%</div>',
        children: [
            new Markup('<h5 class="card-title">%children%</h5>', children: [$title]),
            new Markup('<p class="card-text">%children%</p>', children: [$content])
        ]
    );
    
    $card->children($body);
    
    return $card;
}

// Usage
$card = createCard('My Title', 'My content', '/image.jpg');
echo $card->render();
```

### Layout with Slots

```php
// Define the layout
$layout = new Markup('<div class="app">%children%</div>');
$layout->children(
    new MarkupSlot('header', '<header class="app-header">%slot%</header>'),
    new MarkupSlot('content', '<main class="app-content">%slot%</main>'),
    new MarkupSlot('footer', '<footer class="app-footer">%slot%</footer>')
);

// Fill the slots
$layout
    ->slot('header', [
        new Markup('<h1>%children%</h1>', children: ['My Application'])
    ])
    ->slot('content', [
        new Markup('<p>%children%</p>', children: ['Main content'])
    ])
    ->slot('footer', [
        '<p>&copy; 2024 My Application</p>'
    ]);

$layout->print();
```

### Dynamic List

```php
$users = [
    ['id' => 1, 'name' => 'Alice', 'role' => 'Admin'],
    ['id' => 2, 'name' => 'Bob', 'role' => 'User'],
    ['id' => 3, 'name' => 'Charlie', 'role' => 'User']
];

$list = new Markup('<ul class="user-list">%children%</ul>');
$list->each($users, function($user, $index, $markup) {
    $li = new Markup('<li class="%classes%">%children%</li>');
    $li->setAttribute('data-user-id', (string)$user['id'])
       ->addClass('user-item')
       ->when($user['role'] === 'Admin', fn($m) => $m->addClass('admin'))
       ->children($user['name'] . ' - ' . $user['role']);
    
    $markup->children($li);
});

echo $list->render();
```

---

## Extending the Markup Class

The `Markup` class can be extended to create your own custom markup libraries with specialized components and behaviors. This allows you to:

- **Create domain-specific markup libraries** for your application or framework
- **Encapsulate common patterns** and components in reusable classes
- **Add custom methods** specific to your use case
- **Pre-configure default behaviors** for your components
- **Build component libraries** with consistent styling and structure

### Basic Extension Example

```php
namespace MyApp\Components;

use MaxPertici\Markup\Markup;

class Button extends Markup
{
    public function __construct(string $text = '', string $type = 'button')
    {
        parent::__construct(
            wrapper: '<button class="%classes%" %attributes%>%children%</button>',
            wrapperClass: ['btn'],
            children: [$text]
        );
        
        $this->setAttribute('type', $type);
    }
    
    public function primary(): self
    {
        return $this->addClass('btn-primary');
    }
    
    public function secondary(): self
    {
        return $this->addClass('btn-secondary');
    }
    
    public function large(): self
    {
        return $this->addClass('btn-lg');
    }
    
    public function small(): self
    {
        return $this->addClass('btn-sm');
    }
}

// Usage
$btn = new Button('Click me');
$btn->primary()->large();
echo $btn->render();
// Output: <button class="btn btn-primary btn-lg" type="button">Click me</button>
```

### Advanced Component Library Example

```php
namespace MyApp\Components;

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupSlot;

class Card extends Markup
{
    public function __construct()
    {
        parent::__construct(
            wrapper: '<div class="%classes%" %attributes%>%children%</div>',
            wrapperClass: ['card']
        );
        
        // Define slots for structured content
        $this->children(
            new MarkupSlot('header', '<div class="card-header">%slot%</div>'),
            new MarkupSlot('body', '<div class="card-body">%slot%</div>'),
            new MarkupSlot('footer', '<div class="card-footer">%slot%</div>')
        );
    }
    
    public function withShadow(): self
    {
        return $this->addClass('shadow-lg');
    }
    
    public function bordered(): self
    {
        return $this->addClass('border');
    }
    
    public function setHeader(mixed ...$content): self
    {
        return $this->slot('header', $content);
    }
    
    public function setBody(mixed ...$content): self
    {
        return $this->slot('body', $content);
    }
    
    public function setFooter(mixed ...$content): self
    {
        return $this->slot('footer', $content);
    }
}

// Usage
$card = new Card();
$card->withShadow()
     ->bordered()
     ->setHeader('<h3>Card Title</h3>')
     ->setBody('<p>Card content goes here</p>')
     ->setFooter('<button class="btn">Action</button>');

echo $card->render();
```

### Framework Integration Example

```php
namespace MyFramework\UI;

use MaxPertici\Markup\Markup;

abstract class Component extends Markup
{
    protected array $props = [];
    
    public function __construct(array $props = [])
    {
        $this->props = $props;
        $this->init();
        parent::__construct();
        $this->build();
    }
    
    /**
     * Initialize component properties
     */
    protected function init(): void
    {
        // Override in child classes
    }
    
    /**
     * Build the component structure
     */
    abstract protected function build(): void;
    
    /**
     * Get a prop value with default
     */
    protected function prop(string $key, mixed $default = null): mixed
    {
        return $this->props[$key] ?? $default;
    }
}

// Example component using the framework
class Alert extends Component
{
    protected function init(): void
    {
        $this->wrapper = '<div class="%classes%" %attributes%>%children%</div>';
        $this->wrapperClass = ['alert'];
    }
    
    protected function build(): void
    {
        $type = $this->prop('type', 'info');
        $message = $this->prop('message', '');
        $dismissible = $this->prop('dismissible', false);
        
        $this->addClass("alert-{$type}")
             ->setAttribute('role', 'alert')
             ->children($message);
        
        if ($dismissible) {
            $this->addClass('alert-dismissible');
            $closeBtn = new Markup(
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
            );
            $this->children($closeBtn);
        }
    }
}

// Usage
$alert = new Alert([
    'type' => 'warning',
    'message' => 'This is a warning message',
    'dismissible' => true
]);
echo $alert->render();
```

### Benefits of Extending Markup

1. **Type Safety**: Your IDE can provide autocompletion for custom methods
2. **Consistency**: Enforce consistent patterns across your application
3. **Reusability**: Create once, use everywhere
4. **Maintainability**: Changes to component structure happen in one place
5. **Documentation**: Self-documenting component APIs
6. **Testing**: Easier to test isolated component behavior

---

## Performance Notes

### Buffer vs Streaming Mode

```php
// Buffer (render) - Keeps everything in memory
$html = $markup->render(); // ~5MB in memory
echo $html;

// Streaming (print) - Direct output
$markup->print(); // ~0.5MB in memory
```

**Recommendations:**
- Use `render()` for small documents or when you need to manipulate the HTML
- Use `print()` for large documents or full pages
- `print()` is approximately 2x faster for documents over 100KB

### Children Optimization

```php
// ❌ Less performant - creates many objects
$list->children(
    new Markup('<li>%children%</li>', children: ['Item 1']),
    new Markup('<li>%children%</li>', children: ['Item 2']),
    new Markup('<li>%children%</li>', children: ['Item 3'])
);

// ✅ More performant - uses childrenWrapper
$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    childrenWrapper: '<li>%child%</li>',
    children: ['Item 1', 'Item 2', 'Item 3']
);
```

---

## See Also

- [MarkupFactory](markup-factory.html) - Quick creation methods
- [MarkupSlot](markup-slot.html) - Slot system
- [MarkupFinder](markup-finder.html) - Direct search methods
- [MarkupQueryBuilder](markup-query-builder.html) - Fluent query builder
- [MarkupCollection](markup-collection.html) - Collection methods
- [Getting Started](getting-started.html) - Introduction and first steps
