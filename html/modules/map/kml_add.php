<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"]    = $_POST["title"];
	$SYS_FORM["cache"]["url"]    = $_POST["url"];

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["title"]) {
		$SYS_FORM["error"]["title"] = 'タイトルを入力して下さい。';
	}
	if (!$SYS_FORM["cache"]["url"]) {
		$SYS_FORM["error"]["url"] = 'URLを入力してください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	if ($id > 0) {
		if (!is_owner($id)) {
			show_error('あなたが投稿したKMLではありません。');
		}

		$u = mysql_exec('update kml_url set title = %s, url = %s'.
						' where id = %s',
						mysql_str($SYS_FORM["cache"]["title"]),
						mysql_str($SYS_FORM["cache"]["url"]),
						mysql_num($id));
	}
	else {
		$id = get_seqid();

		$q = mysql_exec("insert into kml_url (id, title, url) values (%s, %s, %s)",
						mysql_num($id),
						mysql_str($SYS_FORM["cache"]["title"]),
						mysql_str($SYS_FORM["cache"]["url"]));

		set_pmt(array('eid' => $id, 'uid' => myuid()));
	}

	$html = 'KMLを追加しました。';
	$html .= reload_form();

	$data = array(title   => 'KMLの追加',
				  icon    => 'finish',
				  content => $html);

	show_dialog($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_DEBUG;

	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id > 0) {
		if (!is_owner($id)) {
			show_error('あなたが投稿したKMLではありません。');
		}
		$d = mysql_uniq('select * from kml_url'.
						' where id = %s',
						mysql_num($id));
	}


	// settingからロード
	if ($d) {
		$title = $d["title"];
		$url   = $d["url"];
	}
	else {
		$title = '';
		$url   = '';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title  = $SYS_FORM["cache"]["title"];
		$url    = $SYS_FORM["cache"]["url"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:title
	$attr = array('name' => 'title', 'value' => $title, 'size' => 42);
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));
	// text:title
	$attr = array('name' => 'url', 'value' => $url, 'size' => 50);
	$SYS_FORM["input"][] = array(title => 'URL',
								 name  => 'url',
								 body  => get_form("text", $attr));


	$SYS_FORM["action"] = 'kml_add.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

	$html = create_form(array(eid => $eid, pid => $id));

	$data = array(title   => 'KMLの追加',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
