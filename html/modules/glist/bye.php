<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$id = (intval($_REQUEST["eid"]) > 0) ? $_REQUEST["eid"] : $_REQUEST["pid"];

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($id);
	default:
		input_data($id);
}

/* 登録*/
function regist_data($id = null) {
	global $COMUNI, $SYS_FORM;

	$gid = get_gid($id);
	$uid = myuid();

	if (exist_check($gid, $uid)) {
		disjoin_group(array(gid => $gid, uid => $uid));
	}
	else {
		shwo_error('参加していません。');
	}

	$data = array(title   => 'グループページから脱退',
				  icon    => 'finish',
				  message => get_gname($gid). 'から脱退しました。',
				  content => create_rform(array(eid => $id, href => home_url($id))));

	show_dialog2($data);

	exit(0);
}

/* フォーム*/
function input_data($id = null) {
	global $COMUNI, $SYS_FORM;

	$gid = get_gid($id);
	$uid = myuid();

	if (!is_login()) {
		$_SESSION["return"] = '/modules/glist/entry.php?eid='. $id;
		show_login('dialog');
	}

	$level = mod_glist_join_level($gid);

	if ( Permission::USER_LEVEL_ADMIN == $level ) {
		show_error('まず、グループ管理者から一般参加者になってください。');
	} else if ( Permission::USER_LEVEL_POWERED <= $level ) {
		show_error('まず、グループ副管理者から一般参加者になってください。');
	} else if ( Permission::USER_LEVEL_EDITOR <= $level ) {
		show_error('まず、編集者から一般参加者になってください。');
	}

	if (!exist_check($gid, $uid)) {
		show_error('参加していません。');
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(value => get_gname($gid));
	$SYS_FORM["input"][] = array(title => '脱退するグループページ',
								 name  => 'gpage_name',
								 body  => get_form("plain", $attr));

	$SYS_FORM["action"] = 'bye.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '脱退';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $id));

	$data = array(title   => 'グループ脱退確認',
				  icon    => 'write',
				  message => 'このグループから本当に脱退しますか？',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

function exist_check($gid = null, $uid = null) {
	// 参加済チェック
	$b = mysql_uniq("select * from group_member".
					" where gid = %s and uid = %s",
					mysql_num($gid), mysql_num($uid));
	if (!$b) {
		return false;
	}
	return true;
}
function mod_glist_join_level($gid) {
	$p = mysql_uniq("select * from group_member where gid = %s and uid = %s",
					  mysql_num($gid), mysql_num(myuid()));
	if($p)return$p['level'];
	else return 0;
}

?>
