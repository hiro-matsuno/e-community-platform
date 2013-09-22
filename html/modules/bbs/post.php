<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"] = isset($_POST["title"]) ? $_POST["title"] : '無題';
	$SYS_FORM["cache"]["body"]  = $_POST["body"];
	$SYS_FORM["cache"]["name"]  = isset($_POST["name"]) ? $_POST["name"] : '名無し';
	$SYS_FORM["cache"]["mail"]  = isset($_POST["mail"]) ? $_POST["mail"] : '';
	$SYS_FORM["cache"]["url"]   = isset($_POST["url"]) ? $_POST["url"] : '';
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["title"] || $SYS_FORM["cache"]["title"] == '') {
		$SYS_FORM["error"]["title"] = 'タイトルが未入力です。';
	}
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />') {
		$SYS_FORM["error"]["body"] = '本文が未入力です。';
	}
	if ($SYS_FORM["cache"]["url"] == 'http://') {
		$SYS_FORM["cache"]["url"] = '';
	}
	if ($SYS_FORM["error"]) {
		return;
	}
	// 登録
	$title   = htmlspecialchars($SYS_FORM["cache"]["title"], ENT_QUOTES);
	$body    = $SYS_FORM["cache"]["body"];
	$uid     = myuid();
	$name    = htmlspecialchars($SYS_FORM["cache"]["name"], ENT_QUOTES);
	$mail    = htmlspecialchars($SYS_FORM["cache"]["mail"], ENT_QUOTES);
	$url     = htmlspecialchars($SYS_FORM["cache"]["url"], ENT_QUOTES);
	$initymd = mysql_current_timestamp();
	$updymd  = mysql_current_timestamp();

	// pidはもう使いません。コード整理時に消します。
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec('insert into mod_bbs_thread'.
						' (id, pid, title, body, uid, name, mail, url, initymd, updymd)'.
						' values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
						mysql_num($eid), mysql_num($pid), mysql_str($title), mysql_str($body),
						mysql_num($uid), mysql_str($name), mysql_str($mail), mysql_str($url),
						$initymd, $updymd);

		$res_msg = 'スレッドを作成しました。';
	}
	else {
		$p = mysql_uniq("select * from mod_bbs_thread where id = %s",
					mysql_num($eid));
		$pid = $p['pid'];
		
		$q = mysql_exec('update mod_bbs_thread set'.
						' title = %s, body = %s, name = %s, mail = %s, url = %s, updymd = %s, pid = %s'.
						' where id = %s',
						mysql_str($title), mysql_str($body), mysql_str($name), mysql_str($mail),
						mysql_str($url), $updymd, mysql_num($pid),
						mysql_num($eid));

		$res_msg = 'スレッドを編集しました。';
	}

	set_pmt(array('eid' => $eid, 'gid' =>get_gid($pid)));
	set_comment($eid);

	$html = $res_msg;
	$data = array(title   => '編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array('eid' => $eid, 'href' => home_url($eid))));

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;
	global $SYS_BOX_TITLE;

	$d = mysql_uniq('select * from mod_bbs_thread'.
					' where id = %s',
					mysql_num($eid));

	// Y-m-d H:i:s
	if ($d) {
		$SYS_BOX_TITLE = 'スレッドの編集';
		$title       = $d["title"];
		$body        = $d["body"];
//		$initymd     = tm2time($d["initymd"]);
		$uid         = $d["uid"];
		$name        = $d["name"];
		$mail        = $d["mail"];
		$url         = $d["url"];
	}
	else {
		$SYS_BOX_TITLE = '新規スレッドの作成';
		$subject     = '';
		$body        = '';
		$uid         = myuid();
		$name        = '';
		$mail        = '';
		$url         = 'http://';
//		$initymd     = time();
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title     = $SYS_FORM["cache"]["title"];
		$body      = $SYS_FORM["cache"]["body"];
		$name      = $SYS_FORM["cache"]["name"];
		$mail      = $SYS_FORM["cache"]["mail"];
		$url       = $SYS_FORM["cache"]["url"];
	}

	$attr = array('name' => 'action', value => 'regist');
	$SYS_FORM["input"][] = array('name'  => 'action',
								 'body'  => get_form('hidden', $attr));

	// text:subject
	$attr = array(name => 'title', value => $title, size => 64);
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	if ($uid == 0) {
		$attr = array(name => 'name', value => $name, size => 32);
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("text", $attr));

		$attr = array(name => 'mail', value => $mail, size => 48);
		$SYS_FORM["input"][] = array(title => 'メールアドレス',
									 name  => 'mail',
									 body  => get_form("text", $attr));
	}
	else {
		$attr = array('name' => 'name', 'value' => get_handle($uid));
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("plain", $attr));
	}

	$attr = array(name => 'url', value => $url, size => 64);
	$SYS_FORM["input"][] = array(title => 'URL',
								 name  => 'url',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array('name' => 'body', 'value' => $body, 'cols' => 64, 'rows' => 10, 'toolbar' => 'Basic');
	$SYS_FORM["input"][] = array(title => '本文',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'post.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["comment"] = true;
	$SYS_FORM["pmt"]     = true;

	$SYS_FORM["submit"] = 'スレッド作成';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => $SYS_BOX_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
