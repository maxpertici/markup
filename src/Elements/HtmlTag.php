<?php
/**
 * Built-in HTML tag enum.
 *
 * @package MaxPertici\Markup
 */

namespace MaxPertici\Markup\Elements;

use MaxPertici\Markup\MarkupElementInterface;

/**
 * Enum HtmlTag
 *
 * Provides common HTML tags with sensible defaults.
 * This is a built-in implementation of MarkupElementInterface.
 *
 * @since 1.2.0
 */
enum HtmlTag: string implements MarkupElementInterface {

	case DIV     = 'div';
	case SPAN    = 'span';
	case P       = 'p';
	case SECTION = 'section';
	case ARTICLE = 'article';
	case HEADER  = 'header';
	case FOOTER  = 'footer';
	case MAIN    = 'main';
	case ASIDE   = 'aside';
	case NAV     = 'nav';
	case UL      = 'ul';
	case OL      = 'ol';
	case LI      = 'li';
	case H1      = 'h1';
	case H2      = 'h2';
	case H3      = 'h3';
	case H4      = 'h4';
	case H5      = 'h5';
	case H6      = 'h6';
	case BUTTON  = 'button';
	case A       = 'a';
	case FORM    = 'form';
	case LABEL   = 'label';
	case INPUT   = 'input';

	/**
	 * Gets the HTML wrapper template for this tag.
	 *
	 * @since 1.2.0
	 *
	 * @return string The wrapper template.
	 */
	public function wrapper(): string {
		// Self-closing tags
		if ( in_array( $this, [ self::INPUT ], true ) ) {
			return sprintf( '<%s class="%%classes%%" %%attributes%%/>', $this->value );
		}

		// Regular tags
		return sprintf(
			'<%s class="%%classes%%" %%attributes%%>%%children%%</%s>',
			$this->value,
			$this->value
		);
	}

	/**
	 * Gets the CSS classes for this tag.
	 *
	 * @since 1.2.0
	 *
	 * @return array Array of CSS class names.
	 */
	public function classes(): array {
		// No default classes for basic HTML tags
		return [];
	}

	/**
	 * Gets the HTML attributes for this tag.
	 *
	 * @since 1.2.0
	 *
	 * @return array Associative array of attribute names and values.
	 */
	public function attributes(): array {
		return match ( $this ) {
			self::BUTTON => [ 'type' => 'button' ],
			default      => [],
		};
	}

	/**
	 * Gets the children wrapper template.
	 *
	 * @since 1.2.0
	 *
	 * @return string The children wrapper template, or empty string.
	 */
	public function childrenWrapper(): string {
		return match ( $this ) {
			self::UL, self::OL => '<li>%child%</li>',
			default            => '',
		};
	}

}

