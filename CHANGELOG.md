# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.0] - 2025-11-16

### Added

- **New Method: `MarkupFinder::css()`** - Find elements using CSS selector syntax for a more familiar and concise query interface.
  - **Simple selectors**: `.class`, `tag`, `#id`, `[attr]`, `[attr="value"]`
  - **Combined selectors**: `tag.class`, `.class1.class2`
  - **Descendant selector** (space): `parent child` - finds any descendant at any level
  - **Direct child selector** (`>`): `parent > child` - finds only immediate children
  - **:has() pseudo-class**: `parent:has(child)` - finds parents containing specific children
  - **Complex selectors**: Combine all above features, e.g., `header > nav:has(li.active) a`
  - Example: `$page->find()->css('nav li.active a')`
  
- **Performance optimization**: Simple selectors (`.class`, `tag`, `#id`) automatically use existing specialized methods (`findByClass()`, `findByTag()`, etc.) for optimal performance.
  - Benchmark shows only ~0.16ms difference (0.5% slower) compared to direct method calls
  
- Added comprehensive example in `examples/css-selector-example.php` demonstrating all selector types and performance comparison.
- Added detailed documentation in README.md with examples, supported selectors table, and performance tips.

### Implementation Details

- CSS selector parsing handles parentheses tracking for `:has()` nested selectors
- Combinator handling distinguishes between descendant (space) and direct child (`>`) relationships
- Recursive search algorithm with context awareness for efficient traversal
- Regex-based segment parsing extracts tag, classes, ID, attributes, and :has() pseudo-class
- Smart optimization: simple selectors bypass parsing and use existing fast paths

## [1.3.0]

### Added

- **New Class: `MarkupFactory`** - Dedicated factory class for creating Markup instances from various sources.
  
- **Factory Method: `MarkupFactory::fromString()`** - Create Markup instances with a simple wrapper, classes, attributes, and string content (first level only, content is not parsed).
  - Parameters: `tag`, `content`, `classes`, `attributes`
  - Perfect for quick element creation with single text content
  - Example: `MarkupFactory::fromString('div', 'Hello', ['container'], ['id' => 'main'])`
  
- **Factory Method: `MarkupFactory::fromHtml()`** - Recursively parse HTML strings into complete Markup trees with nested Markup instances.
  - Automatically extracts classes and attributes from HTML elements
  - Preserves entire nested structure as Markup instances
  - Supports self-closing tags (e.g., `<img/>`, `<br/>`)
  - Text nodes become string children
  - Example: `MarkupFactory::fromHtml('<div class="card"><h2>Title</h2><p>Text</p></div>')`
  
- Added comprehensive examples in `examples/factory-methods.php` and `examples/quick-test.php` demonstrating both factory methods.
- Added dedicated documentation in `FACTORY_METHODS.md`.

### Implementation Details

- `MarkupFactory` follows the Factory pattern for better separation of concerns
- `fromString()` creates a simple wrapper without parsing, making it very fast for basic elements
- `fromHtml()` uses custom regex-based HTML parsing for recursive tree building, supporting nested structures while remaining lightweight (no external dependencies)

## Initial 

- Core Markup class with fluent API
- Wrapper system with `%children%` placeholder
- Children wrapper system with `%child%` placeholder
- CSS class management (addClass, removeClass, hasClass, classes)
- HTML attributes management (setAttribute, removeAttribute, getAttribute, etc.)
- Slot system for named content placeholders
- Dual rendering modes: `render()` (returns string) and `print()` (streams output)
- Conditional method `when()` for conditional content
- Iterative method `each()` for looping through data
- Children manipulation methods (getChildren, setChildren, orderChildren)
- Metadata support (slug, description)
- MarkupInterface for consistent implementation
- Full documentation and examples