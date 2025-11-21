---
layout: default
title: MarkupFinder
nav_order: 7
---

# MarkupFinder Class
{: .no_toc }

Search and query system for finding elements in a Markup tree.
{: .fs-6 .fw-300 }

<details markdown="block">
<summary>Table of Contents</summary>

* TOC
{:toc}

</details>

---

## Overview

The `MarkupFinder` class provides a powerful search and query system for finding `Markup` elements within a tree structure. It offers multiple search methods from simple class/tag lookups to complex CSS selector queries.

### Key Features

- **Class-based search** - Find elements by CSS class name
- **Tag search** - Find elements by HTML tag
- **Attribute search** - Find elements by HTML attributes
- **Slug search** - Find elements by their slug identifier
- **Custom callbacks** - Create complex search logic
- **CSS selectors** - Use familiar CSS selector syntax
- **Deep/shallow search** - Control recursion depth
- **First/all results** - Get first match or all matches

### When to Use MarkupFinder

**Use `find()` methods when:**
- You need to locate specific elements after building
- You want to modify elements conditionally
- You're building a component system
- You need to validate structure
- You're testing markup output

---

## Constructor

```php
public function __construct(Markup $markup)
```

Creates a new MarkupFinder instance for searching within a Markup tree.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$markup` | `Markup` | The root Markup instance to search in |

### Examples

```php
use MaxPertici\Markup\Markup;

// Build a markup tree
$page = new Markup('<div class="page">%children%</div>');
$page->children(
    new Markup('<header class="header">%children%</header>', children: ['Header']),
    new Markup('<main class="content">%children%</main>', children: ['Content']),
    new Markup('<footer class="footer">%children%</footer>', children: ['Footer'])
);

// Create a finder
$finder = new MarkupFinder($page);

// Or use the fluent API from Markup
$results = $page->find()->findByClass('header');
```

**Note:** You typically don't instantiate `MarkupFinder` directly. Use the `find()` method on `Markup` instances instead.

---

## Search by Class

### findByClass()

Finds all Markup elements that have a specific CSS class.

```php
findByClass(string $class, bool $deep = true): array
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$class` | `string` | The CSS class to search for |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Find all elements with 'active' class
$active = $markup->find()->findByClass('active');

// Shallow search (direct children only)
$directCards = $markup->find()->findByClass('card', false);

// Practical usage
$buttons = $markup->find()->findByClass('btn');
foreach ($buttons as $button) {
    $button->addClass('enhanced');
}
```

---

### findByClasses()

Finds all Markup elements that have **all** specified CSS classes (AND logic).

```php
findByClasses(array $classes, bool $deep = true): array
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$classes` | `array` | Array of CSS classes. Element must have all classes |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Find elements with both 'card' AND 'featured' classes
$featured = $markup->find()->findByClasses(['card', 'featured']);

// Find active primary buttons
$activePrimary = $markup->find()->findByClasses(['btn', 'btn-primary', 'active']);

// Process matching elements
$results = $markup->find()->findByClasses(['article', 'published']);
foreach ($results as $article) {
    echo $article->getAttribute('id');
}
```

---

## Search by Tag

### findByTag()

Finds all Markup elements that match a specific HTML tag.

```php
findByTag(string $tag, bool $deep = true): array
```

The tag name is extracted from the wrapper template (e.g., `<div...>` matches 'div').

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag` | `string` | The HTML tag name to search for (e.g., 'div', 'span', 'article') |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Find all div elements
$divs = $markup->find()->findByTag('div');

// Find all anchor tags
$links = $markup->find()->findByTag('a');
foreach ($links as $link) {
    // Add external link attributes
    if (str_contains($link->getAttribute('href'), 'http')) {
        $link->setAttribute('target', '_blank')
             ->setAttribute('rel', 'noopener noreferrer');
    }
}

// Find all sections
$sections = $markup->find()->findByTag('section');

// Shallow search for direct article children
$articles = $markup->find()->findByTag('article', false);
```

---

## Search by Attribute

### findByAttribute()

Finds all Markup elements that have a specific HTML attribute, optionally with a specific value.

```php
findByAttribute(string $name, ?string $value = null, bool $deep = true): array
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | `string` | The attribute name to search for |
| `$value` | `string\|null` | Optional. The attribute value to match. If null, checks only existence. Default: null |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Find all elements with a 'data-id' attribute (any value)
$withDataId = $markup->find()->findByAttribute('data-id');

