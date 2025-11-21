<?php
/**
 * Markup class for building and rendering HTML structures.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

use MaxPertici\Markup\Contracts\MarkupInterface;
use MaxPertici\Markup\MarkupFinder;

/**
 * Class Markup
 *
 * Provides a flexible system for building HTML markup with support for wrappers,
 * children elements, and both string generation and direct rendering modes.
 *
 * @since 1.0.0
 */
class Markup implements MarkupInterface {

	/**
	 * The generated markup string.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $markup = '';

	/**
	 * The slug identifier for this markup instance.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $slug = null;

	/**
	 * The description for this markup instance.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $description = null;

	/**
	 * The wrapper HTML template with %children% placeholder.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $wrapper = '';

	/**
	 * CSS classes for the wrapper element.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $wrapperClass = [];

	/**
	 * HTML attributes for the wrapper element.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $wrapperAttributes = [];

	/**
	 * The children wrapper HTML template with %child% placeholder.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $childrenWrapper = '';

	/**
	 * Array of child elements (strings, Markup instances, or callables).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $children = [];

	/**
	 * Array of registered MarkupSlot declarations keyed by slot name.
	 *
	 * @since 1.0.0
	 * @var array<string, MarkupSlot>
	 */
	protected array $declaredSlots = [];

	/**
	 * Array of slot content keyed by slot name.
	 *
	 * @since 1.0.0
	 * @var array<string, array>
	 */
	protected array $slotsContent = [];

	/**
	 * Whether to output content directly or store it.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $streaming = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wrapper            Optional. The wrapper HTML template. Default empty string.
	 * @param array  $wrapperClass       Optional. CSS classes for the wrapper. Default empty array.
	 * @param array  $wrapperAttributes  Optional. HTML attributes for the wrapper. Default empty array.
	 * @param string $childrenWrapper    Optional. The children wrapper HTML template. Default empty string.
	 * @param array  $children           Optional. Array of child elements. Default empty array.
	 */
	public function __construct(
		string $wrapper = '',
		array $wrapperClass = [],
		array $wrapperAttributes = [],
		string $childrenWrapper = '',
		array $children = [],
	) {
		$this->wrapper            = $wrapper;
		$this->wrapperClass       = $wrapperClass;
		$this->wrapperAttributes  = $wrapperAttributes;
		$this->childrenWrapper    = $childrenWrapper;
		$this->children           = $children;
	}

	/**
	 * Creates a new Markup instance with optional wrapper.
	 *
	 * This static factory method provides a convenient way to create
	 * Markup instances without using the new keyword.
	 *
	 * Example usage:
	 * ```php
	 * $markup = Markup::make('<div>%children%</div>')
	 *     ->addClass('container')
	 *     ->children('Content');
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param string $wrapper Optional. The wrapper HTML template. Default empty string.
	 * @return self A new Markup instance.
	 */
	public static function make( string $wrapper = '' ): self {
		return new self( $wrapper );
	}

	/**
	 * Magic method to handle deep cloning of Markup instances.
	 *
	 * This ensures that when a Markup instance is cloned, all nested
	 * Markup instances in children and slots are also cloned, creating
	 * a truly independent copy.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __clone() {
		// Deep clone children
		foreach ( $this->children as $key => $child ) {
			if ( $child instanceof Markup || $child instanceof MarkupSlot || $child instanceof MarkupFlow ) {
				$this->children[ $key ] = clone $child;
			}
		}

		// Deep clone declared slots
		foreach ( $this->declaredSlots as $name => $slot ) {
			$this->declaredSlots[ $name ] = clone $slot;
		}

		// Deep clone slot content
		foreach ( $this->slotsContent as $slotName => $items ) {
			foreach ( $items as $key => $item ) {
				if ( $item instanceof Markup || $item instanceof MarkupSlot || $item instanceof MarkupFlow ) {
					$this->slotsContent[ $slotName ][ $key ] = clone $item;
				}
			}
		}
	}

	/**
	 * Sets or retrieves the slug identifier for this markup instance.
	 *
	 * When called with a parameter, sets the slug and returns $this for method chaining.
	 * When called without a parameter, returns the current slug value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $slug Optional. The slug to set. If null, acts as a getter.
	 * @return self|string|null Returns $this when setting (for chaining), or the slug value when getting.
	 */
	public function slug( ?string $slug = null ) {
		if ( null === $slug ) {
			return $this->slug;
		}

		$this->slug = $slug;
		return $this;
	}

	/**
	 * Gets the wrapper template for this markup instance.
	 *
	 * This method is useful for introspection and search operations.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper HTML template.
	 */
	public function getWrapper(): string {
		return $this->wrapper;
	}

	/**
	 * Sets or retrieves the description for this markup instance.
	 *
	 * When called with a parameter, sets the description and returns $this for method chaining.
	 * When called without a parameter, returns the current description value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $description Optional. The description to set. If null, acts as a getter.
	 * @return self|string|null Returns $this when setting (for chaining), or the description value when getting.
	 */
	public function description( ?string $description = null ) {
		if ( null === $description ) {
			return $this->description;
		}

		$this->description = $description;
		return $this;
	}

