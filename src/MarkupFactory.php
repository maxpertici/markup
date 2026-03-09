<?php
/**
 * Markup Factory for creating Markup instances from various sources.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

use MaxPertici\Markup\Contracts\MarkupElementInterface;

/**
 * Class MarkupFactory
 *
 * Factory class for creating Markup instances using convenient methods.
 * Provides two main creation patterns:
 * - create(): Quick creation of elements with classes and attributes
 * - fromHtml(): Recursive parsing of HTML into complete Markup trees
 *
 * @since 1.0.0
 */
class MarkupFactory {

	/**
	 * Creates a Markup instance from a predefined element configuration.
	 *
	 * This method allows you to create Markup from predefined element configurations.
	 * You can use built-in elements like HtmlTag, or create your own custom elements.
	 *
	 * Example usage:
	 * ```php
	 * use MaxPertici\Markup\Elements\HtmlTag;
	 *
	 * $div = MarkupFactory::fromElement(HtmlTag::DIV, [$child1, $child2]);
	 * $section = MarkupFactory::fromElement(HtmlTag::SECTION, [$content], ['main-section']);
	 * 
	 * // With custom element
	 * $card = MarkupFactory::fromElement(MyComponent::CARD, [$header, $body]);
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param MarkupElementInterface $element    The element configuration to use.
	 * @param array                  $children   Optional. Children elements. Default empty array.
	 * @param array                  $classes    Optional. Additional CSS classes. Default empty array.
	 * @param array                  $attributes Optional. Additional HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function fromElement(
		MarkupElementInterface $element,
		array $children = [],
		array $classes = [],
		array $attributes = []
	): Markup {
		// Merge element classes with provided classes
		$all_classes = array_merge( $element->classes(), $classes );

		// Merge element attributes with provided attributes
		$all_attributes = array_merge( $element->attributes(), $attributes );

		// Create the markup instance
		$markup = new Markup(
			$element->wrapper(),
			$all_classes,
			$all_attributes,
			$element->childrenWrapper(),
			$children
		);

		return $markup;
	}

	/**
	 * Creates a Markup instance from a tag name and parameters.
	 *
	 * This is the primary method for creating custom HTML elements.
	 * For common elements (div, span, p, etc.), consider using the helper methods instead.
	 *
	 * Example usage:
	 * ```php
	 * $div = MarkupFactory::create('div', ['container'], ['id' => 'main']);
	 * $div->append('Hello World');
	 * // Output: <div class="container" id="main">Hello World</div>
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag        The HTML tag name (e.g., 'div', 'p', 'span', 'article').
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes (associative array). Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function create(
		string $tag,
		array $classes = [],
		array $attributes = []
	): Markup {
		$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%>%%children%%</%s>', $tag, $tag );

		return new Markup( $wrapper, $classes, $attributes );
	}

	/**
	 * Creates a Markup instance by recursively parsing an HTML string.
	 *
	 * This method parses an HTML string and creates a complete Markup structure
	 * with all nested elements. Each HTML element becomes a Markup instance,
	 * preserving the entire tree structure.
	 * 
	 * Note: HTML comments (<!-- ... -->) are automatically removed during parsing.
	 * 
	 * If the HTML contains multiple root-level elements (e.g., multiple divs or
	 * elements at the same level), they will be returned inside a MarkupFlow
	 * instead of being wrapped in a container element.
	 *
	 * Example usage:
	 * ```php
	 * // Single root element
	 * $html = '<div class="card"><h2>Title</h2><p>Content</p></div>';
	 * $markup = MarkupFactory::fromHtml($html);
	 * echo $markup->render();
	 * 
	 * // Multiple root elements - will be wrapped in a div
	 * $html = '<noscript>...</noscript><header>...</header><main>...</main>';
	 * $markup = MarkupFactory::fromHtml($html); // Returned as MarkupFlow
	 * 
	 * // Limit parsing depth to 3 levels
	 * $markup = MarkupFactory::fromHtml($html, 3);
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param string   $html          The HTML string to parse.
	 * @param int|null $max_depth     Optional. Maximum parsing depth. Default null (unlimited).
	 * @param int      $current_depth Internal. Current depth level. Default 0.
	 * @return Contracts\MarkupInterface A new Markup instance or a MarkupFlow when multiple roots are detected.
	 */
	public static function fromHtml( string $html, ?int $max_depth = null, int $current_depth = 0 ): Contracts\MarkupInterface {
		// Remove HTML comments
		$html = preg_replace( '/<!--(.|\s)*?-->/', '', $html );
		
		// Remove leading/trailing whitespace
		$html = trim( $html );

		// If empty, return empty Markup
		if ( empty( $html ) ) {
			return new Markup();
		}

		// If max depth is reached, return HTML as raw string
		if ( null !== $max_depth && $current_depth >= $max_depth ) {
			return new Markup( '', [], [], '', [ $html ] );
		}

		// Try to parse as an HTML element
		if ( preg_match( '/^<(\w+)([^>]*)>(.*?)<\/\1>$/s', $html, $matches ) ) {
			$tag               = $matches[1];
			$attributes_string = $matches[2];
			$inner_html        = $matches[3];

			// Parse classes and attributes (optimized)
			list( $classes, $attributes ) = self::parseAttributes( $attributes_string );

			// Create wrapper
			$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%>%%children%%</%s>', $tag, $tag );
			$markup  = new Markup( $wrapper, $classes, $attributes );

			if ( in_array( strtolower( $tag ), [ 'script', 'style', 'noscript', 'textarea', 'title', 'xmp', 'plaintext' ], true ) ) {
				$markup->children( $inner_html );
				return $markup;
			}

			// Parse children recursively with depth tracking
			$children = self::parseChildren( $inner_html, $max_depth, $current_depth + 1 );

			// Group children if appropriate
			$children = self::maybeGroupChildren( $children, $tag );

			foreach ( $children as $child ) {
				$markup->children( $child );
			}

			return $markup;
		}

		// Try self-closing tag
		if ( preg_match( '/^<(\w+)([^>]*)\/?>$/', $html, $matches ) ) {
			$tag               = $matches[1];
			$attributes_string = $matches[2];

			// Parse classes and attributes (optimized)
			list( $classes, $attributes ) = self::parseAttributes( $attributes_string );

			// Create self-closing wrapper
			$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%/>', $tag );
			return new Markup( $wrapper, $classes, $attributes );
		}

		// Check if HTML contains multiple root-level elements
		// If so, wrap them in a container div
		if ( preg_match( '/<\w+/', $html ) ) {
			$children = self::parseChildren( $html, $max_depth, $current_depth );
			
			// If we found multiple elements, return a flow instead of wrapping
			if ( count( $children ) > 1 || ( count( $children ) === 1 && $children[0] instanceof Markup ) ) {
				return new MarkupFlow( $children );
			}
		}

		// If it's just text, return as string child
		return new Markup( '', [], [], '', [ $html ] );
	}

