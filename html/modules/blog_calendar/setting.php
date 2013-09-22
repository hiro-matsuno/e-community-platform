<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

define('DEFAULT_VIEW_TYPE',1);

require_once dirname(__FILE__). '/../../lib.php';

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

	$target     = $_POST['target'];

	// settingに登録
	$d = mysql_exec("delete from blog_calendar_setting where id = %s", mysql_num($pid));
	$q = mysql_exec("insert into blog_calendar_setting".
					" (id, view_type) values (%s, %s)",
					mysql_num($pid), mysql_num(0));


	$d = mysql_exec("delete from blog_calendar_list where id = %s", mysql_num($pid));
	$value = array();
	foreach ($target as $t) {
		$value[] = '('. mysql_num($pid). ', '. mysql_num($t). ')';
	}
	$i = mysql_exec("insert into blog_calendar_list (id, blog_id) values %s",
					implode(',', $value));

	$html = '編集完了しました。';
	$data = array(title   => 'ブログカレンダー設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(pid => $pid, href => home_url($pid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	//同一ページ内のブログパーツのリストを作成
	$b = mysql_full('select * from block where pid = %s and module = %s',
					mysql_num(get_site_id($pid)), mysql_str('blog'));

	$option = array();
	if ($b) {
		while ($r = mysql_fetch_array($b)) {
			$option[$r['id']] = $r['name'];
		}
	}

	$d = mysql_uniq("select * from blog_calendar_setting where id = %s",
					mysql_num($pid));

	// settingからロード
	if ($d) {
		$view_type = $d["view_type"];
		$opt_value  = array();
		$b = mysql_full('select * from blog_calendar_list where id = %s',
						mysql_num($pid));
		if ($b) {
			while ($r = mysql_fetch_array($b)) {
				$opt_value[$r['blog_id']] = true;
			}
		}
	}
	else {
		$view_type = DEFAULT_VIEW_TYPE;
		$opt_value  = array();
		foreach($option as $key => $val)$opt_value[$key]=true;
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// radio:view_type
//	$opt_view_type = array(0 => '非表示', 1 => '全文表示', 2 => '本文を省略して表示', 3 => 'タイトルだけ表示');
//	$attr = array(name => 'view_type', value => $view_type, option => $opt_view_type);
//	$SYS_FORM["input"][] = array(title => 'ブロックでの表示方法',
//								 name  => 'view_type',
//								 body  => get_form("radio", $attr));

	// checkbox:target
	$attr = array(name => 'target', value => $opt_value, option => $option);
	$SYS_FORM["input"][] = array(title => '対象ブログ',
								 name  => 'target',
								 body  => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '設定';

	$html = create_form(array(pid => $pid));

	$data = array(title   => 'ブログカレンダー設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
