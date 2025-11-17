<?php
/**
 * Markup Finder for searching elements in a Markup tree.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupSlot;

/**
 * Class MarkupFinder
 *
 * Provides search functionality to find Markup elements based on various criteria
 * such as tags, classes, attributes, slugs, or custom callbacks.
 *
 * @since 1.0.0
 */
class MarkupFinder {

	/**
	 * The root Markup instance to search in.
	 *
	 * @since 1.0.0
	 * @var Markup
	 */
	private Markup $root;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $markup The root Markup instance to search in.
	 */
	public function __construct( Markup $markup ) {
		$this->root = $markup;
	}

	/**
	 * Finds all Markup elements that match a CSS class.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param bool $deep Optional. Whether to search recursively. Default true.
	 * @return array Array of all Markup instances.
	 */
	public function all( bool $deep = true ): array {
		return $this->search( fn() => true, $deep );
	}

	/**
	 * Finds Markup elements using CSS selector syntax.
	 *
	 * Supports:
	 * - Basic selectors: tag, .class, #id, [attr], [attr="value"]
	 * - Combinations: tag.class[attr]
	 * - Descendant: "parent child"
	 * - Direct child: "parent > child"
	 * - :has() pseudo-class: "parent:has(child)"
	 *
	 * Examples:
	 * ```php
	 * $markup->find()->css('.section');                    // by class
	 * $markup->find()->css('div');                         // by tag
	 * $markup->find()->css('[role="main"]');               // by attribute
	 * $markup->find()->css('nav li.active');               // descendant
	 * $markup->find()->css('.header > nav');               // direct child
	 * $markup->find()->css('section:has(.highlight)');     // has child
	 * $markup->find()->css('header > nav:has(li.active) a'); // complex
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param string $selector The CSS selector string.
	 * @return array Array of matching Markup instances.
	 */
	public function css( string $selector ): array {
		$selector = trim( $selector );

		// Optimization: Use existing methods for simple selectors
		if ( preg_match( '/^\.[\w-]+$/', $selector ) ) {
			// ".class" -> findByClass()
			return $this->findByClass( substr( $selector, 1 ) );
		}

		if ( preg_match( '/^\w+$/', $selector ) ) {
			// "div" -> findByTag()
			return $this->findByTag( $selector );
		}

		if ( preg_match( '/^#[\w-]+$/', $selector ) ) {
			// "#id" -> findByAttribute('id', 'value')
			return $this->findByAttribute( 'id', substr( $selector, 1 ) );
		}

		// Complex selector - parse and search
		$segments = $this->parseSelector( $selector );

		if ( empty( $segments ) ) {
			return [];
		}

		// Start search with the first segment
		return $this->searchWithSelector( $segments, 0, $this->root );
	}

	/**
	 * Parses a CSS selector into segments with combinators.
	 *
	 * @since 1.0.0
	 *
	 * @param string $selector The CSS selector string.
	 * @return array Array of segments with combinators.
	 */
	private function parseSelector( string $selector ): array {
		$segments = [];
		$current  = '';
		$length   = strlen( $selector );
		$in_has   = 0; // Track parenthesis depth for :has()

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $selector[ $i ];

			// Track :has() parentheses
			if ( '(' === $char ) {
				$in_has++;
				$current .= $char;
				continue;
			}

			if ( ')' === $char ) {
				$in_has--;
				$current .= $char;
				continue;
			}

			// Only process combinators outside of :has()
			if ( 0 === $in_has ) {
				// Direct child combinator
				if ( '>' === $char ) {
					if ( '' !== trim( $current ) ) {
						$segments[] = [
							'selector'   => trim( $current ),
							'combinator' => '>',
						];
						$current    = '';
					}
					continue;
				}

				// Descendant combinator (space)
				if ( ' ' === $char && '' !== trim( $current ) ) {
					// Check if next non-space char is not >
					$next_pos = $i + 1;
					while ( $next_pos < $length && ' ' === $selector[ $next_pos ] ) {
						$next_pos++;
					}

					if ( $next_pos < $length && '>' !== $selector[ $next_pos ] ) {
						$segments[] = [
							'selector'   => trim( $current ),
							'combinator' => ' ',
						];
						$current    = '';
						continue;
					}
				}
			}

			// Add character to current segment
			if ( ' ' !== $char || $in_has > 0 ) {
				$current .= $char;
			}
		}

		// Add last segment
		if ( '' !== trim( $current ) ) {
			$segments[] = [
				'selector'   => trim( $current ),
				'combinator' => null,
			];
		}

