<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/** 認証チェック
 * セッションはロックするのでDBを利用する
 * */
mb_http_output("UTF-8");


//DBへ接続 (lib.phpを使わない セッションも使わない)
$NO_SESSION = true;
include_once(dirname(__FILE__).'/../../config.php');
include_once(dirname(__FILE__).'/../../includes/MySql.php');
mysql_connect_ecom();

include_once(dirname(__FILE__).'/classes/EcomMapDB.php');
include_once(dirname(__FILE__).'/classes/EcomMapAuth.php');

//認証チェック
$uid = $_GET['authid']; //eコミの$uid
if (!EcomMapAuth::checkAuthKey($uid, $_GET['authkey'])) {
	echo "{error:'認証キーが不正です'}";
	return;
}

include_once(dirname(__FILE__).'/../../includes/Element.php');

//ユーザID情報取得
$user = new User($uid);
if (!$user->getEnable()) {
	echo "{error:'ユーザが無効です'}";
	return;
}
//ブロック情報
$blk_id = $_GET['blk_id'];
$element = new Element($blk_id);

//グループと権限
$gid = $element->getGid();
$level = $user->getLevel();
if ($level == Permission::USER_LEVEL_ADMIN || $level == Permission::USER_LEVEL_ANONYMOUS) {
} else {
	$group = new Group( $gid );
	$level = $group->getUserLevel( $user );
}
//ユーザ情報出力JSON
$userInfo = array(
	'uid'=>$user->getUid(),		//eコミ内ユーザID
	//'authid'=>$user->getEmail(),	//ログインID = e-mail
	'authid'=>$_SERVER['SERVER_NAME'].":".$user->getUid(),
	'email'=>$user->getEmail(),	//e-mail
	'handle'=>$user->getHandle(),	//名前
	'level'=>EcomMapAuth::getEcomMapUserLevel($level) //eコミマップ権限レベル
);
if ($gid) {
	$ecommap_gid = EcomMapDB::getBlockOption($blk_id, "ecommap_gid");
	if ($ecommap_gid) $userInfo['gid'] = $ecommap_gid; //eコミマップグループID
}

//JSONラッパーinclude php5.2より前に対応
include_once(dirname(__FILE__).'/../../includes/json_wrapper.php');

echo json_encode($userInfo);

?>