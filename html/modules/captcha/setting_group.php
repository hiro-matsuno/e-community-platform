<?php
/*
 *  地域防災キット基本モジュール
 *  CAPTCHA設定(グループ)
 */

require dirname(__FILE__). '/../../lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

$gid  = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;

if (!is_master(array('gid' => $gid))) {
	show_error('グループ管理者のみ変更可能な機能です。');
}

switch ($act) {
	case 'regist':
		entry_data($gid);
		break;
	default:
		input_new($gid);
}

/* entry_data */
function entry_data($gid = 0) {
	$type = isset($_POST['type']) ? intval($_POST['type']) : 0;

	$d = mysql_exec('delete from core_captcha_setting_group where gid = %s',
					mysql_num($gid));
	$i = mysql_exec('insert into core_captcha_setting_group (gid, type) values (%s, %s)',
					mysql_num($gid), mysql_num($type));

	$html = 'コメント投稿時の画像認証を設定しました。';
	$data = array('title'   => 'コメント投稿時の画像認証',
				  'icon'    => 'finish',
				  'content' => $html. create_form_remove());

	show_dialog($data);

	exit(0);
}

/* input_new */
function input_new($gid = 0) {
	global $SYS_FORM;

	$q = mysql_uniq('select * from core_captcha_setting_group where gid = %s',
					mysql_num($gid));

	if ($q) {
		$type = $q['type'];
	}
	else {
		$c = mysql_uniq('select * from core_captcha_setting_master limit 1');
		if ($c) {
			$type = $c['type'];
		}
		else {
			$type = 0;
		}
	}

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$option = array(0 => '無効にする', 1 => '有効にする');
	$attr = array('name' => 'type', 'value' => $type, 'option' => $option, 'ahtml' => $ahtml);
	$SYS_FORM["input"][] = array('title' => 'コメント書き込み時のCAPTCHAの設定',
								 'name'  => 'use_confirm',
								 'body'  => get_form("radio", $attr));

	$SYS_FORM["action"] = 'setting_group.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array('eid' => get_eid(array('gid' => $gid))));

	$data = array('title'   => 'コメント投稿時の画像認証',
				  'icon'    => 'write',
				  'content' => $html);

	show_dialog($data);

	exit(0);
}

?>
