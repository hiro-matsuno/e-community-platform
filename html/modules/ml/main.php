<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');
include_once(dirname(__FILE__). '/func.php');

define('MOD_ML_VIEWNUM', 50);

//ini_set('display_errors', 1);

function mod_ml_main($id = 0) {
	global $COMUNI_HEAD_CSSRAW;

	$COMUNI_HEAD_CSSRAW[] = mod_ml_style();

	$html = '';

	mod_ml_tmp_css();

	$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	switch ($act) {
		case 'mod_ml_backnumber':
			$html = mod_ml_backnumber($id);
		break;
		case 'regist':
			$html = mod_ml_regist($id);
		break;
		case 'quit':
			$html = mod_ml_quit($id);
		break;
		case 'post':
			$html = mod_ml_post($id);
		break;
		case 'post_confirm':
			$html = mod_ml_post_confirm($id);
		break;
		case 'post_exec':
			$html = mod_ml_post_exec($id);
		break;
		case 'detail':
			$html = mod_ml_view_mail($id);
		break;
		case 'finish':
			$html  = '投稿が完了しました。';
			$html .= create_form_return(array('eid' => $id, href => home_url($id)));
		break;
		default:
			$html = mod_ml_backnumber($id);
	}

	return $html;
}

function mod_ml_regist($id = 0) {
	if (myuid() == 0) {
		return 'ゲストは参加できません。<a href="/login.php">ログイン</a>または<a href="/regist.php">ユーザー登録</a>をして下さい。';
	}

	$c = mysql_uniq('select * from mod_ml_member where ml_id = %s and uid = %s',
					mysql_num($id), mysql_num(myuid()));

	if ($c) {
		return '既に参加中のメッセージングリストです。';
	}

	$i = mysql_exec('insert into mod_ml_member (ml_id, uid, status) values (%s, %s, %s)',
					mysql_num($id), mysql_num(myuid()), mysql_num(1));

	$html = 'メッセージングリストに参加しました。';
	$html .= create_form_return(array('eid' => $id, href => home_url($id)));

	return $html;
}

function mod_ml_post_exec($id) {
	require_once('Net/UserAgent/Mobile.php');

	global $SYS_FORM;

	$c = mysql_uniq('select * from mod_ml_setting where id = %s',
					mysql_num($id));

	if ($c) {
		$title      = $c['title'];
		$desc       = $c['desc'];
		$ml_prefix  = $c['ml_prefix'];
	}

	$key = isset($_REQUEST['key']) ? htmlesc($_REQUEST['key']) : '';

	if ($key) {
		$k = mysql_uniq('select * from mod_ml_post_key where id = %s', mysql_str($key));
		if ($k) {
			$myuid = $k['uid'];
		}
		else {
			error_window('投稿キーが取得できません。');
		}
	}
	else {
		$myuid = myuid();
	}

	$subject = isset($_REQUEST['subject']) ? $_REQUEST['subject'] : '無題';
	$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : '';

	$convert = isset($_REQUEST['convert']) ? htmlesc($_REQUEST['convert']) : 'UTF-8';

	if ($convert == 'SJIS') {
		$subject = mb_convert_encoding($subject, "UTF-8", "SJIS");
		$message = mb_convert_encoding($message, "UTF-8", "SJIS");
	}

	$subject = htmlesc($subject);
	$message = htmlesc($message);

	if ($message == '') {
		$SYS_FORM['error']['message'] = '本文は何か書いて下さい。';
		return mod_ml_post($id);
		exit(0);
	}

	$lockfile = dirname(__FILE__). '/lock/lockfile';
	$fp = fopen($lockfile, "w");
	flock($fp,LOCK_EX);

	$d = mysql_uniq('select count(*) as count from mod_ml_data where ml_id = %s',
					mysql_num($id));

	if ($d && $d['count'] > 0) {
		$count = $d['count'] + 1;
	}
	else {
		$count = 1;
	}

	if (isset($ml_prefix) && $ml_prefix != '') {
		$subject = '['. $ml_prefix. ':'. sprintf("%05d", $count). '] '. $subject;
	}

	$new_id = get_seqid();
	$i = mysql_exec('insert into mod_ml_data'.
					' (id, ml_id, uid, subject, message)'.
					' values (%s, %s, %s, %s, %s)',
					mysql_num($new_id), mysql_num($id), mysql_num($myuid),
					mysql_str($subject), mysql_str($message));

	set_pmt(array('eid' => $new_id, 'gid' => get_gid($id), 'unit' => PMT_PUBLIC));

	flock($fp,LOCK_UN);
	fclose($fp);

	$m = mysql_full('select * from mod_ml_member where ml_id = %s',
					mysql_num($id));

	if ($m) {
		$ref = '/index.php?module=ml&pid='. $id. '&action=post&eid='. $id;

		while ($res = mysql_fetch_assoc($m)) {
			$to_uid = $res['uid'];

			$key_href = rand_str(24);
			set_post_key($id, $to_uid, $key_href);

			$uniq_msg  = "[". get_handle($myuid). "さんから". $title. "への投稿です]\n\n";
			$uniq_msg .= $message;

			$uniq_msg .= "\n\nこのメッセージングリストへ投稿するためには下記のURLから投稿して下さい。\n";
			$uniq_msg .= CONF_URLBASE. '/modules/ml/post.php?key='. $key_href;
/*
			$fwd_mail = array();
			$fwd_mail = get_fwd_mail($to_uid);
			if (count($fwd_mail) > 0) {
				sys_fwdmail(array('to' => $fwd_mail, 'subject' => $subject, 'body' => $uniq_msg));
			}
*/
			send_message( $myuid, $to_uid, 0, $subject, $uniq_msg );
		}
	}

	if (Net_UserAgent_Mobile::isMobile()) {
		header('Content-Type: text/html; charset=Shift_JIS');
		$sitename = CONF_SITENAME;

		ob_start();
		echo <<<__HTML__
<html>
<head>
<title>携帯投稿[ML]</title>
</head>
<body>
<center>メッセージ投稿</center>
<hr>
投稿が完了しました。
<hr>
<p align="center">${sitename}</p>
</body>
</html>
__HTML__;
		;
		$output = ob_get_contents();
		ob_end_clean();
		echo mb_convert_encoding($output, "SJIS", "UTF-8");

		exit(0);
	}

	$html  = '投稿が完了しました。';
	$html .= create_form_return(array('eid' => $id, href => home_url($id)));

	return $html;
}

