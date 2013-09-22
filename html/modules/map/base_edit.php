<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = array(0, 0);

switch ($_REQUEST["action"]) {
	case 'modify':
		modify_data($eid, $pid);
	break;
	case 'delete':
		delete_data($eid, $pid);
	break;
	case 'edit':
		edit_data($eid, $pid);
	break;
	default:
		list_data($eid, $pid);
}

function delete_data($eid = null, $pid = null) {
	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	$d = mysql_exec('delete from map_base where id = %s',
					mysql_num($id));

	$html = '削除しました。';
	$href = '/modules/map/setting.php?eid='. $eid;

	$html .= return_dialog(array('string' => '戻る', 'href' => 'layer_edit.php'));

	$data = array(title   => 'ベースマップ削除完了',
				  icon    => 'finish',
				  content => $html);

	show_dialog($data);
}

function list_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$f = mysql_full('select * from map_base order by id desc');

	if (!$f) {
		show_error('編集するベースマップがありません。');
	}

	$list = array();

	$list[] = array('id' => '',
					'del' => '',
					'cp_name' => '表示名',
					'base_url' => 'リクエストURL');

	while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
		$list[] = array('id'       => make_href('[編集]', 'base_edit.php?action=edit&id='. $d['id']),
						'del'      => make_href('[削除]', 'base_edit.php?action=delete&id='. $d['id']),
						'cp_name'  => $d['cp_name'],
						'base_url' => clip_str($d['base_url'], 42));
	}

	$style = array('id' => 'width: 4em; text-align: center;',
				   'del' => 'width: 4em; text-align: center;');

	$html = create_list($list, $style);

	$data = array(title   => 'ベースマップ編集',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}


/* 登録*/
function modify_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["map_type"]      = $_POST["map_type"];
	$SYS_FORM["cache"]["base_url"]      = $_POST["base_url"];
	$SYS_FORM["cache"]["bbox_format"]   = $_POST["bbox_format"];
	$SYS_FORM["cache"]["use_geo"]       = $_POST["use_geo"];
	$SYS_FORM["cache"]["cp_name"]       = $_POST["cp_name"];
	$SYS_FORM["cache"]["cp_name_short"] = $_POST["cp_name_short"];
	$SYS_FORM["cache"]["cp_text"]       = $_POST["cp_text"];
	$SYS_FORM["cache"]["min_scale"]     = ($_POST["min_scale"] != '') ? intval($_POST["min_scale"]): 0;
	$SYS_FORM["cache"]["max_scale"]     = ($_POST["max_scale"] != '') ? intval($_POST["max_scale"]): 19;
	$SYS_FORM["cache"]["opacity"]       = ($_POST["opacity"] != '') ? $_POST["opacity"] : 1;

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["base_url"]) {
		$SYS_FORM["error"]["base_url"] = 'リクエストURLを入力してください。';
	}
	if (!$SYS_FORM["cache"]["cp_name"]) {
		$SYS_FORM["error"]["cp_name"] = 'ベースマップ名を入力してください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	// settingに登録
	if ($id > 0) {
		$q = mysql_exec("update map_base".
						" set map_type = %s, base_url = %s, bbox_format = %s,".
						" use_geo = %s, cp_name = %s, cp_name_short = %s, cp_text = %s,".
						" min_scale = %s, max_scale = %s, opacity = %s".
						" where id = %s",
						mysql_num($SYS_FORM["cache"]["map_type"]),
						mysql_str($SYS_FORM["cache"]["base_url"]),
						mysql_str($SYS_FORM["cache"]["bbox_format"]),
						mysql_num($SYS_FORM["cache"]["use_geo"]),
						mysql_str($SYS_FORM["cache"]["cp_name"]),
						mysql_str($SYS_FORM["cache"]["cp_name_short"]),
						mysql_str($SYS_FORM["cache"]["cp_text"]),
						mysql_num($SYS_FORM["cache"]["min_scale"]),
						mysql_num($SYS_FORM["cache"]["max_scale"]),
						mysql_str($SYS_FORM["cache"]["opacity"]),
						mysql_num($id));
	}
	else {
		show_error('ベースマップID不明');
	}

	$html = '編集完了しました。';
	$href = '/modules/map/setting.php?eid='. $eid;

	$html .= reload_form();

	$data = array(title   => 'ベースマップの追加完了',
				  icon    => 'finish',
				  content => $html);

	show_dialog($data);

	exit(0);
}

