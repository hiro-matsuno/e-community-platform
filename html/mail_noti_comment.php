<?php
/*
 *  地域防災キット基本モジュール
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

	if (!is_owner($site_id)) {
		show_error('この機能はサイト管理者のみ利用できます。');
	}

	$type = intval($_REQUEST['type']);

	$d = mysql_exec('delete from mail_noti_ct'.
					' where eid = %s and uid = %s',
					mysql_num($site_id), mysql_num($myuid));

	$i = mysql_exec('insert into mail_noti_ct (eid, uid, type) values (%s, %s, %s)',
					mysql_num($site_id), mysql_num($myuid), mysql_num($type));

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

	$q = mysql_uniq('select * from mail_noti_ct'.
					' where eid = %s and uid = %s',
					mysql_num($site_id), mysql_num($myuid));

	$type = $q['type'];

	$SYS_FORM['head'][] = 'このサイトに関するコメント投稿・トラックバックの受信があった時に通知を受け取りますか？<br>※メッセージボックスへ通知が行われます。必要に応じてメール転送設定を行って下さい。';

	$attr = array('name' => 'action', 'value' => 'set');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'site_id', 'value' => $site_id);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$option = array('1' => 'コメント・トラックバック両方', '2' => 'コメントのみ', '3' => 'トラックバックのみ', '4' => 'どちらも受け取らない');
	$attr = array('name' => 'type', 'value' => $type, 'option' => $option, 'break_num' => 1);
	$SYS_FORM['input'][] = array('title' => '通知タイプ', 'body' => get_form('radio', $attr));

	$SYS_FORM["action"] = 'mail_noti_comment.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'コメント・トラックバックの通知設定',
				  'icon'    => 'notice',
				  'content' => $html);

	show_dialog($data);
}

?>
