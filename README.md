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
- 🔍 **Search & Find** - Query your markup tree by tag, class, attribute, or custom logic
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
use MaxPertici\Markup\MarkupSlot;
use MaxPertici\Markup\MarkupFactory;
```

## Basic Usage

### Factory Methods (Quick Creation)

**New in v1.1.0**: Create Markup instances quickly using the `MarkupFactory` class.

#### `MarkupFactory::fromString()` - Simple Element Creation

Create a wrapper element with content, classes, and attributes in one line:

```php
use MaxPertici\Markup\MarkupFactory;

// Create a simple div
$div = MarkupFactory::fromString(
    'div',                                    // tag name
    'Hello World!',                           // content (string only)
    ['container', 'text-center'],             // CSS classes
    ['id' => 'main', 'data-test' => 'value'] // HTML attributes
);

echo $div->render();
// Output: <div class="container text-center" id="main" data-test="value">Hello World!</div>

// Still fully chainable
$paragraph = MarkupFactory::fromString('p', 'Initial content', ['paragraph'])
    ->addClass('text-bold')
    ->setAttribute('role', 'note');
```

**Use case**: Perfect for quick element creation when you need a simple wrapper with a single text content.

#### `MarkupFactory::fromHtml()` - Parse HTML Tree (Recursive)

Parse existing HTML and convert it into a complete Markup structure:

```php
// Simple HTML parsing
$html = '<div class="card" id="card-1">Card content</div>';
$markup = MarkupFactory::fromHtml($html);

echo $markup->render();
// Output: <div class="card" id="card-1">Card content</div>

// Nested HTML - fully recursive
$complexHtml = '<div class="card">
    <h2 class="title">Card Title</h2>
    <p class="content">This is the card content with <strong>bold text</strong>.</p>
</div>';

$nested = MarkupFactory::fromHtml($complexHtml);
echo $nested->render();
// Preserves entire structure as nested Markup instances

// Still manipulable after parsing
$parsed = MarkupFactory::fromHtml('<div class="box">Content</div>');
$parsed->addClass('new-class')
       ->setAttribute('data-dynamic', 'true')
       ->children(' - Added content');

echo $parsed->render();
// Output: <div class="box new-class" data-dynamic="true">Content - Added content</div>
```

**Use case**: Parse existing HTML templates, convert HTML strings to Markup objects, or migrate from string-based HTML generation.

**Key differences:**
- `MarkupFactory::fromString()`: Creates simple wrapper (first level only) with single string content
- `MarkupFactory::fromHtml()`: Recursively parses entire HTML tree, preserving all nested structure

### Simple Example

Create a basic HTML element using the constructor:

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

### Creating Markup from Strings and HTML

The library provides two factory methods to create Markup instances from strings or HTML:

#### `fromString()` - Simple Wrapper (First Level Only)

Creates a simple wrapper with a tag, classes, attributes, and string content. The content is **not parsed** and remains as a single string child.

```php
$div = Markup::fromString(
    tag: 'div',
    content: 'This is <strong>HTML</strong> that will NOT be parsed',
    classes: ['container', 'text-center'],
    attributes: ['id' => 'main', 'data-type' => 'simple']
);

echo $div->render();
// Output: <div class="container text-center" id="main" data-type="simple">This is <strong>HTML</strong> that will NOT be parsed</div>

// You can still chain methods
$div->addClass('bg-light')
    ->setAttribute('data-enhanced', 'true');
```

**Use `fromString()` when:**
- You need a simple wrapper around string content
- Performance is critical (3x faster than `fromHtml()`)
- You don't need to parse or manipulate the inner HTML structure

#### `fromHtml()` - Recursive Parsing

Parses an HTML string and creates a complete Markup tree with nested Markup instances for each HTML element.

```php
$html = '
<div class="card" id="user-card" data-user-id="123">
    <div class="card-header">
        <h2 class="title">User Profile</h2>
        <span class="badge">Premium</span>
    </div>
    <div class="card-body">
        <p>Welcome back, <strong>John Doe</strong>!</p>
        <a href="/profile" class="btn btn-primary">View Profile</a>
    </div>
</div>
';

$parsed = Markup::fromHtml($html);

// The entire structure is now a Markup tree
// You can manipulate any part of it
$parsed->addClass('shadow-lg')
       ->setAttribute('data-enhanced', 'true');

