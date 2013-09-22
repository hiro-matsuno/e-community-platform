<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/**
 * Description of AutoLinkFilter
 *
 * @author ikeda
 */
class AutoLinkFilter {

	static public function filter( $text ) {
		return AutoLinkFilter::replaceUrlRecursive( $text );
	}

	static public function replaceUrlRecursive( $text, $inAnchor=false ) {

		if ( preg_match( "/^(<\s*a[^>]*>)(.*)$/", $text, $match ) ) {
			return $match[1].AutoLinkFilter::replaceUrlRecursive( $match[2], true );
		} else if ( preg_match( "/^(<\s*[^>]*>)(.*)$/", $text, $match ) ) {
			return $match[1].AutoLinkFilter::replaceUrlRecursive( $match[2], false );
		} else if ( $inAnchor and preg_match( "/^(.*?<\s*\/\s*a\s*>)(.*)$/", $text, $match ) ) {
			return $match[1].AutoLinkFilter::replaceUrlRecursive( $match[2] );
		} else if ( preg_match( "/^(.*?)(<.*)$/", $text, $match ) ) {
			return AutoLinkFilter::replaceUrlRecursive( $match[1] )
				.AutoLinkFilter::replaceUrlRecursive( $match[2] );
		} else {
			$pattern = "%((?:https?://|ftp://|www\.)(?:[\d\w-/\.$,;:&=?!*~@#_()\%]+))%is";
			return preg_replace( $pattern, "<a href='$1' target='_parent'>$1</a>", $text );
		}

	}

}
?>