	/**
	 * Adds one or more CSS classes to the wrapper element.
	 *
	 * Accepts a single class name, multiple class names as separate arguments,
	 * or an array of class names. Duplicate classes are automatically prevented.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array ...$classes CSS class name(s) to add.
	 * @return self Returns $this for method chaining.
	 */
	public function addClass( ...$classes ): self {
		foreach ( $classes as $class ) {
			if ( is_array( $class ) ) {
				foreach ( $class as $c ) {
					if ( ! in_array( $c, $this->wrapperClass, true ) ) {
						$this->wrapperClass[] = $c;
					}
				}
			} else {
				if ( ! in_array( $class, $this->wrapperClass, true ) ) {
					$this->wrapperClass[] = $class;
				}
			}
		}
		return $this;
	}

	/**
	 * Removes one or more CSS classes from the wrapper element.
	 *
	 * Accepts a single class name, multiple class names as separate arguments,
	 * or an array of class names.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array ...$classes CSS class name(s) to remove.
	 * @return self Returns $this for method chaining.
	 */
	public function removeClass( ...$classes ): self {
		foreach ( $classes as $class ) {
			if ( is_array( $class ) ) {
				foreach ( $class as $c ) {
					$this->wrapperClass = array_diff( $this->wrapperClass, array( $c ) );
				}
			} else {
				$this->wrapperClass = array_diff( $this->wrapperClass, array( $class ) );
			}
		}
		// Re-index array to avoid gaps in numeric keys
		$this->wrapperClass = array_values( $this->wrapperClass );
		return $this;
	}

	/**
	 * Checks if the wrapper has a specific CSS class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The CSS class name to check.
	 * @return bool True if the class exists, false otherwise.
	 */
	public function hasClass( string $class ): bool {
		return in_array( $class, $this->wrapperClass, true );
	}

	/**
	 * Gets or sets all CSS classes for the wrapper element.
	 *
	 * When called without a parameter, returns the current classes array.
	 * When called with a parameter, replaces all existing classes and returns $this for chaining.
	 * To add or remove specific classes, use addClass() or removeClass() instead.
	 *
	 * @since 1.0.0
	 *
	 * @param array|null $classes Optional. Array of CSS class names to set. If null, acts as a getter.
	 * @return self|array Returns $this when setting (for chaining), or the classes array when getting.
	 */
	public function classes( ?array $classes = null ) {
		if ( null === $classes ) {
			return $this->wrapperClass;
		}

		$this->wrapperClass = array_values( array_unique( $classes ) );
		return $this;
	}

	/**
	 * Sets or removes an HTML attribute on the wrapper element.
	 *
	 * If value is provided, sets the attribute. If value is null, removes the attribute.
	 * For boolean attributes (e.g., disabled, readonly), pass the attribute name as the value.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $name  The attribute name.
	 * @param string|null $value The attribute value. Pass null to remove the attribute.
	 * @return self Returns $this for method chaining.
	 */
	public function setAttribute( string $name, ?string $value ): self {
		if ( null === $value ) {
			unset( $this->wrapperAttributes[ $name ] );
		} else {
			$this->wrapperAttributes[ $name ] = $value;
		}
		return $this;
	}

	/**
	 * Removes an HTML attribute from the wrapper element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The attribute name to remove.
	 * @return self Returns $this for method chaining.
	 */
	public function removeAttribute( string $name ): self {
		unset( $this->wrapperAttributes[ $name ] );
		return $this;
	}

	/**
	 * Checks if the wrapper has a specific HTML attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The attribute name to check.
	 * @return bool True if the attribute exists, false otherwise.
	 */
	public function hasAttribute( string $name ): bool {
		return isset( $this->wrapperAttributes[ $name ] );
	}

	/**
	 * Gets the value of a specific HTML attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The attribute name.
	 * @return string|null The attribute value or null if not set.
	 */
	public function getAttribute( string $name ): ?string {
		return $this->wrapperAttributes[ $name ] ?? null;
	}

	/**
	 * Gets or sets all HTML attributes for the wrapper element.
	 *
	 * When called without a parameter, returns the current attributes array.
	 * When called with a parameter, replaces all existing attributes and returns $this for chaining.
	 * To add or remove specific attributes, use setAttribute() or removeAttribute() instead.
	 *
	 * @since 1.0.0
	 *
	 * @param array|null $attributes Optional. Associative array of attribute names and values. If null, acts as a getter.
	 * @return self|array Returns $this when setting (for chaining), or the attributes array when getting.
	 */
	public function attributes( ?array $attributes = null ) {
		if ( null === $attributes ) {
			return $this->wrapperAttributes;
		}

		$this->wrapperAttributes = $attributes;
		return $this;
	}

	/**
	 * Adds child elements to the markup.
	 *
	 * Children can be strings, Markup instances, MarkupSlot declarations, or callable functions.
	 * When a MarkupSlot object is added, it is automatically registered for later reference.
	 * Multiple children can be passed as separate arguments or as an array.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed ...$children Child elements to add (strings, Markup instances, MarkupSlot objects, or callables).
	 * @return self Returns $this for method chaining.
	 */
	public function children( ...$children ): self {
		foreach ( $children as $child ) {
			if ( is_array( $child ) ) {
				// If an array is passed, add each element
				foreach ( $child as $item ) {
					$this->addChildItem( $item );
				}
			} else {
				$this->addChildItem( $child );
			}
		}
		return $this;
	}

