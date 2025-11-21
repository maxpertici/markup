<?php
/**
 * Query builder for searching Markup elements with a fluent interface.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Class MarkupQueryBuilder
 *
 * Provides a fluent, chainable interface for building Markup search queries.
 *
 * @since 1.0.0
 */
class MarkupQueryBuilder {

	/**
	 * The root Markup instance to search in.
	 *
	 * @since 1.0.0
	 * @var Markup
	 */
	private Markup $root;

	/**
	 * The search criteria.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $criteria = [];

	/**
	 * Whether to search deeply (recursively).
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $deep = true;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $root The root Markup instance to search in.
	 */
	public function __construct( Markup $root ) {
		$this->root = $root;
	}

	/**
	 * Adds a CSS selector criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param string $selector The CSS selector.
	 * @return self For chaining.
	 */
	public function css( string $selector ): self {
		$this->criteria[] = [
			'type'  => 'css',
			'value' => $selector,
		];
		return $this;
	}

	/**
	 * Adds a class criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The CSS class to match.
	 * @return self For chaining.
	 */
	public function class( string $class ): self {
		$this->criteria[] = [
			'type'  => 'class',
			'value' => $class,
		];
		return $this;
	}

	/**
	 * Adds a tag criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag The HTML tag to match.
	 * @return self For chaining.
	 */
	public function tag( string $tag ): self {
		$this->criteria[] = [
			'type'  => 'tag',
			'value' => $tag,
		];
		return $this;
	}

	/**
	 * Adds an attribute criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $name  The attribute name.
	 * @param string|null $value Optional. The attribute value. Default null (checks existence only).
	 * @return self For chaining.
	 */
	public function attribute( string $name, ?string $value = null ): self {
		$this->criteria[] = [
			'type'  => 'attribute',
			'name'  => $name,
			'value' => $value,
		];
		return $this;
	}

	/**
	 * Adds an attribute criteria (alias).
	 *
	 * @since 1.0.0
	 *
	 * @param string      $name  The attribute name.
	 * @param string|null $value Optional. The attribute value. Default null.
	 * @return self For chaining.
	 */
	public function hasAttribute( string $name, ?string $value = null ): self {
		return $this->attribute( $name, $value );
	}

	/**
	 * Adds a slug criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug to match.
	 * @return self For chaining.
	 */
	public function slug( string $slug ): self {
		$this->criteria[] = [
			'type'  => 'slug',
			'value' => $slug,
		];
		return $this;
	}

	/**
	 * Adds a custom callback criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to test each element.
	 * @return self For chaining.
	 */
	public function where( callable $callback ): self {
		$this->criteria[] = [
			'type'     => 'callback',
			'callback' => $callback,
		];
		return $this;
	}

	/**
	 * Adds an OR callback criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to test each element.
	 * @return self For chaining.
	 */
	public function orWhere( callable $callback ): self {
		$this->criteria[] = [
			'type'     => 'or_callback',
			'callback' => $callback,
		];
		return $this;
	}

	/**
	 * Sets whether to search deeply (recursively).
	 *
	 * @since 1.0.0
	 *
	 * @param bool $deep Whether to search deeply.
	 * @return self For chaining.
	 */
	public function deep( bool $deep = true ): self {
		$this->deep = $deep;
		return $this;
	}

	/**
	 * Sets to search only immediate children (not recursive).
	 *
	 * @since 1.0.0
	 *
	 * @return self For chaining.
	 */
	public function shallow(): self {
		return $this->deep( false );
	}

	/**
	 * Executes the query and returns a collection of results.
	 *
	 * @since 1.0.0
	 *
	 * @return MarkupCollection The collection of matching elements.
	 */
	public function get(): MarkupCollection {
		$finder = new MarkupFinder( $this->root );

		// If only one CSS criteria, use it directly for optimization
		if ( 1 === count( $this->criteria ) && 'css' === $this->criteria[0]['type'] ) {
			$results = $finder->css( $this->criteria[0]['value'] );
			return new MarkupCollection( $results );
		}

		// Build and execute the callback
		$callback = $this->buildCallback();
		$results  = $finder->search( $callback, $this->deep );

		return new MarkupCollection( $results );
	}