/* フォーム*/
function edit_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_DEBUG;

	$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id == 0) {
		show_error('ベースマップを選択して下さい。');
	}

	$d = mysql_uniq("select * from map_base where id = %s",
					mysql_num($id));

	// settingからロード
	// map_type...0:GMAP, 1:GTileLayer, 2:WMS
	if ($d) {
		$map_type      = $d["map_type"];
		$base_url      = $d["base_url"];
		$bbox_format   = $d["bbox_format"];
		$use_geo       = $d["use_geo"];
		$cp_name       = $d["cp_name"];
		$cp_name_short = $d["cp_name_short"];
		$cp_text       = $d["cp_text"];
		$min_scale     = $d["min_scale"];
		$max_scale     = $d["max_scale"];
		$opacity       = $d["opacity"];
	}
	else {
		$map_type      = 2;
		$base_url      = '';
		$bbox_format   = '';
		$use_geo       = '0';
		$cp_name       = '';
		$cp_name_short = '';
		$cp_text       = '';
		$min_scale     = 0;
		$max_scale     = 19;
		$opacity       = '1.0';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$map_type      = $SYS_FORM["cache"]["map_type"];
		$base_url      = $SYS_FORM["cache"]["base_url"];
		$bbox_format   = $SYS_FORM["cache"]["bbox_format"];
		$use_geo       = $SYS_FORM["cache"]["use_geo"];
		$cp_name       = $SYS_FORM["cache"]["cp_name"];
		$cp_name_short = $SYS_FORM["cache"]["cp_name_short"];
		$cp_text       = $SYS_FORM["cache"]["cp_text"];
		$min_scale     = $SYS_FORM["cache"]["min_scale"];
		$max_scale     = $SYS_FORM["cache"]["max_scale"];
		$opacity       = $SYS_FORM["cache"]["opacity"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'modify');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'id', value =>$id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// radio:map_type
	$option = array(0 => 'Google Maps標準', 1 => 'Google Maps タイル',
					2 => 'WMS');

	$attr = array(name => 'map_type', value => $map_type, option => $option);
	$SYS_FORM["input"][] = array(title => 'マップの種類',
								 name  => 'map_type',
								 body  => get_form("radio", $attr));

	// text:base_url
	$attr = array(name => 'base_url', value => $base_url, size => 48);
	$SYS_FORM["input"][] = array(title => 'リクエストURL(引数付きで)',
								 name  => 'base_url',
								 body  => get_form("text", $attr));

	// text:bbox_format
	$attr = array(name => 'bbox_format', value => $bbox_format, size => 48);
	$SYS_FORM["input"][] = array(title => 'BBOXのフォーマット (例 [west],[south],[east],[north])',
								 name  => 'bbox_format',
								 body  => get_form("text", $attr));

	// radio:use_geo
	$option = array(0 => 'EPSG:54004', 1 => 'EPSG:4326');
	$attr = array(name => 'use_geo', value => $use_geo, option => $option);
	$SYS_FORM["input"][] = array(title => '座標系',
								 name  => 'use_geo',
								 body  => get_form("radio", $attr));

	// text:cp_name
	$attr = array(name => 'cp_name', value => $cp_name, size => 32);
	$SYS_FORM["input"][] = array(title => 'ベースマップの名前',
								 name  => 'cp_name',
								 body  => get_form("text", $attr));
	// text:cp_name_short
	$attr = array(name => 'cp_name_short', value => $cp_name_short, size => 16);
	$SYS_FORM["input"][] = array(title => 'ベースマップの名前(省略形)',
								 name  => 'cp_name_short',
								 body  => get_form("text", $attr));
	// text:cp_text
	$attr = array(name => 'cp_text', value => $cp_text, size => 16);
	$SYS_FORM["input"][] = array(title => '著作権の表記',
								 name  => 'cp_text',
								 body  => get_form("text", $attr));
	// num:min_scale
	$attr = array(name => 'min_scale', value => $min_scale, size => 3);
	$SYS_FORM["input"][] = array(title => '拡大の最小値 (0～19)',
								 name  => 'min_scale',
								 body  => get_form("num", $attr));
	// num:max_scale
	$attr = array(name => 'max_scale', value => $max_scale, size => 3);
	$SYS_FORM["input"][] = array(title => '拡大の最大値 (0～19)',
								 name  => 'max_scale',
								 body  => get_form("num", $attr));
	// num:opacity
	$attr = array(name => 'opacity', value => $opacity, size => 3);
	$SYS_FORM["input"][] = array(title => '透明度 (0.1～1.0)',
								 name  => 'opacity',
								 body  => get_form("num", $attr));

	$SYS_FORM["action"] = 'base_edit.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'history.back();';

	$html = create_form();

	$data = array(title   => 'ベースマップの編集',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
