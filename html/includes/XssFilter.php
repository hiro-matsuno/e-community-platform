<?php

/**
 * XSS攻撃への対処として入力文字列をサニタイズするためのクラス.
 *
 * HTMLとして出力される可能性のあるユーザ入力は、必ずこのフィルタか
 * htmlspecialchars 関数を通してから利用するようにする.
 *
 * このソースコードは、drupal 6.16 の modules/filter/filter.module から引用、
 * 改変して作成した.
 *
 * All Drupal code is Copyright 2001 - 2009 by the original authors.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.
 *
 * Drupal is a registered trademark of Dries Buytaert.
 *
 * Drupal includes works under other copyright notices and distributed
 * according to the terms of the GNU General Public License or a compatible
 * license, including:
 *
 *   jQuery - Copyright (c) 2008 - 2009 John Resig
 *
 *
 * @author ikeda
 */
class XssFilter {

	/**
	 * Filters XSS. Based on kses by Ulf Harnhammar, see
	 * http://sourceforge.net/projects/kses
	 *
	 * For examples of various XSS attacks, see:
	 * http://ha.ckers.org/xss.html
	 *
	 * This code does four things:
	 * - Removes characters and constructs that can trick browsers
	 * - Makes sure all HTML entities are well-formed
	 * - Makes sure all HTML tags and attributes are well-formed
	 * - Makes sure no HTML tags contain URLs with a disallowed protocol (e.g. javascript:)
	 *
	 * @param $string
	 *   The string with raw HTML in it. It will be stripped of everything that can cause
	 *   an XSS attack.
	 * @param $allowed_tags
	 *   An array of allowed tags.
	 *
	 */
	static public function filter( $string,
							$allowed_tags = array('a', 'em', 'strong', 'cite', 'code', 'br', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'img', 'embed', 'noembed')) {
		// Only operate on valid UTF-8 strings. This is necessary to prevent cross
		// site scripting issues on Internet Explorer 6.
		if (!XssFilter::validate_utf8($string)) {
			return '';
		}
		// Store the input format
		XssFilter::_filter_xss_split($allowed_tags, TRUE);
		// Remove NUL characters (ignored by some browsers)
		$string = str_replace(chr(0), '', $string);
		// Remove Netscape 4 JS entities
		$string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

		// Defuse all HTML entities
		$string = str_replace('&', '&amp;', $string);
		// Change back only well-formed entities in our whitelist
		// Decimal numeric entities
		$string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
		// Hexadecimal numeric entities
		$string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
		// Named entities
		$string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

		$string = preg_replace_callback('%
(
<(?=[^a-zA-Z!/])  # a lone <
|                 # or
<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
|                 # or
>                 # just a >
)%x', '_filter_xss_split_wrap', $string);

		return $string;
	}

	/**
	 * Processes an HTML tag.
	 *
	 * @param @m
	 *   An array with various meaning depending on the value of $store.
	 *   If $store is TRUE then the array contains the allowed tags.
	 *   If $store is FALSE then the array has one element, the HTML tag to process.
	 * @param $store
	 *   Whether to store $m.
	 * @return
	 *   If the element isn't allowed, an empty string. Otherwise, the cleaned up
	 *   version of the HTML element.
	 */
	static public function _filter_xss_split($m, $store = FALSE) {
		static $allowed_html;

		if ($store) {
			$allowed_html = array_flip($m);
			return;
		}

		$string = $m[1];

		if (substr($string, 0, 1) != '<') {
			// We matched a lone ">" character
			return '&gt;';
		}
		else if (strlen($string) == 1) {
			// We matched a lone "<" character
			return '&lt;';
		}

		if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $string, $matches)) {
			// Seriously malformed
			return '';
		}

		$slash = trim($matches[1]);
		$elem = &$matches[2];
		$attrlist = &$matches[3];

		if (!isset($allowed_html[strtolower($elem)])) {
			// Disallowed HTML element
			return '';
		}

		if ($slash != '') {
			return "</$elem>";
		}

