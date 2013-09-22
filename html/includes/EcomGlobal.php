<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

/**
 * 既存のグローバル変数の操作メソッド.
 * @author ikeda
 */
class EcomGlobal {

	public static function addJqueryReady( $file ) {

		global $JQUERY;
		if ( false === array_search( $file, $JQUERY["ready"] ) ) {
			$JQUERY["ready"][] = $file;
		}
		
	}

	public static function removeJQueryReady( $file ) {

		global $JQUERY;

		$index = array_search( $file, $JQUERY["ready"] );
		if ( false !== $index ) {
			$JQUERY["ready"][$index] = null;
		}

	}

	public static function addHeadJs( $file ) {

		global $COMUNI_HEAD_JS;
		if ( false === array_search( $file, $COMUNI_HEAD_JS ) ) {
			$COMUNI_HEAD_JS[] = $file;
		}

	}

	public static function removeHeadJs( $file ) {

		global $COMUNI_HEAD_JS;

		$index = array_search( $file, $COMUNI_HEAD_JS );
		if ( false !== $index ) {
			$COMUNI_HEAD_JS[$index] = null;
		}

	}

	public static function addFootJs( $file ) {

		global $COMUNI_FOOT_JS;
		if ( false === array_search( $file, $COMUNI_FOOT_JS ) ) {
			$COMUNI_FOOT_JS[] = $file;
		}

	}

	public static function removeFootJs( $file ) {

		global $COMUNI_FOOT_JS;

		$index = array_search( $file, $COMUNI_FOOT_JS );
		if ( false !== $index ) {
			$COMUNI_FOOT_JS[$index] = null;
		}

	}

	public static function addHeadJsRaw( $code ) {

		global $COMUNI_HEAD_JSRAW;
		if ( false === array_search( $code, $COMUNI_HEAD_JSRAW ) ) {
			$COMUNI_HEAD_JSRAW[] = $code;
		}

	}

	public static function removeHeadJsRaw( $code ) {

		global $COMUNI_HEAD_JSRAW;

		$index = array_search( $code, $COMUNI_HEAD_JSRAW );
		if ( false !== $index ) {
			$COMUNI_HEAD_JSRAW[$index] = null;
		}

	}

	public static function addFootJsRaw( $code ) {

		global $COMUNI_FOOT_JSRAW;
		if ( false === array_search( $code, $COMUNI_FOOT_JSRAW ) ) {
			$COMUNI_FOOT_JSRAW[] = $code;
		}

	}

	public static function removeFootJsRaw( $code ) {

		global $COMUNI_FOOT_JSRAW;

		$index = array_search( $code, $COMUNI_FOOT_JSRAW );
		if ( false !== $index ) {
			$COMUNI_FOOT_JSRAW[$index] = null;
		}

	}

	public static function addHeadCss( $file ) {

		global $COMUNI_HEAD_CSS;
		if ( false === array_search( $file, $COMUNI_HEAD_CSS ) ) {
			$COMUNI_HEAD_CSS[] = $file;
		}

	}

	public static function removeHeadCss( $file ) {

		global $COMUNI_HEAD_CSS;

		$index = array_search( $file, $COMUNI_HEAD_CSS );
		if ( false !== $index ) {
			$COMUNI_HEAD_CSS[$index] = null;
		}

	}

	public static function addFootCss( $file ) {

		global $COMUNI_FOOT_CSS;
		if ( false === array_search( $file, $COMUNI_FOOT_CSS ) ) {
			$COMUNI_FOOT_CSS[] = $file;
		}

	}

	public static function removeFootCss( $file ) {

		global $COMUNI_FOOT_CSS;

		$index = array_search( $file, $COMUNI_FOOT_CSS );
		if ( false !== $index ) {
			$COMUNI_FOOT_CSS[$index] = null;
		}

	}

	public static function addHeadCssRaw( $code ) {

		global $COMUNI_HEAD_CSSRAW;
		if ( false === array_search( $code, $COMUNI_HEAD_CSSRAW ) ) {
			$COMUNI_HEAD_CSSRAW[] = $code;
		}

	}

	public static function removeHeadCssRaw( $code ) {

		global $COMUNI_HEAD_CSSRAW;

		$index = array_search( $code, $COMUNI_HEAD_CSSRAW );
		if ( false !== $index ) {
			$COMUNI_HEAD_CSSRAW[$index] = null;
		}

	}

	public static function addFootCssRaw( $code ) {

		global $COMUNI_FOOT_CSSRAW;
		if ( false === array_search( $code, $COMUNI_FOOT_CSSRAW ) ) {
			$COMUNI_FOOT_CSSRAW[] = $code;
		}

	}

	public static function removeFootCssRaw( $code ) {

		global $COMUNI_FOOT_CSSRAW;

		$index = array_search( $code, $COMUNI_FOOT_CSSRAW );
		if ( false !== $index ) {
			$COMUNI_FOOT_CSSRAW[$index] = null;
		}

	}

	public static function addHeadHtml( $file ) {

		global $COMUNI_HEAD_HTML;
		if ( false === array_search( $file, $COMUNI_HEAD_HTML ) ) {
			$COMUNI_HEAD_HTML[] = $file;
		}

	}

	public static function removeHeadHtml( $file ) {

		global $COMUNI_HEAD_HTML;

		$index = array_search( $file, $COMUNI_HEAD_HTML );
		if ( false !== $index ) {
			$COMUNI_HEAD_HTML[$index] = null;
		}

	}

	public static function addFootHtml( $file ) {

		global $COMUNI_FOOT_HTML;
		if ( false === array_search( $file, $COMUNI_FOOT_HTML ) ) {
			$COMUNI_FOOT_HTML[] = $file;
		}

	}

	public static function removeFootHtml( $file ) {

		global $COMUNI_FOOT_HTML;

		$index = array_search( $file, $COMUNI_FOOT_HTML );
		if ( false !== $index ) {
			$COMUNI_FOOT_HTML[$index] = null;
		}

	}

	public static function addTPath( $path ) {

		global $COMUNI_TPATH;
		if ( false === array_search( $path, $COMUNI_TPATH ) ) {
			$COMUNI_TPATH[] = $path;
		}

	}

	public static function removeTPath( $path ) {

		global $COMUNI_TPATH;

		$index = array_search( $path, $COMUNI_TPATH );
		if ( false !== $index ) {
			$COMUNI_TPATH[$index] = null;
		}

	}

}
?>
