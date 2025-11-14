<?php
/**
 * Markup Data Tree Walker
 *
 * Utility class for traversing data structures.
 *
 * @package MaxPertici\Markup\Utils
 */

namespace MaxPertici\Markup\Utils;

/**
 * Class MarkupDataTreeWalker
 *
 * This class allows traversing an array of data and is used by the Markup class
 * to execute callbacks for each element in the tree.
 *
 * @since 1.0.0
 */
class MarkupDataTreeWalker {

	/**
	 * The callback function to execute for each node.
	 *
	 * @since 1.0.0
	 * @var callable
	 */
	private $callback;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The callback function to execute for each node.
	 */
	public function __construct( callable $callback ) {
		$this->callback = $callback;
	}

	/**
	 * Walk through the data tree and execute the callback for each node.
	 *
	 * @since 1.0.0
	 *
	 * @param array       $data Data array to traverse.
	 * @param string|null $path Optional. Current path in the tree. Default empty string.
	 * @return void
	 */
	public function walk( $data, ?string $path = '' ): void {
		foreach ( $data as $key => $value ) {
			$currentPath = $path ? $path . '_' . $key : (string) $key;
			call_user_func( $this->callback, $value, $currentPath );

			if ( is_array( $value ) ) {
				$this->walk( $value, $currentPath );
			}
		}
	}
}