	/**
	 * Executes the query and returns all results as an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of matching Markup instances.
	 */
	public function all(): array {
		return $this->get()->all();
	}

	/**
	 * Executes the query and returns the first result.
	 *
	 * @since 1.0.0
	 *
	 * @return Markup|null The first matching element or null.
	 */
	public function first(): ?Markup {
		// Optimization: use findFirst for better performance
		if ( empty( $this->criteria ) ) {
			return $this->root;
		}

		$finder   = new MarkupFinder( $this->root );
		$callback = $this->buildCallback();

		return $finder->findFirst( $callback, $this->deep );
	}

	/**
	 * Checks if any results exist.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if at least one match exists.
	 */
	public function exists(): bool {
		return null !== $this->first();
	}

	/**
	 * Checks if no results exist.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if no matches exist.
	 */
	public function doesntExist(): bool {
		return ! $this->exists();
	}

	/**
	 * Counts the number of matching elements.
	 *
	 * @since 1.0.0
	 *
	 * @return int The count of matches.
	 */
	public function count(): int {
		return $this->get()->count();
	}

	/**
	 * Builds a callback from the criteria.
	 *
	 * @since 1.0.0
	 *
	 * @return callable The callback to test elements.
	 */
	private function buildCallback(): callable {
		return function ( Markup $markup ) {
			$has_or = false;
			$or_met = false;

			foreach ( $this->criteria as $criterion ) {
				$type = $criterion['type'];

				// Handle OR logic
				if ( 'or_callback' === $type ) {
					$has_or = true;
					if ( $criterion['callback']( $markup ) ) {
						$or_met = true;
					}
					continue;
				}

				// Regular AND logic
				if ( ! $this->matchesCriterion( $markup, $criterion ) ) {
					return $has_or ? $or_met : false;
				}
			}

			return true;
		};
	}

	/**
	 * Checks if a Markup element matches a criterion.
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $markup    The Markup element to test.
	 * @param array  $criterion The criterion to test against.
	 * @return bool True if matches.
	 */
	private function matchesCriterion( Markup $markup, array $criterion ): bool {
		switch ( $criterion['type'] ) {
			case 'class':
				return $markup->hasClass( $criterion['value'] );

			case 'tag':
				return $this->matchesTag( $markup, $criterion['value'] );

			case 'attribute':
				if ( ! $markup->hasAttribute( $criterion['name'] ) ) {
					return false;
				}
				if ( null === $criterion['value'] ) {
					return true;
				}
				return $markup->getAttribute( $criterion['name'] ) === $criterion['value'];

			case 'slug':
				return $markup->slug() === $criterion['value'];

			case 'callback':
				return $criterion['callback']( $markup );

			case 'css':
				// For CSS in combined criteria, we need to check if element matches
				$finder  = new MarkupFinder( $markup );
				$results = $finder->css( $criterion['value'] );
				// Check if the markup itself is in the results
				foreach ( $results as $result ) {
					if ( $result === $markup ) {
						return true;
					}
				}
				return false;

			default:
				return false;
		}
	}

	/**
	 * Checks if a Markup element matches a tag.
	 *
	 * @since 1.0.0
	 *
	 * @param Markup $markup The Markup element to test.
	 * @param string $tag    The tag to match.
	 * @return bool True if matches.
	 */
	private function matchesTag( Markup $markup, string $tag ): bool {
		$wrapper = $markup->getWrapper();

		if ( empty( $wrapper ) ) {
			return false;
		}

		// Extract tag name from wrapper (e.g., '<div...>' => 'div')
		if ( preg_match( '/^<(\w+)/', $wrapper, $matches ) ) {
			return strtolower( $matches[1] ) === strtolower( $tag );
		}

		return false;
	}

}

