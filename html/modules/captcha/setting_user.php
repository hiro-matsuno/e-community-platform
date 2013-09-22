<?php
/*
 *  地域防災キット基本モジュール
 *  CAPTCHA設定(ユーザー)
 */

require dirname(__FILE__). '/../../lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($act) {
	case 'regist':
		entry_data();
		break;
	default:
		input_new();
}

/* entry_data */
function entry_data() {
	$type = isset($_POST['type']) ? intval($_POST['type']) : 0;

	$d = mysql_exec('delete from core_captcha_setting_user where uid = %s',
					mysql_num(myuid()));
	$i = mysql_exec('insert into core_captcha_setting_user (uid, type) values (%s, %s)',
					mysql_num(myuid()), mysql_num($type));

	$ref  = '/module/system/captcha.php';
	$html = 'コメント投稿時の画像認証を設定しました。';
	$data = array('title'   => 'コメント投稿時の画像認証',
				  'icon'    => 'finish',
				  'content' => $html. create_form_remove());

	show_dialog($data);

	exit(0);
}

/* input_new */
function input_new() {
	global $SYS_FORM;

	$q = mysql_uniq('select * from core_captcha_setting_user where uid = %s',
					mysql_num(myuid()));

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

	$attr = array('name' => 'action', 'value' => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$option = array(0 => '無効にする', 1 => '有効にする');
	$attr = array('name' => 'type', 'value' => $type, 'option' => $option, 'ahtml' => $ahtml);
	$SYS_FORM["input"][] = array('title' => 'コメント書き込み時のCAPTCHAの設定',
								 'name'  => 'use_confirm',
								 'body'  => get_form("radio", $attr));

	$SYS_FORM["action"] = 'setting_user.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'コメント投稿時の画像認証',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