	/**
	 * Groups children in a MarkupFlow if appropriate based on the parent tag.
	 *
	 * This method determines if children should be grouped together. Children are grouped when:
	 * - There are multiple children (2 or more)
	 * - The children are mixed (contains both strings and Markup instances)
	 * - The parent tag is not a container element (ul, ol, table, etc.)
	 *
	 * This preserves the structure for cases like:
	 * - <li>Text <ul>...</ul></li> - grouped together
	 * - <p>Text <strong>bold</strong> more text</p> - grouped together
	 * - <ul><li>Item 1</li><li>Item 2</li></ul> - NOT grouped (container)
	 *
	 * @since 1.0.0
	 *
	 * @param array  $children The parsed children array.
	 * @param string $tag      The parent tag name.
	 * @return array Modified children array (may contain MarkupFlow).
	 */
	private static function maybeGroupChildren( array $children, string $tag ): array {
		// If only one child or empty, no grouping needed
		if ( count( $children ) <= 1 ) {
			return $children;
		}

		// Container elements that should NOT group their children
		$container_elements = [
			'ul', 'ol', 'dl',           // Lists
			'table', 'thead', 'tbody', 'tfoot', 'tr', // Tables
			'select', 'datalist',       // Form containers
			'nav', 'menu',              // Navigation containers
			'div', 'section', 'article', 'header', 'footer', 'aside', 'main', // Semantic containers
		];

		// Don't group for container elements
		if ( in_array( strtolower( $tag ), $container_elements, true ) ) {
			return $children;
		}

		// Check if children are mixed (contains both strings and Markup)
		$has_string = false;
		$has_markup = false;

		foreach ( $children as $child ) {
			if ( is_string( $child ) ) {
				$has_string = true;
			} elseif ( $child instanceof Markup ) {
				$has_markup = true;
			}

			// If we found both types, we should group them in a flow
			if ( $has_string && $has_markup ) {
				return [ new MarkupFlow( $children ) ];
			}
		}

		// Not mixed content, return as-is
		return $children;
	}

