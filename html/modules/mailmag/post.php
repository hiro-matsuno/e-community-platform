<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

if (!is_login()) {
	show_error('メッセージを送るためにはログインをして下さい。');
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

	$block_id = isset($_POST['block_id']) ? intval($_POST['block_id']) : 0;

	$gid = get_gid($block_id);

	if ($gid == 0) {
		show_error('グループが見つかりません。');
	}

	$f = mysql_full('select * from group_member'.
					' where gid = %s',
					mysql_num($gid));

	$to_uid_array = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$to_uid_array[] = $r['uid'];
		}
	}

	$from_uid = myuid();
	$subject  = isset($_POST['subject']) ? strip_tags($_POST['subject']) : '無題';
	$message  = isset($_POST['message']) ? strip_tags($_POST['message']) : '';

	//$message = nl2br($message);

	if ($message == '') {
		$SYS_FORM['error']['message'] = '内容がありません。';
	}

	if (isset($SYS_FORM['error'])) {
		return;
	}

	$content = null;

	if ( send_message( $from_uid, 0, $gid, $subject, $message ) ) {
		$content = "メッセージを送信しました";
		mailmag_add($block_id, $subject, $message);
	} else {
		$content = "メッセージの送信に失敗しました";
	}

	$content .= create_form_return(array(eid => $block_id, href => home_url($block_id)));

	$data = array('title'   => 'メッセージの送信',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

function print_form() {
	global $SYS_FORM;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

	if ($eid == 0) {
		show_error('送り先が特定できません。');
	}

	$g = mysql_uniq('select * from page'.
					' where gid = %s',
					mysql_num(get_gid($eid)));
	
	if ($g) {
		$gname = $g['sitename'];
	}

	$attr = array('name' => 'block_id', 'value' => $eid);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'action', 'value' => 'send_msg');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$SYS_FORM['input'][] = array('title' => 'From: ',
								 'name'  => 'handle',
								 'body'  => get_handle(myuid()));

	$attr = array('name' => 'to', 'value' => $to_uid);
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));
	$SYS_FORM['input'][] = array('title' => 'To: ',
								 'name'  => 'to_str',
								 'body'  => $gname. '参加メンバー全員');

	$attr = array('name' => 'subject', 'size' => '42');
	$SYS_FORM['input'][] = array('title' => '題名: ',
								 'name'  => 'subject',
								 'body'  => get_form('text', $attr));

	$attr = array('name' => 'message', 'height' => '200px');
	$SYS_FORM['input'][] = array('title' => 'メッセージ: ',
								 'name'  => 'message',
								 'body'  => get_form('textarea', $attr));

	$SYS_FORM["action"] = 'post.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'メッセージを送る';
	$SYS_FORM["cancel"] = '戻る';
//	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$content = create_form(array('eid' => $eid));

	$data = array('title'   => 'メッセージの送信',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

function mailmag_add($pid = 0, $subject = '無題', $body = '') {
	$new_id = get_seqid();

	$i = mysql_exec('insert into mailmag_data (id, pid, subject, body)'.
					' values (%s, %s, %s, %s)',
					mysql_num($new_id), mysql_num($pid), mysql_str($subject), mysql_str($body));

	set_pmt(array(eid => $new_id, gid =>get_gid($pid), unit => PMT_PUBLIC));
}

?>
