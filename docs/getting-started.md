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

A simple paragraph:

```php
<?php
use MaxPertici\Markup\Markup;

$p = new Markup('<p>%children%</p>', children: ['Hello, World!']);
echo $p->render();
```

```html
<p>Hello, World!</p>
```

**Key concepts:**
- `%children%` → placeholder for content
- `children: [...]` → defines the content
- `render()` → generates the HTML

## Adding Classes

```php
$button = new Markup(
    '<button class="%classes%">%children%</button>',
    classes: ['btn', 'btn-primary'],
    children: ['Click me']
);

echo $button->render();
```

```html
<button class="btn btn-primary">Click me</button>
```

## Managing Attributes

```php
$link = new Markup(
    '<a %attributes%>%children%</a>',
    attributes: [
        'href' => 'https://example.com',
        'target' => '_blank'
    ],
    children: ['Visit our site']
);

echo $link->render();
```

```html
<a href="https://example.com" target="_blank">Visit our site</a>
```

## Quick Creation with Factory

Create elements in one line with `MarkupFactory`:

```php
use MaxPertici\Markup\MarkupFactory;

$div = MarkupFactory::create('div', ['container'], ['id' => 'main']);
$div->append('Content goes here');

echo $div->render();
```

Or use the shortcut `Markup::make()`:

```php
$div = Markup::make('div', ['container'], ['id' => 'main']);
$div->append('Content goes here');

echo $div->render();
```

```html
<div class="container" id="main">Content goes here</div>
```

## Parsing HTML

Transform existing HTML into manipulable objects with `MarkupFactory`:

```php
$html = '<div class="card"><h2>Title</h2></div>';
$card = MarkupFactory::fromHtml($html);

$card->addClass('shadow')
     ->setAttribute('data-enhanced', 'true');

echo $card->render();
```

Or use `Markup::fromHtml()`:

```php
$card = Markup::fromHtml('<div class="card"><h2>Title</h2></div>');
$card->addClass('shadow')
     ->setAttribute('data-enhanced', 'true');

echo $card->render();
```

```html
<div class="card shadow" data-enhanced="true"><h2>Title</h2></div>
```

## Composing Structures

Nest elements naturally:

```php
$card = new Markup(
    '<div class="card">%children%</div>',
    children: [
        new Markup('<h3>%children%</h3>', children: ['Product Name']),
        new Markup('<p>%children%</p>', children: ['$99.00'])
    ]
);

echo $card->render();
```

```html
<div class="card">
    <h3>Product Name</h3>
    <p>$99.00</p>
</div>
```

## Building Lists

Simple list with automatic wrapping:

```php
use MaxPertici\Markup\Markup;

$menu = new Markup(
    wrapper: '<ul>%children%</ul>',
    childrenWrapper: '<li>%child%</li>',
    children: ['Home', 'About', 'Services', 'Contact']
);

echo $menu->render();
```

```html
<ul>
    <li>Home</li>
    <li>About</li>
    <li>Services</li>
    <li>Contact</li>
</ul>
```

## Nested Lists

Create sublists with `MarkupFlow`:

```php
use MaxPertici\Markup\MarkupFlow;

$nav = new Markup(
    wrapper: '<ul>%children%</ul>',
    childrenWrapper: '<li>%child%</li>',
    children: [
        'Home',
        new MarkupFlow([
            'Products',
            new Markup(
                wrapper: '<ul>%children%</ul>',
                children: ['<li>Electronics</li>', '<li>Clothing</li>']
            )
        ]),
        'Contact'
    ]
);

echo $nav->render();
```

```html
<ul>
    <li>Home</li>
    <li>Products
        <ul>
            <li>Electronics</li>
            <li>Clothing</li>
        </ul>
    </li>
    <li>Contact</li>
</ul>
```

## Real-World Example: Scraping Articles

Fetch HTML from a URL and extract articles:

```php
use MaxPertici\Markup\Markup;

// Fetch and parse HTML
$html = file_get_contents('https://example.com/blog');
$page = Markup::fromHtml($html);

// Find articles and process with collection methods
$articles = $page->find()->tag('article')->get();

$articles->each(function($article) {
    // Get title and excerpt
    $title = $article->find()->tag('h2')->first();
    $excerpt = $article->find()->tag('p')->first();
    
    if ($title) {
        echo "Title: " . $title->render() . "\n";
    }
    if ($excerpt) {
        echo "Excerpt: " . $excerpt->render() . "\n";
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

## What's Next?

You now know the basics! Explore the full documentation to discover:

- **[Markup](/markup/markup.html)** - Core class with all HTML manipulation methods
- **[MarkupFactory](/markup/markup-factory.html)** - Quick element creation and HTML parsing
- **[MarkupQueryBuilder](/markup/markup-query-builder.html)** - Fluent interface for searching elements
- **[MarkupCollection](/markup/markup-collection.html)** - Collection methods for working with multiple elements
- **[MarkupFinder](/markup/markup-finder.html)** - Advanced search and filtering capabilities
- **[MarkupFlow](/markup/markup-flow.html)** - Group elements without wrapper
- **[MarkupSlot](/markup/markup-slot.html)** - Slot system for reusable components

---

**Need help?** Check out the [full README](https://github.com/maxpertici/markup) or open an [issue on GitHub](https://github.com/maxpertici/markup/issues).

