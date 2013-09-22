<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require dirname(__FILE__). '/lib.php';

if (!is_login()) {
	show_error('フレンドリストを使用するためにはログインして下さい。');
}

$from_uid = isset($_REQUEST['from']) ? $_REQUEST['from'] : null;
$to_uid   = isset($_REQUEST['to']) ? $_REQUEST['to'] : null;

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : null;

switch ($mode) {
	case 'allow':
		allow_friend($from_uid, $to_uid);
	break;
	default:
		deny_friend($from_uid, $to_uid);
}

exit(0);

function deny_friend($from_uid = 0, $to_uid = 0) {
	$d = mysql_exec('delete from friend_tmp'.
					' where to_uid = %s and from_uid = %s',
					mysql_num($to_uid), mysql_num($from_uid));

	$content = get_handle($from_uid). 'さんのフレンドリスト申請を拒否しました。';
	$data = array('title'   => 'フレンドリストの追加',
				  'icon'    => 'finish',
				  'content' => $content);

	show_dialog($data);

	exit(0);

}

function allow_friend($from_uid = 0, $to_uid = 0) {
	$q = mysql_uniq('select * from friend_tmp'.
					' where to_uid = %s and from_uid = %s',
					mysql_num($to_uid), mysql_num($from_uid));
	if (!$q) {
		show_error('見つかりません。');
	}

	// to_uid のフレンドリスト検索
	$c = mysql_uniq("select * from friend_user where owner = %s".
					" and gid = pid;", mysql_num($to_uid));
	if ($c) {
		$to_gid = $c['gid'];
	}
	unset($c);
	$c = mysql_uniq('select * from unit'.
					' where id = %s and uid = %s',
					mysql_num($to_gid), mysql_num($from_uid));
	if (!$c) {
		$i = mysql_exec("insert into unit (id, uid) values (%s, %s)",
						mysql_num($to_gid), mysql_num($from_uid));
	}
	unset($c);
	$c = mysql_uniq("select * from friend_extra where uid = %s",
					mysql_num($to_uid));

	$f = mysql_full('select uid from unit as u'.
					' inner join friend_user as fu on u.id = fu.gid'.
					' where fu.owner = %s',
					mysql_num($to_uid));
	if ($f) {
		while ($res = mysql_fetch_assoc($f)) {
			$ins[] = sprintf('(%s, %s)', $c['gid'], $res['uid']);
		}
		$value = implode(',', $ins);
		$d = mysql_exec('delete from unit where id = %s', $c['gid']);
		$i = mysql_exec('insert into unit (id, uid) values %s', $value);
	}
	unset($c); unset($f);

	// from_uid のフレンドリスト検索
	$c = mysql_uniq("select * from friend_user where owner = %s".
					" and gid = pid;", mysql_num($from_uid));
	if ($c) {
		$from_gid = $c['gid'];
	}
	unset($c);
	$c = mysql_uniq('select * from unit'.
					' where id = %s and uid = %s',
					mysql_num($from_gid), mysql_num($to_uid));
	if (!$c) {
		$i = mysql_exec("insert into unit (id, uid) values (%s, %s)",
						mysql_num($from_gid), mysql_num($to_uid));
	}
	unset($c);
	$c = mysql_uniq("select * from friend_extra where uid = %s",
					mysql_num($from_uid));

	$f = mysql_full('select uid from unit as u'.
					' inner join friend_user as fu on u.id = fu.gid'.
					' where fu.owner = %s',
					mysql_num($from_uid));
	if ($f) {
		while ($res = mysql_fetch_assoc($f)) {
			$ins[] = sprintf('(%s, %s)', $c['gid'], $res['uid']);
		}
		$value = implode(',', $ins);
		$d = mysql_exec('delete from unit where id = %s', $c['gid']);
		$i = mysql_exec('insert into unit (id, uid) values %s', $value);
	}
	unset($c); unset($f);

	$d = mysql_exec('delete from friend_tmp'.
					' where to_uid = %s and from_uid = %s',
					mysql_num($to_uid), mysql_num($from_uid));

	$messg = sprintf("%sさんへのフレンド申請が許可されました。\n%sさんがフレンドリストに追加されました。",get_handle($to_uid),get_handle($to_uid));
	send_message2($from_uid,'フレンド申請許可',$messg);

	$content = get_handle($from_uid). 'さんをフレンドリストに追加しました。';
	$data = array('title'   => 'フレンドリストの追加',
				  'icon'    => 'finish',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

?>
