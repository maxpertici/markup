<?php
/**
 * Slot class for declaring named content placeholders.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Class Slot
 *
 * Represents a slot declaration that can be added as a child to Markup.
 * The Markup class will automatically detect and register Slot objects.
 *
 * @since 1.0.0
 */
class Slot {

	/**
	 * The name/identifier of the slot.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $name;

	/**
	 * Optional description of the slot's purpose.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $description;

	/**
	 * Optional wrapper template for slot content.
	 * Use %slot% placeholder for where the content should be placed.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $wrapper;

	/**
	 * Whether to preserve the wrapper even if slot is empty.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $preserve = false;

	/**
	 * Constructor.
	 *
	 * Creates a slot declaration that will be registered by Markup when added as a child.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name        The name/identifier of the slot.
	 * @param string $wrapper     Optional. Wrapper template with %slot% placeholder. Default empty string.
	 * @param string $description Optional. Description of the slot's purpose. Default empty string.
	 */
	public function __construct( string $name, string $wrapper = '', string $description = '' ) {
		$this->name        = $name;
		$this->wrapper     = $wrapper;
		$this->description = $description;
	}

	/**
	 * Gets or sets the slot name.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $name Optional. The name to set. If null, acts as getter.
	 * @return string|self The slot name when getting, or $this for method chaining when setting.
	 */
	public function name( ?string $name = null ) {
		if ( null === $name ) {
			return $this->name;
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * Gets or sets the slot description.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $description Optional. The description to set. If null, acts as getter.
	 * @return string|self The slot description when getting, or $this for method chaining when setting.
	 */
	public function description( ?string $description = null ) {
		if ( null === $description ) {
			return $this->description;
		}

		$this->description = $description;
		return $this;
	}

	/**
	 * Gets or sets the slot wrapper template.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $wrapper Optional. The wrapper template with %slot% placeholder. If null, acts as getter.
	 * @return string|self The slot wrapper when getting, or $this for method chaining when setting.
	 */
	public function wrapper( ?string $wrapper = null ) {
		if ( null === $wrapper ) {
			return $this->wrapper;
		}

		$this->wrapper = $wrapper;
		return $this;
	}

	/**
	 * Preserves the wrapper even if the slot is empty.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $preserve Optional. Whether to preserve wrapper if empty. Default true.
	 * @return self Returns $this for method chaining.
	 */
	public function preserve( bool $preserve = true ): self {
		$this->preserve = $preserve;
		return $this;
	}

	/**
	 * Gets whether the wrapper should be preserved even if empty.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if wrapper should be preserved when empty, false otherwise.
	 */
	public function isPreserved(): bool {
		return $this->preserve;
	}

	/**
	 * Gets slot information as an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array with slot information.
	 */
	public function toArray(): array {
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'wrapper'     => $this->wrapper,
			'preserve'    => $this->preserve,
		];
	}
}

