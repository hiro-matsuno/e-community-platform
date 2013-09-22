<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

/**
 * データの取得に失敗した場合に発生する.
 */
class DataNotFoundException extends Exception {

	public function __construct( $message=null ) {
		parent::__construct( $message );
	}

}

/**
 * 直接アクセスするべきでない PHP ファイルにアクセスした場合に発生する.
 */
class IllegalAccessException extends Exception {

	public function __construct( $message=null ) {
		parent::__construct( $message );
	}

}

/**
 * SQL オペレーションに失敗した場合に発生する.
 */
class SQLException extends Exception {
	
	public function __construct( $message=null ) {
		if ( null === $message ) $message = mysql_error();
		parent::__construct( $message );
	} 
	
}

//	PHP5.1以降はシステムで定義されている
if ( !class_exists( "InvalidArgumentException" ) ) {

	/**
	 * 引数として不正な値が入力された場合に発生する.
	 */
	class InvalidArgumentException extends Exception {

		public function __construct( $message=null ) {
			parent::__construct( $message );
		}

	}
	
}

/**
 * アクセスできない機能へのアクセスがあった場合に発生する.
 */
class PermissionDeniedException extends Exception {

	public function __construct( $message=null ) {
		parent::__construct( $message );
	}

}

class NoSuchFunctionException extends Exception {

	public function __construct( $message=null ) {
		parent::__construct( $message );
	}

}

?>