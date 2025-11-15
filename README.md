# Markup

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.0-blue)](https://www.php.net/)

> **⚠️ Disclaimer:** This README was written by an AI, but it has been *almost* reviewed by a human.

A flexible and intuitive PHP library for building HTML markup structures using a fluent, chainable API. Create reusable components with slots, manage CSS classes and attributes, and render HTML efficiently.

## Introduction

**Markup** provides a modern, object-oriented approach to generating HTML in PHP. Instead of mixing HTML strings or using complex templating engines, Markup offers:

- **Fluent Interface**: Chain methods for clean, readable code
- **Slot System**: Like Vue.js or Laravel Blade components
- **Type Safety**: Leverage PHP 8+ type system
- **Dual Rendering Modes**: Buffer content or stream directly for performance
- **No Dependencies**: Pure PHP, easy to integrate anywhere

Perfect for building reusable UI components, generating dynamic HTML, or creating a component library for your PHP applications.

## Features

- ✨ **Fluent API** - Chain methods for intuitive markup building
- 🎯 **Slot System** - Named placeholders for flexible content injection
- 🎨 **CSS Class Management** - Add, remove, check classes easily
- ⚙️ **HTML Attributes** - Full control over element attributes
- 🔄 **Nested Components** - Compose complex structures from simple parts
- 🚀 **Dual Rendering** - `render()` to return string, `print()` for streaming
- 🔁 **Conditional & Loop Helpers** - `when()` and `each()` for dynamic content
- 📦 **Zero Dependencies** - Pure PHP, no external requirements

## Requirements

- PHP 8.0 or higher

## Installation

Install via Composer:

```bash
composer require maxpertici/markup
```

The package uses PSR-4 autoloading:

```php
<?php
require 'vendor/autoload.php';

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\Slot;
```

## Basic Usage

### Simple Example

Create a basic HTML element:

```php
use MaxPertici\Markup\Markup;

$paragraph = new Markup('<p>%children%</p>');
$paragraph->children('Hello, World!');

echo $paragraph->render();
// Output: <p>Hello, World!</p>
```

### Render vs Print

Two ways to output your markup:

```php
// render() - Returns HTML as a string
$html = $markup->render();
echo $html;

// print() - Directly outputs HTML (streaming, better performance)
$markup->print();
```

### Wrappers and Children

The `%children%` placeholder is where child elements will be rendered:

```php
$card = new Markup(wrapper: '<div class="card">%children%</div>');
$card->children(
    new Markup(wrapper: '<h2>%children%</h2>', children: ['Card Title']),
    new Markup(wrapper: '<p>%children%</p>', children: ['Card content goes here.'])
);

echo $card->render();
```

**Output:**
```html
<div class="card">
    <h2>Card Title</h2>
    <p>Card content goes here.</p>
</div>
```

### Managing CSS Classes

```php
$button = new Markup('<button class="%classes%">%children%</button>');
$button->addClass('btn', 'btn-primary')
       ->children('Click Me');

echo $button->render();
// Output: <button class="btn btn-primary">Click Me</button>

// Remove a class
$button->removeClass('btn-primary')
       ->addClass('btn-secondary');

// Check if class exists
if ($button->hasClass('btn')) {
    // true
}

// Get all classes
$classes = $button->classes(); // ['btn', 'btn-secondary']
```

### Managing HTML Attributes

```php
$link = new Markup('<a %attributes%>%children%</a>');
$link->setAttribute('href', 'https://example.com')
     ->setAttribute('target', '_blank')
     ->setAttribute('rel', 'noopener')
     ->children('Visit Example');

echo $link->render();
// Output: <a href="https://example.com" target="_blank" rel="noopener">Visit Example</a>

// Get attribute value
$href = $link->getAttribute('href'); // 'https://example.com'

// Check if attribute exists
if ($link->hasAttribute('target')) {
    // true
}

// Remove attribute
$link->removeAttribute('target');
```

## Advanced Features

### Slot System

Slots allow you to define named placeholders in your components, similar to Vue.js or Laravel Blade:

#### Declaring Slots

```php
use MaxPertici\Markup\Slot;

$layout = new Markup(wrapper: '<div class="layout">%children%</div>');
$layout->children(
    new Slot(name: 'header', wrapper: '<header>%slot%</header>', description: 'Page header content'),
    new Markup(
        wrapper: '<main class="content">%children%</main>',
        children: [
            new Slot(name: 'content', description: 'Main page content')
        ]
    ),
    new Slot(name: 'footer', wrapper: '<footer>%slot%</footer>', description: 'Page footer content')
);
```

#### Filling Slots

```php
$layout->slot('header', [
    new Markup(wrapper: '<h1>%children%</h1>', children: ['My Website'])
]);

$layout->slot('content', [
    new Markup(wrapper: '<p>%children%</p>', children: ['Welcome to my website!'])
]);

$layout->slot('footer', [
    '<p>&copy; 2024 My Website</p>'
]);

echo $layout->render();
```

**Output:**
```html
<div class="layout">
    <header>
        <h1>My Website</h1>
    </header>
    <main class="content">
        <p>Welcome to my website!</p>
    </main>
    <footer>
        <p>&copy; 2024 My Website</p>
    </footer>
</div>
```

#### Slot Information

```php
// Check if slot exists
if ($layout->hasSlot('header')) {
    // true
}

// Check if slot is filled
if ($layout->isSlotFilled('header')) {
    // true
}

// Get all slot names
$names = $layout->slotNames(); // ['header', 'content', 'footer']

// Get filled slot names
$filled = $layout->filledSlotNames(); // ['header', 'content', 'footer']

// Get slot information
$info = $layout->getSlotsInfo();
/*
[
    'header' => [
        'name' => 'header',
        'description' => 'Page header content',
        'wrapper' => '<header>%slot%</header>',
        'preserve' => false,
        'filled' => true,
        'items_count' => 1
    ],
    ...
]
*/
```

#### Preserved Slots

Preserve the wrapper even when a slot is empty:

```php
$slot = new Slot(name: 'sidebar', wrapper: '<aside class="sidebar">%slot%</aside>');
$slot->preserve(); // Wrapper will render even if empty

$layout->children($slot);
echo $layout->render();
// Output includes: <aside class="sidebar"></aside>
```

### Conditional Methods

Use `when()` to conditionally add content:

```php
$card = new Markup(wrapper: '<div class="card">%children%</div>');

$isAdmin = true;
$card->children('Regular content')
     ->when($isAdmin, function($markup) {
         $markup->children(
             new Markup(
                 wrapper: '<div class="admin-tools">%children%</div>',
                 children: ['Admin Only Content']
             )
         );
     });

echo $card->render();
```

### Iterative Methods

Use `each()` to loop through data:

```php
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
    ['name' => 'Charlie', 'email' => 'charlie@example.com']
];

$list = new Markup(wrapper: '<ul class="user-list">%children%</ul>');
$list->each($users, function($user, $index, $markup) {
    $markup->children(
        new Markup(
            wrapper: '<li>%children%</li>',
            children: [$user['name'] . ' - ' . $user['email']]
        )
    );
});

echo $list->render();
```

**Output:**
```html
<ul class="user-list">
    <li>Alice - alice@example.com</li>
    <li>Bob - bob@example.com</li>
    <li>Charlie - charlie@example.com</li>
</ul>
```

### Manipulating Children

```php
// Get children
$children = $markup->getChildren();

// Replace all children
$markup->setChildren([
    'New child 1',
    new Markup(wrapper: '<span>%children%</span>', children: ['New child 2'])
]);

// Reorder children
$markup->orderChildren(function($children) {
    return array_reverse($children);
});
```

### Children Wrapper

Wrap each child individually using the `%child%` placeholder:

```php
$list = new Markup(
    wrapper: '<ul class="grid">%children%</ul>',
    children_wrapper: '<li class="grid-item">%child%</li>',
    children: ['Item 1', 'Item 2', 'Item 3']
);

echo $list->render();
```

**Output:**
```html
<ul class="grid">
    <li class="grid-item">Item 1</li>
    <li class="grid-item">Item 2</li>
    <li class="grid-item">Item 3</li>
</ul>
```

### Metadata

Add identification and description to markup instances:

```php
$component = new Markup('<div>%children%</div>');
$component->slug('hero-section')
          ->description('Main hero section with CTA');

// Retrieve metadata
$slug = $component->slug(); // 'hero-section'
$desc = $component->description(); // 'Main hero section with CTA'
```

## Practical Examples

### Card Component

```php
function createCard($title, $content, $imageUrl = null) {
    $card = new Markup(wrapper: '<div class="card">%children%</div>');
    $card->addClass('shadow-lg', 'rounded');
    
    if ($imageUrl) {
        $card->children(
            (new Markup(wrapper: '<img %attributes%/>'))
                ->setAttribute('src', $imageUrl)
                ->setAttribute('alt', $title)
                ->addClass('card-img-top')
        );
    }
    
    $cardBody = new Markup(wrapper: '<div class="card-body">%children%</div>');
    $cardBody->children(
        new Markup(wrapper: '<h5 class="card-title">%children%</h5>', children: [$title]),
        new Markup(wrapper: '<p class="card-text">%children%</p>', children: [$content])
    );
    
    $card->children($cardBody);
    
    return $card;
}

$myCard = createCard(
    title: 'Beautiful Sunset',
    content: 'Witness the most amazing sunset views.',
    imageUrl: '/images/sunset.jpg'
);

echo $myCard->render();
```

### Navigation Menu

```php
$menuItems = [
    ['label' => 'Home', 'url' => '/', 'active' => true],
    ['label' => 'About', 'url' => '/about', 'active' => false],
    ['label' => 'Services', 'url' => '/services', 'active' => false],
    ['label' => 'Contact', 'url' => '/contact', 'active' => false],
];

$nav = new Markup(wrapper: '<nav class="navbar">%children%</nav>');
$ul = new Markup(wrapper: '<ul class="nav-list">%children%</ul>');

$ul->each($menuItems, function($item, $index, $markup) {
    $li = new Markup(wrapper: '<li class="%classes%">%children%</li>');
    $li->when($item['active'], fn($m) => $m->addClass('active'));
    
    $link = new Markup(wrapper: '<a %attributes%>%children%</a>');
    $link->setAttribute('href', $item['url'])
         ->children($item['label']);
    
    $li->children($link);
    $markup->children($li);
});

$nav->children($ul);
echo $nav->render();
```

**Output:**
```html
<nav class="navbar">
    <ul class="nav-list">
        <li class="active"><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/services">Services</a></li>
        <li><a href="/contact">Contact</a></li>
    </ul>
</nav>
```

### Layout with Multiple Slots

```php
// Define layout component
$layout = new Markup(wrapper: '<div class="page-wrapper">%children%</div>');
$layout->children(
    new Slot(name: 'alerts', wrapper: '<div class="alerts-container">%slot%</div>'),
    new Slot(name: 'sidebar', wrapper: '<aside class="sidebar">%slot%</aside>'),
    new Markup(
        wrapper: '<main class="main-content">%children%</main>',
        children: [
            new Slot(name: 'breadcrumbs', wrapper: '<nav class="breadcrumbs">%slot%</nav>'),
            new Slot(name: 'content', wrapper: '<div class="content">%slot%</div>'),
            new Slot(name: 'actions', wrapper: '<div class="actions">%slot%</div>')
        ]
    )
);

// Fill the slots
$layout->slot('alerts', [
    '<div class="alert alert-success">Welcome back!</div>'
]);

$layout->slot('sidebar', [
    new Markup(
        wrapper: '<ul>%children%</ul>',
        children: [
            '<li>Dashboard</li>',
            '<li>Profile</li>',
            '<li>Settings</li>'
        ]
    )
]);

$layout->slot('breadcrumbs', [
    '<a href="/">Home</a> / <span>Dashboard</span>'
]);

$layout->slot('content', [
    new Markup(wrapper: '<h1>%children%</h1>', children: ['Dashboard']),
    new Markup(wrapper: '<p>%children%</p>', children: ['Welcome to your dashboard!'])
]);

$layout->slot('actions', [
    '<button class="btn btn-primary">New Item</button>'
]);

$layout->print();
```

### Form Component

```php
$form = new Markup(wrapper: '<form %attributes%>%children%</form>');
$form->setAttribute('method', 'post')
     ->setAttribute('action', '/submit');

$fields = [
    ['type' => 'text', 'name' => 'username', 'label' => 'Username', 'required' => true],
    ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
    ['type' => 'password', 'name' => 'password', 'label' => 'Password', 'required' => true],
];

$form->each($fields, function($field, $index, $markup) {
    $group = new Markup(wrapper: '<div class="form-group">%children%</div>');
    
    $label = new Markup(wrapper: '<label %attributes%>%children%</label>');
    $label->setAttribute('for', $field['name'])
          ->children($field['label']);
    
    $input = new Markup(wrapper: '<input %attributes%/>');
    $input->setAttribute('type', $field['type'])
          ->setAttribute('name', $field['name'])
          ->setAttribute('id', $field['name'])
          ->addClass('form-control')
          ->when($field['required'], fn($m) => $m->setAttribute('required', 'required'));
    
    $group->children($label, $input);
    $markup->children($group);
});

$submit = new Markup(wrapper: '<button %attributes%>%children%</button>');
$submit->setAttribute('type', 'submit')
       ->addClass('btn', 'btn-primary')
       ->children('Submit');

$form->children($submit);
echo $form->render();
```

### Data Table

```php
$data = [
    ['id' => 1, 'name' => 'Product A', 'price' => '$19.99'],
    ['id' => 2, 'name' => 'Product B', 'price' => '$29.99'],
    ['id' => 3, 'name' => 'Product C', 'price' => '$39.99'],
];

$table = new Markup(wrapper: '<table class="table">%children%</table>');

// Header
$thead = new Markup(wrapper: '<thead>%children%</thead>');
$headerRow = new Markup(wrapper: '<tr>%children%</tr>');
$headerRow->children(
    '<th>ID</th>',
    '<th>Name</th>',
    '<th>Price</th>'
);
$thead->children($headerRow);

// Body
$tbody = new Markup(wrapper: '<tbody>%children%</tbody>');
$tbody->each($data, function($row, $index, $markup) {
    $tr = new Markup(wrapper: '<tr>%children%</tr>');
    $tr->children(
        new Markup(wrapper: '<td>%children%</td>', children: [$row['id']]),
        new Markup(wrapper: '<td>%children%</td>', children: [$row['name']]),
        new Markup(wrapper: '<td>%children%</td>', children: [$row['price']])
    );
    $markup->children($tr);
});

$table->children($thead, $tbody);
echo $table->render();
```

## Rendering Modes

### Buffer Mode (`render()`)

Returns HTML as a string. Use when you need to:
- Store HTML in a variable
- Manipulate HTML before output
- Test or validate HTML
- Return HTML from functions

```php
$markup = new Markup(wrapper: '<div>%children%</div>', children: ['Content']);
$html = $markup->render();

// Now you can manipulate $html
$html = str_replace('Content', 'Modified Content', $html);
echo $html;
```

**Advantages:**
- Full control over output
- Easy to test
- Can be stored or passed around

### Streaming Mode (`print()`)

Directly outputs HTML. Use when you need:
- Better performance for large HTML
- Reduced memory usage
- Direct output in templates

```php
$markup = new Markup(wrapper: '<div>%children%</div>', children: ['Content']);
$markup->print(); // Immediately outputs to browser
```

**Advantages:**
- Lower memory footprint
- Faster for large documents
- Immediate output (better for streaming responses)

## API Reference

### Markup Class

#### Constructor

```php
public function __construct(
    string $wrapper = '',
    array $wrapper_class = [],
    array $wrapper_attributes = [],
    string $children_wrapper = '',
    array $children = [],
    string $path = ''
)
```

**Parameters:**
- `$wrapper` - HTML template with `%children%` placeholder
- `$wrapper_class` - Array of CSS classes
- `$wrapper_attributes` - Associative array of HTML attributes
- `$children_wrapper` - HTML template with `%child%` placeholder (wraps each child)
- `$children` - Array of initial children
- `$path` - Internal data tree path (rarely used directly)

#### Metadata Methods

```php
slug(?string $slug = null): self|string|null
description(?string $description = null): self|string|null
```

#### CSS Class Methods

```php
addClass(string|array ...$classes): self
removeClass(string|array ...$classes): self
hasClass(string $class): bool
classes(?array $classes = null): self|array
```

#### HTML Attribute Methods

```php
setAttribute(string $name, ?string $value): self
removeAttribute(string $name): self
hasAttribute(string $name): bool
getAttribute(string $name): ?string
attributes(?array $attributes = null): self|array
```

#### Children Methods

```php
children(mixed ...$children): self
getChildren(): array
setChildren(array $children): self
orderChildren(callable $callback): self
```

#### Slot Methods

```php
slot(string $name, mixed $items): self
slots(?array $names = null): array
getSlot(string $name): ?Slot
slotNames(): array
filledSlotNames(): array
hasSlot(string $name): bool
isSlotFilled(string $name): bool
getSlotsInfo(): array
```

#### Conditional & Loop Methods

```php
when(bool $condition, callable $callback): self
each(array $items, callable $callback): self
```

#### Rendering Methods

```php
render(): string
print(): void
```

### Slot Class

#### Constructor

```php
public function __construct(
    string $name,
    string $wrapper = '',
    string $description = ''
)
```

**Parameters:**
- `$name` - Slot identifier
- `$wrapper` - HTML template with `%slot%` placeholder
- `$description` - Human-readable description

#### Methods

```php
name(?string $name = null): string|self
description(?string $description = null): string|self
wrapper(?string $wrapper = null): string|self
preserve(bool $preserve = true): self
isPreserved(): bool
toArray(): array
```

### MarkupInterface

Interface that all markup classes must implement:

```php
interface MarkupInterface {
    public function render(): string;
    public function print(): void;
}
```

## Best Practices

### Naming Conventions

- **Slots**: Use descriptive kebab-case names: `'main-content'`, `'sidebar-widgets'`
- **CSS Classes**: Follow your project's conventions (BEM, utility classes, etc.)
- **Component Slugs**: Use kebab-case for component identification: `'hero-section'`, `'product-card'`

### Code Organization

Create reusable component functions:

```php
// components/card.php
function card($title, $content, $footer = null) {
    $card = new Markup(wrapper: '<div class="card">%children%</div>');
    $card->children(
        new Slot(name: 'header', wrapper: '<div class="card-header">%slot%</div>'),
        new Slot(name: 'body', wrapper: '<div class="card-body">%slot%</div>'),
        new Slot(name: 'footer', wrapper: '<div class="card-footer">%slot%</div>')
    );
    
    $card->slot('header', $title);
    $card->slot('body', $content);
    
    if ($footer) {
        $card->slot('footer', $footer);
    }
    
    return $card;
}
```

### Component Reusability

Build a component library:

```php
// components/button.php
function button($text, $type = 'primary', $size = 'md') {
    $btn = new Markup(wrapper: '<button class="%classes%">%children%</button>');
    $btn->addClass('btn', "btn-{$type}", "btn-{$size}")
        ->children($text);
    return $btn;
}

// Usage
echo button(text: 'Save', type: 'success', size: 'lg')->render();
```

### Performance Tips

1. **Use `print()` for large documents**: Reduces memory usage
2. **Avoid deep nesting**: Keep component hierarchies shallow when possible
3. **Cache rendered components**: Store frequently used HTML strings
4. **Use children_wrapper efficiently**: Better than manually wrapping each child

```php
// ❌ Less efficient
$list->children(
    new Markup(wrapper: '<li>%children%</li>', children: ['Item 1']),
    new Markup(wrapper: '<li>%children%</li>', children: ['Item 2']),
    new Markup(wrapper: '<li>%children%</li>', children: ['Item 3'])
);

// ✅ More efficient
$list = new Markup(
    wrapper: '<ul>%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: ['Item 1', 'Item 2', 'Item 3']
);
```

### Type Safety

Leverage PHP 8 types for component functions:

```php
function alert(string $message, string $type = 'info'): Markup {
    $alert = new Markup(wrapper: '<div class="%classes%" role="alert">%children%</div>');
    $alert->addClass('alert', "alert-{$type}")
          ->children($message);
    return $alert;
}
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

**Author:** Max Pertici  
**Email:** hello@maxpertici.fr  
**Website:** [maxpertici.fr](https://maxpertici.fr)

## Support

- **Issues**: [GitHub Issues](https://github.com/maxpertici/markup/issues)
- **Email**: hello@maxpertici.fr

---

Made with ❤️ by [Max Pertici](https://maxpertici.fr)

