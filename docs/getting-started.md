---
layout: default
title: Getting Started
nav_order: 2
---

# Getting Started

This guide will help you get started quickly with the Markup library. In just a few minutes, you'll be able to create your first HTML components in PHP.

## Installation

Install Markup via Composer:

```bash
composer require maxpertici/markup
```

## Your First Component

Create a simple HTML paragraph:

```php
<?php
require 'vendor/autoload.php';

use MaxPertici\Markup\Markup;

$paragraph = new Markup('<p>%children%</p>');
$paragraph->children('Hello, World!');

echo $paragraph->render();
// Output: <p>Hello, World!</p>
```

**Explanation:**
- `%children%` is a placeholder where content will be inserted
- `children()` defines the content to display
- `render()` returns the final HTML

## Adding CSS Classes

```php
$button = new Markup('<button class="%classes%">%children%</button>');
$button->addClass('btn', 'btn-primary')
       ->children('Click Here');

echo $button->render();
// Output: <button class="btn btn-primary">Click Here</button>
```

The `%classes%` placeholder is automatically replaced by the added classes.

## Managing HTML Attributes

```php
$link = new Markup('<a %attributes%>%children%</a>');
$link->setAttribute('href', 'https://example.com')
     ->setAttribute('target', '_blank')
     ->children('Visit our site');

echo $link->render();
// Output: <a href="https://example.com" target="_blank">Visit our site</a>
```

## Quick Creation with MarkupFactory

Use `MarkupFactory` to create elements faster:

```php
use MaxPertici\Markup\MarkupFactory;

// Create a div with classes and attributes
$div = MarkupFactory::create(
    'div',
    ['container', 'text-center'],
    ['id' => 'main']
);
$div->append('Div content');

echo $div->render();
// Output: <div class="container text-center" id="main">Div content</div>
```

## Parsing Existing HTML

```php
$html = '<div class="card"><h2>Title</h2><p>Content</p></div>';
$parsed = MarkupFactory::fromHtml($html);

// You can now manipulate the structure
$parsed->addClass('shadow')
       ->setAttribute('data-enhanced', 'true');

echo $parsed->render();
```

## Composing Complex Structures

```php
$card = new Markup('<div class="card">%children%</div>');
$card->children(
    new Markup('<h3>%children%</h3>', children: ['Card Title']),
    new Markup('<p>%children%</p>', children: ['Card description'])
);

echo $card->render();
```

**Output:**
```html
<div class="card">
    <h3>Card Title</h3>
    <p>Card description</p>
</div>
```

## What's Next?

You now know the basics! Explore the full documentation to discover:

- **[Markup](/markup/markup.html)** - All methods of the Markup class
- **[MarkupFactory](/markup/markup-factory.html)** - Quick element creation and HTML parsing
- **[MarkupSlot](/markup/markup-slot.html)** - Slot system for reusable components
- **[MarkupFinder](/markup/markup-finder.html)** - Search and manipulate elements

---

**Need help?** Check out the [full README](https://github.com/maxpertici/markup) or open an [issue on GitHub](https://github.com/maxpertici/markup/issues).