// Find elements with specific attribute value
$mainElements = $markup->find()->findByAttribute('role', 'main');

// Find all navigation elements
$navs = $markup->find()->findByAttribute('role', 'navigation');
foreach ($navs as $nav) {
    $nav->addClass('enhanced-nav');
}

// Find all elements with a title attribute
$withTooltips = $markup->find()->findByAttribute('title');

// Find all disabled inputs
$disabled = $markup->find()->findByAttribute('disabled');
```

---

## Search by Slug

### findBySlug()

Finds all Markup elements that have a specific slug identifier.

```php
findBySlug(string $slug, bool $deep = true): array
```

Slugs are custom identifiers set with `$markup->slug('identifier')`.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$slug` | `string` | The slug to search for |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Create markup with slugs
$page = new Markup('<div>%children%</div>');
$header = new Markup('<header>%children%</header>');
$header->slug('main-header');
$page->children($header);

// Find by slug
$results = $page->find()->findBySlug('main-header');
$mainHeader = $results[0] ?? null;

if ($mainHeader) {
    $mainHeader->addClass('sticky');
}

// Useful for component identification
$hero = $markup->find()->findBySlug('hero-section');
$sidebar = $markup->find()->findBySlug('sidebar-widget');
$cta = $markup->find()->findBySlug('call-to-action');
```

---

## Custom Search

### search()

Finds all Markup elements using a custom callback function.

```php
search(callable $callback, bool $deep = true): array
```

The callback receives a `Markup` instance and should return `true` if it matches the search criteria.

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `callable` | Function that receives `Markup $markup` and returns `bool` |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

```php
// Find elements with both a class and an attribute
$results = $markup->find()->search(function($m) {
    return $m->hasClass('card') && $m->hasAttribute('data-id');
});

// Find elements with specific class pattern
$results = $markup->find()->search(function($m) {
    foreach ($m->classes() as $class) {
        if (str_starts_with($class, 'btn-')) {
            return true;
        }
    }
    return false;
});

// Complex search logic
$premium = $markup->find()->search(function($m) {
    return $m->hasClass('product') 
        && $m->getAttribute('data-price') !== null
        && (int)$m->getAttribute('data-price') > 100;
});

// Find elements with children
$withChildren = $markup->find()->search(function($m) {
    return count($m->getChildren()) > 0;
});
```

---

### findFirst()

Finds the **first** Markup element that matches a callback.

```php
findFirst(callable $callback, bool $deep = true): ?Markup
```

Stops searching as soon as a match is found (more efficient than `search()` for single results).

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `callable` | Function that receives `Markup $markup` and returns `bool` |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `Markup|null` - The first matching `Markup` instance or `null` if not found

#### Examples

```php
// Find first active element
$active = $markup->find()->findFirst(function($m) {
    return $m->hasClass('active');
});

if ($active) {
    echo "Found: " . $active->getAttribute('id');
}

// Find first element with error
$firstError = $markup->find()->findFirst(function($m) {
    return $m->hasClass('error') || $m->hasClass('invalid');
});

// Efficient single-element lookup
$mainNav = $markup->find()->findFirst(function($m) {
    return $m->hasAttribute('role', 'navigation') 
        && $m->hasClass('main-nav');
});
```

---

### all()

Gets all Markup instances in the tree (flattened).

```php
all(bool $deep = true): array
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `array` - Array of all `Markup` instances

#### Examples

```php
// Get all Markup instances
$allElements = $markup->find()->all();
echo "Total elements: " . count($allElements);

// Only direct children
$directChildren = $markup->find()->all(false);

// Process all elements
foreach ($markup->find()->all() as $element) {
    // Add a common class
    $element->addClass('processed');
}

// Count elements by tag
$tags = [];
foreach ($markup->find()->all() as $element) {
    $wrapper = $element->getWrapper();
    if (preg_match('/^<(\w+)/', $wrapper, $matches)) {
        $tag = $matches[1];
        $tags[$tag] = ($tags[$tag] ?? 0) + 1;
    }
}
print_r($tags);
```

---

### count()

Counts all Markup instances that match a callback.

```php
count(callable $callback, bool $deep = true): int
```

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `callable` | Function that receives `Markup $markup` and returns `bool` |
| `$deep` | `bool` | Optional. Whether to search recursively. Default: true |

