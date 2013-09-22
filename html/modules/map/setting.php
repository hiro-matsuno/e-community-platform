<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$eid = intval($_REQUEST["eid"]);
$pid = intval($_REQUEST["pid"]);

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$id = ($eid > 0) ? $eid : (($pid > 0) ? $pid : null);

	if ($id == null) {
		show_error('選択IDがありません。');
	}

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["map_base"]  = $_POST["map_base"];
	$SYS_FORM["cache"]["map_layer"] = $_POST["map_layer"];
	$SYS_FORM["cache"]["map_kml"]   = $_POST["map_kml"];
	$SYS_FORM["cache"]["kml_url"]   = $_POST["kml_url"];
	$SYS_FORM["cache"]["header"]    = $_POST["header"];
	$SYS_FORM["cache"]["footer"]    = $_POST["footer"];

	if ($SYS_FORM["cache"]["header"] == '<br />') {
		$SYS_FORM["cache"]["header"] = '';
	}
	if ($SYS_FORM["cache"]["footer"] == '<br />') {
		$SYS_FORM["cache"]["footer"] = '';
	}

	// 入力エラーチェック
	if (count($SYS_FORM["cache"]["map_base"]) == 0) {
		$SYS_FORM["error"]["map_base"] = 'URLを1個くらいは入力してください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	// settingへ登録
	$d = mysql_exec("delete from map_setting where id = %s", mysql_num($id));
	$q = mysql_exec("insert into map_setting".
					" (id, header, footer)".
					" values (%s, %s, %s)",
					mysql_num($id),
					mysql_str($SYS_FORM["cache"]["header"]),
					mysql_str($SYS_FORM["cache"]["footer"]));
	if (!$q) {
		die("insert failure...". mysql_error());
	}

	// map_baseへ登録
	$d = mysql_exec("delete from map_base_data where id = %s", mysql_num($id));
	$vpos = 0;
	foreach ($SYS_FORM["cache"]["map_base"] as $val) {
		$q = mysql_exec("insert into map_base_data".
						" (id, map_id, vpos)".
						" values (%s, %s, %s)",
						mysql_num($id),
						mysql_num($val),
						mysql_num($vpos));
		$vpos++;
		if (!$q) {
			die("insert failure...". mysql_error());
		}
	}

	// map_layerへ登録
	$d = mysql_exec("delete from map_layer_data where id = %s", mysql_num($id));
	$vpos = 0;
	if ($SYS_FORM["cache"]["map_layer"]) {
		foreach ($SYS_FORM["cache"]["map_layer"] as $val) {
			$q = mysql_exec("insert into map_layer_data".
							" (id, layer_id, vpos, visible)".
							" values (%s, %s, %s, %s)",
							mysql_num($id),	mysql_num($val),
							mysql_num($vpos), mysql_num(1));
			$vpos++;
			if (!$q) {
				die("insert failure...". mysql_error());
			}
		}
	}

	// map_layerへ登録
	$d = mysql_exec("delete from map_kml_data where id = %s", mysql_num($id));
	$vpos = 0;
	foreach ($SYS_FORM["cache"]["map_kml"] as $val) {
		$q = mysql_exec("insert into map_kml_data".
						" (id, kml_id, vpos, visible)".
						" values (%s, %s, %s, %s)",
						mysql_num($id),	mysql_num($val),
						mysql_num($vpos), mysql_num(1));
		$vpos++;
		if (!$q) {
			die("insert failure...". mysql_error());
		}
	}

	// map_layerへ登録
	$d = mysql_exec("delete from kml_url_data where id = %s", mysql_num($id));
	$vpos = 0;
	foreach ($SYS_FORM["cache"]["kml_url"] as $val) {
		$q = mysql_exec("insert into kml_url_data".
						" (id, kml_id, vpos, visible)".
						" values (%s, %s, %s, %s)",
						mysql_num($id),	mysql_num($val),
						mysql_num($vpos), mysql_num(1));
		$vpos++;
		if (!$q) {
			die("insert failure...". mysql_error());
		}
	}


	$html = '編集完了しました。';
	$data = array(title   => 'マップの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($id))));

	show_input($data);

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

	if (is_admin()) {
		$add_href = '/modules/map/add_base.php?pid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
		$add_form = '<div style="text-align: left; padding: 2px;">'.
					'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
					'" style="font-size: 0.8em;" class="thickbox">ベースマップを追加する</a>'.
					'</div>';
		$add_href = '/modules/map/base_edit.php?keepThis=true&TB_iframe=true&height=480&width=640';
		$add_form .= '<div style="text-align: left; padding: 2px;">'.
					'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
					'" style="font-size: 0.8em;" class="thickbox">ベースマップの編集</a>'.
					'</div>';

	}

	if (isset($SYS_FORM["cache"]["map_base"])) {
		$value = array();
		foreach ($SYS_FORM["cache"]["map_base"] as $val) {
			$value[$val] = true;
		}
	}
	$attr = array(name => 'map_base', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => 'ベースマップの選択',
								 name  => 'map_base',
								 body  => get_form("checkbox", $attr). $add_form);

	$option = array();
	// checkbox:map_layer
	$f = mysql_full("select * from map_layer_data".
					" where id = %s order by vpos",
					mysql_num($id));

	$cur_layer = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_layer[$r['layer_id']] = $r['vpos'];
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

	if (is_admin()) {
		$add_href = '/modules/map/add_layer.php?pid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
		$add_form = '<div style="text-align: left; padding: 2px;">'.
					'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
					'" style="font-size: 0.8em;" class="thickbox">外部レイヤーを追加する</a>'.
					'</div>';

		$add_href = '/modules/map/layer_edit.php?keepThis=true&TB_iframe=true&height=480&width=640';
		$add_form .= '<div style="text-align: left; padding: 2px;">'.
					'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
					'" style="font-size: 0.8em;" class="thickbox">外部レイヤーの編集</a>'.
					'</div>';
	}

	if (isset($SYS_FORM["cache"]["map_layer"])) {
		$value = array();
		foreach ($SYS_FORM["cache"]["map_layer"] as $val) {
			$value[$val] = true;
		}
	}
	$attr = array(name => 'map_layer', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => '外部レイヤーの選択',
								 name  => 'map_layer',
								 body  => get_form("checkbox", $attr). $add_form);

	$option = array();
	// checkbox:map_kml
	$f = mysql_full("select * from map_kml_data".
					" where id = %s order by vpos",
					mysql_num($id));

	$cur_kml = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_kml[$r['kml_id']] = $r['vpos'];
		}
	}

	$q = mysql_full("select block.* from block where block.pid = %s",
					mysql_num(get_site_id($id)));

	$extmap = array('bosai_web' => true, 'reporter' => true, 'blog' => true, 'schedule' => true, 'mapmaker' => true);

	$num = count($cur_kml); $opt = array();
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			if ($extmap[$r["module"]] != true) {
				continue;
			}
			$name = $r["name"];
			if (isset($cur_kml[$r['id']])) {
				$value[$r['id']] = true;
				$opt[$r['id']] = array(id => $r['id'], name => $name);
			}
			else {
				$num++;
				$opt[$num] = array(id => $r['id'], name => $name);
			}
		}
	}
	if (count($opt) > 0) {
		sort($opt);
	}
	foreach ($opt as $o) {
		$option[$o['id']] = $o['name'];
	}