	/**
	 * Appends child elements to the markup (alias of children()).
	 *
	 * This is a semantic alias for children() that makes the API more intuitive.
	 * Works exactly like children() but with clearer "append" intent.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed ...$children Child elements to append (strings, Markup instances, MarkupSlot objects, or callables).
	 * @return self Returns $this for method chaining.
	 */
	public function append( ...$children ): self {
		return $this->children( ...$children );
	}

	/**
	 * Adds child elements at the beginning of the children array.
	 *
	 * Children can be strings, Markup instances, MarkupSlot declarations, or callable functions.
	 * Multiple children can be passed as separate arguments or as an array.
	 *
	 * Example usage:
	 * ```php
	 * $markup->prepend('First item', new Markup('<div>%children%</div>'));
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param mixed ...$children Child elements to prepend (strings, Markup instances, MarkupSlot objects, or callables).
	 * @return self Returns $this for method chaining.
	 */
	public function prepend( ...$children ): self {
		// Process children in reverse order to maintain the correct order
		foreach ( array_reverse( $children ) as $child ) {
			if ( is_array( $child ) ) {
				// If an array is passed, prepend each element in reverse order
				foreach ( array_reverse( $child ) as $item ) {
					array_unshift( $this->children, $item );

					// If it's a MarkupSlot object, register it
					if ( $item instanceof MarkupSlot ) {
						$this->declaredSlots[ $item->name() ] = $item;
					}
				}
			} else {
				array_unshift( $this->children, $child );

				// If it's a MarkupSlot object, register it
				if ( $child instanceof MarkupSlot ) {
					$this->declaredSlots[ $child->name() ] = $child;
				}
			}
		}

		return $this;
	}

	/**
	 * Gets the current children array.
	 *
	 * When called without parameters, returns the array of all child elements.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of child elements.
	 */
	public function getChildren(): array {
		return $this->children;
	}

	/**
	 * Replaces the entire children array with a new one.
	 *
	 * This method allows you to completely replace the children array,
	 * useful for reordering or filtering children. All MarkupSlot objects in
	 * the new array will be automatically registered.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children The new children array.
	 * @return self Returns $this for method chaining.
	 */
	public function setChildren( array $children ): self {
		// Reset children and declared slots
		$this->children      = [];
		$this->declaredSlots = [];

		// Add each item, registering Slots as we go
		foreach ( $children as $child ) {
			$this->addChildItem( $child );
		}

		return $this;
	}

	/**
	 * Transforms children elements using a callback function.
	 *
	 * The callback receives each child element and should return the transformed element.
	 * This method modifies the children array in place.
	 *
	 * Example usage:
	 * ```php
	 * $markup->mapChildren(function($child) {
	 *     if ($child instanceof Markup) {
	 *         $child->addClass('mapped');
	 *     }
	 *     return $child;
	 * });
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to transform each child. Receives ($child).
	 * @return self Returns $this for method chaining.
	 */
	public function mapChildren( callable $callback ): self {
		$mapped = array_map( $callback, $this->children );
		return $this->setChildren( $mapped );
	}

	/**
	 * Filters children elements based on a callback condition.
	 *
	 * The callback receives each child element and should return true to keep it,
	 * false to remove it. This method modifies the children array in place.
	 *
	 * Example usage:
	 * ```php
	 * $markup->filterChildren(function($child) {
	 *     return $child instanceof Markup && $child->hasClass('keep');
	 * });
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to test each child. Receives ($child).
	 * @return self Returns $this for method chaining.
	 */
	public function filterChildren( callable $callback ): self {
		$filtered = array_filter( $this->children, $callback );
		return $this->setChildren( array_values( $filtered ) );
	}

	/**
	 * Checks if the markup has no children.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the markup has no children, false otherwise.
	 */
	public function isEmpty(): bool {
		return empty( $this->children );
	}

	/**
	 * Counts the number of direct children.
	 *
	 * @since 1.0.0
	 *
	 * @return int The number of children.
	 */
	public function countChildren(): int {
		return count( $this->children );
	}

	/**
	 * Gets the first child element.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The first child element or null if no children.
	 */
	public function first() {
		return $this->children[0] ?? null;
	}

	/**
	 * Gets the last child element.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The last child element or null if no children.
	 */
	public function last() {
		return $this->children[ count( $this->children ) - 1 ] ?? null;
	}

	/**
	 * Gets the nth child element (0-indexed).
	 *
	 * @since 1.0.0
	 *
	 * @param int $index The index of the child to retrieve (0-based).
	 * @return mixed The nth child element or null if index is out of bounds.
	 */
	public function nth( int $index ) {
		return $this->children[ $index ] ?? null;
	}

