<?php
/**
 * Markup Factory for creating Markup instances from various sources.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup;

/**
 * Class MarkupFactory
 *
 * Factory class for creating Markup instances using convenient methods.
 * Provides two main creation patterns:
 * - fromString(): Quick creation of simple elements (first level only)
 * - fromHtml(): Recursive parsing of HTML into complete Markup trees
 *
 * @since 1.1.0
 */
class MarkupFactory {

	/**
	 * Creates a Markup instance from an enum implementing MarkupElementInterface.
	 *
	 * This method allows you to create Markup from predefined element configurations.
	 * You can use built-in enums like HtmlTag, or create your own custom enums.
	 *
	 * Example usage:
	 * ```php
	 * use MaxPertici\Markup\Elements\HtmlTag;
	 *
	 * $div = MarkupFactory::fromEnum(HtmlTag::DIV, [$child1, $child2]);
	 * $section = MarkupFactory::fromEnum(HtmlTag::SECTION, [$content], ['main-section']);
	 * 
	 * // With custom enum
	 * $card = MarkupFactory::fromEnum(MyComponent::CARD, [$header, $body]);
	 * ```
	 *
	 * @since 1.2.0
	 *
	 * @param MarkupElementInterface $element    The element enum to use.
	 * @param array                  $children   Optional. Children elements. Default empty array.
	 * @param array                  $classes    Optional. Additional CSS classes. Default empty array.
	 * @param array                  $attributes Optional. Additional HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function fromEnum(
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
	 * Creates a simple Markup instance from basic parameters (first level only).
	 *
	 * This method creates a Markup with a single wrapper element containing
	 * one string child. Perfect for quick element creation without nesting.
	 *
	 * Example usage:
	 * ```php
	 * $div = MarkupFactory::fromString('div', 'Hello World', ['container'], ['id' => 'main']);
	 * // Output: <div class="container" id="main">Hello World</div>
	 * ```
	 *
	 * @since 1.1.0
	 *
	 * @param string $tag        The HTML tag name (e.g., 'div', 'p', 'span').
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes (associative array). Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function fromString(
		string $tag,
		string $content = '',
		array $classes = [],
		array $attributes = []
	): Markup {
		$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%>%%children%%</%s>', $tag, $tag );

		$markup = new Markup( $wrapper, $classes, $attributes );

		if ( ! empty( $content ) ) {
			$markup->children( $content );
		}

		return $markup;
	}

	/**
	 * Creates a Markup instance by recursively parsing an HTML string.
	 *
	 * This method parses an HTML string and creates a complete Markup structure
	 * with all nested elements. Each HTML element becomes a Markup instance,
	 * preserving the entire tree structure.
	 *
	 * Example usage:
	 * ```php
	 * $html = '<div class="card"><h2>Title</h2><p>Content</p></div>';
	 * $markup = MarkupFactory::fromHtml($html);
	 * echo $markup->render();
	 * 
	 * // Limit parsing depth to 3 levels
	 * $markup = MarkupFactory::fromHtml($html, 3);
	 * ```
	 *
	 * @since 1.1.0
	 *
	 * @param string   $html          The HTML string to parse.
	 * @param int|null $max_depth     Optional. Maximum parsing depth. Default null (unlimited).
	 * @param int      $current_depth Internal. Current depth level. Default 0.
	 * @return Markup A new Markup instance representing the parsed HTML tree.
	 */
	public static function fromHtml( string $html, ?int $max_depth = null, int $current_depth = 0 ): Markup {
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

			// Parse classes
			$classes = [];
			if ( preg_match( '/class=["\']([^"\']+)["\']/', $attributes_string, $class_matches ) ) {
				$classes            = array_filter( explode( ' ', $class_matches[1] ) );
				$attributes_string  = preg_replace( '/\s*class=["\'][^"\']*["\']/', '', $attributes_string );
			}

			// Parse other attributes
			$attributes = [];
			preg_match_all( '/(\w+)=["\']([^"\']*)["\']/', $attributes_string, $attr_matches, PREG_SET_ORDER );
			foreach ( $attr_matches as $match ) {
				$attributes[ $match[1] ] = $match[2];
			}

			// Create wrapper
			$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%>%%children%%</%s>', $tag, $tag );
			$markup  = new Markup( $wrapper, $classes, $attributes );

			// Parse children recursively with depth tracking
			$children = self::parseChildren( $inner_html, $max_depth, $current_depth + 1 );
			foreach ( $children as $child ) {
				$markup->children( $child );
			}

			return $markup;
		}

		// Try self-closing tag
		if ( preg_match( '/^<(\w+)([^>]*)\/?>$/', $html, $matches ) ) {
			$tag               = $matches[1];
			$attributes_string = $matches[2];

			// Parse classes
			$classes = [];
			if ( preg_match( '/class=["\']([^"\']+)["\']/', $attributes_string, $class_matches ) ) {
				$classes            = array_filter( explode( ' ', $class_matches[1] ) );
				$attributes_string  = preg_replace( '/\s*class=["\'][^"\']*["\']/', '', $attributes_string );
			}

			// Parse other attributes
			$attributes = [];
			preg_match_all( '/(\w+)=["\']([^"\']*)["\']/', $attributes_string, $attr_matches, PREG_SET_ORDER );
			foreach ( $attr_matches as $match ) {
				$attributes[ $match[1] ] = $match[2];
			}

			// Create self-closing wrapper
			$wrapper = sprintf( '<%s class="%%classes%%" %%attributes%%/>', $tag );
			return new Markup( $wrapper, $classes, $attributes );
		}

