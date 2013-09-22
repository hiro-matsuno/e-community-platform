<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
kick_guest();

$gid = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;

if ($gid == 0) {
	show_error('グループページの指定が無効です。');
}

$m = mysql_uniq('select * from page where gid = %s',
				mysql_num($gid));

if (!$m) {
	show_error('グループページがすでにありません。');
}
$gpage_id = $m['id'];

if (!is_owner($gpage_id,100)) {
	show_error('あなたはグループページの管理者ではありません。');
}

switch ($_REQUEST["action"]) {
	case 'delete':
		delete_gpage($gpage_id);
	break;
	default:
		show_confirm($gpage_id);
}

/* 
 * delete
 */
function delete_gpage($gpage_id) {
	$d = mysql_full('select * from block where pid = %s',
					mysql_num($gpage_id));

	$block_id = array();
	if ($d) {
		while ($b = mysql_fetch_array($d, MYSQL_ASSOC)) {
			$block_id[] = $b['id'];
		}
	}

	// マイページ自体の削除
	$d = mysql_exec('delete from page where id = %s',
					mysql_num($gpage_id));
	$d = mysql_exec('delete from element where id = %s',
					mysql_num($gpage_id));
	// パーツ削除
	$d = mysql_exec('delete from block where pid = %s',
					mysql_num($gpage_id));

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "group_delete", array( $gpage_id ) );

	$html = 'グループページを削除しました。';
	$data = array(title   => 'グループページ削除完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => CONF_SITEURL)));

	show_dialog($data);

	exit(0);
}

function show_confirm($gpage_id) {
	global $SYS_FORM;

	$d = mysql_uniq('select * from page where id = %s',
					mysql_num($gpage_id));
					
	$gid = $d['gid'];
	$sitename = $d['sitename'];

	// text:sitename
	$SYS_FORM["head"][] = 'グループページを削除すると全てのコンテンツは'.
							'削除されてしまいます。<br>本当によろしいですか？';

	$SYS_FORM["head"][] = '削除対象: <strong>'. $sitename. '</strong>';

	// hidden:confirm
	$attr = array(name => 'action', value => 'delete');
	$SYS_FORM["input"][] = array('body' => get_form("hidden", $attr));

	$attr = array(name => 'gid', value => $gid);
	$SYS_FORM["input"][] = array('body' => get_form("hidden", $attr));

	$SYS_FORM["action"] = 'delete_gpage.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'グループページを削除する';
	$SYS_FORM["cancel"] = '取り止め';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'グループページの削除',
				  icon    => 'notice',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
