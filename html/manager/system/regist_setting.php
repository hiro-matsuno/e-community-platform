<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/../../regist_lib.php';

define('SES_NAME', 'REGIST_SETTING');

admin_check();

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($act) {
	case 'regist':
		entry_data();
		break;
	default:
		input_new();
}

/* entry_data */
function entry_data() {
	$gid          = 1; // const
	$use_confirm = isset($_POST['use_confirm']) ? intval($_POST['use_confirm']) : 0;
	$app_level   = isset($_POST['app_level']) ? intval($_POST['app_level']) : 100;

	mysql_exec('lock table join_req_info, prof_add_req, regist_setting write');
	$d = mysql_exec('delete from regist_setting where id = %s',
					mysql_num($gid));
	$i = mysql_exec('insert regist_setting (id, use_confirm, app_level)'.
					' values (%s, %s, %s)',
					mysql_num($gid), mysql_num($use_confirm), mysql_num($app_level));

	regist_form_regist();

	$ref = '/manager/system/regist_setting.php';
	$html = 'ユーザーの登録方法を設定しました。';
	$data = array(title   => 'ユーザー登録設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => '設定画面に戻る')));

	mysql_exec('unlock tables');
	show_input($data);

	exit(0);
}

/* input_new */
function input_new() {
	global $SYS_FORM,$COMUNI_HEAD_CSSRAW,$COMUNI_HEAD_JSRAW,$JQUERY;

	$gid          = 1; // const
	$use_confirm = 0;
	$app_level   = 100;

	$q = mysql_uniq('select * from regist_setting where id = %s', mysql_num($gid));

	if ($q) {
		$use_confirm = $q['use_confirm'];
		$app_level   = $q['app_level'];
	}
	
	//登録済みフォームを読み出し
	$items = regist_data_get_reqdata();
	$default_items = array(array('title' => 'ニックネーム','req' => true),
							array('title' => 'メールアドレス','req' => true),
							array('title' => 'パスワード','req' => true),
							array('title' => 'パスワード(再入力)','req' => true));

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$option = array(0 => '登録不可', 1 => '承認制', 2 => '誰でも自由に登録');
	$attr = array(name => 'use_confirm', value => $use_confirm, option => $option);
	$SYS_FORM["input"][] = array(title => 'ユーザー登録',
								 name  => 'use_confirm',
								 body  => get_form("radio", $attr));

	$option = array();
	$q = mysql_full('select * from conf_user_level where is_admin > 0 order by level desc');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$option[$r['level']] = $r['name'];
		}
	}
	$attr = array(name => 'app_level', value => $app_level, option => $option);
	$SYS_FORM["input"][] = array(title => '承認メールを送る管理者のレベル',
								 name  => 'app_level',
								 body  => get_form("select", $attr));
								 
//登録項目の並べ替え・削除・必須項目の選択
	$SYS_FORM["input"][] = array(title => '追加登録事項のレイアウト',
								name => 'items',
								body => regist_form_create_html($items,$default_items));

	$SYS_FORM["action"] = 'regist_setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'ユーザー登録設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}


?>
