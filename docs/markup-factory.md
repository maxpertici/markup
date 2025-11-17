---
layout: default
title: MarkupFactory
nav_order: 6
---

# MarkupFactory Class
{: .no_toc }

Factory class for creating Markup instances using convenient static methods.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupFactory` class provides a collection of static factory methods for creating `Markup` instances in various ways. It follows the Factory design pattern to offer convenient, expressive shortcuts for common markup creation tasks.

### Key Features

- **Multiple Creation Patterns** - Three different ways to create Markup instances
- **Type-Safe Element Creation** - Use enums for component libraries
- **HTML Parsing** - Convert existing HTML into Markup structures
- **Helper Methods** - Quick shortcuts for common HTML elements
- **Zero Configuration** - Static methods, no instantiation needed

### When to Use MarkupFactory

**Use `MarkupFactory::create()`** when:
- Need quick element creation
- Building custom HTML elements
- Performance is critical (~3x faster than fromHtml)
- Don't need to parse nested HTML

**Use `MarkupFactory::fromElement()`** when:
- Working with predefined component libraries
- Building type-safe, reusable components
- Need IDE autocompletion and type checking
- Working with CSS frameworks (Bootstrap, Tailwind, etc.)

**Use `MarkupFactory::fromHtml()`** when:
- Converting existing HTML to Markup
- Migrating from string-based templates
- Need to parse complex nested structures
- Working with legacy HTML code

**Use helper methods** (`div()`, `p()`, `button()`, etc.) when:
- Need very quick element creation with content
- Prefer readable, explicit method names
- Building simple elements

---

## Factory Methods

### create()

Creates a Markup instance from a tag name with optional classes and attributes.

```php
public static function create(
    string $tag,
    array $classes = [],
    array $attributes = []
): Markup
```

This is the fastest and most flexible way to create custom HTML elements. Content is added using the `append()` or `children()` methods.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag` | `string` | HTML tag name (e.g., 'div', 'span', 'article') |
| `$classes` | `array` | Optional. Array of CSS class names |
| `$attributes` | `array` | Optional. Associative array of HTML attributes |

#### Return

- `Markup` - A new Markup instance ready to have content added

#### Examples

```php
use MaxPertici\Markup\MarkupFactory;

// Simple div
$div = MarkupFactory::create('div');
$div->append('Hello World');

// Div with classes
$container = MarkupFactory::create('div', ['container', 'text-center']);
$container->append('Centered content');

// Complete element with everything
$article = MarkupFactory::create(
    'article',
    ['post', 'featured'],
    ['id' => 'post-123', 'data-author' => 'john']
);
$article->append('<h2>Article Title</h2>')
        ->append('<p>Article content...</p>');

echo $article->render();
/*
<article class="post featured" id="post-123" data-author="john">
    <h2>Article Title</h2>
    <p>Article content...</p>
</article>
*/

// Chaining after creation
$button = MarkupFactory::create('button', ['btn'])
    ->addClass('btn-primary')
    ->setAttribute('type', 'submit')
    ->append('Submit Form');
```

**Performance:** This is approximately **3x faster** than `fromHtml()` for simple elements.

**Use when:**
- You need maximum performance
- Building elements programmatically
- You want full control over structure
- Content will be added dynamically

---

### fromElement()

Creates a Markup instance from a predefined element configuration (enum).

```php
public static function fromElement(
    MarkupElementInterface $element,
    array $children = [],
    array $classes = [],
    array $attributes = []
): Markup
```

This is the most powerful and type-safe way to create Markup. Elements define their wrapper template, default classes, and attributes via the `MarkupElementInterface`.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$element` | `MarkupElementInterface` | Element configuration (usually an enum case) |
| `$children` | `array` | Optional. Array of children to add |
| `$classes` | `array` | Optional. Additional CSS classes to merge with element classes |
| `$attributes` | `array` | Optional. Additional HTML attributes to merge with element attributes |

#### Return

- `Markup` - A new Markup instance with merged configuration

#### Examples

**Using Built-in HtmlTag Enum:**

```php
use MaxPertici\Markup\MarkupFactory;
use MaxPertici\Markup\Elements\HtmlTag;

