<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

list($eid, $pid) = get_edit_ids();

/* ふりわけ。*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* とうろく。*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["app_type"]   = intval($_POST["app_type"]);
	$SYS_FORM["cache"]["bbs_header"] = $_POST["bbs_header"];
	$SYS_FORM["cache"]["bbs_footer"] = $_POST["bbs_footer"];
	$SYS_FORM["cache"]["view_type"]  = intval($_POST["view_type"]);
	$SYS_FORM["cache"]["view_num"]   = intval($_POST["view_num"]);
	$SYS_FORM["cache"]["rec_num"]    = intval($_POST["rec_num"]);
	$SYS_FORM["cache"]["backnumber"] = intval($_POST["backnumber"]);
	$SYS_FORM["cache"]["backnum_pmt"] = intval($_POST["backnum_pmt"]);

	if (!$SYS_FORM["cache"]["view_type"]) {
		$SYS_FORM["cache"]["view_type"] = 1;
	}
	if (!$SYS_FORM["cache"]["view_num"]) {
		$SYS_FORM["cache"]["view_num"] = 10;
	}

	if ($SYS_FORM["cache"]["view_num"] > 50) {
		$SYS_FORM["error"]["view_num"] = 'スレッドの表示件数は最大50件です。';
	}
	if ($SYS_FORM["cache"]["rec_num"] > 2000) {
		$SYS_FORM["error"]["rec_num"] = '各スレッドの保存件数は最大2000件です。';
	}

	if ($SYS_FORM["error"]) {
		return;
	}

	// settingにとうろく
	$d = mysql_exec("delete from mod_fbbs_setting where id = %s", mysql_num($eid));
	$q = mysql_exec("insert into mod_fbbs_setting".
					" (id, view_type, view_num, rec_num, backnumber, backnum_pmt, header, footer) ".
					" values (%s, %s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["view_type"]),
					mysql_num($SYS_FORM["cache"]["view_num"]),
					mysql_num($SYS_FORM["cache"]["rec_num"]),
					mysql_num($SYS_FORM["cache"]["backnumber"]),
					mysql_num($SYS_FORM["cache"]["backnum_pmt"]),
					mysql_str($SYS_FORM["cache"]["bbs_header"]),
					mysql_str($SYS_FORM["cache"]["bbs_footer"]));

	$d = mysql_exec('delete from mod_fbbs_allow where eid = %s', mysql_num($eid));
	$u = mysql_exec('insert into mod_fbbs_allow (eid, type) values (%s, %s)',
					mysql_num($eid), mysql_num($SYS_FORM["cache"]["app_type"]));

	$html = '編集完了しました。';
	$data = array(title   => '掲示板（電子会議室）の基本設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* ふぉーむ。*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	$d = mysql_uniq("select * from mod_fbbs_setting where id = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$view_type   = $d["view_type"];
		$view_num    = $d["view_num"];
		$rec_num     = $d["rec_num"];
		$backnumber  = $d["backnumber"];
		$backnum_pmt = $d["backnum_pmt"];

		$header     = $d["header"];
		$footer     = $d["footer"];

		$c = mysql_uniq("select * from mod_fbbs_allow where eid = %s",
						mysql_num($eid));
		if ($c) {
			$app_type = $c['type'];
		}
	}
	else {
		$app_type    = 0;
		$header      = '';
		$footer      = '';
		$view_type   = 1;
		$view_num    = 10;
		$rec_num     = 1000;
		$backnumber  = 0;
		$backnum_pmt = 0;
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$app_type    = $SYS_FORM["cache"]["app_type"];
		$header      = $SYS_FORM["cache"]["bbs_header"];
		$footer      = $SYS_FORM["cache"]["bbs_footer"];
		$view_type   = $SYS_FORM["cache"]["view_type"];
		$view_num    = $SYS_FORM["cache"]["view_num"];
		$rec_num     = $SYS_FORM["cache"]["rec_num"];
		$backnumber  = $SYS_FORM["cache"]["backnumber"];
		$backnum_pmt = $SYS_FORM["cache"]["backnum_pmt"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:type
	$option = array(0 => '誰でも作成可', 1 => '登録ユーザーのみ可');
	if (get_gid($eid) > 0) {
		$option[2] = 'グループ参加者のみ可';
	}
	$attr = array(name => 'app_type', value => $app_type, option => $option);
	$SYS_FORM["input"][] = array(title => '新規スレッドを作成できるユーザーのレベル',
								 name  => 'app_type',
								 body  => get_form("radio", $attr));

	// text:view_num
	$attr = array(name => 'view_num', value => $view_num,
				  size => 3, ahtml => ' (最大50件)');
	$SYS_FORM["input"][] = array(title => 'トップページにおける掲示板（電子会議室）での最新スレッド表示件数',
								 name  => 'view_num',
								 body  => get_form("num", $attr));

	// text:view_num
	$attr = array(name => 'rec_num', value => $rec_num,
				  size => 4, ahtml => ' (最大2000件)');
	$SYS_FORM["input"][] = array(title => '各スレッドに保存できる投稿の総数',
								 name  => 'rec_num',
								 body  => get_form("num", $attr));

	// text:disp_type
	$option = array(1 => 'タイトルのみ表示', 2 => 'スレッドの概要も表示');
	$attr = array(name => 'view_type', value => $view_type, option => $option);
	$SYS_FORM["input"][] = array(title => 'スレッドの表示方法',
								 name  => 'view_type',
								 body  => get_form("radio", $attr));

	// text:disp_type
	$option = array(0 => '保存しない', 1 => '保存する');
	$attr = array(name => 'backnumber', value => $backnumber, option => $option);
	$SYS_FORM["input"][] = array(title => 'バックナンバーの保存',
								 name  => 'backnumber',
								 body  => get_form("radio", $attr));

	// text:disp_type
	$option = array(0 => '誰にでも公開', 1 => '登録ユーザーのみ公開', 2 => '非公開');
	$attr = array(name => 'backnum_pmt', value => $backnum_pmt, option => $option);
	$SYS_FORM["input"][] = array(title => 'バックナンバーの公開',
								 name  => 'backnum_pmt',
								 body  => get_form("radio", $attr));

	// fck:header
	$attr = array(name => 'bbs_header', value => $header,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'ヘッダー (パーツ上部に表示)',
								 name  => 'bbs_header',
								 body  => get_form("fck", $attr));

	// fck:footer
	$attr = array(name => 'bbs_footer', value => $footer,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'フッター (パーツ下部に表示)',
								 name  => 'bbs_footer',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'history.back(); return false;';

	$html = create_form(array(eid => $eid));

	$data = array(title   => '掲示板（電子会議室）基本設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
