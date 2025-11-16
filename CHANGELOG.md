# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


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