// Simple div
$div = MarkupFactory::fromElement(HtmlTag::DIV);

// Div with children and classes
$container = MarkupFactory::fromElement(
    HtmlTag::DIV,
    ['Content here'],
    ['container', 'mx-auto']
);

// Section with attributes
$section = MarkupFactory::fromElement(
    HtmlTag::SECTION,
    ['Section content'],
    ['main-section'],
    ['id' => 'hero', 'data-theme' => 'dark']
);

echo $section->render();
// Output: <section class="main-section" id="hero" data-theme="dark">Section content</section>
```

**Creating Custom Component Enums:**

```php
use MaxPertici\Markup\Contracts\MarkupElementInterface;

enum BootstrapComponent: string implements MarkupElementInterface {
    
    case CARD = 'card';
    case ALERT_SUCCESS = 'alert-success';
    case BUTTON_PRIMARY = 'btn-primary';
    
    public function wrapper(): string {
        return match ($this) {
            self::CARD => '<div class="%classes%" %attributes%>%children%</div>',
            self::ALERT_SUCCESS => '<div class="alert %classes%" %attributes% role="alert">%children%</div>',
            self::BUTTON_PRIMARY => '<button class="btn %classes%" %attributes%>%children%</button>',
        };
    }
    
    public function classes(): array {
        return match ($this) {
            self::CARD => ['card'],
            self::ALERT_SUCCESS => ['alert-success'],
            self::BUTTON_PRIMARY => ['btn-primary'],
        };
    }
    
    public function attributes(): array {
        return match ($this) {
            self::BUTTON_PRIMARY => ['type' => 'button'],
            default => [],
        };
    }
    
    public function childrenWrapper(): string {
        return '';
    }
}

// Usage
$card = MarkupFactory::fromElement(
    BootstrapComponent::CARD,
    ['Card content here'],
    ['shadow-lg', 'mb-4'] // Additional classes merged with 'card'
);

$alert = MarkupFactory::fromElement(
    BootstrapComponent::ALERT_SUCCESS,
    ['Operation successful!'],
    ['alert-dismissible'] // Additional classes merged with 'alert-success'
);

echo $card->render();
// Output: <div class="card shadow-lg mb-4">Card content here</div>

echo $alert->render();
// Output: <div class="alert alert-success alert-dismissible" role="alert">Operation successful!</div>
```

**Benefits:**
- ✅ IDE autocompletion for all components
- ✅ Type safety - catch typos at compile time
- ✅ Centralized component definitions
- ✅ Easy to share as Composer packages
- ✅ Perfect for CSS framework integration

---

### fromHtml()

Creates a Markup instance by recursively parsing an HTML string.

```php
public static function fromHtml(
    string $html,
    ?int $max_depth = null,
    int $current_depth = 0
): Markup
```

This method parses HTML and creates a complete Markup tree with nested Markup instances for each HTML element. It preserves the entire structure including classes, attributes, and nesting.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$html` | `string` | HTML string to parse |
| `$max_depth` | `int\|null` | Optional. Maximum parsing depth. Null for unlimited |
| `$current_depth` | `int` | Internal. Current depth level. Default 0 |

#### Return

- `Markup` - A new Markup tree representing the parsed HTML structure

#### Examples

**Simple HTML Parsing:**

```php
use MaxPertici\Markup\MarkupFactory;

// Parse a simple element
$html = '<div class="card" id="card-1">Card content</div>';
$markup = MarkupFactory::fromHtml($html);

echo $markup->render();
// Output: <div class="card" id="card-1">Card content</div>

// Still chainable after parsing
$markup->addClass('shadow-lg')
       ->setAttribute('data-enhanced', 'true');

echo $markup->render();
// Output: <div class="card shadow-lg" id="card-1" data-enhanced="true">Card content</div>
```

