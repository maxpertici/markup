<?php
/**
 * Interface for markup element definitions.
 *
 * @package MaxPertici\Markup\Contracts
 */

namespace MaxPertici\Markup\Contracts;

/**
 * Interface MarkupElementInterface
 *
 * Defines the contract for markup element configurations.
 * Implement this interface to create custom element enums.
 *
 * @since 1.0.0
 */
interface MarkupElementInterface {

	/**
	 * Gets the HTML wrapper template for this element.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper template with %children%, %classes%, and %attributes% placeholders.
	 */
	public function wrapper(): string;

	/**
	 * Gets the CSS classes for this element.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of CSS class names.
	 */
	public function classes(): array;

	/**
	 * Gets the HTML attributes for this element.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of attribute names and values.
	 */
	public function attributes(): array;

	/**
	 * Gets the children wrapper template (optional).
	 *
	 * @since 1.0.0
	 *
	 * @return string The children wrapper template with %child% placeholder, or empty string.
	 */
	public function childrenWrapper(): string;

}