#### Return

- `int` - The count of matching instances

#### Examples

```php
// Count active elements
$activeCount = $markup->find()->count(function($m) {
    return $m->hasClass('active');
});

// Count all buttons
$buttonCount = $markup->find()->count(function($m) {
    return $m->hasClass('btn');
});

// Count elements with data attributes
$dataCount = $markup->find()->count(function($m) {
    foreach ($m->attributes() as $name => $value) {
        if (str_starts_with($name, 'data-')) {
            return true;
        }
    }
    return false;
});

echo "Found {$buttonCount} buttons";
```

---

## CSS Selector Search

### css()

Finds Markup elements using CSS selector syntax.

```php
css(string $selector): array
```

This is the most powerful search method, supporting familiar CSS selector patterns.

#### Supported Selectors

**Basic Selectors:**
- `.class` - Class selector
- `tag` - Tag selector
- `#id` - ID selector
- `[attr]` - Attribute existence
- `[attr="value"]` - Attribute with value

**Combinations:**
- `tag.class` - Element with tag and class
- `tag[attr]` - Element with tag and attribute
- `.class1.class2` - Element with multiple classes
- `tag.class[attr="value"]` - Combined criteria

**Relationships:**
- `ancestor descendant` - Descendant combinator (space)
- `parent > child` - Direct child combinator

**Pseudo-classes:**
- `:has(selector)` - Contains matching descendant

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$selector` | `string` | The CSS selector string |

#### Return

- `array` - Array of matching `Markup` instances

#### Examples

**Basic Selectors:**

```php
// Find by class
$cards = $markup->find()->css('.card');

// Find by tag
$divs = $markup->find()->css('div');

// Find by ID
$main = $markup->find()->css('#main-content');

// Find by attribute
$required = $markup->find()->css('[required]');
$submitButtons = $markup->find()->css('[type="submit"]');

// Find by role
$navs = $markup->find()->css('[role="navigation"]');
```

**Combined Selectors:**

```php
// Tag with class
$activeDivs = $markup->find()->css('div.active');

// Multiple classes
$primaryButtons = $markup->find()->css('.btn.btn-primary');

// Tag with attribute
$externalLinks = $markup->find()->css('a[target="_blank"]');

// Complex combination
$results = $markup->find()->css('article.featured[data-priority="high"]');
```

**Descendant Relationships:**

```php
// All links inside nav
$navLinks = $markup->find()->css('nav a');

// All list items in active lists
$activeItems = $markup->find()->css('ul.active li');

// Nested classes
$cardTitles = $markup->find()->css('.card .title');

// Deep nesting
$results = $markup->find()->css('.container .row .col .card');
```

**Direct Child Combinator:**

```php
// Direct children only
$directItems = $markup->find()->css('ul > li');

// Direct nav links (not nested)
$topLinks = $markup->find()->css('nav > a');

// Mixed relationships
$results = $markup->find()->css('.container > .row div.card');
```

**:has() Pseudo-class:**

```php
// Find sections that contain an h2
$sectionsWithHeadings = $markup->find()->css('section:has(h2)');

// Find cards that contain images
$cardsWithImages = $markup->find()->css('.card:has(img)');

// Find articles that have active elements
$activeArticles = $markup->find()->css('article:has(.active)');

// Complex has queries
$results = $markup->find()->css('div:has(.error):has(.warning)');
```

**Complex Real-World Examples:**

```php
// Find active navigation links
$activeNavLinks = $markup->find()->css('nav > ul > li.active > a');

// Find featured product cards with images
$featured = $markup->find()->css('.product.featured:has(img)');

// Find sections with highlighted content
$highlighted = $markup->find()->css('section:has(.highlight)');

// Find primary buttons in active forms
$formButtons = $markup->find()->css('form.active button.btn-primary');

// Find error inputs in validation forms
$errors = $markup->find()->css('form:has(.error) input[type="text"]');
```

**Modifying Found Elements:**

```php
// Add class to all nav links
$links = $markup->find()->css('nav a');
foreach ($links as $link) {
    $link->addClass('nav-link');
}

// Set attributes on external links
$external = $markup->find()->css('a[target="_blank"]');
foreach ($external as $link) {
    $link->setAttribute('rel', 'noopener noreferrer');
}