**Nested HTML Parsing:**

```php
// Complex nested structure
$html = '
<div class="container">
    <header class="header">
        <h1 class="title">My Site</h1>
        <nav class="nav">
            <a href="/">Home</a>
            <a href="/about">About</a>
        </nav>
    </header>
    <main class="content">
        <p>Welcome to <strong>my site</strong>!</p>
    </main>
</div>
';

$page = MarkupFactory::fromHtml($html);

// The entire structure is now Markup instances
// You can find and manipulate any part
$links = $page->find()->css('nav a');
foreach ($links as $link) {
    $link->addClass('nav-link');
}

echo $page->render();
// All <a> tags now have 'nav-link' class
```

**Limiting Parsing Depth:**

```php
// Parse only 2 levels deep
$html = '
<div class="level-1">
    <div class="level-2">
        <div class="level-3">
            <div class="level-4">Deep content</div>
        </div>
    </div>
</div>
';

$markup = MarkupFactory::fromHtml($html, 2);

// First 2 levels are parsed as Markup
// Remaining levels are kept as raw HTML string
```

**Self-Closing Tags:**

```php
// Self-closing tags are properly handled
$html = '<img src="photo.jpg" alt="Photo" class="img-fluid"/>';
$img = MarkupFactory::fromHtml($html);

$img->addClass('rounded');
echo $img->render();
// Output: <img src="photo.jpg" alt="Photo" class="img-fluid rounded"/>

// Void elements (br, hr, img, input, etc.)
$html = '<br><hr><input type="text">';
$elements = MarkupFactory::fromHtml($html);
```

**Text Nodes:**

```php
// Text nodes become string children
$html = '<p>This is <strong>bold</strong> text</p>';
$markup = MarkupFactory::fromHtml($html);

// Structure:
// Markup(<p>) with children:
//   - "This is "
//   - Markup(<strong>) with child: "bold"
//   - " text"
```

**Important Notes:**
- Self-closing tags and void elements are detected automatically
- Malformed HTML may produce unexpected results
- Whitespace between elements may be normalized
- Unclosed tags are handled gracefully
- Maximum depth prevents infinite recursion in malformed HTML

---

## Helper Methods

Helper methods are convenient shortcuts for creating common HTML elements. They all follow the same pattern: create the element, optionally add content, and return the Markup instance.

### Common Elements

#### div()

Creates a `<div>` element.

```php
public static function div(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
// Empty div
$div = MarkupFactory::div();

// Div with content
$div = MarkupFactory::div('Hello World');

// Div with classes
$container = MarkupFactory::div('Content', ['container', 'mx-auto']);

// Complete div
$box = MarkupFactory::div(
    'Box content',
    ['box', 'rounded'],
    ['id' => 'main-box', 'data-type' => 'primary']
);

echo $box->render();
// Output: <div class="box rounded" id="main-box" data-type="primary">Box content</div>
```

---

#### span()

Creates a `<span>` element.

```php
public static function span(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$badge = MarkupFactory::span('New', ['badge', 'badge-primary']);

$icon = MarkupFactory::span('', ['icon', 'icon-home'], ['aria-hidden' => 'true']);
```

---

#### p()

Creates a `<p>` (paragraph) element.

```php
public static function p(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$paragraph = MarkupFactory::p('This is a paragraph.');

$intro = MarkupFactory::p(
    'Welcome to our site!',
    ['lead', 'text-muted']
);
```

---

### Semantic Elements

#### section()

Creates a `<section>` element.

```php
public static function section(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$section = MarkupFactory::section('', ['hero-section'], ['id' => 'hero']);
$section->append('<h1>Welcome</h1>')
        ->append('<p>Get started today</p>');
```

---

#### article()

Creates an `<article>` element.

```php
public static function article(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$article = MarkupFactory::article('', ['post'], ['data-id' => '123']);
$article->append('<h2>Article Title</h2>')
        ->append('<p>Article content...</p>');
```

---

#### header()

Creates a `<header>` element.

