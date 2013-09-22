<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$eid = intval($_REQUEST['eid']);

if (!isset($_SESSION['mod_contact_confirm'])) {
	error_window('既に送信済みかエラーが発生しました。');
}

$s = mysql_uniq('select * from mod_contact_setting where id = %s',
				mysql_num($eid));

$q = mysql_full('select d.*, p.position from mod_contact_form_data as d'.
				' inner join mod_contact_form_pos as p on d.id = p.id'.
				' where d.eid = %s order by p.position',
				mysql_num($eid));

$subject = 'お問い合わせ: '. $s['subject'];
$body[]  = $s['subject']. 'からお問い合わせがありました。';

$csv = array();
if ($q) {
	while ($res = mysql_fetch_assoc($q)) {
		$body[] = mod_contact_add_post_data($res);
		$csv[]  = mod_contact_add_log_data($res);
	}
}

$split = '<_'. $eid. '_>';
$i = mysql_exec('insert into mod_contact_send_data'.
				' (eid, uid, data)'.
				' values (%s, %s, %s)',
				mysql_num($eid), mysql_num(myuid()), mysql_str(implode($split, $csv)));

$body[] = '―――――――――――――――――――――――――――――――――――';
//$body[] = '投稿元: '. home_url($eid);
$body[] = '投稿元: '. CONF_URLBASE. '/index.php?module=contact&eid='. $eid;

$mail_body = implode("\n", $body);

if ($s['mail'] != '') {
	sys_sendmail(array('to' => $s['mail'], 'subject' => $subject, 'body' => $mail_body));
}

$o = mysql_uniq('select * from owner where id = %s', $eid);

/*
$fwd = get_fwd_mail($o['uid']);
if (isset($fwd) && count($fwd) > 0) {
	$to = $fwd;
}
else {
	$to = $email;
}
*/

send_message(0, $o['uid'], 0, $subject, $mail_body);

//$body_head = get_handle($uid). " 様\n\n";
///sys_fwdmail(array('to' => $to, 'subject' => $subject, 'body' => $body_head. $body));

unset_session('/^mod_contact/');

header('Location: '. CONF_URLBASE. '/index.php?module=contact&eid='. $eid. '&blk_id='. $eid. '&finish=1');

function mod_contact_add_post_data($res = array()) {
	$str = array();

	$str[] = '―――――――――――――――――――――――――――――――――――';
	$str[] = '■ '. $res['title'];

	switch ($res['type']) {
		case 'text':
		case 'textarea':
		case 'select':
		case 'radio':
			$value = htmlesc($_SESSION['mod_contact_data'][$res['position']]);
		break;
		case 'checkbox':
			$value = htmlesc(implode(', ', $_SESSION['mod_contact_data'][$res['position']]));
		break;
		default: 
			$value = '';
	}

	$str[] = $value;

	return implode("\n", $str);
}

function mod_contact_add_log_data($res = array()) {
	$str = array();

	switch ($res['type']) {
		case 'text':
		case 'textarea':
		case 'select':
		case 'radio':
			$value = htmlesc($_SESSION['mod_contact_data'][$res['position']]);
		break;
		case 'checkbox':
			$value = htmlesc(implode(', ', $_SESSION['mod_contact_data'][$res['position']]));
		break;
		default: 
			$value = '';
	}

	return $value;
}

?>

