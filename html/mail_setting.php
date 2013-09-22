<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

if (!is_login()) {
	show_error('ログインして下さい。');
}

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'set':
		set_mail();
	default:
		print_form();
}

exit(0);

function set_mail() {
	global $SYS_FORM;

	$myuid = myuid();

	$fwd = isset($_POST['fwd']) ? $_POST['fwd']  : array();
	$add = isset($_POST['add']) ? $_POST['add']  : '';

	if ($add != '') {
		$fwd[] = htmlesc($add);
	}

	$ins_query = array();
	foreach ($fwd as $f => $v) {
		$ins_query[] = '('. $myuid. ', '. mysql_str($v). ')';
	}
	$d = mysql_exec('delete from fwd_mail where uid = %s',
					mysql_num($myuid));
	if (count($ins_query) > 0) {
		$i = mysql_exec('insert into fwd_mail (uid, mail) values %s',
						implode(',', $ins_query));
	}

	$html  = '設定を変更しました。';
	$html .= create_form_remove();

	$data = array('title'   => 'メール転送設定の変更',
				  'icon'    => 'finish',
				  'content' => $html);

	show_dialog($data);
}

function print_form() {
	global $SYS_FORM;

	$myuid = myuid();

	// FWD
	$fwd = array();
	$f = mysql_full('select * from fwd_mail'.
					' where uid = %s order by id',
					mysql_num($myuid));
	if ($f) {
		while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
			$fwd_value[$d['mail']] = true;
			$fwd[$d['mail']] = $d['mail'];
		}
	}
	$m = mysql_uniq('select * from user where id = %s',
					mysql_num($myuid));

	if (!isset($fwd[$m['email']])) {
		$fwd[$m['email']] = $m['email']. ' (登録メールアドレス)';
	}
	else {
		$fwd[$m['email']] .= ' (登録メールアドレス)';
	}

	$attr = array('name' => 'action', 'value' => 'set');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'fwd', value => $fwd_value, 'option' => $fwd, 'break_num' => 1);
	$SYS_FORM['input'][] = array('title' => '現在の転送先',
								 'name'  => 'fwd',
								 'body'  => get_form('checkbox', $attr));

	$attr = array('name' => 'add', 'value' => '', 'size' => 42);
	$SYS_FORM['input'][] = array('title' => 'メールアドレスの追加',
								 'name'  => 'add',
								 'body'  => get_form('text', $attr));

	$SYS_FORM["action"] = 'mail_setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'メール転送設定',
				  'icon'    => 'notice',
				  'content' => $html);

	show_dialog($data);
}

function mymail() {
	$q = mysql_uniq('select * from user where id = %s', mysql_num(myuid()));

	if ($q) {
		return $q['email'];
	}
	return;
}

?>
