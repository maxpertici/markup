<?php
/**
 * Collection for Markup elements.
 *
 * Provides a fluent, chainable interface for working with arrays of Markup instances.
 * Inspired by Laravel Collections for excellent DX.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Class MarkupCollection
 *
 * A collection class providing chainable methods for working with Markup elements.
 *
 * @since 1.0.0
 */
class MarkupCollection implements \IteratorAggregate, \Countable, \ArrayAccess {

	/**
	 * The items contained in the collection.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $items;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items The items to include in the collection.
	 */
	public function __construct( array $items = [] ) {
		$this->items = array_values( $items );
	}

	/**
	 * Creates a new collection instance.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items The items to include.
	 * @return self
	 */
	public static function make( array $items = [] ): self {
		return new self( $items );
	}

	/**
	 * Applies a callback to each item and returns a new collection.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to apply to each item.
	 * @return self New collection with transformed items.
	 */
	public function map( callable $callback ): self {
		return new self( array_map( $callback, $this->items ) );
	}

	/**
	 * Filters the collection using a callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to test each item.
	 * @return self New filtered collection.
	 */
	public function filter( callable $callback ): self {
		return new self( array_filter( $this->items, $callback ) );
	}

	/**
	 * Filters the collection by rejecting items that pass the callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to test each item.
	 * @return self New collection with rejected items removed.
	 */
	public function reject( callable $callback ): self {
		return $this->filter(
			function ( $item ) use ( $callback ) {
				return ! $callback( $item );
			}
		);
	}

	/**
	 * Executes a callback on each item. Returns the collection for chaining.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to execute. Receives (item, index).
	 * @return self The same collection for chaining.
	 */
	public function each( callable $callback ): self {
		foreach ( $this->items as $index => $item ) {
			if ( false === $callback( $item, $index ) ) {
				break;
			}
		}

		return $this;
	}

	/**
	 * Gets the first item that passes an optional callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable|null $callback Optional. Callback to test items. Default null.
	 * @param mixed         $default  Optional. Default value if no item found. Default null.
	 * @return mixed The first matching item or default.
	 */
	public function first( ?callable $callback = null, $default = null ): mixed {
		if ( null === $callback ) {
			return $this->items[0] ?? $default;
		}

		foreach ( $this->items as $item ) {
			if ( $callback( $item ) ) {
				return $item;
			}
		}

		return $default;
	}

	/**
	 * Gets the last item that passes an optional callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable|null $callback Optional. Callback to test items. Default null.
	 * @param mixed         $default  Optional. Default value if no item found. Default null.
	 * @return mixed The last matching item or default.
	 */
	public function last( ?callable $callback = null, $default = null ): mixed {
		if ( null === $callback ) {
			return $this->items[ array_key_last( $this->items ) ] ?? $default;
		}

		$items = array_reverse( $this->items, true );
		foreach ( $items as $item ) {
			if ( $callback( $item ) ) {
				return $item;
			}
		}

		return $default;
	}

	/**
	 * Gets all items where a method returns a truthy value.
	 *
	 * Example: $collection->where('hasClass', 'active')
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method name to call on each item.
	 * @param mixed  ...$args Arguments to pass to the method.
	 * @return self New filtered collection.
	 */
	public function where( string $method, ...$args ): self {
		return $this->filter(
			function ( $item ) use ( $method, $args ) {
				return $item->$method( ...$args );
			}
		);
	}

	/**
	 * Extracts values from each item by calling a method or accessing an attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The method name to call or attribute to get.
	 * @return array Array of extracted values.
	 */
	public function pluck( string $key ): array {
		return array_map(
			function ( $item ) use ( $key ) {
				if ( method_exists( $item, $key ) ) {
					return $item->$key();
				}
				if ( method_exists( $item, 'getAttribute' ) ) {
					return $item->getAttribute( $key );
				}
				return null;
			},
			$this->items
		);
	}

	/**
	 * Groups the collection by a callback or attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param callable|string $key Callback or attribute name to group by.
	 * @return array Associative array of grouped items.
	 */
	public function groupBy( callable|string $key ): array {
		$groups = [];

		foreach ( $this->items as $item ) {
			$group_key = is_callable( $key ) ? $key( $item ) : $item->getAttribute( $key );

			if ( ! isset( $groups[ $group_key ] ) ) {
				$groups[ $group_key ] = new self();
			}

			$groups[ $group_key ]->items[] = $item;
		}

		return $groups;
	}

	/**
	 * Returns unique items based on a callback or attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param callable|string|null $key Optional. Callback or attribute to determine uniqueness. Default null.
	 * @return self New collection with unique items.
	 */
	public function unique( callable|string|null $key = null ): self {
		if ( null === $key ) {
			return new self( array_unique( $this->items, SORT_REGULAR ) );
		}

		$exists = [];
		$items  = [];

		foreach ( $this->items as $item ) {
			$id = is_callable( $key ) ? $key( $item ) : $item->getAttribute( $key );

			if ( ! in_array( $id, $exists, true ) ) {
				$exists[] = $id;
				$items[]  = $item;
			}
		}

		return new self( $items );
	}

