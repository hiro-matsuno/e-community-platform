<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/** eコミマップ連携 認証クラス.
 * EcomMapDB.php のincludeが必要
 * include_once(MOD_ECOMMAP_PATH.'/classes/EcomMapAuth.php');
 * @author haru@digitalearth.co.jp
 * */

class EcomMapAuth
{
	const AUTH_PHP = "/modules/ecommap/auth.php";
	
	/** 認証キーのチェックを行う Callback時の認証に利用 */
	static function checkAuthKey($uid, $auth_key)
	{
		//DBから取得して比較
		return EcomMapDB::checkAuthKey($uid, $auth_key);
	}
	
	/** 認証キーのチェックを行う Callback時の認証に利用 */
	static function setAuthKey($uid, $auth_key)
	{
		//DBに登録 有効期限10分
		return EcomMapDB::setAuthKey($uid, $auth_key, 600);
	}
	static function setAuthExpiry($auth_key, $expiry_sec=600)
	{
		//DBの有効期限延長
		return EcomMapDB::setAuthExpiry($auth_key, $expiry_sec);
	}
	
	/** 認証情報取得URLを生成
	 * 外部システムにリンクするときに渡すURL */
	static function getAuthUrlParam($uid, $auth_key, $blk_id)
	{
		return urlencode(self::AUTH_PHP."?blk_id=".$blk_id."&authid=".$uid."&authkey=".$auth_key);
	}
	
	/** 認証キー生成 */
	static function createAuthKey($uid)
	{
		//認証キー生成
		return strtolower(md5(time().CONF_RANDOM_SEED.$uid));
	}
	
	/** eコミマップのユーザレベルを返却
	final static public int LEVEL_ADMIN = 100;
	final static public int LEVEL_SUPERUSER = 80;
	final static public int LEVEL_MANAGER = 60;
	final static public int LEVEL_EDITOR = 30;
	final static public int LEVEL_MENBER = 20;
	final static public int LEVEL_GUEST = 10;
	final static public int LEVEL_NONE = 0;
	final static public int LEVEL_NOEXIST = -100;
	 * */
	static function getEcomMapUserLevel($level)
	{
		switch ($level) {
			case Permission::USER_LEVEL_ADMIN : return 60;
			case Permission::USER_LEVEL_POWERED : return 60;
			case Permission::USER_LEVEL_EDITOR : return 30;
			case Permission::USER_LEVEL_DELETER : return 20;
			case Permission::USER_LEVEL_AUTHORIZED : return 20;
			case Permission::USER_LEVEL_ANONYMOUS : return 0;
			default : 0;
		}
	}
}
?>