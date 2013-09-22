<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/sql/MySqlConnection.php";
require_once dirname(__FILE__)."/sql/MySqlData.php";
require_once dirname(__FILE__)."/sql/MySqlDatabase.php";
require_once dirname(__FILE__)."/sql/MySqlResult.php";
require_once dirname(__FILE__)."/sql/MySqlStatement.php";
require_once dirname(__FILE__)."/sql/MySqlTable.php";

//-----------------------------------------------------
// * connect db
//-----------------------------------------------------
function mysql_connect_ecom() {

	global $mysql_link;
	global $mysql_connection;

	try {

		$mysql_connection = new MySqlConnection( CONF_MYSQL_HOST, CONF_MYSQL_USER, CONF_MYSQL_PASSWD );
		$mysql_connection->connect();
		$mysql_connection->useDatabase( CONF_MYSQL_DB );

		MySqlPlaneStatement::execNow( "set names utf8" );

		$mysql_link = $mysql_connection->getConnection();

	} catch ( Exception $e ) {
		die( $e->__toString() );
	}

}

function mysql_get_ecom_connection() {

	global $mysql_link;

	return $mysql_link;
	
}

//-----------------------------------------------------
// * mysql の select 用クエリー処理 (複数行)
//-----------------------------------------------------
function mysql_full() {
	$args = func_get_args();
	$fmt  = array_shift($args);
	$q    = vsprintf($fmt, $args);

	$r = mysql_query($q);

	if ($r && (mysql_num_rows($r) > 0)) {
		return $r;
	}
	else {
		return false;
	}
}

//-----------------------------------------------------
// * mysql の insert&update&delete 用クエリー処理
//-----------------------------------------------------
function mysql_exec() {
	$args = func_get_args();
	$fmt  = array_shift($args);
	$q    = vsprintf($fmt, $args);

	return mysql_query($q);
}

//-----------------------------------------------------
// * mysql の select 用クエリー発行 (結果が1行)
//-----------------------------------------------------
function mysql_uniq() {
	$args = func_get_args();
	$fmt  = array_shift($args);
	$q    = vsprintf($fmt, $args);
	$r    = mysql_query($q);
	if (!$r) {
		return null;
//		die (mysql_error());
	}
	$d    = mysql_fetch_array($r, MYSQL_ASSOC);

	mysql_free_result($r);

	return $d;
}

//-----------------------------------------------------
// * mysql のクエリー処理 (LIKE 用)
//-----------------------------------------------------
function mysql_like($str) {
	if (!isset($str)) {
		return 'NULL';
	}
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
//	return "'". mysql_real_escape_string(mb_convert_encoding($str, 'EUC-JP', 'UTF-8')). "'";
	return "'%". mysql_real_escape_string($str). "%'";
}

//-----------------------------------------------------
// * mysql のクエリー処理 (TEXT)
//-----------------------------------------------------
function mysql_str($str) {
	if (!isset($str)) {
		return 'NULL';
	}
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
//	return "'". mysql_real_escape_string(mb_convert_encoding($str, 'EUC-JP', 'UTF-8')). "'";
	return "'". mysql_real_escape_string($str). "'";
}

//-----------------------------------------------------
// * mysql のクエリー処理 (BOOL)
//-----------------------------------------------------
function mysql_bool($bool) {
	if (!isset($bool)) {
		return 'NULL';
	}
	if ($bool) {
		return 1;
	}
	else {
		return 0;
	}
}

//-----------------------------------------------------
// * mysql のクエリー処理 (INT)
//-----------------------------------------------------
function mysql_num($num) {
	if (!isset($num)) {
		return 'NULL';
	}
	if (!is_numeric($num)) {
		return 'NULL';
	}
	return $num;
}

//-----------------------------------------------------
// * mysql のクエリー処理 (IN, INT)
//-----------------------------------------------------
function mysql_numin($ary = array()) {
	$res = array();
	foreach ($ary as $num) {
		if (!is_numeric($num)) {
			continue;
		}
		$res[] = $num;
	}
	return '('. implode(',', $res). ')';
}

//-----------------------------------------------------
// * mysql のクエリー処理 (現在の日時)
//-----------------------------------------------------
function mysql_current_timestamp() {
	return "'". date('Y-m-d H:i:s'). "'";
}

//-----------------------------------------------------
// * mysql のクエリー処理 (time()指定)
//-----------------------------------------------------
function mysql_date($tm) {
	return "'". date('Y-m-d H:i:s', $tm). "'";
}

//-----------------------------------------------------
// * mysql のクエリー処理 (パーツのテーブル指定で閲覧権限ごと引っ張る)
//-----------------------------------------------------
function mysql_fullpmt($id = 0, $table = '', $offset = 0, $limit = 0) {
	$limit_query = '';
	if ($limit > 0) {
		$limit_query = sprintf(' limit %s, %s', mysql_num($offset), mysql_num($limit));
	}
	$f = mysql_full('select d.* from '. $table. ' as d'.
					' inner join owner as o on d.id = o.id'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' where d.pid = %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' group by d.id'.
					' order by d.initymd DESC'.
					$limit_query,
					mysql_num($id), mysql_num(public_status($id)), mysql_num(myuid()));

	return $f;
}

?>
