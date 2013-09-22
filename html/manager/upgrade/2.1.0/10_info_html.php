<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/../../../lib.php";

$result = mysql_query( "CREATE TABLE IF NOT EXISTS info_html ("
						." id bigint,"
						." name TEXT," /* 設定の名称 */
						." module TEXT," /* 設定を行ったモジュール */
						." html TEXT,"   /* 表示するHTML */
						." pos TEXT,"     /* 表示位置 LEFT,CENTER,RIGHT,TOP,BOTTOM */
						." enabled tinyint"  /* 表示が有効化されているかどうか */
						." ) DEFAULT CHARSET=utf8" );
						
if ( !$result ) {
	print ( __FILE__." の実行中にエラーが発生しました.<br/>\n".mysql_error() );
	throw new Exception( __FILE__." の実行中にエラーが発生しました.<br/>\n" );
}

?>