// Enhance cards with images
$cardsWithImages = $markup->find()->css('.card:has(img)');
foreach ($cardsWithImages as $card) {
    $card->addClass('has-image');
}
```

---

## Practical Examples

### Component Enhancement System

```php
use MaxPertici\Markup\Markup;

// Build a page structure
$page = new Markup('<div class="page">%children%</div>');
$page->children(
    new Markup('<nav class="main-nav">%children%</nav>', children: [
        new Markup('<a href="/" class="nav-link">%children%</a>', children: ['Home']),
        new Markup('<a href="/about" class="nav-link active">%children%</a>', children: ['About']),
        new Markup('<a href="/contact" class="nav-link">%children%</a>', children: ['Contact'])
    ]),
    new Markup('<main>%children%</main>', children: [
        new Markup('<article class="post featured">%children%</article>', children: ['Featured Post']),
        new Markup('<article class="post">%children%</article>', children: ['Regular Post'])
    ])
);

// Enhance all active links
$activeLinks = $page->find()->css('.active');
foreach ($activeLinks as $link) {
    $link->setAttribute('aria-current', 'page');
}

// Add IDs to all articles
$articles = $page->find()->findByTag('article');
foreach ($articles as $index => $article) {
    $article->setAttribute('id', 'article-' . ($index + 1));
}

// Enhance featured posts
$featured = $page->find()->findByClasses(['post', 'featured']);
foreach ($featured as $post) {
    $post->addClass('enhanced')
         ->setAttribute('data-featured', 'true');
}

echo $page->render();
```

---

### Validation System

```php
function validateMarkupStructure(Markup $markup): array
{
    $errors = [];
    
    // Check for images without alt attributes
    $images = $markup->find()->css('img');
    foreach ($images as $img) {
        if (!$img->hasAttribute('alt')) {
            $errors[] = 'Image missing alt attribute: ' . ($img->getAttribute('src') ?? 'unknown');
        }
    }
    
    // Check for links without href
    $links = $markup->find()->findByTag('a');
    foreach ($links as $link) {
        if (!$link->hasAttribute('href')) {
            $errors[] = 'Link missing href attribute';
        }
    }
    
    // Check for external links without security attributes
    $externalLinks = $markup->find()->css('a[target="_blank"]');
    foreach ($externalLinks as $link) {
        if (!$link->hasAttribute('rel')) {
            $errors[] = 'External link missing rel attribute: ' . $link->getAttribute('href');
        }
    }
    
    // Check for forms without action
    $forms = $markup->find()->findByTag('form');
    foreach ($forms as $form) {
        if (!$form->hasAttribute('action')) {
            $errors[] = 'Form missing action attribute';
        }
    }
    
    return $errors;
}

// Usage
$errors = validateMarkupStructure($page);
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "⚠️  " . $error . "\n";
    }
}
```

---

### Auto-Enhancement Pipeline

```php
class MarkupEnhancer
{
    private Markup $markup;
    
    public function __construct(Markup $markup)
    {
        $this->markup = $markup;
    }
    
    public function enhanceAll(): self
    {
        $this->enhanceImages()
             ->enhanceLinks()
             ->enhanceLists()
             ->enhanceForms();
        
        return $this;
    }
    
    public function enhanceImages(): self
    {
        $images = $this->markup->find()->findByTag('img');
        
        foreach ($images as $img) {
            // Add loading attribute
            if (!$img->hasAttribute('loading')) {
                $img->setAttribute('loading', 'lazy');
            }
            
            // Add default alt if missing
            if (!$img->hasAttribute('alt')) {
                $img->setAttribute('alt', '');
            }
            
            // Add image class
            $img->addClass('enhanced-image');
        }
        
        return $this;
    }
    
    public function enhanceLinks(): self
    {
        $links = $this->markup->find()->findByTag('a');
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href') ?? '';
            
            // External links
            if (str_starts_with($href, 'http')) {
                $link->addClass('external-link');
                
                if ($link->getAttribute('target') === '_blank') {
                    $link->setAttribute('rel', 'noopener noreferrer');
                }
            }
            
            // Email links
            if (str_starts_with($href, 'mailto:')) {
                $link->addClass('email-link');
            }
            
            // Phone links
            if (str_starts_with($href, 'tel:')) {
                $link->addClass('phone-link');
            }
        }
        
