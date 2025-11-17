# Markup

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](https://www.php.net/)

📚 **[Complete Documentation](https://maxpertici.github.io/markup/)** | [Getting Started](https://maxpertici.github.io/markup/getting-started) | [Markup](https://maxpertici.github.io/markup/markup) | [MarkupFactory](https://maxpertici.github.io/markup/markup-factory) | [MarkupSlot](https://maxpertici.github.io/markup/markup-slot) | [MarkupFinder](https://maxpertici.github.io/markup/markup-finder)

A flexible and intuitive PHP library for building HTML markup structures using a fluent, chainable API. Create reusable components with slots, manage CSS classes and attributes, and render HTML efficiently.

## Introduction

**Markup** provides a modern, object-oriented approach to generating HTML in PHP. Instead of mixing HTML strings or using complex templating engines, Markup offers:

- ✨ **Fluent API** - Chain methods for intuitive markup building
- 🎯 **Slot System** - Named placeholders like Vue.js or Laravel Blade
- 🎨 **CSS Class Management** - Add, remove, check classes easily
- ⚙️ **HTML Attributes** - Full control over element attributes
- 🔄 **Nested Components** - Compose complex structures from simple parts
- 🚀 **Dual Rendering** - `render()` to return string, `print()` for streaming
- 🔁 **Conditional & Loop Helpers** - `when()` and `each()` for dynamic content
- 🔍 **Search & Find** - Query your markup tree with CSS selectors
- 📦 **Zero Dependencies** - Pure PHP, no external requirements

Perfect for building reusable UI components, generating dynamic HTML, or creating a component library for your PHP applications.

## Requirements

- PHP 8.1 or higher

## Installation

Install via Composer:

```bash
composer require maxpertici/markup
```

## Getting Started

### Quick Example

```php
<?php
require 'vendor/autoload.php';

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupFactory;

// Simple element creation
$paragraph = new Markup('<p class="text-center">%children%</p>');
$paragraph->children('Hello, World!');
echo $paragraph->render();
// Output: <p class="text-center">Hello, World!</p>

// Using the Factory
$div = MarkupFactory::create(
    'div',
    ['container', 'text-center'],
    ['id' => 'main']
)->children('Content here');

echo $div->render();
// Output: <div class="container text-center" id="main">Content here</div>

// Parse existing HTML
$html = '<div class="card"><h2>Title</h2><p>Content</p></div>';
$markup = MarkupFactory::fromHtml($html);
$markup->addClass('shadow-lg');
echo $markup->render();
```

### Creating Components

```php
// Card component
$card = new Markup('<div class="card">%children%</div>');
$card->children(
    new Markup('<h2 class="card-title">%children%</h2>', children: ['Card Title']),
    new Markup('<p class="card-body">%children%</p>', children: ['Card content'])
);

echo $card->render();
```

**Output:**
```html
<div class="card">
    <h2 class="card-title">Card Title</h2>
    <p class="card-body">Card content</p>
</div>
```

### Managing Classes and Attributes

```php
$button = new Markup('<button class="%classes%" %attributes%>%children%</button>');
$button->addClass('btn', 'btn-primary')
       ->setAttribute('type', 'submit')
       ->setAttribute('id', 'submit-btn')
       ->children('Submit');

echo $button->render();
// Output: <button class="btn btn-primary" type="submit" id="submit-btn">Submit</button>

// Check and modify
if ($button->hasClass('btn-primary')) {
    $button->removeClass('btn-primary')->addClass('btn-secondary');
}
```

### Using Slots

```php
use MaxPertici\Markup\MarkupSlot;

// Define layout with slots
$layout = new Markup('<div class="layout">%children%</div>');
$layout->children(
    new MarkupSlot('header', '<header>%slot%</header>'),
    new MarkupSlot('content', '<main>%slot%</main>'),
    new MarkupSlot('footer', '<footer>%slot%</footer>')
);

// Fill the slots
$layout->slot('header', ['<h1>My Website</h1>'])
       ->slot('content', ['<p>Welcome!</p>'])
       ->slot('footer', ['<p>&copy; 2024</p>']);

echo $layout->render();
```

### Finding Elements

```php
use MaxPertici\Markup\Elements\HtmlTag;

// Build a page structure
$page = MarkupFactory::fromElement(HtmlTag::DIV)
    ->addClass('page')
    ->children(
        MarkupFactory::fromElement(HtmlTag::HEADER)->addClass('header'),
        MarkupFactory::fromElement(HtmlTag::MAIN)
            ->addClass('content')
            ->children(
                MarkupFactory::fromElement(HtmlTag::P)->addClass('intro'),
                MarkupFactory::fromElement(HtmlTag::P)->addClass('highlight')
            )
    );

// Find elements using CSS selectors
$headers = $page->find()->css('.header');
$paragraphs = $page->find()->css('main p');
$highlighted = $page->find()->css('.highlight');

// Modify found elements
foreach ($page->find()->css('.intro') as $intro) {
    $intro->addClass('text-large');
}
```

### Conditional and Loops

```php
// Conditional rendering
$card = new Markup('<div class="card">%children%</div>');
$isAdmin = true;

$card->children('Regular content')
     ->when($isAdmin, function($markup) {
         $markup->children('<div class="admin-panel">Admin tools</div>');
     });

// Loop through data
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
];

$list = new Markup('<ul>%children%</ul>');
$list->each($users, function($user, $index, $markup) {
    $markup->children(
        new Markup('<li>%children%</li>', children: [
            "{$user['name']} - {$user['email']}"
        ])
    );
});

echo $list->render();
```

## Documentation

For complete documentation, examples, and API reference, visit:

### 📖 [Complete Documentation](https://maxpertici.github.io/markup/)

#### Main Sections

- **[Getting Started](https://maxpertici.github.io/markup/getting-started)** - Installation and basic usage
- **[Markup](https://maxpertici.github.io/markup/markup)** - Core Markup class documentation
- **[MarkupFactory](https://maxpertici.github.io/markup/markup-factory)** - Factory methods and HTML parsing
- **[MarkupSlot](https://maxpertici.github.io/markup/markup-slot)** - Slot system for flexible layouts
- **[MarkupFinder](https://maxpertici.github.io/markup/markup-finder)** - Search and query elements with CSS selectors

## Key Features

### Factory Methods

Create elements quickly with `MarkupFactory`:

```php
// Create elements
$div = MarkupFactory::create('div', ['container'], ['id' => 'main']);

// Parse HTML
$markup = MarkupFactory::fromHtml('<div class="box">Content</div>');

// Use predefined elements
$button = MarkupFactory::fromElement(
    HtmlTag::BUTTON,
    ['Click me'],
    ['btn', 'btn-primary']
);
```

### CSS Selector Queries

Find elements using familiar CSS selector syntax:

```php
$page->find()->css('.section');              // by class
$page->find()->css('div');                   // by tag
$page->find()->css('#hero');                 // by ID
$page->find()->css('[role="main"]');         // by attribute
$page->find()->css('nav li.active');         // combined
$page->find()->css('.header > nav');         // direct child
$page->find()->css('nav:has(li.active)');    // has pseudo-class
```

### Enum-Based Components

Create reusable component libraries with PHP enums:

```php
use MaxPertici\Markup\Contracts\MarkupElementInterface;

enum BootstrapComponent implements MarkupElementInterface {
    case CARD;
    case BUTTON_PRIMARY;
    case ALERT_SUCCESS;
    // ... implement required methods
}

$card = MarkupFactory::fromElement(BootstrapComponent::CARD, ['Card content']);
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

**Author:** Max Pertici  
**Email:** hello@maxpertici.fr  
**Website:** [maxpertici.fr](https://maxpertici.fr)

## Support

- 📚 **Documentation**: [https://maxpertici.github.io/markup/](https://maxpertici.github.io/markup/)
- 🐛 **Issues**: [GitHub Issues](https://github.com/maxpertici/markup/issues)
- ✉️ **Email**: hello@maxpertici.fr

---

Made with ❤️ by [Max Pertici](https://maxpertici.fr)

