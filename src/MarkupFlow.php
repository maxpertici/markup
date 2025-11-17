<?php
/**
 * MarkupFlow class for flowing multiple elements within a single wrapper.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Class MarkupFlow
 *
 * Allows flowing multiple elements (strings, Markup instances, callables)
 * to be rendered together within a single children wrapper.
 *
 * This is useful when you want to combine text and Markup objects
 * in the same list item or wrapper element, creating a natural flow
 * of mixed content (text and inline elements).
 *
 * Example usage:
 * ```php
 * $list = new Markup(
 *     wrapper: '<ul>%children%</ul>',
 *     childrenWrapper: '<li>%child%</li>',
 *     children: [
 *         'Item 1',
 *         'Item 2',
 *         new MarkupFlow([
 *             'Item 3: ',
 *             $sublist,
 *             ' - additional text'
 *         ]),
 *     ],
 * );
 * ```
 *
 * @since 1.0.0
 */
class MarkupFlow {

	/**
	 * The items flowing together.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $items = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Array of items to flow together (strings, Markup instances, or callables).
	 */
	public function __construct( array $items = [] ) {
		$this->items = $items;
	}

	/**
	 * Gets the items in the flow.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of items.
	 */
	public function items(): array {
		return $this->items;
	}

	/**
	 * Adds one or more items to the flow.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed ...$items Items to add to the flow.
	 * @return self Returns $this for method chaining.
	 */
	public function add( ...$items ): self {
		foreach ( $items as $item ) {
			$this->items[] = $item;
		}
		return $this;
	}

	/**
	 * Checks if the flow is empty.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the flow has no items, false otherwise.
	 */
	public function isEmpty(): bool {
		return empty( $this->items );
	}

	/**
	 * Gets the number of items in the flow.
	 *
	 * @since 1.0.0
	 *
	 * @return int The number of items.
	 */
	public function count(): int {
		return count( $this->items );
	}

}