	/**
	 * Wraps all children within a new Markup element.
	 *
	 * This method takes all current children and wraps them in a new Markup instance
	 * with the specified wrapper template. The wrapped children become the only child
	 * of the current Markup.
	 *
	 * Example usage:
	 * ```php
	 * $markup = new Markup('<div>%children%</div>')
	 *     ->children('Item 1', 'Item 2', 'Item 3')
	 *     ->wrapChildren('<ul>%children%</ul>');
	 * // Result: <div><ul>Item 1Item 2Item 3</ul></div>
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param string $wrapper            The wrapper HTML template for the new Markup.
	 * @param array  $wrapperClass       Optional. CSS classes for the wrapper. Default empty array.
	 * @param array  $wrapperAttributes  Optional. HTML attributes for the wrapper. Default empty array.
	 * @param string $childrenWrapper    Optional. The children wrapper HTML template. Default empty string.
	 * @return self Returns $this for method chaining.
	 */
	public function wrapChildren(
		string $wrapper,
		array $wrapperClass = [],
		array $wrapperAttributes = [],
		string $childrenWrapper = ''
	): self {
		$wrapped = new Markup(
			$wrapper,
			$wrapperClass,
			$wrapperAttributes,
			$childrenWrapper,
			$this->children
		);

		// Copy declared slots from this instance to the wrapper
		foreach ( $this->declaredSlots as $name => $slot ) {
			$wrapped->declaredSlots[ $name ] = $slot;
		}

		// Replace children with the wrapped version
		$this->children      = [ $wrapped ];
		$this->declaredSlots = [];

		return $this;
	}

	/**
	 * Orders children using a callback function.
	 *
	 * The callback receives the current children array and should return
	 * a reordered array. This method is chainable for fluent interfaces.
	 *
	 * Example usage:
	 * ```php
	 * $markup->orderChildren(fn($children) => array_reverse($children));
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback Function that receives children array and returns reordered array.
	 * @return self Returns $this for method chaining.
	 */
	public function orderChildren( callable $callback ): self {
		$reordered = call_user_func( $callback, $this->children );
		return $this->setChildren( $reordered );
	}

	/**
	 * Adds a single child item, detecting and registering MarkupSlot objects.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The child item to add.
	 * @return void
	 */
	private function addChildItem( $item ): void {
		// If it's a MarkupSlot object, register it
		if ( $item instanceof MarkupSlot ) {
			$this->declaredSlots[ $item->name() ] = $item;
		}

		// Add to children array
		$this->children[] = $item;
	}

	/**
	 * Gets all declared MarkupSlot objects, optionally filtered by names.
	 *
	 * Returns MarkupSlot objects that were added as children.
	 * When called without parameter, returns all slots.
	 * When called with an array of names, returns only those slots.
	 *
	 * @since 1.0.0
	 *
	 * @param array|null $names Optional. Array of slot names to filter. If null, returns all slots.
	 * @return array<string, MarkupSlot> Array of MarkupSlot objects keyed by slot name.
	 */
	public function slots( ?array $names = null ): array {
		if ( null === $names ) {
			return $this->declaredSlots;
		}

		return array_filter(
			$this->declaredSlots,
			fn( $key ) => in_array( $key, $names, true ),
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Gets a specific declared MarkupSlot object by name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the slot to retrieve.
	 * @return MarkupSlot|null The MarkupSlot object if found, null otherwise.
	 */
	public function getSlot( string $name ): ?MarkupSlot {
		return $this->declaredSlots[ $name ] ?? null;
	}

	/**
	 * Adds content to a named slot.
	 *
	 * Accepts arrays or any supported type (string, Markup, MarkupSlot, callable).
	 * In the wrapper template, use %slot:name% placeholder to position the slot.
	 * Multiple elements can be added to the same slot.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The name of the slot.
	 * @param mixed  $items Items to add - can be an array or any supported type (string, Markup, MarkupSlot, callable).
	 * @return self Returns $this for method chaining.
	 */
	public function slot( string $name, $items ): self {
		// Initialize slot content array if not exists
		if ( ! isset( $this->slotsContent[ $name ] ) ) {
			$this->slotsContent[ $name ] = [];
		}

		// If items is an array, add each element
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$this->addSlotItem( $name, $item );
			}
		} else {
			// Single item
			$this->addSlotItem( $name, $items );
		}

		return $this;
	}

	/**
	 * Adds a single item to a slot's content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The slot name.
	 * @param mixed  $item The item to add.
	 * @return void
	 */
	private function addSlotItem( string $name, $item ): void {
		// Register MarkupSlot objects if they're being added as slot content
		if ( $item instanceof MarkupSlot ) {
			$this->declaredSlots[ $item->name() ] = $item;
		}

		$this->slotsContent[ $name ][] = $item;
	}

	/**
	 * Gets the names of all declared slots.
	 *
	 * Returns an array of slot names that have been added as MarkupSlot children.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of declared slot names.
	 */
	public function slotNames(): array {
		return array_keys( $this->declaredSlots );
	}

	/**
	 * Gets the names of all slots that have been filled with content.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of slot names that have been filled.
	 */
	public function filledSlotNames(): array {
		$filled = [];

		foreach ( $this->slotsContent as $name => $items ) {
			if ( ! empty( $items ) ) {
				$filled[] = $name;
			}
		}

		return $filled;
	}

	/**
	 * Checks if a slot has been declared (added as a MarkupSlot child).
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the slot to check.
	 * @return bool True if the slot has been declared, false otherwise.
	 */
	public function hasSlot( string $name ): bool {
		return isset( $this->declaredSlots[ $name ] );
	}