echo $parsed->render();
```

**Use `fromHtml()` when:**
- You need to parse existing HTML into a Markup structure
- You want to manipulate complex HTML programmatically
- You need access to nested elements as Markup instances
- Converting legacy HTML to Markup components

#### Combining Both Approaches

```php
$container = new Markup('<div class="container">%children%</div>');

// Parse complex HTML
$header = Markup::fromHtml('<header><h1>Title</h1><nav>...</nav></header>');

// Create simple wrapper with string content
$footer = Markup::fromString('footer', '© 2024 My Site', ['site-footer']);

$container->children($header, $footer);
```

#### Important Notes

- `fromString()` is approximately **3x faster** than `fromHtml()`
- `fromHtml()` uses PHP's DOMDocument, which may:
  - Remove some whitespace between elements
  - Convert self-closing tags (e.g., `<img />` becomes `<img></img>`)
  - Add missing closing tags for malformed HTML
- Both methods return chainable Markup instances

### MarkupFactory and Enums

**New in v1.2.0**: Create reusable component definitions using PHP 8.1 enums.

#### The Power of Enums

Instead of manually defining wrapper templates, classes, and attributes every time, you can create enum-based component libraries that are:

- ✅ **Type-safe** - Full IDE autocompletion and type checking
- ✅ **Reusable** - Define once, use everywhere
- ✅ **Extensible** - Create your own enum libraries
- ✅ **Shareable** - Package and distribute via Composer

#### Using `MarkupFactory::fromElement()`

The `fromElement()` method creates Markup instances from any element implementing `MarkupElementInterface`:

```php
use MaxPertici\Markup\MarkupFactory;
use MaxPertici\Markup\Elements\HtmlTag;

// Create from built-in HtmlTag element
$div = MarkupFactory::fromElement(
    HtmlTag::DIV,                          // The element
    ['Hello World'],                        // Children (array)
    ['container', 'text-center'],          // Additional CSS classes
    ['id' => 'main', 'data-role' => 'app'] // Additional attributes
);

echo $div->render();
// Output: <div class="container text-center" id="main" data-role="app">Hello World</div>
```

**Signature:**
```php
MarkupFactory::fromElement(
    MarkupElementInterface $element,  // The element configuration
    array $children = [],              // Children to add
    array $classes = [],               // Additional CSS classes
    array $attributes = []             // Additional HTML attributes
): Markup
```

#### Built-in HtmlTag Enum

The package includes a `HtmlTag` enum for common HTML elements:

```php
use MaxPertici\Markup\Elements\HtmlTag;
use MaxPertici\Markup\MarkupFactory;

// Available tags
HtmlTag::DIV
HtmlTag::SPAN
HtmlTag::P
HtmlTag::SECTION
HtmlTag::ARTICLE
HtmlTag::HEADER
HtmlTag::FOOTER
HtmlTag::MAIN
HtmlTag::ASIDE
HtmlTag::NAV
HtmlTag::UL
HtmlTag::OL
HtmlTag::LI
HtmlTag::H1
HtmlTag::H2
HtmlTag::H3
HtmlTag::H4
HtmlTag::H5
HtmlTag::H6
HtmlTag::BUTTON
HtmlTag::A
HtmlTag::FORM
HtmlTag::LABEL
HtmlTag::INPUT

// Usage examples
$section = MarkupFactory::fromElement(HtmlTag::SECTION, ['Content'], ['main-section']);
$button = MarkupFactory::fromElement(HtmlTag::BUTTON, ['Click me'], ['btn', 'btn-primary']);
$list = MarkupFactory::fromElement(HtmlTag::UL, ['Item 1', 'Item 2', 'Item 3'], ['menu']);
// UL/OL automatically wrap children in <li> tags!

echo $list->render();
// Output: <ul class="menu"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>
```

#### Creating Custom Enums

Create your own component library by implementing `MarkupElementInterface`:

```php
use MaxPertici\Markup\MarkupElementInterface;

/**
 * Bootstrap components enum
 */
enum BootstrapComponent: string implements MarkupElementInterface {
    