		// If it's just text, return as string child
		return new Markup( '', [], [], '', [ $html ] );
	}

	/**
	 * Parses child elements from HTML string recursively.
	 *
	 * This helper method splits HTML content into individual elements
	 * and text nodes, parsing each one appropriately.
	 *
	 * @since 1.1.0
	 *
	 * @param string   $html          The HTML string to parse for children.
	 * @param int|null $max_depth     Optional. Maximum parsing depth. Default null (unlimited).
	 * @param int      $current_depth Current depth level. Default 0.
	 * @return array Array of children (Markup instances or strings).
	 */
	private static function parseChildren( string $html, ?int $max_depth = null, int $current_depth = 0 ): array {
		$children = [];
		$html     = trim( $html );

		if ( empty( $html ) ) {
			return $children;
		}

		$offset = 0;
		$length = strlen( $html );

		while ( $offset < $length ) {
			// Try to find next opening tag
			if ( preg_match( '/<(\w+)([^>]*)>/A', $html, $matches, 0, $offset ) ) {
				$tag = $matches[1];

				// Check if there's text before this tag
				$tag_start = strpos( $html, $matches[0], $offset );
				if ( $tag_start > $offset ) {
					$text = substr( $html, $offset, $tag_start - $offset );
					$text = trim( $text );
					if ( ! empty( $text ) ) {
						$children[] = $text;
					}
				}

				// Find the closing tag
				$tag_pos    = $tag_start;
				$open_count = 1;
				$search_pos = $tag_pos + strlen( $matches[0] );

				// Check if it's a self-closing tag
				if ( preg_match( '/\/>$/', $matches[0] ) ) {
					// Self-closing tag
					$element    = substr( $html, $tag_pos, strlen( $matches[0] ) );
					$children[] = self::fromHtml( $element, $max_depth, $current_depth );
					$offset     = $search_pos;
					continue;
				}

				// Find matching closing tag
				while ( $open_count > 0 && $search_pos < $length ) {
					// Look for any tag (opening or closing)
					if ( preg_match( '/<(\/?)'.$tag.'(?:\s[^>]*)?>/', $html, $tag_match, PREG_OFFSET_CAPTURE, $search_pos ) ) {
						$is_closing = ! empty( $tag_match[1][0] );
						$match_pos  = $tag_match[0][1];

						if ( $is_closing ) {
							--$open_count;
							if ( 0 === $open_count ) {
								// Found the matching closing tag
								$element_length = $match_pos + strlen( $tag_match[0][0] ) - $tag_pos;
								$element        = substr( $html, $tag_pos, $element_length );
								$children[]     = self::fromHtml( $element, $max_depth, $current_depth );
								$offset         = $tag_pos + $element_length;
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
					$text = trim( $text );
					if ( ! empty( $text ) ) {
						$children[] = $text;
					}
					break;
				}
			} else {
				// No more tags, rest is text
				$text = substr( $html, $offset );
				$text = trim( $text );
				if ( ! empty( $text ) ) {
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
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function div( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'div', $content, $classes, $attributes );
	}

	/**
	 * Creates a span element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function span( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'span', $content, $classes, $attributes );
	}

	/**
	 * Creates a paragraph element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function p( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'p', $content, $classes, $attributes );
	}

	/**
	 * Creates a button element.
	 *
	 * @since 1.2.0
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
		return self::fromString( 'button', $content, $classes, $attributes );
	}

	/**
	 * Creates a link element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $href       The link URL.
	 * @param string $text       Optional. The link text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. Additional HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function a( string $href, string $text = '', array $classes = [], array $attributes = [] ): Markup {
		$attributes['href'] = $href;
		return self::fromString( 'a', $text, $classes, $attributes );
	}

	/**
	 * Creates a section element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function section( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'section', $content, $classes, $attributes );
	}

	/**
	 * Creates an article element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function article( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'article', $content, $classes, $attributes );
	}

	/**
	 * Creates a header element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function header( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'header', $content, $classes, $attributes );
	}

	/**
	 * Creates a footer element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function footer( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'footer', $content, $classes, $attributes );
	}

	/**
	 * Creates a nav element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function nav( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'nav', $content, $classes, $attributes );
	}

	/**
	 * Creates an h1 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h1( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h1', $content, $classes, $attributes );
	}

	/**
	 * Creates an h2 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h2( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h2', $content, $classes, $attributes );
	}

	/**
	 * Creates an h3 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h3( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h3', $content, $classes, $attributes );
	}

	/**
	 * Creates an h4 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h4( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h4', $content, $classes, $attributes );
	}

	/**
	 * Creates an h5 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h5( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h5', $content, $classes, $attributes );
	}

	/**
	 * Creates an h6 heading element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The heading text. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function h6( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'h6', $content, $classes, $attributes );
	}

	/**
	 * Creates an unordered list element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function ul( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'ul', $content, $classes, $attributes );
	}

	/**
	 * Creates an ordered list element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function ol( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'ol', $content, $classes, $attributes );
	}

	/**
	 * Creates a list item element.
	 *
	 * @since 1.2.0
	 *
	 * @param string $content    Optional. The text content. Default empty string.
	 * @param array  $classes    Optional. CSS classes. Default empty array.
	 * @param array  $attributes Optional. HTML attributes. Default empty array.
	 * @return Markup A new Markup instance.
	 */
	public static function li( string $content = '', array $classes = [], array $attributes = [] ): Markup {
		return self::fromString( 'li', $content, $classes, $attributes );
	}

}

