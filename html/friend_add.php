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
	$category = '';

	$c = mysql_uniq('select * from friend_tmp'.
					' where to_uid = %s and from_uid = %s',
					mysql_num($to_uid), mysql_num($from_uid));
	if ($c) {
		show_error('既に申請済みです。');
	}

	$from_name = get_handle(myuid());
	$to_name   = get_handle($to_uid);

	$subject  = $from_name. 'さんからのフレンド追加申請';
	$message  = isset($_POST['message']) ? strip_tags($_POST['message']) : '特に無し';
	$message  = nl2br($message);

	$allow_url = CONF_URLBASE."/friend_exec.php?from=${from_uid}&to=${to_uid}&c=${category}&mode=allow";
	$deny_url = CONF_URLBASE."/friend_exec.php?from=${from_uid}&to=${to_uid}&c=${category}&mode=deny";

	$body1 = <<<__MESSAGE__
${to_name} さんへ、${from_name} さんからフレンドリストの追加申請がありました。<br>

<ul>
<li><a href="$allow_url">許可します。</a></li>
<li><a href="$deny_url">拒否します。</a></li>
</ul>
--- 申請時のメッセージ ---<br>
${message}
__MESSAGE__;
	$body2 = <<<__MESSAGE__
${to_name} さんへ、${from_name} さんからフレンドリストの追加申請がありました。<br>

$allow_url
許可します

$deny_url
拒否します

--- 申請時のメッセージ ---<br>
${message}
__MESSAGE__;

	$i = mysql_exec('insert into friend_tmp'.
					' (to_uid, from_uid)'.
					' values (%s, %s)',
					mysql_num($to_uid),
					mysql_num($from_uid));

	send_message2($to_uid,$subject,array($body1,$body2));

	$content  = 'フレンドリストへの追加を申請しました。';
	$content .= create_form_remove();

	$data = array('title'   => 'フレンドリストの追加',
				  'icon'    => 'finish',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

function print_form() {
	global $SYS_FORM;

	$to_uid = isset($_REQUEST['to']) ? intval($_REQUEST['to']) : 0;

	if ($to_uid == 0) {
		show_error('送り先が特定できません。');
	}

	$attr = array('name' => 'action', 'value' => 'send_msg');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'to', 'value' => $to_uid);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));
	$SYS_FORM['input'][] = array('title' => 'フレンドリストに追加したいユーザー',
								 'name'  => 'to_str',
								 'body'  => get_handle($to_uid));

	$attr = array('name' => 'message', 'height' => '200px');
	$SYS_FORM['input'][] = array('title' => 'メッセージ',
								 'name'  => 'message',
								 'body'  => get_form('textarea', $attr));

	$SYS_FORM["action"] = 'friend_add.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'フレンドリスト追加申請';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$content  = '<div style="padding: 0 5px 3px 5px;">'.
				'フレンドリストは一方的に追加できません。下記から相手に申請を行って下さい。'.
				'</div>';
	$content .= create_form();

	$data = array('title'   => 'フレンドリストの追加',
				  'icon'    => 'friend',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

?>
