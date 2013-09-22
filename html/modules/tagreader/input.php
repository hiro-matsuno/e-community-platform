<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/common.php';

define('MOD_TAGREADER_DEFAULT_NUM', 10);
define('MOD_TAGREADER_MAX_NUM', 30);

/* 振り分け*/
$id = (intval($_REQUEST["eid"]) > 0) ? $_REQUEST["eid"] : $_REQUEST["pid"];
print $id;
if(!is_owner($id,80))show_error('権限がありません');

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($id);
	default:
		input_data($id);
}

/* 登録*/
function regist_data($id = null) {
	global $SYS_FORM;

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["mod_target"] = join(' ', $_POST["mod_target"]);
	$SYS_FORM["cache"]["tagreader_header"] = $_POST["tagreader_header"];
	$SYS_FORM["cache"]["tagreader_footer"] = $_POST["tagreader_footer"];
	$SYS_FORM["cache"]["keyword"]   = $_POST["keyword"];
	$SYS_FORM["cache"]["disp_num"]  = intval($_POST["disp_num"]);
	$SYS_FORM["cache"]["disp_body"] = intval($_POST["disp_body"]);

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["disp_num"]) {
		$SYS_FORM["cache"]["disp_num"] = MOD_tagreader_DEFAULT_NUM;
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	// 登録
	$eid = intval($_REQUEST["eid"]);
	$pid = intval($_REQUEST["pid"]);

	if ($eid == 0) {
		die("eid not found.");
	}

	// settingに登録
	$d = mysql_exec("delete from tagreader_setting where eid = %s", mysql_num($eid));
	$q = mysql_exec("insert into tagreader_setting".
					" (eid, mod_target, header, footer, keyword, disp_num)".
					" values (%s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["mod_target"]),
					mysql_str($SYS_FORM["cache"]["tagreader_header"]),
					mysql_str($SYS_FORM["cache"]["tagreader_footer"]),
					mysql_str($SYS_FORM["cache"]["keyword"]),
					mysql_num($SYS_FORM["cache"]["disp_num"]));

	if (!$q) {
		die("insert failure...");
	}

	mod_tagreader_crawl($eid);

	$html = '編集完了しました。';
	$data = array(title   => 'タグリーダーの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($id = null) {
	global $SYS_FORM, $JQUERY;

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	$eid = $id;

	$d = mysql_uniq("select * from tagreader_setting where eid = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$mod_target = $d["mod_target"];
		$header     = $d["header"];
		$footer     = $d["footer"];
		$keyword    = $d["keyword"];
		$disp_num   = $d["disp_num"];
		$disp_body  = $d["disp_body"];
	}
	else {
		$mod_target = '';
		$header     = '';
		$footer     = '';
		$keyword    = '';
		$disp_num   = MOD_TAGREADER_DEFAULT_NUM;
		$disp_body  = 0;
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$mod_target = $SYS_FORM["cache"]["tagreader_header"];
		$header     = $SYS_FORM["cache"]["tagreader_header"];
		$footer     = $SYS_FORM["cache"]["tagreader_footer"];
		$keyword    = $SYS_FORM["cache"]["keyword"];
		$disp_num   = $SYS_FORM["cache"]["disp_num"];
		$disp_body  = $SYS_FORM["cache"]["disp_body"];
	}

	$mod_checked = explode(' ', $mod_target);
	foreach ($mod_checked as $mod) {
		$value[$mod] = true;
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$q = mysql_full("select * from module_setting");

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			switch ($r['mod_name']) {
				case 'blog':
				case 'schedule':
				case 'map':
				case 'page':
					$option[$r['mod_name']] = $r['mod_title'];
					break;
				default:
			}
		}
	}

	$attr = array(name => 'mod_target', value => $value, option => $option);
	$SYS_FORM["input"][] = array(title => '検索モジュール',
								 name  => 'mod_target',
								 body  => get_form("checkbox", $attr));

	// text:keyword
	$attr = array(name => 'keyword', value => $keyword,
				  size => 32, ahtml => '複数は空白で区切ってください。');
	$SYS_FORM["input"][] = array(title => '検索キーワード',
								 name  => 'keyword',
								 body  => get_form("text", $attr));

	// text:disp_num
	$attr = array(name => 'disp_num', value => $disp_num,
				  size => 3, ahtml => '最大'. MOD_TAGREADER_MAX_NUM. '件');
	$SYS_FORM["input"][] = array(title => '表示件数',
								 name  => 'disp_num',
								 body  => get_form("num", $attr));
/*
	// checkbox:disp_body
	$attr = array(name => 'disp_body', value => $disp_body, option => array(1 => '概要文も表示する'),
				  ahtml => '文中のHTMLタグ等は無効になります。');
	$SYS_FORM["input"][] = array(title => '概要文の表示',
								 name  => 'disp_body',
								 body  => get_form("checkbox", $attr));
*/
	// fck:header
	$attr = array(name => 'tagreader_header', value => $header,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'ヘッダー (一覧の上側に表示)',
								 name  => 'tagreader_header',
								 body  => get_form("fck", $attr));

	// fck:footer
	$attr = array(name => 'tagreader_footer', value => $footer,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'フッター (一覧の下側に表示)',
								 name  => 'tagreader_footer',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
//	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid, pid => $id));

	$data = array(title   => 'タグリーダーの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
