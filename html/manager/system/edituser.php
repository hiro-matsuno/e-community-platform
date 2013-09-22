<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

define('SES_NAME', 'sys_adduser');

get_user_level();

admin_check();

print_list();

function print_list() {
	global $user_level;

	$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : null;
	$level  = isset($_REQUEST['level']) ? $_REQUEST['level'] : 'all';

	if ($target) {
		if (!user_exists($target)) {
			show_error('ユーザーの指定が違います。');
		}

		$q = mysql_uniq('select id from user where id = %s and level = 100',
						mysql_num($target));
		if(($q or $level == 100) and !is_su())show_error('操作権限がありません');		

		$q = mysql_exec('update user set level = %s where id = %s',
						mysql_num($level), mysql_num($target));

		//	モジュールコールバックを呼び出し.
		ModuleManager::getInstance()
			->execCallbackFunctions( "user_update", array( $target ) );

		header('Location: '. CONF_URLBASE. '/manager/system/edituser.php?level='. $level);
		exit(0);
	}

	$addq = '';
	if ($level == 'all') {
		$addq = ' order by level';
	}
	else {
		$addq = sprintf(' where level = %s order by id', mysql_num(intval($level)));
	}

	$q = mysql_full('select id, handle, level from user'.$addq);
	$list = array();
	$list[] = array('uid'      => 'ユーザーID',
					'nickname' => 'ニックネーム',
					'level'    => 'ユーザーレベルの変更');

	$style = array('uid' => 'width: 90px;', 'level' => 'width: 200px;');

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$list[] = array('uid'      => $r['id'],
							'nickname' => $r['handle'],
							'level'    => select_tab('level_'. $r['id'], $r['level']));
		}
	}

	$html = '<div class="sub_menu" style="font-size: 0.8em; padding: 3px;">';
	foreach ($user_level as $l => $v) {
		$html .= '<a href="edituser.php?level='. $l. '">'. $v. '</a> | ';
	}
	$html .= '<a href="edituser.php">全て</a>';
	$html .= '</div>';

	$html .= create_list($list, $style);

	$data = array(title   => 'ユーザー一覧',
				  content => $html);

	show_input($data);

	exit(0);
}

function user_exists($uid = 0) {
	if (mysql_uniq('select * from user where id = %s', mysql_num($uid))) {
		return true;
	}
	return false;
}

function get_user_level() {
	global $user_level,$admin_name;
	$user_level = array();
	$q = mysql_full('select * from conf_user_level order by level desc');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			if($r['level'] == 100){
				$admin_name = $r['name'];
				if(!is_su())continue;
			}
			$user_level[$r['level']] = $r['name'];
		}
	}
}

function select_tab($name, $value) {
	global $user_level,$admin_name;

	if ($value == null) {
		$value = 10;
	}

	list($n, $t) = explode('_', $name);
	
	if($value == 100 and !is_su())$option = array(100 => $admin_name);
	else $option = $user_level;

	$attr = array('name' => $name, 'value' => $value, 'option' => $option,
				  'onChange' => 'location.href=\'edituser.php?target='. $t. '&level=\' + this.value;');
	return get_form('select', $attr);
}

?>