		// Is there a closing XHTML slash at the end of the attributes?
		// In PHP 5.1.0+ we could count the changes, currently we need a separate match
		$xhtml_slash = preg_match('%\s?/\s*$%', $attrlist) ? ' /' : '';
		$attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist);

		// Clean up attributes
		$attr2 = implode(' ', XssFilter::_filter_xss_attributes($attrlist));
		$attr2 = preg_replace('/[<>]/', '', $attr2);
		$attr2 = strlen($attr2) ? ' '. $attr2 : '';

		return "<$elem$attr2$xhtml_slash>";
	}

	/**
	 * Processes a string of HTML attributes.
	 *
	 * @return
	 *   Cleaned up version of the HTML attributes.
	 */
	static private function _filter_xss_attributes($attr) {
		$attrarr = array();
		$mode = 0;
		$attrname = '';

		while (strlen($attr) != 0) {
			// Was the last operation successful?
			$working = 0;

			switch ($mode) {
				case 0:
				// Attribute name, href for instance
					if (preg_match('/^([-a-zA-Z]+)/', $attr, $match)) {
						$attrname = strtolower($match[1]);
						$skip = ($attrname == 'style' || substr($attrname, 0, 2) == 'on');
						$working = $mode = 1;
						$attr = preg_replace('/^[-a-zA-Z]+/', '', $attr);
					}

					break;

				case 1:
				// Equals sign or valueless ("selected")
					if (preg_match('/^\s*=\s*/', $attr)) {
						$working = 1;
						$mode = 2;
						$attr = preg_replace('/^\s*=\s*/', '', $attr);
						break;
					}

					if (preg_match('/^\s+/', $attr)) {
						$working = 1;
						$mode = 0;
						if (!$skip) {
							$attrarr[] = $attrname;
						}
						$attr = preg_replace('/^\s+/', '', $attr);
					}

					break;

				case 2:
				// Attribute value, a URL after href= for instance
					if (preg_match('/^"([^"]*)"(\s+|$)/', $attr, $match)) {
						$thisval = XssFilter::filter_xss_bad_protocol($match[1]);

						if (!$skip) {
							$attrarr[] = "$attrname=\"$thisval\"";
						}
						$working = 1;
						$mode = 0;
						$attr = preg_replace('/^"[^"]*"(\s+|$)/', '', $attr);
						break;
					}

					if (preg_match("/^'([^']*)'(\s+|$)/", $attr, $match)) {
						$thisval = XssFilter::filter_xss_bad_protocol($match[1]);

						if (!$skip) {
							$attrarr[] = "$attrname='$thisval'";
							;
						}
						$working = 1;
						$mode = 0;
						$attr = preg_replace("/^'[^']*'(\s+|$)/", '', $attr);
						break;
					}

					if (preg_match("%^([^\s\"']+)(\s+|$)%", $attr, $match)) {
						$thisval = XssFilter::filter_xss_bad_protocol($match[1]);

						if (!$skip) {
							$attrarr[] = "$attrname=\"$thisval\"";
						}
						$working = 1;
						$mode = 0;
						$attr = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attr);
					}

					break;
			}

			if ($working == 0) {
				// not well formed, remove and try again
				$attr = preg_replace('/
^
(
"[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
|               # or
\'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
|               # or
\S              # - a non-whitespace character
)*              # any number of the above three
\s*             # any number of whitespaces
/x', '', $attr);
				$mode = 0;
			}
		}

		// the attribute list ends with a valueless attribute like "selected"
		if ($mode == 1) {
			$attrarr[] = $attrname;
		}
		return $attrarr;
	}

	/**
	 * Processes an HTML attribute value and ensures it does not contain an URL
	 * with a disallowed protocol (e.g. javascript:)
	 *
	 * @param $string
	 *   The string with the attribute value.
	 * @param $decode
	 *   Whether to decode entities in the $string. Set to FALSE if the $string
	 *   is in plain text, TRUE otherwise. Defaults to TRUE.
	 * @return
	 *   Cleaned up and HTML-escaped version of $string.
	 */
	static private function filter_xss_bad_protocol($string, $decode = TRUE) {
		static $allowed_protocols;
		if (!isset($allowed_protocols)) {
			$allowed_protocols = array_flip( array('http', 'https', 'ftp', 'news', 'nntp', 'telnet', 'mailto', 'irc', 'ssh', 'sftp', 'webcal', 'rtsp') );
		}

		// Get the plain text representation of the attribute value (i.e. its meaning).
//		if ($decode) {
//			$string = decode_entities($string);
//		}

		// Iteratively remove any invalid protocol found.

		do {
			$before = $string;
			$colonpos = strpos($string, ':');
			if ($colonpos > 0) {
				// We found a colon, possibly a protocol. Verify.
				$protocol = substr($string, 0, $colonpos);
				// If a colon is preceded by a slash, question mark or hash, it cannot
				// possibly be part of the URL scheme. This must be a relative URL,
				// which inherits the (safe) protocol of the base document.
				if (preg_match('![/?#]!', $protocol)) {
					break;
				}
				// Per RFC2616, section 3.2.3 (URI Comparison) scheme comparison must be case-insensitive
				// Check if this is a disallowed protocol.
				if (!isset($allowed_protocols[strtolower($protocol)])) {
					$string = substr($string, $colonpos + 1);
				}
			}
		} while ($before != $string);
		return XssFilter::check_plain($string);
	}

	/**
	 * Checks whether a string is valid UTF-8.
	 *
	 * All functions designed to filter input should use drupal_validate_utf8
	 * to ensure they operate on valid UTF-8 strings to prevent bypass of the
	 * filter.
	 *
	 * When text containing an invalid UTF-8 lead byte (0xC0 - 0xFF) is presented
	 * as UTF-8 to Internet Explorer 6, the program may misinterpret subsequent
	 * bytes. When these subsequent bytes are HTML control characters such as
	 * quotes or angle brackets, parts of the text that were deemed safe by filters
	 * end up in locations that are potentially unsafe; An onerror attribute that
	 * is outside of a tag, and thus deemed safe by a filter, can be interpreted
	 * by the browser as if it were inside the tag.
	 *
	 * This function exploits preg_match behaviour (since PHP 4.3.5) when used
	 * with the u modifier, as a fast way to find invalid UTF-8. When the matched
	 * string contains an invalid byte sequence, it will fail silently.
	 *
	 * preg_match may not fail on 4 and 5 octet sequences, even though they
	 * are not supported by the specification.
	 *
	 * The specific preg_match behaviour is present since PHP 4.3.5.
	 *
	 * @param $text
	 *   The text to check.
	 * @return
	 *   TRUE if the text is valid UTF-8, FALSE if not.
	 */
	static private function validate_utf8( $text ) {
		if (strlen($text) == 0) {
			return TRUE;
		}
		return (preg_match('/^./us', $text) == 1);
	}

	/**
	 * Encode special characters in a plain-text string for display as HTML.
	 *
	 * Uses drupal_validate_utf8 to prevent cross site scripting attacks on
	 * Internet Explorer 6.
	 */
	static private function check_plain($text) {
		$text = htmlspecialchars_decode( $text, ENT_QUOTES );
		return XssFilter::validate_utf8($text) ? htmlspecialchars($text, ENT_QUOTES) : '';
	}

}

function _filter_xss_split_wrap( $m ) {
	return XssFilter::_filter_xss_split( $m );
}

?>