function set_post_key($eid, $uid, $key) {
	$q = mysql_exec('insert into mod_ml_post_key (id, eid, uid, updymd)'.
					' values (%s, %s, %s, %s)',
					mysql_str($key), mysql_num($eid), mysql_num($uid),
					mysql_current_timestamp());
}

function mod_ml_post($id, $conv = 'UTF-8') {
	global $SYS_FORM;

	$c = mysql_uniq('select * from mod_ml_setting where id = %s',
					mysql_num($id));

	if ($c) {
		$title = $c['title'];
		$desc  = $c['desc'];
	}

	$key = isset($_REQUEST['key']) ? $_REQUEST['key'] : '';
	if ($key) {
		$k = mysql_uniq('select * from mod_ml_post_key where id = %s', mysql_str($key));
		if ($k) {
			$myuid = $k['uid'];
		}
		else {
			error_window('投稿キーが取得できません。');
		}
	}
	else {
		$myuid = myuid();
	}

	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$to        = $title;
		$from      = get_handle($myuid);
		$subject   = $SYS_FORM["cache"]["subject"];
		$message   = $SYS_FORM["cache"]["message"];
	}
	else {
		$to        = $title;
		$from      = get_handle($myuid);
		$subject   = '';
		$message   = '';
	}

	$attr = array('name' => 'uid', value => $myuid);
	$SYS_FORM["input"][] = array('name'  => 'uid',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'module', value => 'ml');
	$SYS_FORM["input"][] = array('name'  => 'module',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'action', value => 'post_exec');
	$SYS_FORM["input"][] = array('name'  => 'action',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'key', value => $key);
	$SYS_FORM["input"][] = array('name'  => 'key',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => session_name(), value => session_id());
	$SYS_FORM["input"][] = array('name'  => session_name(),
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'convert', value => $conv);
	$SYS_FORM["input"][] = array('name'  => 'convert',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'to', 'value' => $to);
	$SYS_FORM["input"][] = array('title' => '投稿先',
								 'name'  => 'from',
								 'body'  => get_form("plain", $attr));

	$attr = array('name' => 'from', 'value' => $from);
	$SYS_FORM["input"][] = array('title' => '差出人',
								 'name'  => 'from',
								 'body'  => get_form("plain", $attr));

	$attr = array('name' => 'subject', value => $subject, size => 40, style => 'width: 80%;');
	$SYS_FORM["input"][] = array('title' => '題名',
								 'name'  => 'subject',
								 'body'  => get_form("text", $attr));

	$attr = array('name' => 'message', 'value' => $message, 'height' => '200px');
	$SYS_FORM["input"][] = array('title' => '本文',
								 'name'  => 'message',
								 'body'  => get_form("textarea", $attr));

	$SYS_FORM["action"] = CONF_URLBASE. '/modules/ml/message.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '投稿';
