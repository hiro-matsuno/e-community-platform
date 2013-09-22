<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data();
	default:
		input_data();
}

/* 登録*/
function regist_data() {
	global $SYS_FORM;

	$id  = 0;
	$css = $_REQUEST['css'];

	$u = mysql_exec('update common_css set css = %s where id = %s',
		mysql_str($css), mysql_num($id));

	if (!$u) {
		show_error('更新失敗。');
	}

	$ref = '/manager/css/input.php';

	$html = '編集完了しました。';
	$data = array(title   => '共通CSS編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => '共通CSS編集に戻る',)));

	show_input($data);

	exit(0);
}

function input_data() {
	global $SYS_FORM;

	$id = 0;

	$q = mysql_uniq('select * from common_css where id = %s',
					mysql_num($id));

	if (!$q) {
		show_error('CSSが見当たりません。');
	}

	$css = $q['css'];

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'css', value => $css, height =>'500px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'スタイルシート',
								 name  => 'css',
								 body  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => '共通CSSの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}


?>
