# Changelog

All notable changes to the Markup package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **DX Improvements**: Added 18 new methods to improve Developer Experience
  - `make()`: Static factory method for creating Markup instances
  - `mapChildren()`: Transform children with a callback function
  - `filterChildren()`: Filter children based on a condition
  - `isEmpty()`: Check if markup has no children
  - `countChildren()`: Count the number of children
  - `first()`: Get the first child element
  - `last()`: Get the last child element
  - `nth()`: Get the nth child element
  - `prepend()`: Add children at the beginning
  - `wrapChildren()`: Wrap all children in a new Markup
  - `tap()`: Execute callback without breaking the chain
  - `unless()`: Inverse of `when()` for conditional execution
  - `pipe()`: Pass instance to function and return result
  - `toArray()`: Convert Markup to associative array
  - `debug()`: Output debug information to error log
  - `stats()`: Get statistics about the Markup instance
  - `is()`: Check instance identity
  - `__clone()`: Deep cloning support for nested Markup instances
- New documentation file: `DX_IMPROVEMENTS.md` with comprehensive examples
- New test file: `examples/test-new-methods.php` demonstrating all new methods

### Changed
- None

### Deprecated
- None

### Removed
- None

### Fixed
- None

### Security
- None

