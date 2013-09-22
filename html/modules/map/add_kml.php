<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

echo '調整中'; exit;

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$eid = intval($_REQUEST["eid"]);
$pid = intval($_REQUEST["pid"]);

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	return;

	$id = ($eid > 0) ? $eid : (($pid > 0) ? $pid : null);

	if ($id = null) {
		show_error('選択IDがありません。');
	}

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["header"]    = $_POST["header"];
	$SYS_FORM["cache"]["footer"]    = $_POST["footer"];
	$SYS_FORM["cache"]["keyword"]   = $_POST["keyword"];
	$SYS_FORM["cache"]["disp_num"]  = intval($_POST["disp_num"]);
	$SYS_FORM["cache"]["disp_body"] = intval($_POST["disp_body"]);
	foreach ($_POST as $key => $value) {
		if (preg_match('/^url_\d/', $key, $match)) {
			if ($value != '') {
				$SYS_FORM["cache"]["url"][] = $value;
			}
		}
	}
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["url"]) {
		$SYS_FORM["error"]["url"] = '最低1個のURLを入力してください。';
	}
	if (!$SYS_FORM["cache"]["disp_num"]) {
		$SYS_FORM["cache"]["disp_num"] = MOD_RSS_DEFAULT_NUM;
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
	$d = mysql_exec("delete from rss_setting where eid = %s", mysql_num($eid));
	$q = mysql_exec("insert into rss_setting".
					" (eid, header, footer, keyword, disp_num, disp_body)".
					" values (%s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["header"]),
					mysql_str($SYS_FORM["cache"]["footer"]),
					mysql_str($SYS_FORM["cache"]["keyword"]),
					mysql_num($SYS_FORM["cache"]["disp_num"]),
					mysql_num($SYS_FORM["cache"]["disp_body"]));

	if (!$q) {
		die("insert failuer...");
	}

	// urlに登録
	$d = mysql_exec("delete from rss_url where eid = %s", mysql_num($eid));
	foreach ($SYS_FORM["cache"]["url"] as $url) {
		$q = mysql_exec("insert into rss_url (eid, url) values (%s, %s)",
						mysql_num($eid), mysql_str($url));
		if (!$q) {
			die("insert url failure...");
		}
	}

	mod_rss_crawl($eid);

	$html = '編集完了しました。';
	$data = array(title   => 'RSSの編集完了',
				  icon    => 'finish',
				  content => $html. create_rform(array(eid => $eid, href => home_url($eid))));

	show_dialog2($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_DEBUG;

	$id = ($eid > 0) ? $eid : (($pid > 0) ? $pid : null);

	if (!$id) {
		show_error('選択IDがありません。');
	}

	if (!is_owner($id)) {
		show_login();
	}

	// checkbox:map_base
	$f = mysql_full("select * from map_base_data".
					" where id = %s order by vpos",
					mysql_num($id));

	$cur_base = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_base[$r['map_id']] = $r['vpos'];
		}
	}

	$m = mysql_full("select * from map_base");

	$num = count($cur_base); $opt = array();
	$option = array(); $value = array();
	if ($m) {
		while ($r = mysql_fetch_array($m)) {
			if (isset($cur_base[$r['id']])) {
				$value[$r['id']] = true;
				$opt[$r['id']] = array(id => $r['id'], name => $r['cp_name']);
			}
			else {
				$num++;
				$opt[$num] = array(id => $r['id'], name => $r['cp_name']);
			}
		}
	}
	if (count($opt) > 0) {
		sort($opt);
	}
	foreach ($opt as $o) {
		$option[$o['id']] = $o['name'];
	}

	$add_href = '/modules/map/add_base.php?eid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
	$add_form = '<div style="text-align: left; padding: 4px;">'.
				'<img src="/fr.gif" align="absmiddle"> <a href="'. $add_href.
				'" style="font-size: 0.8em;" class="thickbox">ベースマップを追加する</a>'.
				'</div>';

	$attr = array(name => 'map_base', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => 'ベースマップの選択',
								 name  => 'subject',
								 body  => get_form("checkbox", $attr). $add_form);

	// checkbox:map_layer
	$f = mysql_full("select * from map_layer_data".
					" where id = %s order by vpos",
					mysql_num($id));

	$cur_layer = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_layer[$r['map_id']] = $r['vpos'];
		}
	}

	$m = mysql_full("select * from map_layer");

	$num = count($cur_layer); $opt = array();
	$option = array(); $value = array();
	if ($m) {
		while ($r = mysql_fetch_array($m)) {
			if (isset($cur_layer[$r['id']])) {
				$value[$r['id']] = true;
				$opt[$r['id']] = array(id => $r['id'], name => $r['cp_name']);
			}
			else {
				$num++;
				$opt[$num] = array(id => $r['id'], name => $r['cp_name']);
			}
		}
	}
	if (count($opt) > 0) {
		sort($opt);
	}
	foreach ($opt as $o) {
		$option[$o['id']] = $o['name'];
	}

	$add_href = '/modules/map/add_layer.php?eid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
	$add_form = '<div style="text-align: left; padding: 4px;">'.
				'<img src="/fr.gif" align="absmiddle"> <a href="'. $add_href.
				'" style="font-size: 0.8em;" class="thickbox">外部レイヤーを追加する</a>'.
				'</div>';

	$attr = array(name => 'map_layer', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => '外部レイヤー(WMS)の選択',
								 name  => 'subject',
								 body  => get_form("checkbox", $attr). $add_form);

	// checkbox:map_kml
	$f = mysql_full("select * from map_kml_data".
					" where id = %s order by vpos",
					mysql_num($id));

	$cur_kml = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_kml[$r['map_id']] = $r['vpos'];
		}
	}

	$m = mysql_full("select * from map_kml");

	$num = count($cur_kml); $opt = array();
	$option = array(); $value = array();
	if ($m) {
		while ($r = mysql_fetch_array($m)) {
			if (isset($cur_kml[$r['id']])) {
				$value[$r['id']] = true;
				$opt[$r['id']] = array(id => $r['id'], name => $r['cp_name']);
			}
			else {
				$num++;
				$opt[$num] = array(id => $r['id'], name => $r['cp_name']);
			}
		}
	}
	if (count($opt) > 0) {
		sort($opt);
	}
	foreach ($opt as $o) {
		$option[$o['id']] = $o['name'];
	}

	$add_href = '/modules/map/add_kml.php?eid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
	$add_form = '<div style="text-align: left; padding: 4px;">'.
				'<img src="/fr.gif" align="absmiddle"> <a href="'. $add_href.
				'" style="font-size: 0.8em;" class="thickbox">KMLを追加する</a>'.
				'</div>';

	$attr = array(name => 'map_kml', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => '情報レイヤー(KML)の選択',
								 name  => 'subject',
								 body  => get_form("checkbox", $attr). $add_form);



	// settingからロード
	if ($d) {
		$header    = $d["header"];
		$footer    = $d["footer"];
		$keyword   = $d["keyword"];
		$disp_num  = $d["disp_num"];
		$disp_body = $d["disp_body"];
		$url       = get_url($eid);
	}
	else {
		$header    = '';
		$footer    = '';
		$keyword   = '';
		$disp_num  = MOD_RSS_DEFAULT_NUM;
		$disp_body = 0;
		$url       = array();
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$header    = $SYS_FORM["cache"]["header"];
		$footer    = $SYS_FORM["cache"]["footer"];
		$keyword   = $SYS_FORM["cache"]["keyword"];
		$disp_num  = $SYS_FORM["cache"]["disp_num"];
		$disp_body = $SYS_FORM["cache"]["disp_body"];
		$url       = $SYS_FORM["cache"]["url"];
	}


	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// fck:header
	$attr = array(name => 'header', value => $header,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'ヘッダー (マップの上側に表示)',
								 name  => 'header',
								 body  => get_form("fck", $attr));

	// fck:footer
	$attr = array(name => 'footer', value => $footer,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'フッター (マップの下側に表示)',
								 name  => 'footer',
								 body  => get_form("fck", $attr));


	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

	$html = create_form(array(eid => $eid, pid => $id));

	$data = array(title   => 'マップの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}


?>