	/**
	 * Checks if a slot has been filled with content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the slot to check.
	 * @return bool True if the slot has been filled, false otherwise.
	 */
	public function isSlotFilled( string $name ): bool {
		return isset( $this->slotsContent[ $name ] ) && ! empty( $this->slotsContent[ $name ] );
	}

	/**
	 * Gets information about all declared slots.
	 *
	 * Returns an associative array with slot names as keys and their information.
	 * Each slot includes: name, description, wrapper, filled status, and items count.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of slot information.
	 */
	public function getSlotsInfo(): array {
		$info = [];

		foreach ( $this->declaredSlots as $name => $slot ) {
			$slotInfo                = $slot->toArray();
			$slotInfo['filled']      = $this->isSlotFilled( $name );
			$slotInfo['items_count'] = isset( $this->slotsContent[ $name ] ) ? count( $this->slotsContent[ $name ] ) : 0;
			$info[ $name ]           = $slotInfo;
		}

		return $info;
	}

	/**
	 * Conditionally executes a callback based on a boolean condition.
	 *
	 * This method allows conditional method chaining. If the condition is true,
	 * the callback is executed with the current Markup instance as its parameter.
	 * The method always returns $this for continued chaining, regardless of the condition.
	 *
	 * Example usage:
	 * ```php
	 * $markup = new Markup()
	 *     ->children('Always added')
	 *     ->when($user_is_admin, function($markup) {
	 *         $markup->children('Admin only content');
	 *     })
	 *     ->children('Always added too');
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param bool     $condition The condition to evaluate.
	 * @param callable $callback  The callback to execute if condition is true. Receives $this as parameter.
	 * @return self Returns $this for method chaining.
	 */
	public function when( bool $condition, callable $callback ): self {
		if ( $condition ) {
			call_user_func( $callback, $this );
		}
		return $this;
	}

	/**
	 * Conditionally executes a callback when the condition is false.
	 *
	 * This is the inverse of when(). If the condition is false,
	 * the callback is executed with the current Markup instance as its parameter.
	 *
	 * Example usage:
	 * ```php
	 * $markup = new Markup()
	 *     ->unless($user_is_logged_in, function($markup) {
	 *         $markup->children('Please log in');
	 *     });
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param bool     $condition The condition to evaluate.
	 * @param callable $callback  The callback to execute if condition is false. Receives $this as parameter.
	 * @return self Returns $this for method chaining.
	 */
	public function unless( bool $condition, callable $callback ): self {
		return $this->when( ! $condition, $callback );
	}

	/**
	 * Executes a callback without interrupting method chaining.
	 *
	 * This method is useful for performing side effects (like logging or debugging)
	 * in the middle of a fluent chain without breaking the flow.
	 *
	 * Example usage:
	 * ```php
	 * $markup = new Markup()
	 *     ->children('Content')
	 *     ->tap(function($markup) {
	 *         error_log('Current children count: ' . $markup->countChildren());
	 *     })
	 *     ->addClass('active');
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to execute. Receives $this as parameter.
	 * @return self Returns $this for method chaining.
	 */
	public function tap( callable $callback ): self {
		call_user_func( $callback, $this );
		return $this;
	}

	/**
	 * Passes the instance to a function and returns the result.
	 *
	 * Unlike tap(), this method returns the result of the callback instead of $this,
	 * breaking the fluent chain. Useful for transforming or extracting data.
	 *
	 * Example usage:
	 * ```php
	 * $html = $markup->pipe(function($markup) {
	 *     return $markup->render();
	 * });
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to execute. Receives $this as parameter.
	 * @return mixed The result of the callback.
	 */
	public function pipe( callable $callback ) {
		return call_user_func( $callback, $this );
	}

	/**
	 * Iterates over an array and executes a callback for each element.
	 *
	 * This method allows looping through data to generate repetitive markup.
	 * The callback receives three parameters: the current item value, the item key/index,
	 * and the Markup instance for method chaining.
	 *
	 * Example usage:
	 * ```php
	 * $users = [
	 *     ['name' => 'John', 'email' => 'john@example.com'],
	 *     ['name' => 'Jane', 'email' => 'jane@example.com']
	 * ];
	 *
	 * $markup = new Markup('<ul>%children%</ul>')
	 *     ->each($users, function($user, $index, $markup) {
	 *         $markup->children(
	 *             new Markup('<li>%children%</li>')
	 *                 ->children($user['name'] . ' - ' . $user['email'])
	 *         );
	 *     });
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param array    $items    The array to iterate over.
	 * @param callable $callback The callback to execute for each item. Receives ($item, $key, $markup).
	 * @return self Returns $this for method chaining.
	 */
	public function each( array $items, callable $callback ): self {
		foreach ( $items as $key => $item ) {
			call_user_func( $callback, $item, $key, $this );
		}
		return $this;
	}