		// Parse each segment
		foreach ( $segments as $key => $segment ) {
			$segments[ $key ]['parsed'] = $this->parseSegment( $segment['selector'] );
		}

		return $segments;
	}

	/**
	 * Parses a single selector segment (e.g., "div.class[attr]:has(child)").
	 *
	 * @since 1.0.0
	 *
	 * @param string $segment The selector segment.
	 * @return array Parsed segment data.
	 */
	private function parseSegment( string $segment ): array {
		$parsed = [
			'tag'        => null,
			'id'         => null,
			'classes'    => [],
			'attributes' => [],
			'has'        => null,
		];

		// Extract :has() first (it contains parentheses)
		if ( preg_match( '/:has\(([^)]+)\)/', $segment, $has_match ) ) {
			$parsed['has'] = $has_match[1];
			$segment       = str_replace( $has_match[0], '', $segment );
		}

		// Extract ID
		if ( preg_match( '/#([\w-]+)/', $segment, $id_match ) ) {
			$parsed['id'] = $id_match[1];
			$segment      = str_replace( $id_match[0], '', $segment );
		}

		// Extract classes
		if ( preg_match_all( '/\.([\w-]+)/', $segment, $class_matches ) ) {
			$parsed['classes'] = $class_matches[1];
			foreach ( $class_matches[0] as $match ) {
				$segment = str_replace( $match, '', $segment );
			}
		}

		// Extract attributes
		if ( preg_match_all( '/\[([\w-]+)(?:="([^"]*)")?\]/', $segment, $attr_matches ) ) {
			foreach ( $attr_matches[1] as $index => $attr_name ) {
				$attr_value                      = isset( $attr_matches[2][ $index ] ) && '' !== $attr_matches[2][ $index ]
					? $attr_matches[2][ $index ]
					: null;
				$parsed['attributes'][ $attr_name ] = $attr_value;
			}
			foreach ( $attr_matches[0] as $match ) {
				$segment = str_replace( $match, '', $segment );
			}
		}

		// What's left is the tag
		$segment = trim( $segment );
		if ( '' !== $segment ) {
			$parsed['tag'] = $segment;
		}

		return $parsed;
	}

	/**
	 * Searches with a selector chain recursively.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $segments The parsed selector segments.
	 * @param int    $index    Current segment index.
	 * @param Markup $context  The context Markup to search from.
	 * @return array Array of matching Markup instances.
	 */
	private function searchWithSelector( array $segments, int $index, Markup $context ): array {
		if ( ! isset( $segments[ $index ] ) ) {
			return [];
		}

		$segment    = $segments[ $index ];
		$parsed     = $segment['parsed'];
		$combinator = $segment['combinator'];
		$is_last    = ! isset( $segments[ $index + 1 ] );

		$results = [];

		// For the first segment, search from root
		if ( 0 === $index ) {
			$candidates = $this->findMatchingSegment( $parsed, $context, true );
		} else {
			// For subsequent segments, behavior depends on combinator
			$prev_combinator = $segments[ $index - 1 ]['combinator'];

			if ( '>' === $prev_combinator ) {
				// Direct child: search only in immediate children
				$candidates = $this->findMatchingSegment( $parsed, $context, false );
			} else {
				// Descendant: search in all descendants
				$candidates = $this->findMatchingSegment( $parsed, $context, true );
			}
		}

		// If this is the last segment, return the candidates
		if ( $is_last ) {
			return $candidates;
		}

		// Otherwise, continue searching in each candidate
		foreach ( $candidates as $candidate ) {
			$nested_results = $this->searchWithSelector( $segments, $index + 1, $candidate );
			$results        = array_merge( $results, $nested_results );
		}

		return $results;
	}

	/**
	 * Finds elements matching a parsed segment.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $parsed The parsed segment.
	 * @param Markup $root   The root to search from.
	 * @param bool   $deep   Whether to search deeply or only direct children.
	 * @return array Array of matching Markup instances.
	 */
	private function findMatchingSegment( array $parsed, Markup $root, bool $deep ): array {
		$finder = new MarkupFinder( $root );

		return $finder->search(
			function ( Markup $markup ) use ( $parsed ) {
				return $this->matchesSegment( $markup, $parsed );
			},
			$deep
		);
	}

	/**
	 * Checks if a Markup element matches a parsed segment.
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $markup The Markup element to test.
	 * @param array  $parsed The parsed segment data.
	 * @return bool True if matches, false otherwise.
	 */
	private function matchesSegment( Markup $markup, array $parsed ): bool {
		// Check tag
		if ( null !== $parsed['tag'] ) {
			$wrapper = $markup->getWrapper();
			if ( empty( $wrapper ) ) {
				return false;
			}

			if ( preg_match( '/^<(\w+)/', $wrapper, $matches ) ) {
				if ( strtolower( $matches[1] ) !== strtolower( $parsed['tag'] ) ) {
					return false;
				}
			} else {
				return false;
			}
		}

		// Check ID
		if ( null !== $parsed['id'] ) {
			if ( $markup->getAttribute( 'id' ) !== $parsed['id'] ) {
				return false;
			}
		}

		// Check classes
		foreach ( $parsed['classes'] as $class ) {
			if ( ! $markup->hasClass( $class ) ) {
				return false;
			}
		}

		// Check attributes
		foreach ( $parsed['attributes'] as $attr_name => $attr_value ) {
			if ( ! $markup->hasAttribute( $attr_name ) ) {
				return false;
			}

			if ( null !== $attr_value && $markup->getAttribute( $attr_name ) !== $attr_value ) {
				return false;
			}
		}

		// Check :has()
		if ( null !== $parsed['has'] ) {
			$finder      = new MarkupFinder( $markup );
			$has_results = $finder->css( $parsed['has'] );

			// Don't include the element itself in :has() results
			$has_results = array_filter(
				$has_results,
				function ( $element ) use ( $markup ) {
					return $element !== $markup;
				}
			);

			if ( empty( $has_results ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Counts all Markup instances that match a callback.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param array    $children  The children array to search in.
	 * @param callable $callback  The callback to test each Markup instance.
	 * @param bool     $deep      Whether to search recursively.
	 * @param array    &$results  The results array to populate.
	 * @return void
	 */
	private function searchInChildren( array $children, callable $callback, bool $deep, array &$results ): void {
		foreach ( $children as $child ) {
			// If it's a MarkupFlow, search in its items
			if ( $child instanceof MarkupFlow ) {
				$this->searchInChildren( $child->items(), $callback, $deep, $results );
			} elseif ( $child instanceof Markup ) {
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
	 * @since 1.0.0
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
			// Get slot content using reflection since slotsContent is private
			$reflection    = new \ReflectionClass( $markup );
			$property      = $reflection->getProperty( 'slotsContent' );
			$property->setAccessible( true );
			$slotsContent = $property->getValue( $markup );

			$slotName = $slot->name();

		if ( isset( $slotsContent[ $slotName ] ) ) {
			foreach ( $slotsContent[ $slotName ] as $item ) {
				// If it's a MarkupFlow, search in its items
				if ( $item instanceof MarkupFlow ) {
					$this->searchInChildren( $item->items(), $callback, true, $results );
				} elseif ( $item instanceof Markup ) {
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
	 * @since 1.0.0
	 *
	 * @param array    $children The children array to search in.
	 * @param callable $callback The callback to test each Markup instance.
	 * @param bool     $deep     Whether to search recursively.
	 * @return Markup|null The first matching Markup or null.
	 */
	private function findFirstInChildren( array $children, callable $callback, bool $deep ): ?Markup {
		foreach ( $children as $child ) {
			// If it's a MarkupFlow, search in its items
			if ( $child instanceof MarkupFlow ) {
				$result = $this->findFirstInChildren( $child->items(), $callback, $deep );
				if ( null !== $result ) {
					return $result;
				}
			} elseif ( $child instanceof Markup ) {
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
	 * @since 1.0.0
	 *
	 * @param Markup   $markup   The Markup instance to search slots in.
	 * @param callable $callback The callback to test each Markup instance.
	 * @return Markup|null The first matching Markup or null.
	 */
	private function findFirstInSlots( Markup $markup, callable $callback ): ?Markup {
		$slots = $markup->slots();

		foreach ( $slots as $slot ) {
			// Get slot content using reflection
			$reflection    = new \ReflectionClass( $markup );
			$property      = $reflection->getProperty( 'slotsContent' );
			$property->setAccessible( true );
			$slotsContent = $property->getValue( $markup );

			$slotName = $slot->name();

		if ( isset( $slotsContent[ $slotName ] ) ) {
			foreach ( $slotsContent[ $slotName ] as $item ) {
				// If it's a MarkupFlow, search in its items
				if ( $item instanceof MarkupFlow ) {
					$result = $this->findFirstInChildren( $item->items(), $callback, true );
					if ( null !== $result ) {
						return $result;
					}
				} elseif ( $item instanceof Markup ) {
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

