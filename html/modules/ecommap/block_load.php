<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/** eコミマップ連携モジュール
 * ブロック内表示ToDoリスト */

require dirname(__FILE__).'/../../lib.php';
include_once(dirname(__FILE__).'/config.php');
$blk_id = $_REQUEST['blk_id'];
$gid = get_gid($blk_id);
$uid = get_myuid();
//グループのユーザレベル
$level = join_level($gid);
$isEditor = $level >= 50;
$isMember = $level > 0;
$isUser = is_login();
//ブロックとグループの権限確認
if (get_pmt($blk_id)>0) {
	if (!$isMember) {
		echo "権限がありません";
		return;
	}
}
session_write_close();//ロックするのでセッションを閉じておく

//パラメータに応じたページをロード

switch ($page) {
	
	
}

?>