	/**
	 * Creates a MarkupQueryBuilder instance for searching within this markup tree.
	 *
	 * The query builder provides a fluent, chainable interface for building
	 * search queries with multiple criteria. It returns a MarkupCollection
	 * that can be further transformed.
	 *
	 * Example usage:
	 * ```php
	 * // Fluent chainable queries
	 * $cards = $markup->find()->class('card')->get();
	 *
	 * // Multiple criteria
	 * $active = $markup->find()
	 *     ->class('item')
	 *     ->hasAttribute('data-active', 'true')
	 *     ->first();
	 *
	 * // Custom callbacks
	 * $elements = $markup->find()
	 *     ->where(fn($m) => $m->hasClass('important'))
	 *     ->get();
	 *
	 * // Collection transformations
	 * $prices = $markup->find()
	 *     ->class('product')
	 *     ->get()
	 *     ->map(fn($el) => $el->getAttribute('price'))
	 *     ->filter(fn($price) => $price > 50);
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @return MarkupQueryBuilder The query builder instance for method chaining.
	 */
	public function find(): MarkupQueryBuilder {
		return new MarkupQueryBuilder( $this );
	}

	/**
	 * Extracts plain text content from this markup and its children.
	 *
	 * This method recursively extracts all text content from the markup tree,
	 * ignoring all HTML tags (wrapper, childrenWrapper, etc.). It processes:
	 * - String children: included as-is
	 * - Markup children: recursively extracts their text
	 * - MarkupFlow: extracts text from all items
	 * - Slots: optionally includes their content if filled
	 * - Callables: optionally executes them to capture output
	 *
	 * Example usage:
	 * ```php
	 * $markup = new Markup(
	 *     wrapper: '<div class="content">%children%</div>',
	 *     children: [
	 *         'Hello ',
	 *         new Markup('<strong>%children%</strong>', children: ['World']),
	 *         '!'
	 *     ]
	 * );
	 *
	 * echo $markup->text(); // Outputs: "Hello World!"
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param bool $recursive         Whether to extract text from nested Markup children. Default true.
	 * @param bool $includeSlots      Whether to include slot content. Default true.
	 * @param bool $executeCallables  Whether to execute callables to capture their output. Default true.
	 * @return string The extracted plain text content.
	 */
	public function text( bool $recursive = true, bool $includeSlots = true, bool $executeCallables = true ): string {
		return $this->extractText( $this->children, $recursive, $includeSlots, $executeCallables );
	}

	/**
	 * Converts the Markup instance to an associative array.
	 *
	 * This method is useful for debugging, serialization, or inspection purposes.
	 * It returns a snapshot of the current state of the Markup instance.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array containing the markup's properties.
	 */
	public function toArray(): array {
		return array(
			'slug'               => $this->slug,
			'description'        => $this->description,
			'wrapper'            => $this->wrapper,
			'wrapperClass'       => $this->wrapperClass,
			'wrapperAttributes'  => $this->wrapperAttributes,
			'childrenWrapper'    => $this->childrenWrapper,
			'children_count'     => count( $this->children ),
			'slots'              => $this->getSlotsInfo(),
		);
	}