```php
public static function header(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$header = MarkupFactory::header('', ['site-header']);
$header->append('<h1>My Site</h1>')
       ->append('<nav>...</nav>');
```

---

#### footer()

Creates a `<footer>` element.

```php
public static function footer(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$footer = MarkupFactory::footer('&copy; 2024 My Site', ['site-footer']);
```

---

#### nav()

Creates a `<nav>` element.

```php
public static function nav(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$nav = MarkupFactory::nav('', ['main-nav'], ['role' => 'navigation']);
$nav->append('<a href="/">Home</a>')
    ->append('<a href="/about">About</a>');
```

---

### Heading Elements

All heading methods follow the same signature pattern.

#### h1() through h6()

Creates heading elements (`<h1>` to `<h6>`).

```php
public static function h1(string $content = '', array $classes = [], array $attributes = []): Markup
public static function h2(string $content = '', array $classes = [], array $attributes = []): Markup
public static function h3(string $content = '', array $classes = [], array $attributes = []): Markup
public static function h4(string $content = '', array $classes = [], array $attributes = []): Markup
public static function h5(string $content = '', array $classes = [], array $attributes = []): Markup
public static function h6(string $content = '', array $classes = [], array $attributes = []): Markup
```

**Examples:**

```php
$h1 = MarkupFactory::h1('Page Title', ['title', 'text-center']);

$h2 = MarkupFactory::h2('Section Heading', ['section-title']);

$h3 = MarkupFactory::h3(
    'Subsection',
    ['subsection-title'],
    ['id' => 'subsection-1']
);

echo $h1->render();
// Output: <h1 class="title text-center">Page Title</h1>
```

---

### List Elements

#### ul()

Creates an unordered list (`<ul>`) element.

```php
public static function ul(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$list = MarkupFactory::ul('', ['nav-list']);
$list->append('<li>Item 1</li>')
     ->append('<li>Item 2</li>')
     ->append('<li>Item 3</li>');

// Better approach with children_wrapper
$list = new Markup(
    wrapper: '<ul class="nav-list">%children%</ul>',
    children_wrapper: '<li>%child%</li>',
    children: ['Item 1', 'Item 2', 'Item 3']
);
```

---

#### ol()

Creates an ordered list (`<ol>`) element.

```php
public static function ol(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$steps = MarkupFactory::ol('', ['steps-list']);
$steps->append('<li>First step</li>')
      ->append('<li>Second step</li>')
      ->append('<li>Third step</li>');
```

---

#### li()

Creates a list item (`<li>`) element.

```php
public static function li(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
$item = MarkupFactory::li('List item', ['nav-item']);

$activeItem = MarkupFactory::li(
    'Active item',
    ['nav-item', 'active'],
    ['aria-current' => 'page']
);
```

---

### Interactive Elements

#### button()

Creates a `<button>` element. Automatically sets `type="button"` if not specified.

```php
public static function button(
    string $content = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
// Simple button
$button = MarkupFactory::button('Click Me', ['btn', 'btn-primary']);

// Submit button
$submit = MarkupFactory::button(
    'Submit Form',
    ['btn', 'btn-success'],
    ['type' => 'submit', 'form' => 'myForm']
);

// Button with icon
$iconButton = MarkupFactory::button('', ['btn', 'btn-icon']);
$iconButton->append('<i class="icon-save"></i> Save');

echo $submit->render();
// Output: <button class="btn btn-success" type="submit" form="myForm">Submit Form</button>
```

---

#### a()

Creates an anchor/link (`<a>`) element. Automatically sets the `href` attribute.

```php
public static function a(
    string $href,
    string $text = '',
    array $classes = [],
    array $attributes = []
): Markup
```

**Examples:**