    case CARD            = 'card';
    case CARD_HEADER     = 'card-header';
    case CARD_BODY       = 'card-body';
    case CARD_FOOTER     = 'card-footer';
    case ALERT_SUCCESS   = 'alert-success';
    case ALERT_WARNING   = 'alert-warning';
    case ALERT_DANGER    = 'alert-danger';
    case BUTTON_PRIMARY  = 'btn-primary';
    case BUTTON_SECONDARY = 'btn-secondary';

    /**
     * Gets the HTML wrapper template.
     */
    public function wrapper(): string {
        return match ($this) {
            self::CARD, self::CARD_HEADER, self::CARD_BODY, self::CARD_FOOTER
                => '<div class="%classes%" %attributes%>%children%</div>',
            self::ALERT_SUCCESS, self::ALERT_WARNING, self::ALERT_DANGER
                => '<div class="alert %classes%" %attributes% role="alert">%children%</div>',
            self::BUTTON_PRIMARY, self::BUTTON_SECONDARY
                => '<button class="btn %classes%" %attributes%>%children%</button>',
        };
    }

    /**
     * Gets the CSS classes for this element.
     */
    public function classes(): array {
        return match ($this) {
            self::CARD            => ['card'],
            self::CARD_HEADER     => ['card-header'],
            self::CARD_BODY       => ['card-body'],
            self::CARD_FOOTER     => ['card-footer'],
            self::ALERT_SUCCESS   => ['alert-success'],
            self::ALERT_WARNING   => ['alert-warning'],
            self::ALERT_DANGER    => ['alert-danger'],
            self::BUTTON_PRIMARY  => ['btn-primary'],
            self::BUTTON_SECONDARY => ['btn-secondary'],
        };
    }

    /**
     * Gets the HTML attributes for this element.
     */
    public function attributes(): array {
        return match ($this) {
            self::BUTTON_PRIMARY, self::BUTTON_SECONDARY => ['type' => 'button'],
            default => [],
        };
    }

    /**
     * Gets the children wrapper template (optional).
     */
    public function childrenWrapper(): string {
        return ''; // No special wrapper for children
    }
}

// Usage
$card = MarkupFactory::fromElement(BootstrapComponent::CARD, [
    MarkupFactory::fromElement(BootstrapComponent::CARD_HEADER, ['Card Title']),
    MarkupFactory::fromElement(BootstrapComponent::CARD_BODY, ['Card content']),
    MarkupFactory::fromElement(BootstrapComponent::CARD_FOOTER, ['Footer text']),
]);

echo $card->render();
```

**Output:**
```html
<div class="card">
    <div class="card-header">Card Title</div>
    <div class="card-body">Card content</div>
    <div class="card-footer">Footer text</div>
</div>
```

#### Real-World Example: Tailwind CSS Components

```php
enum TailwindComponent implements MarkupElementInterface {
    
    case CONTAINER;
    case CARD;
    case BUTTON_PRIMARY;
    case BUTTON_SECONDARY;
    case BADGE;
    case HERO_SECTION;

    public function wrapper(): string {
        return match ($this) {
            self::CONTAINER, self::CARD, self::HERO_SECTION
                => '<div class="%classes%" %attributes%>%children%</div>',
            self::BUTTON_PRIMARY, self::BUTTON_SECONDARY
                => '<button class="%classes%" %attributes%>%children%</button>',
            self::BADGE
                => '<span class="%classes%" %attributes%>%children%</span>',
        };
    }

    public function classes(): array {
        return match ($this) {
            self::CONTAINER => [
                'container', 'mx-auto', 'px-4'
            ],
            self::CARD => [
                'bg-white', 'rounded-lg', 'shadow-md', 'p-6'
            ],
            self::BUTTON_PRIMARY => [
                'bg-blue-500', 'hover:bg-blue-700', 
                'text-white', 'font-bold', 'py-2', 'px-4', 'rounded'
            ],
            self::BUTTON_SECONDARY => [
                'bg-gray-500', 'hover:bg-gray-700',
                'text-white', 'font-bold', 'py-2', 'px-4', 'rounded'
            ],
            self::BADGE => [
                'inline-block', 'bg-gray-200', 'rounded-full',
                'px-3', 'py-1', 'text-sm', 'font-semibold', 'text-gray-700'
            ],
            self::HERO_SECTION => [
                'bg-gradient-to-r', 'from-blue-500', 'to-purple-600',
                'text-white', 'py-20', 'text-center'
            ],
        };
    }