/*
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
*/

	if (isset($SYS_FORM["cache"]["map_kml"])) {
		$value = array();
		foreach ($SYS_FORM["cache"]["map_kml"] as $val) {
			$value[$val] = true;
		}
	}
	$attr = array(name => 'map_kml', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => 'サイト内情報の選択',
								 name  => 'map_kml',
								 body  => get_form("checkbox", $attr));

	$add_href = '/modules/map/kml_add.php?&keepThis=true&TB_iframe=true&height=480&width=640';
	$add_form = '<div style="text-align: left; padding: 2px;">'.
				'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
				'" style="font-size: 0.8em;" class="thickbox">KMLを追加する</a>'.
				'</div>';

	$add_href = '/modules/map/kml_edit.php?keepThis=true&TB_iframe=true&height=480&width=640';
	$add_form .= '<div style="text-align: left; padding: 2px;">'.
				'<img src="/image/fr.gif" align="absmiddle"> <a href="'. $add_href.
				'" style="font-size: 0.8em;" class="thickbox">KMLの編集</a>'.
				'</div>';

	$option = array(); $value = array();
	$k = mysql_full('select * from kml_url as k'.
					' inner join owner as o on o.id = k.id'.
					' where o.uid = %s',
					mysql_num(myuid()));

	if ($k) {
		while ($r = mysql_fetch_array($k)) {
			$option[$r['id']] = $r['title'];
		}
	}

	$d = mysql_full('select * from kml_url_data'.
					' where id = %s',
					mysql_num($id));
	if ($d) {
		while ($r = mysql_fetch_array($d)) {
			$value[$r['kml_id']] = true;
		}
	}

	$attr = array(name => 'kml_url', value => $value, option => $option, split => '<br />');
	$SYS_FORM["input"][] = array(title => '外部KMLの選択',
								 name  => 'kml_url',
								 body  => get_form("checkbox", $attr). $add_form);

	$d = mysql_uniq("select * from map_setting where id = %s",
					mysql_num($id));

	// settingからロード
	if ($d) {
		$header    = $d["header"];
		$footer    = $d["footer"];
	}
	else {
		$header    = '';
		$footer    = '';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$header    = $SYS_FORM["cache"]["header"];
		$footer    = $SYS_FORM["cache"]["footer"];
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

	$html = create_form(array(eid => $eid, pid => $id));

	$data = array(title   => 'マップの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}


?>