```php
// Simple link
$link = MarkupFactory::a('https://example.com', 'Visit Example');

// Link with classes
$navLink = MarkupFactory::a('/', 'Home', ['nav-link', 'active']);

// External link with attributes
$external = MarkupFactory::a(
    'https://example.com',
    'External Site',
    ['external-link'],
    ['target' => '_blank', 'rel' => 'noopener noreferrer']
);

// Link without text (add content later)
$dynamicLink = MarkupFactory::a('/profile', '', ['profile-link']);
$dynamicLink->append('<img src="avatar.jpg" alt="Profile">');

echo $external->render();
// Output: <a href="https://example.com" class="external-link" target="_blank" rel="noopener noreferrer">External Site</a>
```

---

## Practical Examples

### Building a Card Component

```php
use MaxPertici\Markup\MarkupFactory;

function createCard($title, $content, $imageUrl = null) {
    $card = MarkupFactory::div('', ['card', 'shadow-lg']);
    
    // Optional image
    if ($imageUrl) {
        $img = MarkupFactory::create('img', ['card-img-top']);
        $img->setAttribute('src', $imageUrl)
            ->setAttribute('alt', $title);
        $card->append($img);
    }
    
    // Card body
    $body = MarkupFactory::div('', ['card-body']);
    $body->append(MarkupFactory::h3($title, ['card-title']))
         ->append(MarkupFactory::p($content, ['card-text']));
    
    $card->append($body);
    
    return $card;
}

// Usage
$productCard = createCard(
    'Premium Widget',
    'The best widget money can buy!',
    '/images/widget.jpg'
);

echo $productCard->render();
```

---

### Building a Navigation Menu

```php
use MaxPertici\Markup\MarkupFactory;

function createNav(array $items) {
    $nav = MarkupFactory::nav('', ['main-nav'], ['role' => 'navigation']);
    $ul = MarkupFactory::ul('', ['nav-list']);
    
    foreach ($items as $item) {
        $li = MarkupFactory::li('', ['nav-item']);
        
        if ($item['active'] ?? false) {
            $li->addClass('active');
        }
        
        $link = MarkupFactory::a(
            $item['url'],
            $item['label'],
            ['nav-link']
        );
        
        $li->append($link);
        $ul->append($li);
    }
    
    $nav->append($ul);
    return $nav;
}

// Usage
$menu = createNav([
    ['label' => 'Home', 'url' => '/', 'active' => true],
    ['label' => 'About', 'url' => '/about'],
    ['label' => 'Contact', 'url' => '/contact'],
]);

echo $menu->render();
```

---

### Parsing and Enhancing Existing HTML

```php
use MaxPertici\Markup\MarkupFactory;

// Start with existing HTML template
$template = '
<div class="blog-post">
    <h2 class="post-title">Blog Post Title</h2>
    <div class="post-meta">
        <span class="author">John Doe</span>
        <span class="date">2024-01-15</span>
    </div>
    <div class="post-content">
        <p>This is the blog post content.</p>
    </div>
</div>
';

// Parse into Markup
$post = MarkupFactory::fromHtml($template);

// Enhance programmatically
$post->addClass('featured')
     ->setAttribute('data-post-id', '123');

// Find and modify specific elements
$title = $post->find()->css('.post-title')[0] ?? null;
if ($title) {
    $title->addClass('text-gradient');
}

$date = $post->find()->css('.date')[0] ?? null;
if ($date) {
    $date->setAttribute('datetime', '2024-01-15');
}

echo $post->render();
```

---

### Using Custom Component Enums

