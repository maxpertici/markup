# Markup

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)](https://www.php.net/)

📚 **[Complete Documentation](https://maxpertici.github.io/markup/)** | [Getting Started](https://maxpertici.github.io/markup/getting-started) | [Markup](https://maxpertici.github.io/markup/markup) | [MarkupFactory](https://maxpertici.github.io/markup/markup-factory) | [MarkupSlot](https://maxpertici.github.io/markup/markup-slot) | [MarkupFinder](https://maxpertici.github.io/markup/markup-finder)

A flexible and intuitive PHP library for building HTML markup structures using a fluent, chainable API. Parse existing HTML, modify and manipulate it easily, or create reusable components with slots, manage CSS classes and attributes, and render HTML efficiently.

## Introduction

**Markup** provides a modern, object-oriented approach to handling HTML in PHP. Instead of mixing HTML strings or using complex templating engines, Markup offers:

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

// Simple element creation
$paragraph = new Markup(
    wrapper: '<p>%children%</p>',
    children: ['Hello, World!']
);
echo $paragraph->render();
// Output: <p>Hello, World!</p>

// Create with classes and attributes using Markup::make()
$div = Markup::make(
    tag: 'div',
    classes: ['container', 'text-center'],
    attributes: ['id' => 'main']
);
$div->append('Content here');

echo $div->render();
// Output: <div class="container text-center" id="main">Content here</div>

// Parse existing HTML with Markup::fromHtml()
$card = Markup::fromHtml(
    html: '<div class="card"><h2>Title</h2></div>'
);
$card->addClass('shadow')
     ->setAttribute('data-enhanced', 'true');

echo $card->render();
// Output: <div class="card shadow" data-enhanced="true"><h2>Title</h2></div>
```

### Creating Components

```php
// Card component
$card = new Markup(
    wrapper: '<div class="card">%children%</div>'
);
$card->children(
    new Markup(
        wrapper:'<h2 class="card-title">%children%</h2>',
        children: ['Card Title']
    ),
    new Markup(
        wrapper:'<p class="card-body">%children%</p>',
        children: ['Card content']
    )
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
$button = new Markup(
    wrapper: '<button class="%classes%" %attributes%>%children%</button>',
    wrapperClasses: ['btn', 'btn-primary'],
    wrapperAttributes: ['type' => 'submit', 'id' => 'submit-btn'],
    children: ['Submit']
);

echo $button->render();
// Output: <button class="btn btn-primary" type="submit" id="submit-btn">Submit</button>

// Modify classes and attributes
$button->addClass('btn-lg')
       ->removeClass('btn-primary')
       ->addClass('btn-secondary')
       ->setAttribute('disabled', 'true');

// Check classes
if ($button->hasClass('btn-secondary')) {
    echo "Button is secondary";
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

### Finding and Manipulating Elements

```php
// Build a page structure
$page = Markup::make(
    tag: 'div',
    classes: ['page']
)->children(
    Markup::make(
        tag: 'header',
        classes: ['header']
    ),
    Markup::make(
        tag: 'main',
        classes: ['content']
    )->children(
        Markup::make(
            tag: 'p',
            classes: ['intro']
        )->append('Introduction text'),
        Markup::make(
            tag: 'p',
            classes: ['highlight']
        )->append('Important note')
    )
);

// Find elements using CSS selectors
$headers = $page->find()->css('.header')->get();
$paragraphs = $page->find()->css('main p')->get();
$highlighted = $page->find()->css('.highlight')->first();

// Modify found elements with collection methods
$page->find()->css('.intro')->get()
    ->each(fn($intro) => $intro->addClass('text-large'));

// Filter and transform
$page->find()->tag('p')->get()
    ->filter(fn($p) => $p->hasClass('intro'))
    ->each(fn($p) => $p->addClass('featured'));
```

### Conditional and Loops

```php
// Conditional rendering
$card = Markup::make(
    tag: 'div',
    classes: ['card']
)->append('Regular content');
$isAdmin = true;

$card->when($isAdmin, function($markup) {
    $markup->append(
        Markup::make(
            tag: 'div',
            classes: ['admin-panel'])
        ->append('Admin tools')
    );
});

// Loop through data
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
];

$list = Markup::make(tag: 'ul');
$list->each($users, function($user, $index, $markup) {
    $markup->append(
        Markup::make(tag: 'li')
        ->append("{$user['name']} - {$user['email']}")
    );
});

echo $list->render();
```

### Nested Lists with MarkupFlow

Use `MarkupFlow` to create complex nested structures:

```php
use MaxPertici\Markup\MarkupFlow;

$nav = new Markup(
    wrapper: '<ul>%children%</ul>',
    childrenWrapper: '<li>%child%</li>',
    children: [
        'Home',
        'About',
        new MarkupFlow([
            'Products',
            new Markup(
                wrapper: '<ul>%children%</ul>',
                children: [
                    '<li>Electronics</li>',
                    '<li>Clothing</li>',
                    '<li>Books</li>',
                ]
            ),
        ]),
        'Contact',
    ]
);

echo $nav->render();
```

**Output:**

```html
<ul>
  <li>Home</li>
  <li>About</li>
  <li>
    Products
    <ul>
      <li>Electronics</li>
      <li>Clothing</li>
      <li>Books</li>
    </ul>
  </li>
  <li>Contact</li>
</ul>
```

`MarkupFlow` allows you to group multiple elements that will be rendered sequentially within a parent element, perfect for creating nested structures.

### Real-World Example: Web Scraping

Fetch HTML from a URL and extract content:

```php
use MaxPertici\Markup\Markup;

// Fetch and parse HTML
$html = file_get_contents('https://example.com/blog');
$page = Markup::fromHtml(html: $html);

// Find articles and process with collection methods
$articles = $page->find()->tag('article')->get();

$articles->each(function($article) {
    // Get title and excerpt
    $title = $article->find()->tag('h2')->first();
    $excerpt = $article->find()->tag('p')->first();

    if ($title) {
        echo "Title: " . $title->text() . "\n";
    }
    if ($excerpt) {
        echo "Excerpt: " . $excerpt->text() . "\n";
    }
    echo "---\n";
});
```

Use collection methods to filter and transform:

```php
// Get only featured articles
$featured = $page->find()->tag('article')->get()
    ->filter(fn($article) => $article->hasClass('featured'));

// Extract all titles
$titles = $page->find()->tag('article')->get()
    ->map(fn($article) => $article->find()->tag('h2')->first())
    ->filter(fn($title) => $title !== null)
    ->map(fn($title) => $title->text());

// Take first 5 articles and enhance them
$page->find()->tag('article')->get()
    ->take(5)
    ->each(fn($article) => $article->addClass('featured-item'));

echo $page->render();
```

## Documentation

For complete documentation, examples, and API reference, visit:

### 📖 [Complete Documentation](https://maxpertici.github.io/markup/)

#### Main Sections

- **[Getting Started](https://maxpertici.github.io/markup/getting-started)** - Installation and basic usage
- **[Markup](https://maxpertici.github.io/markup/markup)** - Core Markup class with all HTML manipulation methods
- **[MarkupFactory](https://maxpertici.github.io/markup/markup-factory)** - Factory methods and HTML parsing
- **[MarkupQueryBuilder](https://maxpertici.github.io/markup/markup-query-builder)** - Fluent interface for searching elements
- **[MarkupCollection](https://maxpertici.github.io/markup/markup-collection)** - Collection methods for working with multiple elements
- **[MarkupFinder](https://maxpertici.github.io/markup/markup-finder)** - Advanced search and filtering capabilities
- **[MarkupFlow](https://maxpertici.github.io/markup/markup-flow)** - Group elements without wrapper
- **[MarkupSlot](https://maxpertici.github.io/markup/markup-slot)** - Slot system for reusable components

## Key Features

### Factory Methods

Create elements quickly with `Markup::make()` (shortcut for `MarkupFactory::create()`):

```php
// Create elements with Markup::make()
$div = Markup::make(
    tag: 'div',
    classes: ['container'],
    attributes: ['id' => 'main']
);
$div->append('Content here');

// Or use MarkupFactory directly
$div = MarkupFactory::create(
    tag: 'div',
    classes: ['container'],
    attributes: ['id' => 'main']
);

// Parse HTML with Markup::fromHtml()
$markup = Markup::fromHtml(html: '<div class="box">Content</div>');
$markup->addClass('shadow');

// Or use MarkupFactory directly
$markup = MarkupFactory::fromHtml(html: '<div class="box">Content</div>');

// Use predefined elements with enums
use MaxPertici\Markup\Elements\HtmlTag;

$button = MarkupFactory::fromElement(
    element: HtmlTag::BUTTON,
    children: ['Click me'],
    classes: ['btn', 'btn-primary']
);
```

### CSS Selector Queries

Find elements using familiar CSS selector syntax:

```php
$page->find()->css('.section')->get();              // by class
$page->find()->css('div')->get();                   // by tag
$page->find()->css('#hero')->first();               // by ID
$page->find()->css('[role="main"]')->get();         // by attribute
$page->find()->css('nav li.active')->get();         // combined
$page->find()->css('.header > nav')->get();         // direct child
$page->find()->css('nav:has(li.active)')->get();    // has pseudo-class
```

### Collection Methods

Work with multiple elements using powerful collection methods:

```php
// Filter elements
$featured = $page->find()->tag('article')->get()
    ->filter(fn($article) => $article->hasClass('featured'));

// Map and transform
$titles = $page->find()->tag('h2')->get()
    ->map(fn($title) => $title->text());

// Take subset
$first5 = $page->find()->tag('p')->get()->take(5);

// Iterate with each
$page->find()->css('.card')->get()
    ->each(fn($card) => $card->addClass('enhanced'));

// Chain methods
$page->find()->tag('article')->get()
    ->filter(fn($article) => $article->hasClass('published'))
    ->take(10)
    ->each(fn($article) => $article->addClass('featured'));
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

$card = MarkupFactory::fromElement(
    element: BootstrapComponent::CARD,
    classes: ['Card content']
);
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
