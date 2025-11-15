<?php
/**
 * Markup class for building and rendering HTML structures.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

use MaxPertici\Markup\Utils\MarkupDataTreeWalker;

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
	protected array $wrapper_class = [];

	/**
	 * HTML attributes for the wrapper element.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $wrapper_attributes = [];

	/**
	 * The children wrapper HTML template with %child% placeholder.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $children_wrapper = '';

	/**
	 * Array of child elements (strings, Markup instances, or callables).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $children = [];

	/**
	 * Array of registered Slot declarations keyed by slot name.
	 *
	 * @since 1.0.0
	 * @var array<string, Slot>
	 */
	protected array $declared_slots = [];

	/**
	 * Array of slot content keyed by slot name.
	 *
	 * @since 1.0.0
	 * @var array<string, array>
	 */
	protected array $slots_content = [];

	/**
	 * Whether to output content directly or store it.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $streaming = false;

	/**
	 * Current path in the data tree.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private static string $path = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wrapper             Optional. The wrapper HTML template. Default empty string.
	 * @param array  $wrapper_class       Optional. CSS classes for the wrapper. Default empty array.
	 * @param array  $wrapper_attributes  Optional. HTML attributes for the wrapper. Default empty array.
	 * @param string $children_wrapper    Optional. The children wrapper HTML template. Default empty string.
	 * @param array  $children            Optional. Array of child elements. Default empty array.
	 * @param string $path                Optional. Initial path in the data tree. Default empty string.
	 */
	public function __construct(
		string $wrapper = '',
		array $wrapper_class = [],
		array $wrapper_attributes = [],
		string $children_wrapper = '',
		array $children = [],
		string $path = '',
	) {
		$this->wrapper            = $wrapper;
		$this->wrapper_class      = $wrapper_class;
		$this->wrapper_attributes = $wrapper_attributes;
		$this->children_wrapper   = $children_wrapper;
		$this->children           = $children;
		self::$path               = $path;
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
	 * @since 1.2.0
	 *
	 * @param string|array ...$classes CSS class name(s) to add.
	 * @return self Returns $this for method chaining.
	 */
	public function addClass( ...$classes ): self {
		foreach ( $classes as $class ) {
			if ( is_array( $class ) ) {
				foreach ( $class as $c ) {
					if ( ! in_array( $c, $this->wrapper_class, true ) ) {
						$this->wrapper_class[] = $c;
					}
				}
			} else {
				if ( ! in_array( $class, $this->wrapper_class, true ) ) {
					$this->wrapper_class[] = $class;
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
	 * @since 1.2.0
	 *
	 * @param string|array ...$classes CSS class name(s) to remove.
	 * @return self Returns $this for method chaining.
	 */
	public function removeClass( ...$classes ): self {
		foreach ( $classes as $class ) {
			if ( is_array( $class ) ) {
				foreach ( $class as $c ) {
					$this->wrapper_class = array_diff( $this->wrapper_class, array( $c ) );
				}
			} else {
				$this->wrapper_class = array_diff( $this->wrapper_class, array( $class ) );
			}
		}
		// Re-index array to avoid gaps in numeric keys
		$this->wrapper_class = array_values( $this->wrapper_class );
		return $this;
	}

	/**
	 * Checks if the wrapper has a specific CSS class.
	 *
	 * @since 1.2.0
	 *
	 * @param string $class The CSS class name to check.
	 * @return bool True if the class exists, false otherwise.
	 */
	public function hasClass( string $class ): bool {
		return in_array( $class, $this->wrapper_class, true );
	}

	/**
	 * Gets or sets all CSS classes for the wrapper element.
	 *
	 * When called without a parameter, returns the current classes array.
	 * When called with a parameter, replaces all existing classes and returns $this for chaining.
	 * To add or remove specific classes, use addClass() or removeClass() instead.
	 *
	 * @since 1.2.0
	 *
	 * @param array|null $classes Optional. Array of CSS class names to set. If null, acts as a getter.
	 * @return self|array Returns $this when setting (for chaining), or the classes array when getting.
	 */
	public function classes( ?array $classes = null ) {
		if ( null === $classes ) {
			return $this->wrapper_class;
		}

		$this->wrapper_class = array_values( array_unique( $classes ) );
		return $this;
	}

	/**
	 * Sets or removes an HTML attribute on the wrapper element.
	 *
	 * If value is provided, sets the attribute. If value is null, removes the attribute.
	 * For boolean attributes (e.g., disabled, readonly), pass the attribute name as the value.
	 *
	 * @since 1.2.0
	 *
	 * @param string      $name  The attribute name.
	 * @param string|null $value The attribute value. Pass null to remove the attribute.
	 * @return self Returns $this for method chaining.
	 */
	public function setAttribute( string $name, ?string $value ): self {
		if ( null === $value ) {
			unset( $this->wrapper_attributes[ $name ] );
		} else {
			$this->wrapper_attributes[ $name ] = $value;
		}
		return $this;
	}

	/**
	 * Removes an HTML attribute from the wrapper element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $name The attribute name to remove.
	 * @return self Returns $this for method chaining.
	 */
	public function removeAttribute( string $name ): self {
		unset( $this->wrapper_attributes[ $name ] );
		return $this;
	}

	/**
	 * Checks if the wrapper has a specific HTML attribute.
	 *
	 * @since 1.2.0
	 *
	 * @param string $name The attribute name to check.
	 * @return bool True if the attribute exists, false otherwise.
	 */
	public function hasAttribute( string $name ): bool {
		return isset( $this->wrapper_attributes[ $name ] );
	}

	/**
	 * Gets the value of a specific HTML attribute.
	 *
	 * @since 1.2.0
	 *
	 * @param string $name The attribute name.
	 * @return string|null The attribute value or null if not set.
	 */
	public function getAttribute( string $name ): ?string {
		return $this->wrapper_attributes[ $name ] ?? null;
	}

	/**
	 * Gets or sets all HTML attributes for the wrapper element.
	 *
	 * When called without a parameter, returns the current attributes array.
	 * When called with a parameter, replaces all existing attributes and returns $this for chaining.
	 * To add or remove specific attributes, use setAttribute() or removeAttribute() instead.
	 *
	 * @since 1.2.0
	 *
	 * @param array|null $attributes Optional. Associative array of attribute names and values. If null, acts as a getter.
	 * @return self|array Returns $this when setting (for chaining), or the attributes array when getting.
	 */
	public function attributes( ?array $attributes = null ) {
		if ( null === $attributes ) {
			return $this->wrapper_attributes;
		}

		$this->wrapper_attributes = $attributes;
		return $this;
	}

	/**
	 * Adds child elements to the markup.
	 *
	 * Children can be strings, Markup instances, Slot declarations, or callable functions.
	 * When a Slot object is added, it is automatically registered for later reference.
	 * Multiple children can be passed as separate arguments or as an array.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed ...$children Child elements to add (strings, Markup instances, Slot objects, or callables).
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
	 * Adds a single child item, detecting and registering Slot objects.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The child item to add.
	 * @return void
	 */
	private function addChildItem( $item ): void {
		// If it's a Slot object, register it
		if ( $item instanceof Slot ) {
			$this->declared_slots[ $item->name() ] = $item;
		}

		// Add to children array
		$this->children[] = $item;
	}

	/**
	 * Gets all declared Slot objects, optionally filtered by names.
	 *
	 * Returns Slot objects that were added as children.
	 * When called without parameter, returns all slots.
	 * When called with an array of names, returns only those slots.
	 *
	 * @since 1.0.0
	 *
	 * @param array|null $names Optional. Array of slot names to filter. If null, returns all slots.
	 * @return array<string, Slot> Array of Slot objects keyed by slot name.
	 */
	public function slots( ?array $names = null ): array {
		if ( null === $names ) {
			return $this->declared_slots;
		}

		return array_filter(
			$this->declared_slots,
			fn( $key ) => in_array( $key, $names, true ),
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Gets a specific declared Slot object by name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the slot to retrieve.
	 * @return Slot|null The Slot object if found, null otherwise.
	 */
	public function getSlot( string $name ): ?Slot {
		return $this->declared_slots[ $name ] ?? null;
	}

	/**
	 * Adds content to a named slot.
	 *
	 * Accepts arrays or any supported type (string, Markup, Slot, callable).
	 * In the wrapper template, use %slot:name% placeholder to position the slot.
	 * Multiple elements can be added to the same slot.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The name of the slot.
	 * @param mixed  $items Items to add - can be an array or any supported type (string, Markup, Slot, callable).
	 * @return self Returns $this for method chaining.
	 */
	public function slot( string $name, $items ): self {
		// Initialize slot content array if not exists
		if ( ! isset( $this->slots_content[ $name ] ) ) {
			$this->slots_content[ $name ] = [];
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
		// Register Slot objects if they're being added as slot content
		if ( $item instanceof Slot ) {
			$this->declared_slots[ $item->name() ] = $item;
		}

		$this->slots_content[ $name ][] = $item;
	}

	/**
	 * Gets the names of all declared slots.
	 *
	 * Returns an array of slot names that have been added as Slot children.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of declared slot names.
	 */
	public function slotNames(): array {
		return array_keys( $this->declared_slots );
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

		foreach ( $this->slots_content as $name => $items ) {
			if ( ! empty( $items ) ) {
				$filled[] = $name;
			}
		}

		return $filled;
	}

	/**
	 * Checks if a slot has been declared (added as a Slot child).
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name of the slot to check.
	 * @return bool True if the slot has been declared, false otherwise.
	 */
	public function hasSlot( string $name ): bool {
		return isset( $this->declared_slots[ $name ] );
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
		return isset( $this->slots_content[ $name ] ) && ! empty( $this->slots_content[ $name ] );
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

		foreach ( $this->declared_slots as $name => $slot ) {
			$slot_info                = $slot->toArray();
			$slot_info['filled']      = $this->isSlotFilled( $name );
			$slot_info['items_count'] = isset( $this->slots_content[ $name ] ) ? count( $this->slots_content[ $name ] ) : 0;
			$info[ $name ]            = $slot_info;
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
	 * @since 1.3.0
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
	 * @since 1.0.0
	 *
	 * @param string $path Optional. Current path in the data tree. Default empty string.
	 * @return string The generated markup string.
	 */
	private function execute( string $path = '' ): string {

		self::$path   = $path;
		$this->markup = '';
		$this->output( $this->wrapperOpenerTag() );
		$that = $this;

		$walker = new MarkupDataTreeWalker(
			function ( $value, $path ) use ( $that ): void {
				self::$path = $path;

				// If it's a Slot object, render its content
				if ( $value instanceof Slot ) {
					$that->output( $that->renderSlot( $value ) );
					return;
				}

				$that->output( $that->childrenOpenerTag() );

				if ( $value instanceof Markup ) {
					// Use render() or print() to respect BlockMarkup's overrides
					if ( $that->streaming ) {
						$value->print();
					} else {
						$that->output( $value->render() );
					}
				} elseif ( is_callable( $value ) ) {
					// Support des callbacks (template parts, etc.)
					if ( $that->streaming ) {
						call_user_func( $value );
					} else {
						ob_start();
						call_user_func( $value );
						$that->output( ob_get_clean() );
					}
				} elseif ( is_string( $value ) ) {
					$that->output( $value );
				}

				$that->output( $that->childrenCloserTag() );
			}
		);

		$walker->walk( $this->children, self::$path );

		$this->output( $this->containerCloserTag() );
		return $this->markup;
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
		$children_wrap = explode( '%children%', (string) $this->wrapper );
		$opener        = $children_wrap[0];

		$opener = str_replace( '%classes%', implode( ' ', $this->wrapper_class ), $opener );

		$attributes = [];
		foreach ( $this->wrapper_attributes as $attribute => $value ) {
			$attributes[] = $attribute . '="' . $value . '"';
		}
		$attributes_str = implode( ' ', $attributes );
		// Only add space if there are attributes
		$attributes_str = ! empty( $attributes_str ) ? ' ' . $attributes_str : '';
		$opener         = str_replace( '%attributes%', $attributes_str, $opener );

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
		$container = explode( '%child%', (string) $this->children_wrapper );
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
		$closer        = '';
		$children_wrap = explode( '%child%', (string) $this->children_wrapper );
		if ( isset( $children_wrap[1] ) ) {
			$closer = $children_wrap[1];
		}
		return $closer;
	}

	/**
	 * Renders a Slot object with its content and wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param Slot $slot The Slot object to render.
	 * @return string The rendered slot content with wrapper if applicable.
	 */
	private function renderSlot( Slot $slot ): string {
		$name        = $slot->name();
		$has_content = isset( $this->slots_content[ $name ] ) && ! empty( $this->slots_content[ $name ] );
		$wrapper     = $slot->wrapper();

		// Check if we should render anything
		if ( ! $has_content && ! $slot->isPreserved() ) {
			return '';
		}

		// In streaming mode, handle wrapper differently
		if ( $this->streaming ) {
			// Output opening wrapper
			if ( ! empty( $wrapper ) ) {
				$wrapper_parts = explode( '%slot%', $wrapper );
				$this->output( $wrapper_parts[0] );
			}

			// Render all items in the slot
			if ( $has_content ) {
				foreach ( $this->slots_content[ $name ] as $item ) {
					if ( $item instanceof Markup ) {
						$item->print();
					} elseif ( $item instanceof Slot ) {
						// Render nested Slot recursively
						$this->renderSlot( $item );
					} elseif ( is_callable( $item ) ) {
						call_user_func( $item );
					} elseif ( is_string( $item ) ) {
						$this->output( $item );
					}
				}
			}

			// Output closing wrapper
			if ( ! empty( $wrapper ) && isset( $wrapper_parts[1] ) ) {
				$this->output( $wrapper_parts[1] );
			}

			return '';
		}

		// Non-streaming mode: accumulate content
		$content = '';

		// Render all items in the slot if there's content
		if ( $has_content ) {
			foreach ( $this->slots_content[ $name ] as $item ) {
				if ( $item instanceof Markup ) {
					$content .= $item->render();
				} elseif ( $item instanceof Slot ) {
					// Render nested Slot recursively
					$content .= $this->renderSlot( $item );
				} elseif ( is_callable( $item ) ) {
					ob_start();
					call_user_func( $item );
					$content .= ob_get_clean();
				} elseif ( is_string( $item ) ) {
					$content .= $item;
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