    public function attributes(): array {
        return match ($this) {
            self::BUTTON_PRIMARY, self::BUTTON_SECONDARY => ['type' => 'button'],
            default => [],
        };
    }

    public function childrenWrapper(): string {
        return '';
    }
}

// Build a page
$page = MarkupFactory::fromElement(TailwindComponent::CONTAINER, [
    MarkupFactory::fromElement(TailwindComponent::HERO_SECTION, [
        '<h1 class="text-5xl font-bold mb-4">Welcome!</h1>',
        '<p class="text-xl mb-8">Start building amazing things</p>',
        MarkupFactory::fromElement(TailwindComponent::BUTTON_PRIMARY, ['Get Started']),
    ]),
    MarkupFactory::fromElement(TailwindComponent::CARD, [
        '<h2 class="text-2xl font-bold mb-4">Features</h2>',
        '<p>Discover what makes us unique...</p>',
        MarkupFactory::fromElement(TailwindComponent::BADGE, ['New']),
    ]),
]);

echo $page->render();
```

#### WordPress Admin Components Example

```php
enum WpAdminComponent implements MarkupElementInterface {
    
    case NOTICE_SUCCESS;
    case NOTICE_ERROR;
    case NOTICE_WARNING;
    case NOTICE_INFO;
    case METABOX;
    case WRAP;
    case FORM_TABLE;

    public function wrapper(): string {
        return match ($this) {
            self::NOTICE_SUCCESS, self::NOTICE_ERROR, 
            self::NOTICE_WARNING, self::NOTICE_INFO
                => '<div class="notice %classes%" %attributes%><p>%children%</p></div>',
            self::METABOX
                => '<div class="postbox %classes%" %attributes%><div class="inside">%children%</div></div>',
            self::WRAP
                => '<div class="wrap %classes%" %attributes%>%children%</div>',
            self::FORM_TABLE
                => '<table class="form-table %classes%" %attributes%><tbody>%children%</tbody></table>',
        };
    }

    public function classes(): array {
        return match ($this) {
            self::NOTICE_SUCCESS => ['notice-success'],
            self::NOTICE_ERROR   => ['notice-error'],
            self::NOTICE_WARNING => ['notice-warning'],
            self::NOTICE_INFO    => ['notice-info'],
            default              => [],
        };
    }

    public function attributes(): array {
        return [];
    }

    public function childrenWrapper(): string {
        return match ($this) {
            self::FORM_TABLE => '<tr><th scope="row">%child%</th><td>%child%</td></tr>',
            default          => '',
        };
    }
}

// Usage in WordPress
$notice = MarkupFactory::fromElement(
    WpAdminComponent::NOTICE_SUCCESS,
    ['Settings saved successfully!'],
    ['is-dismissible']
);

echo $notice->render();
// Output: <div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>
```

#### Key Benefits of Enum-Based Components

1. **IDE Autocompletion**: Your IDE knows all available components
```php
// Type "BootstrapComponent::" and see all options!
MarkupFactory::fromElement(BootstrapComponent::CARD, ...);
```

2. **Type Safety**: Catch errors at compile time
```php
// This will error if CARDS doesn't exist (typo)
MarkupFactory::fromElement(BootstrapComponent::CARDS, ...); // ❌ Error
```

3. **Centralized Configuration**: Change once, apply everywhere
```php
// Update BUTTON_PRIMARY classes in one place
// All buttons using this element are updated automatically
```

4. **Shareable Libraries**: Package your enums
```php
// my-company/bootstrap-components
composer require my-company/bootstrap-components

use MyCompany\BootstrapComponents\Component;
$card = MarkupFactory::fromElement(Component::CARD, ...);
```

5. **Framework-Agnostic**: Works with any CSS framework
```php
// Bootstrap, Tailwind, Bulma, Foundation, Material UI...
// Create enums for any framework you use!
```

#### The Four Required Methods

Every enum implementing `MarkupElementInterface` must implement these methods:

```php
public function wrapper(): string
    // Returns the HTML template with placeholders:
    // %children% - where child elements go
    // %classes% - where CSS classes are inserted
    // %attributes% - where HTML attributes go

public function classes(): array
    // Returns array of CSS class names for this element
    // These are merged with additional classes passed to fromElement()