	/**
	 * Outputs debug information about the Markup instance.
	 *
	 * This method logs the Markup's properties to the error log when WP_DEBUG is enabled.
	 * It's useful for debugging during development.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns $this for method chaining.
	 */
	public function debug(): self {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( print_r( $this->toArray(), true ) );
		}
		return $this;
	}

	/**
	 * Gets statistics about the Markup instance.
	 *
	 * This method returns an array with various counts and metrics
	 * about the current state of the Markup.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array containing statistics.
	 */
	public function stats(): array {
		return array(
			'children_count'     => count( $this->children ),
			'classes_count'      => count( $this->wrapperClass ),
			'attributes_count'   => count( $this->wrapperAttributes ),
			'slots_count'        => count( $this->declaredSlots ),
			'filled_slots_count' => count( $this->filledSlotNames() ),
			'is_empty'           => $this->isEmpty(),
			'has_wrapper'        => ! empty( $this->wrapper ),
		);
	}

	/**
	 * Checks if this is the same Markup instance as another.
	 *
	 * This performs an identity check (===), not an equality check.
	 * Two Markup instances with identical properties are not considered "the same".
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $markup The Markup instance to compare with.
	 * @return bool True if this is the exact same instance, false otherwise.
	 */
	public function is( Markup $markup ): bool {
		return $this === $markup;
	}

	/**
	 * Recursively extracts text from an array of children.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children          The children array to process.
	 * @param bool  $recursive         Whether to recursively extract from nested Markup.
	 * @param bool  $includeSlots      Whether to include slot content.
	 * @param bool  $executeCallables  Whether to execute callables.
	 * @return string The extracted text.
	 */
	private function extractText( array $children, bool $recursive, bool $includeSlots, bool $executeCallables ): string {
		$text = '';

		foreach ( $children as $child ) {
			// Handle MarkupSlot objects
			if ( $child instanceof MarkupSlot ) {
				if ( $includeSlots ) {
					$text .= $this->extractSlotText( $child, $recursive, $includeSlots, $executeCallables );
				}
				continue;
			}

			// Handle MarkupFlow objects
			if ( $child instanceof MarkupFlow ) {
				foreach ( $child->items() as $item ) {
					$text .= $this->extractItemText( $item, $recursive, $includeSlots, $executeCallables );
				}
				continue;
			}

			// Handle regular items
			$text .= $this->extractItemText( $child, $recursive, $includeSlots, $executeCallables );
		}

		return $text;
	}

	/**
	 * Extracts text from a single item (string, Markup, or callable).
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item              The item to process.
	 * @param bool  $recursive         Whether to recursively extract from nested Markup.
	 * @param bool  $includeSlots      Whether to include slot content.
	 * @param bool  $executeCallables  Whether to execute callables.
	 * @return string The extracted text.
	 */
	private function extractItemText( $item, bool $recursive, bool $includeSlots, bool $executeCallables ): string {
		// Handle Markup objects
		if ( $item instanceof Markup ) {
			if ( $recursive ) {
				return $item->text( $recursive, $includeSlots, $executeCallables );
			}
			return '';
		}

		// Handle strings
		if ( is_string( $item ) ) {
			return $item;
		}

		// Handle callables
		if ( is_callable( $item ) && $executeCallables ) {
			ob_start();
			call_user_func( $item );
			$output = ob_get_clean();
			// Strip tags from callable output
			return strip_tags( $output );
		}

		return '';
	}

	/**
	 * Extracts text from a MarkupSlot and its content.
	 *
	 * @since 1.0.0
	 *
	 * @param MarkupSlot $slot              The slot to process.
	 * @param bool       $recursive         Whether to recursively extract from nested Markup.
	 * @param bool       $includeSlots      Whether to include slot content.
	 * @param bool       $executeCallables  Whether to execute callables.
	 * @return string The extracted text.
	 */
	private function extractSlotText( MarkupSlot $slot, bool $recursive, bool $includeSlots, bool $executeCallables ): string {
		$name       = $slot->name();
		$hasContent = isset( $this->slotsContent[ $name ] ) && ! empty( $this->slotsContent[ $name ] );

		// If slot has no content and is not preserved, return empty
		if ( ! $hasContent && ! $slot->isPreserved() ) {
			return '';
		}

		// If slot has no content, return empty
		if ( ! $hasContent ) {
			return '';
		}

		$text = '';

		// Extract text from all slot items
		foreach ( $this->slotsContent[ $name ] as $item ) {
			if ( $item instanceof MarkupSlot ) {
				// Recursive slot
				$text .= $this->extractSlotText( $item, $recursive, $includeSlots, $executeCallables );
			} elseif ( $item instanceof MarkupFlow ) {
				// MarkupFlow in slot
				foreach ( $item->items() as $flowItem ) {
					$text .= $this->extractItemText( $flowItem, $recursive, $includeSlots, $executeCallables );
				}
			} else {
				// Regular item
				$text .= $this->extractItemText( $item, $recursive, $includeSlots, $executeCallables );
			}
		}

		return $text;
	}

	/**
	 * Renders and returns the generated markup as a string.
	 *
	 * @since 1.0.0
	 *
	 * @return string The generated HTML markup.
	 */
	public function render(): string {
		$this->streaming = false;
		return $this->execute();
	}

	/**
	 * Print the markup directly to output.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		$this->streaming = true;
		$this->execute();
	}

	/**
	 * Execute the markup generation process.
	 *
	 * Walks through the children tree and generates or outputs the markup
	 * based on the current streaming mode.
	 *
	 * Optimized to directly iterate children instead of using TreeWalker
	 * for better performance.
	 *
	 * @since 1.0.0
	 *
	 * @return string The generated markup string.
	 */
	private function execute(): string {
		$this->markup = '';
		$this->output( $this->wrapperOpenerTag() );

		// Directly iterate children instead of using TreeWalker (much faster)
		$this->renderChildren( $this->children );

		$this->output( $this->containerCloserTag() );
		return $this->markup;
	}

	/**
	 * Renders children elements efficiently.
	 *
	 * This method replaces the TreeWalker for better performance.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children The children to render.
	 * @return void
	 */
	private function renderChildren( array $children ): void {
		foreach ( $children as $value ) {
			// If it's a MarkupSlot object, render its content
			if ( $value instanceof MarkupSlot ) {
				$this->output( $this->renderSlot( $value ) );
				continue;
			}

			$this->output( $this->childrenOpenerTag() );

			// If it's a MarkupFlow, render all its items together in the same wrapper
			if ( $value instanceof MarkupFlow ) {
				foreach ( $value->items() as $item ) {
					$this->renderItem( $item );
				}
			} else {
				$this->renderItem( $value );
			}

			$this->output( $this->childrenCloserTag() );
		}
	}

	/**
	 * Renders a single item (Markup, string, or callable).
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The item to render.
	 * @return void
	 */
	private function renderItem( $item ): void {
		if ( $item instanceof Markup ) {
			// Use render() or print() to respect subclass overrides
			if ( $this->streaming ) {
				$item->print();
			} else {
				$this->output( $item->render() );
			}
		} elseif ( is_string( $item ) ) {
			// Check strings first to avoid treating function names as callables
			$this->output( $item );
		} elseif ( is_callable( $item ) ) {
			// Support for callbacks (closures, array callables, etc.)
			if ( $this->streaming ) {
				call_user_func( $item );
			} else {
				ob_start();
				call_user_func( $item );
				$this->output( ob_get_clean() );
			}
		}
	}

	/**
	 * Output content based on current mode.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Content to output.
	 * @return void
	 */
	private function output( string $content ): void {
		if ( $this->streaming ) {
			echo $content;
		} else {
			$this->markup .= $content;
		}
	}

	/**
	 * Generate the wrapper opening tag.
	 *
	 * Replaces placeholders for classes and attributes in the wrapper template.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper opening HTML tag.
	 */
	private function wrapperOpenerTag(): string {
		$childrenWrap = explode( '%children%', (string) $this->wrapper );
		$opener       = $childrenWrap[0];

		$opener = str_replace( '%classes%', implode( ' ', $this->wrapperClass ), $opener );

		// Build attributes string
		if ( ! empty( $this->wrapperAttributes ) ) {
			$attributes = [];
			foreach ( $this->wrapperAttributes as $attribute => $value ) {
				$attributes[] = $attribute . '="' . $value . '"';
			}
			$attributesStr = ' ' . implode( ' ', $attributes );
		} else {
			$attributesStr = '';
		}
		$opener = str_replace( '%attributes%', $attributesStr, $opener );

		// Clean up empty attributes (e.g., class="")
		$opener = preg_replace( '/\s+class=""/', '', $opener );
		$opener = preg_replace( '/\s+id=""/', '', $opener );
		
		// Clean up multiple spaces
		$opener = preg_replace( '/\s+/', ' ', $opener );
		$opener = preg_replace( '/\s+>/', '>', $opener );

		return $opener;
	}

	/**
	 * Generate the wrapper closing tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper closing HTML tag.
	 */
	private function containerCloserTag(): string {
		$closer    = '';
		$container = explode( '%children%', (string) $this->wrapper );
		if ( isset( $container[1] ) ) {
			$closer = $container[1];
		}
		return $closer;
	}

	/**
	 * Generate the children wrapper opening tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string The children wrapper opening HTML tag.
	 */
	private function childrenOpenerTag(): string {
		$container = explode( '%child%', (string) $this->childrenWrapper );
		$opener    = $container[0];
		return $opener;
	}

	/**
	 * Generate the children wrapper closing tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string The children wrapper closing HTML tag.
	 */
	private function childrenCloserTag(): string {
		$closer       = '';
		$childrenWrap = explode( '%child%', (string) $this->childrenWrapper );
		if ( isset( $childrenWrap[1] ) ) {
			$closer = $childrenWrap[1];
		}
		return $closer;
	}

	/**
	 * Renders a MarkupSlot object with its content and wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param MarkupSlot $slot The MarkupSlot object to render.
	 * @return string The rendered slot content with wrapper if applicable.
	 */
	private function renderSlot( MarkupSlot $slot ): string {
		$name       = $slot->name();
		$hasContent = isset( $this->slotsContent[ $name ] ) && ! empty( $this->slotsContent[ $name ] );
		$wrapper    = $slot->wrapper();

		// Check if we should render anything
		if ( ! $hasContent && ! $slot->isPreserved() ) {
			return '';
		}

		// In streaming mode, handle wrapper differently
		if ( $this->streaming ) {
			// Output opening wrapper
			if ( ! empty( $wrapper ) ) {
				$wrapperParts = explode( '%slot%', $wrapper );
				$this->output( $wrapperParts[0] );
			}

		// Render all items in the slot
		if ( $hasContent ) {
			foreach ( $this->slotsContent[ $name ] as $item ) {
				if ( $item instanceof Markup ) {
					$item->print();
				} elseif ( $item instanceof MarkupSlot ) {
					// Render nested MarkupSlot recursively
					$this->renderSlot( $item );
				} elseif ( $item instanceof MarkupFlow ) {
					// Render all items in the flow
					foreach ( $item->items() as $groupItem ) {
						$this->renderItem( $groupItem );
					}
				} elseif ( is_string( $item ) ) {
					// Check strings first to avoid treating function names as callables
					$this->output( $item );
				} elseif ( is_callable( $item ) ) {
					call_user_func( $item );
				}
			}
		}

			// Output closing wrapper
			if ( ! empty( $wrapper ) && isset( $wrapperParts[1] ) ) {
				$this->output( $wrapperParts[1] );
			}

			return '';
		}

		// Non-streaming mode: accumulate content
		$content = '';

	// Render all items in the slot if there's content
	if ( $hasContent ) {
		foreach ( $this->slotsContent[ $name ] as $item ) {
			if ( $item instanceof Markup ) {
				$content .= $item->render();
			} elseif ( $item instanceof MarkupSlot ) {
				// Render nested MarkupSlot recursively
				$content .= $this->renderSlot( $item );
			} elseif ( $item instanceof MarkupFlow ) {
				// Render all items in the flow
				foreach ( $item->items() as $groupItem ) {
					if ( $groupItem instanceof Markup ) {
						$content .= $groupItem->render();
					} elseif ( is_string( $groupItem ) ) {
						$content .= $groupItem;
					} elseif ( is_callable( $groupItem ) ) {
						ob_start();
						call_user_func( $groupItem );
						$content .= ob_get_clean();
					}
				}
			} elseif ( is_string( $item ) ) {
				// Check strings first to avoid treating function names as callables
				$content .= $item;
			} elseif ( is_callable( $item ) ) {
				ob_start();
				call_user_func( $item );
				$content .= ob_get_clean();
			}
		}
	}

		// Apply wrapper if slot has one
		if ( ! empty( $wrapper ) ) {
			$content = str_replace( '%slot%', $content, $wrapper );
		}

		return $content;
	}

}

