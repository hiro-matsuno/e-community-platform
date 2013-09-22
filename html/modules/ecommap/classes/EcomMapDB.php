<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/** eコミマップ連携DBクラス */

class EcomMapDB
{
	/** 基本設定テーブル */
	const TABLE_SETTING = "mod_ecommap_setting";
	/** ブロックの情報格納KVテーブル */
	const TABLE_BLOCK = "mod_ecommap_block";
	
	/** ユーザ毎の情報格納KVテーブル */
	const TABLE_USER = "mod_ecommap_user";
	
	/** ブロックの情報認証キー情報格納テーブル */
	const TABLE_AUTH = "mod_ecommap_auth";
	
	/** テーブルが作成済みかチェック */
	static function hasTable()
	{
		$hasTable = false;
		if ($result = mysql_query("SHOW COLUMNS FROM ".self::TABLE_SETTING)) $hasTable = true; 
		if ($result) mysql_free_result($result);
		return $hasTable;
	}
	
	/** 防災力評価用のテーブル群を作成する */
	static function createTables()
	{
		//基本情報テーブル
		self::createTable(self::TABLE_SETTING, 
			"gid INT, INDEX(gid), uid INT, INDEX(uid), option_key VARCHAR(256) NOT NULL, INDEX(option_key), option_value VARCHAR(1024)" );
		//バージョンを設定
		$result = self::setOption("VERSION", MOD_ECOMMAP_VERSION);
		
		//ブロック設定テーブル
		self::createTable(self::TABLE_BLOCK,
			"blk_id INT, INDEX(blk_id), option_key VARCHAR(256) NOT NULL, INDEX(option_key), option_value VARCHAR(1024)" );
		
		//ユーザ設定テーブル
		self::createTable(self::TABLE_USER,
			"uid INT, INDEX(uid), blk_id INT, INDEX(blk_id), option_key VARCHAR(256) NOT NULL, INDEX(option_key), option_value TEXT" );
		
		//認証設定テーブル
		self::createTable(self::TABLE_AUTH,
			"auth_id VARCHAR(256) NOT NULL, INDEX(auth_id), auth_key VARCHAR(1024) NOT NULL, INDEX(auth_key), expiry DATETIME, INDEX(expiry)", "InnoDB");
	}
	/** テーブル作成関数 結果はechoするので、ob_start()でバッファしておく */
	private function createTable($tableName, $columns, $engine=null, $charset="utf8")
	{
		$result = mysql_exec("CREATE TABLE ".$tableName." (".$columns.") DEFAULT CHARSET=".$charset.($engine ? " ENGINE=".$engine : "") );
		if (!$result) { echo ("<li class=\"error\">テーブル $tableName の作成に失敗しました</li>"); return false;}
		else { echo "<li>テーブル $tableName を作成しました</li>"; return true; }
	}
	
	/** lib.phpのmysql_uniq がエラーが出るとdieするのでこれを利用 */
	private function mysql_uniq()
	{
		$args = func_get_args();
		$r = mysql_query(vsprintf(array_shift($args), $args));
		if (!$r) return null;
		$d = mysql_fetch_array($r, MYSQL_ASSOC);
		mysql_free_result($r);
		return $d;
	}
	
	/*---------------- 認証テーブル ----------------*/
	/** 認証情報をDBに登録
	 * @param int  $uid ユーザID
	 * @param string $authkey
	 * @param int $expiry_sec 現在時刻からの有効期限 秒 */
	static function setAuthKey($uid, $auth_key, $expiry_sec)
	{
		//追加（同時利用もあるので複数可）
		$result = mysql_exec("INSERT INTO ".self::TABLE_AUTH." (auth_id, auth_key, expiry) VALUES (%s, %s, %s)", mysql_str($uid), mysql_str($auth_key), mysql_date(time()+$expiry_sec));
		//期限切れを削除
		mysql_exec("DELETE FROM ".self::TABLE_AUTH." WHERE expiry<%s", mysql_date(time()));
		return $result;
	}
	/** 認証情報の期限を延長
	 * @param string $authkey
	 * @param int $expiry_sec 現在時刻からの有効期限 秒 */
	static function setAuthExpiry($auth_key, $expiry_sec)
	{
		//追加（同時利用もあるので複数可）
		$result = mysql_exec("UPDATE ".self::TABLE_AUTH." SET expiry=%s WHERE auth_key=%s", mysql_date(time()+$expiry_sec), mysql_str($auth_key));
		return $result;
	}
	/** 認証情報をDBから取得
	 * @param int  $uid ユーザID
	 * 有効期限内の物のみ */
	static function checkAuthKey($uid, $auth_key)
	{
		$result = mysql_query("SELECT * FROM ".self::TABLE_AUTH." WHERE auth_id=".mysql_str($uid)." AND auth_key=".mysql_str($auth_key)." AND expiry>=".mysql_date(time()));
		if (!$result) return null;
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		return $row['auth_key'];
	}
	