public function attributes(): array
    // Returns array of HTML attributes (key => value)
    // These are merged with additional attributes passed to fromElement()

public function childrenWrapper(): string
    // Optional wrapper for each child element
    // Use %child% placeholder
    // Return empty string if not needed
```

#### Tips for Creating Enums

1. **Use String-Backed Enums** for serialization/debugging:
```php
enum MyComponents: string implements MarkupElementInterface {
    case CARD = 'card';  // ✅ Can be serialized
```

2. **Group Related Components** in one enum:
```php
enum AlertComponent {
    case SUCCESS;
    case WARNING;
    case ERROR;
    case INFO;
}
```

3. **Use Match Expressions** for concise definitions:
```php
public function wrapper(): string {
    return match ($this) {
        self::BUTTON, self::LINK => '<a class="%classes%" %attributes%>%children%</a>',
        default => '<div class="%classes%" %attributes%>%children%</div>',
    };
}
```

4. **Document Your Enums** with PHPDoc:
```php
/**
 * Bootstrap Alert Components
 * 
 * @see https://getbootstrap.com/docs/5.3/components/alerts/
 */
enum AlertComponent implements MarkupElementInterface {
    /** Success alert with green styling */
    case SUCCESS;
    // ...
}
```

### MarkupSlot System

Slots allow you to define named placeholders in your components, similar to Vue.js or Laravel Blade:

#### Declaring Slots

```php
use MaxPertici\Markup\MarkupSlot;

$layout = new Markup(wrapper: '<div class="layout">%children%</div>');
$layout->children(
    new MarkupSlot(name: 'header', wrapper: '<header>%slot%</header>', description: 'Page header content'),
    new Markup(
        wrapper: '<main class="content">%children%</main>',
        children: [
            new MarkupSlot(name: 'content', description: 'Main page content')
        ]
    ),
    new MarkupSlot(name: 'footer', wrapper: '<footer>%slot%</footer>', description: 'Page footer content')
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

#### MarkupSlot Information

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
$slot = new MarkupSlot(name: 'sidebar', wrapper: '<aside class="sidebar">%slot%</aside>');
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

### Searching and Finding Elements (MarkupFinder)

**New in v1.3.0**: Search through your Markup tree to find specific elements.

The `MarkupFinder` class allows you to search for Markup elements based on various criteria, similar to DOM querying in JavaScript. This is perfect for:

- Finding elements to modify programmatically
- Testing and validation
- Debugging complex structures
- Dynamic content manipulation

#### Basic Search

```php
use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupFactory;
use MaxPertici\Markup\Elements\HtmlTag;

// Build a structure
$page = MarkupFactory::fromElement(HtmlTag::DIV)
    ->addClass('page', 'container')
    ->children(
        MarkupFactory::fromElement(HtmlTag::HEADER)
            ->addClass('header')
            ->slug('main-header'),
        MarkupFactory::fromElement(HtmlTag::MAIN)
            ->addClass('content')
            ->setAttribute('role', 'main')
            ->children(
                MarkupFactory::fromElement(HtmlTag::P)->addClass('intro'),
                MarkupFactory::fromElement(HtmlTag::P)->addClass('highlight')
            )
    );

// Get the finder instance
$finder = $page->find();
```

#### Find by Class

```php
// Find all elements with a specific class
$headers = $page->find()->findByClass('header');

// Find all elements with class 'highlight'
$highlighted = $page->find()->findByClass('highlight');
```

#### Find by Tag

```php
// Find all <div> elements
$divs = $page->find()->findByTag('div');

// Find all <p> elements
$paragraphs = $page->find()->findByTag('p');
```

#### Find by Slug

```php
// Find element by slug identifier
$header = $page->find()->findBySlug('main-header');

if (!empty($header)) {
    // Modify the found element
    $header[0]->addClass('sticky');
}
```

#### Find by Attribute

```php
// Find all elements with a specific attribute
$elementsWithRole = $page->find()->findByAttribute('role');

// Find elements with specific attribute value
$mainElements = $page->find()->findByAttribute('role', 'main');
```

#### Find by Multiple Classes

```php
// Find elements that have ALL specified classes
$specialSections = $page->find()->findByClasses(['section', 'featured']);
```

#### Custom Search with Callback

```php
// Find elements using a custom callback
$results = $page->find()->search(function($markup) {
    // Find elements with class 'active' AND attribute 'data-id'
    return $markup->hasClass('active') && $markup->hasAttribute('data-id');
});

// Find all buttons
$buttons = $page->find()->search(function($markup) {
    // Check if wrapper contains <button tag
    $reflection = new \ReflectionClass($markup);
    $property = $reflection->getProperty('wrapper');
    $property->setAccessible(true);
    $wrapper = $property->getValue($markup);
    
    return preg_match('/^<button/', $wrapper);
});
```

#### Find First Match

```php
// Find only the first element that matches
$firstNav = $page->find()->findFirst(function($markup) {
    return $markup->hasClass('nav');
});

if (null !== $firstNav) {
    // Found! Do something with it
    $firstNav->addClass('primary-nav');
}
```

#### Count Matching Elements

```php
// Count elements that match a condition
$count = $page->find()->count(function($markup) {
    return $markup->hasClass('card');
});

echo "Found {$count} cards";
```

#### Get All Elements

```php
// Get all Markup instances in the tree (flattened)
$allElements = $page->find()->all();

echo "Total elements: " . count($allElements);
```

#### Shallow vs Deep Search

By default, searches are recursive (deep). You can limit searches to direct children only:

```php
// Deep search (default) - searches entire tree
$sections = $page->find()->findByTag('section', true);

// Shallow search - only direct children
$sections = $page->find()->findByTag('section', false);
```

#### Practical Example: Modifying Found Elements

```php
// Build a navigation menu
$nav = MarkupFactory::fromElement(HtmlTag::NAV)
    ->addClass('menu')
    ->children(
        MarkupFactory::fromElement(HtmlTag::UL)->children(
            MarkupFactory::fromElement(HtmlTag::LI)->children('Home'),
            MarkupFactory::fromElement(HtmlTag::LI)->children('About'),
            MarkupFactory::fromElement(HtmlTag::LI)->children('Contact')
                ->addClass('active')
        )
    );

// Find and modify the active menu item
$activeItems = $nav->find()->findByClass('active');

foreach ($activeItems as $item) {
    $item->setAttribute('aria-current', 'page');
}

// Find all list items and add a class
$listItems = $nav->find()->findByTag('li');

foreach ($listItems as $item) {
    $item->addClass('menu-item');
}

echo $nav->render();
```

#### MarkupFinder API

```php
// All available methods
$finder = $markup->find();

$finder->findByClass(string $class, bool $deep = true): array
$finder->findByTag(string $tag, bool $deep = true): array
$finder->findBySlug(string $slug, bool $deep = true): array
$finder->findByAttribute(string $name, ?string $value = null, bool $deep = true): array
$finder->findByClasses(array $classes, bool $deep = true): array
$finder->search(callable $callback, bool $deep = true): array
$finder->findFirst(callable $callback, bool $deep = true): ?Markup
$finder->count(callable $callback, bool $deep = true): int
$finder->all(bool $deep = true): array
```

**Search Performance Tips:**
- Use shallow search (`$deep = false`) when you know elements are in direct children
- Use `findFirst()` when you only need one element
- Cache search results if you need to use them multiple times
- Be specific with your callbacks to avoid unnecessary traversal

#### CSS Selector Syntax

**New in v1.4.0**: Find elements using CSS selector syntax for a more familiar query interface.

The `css()` method provides a jQuery/querySelectorAll-like syntax for finding elements:

```php
// Simple selectors
$sections = $page->find()->css('.section');           // by class
$divs = $page->find()->css('div');                     // by tag
$hero = $page->find()->css('#hero-section');          // by ID
$withRole = $page->find()->css('[role]');              // by attribute
$main = $page->find()->css('[role="main"]');           // by attribute value

// Combined selectors
$highlighted = $page->find()->css('p.highlight');      // tag + class
$multiple = $page->find()->css('.hero.section');       // multiple classes

// Descendant selector (space) - any level deep
$navLinks = $page->find()->css('nav a');               // all <a> inside <nav>
$activeItems = $page->find()->css('nav li.active');    // active <li> inside <nav>

// Direct child selector (>) - immediate children only
$headerNav = $page->find()->css('.header > nav');      // <nav> direct child of .header
$directP = $page->find()->css('.content > p');         // <p> direct children of .content

// :has() pseudo-class - find parents containing specific children
$liWithLinks = $page->find()->css('li:has(a)');                     // <li> containing <a>
$sectionsWithHighlight = $page->find()->css('section:has(.highlight)'); // sections with .highlight
$divsWithDirectP = $page->find()->css('div:has(> p)');             // divs with direct <p> child

// Complex selectors
$activeLinks = $page->find()->css('header > nav li.active a');      // combine everything
$navWithActive = $page->find()->css('nav:has(li.active) a');        // all links in nav with active items
```

**Supported Selectors:**

| Selector | Description | Example |
|----------|-------------|---------|
| `.class` | Class selector | `.section`, `.btn-primary` |
| `tag` | Tag selector | `div`, `p`, `span` |
| `#id` | ID selector | `#main-content`, `#hero` |
| `[attr]` | Attribute exists | `[role]`, `[data-id]` |
| `[attr="value"]` | Attribute equals | `[role="main"]`, `[type="button"]` |
| `tag.class` | Combined | `p.highlight`, `div.container` |
| `.class1.class2` | Multiple classes | `.btn.btn-primary` |
| `A B` | Descendant | `nav li`, `section p` |
| `A > B` | Direct child | `.header > nav`, `ul > li` |
| `:has(selector)` | Has child | `li:has(a)`, `section:has(.highlight)` |

**Performance Optimization:**

Simple selectors (`.class`, `tag`, `#id`) are automatically optimized to use the existing specialized methods (`findByClass()`, `findByTag()`, etc.) for maximum performance:

```php
// These have identical performance:
$page->find()->css('.section');     // Uses findByClass() internally
$page->find()->findByClass('section');

// Benchmark results (1000 iterations):
// findByClass(): 3.36 ms
// css('.section'): 3.52 ms (only 0.16ms difference!)
```

**Practical Examples:**

```php
// Build a navigation structure
$page = MarkupFactory::fromElement(HtmlTag::DIV)
    ->addClass('page')
    ->children(
        MarkupFactory::fromElement(HtmlTag::HEADER)
            ->addClass('header')
            ->children(
                MarkupFactory::fromElement(HtmlTag::NAV)
                    ->addClass('nav', 'primary')
                    ->children(
                        MarkupFactory::fromElement(HtmlTag::UL)
                            ->children(
                                MarkupFactory::fromElement(HtmlTag::LI)
                                    ->addClass('active')
                                    ->children(
                                        MarkupFactory::fromElement(HtmlTag::A)
                                            ->setAttribute('href', '/')
                                            ->children('Home')
                                    ),
                                MarkupFactory::fromElement(HtmlTag::LI)->children(
                                    MarkupFactory::fromElement(HtmlTag::A)
                                        ->setAttribute('href', '/about')
                                        ->children('About')
                                )
                            )
                    )
            )
    );

// Find all navigation links
$allLinks = $page->find()->css('nav a');
echo "Found " . count($allLinks) . " links\n";

// Find only links in the header's navigation
$headerLinks = $page->find()->css('.header > nav a');

// Find the active menu item
$activeItem = $page->find()->css('li.active');

// Find navigations that have an active item
$navWithActive = $page->find()->css('nav:has(li.active)');

// Modify found elements
foreach ($page->find()->css('nav a') as $link) {
    $link->addClass('nav-link');
}
```

**Comparison: Old API vs CSS Selectors:**

```php
// Old API - verbose but explicit
$sections = $page->find()->findByClass('section');
$mainSections = $page->find()->search(function($m) {
    return $m->hasClass('section') && $m->hasAttribute('role', 'main');
});

// CSS Selectors - concise and familiar
$sections = $page->find()->css('.section');
$mainSections = $page->find()->css('.section[role="main"]');
```

**When to use CSS selectors:**
- ✅ Familiar with CSS/jQuery selectors
- ✅ Need complex descendant/child relationships
- ✅ Want concise, readable queries
- ✅ Working with nested structures

**When to use the original API:**
- ✅ Need maximum performance for simple queries
- ✅ Custom search logic not expressible in CSS
- ✅ Working with slugs (not supported in CSS selectors)
- ✅ Complex callback-based conditions

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
    new MarkupSlot(name: 'alerts', wrapper: '<div class="alerts-container">%slot%</div>'),
    new MarkupSlot(name: 'sidebar', wrapper: '<aside class="sidebar">%slot%</aside>'),
    new Markup(
        wrapper: '<main class="main-content">%children%</main>',
        children: [
            new MarkupSlot(name: 'breadcrumbs', wrapper: '<nav class="breadcrumbs">%slot%</nav>'),
            new MarkupSlot(name: 'content', wrapper: '<div class="content">%slot%</div>'),
            new MarkupSlot(name: 'actions', wrapper: '<div class="actions">%slot%</div>')
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

### MarkupFactory Class

Factory class for creating Markup instances.

#### Static Methods

```php
MarkupFactory::fromString(
    string $tag,
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup

MarkupFactory::fromHtml(string $html, int $max_depth = PHP_INT_MAX): Markup

MarkupFactory::fromElement(
    MarkupElementInterface $element,
    array $children = [],
    array $classes = [],
    array $attributes = []
): Markup
```

**`fromString()`** - Creates a Markup instance with a simple wrapper (first level only). Content is not parsed.

**`fromHtml()`** - Recursively parses HTML and creates a complete Markup tree. Optional `$max_depth` parameter limits parsing depth.

**`fromElement()`** - Creates a Markup instance from a predefined element configuration (implementing `MarkupElementInterface`). The element defines the wrapper template, default classes, and attributes. Additional classes and attributes are merged with the element's defaults.

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

#### MarkupSlot Methods

```php
slot(string $name, mixed $items): self
slots(?array $names = null): array
getSlot(string $name): ?MarkupSlot
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

#### Search & Find Methods

```php
find(): MarkupFinder
```

Creates a `MarkupFinder` instance for searching within the markup tree. See [Searching and Finding Elements](#searching-and-finding-elements-markupfinder) for detailed usage.

#### Rendering Methods

```php
render(): string
print(): void
```

### MarkupSlot Class

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

### MarkupFinder Class

**New in v1.3.0**: Search and query utility for finding Markup elements in a tree.

#### Constructor

```php
public function __construct(Markup $markup)
```

**Parameters:**
- `$markup` - The root Markup instance to search in

**Note:** Usually accessed via `$markup->find()` rather than direct instantiation.

#### Search Methods

```php
css(string $selector): array
```
**New in v1.4.0**: Finds Markup elements using CSS selector syntax. Supports: `.class`, `tag`, `#id`, `[attr]`, `[attr="value"]`, combined selectors, descendant (` `), direct child (`>`), and `:has()` pseudo-class. See [CSS Selector Syntax](#css-selector-syntax) for detailed usage.

```php
findByClass(string $class, bool $deep = true): array
```
Finds all Markup elements that have a specific CSS class.

```php
findByTag(string $tag, bool $deep = true): array
```
Finds all Markup elements with a specific HTML tag (e.g., 'div', 'p', 'span').

```php
findBySlug(string $slug, bool $deep = true): array
```
Finds all Markup elements with a specific slug identifier.

```php
findByAttribute(string $name, ?string $value = null, bool $deep = true): array
```
Finds all Markup elements with a specific attribute. If `$value` is provided, matches only elements where the attribute has that exact value.

```php
findByClasses(array $classes, bool $deep = true): array
```
Finds all Markup elements that have ALL of the specified classes (AND logic).

```php
search(callable $callback, bool $deep = true): array
```
Finds all Markup elements that match a custom callback function. The callback receives a Markup instance and should return `true` for matches.

```php
findFirst(callable $callback, bool $deep = true): ?Markup
```
Finds the first Markup element that matches a callback. Returns `null` if no match found.

```php
count(callable $callback, bool $deep = true): int
```
Counts how many Markup elements match a callback function.

```php
all(bool $deep = true): array
```
Returns all Markup instances in the tree as a flattened array.

**Parameters common to all methods:**
- `$deep` - When `true` (default), searches recursively through the entire tree. When `false`, only searches direct children.

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
        new MarkupSlot(name: 'header', wrapper: '<div class="card-header">%slot%</div>'),
        new MarkupSlot(name: 'body', wrapper: '<div class="card-body">%slot%</div>'),
        new MarkupSlot(name: 'footer', wrapper: '<div class="card-footer">%slot%</div>')
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

