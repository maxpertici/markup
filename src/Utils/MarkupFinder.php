<?php
/**
 * Markup Finder for searching elements in a Markup tree.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup\Utils;

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupSlot;

/**
 * Class MarkupFinder
 *
 * Provides search functionality to find Markup elements based on various criteria
 * such as tags, classes, attributes, slugs, or custom callbacks.
 *
 * @since 1.3.0
 */
class MarkupFinder {

	/**
	 * The root Markup instance to search in.
	 *
	 * @since 1.3.0
	 * @var Markup
	 */
	private Markup $root;

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param Markup $markup The root Markup instance to search in.
	 */
	public function __construct( Markup $markup ) {
		$this->root = $markup;
	}

	/**
	 * Finds all Markup elements that match a CSS class.
	 *
	 * @since 1.3.0
	 *
	 * @param string $class The CSS class to search for.
	 * @param bool   $deep  Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function findByClass( string $class, bool $deep = true ): array {
		return $this->search(
			function ( Markup $markup ) use ( $class ) {
				return $markup->hasClass( $class );
			},
			$deep
		);
	}

	/**
	 * Finds all Markup elements that match an attribute.
	 *
	 * @since 1.3.0
	 *
	 * @param string      $name  The attribute name.
	 * @param string|null $value Optional. The attribute value to match. If null, just checks existence. Default null.
	 * @param bool        $deep  Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function findByAttribute( string $name, ?string $value = null, bool $deep = true ): array {
		return $this->search(
			function ( Markup $markup ) use ( $name, $value ) {
				if ( ! $markup->hasAttribute( $name ) ) {
					return false;
				}

				if ( null === $value ) {
					return true;
				}

				return $markup->getAttribute( $name ) === $value;
			},
			$deep
		);
	}

	/**
	 * Finds all Markup elements that match a slug.
	 *
	 * @since 1.3.0
	 *
	 * @param string $slug The slug to search for.
	 * @param bool   $deep Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function findBySlug( string $slug, bool $deep = true ): array {
		return $this->search(
			function ( Markup $markup ) use ( $slug ) {
				return $markup->slug() === $slug;
			},
			$deep
		);
	}

	/**
	 * Finds all Markup elements that match a tag.
	 *
	 * This method extracts the tag name from the wrapper template
	 * (e.g., '<div...>' will match 'div').
	 *
	 * @since 1.3.0
	 *
	 * @param string $tag  The HTML tag to search for (e.g., 'div', 'span').
	 * @param bool   $deep Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function findByTag( string $tag, bool $deep = true ): array {
		return $this->search(
			function ( Markup $markup ) use ( $tag ) {
				$wrapper = $markup->getWrapper();

				if ( empty( $wrapper ) ) {
					return false;
				}

				// Extract tag name from wrapper (e.g., '<div...>' => 'div')
				if ( preg_match( '/^<(\w+)/', $wrapper, $matches ) ) {
					return strtolower( $matches[1] ) === strtolower( $tag );
				}

				return false;
			},
			$deep
		);
	}

	/**
	 * Finds all Markup elements that match multiple classes (AND logic).
	 *
	 * @since 1.3.0
	 *
	 * @param array $classes Array of CSS classes to match. Element must have all classes.
	 * @param bool  $deep    Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function findByClasses( array $classes, bool $deep = true ): array {
		return $this->search(
			function ( Markup $markup ) use ( $classes ) {
				foreach ( $classes as $class ) {
					if ( ! $markup->hasClass( $class ) ) {
						return false;
					}
				}
				return true;
			},
			$deep
		);
	}

	/**
	 * Finds all Markup elements using a custom callback.
	 *
	 * The callback receives a Markup instance and should return true if it matches.
	 *
	 * Example:
	 * ```php
	 * $finder->search(function($markup) {
	 *     return $markup->hasClass('active') && $markup->hasAttribute('data-id');
	 * });
	 * ```
	 *
	 * @since 1.3.0
	 *
	 * @param callable $callback The callback to test each Markup instance. Receives (Markup $markup).
	 * @param bool     $deep     Optional. Whether to search recursively. Default true.
	 * @return array Array of matching Markup instances.
	 */
	public function search( callable $callback, bool $deep = true ): array {
		$results = [];

		// Test the root itself
		if ( call_user_func( $callback, $this->root ) ) {
			$results[] = $this->root;
		}

		// Search in children
		$this->searchInChildren( $this->root->getChildren(), $callback, $deep, $results );

		// Search in slots if deep search
		if ( $deep ) {
			$this->searchInSlots( $this->root, $callback, $results );
		}

		return $results;
	}

	/**
	 * Finds the first Markup element that matches a callback.
	 *
	 * @since 1.3.0
	 *
	 * @param callable $callback The callback to test each Markup instance.
	 * @param bool     $deep     Optional. Whether to search recursively. Default true.
	 * @return Markup|null The first matching Markup instance or null if not found.
	 */
	public function findFirst( callable $callback, bool $deep = true ): ?Markup {
		// Test the root itself
		if ( call_user_func( $callback, $this->root ) ) {
			return $this->root;
		}

		// Search in children
		$result = $this->findFirstInChildren( $this->root->getChildren(), $callback, $deep );
		if ( null !== $result ) {
			return $result;
		}

		// Search in slots if deep search
		if ( $deep ) {
			return $this->findFirstInSlots( $this->root, $callback );
		}

		return null;
	}

