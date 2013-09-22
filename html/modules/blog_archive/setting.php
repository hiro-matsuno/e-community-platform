<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

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

	$latest_num = intval($_POST['latest_num']);
	$target     = $_POST['target'];

	// settingに登録
	$d = mysql_exec("delete from blog_archive_setting where id = %s", mysql_num($eid));
	$q = mysql_exec("insert into blog_archive_setting".
					" (id, latest_num) values (%s, %s)",
					mysql_num($eid), mysql_num($latest_num));


	$d = mysql_exec("delete from blog_archive_list where id = %s", mysql_num($eid));
	$value = array();
	foreach ($target as $t) {
		$value[] = '('. mysql_num($eid). ', '. mysql_num($t). ')';
	}
	$i = mysql_exec("insert into blog_archive_list (id, blog_id) values %s",
					implode(',', $value));

	$html = '編集完了しました。';
	$data = array(title   => 'ブログアーカイブ設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	//同一ページ内のブログパーツのリストを作成
	$b = mysql_full('select * from block where pid = %s and module = %s',
					mysql_num(get_site_id($eid)), mysql_str('blog'));

	$option = array();
	if ($b) {
		while ($r = mysql_fetch_array($b)) {
			$option[$r['id']] = $r['name'];
		}
	}

	$d = mysql_uniq("select * from blog_archive_setting where id = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$latest_num = $d["latest_num"];
		$opt_value  = array();
		$b = mysql_full('select * from blog_archive_list where id = %s',
						mysql_num($eid));
		if ($b) {
			while ($r = mysql_fetch_array($b)) {
				$opt_value[$r['blog_id']] = true;
			}
		}
	}
	else {
		$latest_num = 8;
		$opt_value  = array();
		foreach($option as $key => $val)$opt_value[$key]=true;
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:latest_num
	$attr = array(name => 'latest_num', value => $latest_num, size => 3);
	$SYS_FORM["input"][] = array(title => '新着記事の表示件数',
								 name  => 'latest_num',
								 body  => get_form("num", $attr));

	// text:disp_type
	$attr = array(name => 'target', value => $opt_value, option => $option);
	$SYS_FORM["input"][] = array(title => '対象ブログ',
								 name  => 'target',
								 body  => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '設定';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'ブログアーカイブ設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