```php
use MaxPertici\Markup\MarkupFactory;
use MaxPertici\Markup\Contracts\MarkupElementInterface;

// Define a Tailwind CSS component library
enum TailwindComponent implements MarkupElementInterface {
    
    case CONTAINER;
    case CARD;
    case BUTTON_PRIMARY;
    case BUTTON_SECONDARY;
    case BADGE;
    
    public function wrapper(): string {
        return match ($this) {
            self::CONTAINER, self::CARD => '<div class="%classes%" %attributes%>%children%</div>',
            self::BUTTON_PRIMARY, self::BUTTON_SECONDARY => '<button class="%classes%" %attributes%>%children%</button>',
            self::BADGE => '<span class="%classes%" %attributes%>%children%</span>',
        };
    }
    
    public function classes(): array {
        return match ($this) {
            self::CONTAINER => ['container', 'mx-auto', 'px-4'],
            self::CARD => ['bg-white', 'rounded-lg', 'shadow-md', 'p-6'],
            self::BUTTON_PRIMARY => ['bg-blue-500', 'hover:bg-blue-700', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded'],
            self::BUTTON_SECONDARY => ['bg-gray-500', 'hover:bg-gray-700', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded'],
            self::BADGE => ['inline-block', 'bg-gray-200', 'rounded-full', 'px-3', 'py-1', 'text-sm', 'font-semibold', 'text-gray-700'],
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

// Build a page with type-safe components
$page = MarkupFactory::fromElement(TailwindComponent::CONTAINER, [
    MarkupFactory::fromElement(TailwindComponent::CARD, [
        MarkupFactory::h2('Welcome!'),
        MarkupFactory::p('Get started with our platform today.'),
        MarkupFactory::fromElement(TailwindComponent::BUTTON_PRIMARY, ['Sign Up']),
        MarkupFactory::fromElement(TailwindComponent::BADGE, ['New']),
    ]),
]);

echo $page->render();
```

---

### Combining Multiple Creation Methods

```php
use MaxPertici\Markup\MarkupFactory;
use MaxPertici\Markup\Elements\HtmlTag;

// Use different methods based on the situation
$page = MarkupFactory::div('', ['page-wrapper']);

// Parse existing header template
$headerHtml = '<header class="site-header"><h1>My Site</h1></header>';
$header = MarkupFactory::fromHtml($headerHtml);
$page->append($header);

// Create main section with fromElement
$main = MarkupFactory::fromElement(HtmlTag::MAIN, [], ['content'], ['role' => 'main']);
$main->append(MarkupFactory::h2('Latest News'))
     ->append(MarkupFactory::p('Check out our latest updates...'));
$page->append($main);

// Use helper method for footer
$footer = MarkupFactory::footer('&copy; 2024 My Site', ['site-footer']);
$page->append($footer);

echo $page->render();
```

---

## Performance Considerations

### Method Performance Comparison

Benchmark results (1000 iterations):

```php
// create() - FASTEST
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $markup = MarkupFactory::create('div', ['class1'], ['id' => 'test']);
}
$time = microtime(true) - $start;
// ~2.5ms

// fromHtml() - SLOWER (3x)
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $markup = MarkupFactory::fromHtml('<div class="class1" id="test"></div>');
}
$time = microtime(true) - $start;
// ~7.5ms

// fromElement() - MEDIUM
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $markup = MarkupFactory::fromElement(HtmlTag::DIV, [], ['class1'], ['id' => 'test']);
}
$time = microtime(true) - $start;
// ~3.5ms
```

### Best Practices

**Use `create()` when:**
- ✅ Performance is critical
- ✅ Building elements programmatically
- ✅ Simple elements without nested HTML
- ✅ Maximum flexibility needed

**Use `fromElement()` when:**
- ✅ Building reusable component libraries
- ✅ Type safety and IDE support are important
- ✅ Working with CSS frameworks
- ✅ Sharing components across projects

**Use `fromHtml()` when:**
- ✅ Parsing existing HTML templates
- ✅ Migrating from string-based HTML
- ✅ Performance is not critical
- ✅ Need to manipulate complex nested structures

**Use helper methods when:**
- ✅ Quick prototyping
- ✅ Readability is more important than flexibility
- ✅ Building simple elements with content
- ✅ Prefer explicit method names

---

## Creating Custom Element Libraries

### Step 1: Define the Interface

All element libraries must implement `MarkupElementInterface`:

```php
// MaxPertici\Markup\Contracts\MarkupElementInterface
interface MarkupElementInterface {
    public function wrapper(): string;
    public function classes(): array;
    public function attributes(): array;
    public function childrenWrapper(): string;
}
```

### Step 2: Create Your Enum