	/**
	 * Sorts the collection using a callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback Callback that returns the value to sort by.
	 * @param int      $options  Optional. Sort options. Default SORT_REGULAR.
	 * @param bool     $descending Optional. Sort descending. Default false.
	 * @return self New sorted collection.
	 */
	public function sortBy( callable $callback, int $options = SORT_REGULAR, bool $descending = false ): self {
		$items = $this->items;

		$values = array_map( $callback, $items );

		$descending ? arsort( $values, $options ) : asort( $values, $options );

		$sorted = [];
		foreach ( array_keys( $values ) as $key ) {
			$sorted[] = $items[ $key ];
		}

		return new self( $sorted );
	}

	/**
	 * Takes the first n items.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Number of items to take.
	 * @return self New collection with limited items.
	 */
	public function take( int $limit ): self {
		if ( $limit < 0 ) {
			return new self( array_slice( $this->items, $limit ) );
		}

		return new self( array_slice( $this->items, 0, $limit ) );
	}

	/**
	 * Skips the first n items.
	 *
	 * @since 1.0.0
	 *
	 * @param int $offset Number of items to skip.
	 * @return self New collection without skipped items.
	 */
	public function skip( int $offset ): self {
		return new self( array_slice( $this->items, $offset ) );
	}

	/**
	 * Slices the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $offset Starting position.
	 * @param int|null $length Optional. Number of items to take. Default null (all remaining).
	 * @return self New sliced collection.
	 */
	public function slice( int $offset, ?int $length = null ): self {
		return new self( array_slice( $this->items, $offset, $length ) );
	}

	/**
	 * Chunks the collection into smaller collections.
	 *
	 * @since 1.0.0
	 *
	 * @param int $size The size of each chunk.
	 * @return array Array of MarkupCollection instances.
	 */
	public function chunk( int $size ): array {
		$chunks = array_chunk( $this->items, $size );

		return array_map(
			function ( $chunk ) {
				return new self( $chunk );
			},
			$chunks
		);
	}

	/**
	 * Checks if the collection is empty.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if empty.
	 */
	public function isEmpty(): bool {
		return empty( $this->items );
	}

	/**
	 * Checks if the collection is not empty.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if not empty.
	 */
	public function isNotEmpty(): bool {
		return ! $this->isEmpty();
	}

	/**
	 * Checks if the collection contains an item.
	 *
	 * @since 1.0.0
	 *
	 * @param callable|mixed $value Callback or value to check.
	 * @return bool True if contains the item.
	 */
	public function contains( $value ): bool {
		if ( is_callable( $value ) ) {
			foreach ( $this->items as $item ) {
				if ( $value( $item ) ) {
					return true;
				}
			}
			return false;
		}

		return in_array( $value, $this->items, true );
	}

	/**
	 * Gets a random item from the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed A random item or null if empty.
	 */
	public function random(): mixed {
		if ( $this->isEmpty() ) {
			return null;
		}

		return $this->items[ array_rand( $this->items ) ];
	}

	/**
	 * Reverses the order of items.
	 *
	 * @since 1.0.0
	 *
	 * @return self New reversed collection.
	 */
	public function reverse(): self {
		return new self( array_reverse( $this->items ) );
	}

	/**
	 * Applies a callback to the collection and returns the result.
	 *
	 * Useful for chaining non-collection operations.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to apply.
	 * @return mixed The result of the callback.
	 */
	public function pipe( callable $callback ): mixed {
		return $callback( $this );
	}

	/**
	 * Passes the collection to a callback and returns the collection.
	 *
	 * Useful for side effects while chaining.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback to execute.
	 * @return self The same collection for chaining.
	 */
	public function tap( callable $callback ): self {
		$callback( $this );
		return $this;
	}

	/**
	 * Gets all items as an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array The items array.
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Gets all items as an array (alias for all).
	 *
	 * @since 1.0.0
	 *
	 * @return array The items array.
	 */
	public function toArray(): array {
		return $this->items;
	}

	/**
	 * Gets all values (re-indexed).
	 *
	 * @since 1.0.0
	 *
	 * @return array The values array.
	 */
	public function values(): array {
		return array_values( $this->items );
	}

	/**
	 * Counts the items in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return int The number of items.
	 */
	public function count(): int {
		return count( $this->items );
	}

	/**
	 * Gets an iterator for the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return \Traversable The iterator.
	 */
	public function getIterator(): \Traversable {
		return new \ArrayIterator( $this->items );
	}

	/**
	 * Checks if an offset exists.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $offset The offset to check.
	 * @return bool True if exists.
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->items[ $offset ] );
	}

	/**
	 * Gets an item at an offset.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $offset The offset to get.
	 * @return mixed The item at the offset.
	 */
	public function offsetGet( $offset ): mixed {
		return $this->items[ $offset ] ?? null;
	}

	/**
	 * Sets an item at an offset.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $offset The offset to set.
	 * @param mixed $value  The value to set.
	 * @return void
	 */
	public function offsetSet( $offset, $value ): void {
		if ( null === $offset ) {
			$this->items[] = $value;
		} else {
			$this->items[ $offset ] = $value;
		}
	}

	/**
	 * Unsets an item at an offset.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $offset The offset to unset.
	 * @return void
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->items[ $offset ] );
	}

	/**
	 * Dumps the collection for debugging.
	 *
	 * @since 1.0.0
	 *
	 * @return self The same collection for chaining.
	 */
	public function dump(): self {
		var_dump( $this->items );
		return $this;
	}

	/**
	 * Dumps the collection and dies.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dd(): void {
		$this->dump();
		die();
	}

}