//	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array('eid' => $id, 'gid' => get_gid($id), 'pid' => $id));

	return $html;
}

function mod_ml_quit($id) {
	$i = mysql_exec('delete from mod_ml_member where ml_id = %s and uid = %s',
					mysql_num($id), mysql_num(myuid()));

	$html = 'メッセージングリストから退会しました。';
	$html .= create_form_return(array('eid' => $id, href => home_url($id)));

	return $html;
}

function mod_ml_view_mail($id) {
	global $SYS_FORM;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

	$q = mysql_uniq("select * from mod_ml_setting".
					" where id = %s",
					mysql_num($pid));

//	$html .= '<div class="mod_ml_header">'. $q["header"]. '</div>';
//	$html .= '<h3 class="mod_ml_title">'. $q['title']. '</h3>';
//	$html .= '<div class="mod_ml_desc">'. $q["desc"]. '</div>';

	$f = mysql_uniq('select * from mod_ml_data where id = %s',
					mysql_num($eid));

	if ($q) {
		$attr = array('name' => 'to', 'value' => $q['title']);
		$SYS_FORM["input"][] = array('title' => '投稿先',
									 'name'  => 'to',
									 'body'  => get_form("plain", $attr));

		$attr = array('name' => 'subject', 'value' => $f['subject']);
		$SYS_FORM["input"][] = array('title' => '題名',
									 'name'  => 'from',
									 'body'  => get_form("plain", $attr));

		$attr = array('name' => 'from', 'value' => get_handle($f['uid']));
		$SYS_FORM["input"][] = array('title' => '差出人',
									 'name'  => 'from',
									 'body'  => get_form("plain", $attr));

		$attr = array('name' => 'subject', 'value' => preg_replace("/\n/", '<br />', $f['message']));
		$SYS_FORM["input"][] = array('title' => '本文',
									 'name'  => 'from',
									 'body'  => get_form("plain", $attr));

		$SYS_FORM["action"] = 'index.php';
		$SYS_FORM["method"] = 'POST';
	}

	$html .= create_form_return(array('string' => 'ページに戻る', 'eid' => $id, href => home_url($id)));
	return $html;

}

function mod_ml_backnumber($id = 0) {
	$page = isset($_REQUEST['p']) ? intval($_REQUEST['p']) : 0;
	$offset = MOD_ML_VIEWNUM + 1;

	$c = mysql_uniq('select * from mod_ml_setting where id = %s',
					mysql_num($id));

	if ($c) {
		$title = $c['title'];
		$desc  = $c['desc'];
		$pmt   = $c['archive_pmt'];
	}

	if (!is_owner($id)) {
		$deny_str = 'バックナンバーは閲覧できません。';
		switch ($pmt) {
			case 0:
				return $deny_str;
			break;
			case 1:
				if (!mod_ml_is_join($id)) {
					return $deny_str;
				}
			break;
			default:
				;
		}
	}

	$html = '<h3 class="mod_ml_title">'. $title. 'バックナンバー</h3>';
	$html .= '<div class="mod_ml_desc">'. $desc. '</div>';

	$q = mysql_full('select * from mod_ml_data where ml_id = %s'.
					' order by id desc limit %s, %s',
					mysql_num($id), mysql_num($page), mysql_num($offset));

	if (mysql_num_rows($q) > MOD_ML_VIEWNUM) {
		$next = null;
		$prev = ($page > 0) ? $page - 1 : null;
	}
	else {
		$next = $page + 1;
		$prev = ($page > 0) ? $page - 1 : null;
	}

	if ($q) {
		$base_href = CONF_URLBASE. '/index.php?module=ml&pid='. $id. '&action=detail&eid=';

		$html .= '<ul class="mod_ml_backnumber_list">';
		while ($res = mysql_fetch_assoc($q)) {
			$html .= '<li class="mod_ml_list">'. make_href($res['subject'], $base_href. $res['id']).
					 '<div class="mod_ml_listdate">from '. get_handle($res['uid']).
					 ' at '. date('Y年m月d日 H:i', strtotime($res['initymd'])). '</div>'.
					 '</li>';
		}
		$html .= '</ul>';
	}

	$html .= create_form_return(array('string' => 'ページに戻る', 'eid' => $id, href => home_url($id)));
	return $html;
}

function mod_ml_style() {
	return <<<__CSS__
input#subject, textarea#message {
	border: solid 1px #777;
}
.mod_ml_list {
	margin-bottom: 5px;
	border-bottom: dashed 1px #ccc;
}
.mod_ml_list a {
	font-size: 14px;
}
.mod_ml_listdate {
	margin-top: 3px; text-align: right; font-size: 12px; color: #666;
}

__CSS__;
	;
}

?>