        return $this;
    }
    
    public function enhanceLists(): self
    {
        // Find all list items
        $items = $this->markup->find()->findByTag('li');
        
        foreach ($items as $index => $item) {
            // Add zebra striping data attribute
            $item->setAttribute('data-index', (string)$index);
            
            if ($index % 2 === 0) {
                $item->addClass('even');
            } else {
                $item->addClass('odd');
            }
        }
        
        return $this;
    }
    
    public function enhanceForms(): self
    {
        // Find all required inputs
        $required = $this->markup->find()->findByAttribute('required');
        
        foreach ($required as $input) {
            $input->addClass('required-field');
            $input->setAttribute('aria-required', 'true');
        }
        
        // Find all submit buttons
        $submits = $this->markup->find()->css('[type="submit"]');
        
        foreach ($submits as $submit) {
            $submit->addClass('submit-button');
        }
        
        return $this;
    }
    
    public function render(): string
    {
        return $this->markup->render();
    }
}

// Usage
$page = new Markup('<div>%children%</div>');
// ... build page structure ...

$enhancer = new MarkupEnhancer($page);
echo $enhancer->enhanceAll()->render();
```

---

### Dynamic Content Injection

```php
function injectAnalytics(Markup $page, string $trackingId): void
{
    // Find all external links
    $externalLinks = $page->find()->search(function($m) {
        $href = $m->getAttribute('href') ?? '';
        return $m->getWrapper() && 
               str_contains($m->getWrapper(), '<a') &&
               str_starts_with($href, 'http');
    });
    
    foreach ($externalLinks as $link) {
        $link->setAttribute('data-analytics', 'external-link')
             ->setAttribute('data-tracking-id', $trackingId);
    }
    
    // Find all buttons
    $buttons = $page->find()->findByTag('button');
    
    foreach ($buttons as $button) {
        $button->setAttribute('data-analytics', 'button-click')
               ->setAttribute('data-tracking-id', $trackingId);
    }
    
    // Find all forms
    $forms = $page->find()->findByTag('form');
    
    foreach ($forms as $form) {
        $form->setAttribute('data-analytics', 'form-submit')
             ->setAttribute('data-tracking-id', $trackingId);
    }
}

// Usage
injectAnalytics($page, 'UA-123456-7');
```

---

### Testing and Debugging

```php
function debugMarkupStructure(Markup $markup): void
{
    echo "=== Markup Structure Debug ===\n\n";
    
    // Count all elements
    $total = $markup->find()->count(fn() => true);
    echo "Total elements: {$total}\n\n";
    
    // Count by tag
    echo "Elements by tag:\n";
    $tags = [];
    foreach ($markup->find()->all() as $element) {
        $wrapper = $element->getWrapper();
        if (preg_match('/^<(\w+)/', $wrapper, $matches)) {
            $tag = $matches[1];
            $tags[$tag] = ($tags[$tag] ?? 0) + 1;
        }
    }
    foreach ($tags as $tag => $count) {
        echo "  - {$tag}: {$count}\n";
    }
    
    echo "\nElements with classes:\n";
    $withClasses = $markup->find()->search(function($m) {
        return count($m->classes()) > 0;
    });
    echo "  Total: " . count($withClasses) . "\n";
    
    echo "\nElements with IDs:\n";
    $withIds = $markup->find()->findByAttribute('id');
    foreach ($withIds as $element) {
        echo "  - " . $element->getAttribute('id') . "\n";
    }
    
    echo "\nActive elements:\n";
    $active = $markup->find()->findByClass('active');
    echo "  Total: " . count($active) . "\n";
}

// Usage
debugMarkupStructure($page);
```

---

## Performance Considerations

### Search Method Performance

Performance comparison (10,000 iterations on complex tree):

```php
// Simple class search - FASTEST
$results = $markup->find()->findByClass('btn'); // ~2ms

// CSS selector (simple) - FAST
$results = $markup->find()->css('.btn'); // ~2ms (optimized to findByClass)

// CSS selector (complex) - MEDIUM
$results = $markup->find()->css('nav > li.active a'); // ~15ms

// Custom callback - VARIES
$results = $markup->find()->search(function($m) {
    return $m->hasClass('btn') && $m->hasAttribute('type');
}); // ~5ms

// :has() pseudo-class - SLOWER
$results = $markup->find()->css('section:has(.active)'); // ~25ms
```

### Best Practices

**1. Use specific methods when possible:**

```php
// ✅ Fast - uses optimized method
$buttons = $markup->find()->findByClass('btn');