```php
use MaxPertici\Markup\Contracts\MarkupElementInterface;

enum MyComponents: string implements MarkupElementInterface {
    
    case PRIMARY_BUTTON = 'btn-primary';
    case SECONDARY_BUTTON = 'btn-secondary';
    case SUCCESS_ALERT = 'alert-success';
    case DANGER_ALERT = 'alert-danger';
    
    /**
     * Returns the HTML wrapper template.
     * Use %children% for content, %classes% for CSS, %attributes% for HTML attributes.
     */
    public function wrapper(): string {
        return match ($this) {
            self::PRIMARY_BUTTON, self::SECONDARY_BUTTON
                => '<button class="%classes%" %attributes%>%children%</button>',
            self::SUCCESS_ALERT, self::DANGER_ALERT
                => '<div class="%classes%" %attributes% role="alert">%children%</div>',
        };
    }
    
    /**
     * Returns default CSS classes for this element.
     * These are merged with classes passed to fromElement().
     */
    public function classes(): array {
        return match ($this) {
            self::PRIMARY_BUTTON => ['btn', 'btn-primary'],
            self::SECONDARY_BUTTON => ['btn', 'btn-secondary'],
            self::SUCCESS_ALERT => ['alert', 'alert-success'],
            self::DANGER_ALERT => ['alert', 'alert-danger'],
        };
    }
    
    /**
     * Returns default HTML attributes for this element.
     * These are merged with attributes passed to fromElement().
     */
    public function attributes(): array {
        return match ($this) {
            self::PRIMARY_BUTTON, self::SECONDARY_BUTTON => ['type' => 'button'],
            default => [],
        };
    }
    
    /**
     * Returns a wrapper for each child element.
     * Use %child% as the placeholder. Return empty string if not needed.
     */
    public function childrenWrapper(): string {
        return ''; // No special wrapper needed
    }
}
```

### Step 3: Use Your Library

```php
use MaxPertici\Markup\MarkupFactory;

// Create components with your enum
$primaryBtn = MarkupFactory::fromElement(
    MyComponents::PRIMARY_BUTTON,
    ['Click Me']
);

$alert = MarkupFactory::fromElement(
    MyComponents::SUCCESS_ALERT,
    ['Operation completed successfully!'],
    ['alert-dismissible'] // Merge additional class
);

echo $primaryBtn->render();
// Output: <button class="btn btn-primary" type="button">Click Me</button>

echo $alert->render();
// Output: <div class="alert alert-success alert-dismissible" role="alert">Operation completed successfully!</div>
```

### Step 4: Share as Package (Optional)

Package your component library and share via Composer:

```bash
# Create composer.json
composer init

# Publish to Packagist
```

```php
// In other projects
composer require yourname/my-components

use YourName\MyComponents\MyComponents;
use MaxPertici\Markup\MarkupFactory;

$button = MarkupFactory::fromElement(MyComponents::PRIMARY_BUTTON, ['Click']);
```

---

## Error Handling

### Empty HTML

```php
// Returns empty Markup instance
$empty = MarkupFactory::fromHtml('');
$empty = MarkupFactory::fromHtml('   '); // Whitespace trimmed

echo $empty->render(); // Outputs nothing
```

### Malformed HTML

```php
// Unclosed tags - parsed as text
$malformed = MarkupFactory::fromHtml('<div>Unclosed');
// Returns Markup with "<div>Unclosed" as text child

// Mismatched tags - parsed as text
$mismatched = MarkupFactory::fromHtml('<div>Content</span>');
```

### Invalid Element Enum

```php
// PHP will catch this at compile time
// MarkupFactory::fromElement(MyComponents::TYPO, []); // ❌ Error: Undefined constant
```

---

## See Also

- [Markup Class](markup.html) - Main markup building class
- [MarkupSlot](markup-slot.html) - Slot system for named placeholders
- [MarkupFinder](markup-finder.html) - Search and query markup trees
- [Getting Started](getting-start.html) - Introduction and first steps

