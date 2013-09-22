<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

define('BLOCK_TITLE', 'ブログ基本設定');
define('DEFAULT_VIEW_TYPE', 1);
define('DEFAULT_VIEW_NUM', 5);

/*----------------------------------------------------*
 *- action
 *----------------------------------------------------*/
list($eid, $pid) = get_edit_ids();

if(!is_owner($pid,80))show_error('権限がありません');

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/*----------------------------------------------------*
 *- regist_data
 *----------------------------------------------------*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$view_type = intval($_POST['view_type']);
	$view_num = intval($_POST['view_num']);

	$d = mysql_exec('delete from blog_setting where id = %s', $pid);
	$q = mysql_exec('insert into blog_setting'.
					' (id, view_type, view_num) values (%s, %s, %s)',
					mysql_num($pid), mysql_num($view_type), mysql_num($view_num));

	$html = '設定が完了しました。';
	$data = array(title   => BLOCK_TITLE,
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $pid, href => home_url($pid))));

	show_input($data);

	exit(0);
}

/*----------------------------------------------------*
 *- input_data
 *----------------------------------------------------*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$view_type = DEFAULT_VIEW_TYPE;
	$view_num  = DEFAULT_VIEW_NUM;

	$s = mysql_uniq("select * from blog_setting where id = %s",
					mysql_num($pid));

	if ($s) {
		$view_type = $s["view_type"];
		$view_num  = $s["view_num"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// radio:view_type
	$option = array(0 => '非表示', 1 => '全文表示', 2 => '本文を省略して表示', 3 => 'タイトルだけ表示');
	$attr = array(name => 'view_type', value => $view_type, option => $option);
	$SYS_FORM["input"][] = array(title => 'パーツでの表示方法',
								 name  => 'view_type',
								 body  => get_form("radio", $attr));
	// radio:view_num
	$attr = array(name => 'view_num', value => $view_num, size => 3);
	$SYS_FORM["input"][] = array(title => '表示する件数',
								 name  => 'view_num',
								 body  => get_form("num", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';
	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => BLOCK_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
