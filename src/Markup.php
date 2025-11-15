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
		$this->output( $this->wrapper_opener_tag() );
		$that = $this;

		$walker = new MarkupDataTreeWalker(
			function ( $value, $path ) use ( $that ): void {
				self::$path = $path;
				$that->output( $that->children_opener_tag() );

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

				$that->output( $that->children_closer_tag() );
			}
		);

		$walker->walk( $this->children, self::$path );

		$this->output( $this->container_closer_tag() );
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
	private function wrapper_opener_tag(): string {
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
	private function container_closer_tag(): string {
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
	private function children_opener_tag(): string {
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
	private function children_closer_tag(): string {
		$closer        = '';
		$children_wrap = explode( '%child%', (string) $this->children_wrapper );
		if ( isset( $children_wrap[1] ) ) {
			$closer = $children_wrap[1];
		}
		return $closer;
	}
}

