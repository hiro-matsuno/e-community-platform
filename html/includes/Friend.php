<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

//-----------------------------------------------------
// * グループ用の「つながり」データを作成 (現在は廃止?)
//-----------------------------------------------------
function create_friend_group($gid) {
	global $COMUNI;

	$c = mysql_uniq("select * from friend_group where owner = %s".
					" and gid = pid;", mysql_num($gid));

	if ($c) {
		return $c["gid"];
	}

	$default_name = '全てのフレンドグループ';

	$new_id = get_seqid('group');

	$q = mysql_exec("insert into friend_group (gid, owner, pid, name)".
					" values (%s, %s, %s, %s);",
					mysql_num($new_id), mysql_num($gid),
					mysql_num($new_id), mysql_str($default_name));

	if (!$q) {
		die(mysql_error());
	}
}

//-----------------------------------------------------
// * ユーザー用の「つながり」データを作成
//-----------------------------------------------------
function create_friend_user($uid) {
	global $COMUNI;

	$c = mysql_uniq("select * from friend_user where owner = %s".
					" and gid = pid;", mysql_num($uid));

	if ($c) {
		return $c["gid"];
	}

	$default_name = $COMUNI["nickname"]. 'のフレンドリスト';

	$new_id = get_seqid('group');

	$q = mysql_exec("insert into friend_user (gid, owner, pid, name)".
					" values (%s, %s, %s, %s);",
					mysql_num($new_id), mysql_num($uid),
					mysql_num($new_id), mysql_str($default_name));

	if (!$q) {
		die(mysql_error());
	}
}

//-----------------------------------------------------
// * 「つながり」の「つながり」データを作成
//-----------------------------------------------------
function create_friend_extra($uid) {
	global $COMUNI;

	$c = mysql_uniq("select * from friend_extra where uid = %s", mysql_num($uid));

	if ($c) {
		return $c["gid"];
	}

	$default_name = $COMUNI["nickname"]. 'の友達の友達';

	$new_id = get_seqid('group');

	$q = mysql_exec("insert into friend_extra (gid, uid, name)".
					" values (%s, %s, %s);",
					mysql_num($new_id), mysql_num($uid), mysql_str($default_name));

	if (!$q) {
		die(mysql_error());
	}
}

//-----------------------------------------------------
// * 「つながり」へユーザーを追加
//-----------------------------------------------------
function add_friend_user($uid, $gid) {
	$c = mysql_uniq("select * from friend_user where owner = %s".
					" and gid = %s;", mysql_num($uid), mysql_num($gid));

	if (!$c) {
		return;
	}

	$default_name = '名称未設定フレンドリスト';

	$new_id = get_seqid('group');

	$q = mysql_exec("insert into friend_user (gid, owner, pid, name)".
					" values (%s, %s, %s, %s);",
					mysql_num($new_id), mysql_num($uid),
					mysql_num($gid), mysql_str($default_name));

	if (!$q) {
		die(mysql_error());
	}
}

/**
 * Description of Friend
 *
 * @author ikeda
 */
class Friend {
    //put your code here
}
?>