// ❌ Slower - uses generic search
$buttons = $markup->find()->search(fn($m) => $m->hasClass('btn'));
```

**2. Use shallow search when appropriate:**

```php
// ✅ Fast - only searches direct children
$directChildren = $markup->find()->findByClass('child', false);

// ❌ Slower - searches entire tree
$directChildren = $markup->find()->findByClass('child', true);
```

**3. Use `findFirst()` when you need only one result:**

```php
// ✅ Fast - stops at first match
$active = $markup->find()->findFirst(fn($m) => $m->hasClass('active'));

// ❌ Slower - searches everything
$active = $markup->find()->search(fn($m) => $m->hasClass('active'))[0] ?? null;
```

**4. Cache search results:**

```php
// ✅ Good - search once, use multiple times
$links = $markup->find()->css('a');
foreach ($links as $link) {
    // Process each link
}

// ❌ Bad - searches multiple times
for ($i = 0; $i < 10; $i++) {
    $links = $markup->find()->css('a'); // Repeated search!
}
```

**5. Prefer simple selectors over complex ones:**

```php
// ✅ Fast
$buttons = $markup->find()->findByClass('btn');

// ❌ Slower (but more flexible)
$buttons = $markup->find()->css('button.btn[type="button"]');
```

---

## Troubleshooting

### Problem: Search Returns Empty Array

```php
// Common causes:

// 1. Element doesn't exist
$results = $markup->find()->findByClass('nonexistent'); // []

// 2. Case sensitivity in tags
$results = $markup->find()->findByTag('DIV'); // Should be 'div'

// 3. Shallow search when element is nested
$deep = $markup->find()->findByClass('nested', false); // Won't find nested elements

// ✅ Solution: use deep search (default)
$results = $markup->find()->findByClass('nested'); // or explicitly: true
```

### Problem: CSS Selector Not Working

```php
// ❌ Wrong: using complex CSS features not supported
$results = $markup->find()->css('div:nth-child(2)'); // Not supported

// ❌ Wrong: pseudo-elements
$results = $markup->find()->css('div::before'); // Not supported

// ✅ Supported selectors
$results = $markup->find()->css('div.class');
$results = $markup->find()->css('div > span');
$results = $markup->find()->css('div:has(.active)');
```

### Problem: Can't Find Elements with Dynamic Classes

```php
// Classes added after search won't be found in same search
$divs = $markup->find()->findByTag('div');
foreach ($divs as $div) {
    $div->addClass('processed');
}

// This finds the original divs, not the modified ones
$processed = $markup->find()->findByClass('processed'); // May be empty

// ✅ Solution: search after modification
$divs = $markup->find()->findByTag('div');
foreach ($divs as $div) {
    $div->addClass('processed');
}
// Now search again
$processed = $markup->find()->findByClass('processed'); // Found!
```

---

## Limitations

### Not Supported

The following CSS features are **not** supported:

- Pseudo-elements (`:before`, `::after`, etc.)
- Positional selectors (`:nth-child`, `:first-child`, `:last-child`, etc.)
- State pseudo-classes (`:hover`, `:focus`, `:visited`, etc.)
- Sibling combinators (`+`, `~`)
- Attribute selectors with operators (`^=`, `$=`, `*=`, `~=`, `|=`)
- Multiple selectors (`,` comma separator)

### Workarounds

```php
// Instead of :first-child
$first = $markup->find()->findByClass('item')[0] ?? null;

// Instead of :nth-child(2)
$second = $markup->find()->findByClass('item')[1] ?? null;

// Instead of comma separator (multiple selectors)
$headers = array_merge(
    $markup->find()->findByTag('h1'),
    $markup->find()->findByTag('h2')
);

// Instead of attribute^= (starts with)
$results = $markup->find()->search(function($m) {
    $id = $m->getAttribute('id') ?? '';
    return str_starts_with($id, 'item-');
});
```

---

## See Also

- [Markup Class](markup.html) - Main markup building class
- [MarkupFactory](markup-factory.html) - Quick creation methods
- [MarkupQueryBuilder](markup-query-builder.html) - Fluent query builder
- [MarkupCollection](markup-collection.html) - Collection methods
- [MarkupSlot](markup-slot.html) - Slot system for named placeholders
- [Getting Started](getting-started.html) - Introduction and first steps

