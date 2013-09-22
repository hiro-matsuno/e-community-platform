<?php
/*
 *  地域防災キット基本モジュール
 *  CAPTCHA設定(マスター)
 */

require dirname(__FILE__). '/../../lib.php';

define('SES_NAME', 'REGIST_SETTING');

su_check();

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

	$d = mysql_exec('delete from core_captcha_setting_master');
	$i = mysql_exec('insert into core_captcha_setting_master (type) values (%s)',
					mysql_num($type));

	$ref  = '/manager/system/captcha.php';
	$html = 'コメント投稿時の画像認証を設定しました。';
	$data = array(title   => 'コメント投稿時の画像認証',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => '設定画面に戻る')));

	show_input($data);

	exit(0);
}

/* input_new */
function input_new() {
	global $SYS_FORM;

	$q = mysql_uniq('select * from core_captcha_setting_master limit 1');

	if ($q) {
		$type = $q['type'];
	}
	else {
		$type = 0;
	}

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$ahtml  = '<div style="padding: 3px; line-height: 1.2em;">';
	$ahtml .= '無効にした場合、各マイページ・グループページのデフォルトの設定が無効になります。<br />';
	$ahtml .= '<span style="color: #f00;">有効</span>にした場合、各マイページ・グループのデフォルトの設定が有効になります。';
	$ahtml .= '</div>';

	$option = array(0 => '無効にする', 1 => '有効にする');
	$attr = array('name' => 'type', 'value' => $type, 'option' => $option, 'ahtml' => $ahtml);
	$SYS_FORM["input"][] = array('title' => 'コメント書き込み時のCAPTCHAの設定',
								 'name'  => 'use_confirm',
								 'body'  => get_form("radio", $attr));

	$SYS_FORM["action"] = 'captcha.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'コメント投稿時の画像認証',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