	/**
	 * Gets all Markup instances in the tree (flattened).
	 *
	 * @since 1.3.0
	 *
	 * @param bool $deep Optional. Whether to search recursively. Default true.
	 * @return array Array of all Markup instances.
	 */
	public function all( bool $deep = true ): array {
		return $this->search( fn() => true, $deep );
	}

	/**
	 * Counts all Markup instances that match a callback.
	 *
	 * @since 1.3.0
	 *
	 * @param callable $callback The callback to test each Markup instance.
	 * @param bool     $deep     Optional. Whether to search recursively. Default true.
	 * @return int The count of matching instances.
	 */
	public function count( callable $callback, bool $deep = true ): int {
		return count( $this->search( $callback, $deep ) );
	}

	/**
	 * Recursively searches in children array.
	 *
	 * @since 1.3.0
	 *
	 * @param array    $children  The children array to search in.
	 * @param callable $callback  The callback to test each Markup instance.
	 * @param bool     $deep      Whether to search recursively.
	 * @param array    &$results  The results array to populate.
	 * @return void
	 */
	private function searchInChildren( array $children, callable $callback, bool $deep, array &$results ): void {
		foreach ( $children as $child ) {
			if ( $child instanceof Markup ) {
				// Test this child
				if ( call_user_func( $callback, $child ) ) {
					$results[] = $child;
				}

				// If deep search, continue recursively
				if ( $deep ) {
					$this->searchInChildren( $child->getChildren(), $callback, $deep, $results );
					$this->searchInSlots( $child, $callback, $results );
				}
			}
		}
	}

	/**
	 * Searches in slot content.
	 *
	 * @since 1.3.0
	 *
	 * @param Markup   $markup   The Markup instance to search slots in.
	 * @param callable $callback The callback to test each Markup instance.
	 * @param array    &$results The results array to populate.
	 * @return void
	 */
	private function searchInSlots( Markup $markup, callable $callback, array &$results ): void {
		// Get all declared slots
		$slots = $markup->slots();

		foreach ( $slots as $slot ) {
			// Get slot content using reflection since slots_content is private
			$reflection       = new \ReflectionClass( $markup );
			$property         = $reflection->getProperty( 'slots_content' );
			$property->setAccessible( true );
			$slots_content = $property->getValue( $markup );

			$slot_name = $slot->name();

			if ( isset( $slots_content[ $slot_name ] ) ) {
				foreach ( $slots_content[ $slot_name ] as $item ) {
					if ( $item instanceof Markup ) {
						// Test this item
						if ( call_user_func( $callback, $item ) ) {
							$results[] = $item;
						}

						// Continue recursively
						$this->searchInChildren( $item->getChildren(), $callback, true, $results );
						$this->searchInSlots( $item, $callback, $results );
					}
				}
			}
		}
	}

	/**
	 * Finds the first matching Markup in children.
	 *
	 * @since 1.3.0
	 *
	 * @param array    $children The children array to search in.
	 * @param callable $callback The callback to test each Markup instance.
	 * @param bool     $deep     Whether to search recursively.
	 * @return Markup|null The first matching Markup or null.
	 */
	private function findFirstInChildren( array $children, callable $callback, bool $deep ): ?Markup {
		foreach ( $children as $child ) {
			if ( $child instanceof Markup ) {
				// Test this child
				if ( call_user_func( $callback, $child ) ) {
					return $child;
				}

				// If deep search, continue recursively
				if ( $deep ) {
					$result = $this->findFirstInChildren( $child->getChildren(), $callback, $deep );
					if ( null !== $result ) {
						return $result;
					}

					$result = $this->findFirstInSlots( $child, $callback );
					if ( null !== $result ) {
						return $result;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Finds the first matching Markup in slots.
	 *
	 * @since 1.3.0
	 *
	 * @param Markup   $markup   The Markup instance to search slots in.
	 * @param callable $callback The callback to test each Markup instance.
	 * @return Markup|null The first matching Markup or null.
	 */
	private function findFirstInSlots( Markup $markup, callable $callback ): ?Markup {
		$slots = $markup->slots();

		foreach ( $slots as $slot ) {
			// Get slot content using reflection
			$reflection       = new \ReflectionClass( $markup );
			$property         = $reflection->getProperty( 'slots_content' );
			$property->setAccessible( true );
			$slots_content = $property->getValue( $markup );

			$slot_name = $slot->name();

			if ( isset( $slots_content[ $slot_name ] ) ) {
				foreach ( $slots_content[ $slot_name ] as $item ) {
					if ( $item instanceof Markup ) {
						// Test this item
						if ( call_user_func( $callback, $item ) ) {
							return $item;
						}

						// Continue recursively
						$result = $this->findFirstInChildren( $item->getChildren(), $callback, true );
						if ( null !== $result ) {
							return $result;
						}

						$result = $this->findFirstInSlots( $item, $callback );
						if ( null !== $result ) {
							return $result;
						}
					}
				}
			}
		}

		return null;
	}

}