	/**
	 * Parses HTML attributes string and extracts classes and other attributes.
	 *
	 * This is an optimized helper method that parses all attributes in a single pass.
	 *
	 * @since 1.0.0
	 *
	 * @param string $attributes_string The HTML attributes string to parse.
	 * @return array Array containing [classes array, attributes array].
	 */
	private static function parseAttributes( string $attributes_string ): array {
		$classes    = [];
		$attributes = [];

		// Single regex to match all attributes at once
		if ( preg_match_all( '/(\w+)=["\']([^"\']*)["\']/', $attributes_string, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attr_name  = $match[1];
				$attr_value = $match[2];

				if ( 'class' === $attr_name ) {
					// Split classes and filter empty values
					$classes = array_values( array_filter( explode( ' ', $attr_value ), 'strlen' ) );
				} else {
					$attributes[ $attr_name ] = $attr_value;
				}
			}
		}

		return [ $classes, $attributes ];
	}

	/**
	 * Parses child elements from HTML string recursively.
	 *
	 * This helper method splits HTML content into individual elements
	 * and text nodes, parsing each one appropriately.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $html          The HTML string to parse for children.
	 * @param int|null $max_depth     Optional. Maximum parsing depth. Default null (unlimited).
	 * @param int      $current_depth Current depth level. Default 0.
	 * @return array Array of children (Markup instances or strings).
	 */
	private static function parseChildren( string $html, ?int $max_depth = null, int $current_depth = 0 ): array {
		$children = [];
		
		// Remove HTML comments
		$html = preg_replace( '/<!--(.|\s)*?-->/', '', $html );
		$html = trim( $html );

		if ( empty( $html ) ) {
			return $children;
		}

		$offset = 0;
		$length = strlen( $html );

		while ( $offset < $length ) {
			// Try to find next opening tag (search from current offset, not anchored)
			if ( preg_match( '/<(\w+)([^>]*)>/', $html, $matches, PREG_OFFSET_CAPTURE, $offset ) ) {
				$tag        = $matches[1][0];
				$tag_start  = $matches[0][1];
				$tag_string = $matches[0][0];

				// Check if there's text before this tag
				if ( $tag_start > $offset ) {
					$text = substr( $html, $offset, $tag_start - $offset );
					// Don't trim whitespace - preserve spaces between inline elements
					if ( '' !== $text ) {
						$children[] = $text;
					}
				}

				// Check if it's a self-closing tag (ends with /> or is a void element)
				$is_self_closing = preg_match( '/\/>$/', $tag_string );
				$void_elements   = [ 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr' ];
				$is_void         = in_array( strtolower( $tag ), $void_elements, true );

				if ( $is_self_closing || $is_void ) {
					// Self-closing or void tag - parse as single element
					$element    = $tag_string;
					$children[] = self::fromHtml( $element, $max_depth, $current_depth );
					$offset     = $tag_start + strlen( $tag_string );
					continue;
				}

				// Special handling for script/style/noscript/textarea/title/xmp/plaintext: take raw content until closing tag
				if ( in_array( strtolower( $tag ), [ 'script', 'style', 'noscript', 'textarea', 'title', 'xmp', 'plaintext' ], true ) ) {
					$close_tag = '</' . strtolower( $tag ) . '>';
					$close_pos = stripos( $html, $close_tag, $tag_start + strlen( $tag_string ) );
					if ( false !== $close_pos ) {
						$element_length = $close_pos + strlen( $close_tag ) - $tag_start;
						$element = substr( $html, $tag_start, $element_length );
						$children[] = self::fromHtml( $element, $max_depth, $current_depth );
						$offset = $tag_start + $element_length;
						continue;
					}
				}

				// Find matching closing tag
				$open_count = 1;
				$search_pos = $tag_start + strlen( $tag_string );

				while ( $open_count > 0 && $search_pos < $length ) {
					// Look for any tag (opening or closing) with the same name
					if ( preg_match( '/<(\/?)'.$tag.'(?:\s[^>]*)?>/i', $html, $tag_match, PREG_OFFSET_CAPTURE, $search_pos ) ) {
						$is_closing = ! empty( $tag_match[1][0] );
						$match_pos  = $tag_match[0][1];

						if ( $is_closing ) {
							--$open_count;
							if ( 0 === $open_count ) {
								// Found the matching closing tag
								$element_length = $match_pos + strlen( $tag_match[0][0] ) - $tag_start;
								$element        = substr( $html, $tag_start, $element_length );
								$children[]     = self::fromHtml( $element, $max_depth, $current_depth );
								$offset         = $tag_start + $element_length;
								break;
							}
						} else {
							++$open_count;
						}

						$search_pos = $match_pos + strlen( $tag_match[0][0] );
					} else {
						// No matching closing tag found
						break;
					}
				}

				if ( $open_count > 0 ) {
					// Unclosed tag, treat rest as text
					$text = substr( $html, $offset );
					// Don't trim whitespace - preserve spaces between inline elements
					if ( '' !== $text ) {
						$children[] = $text;
					}
					break;
				}
			} else {
				// No more tags, rest is text
				$text = substr( $html, $offset );
				// Don't trim whitespace - preserve spaces between inline elements
				if ( '' !== $text ) {
					$children[] = $text;
				}
				break;
			}
		}

		return $children;
	}

	/**
	 * Creates a div element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function div( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'div', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a span element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function span( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'span', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a paragraph element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function p( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'p', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a button element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The button label. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function button( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		// Ensure type attribute is set to button by default
		if ( ! isset( $attributes['type'] ) ) {
			$attributes['type'] = 'button';
		}
		$markup = self::create( 'button', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a link element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text       Optional. The link text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes (should include 'href'). Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function a( string $text = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'a', $classes, $attributes );
		if ( ! empty( $text ) ) {
			$markup->append( $text );
		}
		return $markup;
	}

	/**
	 * Creates a section element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function section( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'section', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an article element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function article( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'article', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a header element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function header( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'header', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a footer element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function footer( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'footer', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a nav element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function nav( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'nav', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h1 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h1( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h1', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h2 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h2( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h2', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h3 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h3( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h3', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h4 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h4( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h4', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h5 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h5( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h5', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an h6 heading element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h6( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'h6', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an unordered list element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function ul( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'ul', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates an ordered list element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function ol( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'ol', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

	/**
	 * Creates a list item element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function li( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		$markup = self::create( 'li', $classes, $attributes );
		if ( ! empty( $content ) ) {
			$markup->append( $content );
		}
		return $markup;
	}

}

