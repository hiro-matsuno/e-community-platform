<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

define('MOD_MAILMAG_DEFAULT_NUM', 10);
define('MOD_MAILMAG_MAX_NUM', 30);

list($eid, $pid) = get_edit_ids();

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

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

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["mailmag_header"] = $_POST["mailmag_header"];
	$SYS_FORM["cache"]["mailmag_footer"] = $_POST["mailmag_footer"];
	$SYS_FORM["cache"]["disp_num"]   = intval($_POST["disp_num"]);
	$SYS_FORM["cache"]["disp_body"]  = intval($_POST["disp_body"]);
	$SYS_FORM["cache"]["write_level"]  = intval($_POST["write_level"]);

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["disp_num"]) {
		$SYS_FORM["cache"]["disp_num"] = MOD_MAILMAG_DEFAULT_NUM;
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	// settingに登録
	$d = mysql_exec("delete from mailmag_setting where eid = %s", mysql_num($eid));
	$q = mysql_exec("insert into mailmag_setting".
					" (eid, header, footer, disp_num, disp_body, write_level)".
					" values (%s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["mailmag_header"]),
					mysql_str($SYS_FORM["cache"]["mailmag_footer"]),
					mysql_num($SYS_FORM["cache"]["disp_num"]),
					mysql_num($SYS_FORM["cache"]["disp_body"]),
					mysql_num($SYS_FORM["cache"]["write_level"]));

	if (!$q) {
		die("insert failuer...");
	}

	$html = '編集完了しました。';
	$data = array(title   => 'メール配信パーツ設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	$d = mysql_uniq("select * from mailmag_setting where eid = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$header      = $d["header"];
		$footer      = $d["footer"];
		$disp_num    = $d["disp_num"];
		$disp_body   = $d["disp_body"];
		$write_level = $d["write_level"];
	}
	else {
		$header      = '';
		$footer      = '';
		$disp_num    = MOD_MAILMAG_DEFAULT_NUM;
		$disp_body   = 0;
		$write_level = 80;
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$header      = $SYS_FORM["cache"]["mailmag_header"];
		$footer      = $SYS_FORM["cache"]["mailmag_footer"];
		$disp_num    = $SYS_FORM["cache"]["disp_num"];
		$disp_body   = $SYS_FORM["cache"]["disp_body"];
		$write_level = $SYS_FORM["cache"]["write_level"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:disp_num
	$attr = array(name => 'disp_num', value => $disp_num,
				  size => 3, ahtml => ' (最大'. MOD_MAILMAG_MAX_NUM. '件)');
	$SYS_FORM["input"][] = array(title => 'パーツの表示件数',
								 name  => 'disp_num',
								 body  => get_form("num", $attr));

	// checkbox:disp_body
	$attr = array(name => 'disp_body', value => $disp_body, option => array(1 => '本文も表示する'));
	$SYS_FORM["input"][] = array(title => 'パーツの本文表示',
								 name  => 'disp_body',
								 body  => get_form("checkbox", $attr));

	// select:write_level
	$option = array();
	$q = mysql_full('select * from conf_group_level order by level desc');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$option[$r['level']] = $r['name']. 'まで';
		}
	}
	$attr = array(name => 'write_level', value => $write_level, option => $option);
	$SYS_FORM["input"][] = array(title => 'メール作成可能な人',
								 name  => 'write_level',
								 body  => get_form("select", $attr));

	// fck:header
	$attr = array(name => 'mailmag_header', value => $header,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'ヘッダー (パーツ上部に表示)',
								 name  => 'mailmag_header',
								 body  => get_form("fck", $attr));

	// fck:footer
	$attr = array(name => 'mailmag_footer', value => $footer,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'フッター (パーツ下部に表示)',
								 name  => 'mailmag_footer',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'メール配信パーツ設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function get_url($eid) {
	$url = array();

	$q = mysql_full("select * from rss_url where eid = %s order by num",
					mysql_num($eid));
	if (!$q) {
		return array();
	}
	while ($d = mysql_fetch_array($q)) {
		$url[] = $d["url"];
	}
	return $url;
}

?>
