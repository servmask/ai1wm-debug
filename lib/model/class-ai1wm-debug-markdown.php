<?php
/**
 * Copyright (C) 2014-2026 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wm_Debug_Markdown {

	/**
	 * Convert a subset of markdown to HTML
	 *
	 * Supports: headings (#/##/###), bold (**), unordered lists (- ),
	 * inline code (`), horizontal rules (---), and paragraphs.
	 *
	 * @param  string $text Raw markdown text
	 * @return string       HTML output
	 */
	public static function to_html( $text ) {
		$text  = str_replace( array( "\r\n", "\r" ), "\n", $text );
		$lines = explode( "\n", $text );

		$html    = '';
		$in_list = false;

		foreach ( $lines as $line ) {
			// Horizontal rule
			if ( preg_match( '/^---+$/', trim( $line ) ) ) {
				if ( $in_list ) {
					$html   .= "</ul>\n";
					$in_list = false;
				}
				$html .= "<hr />\n";
				continue;
			}

			// Headings
			if ( preg_match( '/^(#{1,3})\s+(.+)$/', $line, $m ) ) {
				if ( $in_list ) {
					$html   .= "</ul>\n";
					$in_list = false;
				}
				$level = strlen( $m[1] );
				$html .= '<h' . $level . '>' . self::inline( $m[2] ) . '</h' . $level . ">\n";
				continue;
			}

			// Unordered list item (- or  - for nested context, treat flat)
			if ( preg_match( '/^\s*-\s+(.+)$/', $line, $m ) ) {
				if ( ! $in_list ) {
					$html   .= "<ul>\n";
					$in_list = true;
				}
				$html .= '<li>' . self::inline( $m[1] ) . "</li>\n";
				continue;
			}

			// Blank line
			if ( trim( $line ) === '' ) {
				if ( $in_list ) {
					$html   .= "</ul>\n";
					$in_list = false;
				}
				continue;
			}

			// Paragraph text
			if ( $in_list ) {
				$html   .= "</ul>\n";
				$in_list = false;
			}
			$html .= '<p>' . self::inline( $line ) . "</p>\n";
		}

		if ( $in_list ) {
			$html .= "</ul>\n";
		}

		return $html;
	}

	/**
	 * Process inline markdown: bold and code
	 *
	 * @param  string $text
	 * @return string
	 */
	private static function inline( $text ) {
		$text = esc_html( $text );

		// Inline code (must come before bold so backticks inside bold work)
		$text = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );

		// Bold
		$text = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text );

		return $text;
	}
}