	/*---------------- 全体設定テーブル ----------------*/
	/** 基本設定をDBに登録 */
	static function setOption($key, $value, $gid=0, $uid=0)
	{
		mysql_exec("DELETE FROM ".self::TABLE_SETTING." WHERE gid=%s AND uid=%s AND option_key=%s", mysql_num($gid), mysql_num($uid), mysql_str($key));
		return mysql_exec("INSERT INTO ".self::TABLE_SETTING." (gid, uid, option_key, option_value) VALUES (%s, %s, %s, %s)", mysql_num($gid), mysql_num($uid), mysql_str($key), mysql_str($value));
	}
	/** 基本設定の値を取得 */
	static function getOption($key, $gid=0, $uid=0)
	{
		$row = self::mysql_uniq("SELECT * FROM ".self::TABLE_SETTING." WHERE gid=%s AND uid=%s AND option_key=%s", mysql_num($gid), mysql_num($uid), mysql_str($key));
		if ($row) return $row['option_value'];
		return null;
	}
	/** 基本設定を連想配列で返却 */
	static function getOptions($gid=0, $uid=0)
	{
		$result = mysql_query("SELECT * FROM ".self::TABLE_SETTING." WHERE gid=". mysql_num($gid)." AND uid=". mysql_num($uid));
		if (!$result) return null;
		$options = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$options[$row['option_key']] = $row['option_value'];
		}
		mysql_free_result($result);
		return $options;
	}
	
	/*---------------- ブロックの設定テーブル ----------------*/
	/** ブロック設定をDBに登録 */
	static function setBlockOption($key, $value, $blk_id)
	{
		mysql_exec("DELETE FROM ".self::TABLE_BLOCK." WHERE blk_id=%s AND option_key=%s", mysql_num($blk_id), mysql_str($key));
		return mysql_exec("INSERT INTO ".self::TABLE_BLOCK." (blk_id, option_key, option_value) VALUES (%s, %s, %s)", mysql_num($blk_id), mysql_str($key), mysql_str($value));
	}
	/** 指定ブロックの設定を連想配列で返却 */
	static function getBlockOptions($blk_id)
	{
		$result =  mysql_query("SELECT * FROM ".self::TABLE_BLOCK." WHERE blk_id=".mysql_num($blk_id));
		if (!$result) return null;
		$options = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$options[$row['option_key']] = $row['option_value'];
		}
		mysql_free_result($result);
		return $options;
	}
	/** 指定ブロックの設定値を取得 */
	static function getBlockOption($blk_id, $key)
	{
		$row = self::mysql_uniq("SELECT * FROM ".self::TABLE_BLOCK." WHERE blk_id=%s AND option_key=%s", mysql_num($blk_id), mysql_str($key));
		if ($row) return $row['option_value'];
		return null;
	}
	
	/*---------------- ユーザ設定テーブル ----------------*/
	/** ユーザ設定をDBに登録 */
	static function setUserOption($key, $value, $uid, $blk_id=null)
	{
		mysql_exec("DELETE FROM ".self::TABLE_USER." WHERE uid=".mysql_num($uid)." AND option_key=".mysql_str($key).($blk_id?" AND blk_id=".mysql_num($blk_id):""));
		if ($blk_id) return mysql_exec("INSERT INTO ".self::TABLE_USER." (uid, blk_id, option_key, option_value) VALUES (%s, %s, %s, %s)", mysql_num($uid), mysql_num($blk_id), mysql_str($key), mysql_str($value));
		else return mysql_exec("INSERT INTO ".self::TABLE_USER." (uid, option_key, option_value) VALUES (%s, %s, %s)", mysql_num($uid), mysql_str($key), mysql_str($value));
	}
	/** ユーザ設定をDBに登録 */
	static function deleteUserOption($key, $uid, $blk_id=null)
	{
		return mysql_exec("DELETE FROM ".self::TABLE_USER." WHERE uid=".mysql_num($uid)." AND option_key=".mysql_str($key).($blk_id?" AND blk_id=".mysql_num($blk_id):""));
	}
	/** 指定ユーザの設定を連想配列で返却 */
	static function getUserOptions($uid, $blk_id=null)
	{
		$result =  mysql_query("SELECT option_key, option_value FROM ".self::TABLE_USER." WHERE uid=".mysql_num($uid).($blk_id?" AND blk_id=".mysql_num($blk_id):""));
		if (!$result) return null;
		$options = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$options[$row['option_key']] = $row['option_value'];
		}
		mysql_free_result($result);
		return $options;
	}
	/** 指定ユーザの設定値を取得 */
	static function getUserOption($key, $uid, $blk_id=null)
	{
		$row = self::mysql_uniq("SELECT option_value FROM ".self::TABLE_USER." WHERE uid=".mysql_num($uid)." AND option_key=".mysql_str($key).($blk_id?" AND blk_id=".mysql_num($blk_id):""));
		if ($row) return $row['option_value'];
		return null;
	}
	/** 指定キーと値のユーザをすべて検索 */
	static function getUserOptionUids($key, $value, $blk_id=null)
	{
		$result = mysql_query("SELECT uid FROM ".self::TABLE_USER." WHERE option_key=". mysql_str($key) ." AND option_value=". mysql_str($value).($blk_id?" AND blk_id=".mysql_num($blk_id):""));
		$uids = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$uids[] = $row['uid'];
		}
		mysql_free_result($result);
		return $uids;
	}
	
}
?>