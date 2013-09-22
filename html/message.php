<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

if (!is_login()) {
	show_error('メッセージを送るためにはログインして下さい。');
}

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'send_msg':
		message_send();
	default:
		print_form();
}

exit(0);

function message_send() {
	global $SYS_FORM;

	$from_uid = myuid();
	$to_uid   =	isset($_POST['to']) ? intval($_POST['to']) : 0;
	$subject  = isset($_POST['subject']) ? strip_tags($_POST['subject']) : '無題';
	$message  = isset($_POST['message']) ? strip_tags($_POST['message']) : '';
	$gid      = isset($_POST['gid']) ? intval($_POST['gid']) : 0;

//	$message = nl2br($message);

	if ($message == '') {
		$SYS_FORM['error']['message'] = '内容がありません。';
	}

	if (isset($SYS_FORM['error'])) {
		return;
	}

	$content = null;

	if ( send_message( $from_uid, $to_uid, $gid, $subject, $message ) ) {
		$content = "メッセージを送信しました";
	} else {
		$content = "メッセージの送信に失敗しました";
	}

	$content .= create_form_remove();

	$data = array('title'   => 'メッセージの送信',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

function print_form() {
	global $SYS_FORM;

	$to_uid = isset($_REQUEST['to']) ? intval($_REQUEST['to']) : 0;
	$gid = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;

	if ($to_uid == 0) {
		show_error('送り先が特定できません。');
	}

	$attr = array('name' => 'action', 'value' => 'send_msg');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$SYS_FORM['input'][] = array('title' => 'From: ',
								 'name'  => 'handle',
								 'body'  => get_handle(myuid()));

	$attr = array('name' => 'to', 'value' => $to_uid);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));
	$attr = array('name' => 'gid', 'value' => $gid);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));
	if($gid)$to_str = get_handle($to_uid).'および「'.get_gname($gid).'」参加メンバー';
	else $to_str = get_handle($to_uid);
	$SYS_FORM['input'][] = array('title' => 'To: ',
								 'name'  => 'to_str',
								 'body'  => $to_str);

	$attr = array('name' => 'subject', 'size' => '42');
	$SYS_FORM['input'][] = array('title' => '題名: ',
								 'name'  => 'subject',
								 'body'  => get_form('text', $attr));

	$attr = array('name' => 'message', 'height' => '200px');
	$SYS_FORM['input'][] = array('title' => 'メッセージ: ',
								 'name'  => 'message',
								 'body'  => get_form('textarea', $attr));

	$SYS_FORM["action"] = 'message.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'メッセージを送る';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$content = create_form();

	$data = array('title'   => 'メッセージの送信',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

?>
