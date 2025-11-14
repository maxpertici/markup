<?php
/**
 * Markup Interface
 *
 * Defines the contract that all markup classes must implement.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Markup Interface
 *
 * This interface must be implemented by all markup classes.
 * It defines two core methods:
 * - getMarkup(): Returns the markup as a string (return mode)
 * - render(): Directly outputs the markup to the browser (echo mode)
 */
interface MarkupInterface {
	/**
	 * Retrieves the markup as a string.
	 *
	 * This method generates and returns the markup content as a string
	 * without outputting it. Use this when you need to store, manipulate,
	 * or pass the markup to another function.
	 *
	 * @since 1.0.0
	 *
	 * @return string The generated markup content.
	 */
	public function getMarkup(): string;

	/**
	 * Renders the markup directly to the browser.
	 *
	 * This method generates the markup and immediately outputs it using echo.
	 * Use this when you want to directly display the markup in a template
	 * or view without storing it in a variable.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render(): void;
}

