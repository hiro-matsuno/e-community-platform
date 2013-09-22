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

	$d = mysql_exec('delete from kml_url where id = %s',
					mysql_num($id));

	$html = '削除しました。';
	$href = '/modules/map/setting.php?eid='. $eid;

	$html .= return_dialog(array('string' => '戻る', 'href' => 'kml_edit.php'));

	$data = array(title   => 'KML削除完了',
				  icon    => 'finish',
				  content => $html);

	show_dialog($data);
}

function list_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$f = mysql_full('select * from kml_url as k'.
					' inner join owner as o on o.id = k.id'.
					' where o.uid = %s', mysql_num(myuid()));

	if (!$f) {
		show_error('編集するKMLがありません。');
	}

	$list = array();

	$list[] = array('id' => '',
					'del' => '',
					'title' => 'タイトル',
					'url' => 'URL');

	while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
		$list[] = array('id'       => make_href('[編集]', 'kml_edit.php?action=edit&id='. $d['id']),
						'del'      => make_href('[削除]', 'kml_edit.php?action=delete&id='. $d['id']),
						'title'  => $d['title'],
						'url' => clip_str($d['url'], 42));
	}

	$style = array('id' => 'width: 4em; text-align: center;',
				   'del' => 'width: 4em; text-align: center;');

	$html = create_list($list, $style);

	$data = array(title   => 'KML編集',
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
	$SYS_FORM["cache"]["title"]    = $_POST["title"];
	$SYS_FORM["cache"]["url"]      = $_POST["url"];

	// settingに登録
	if ($id > 0) {
		$q = mysql_exec("update kml_url".
						" set title = %s, url = %s".
						" where id = %s",
						mysql_str($SYS_FORM["cache"]["title"]),
						mysql_str($SYS_FORM["cache"]["url"]),
						mysql_num($id));
	}
	else {
		show_error('ベースマップID不明');
	}

	$html = '編集完了しました。';
	$href = '/modules/map/setting.php?eid='. $eid;

	$html .= reload_form();

	$data = array(title   => 'KMLの編集完了',
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

	$d = mysql_uniq("select * from kml_url where id = %s",
					mysql_num($id));

	// settingからロード
	// map_type...0:GMAP, 1:GTileLayer, 2:WMS
	if ($d) {
		$title    = $d["title"];
		$url      = $d["url"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'modify');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'id', value =>$id);
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

	$SYS_FORM["action"] = 'kml_edit.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'history.back();';

	$html = create_form();

	$data = array(title   => 'KMLの編集',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
