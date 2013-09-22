<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require_once dirname(__FILE__). '/lib.php';

if (!is_login()) {
	show_error('ログインして下さい。');
}

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'set':
		set_mail();
	default:
		print_form();
}

exit(0);

function set_mail() {
	global $SYS_FORM;

	$myuid = myuid();

	$site_id = isset($_POST['site_id']) ? intval($_POST['site_id'])  : 0;

	if ($site_id == 0) {
		show_error('サイトIDが不明です。');
	}

	$d = mysql_exec('delete from mail_noti'.
					' where eid = %s and uid = %s',
					mysql_num($site_id), mysql_num($myuid));

	$i = mysql_exec('insert into mail_noti (eid, uid) values (%s, %s)',
					mysql_num($site_id), mysql_num($myuid));

	$html  = '設定を変更しました。';
	$html .= create_form_remove();

	$data = array('title'   => 'メール通知設定',
				  'icon'    => 'finish',
				  'content' => $html);

	show_dialog($data);
}

function print_form() {
	global $SYS_FORM;

	$myuid   = myuid();
	$site_id = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

	$SYS_FORM['head'][] = 'このサイトが更新された時にメールで通知しますか？';
	$SYS_FORM['head'][] = '(RSSの表示等、一部のコンテンツは除きます)';

	$attr = array('name' => 'action', 'value' => 'set');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'site_id', 'value' => $site_id);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$SYS_FORM["action"] = 'mail_noti.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'メール通知設定',
				  'icon'    => 'notice',
				  'content' => $html);

	show_dialog($data);
}

?>
