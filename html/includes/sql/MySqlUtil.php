<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

global $method_exists;

if ( "5.0" == substr( phpversion(), 0, 3 ) ) {
	$method_exists = "method_exists50";
} else {
	$method_exists = "method_exists";
}

/**
 * Description of MySqlUtil
 *
 * @author ikeda
 */
class MySqlUtil {

	/**
	 * 与えられたカラム名のテーブルレコード変数について、
	 * 定義されている setter メソッドの名前を返す.
	 *
	 * @attention public とされているが、モジュール外からアクセスの必要は無い.
	 *
	 * @param string $className
	 * @param string $key
	 * @return string
	 */
	static public function getSetterName( $className, $key ) {

		global $method_exists;

		$methodName1 = "set_".$key;
		$methodName2 = preg_replace_callback( "/_([a-zA-Z])/",
				"replaceMySqlDataMethodNameCallback",
				$methodName1 );

		if ( $method_exists( $className, $methodName1 ) ) {
			return $methodName1;
		} else if ( $method_exists( $className, $methodName2 ) ) {
			return $methodName2;
		} else {
			throw new NoSuchFunctionException("no setter for $key of $className");
		}

	}

	/**
	 * 与えられたカラム名のテーブルレコード変数について、
	 * 定義されている getter メソッドの名前を返す.
	 *
	 * @attention public とされているが、モジュール外からアクセスの必要は無い.
	 *
	 * @param string $className
	 * @param string $key
	 * @return string
	 */
	static public function getGetterName( $className, $key ) {

		global $method_exists;

		$methodName1 = "get_".$key;
		$methodName2 = preg_replace_callback( "/_([a-zA-Z])/",
				"replaceMySqlDataMethodNameCallback",
				$methodName1 );

		if ( $method_exists( $className, $methodName1 ) ) {
			return $methodName1;
		} else if ( $method_exists( $className, $methodName2 ) ) {
			return $methodName2;
		} else {
			throw new NoSuchFunctionException("no getter for $key of $className");
		}

	}

	/**
	 * 与えられた文字列についてクエリに使える文字列にするためエスケープ処理し、
	 * シングルクォートで囲った文字列を返す.
	 * @param string $str
	 * @return string
	 */
	static public function decorateText( $str ) {

		return "'".mysql_real_escape_string( $str )."'";

	}

}

function replaceMySqlDataMethodNameCallback( $match ) {

	return strtoupper( $match[1] );

}

?>
