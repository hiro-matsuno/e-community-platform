<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
kick_guest();

switch ($_REQUEST["action"]) {
	case 'delete':
		delete_mypage();
	break;
	default:
		show_confirm();
}

/* 
 * delete
 */
function delete_mypage() {
	$target = isset($_REQUEST['target']) ? intval($_REQUEST['target']) : 0;

	if ($target == 0) {
		show_error('マイページの指定が無効です。');
	}
	if ($target != myuid()) {
		show_error('自分のマイページではありません。');
	}

	$m = mysql_uniq('select * from page where uid = %s',
					mysql_num($target));

	$mypage_id = $m['id'];

	$d = mysql_full('select * from block where pid = %s',
					mysql_num($mypage_id));

	$block_id = array();
	if ($d) {
		while ($b = mysql_fetch_array($d, MYSQL_ASSOC)) {
			$block_id[] = $b['id'];
		}
	}

	// マイページ自体の削除
	$d = mysql_exec('delete from page where id = %s',
					mysql_num($mypage_id));
	$d = mysql_exec('delete from element where id = %s',
					mysql_num($mypage_id));
	// パーツ削除
	$d = mysql_exec('delete from block where pid = %s',
					mysql_num($mypage_id));

	$html = 'マイページを削除しました。';
	$data = array(title   => 'マイページ削除完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => CONF_SITEURL)));

	show_dialog($data);

	exit(0);
}

function show_confirm() {
	global $SYS_FORM;

	$target = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;

	if ($target == 0) {
		show_error('マイページの指定が無効です。');
	}
	if ($target != myuid()) {
		show_error('自分のマイページではありません。');
	}

	$d = mysql_uniq('select * from page where uid= %s', mysql_num($target));

	if (!$d) {
		show_error('マイページがすでにありません。');
	}
	$sitename = $d['sitename'];

	// text:sitename
	$SYS_FORM["head"][] = 'マイページを削除するとプロフィール以外のコンテンツは'.
							'全て削除されてしまいます。<br>本当によろしいですか？';

	$SYS_FORM["head"][] = '削除対象: <strong>'. $sitename. '</strong>';

	// hidden:confirm
	$attr = array(name => 'action', value => 'delete');
	$SYS_FORM["input"][] = array('body' => get_form("hidden", $attr));

	$attr = array(name => 'target', value => $target);
	$SYS_FORM["input"][] = array('body' => get_form("hidden", $attr));

	$SYS_FORM["action"] = 'delete_mypage.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'マイページを削除する';
	$SYS_FORM["cancel"] = '取り止め';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'マイページの削除',
				  icon    => 'notice',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
