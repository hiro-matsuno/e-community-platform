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
	case 'resend':
		change_passwd();
	default:
		print_form();
}

exit(0);

function change_passwd() {
	global $SYS_FORM;

	$new_passwd  = isset($_POST['new_passwd'])  ? $_POST['new_passwd']  : '';
	$conf_passwd = isset($_POST['conf_passwd']) ? $_POST['conf_passwd'] : '';

	if (!isValidPasswd($new_passwd)) {
		$SYS_FORM['error']['new_passwd'] = 'パスワードは８文字以上の英数字を入力してください。';
	}
	if ($new_passwd != $conf_passwd) {
		$SYS_FORM['error']['conf_passwd'] = '再入力されたパスワードが一致しません。';
	}

	if (isset($SYS_FORM['error'])) {
		return;
	}

	$q = mysql_exec('update user set password = %s where id = %s',
				mysql_str(md5($new_passwd)), mysql_num(myuid()));

	$html  = 'パスワードを変更しました。';
	$html .= create_form_remove();

	$data = array('title'   => 'ログインパスワードの変更',
				  'icon'    => 'finish',
				  'content' => $html);

	show_dialog($data);
}

function print_form() {
	global $SYS_FORM;

	$attr = array('name' => 'action', 'value' => 'resend');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	// text:sitename
	$attr = array('name' => 'mail', 'size' => 42);
	$SYS_FORM['input'][] = array('title' => '現在のメールアドレス',
								 'name'  => 'mail',
								 'body'  => mymail());

	$attr = array('name' => 'new_passwd', value => '', 'size' => 36);
	$SYS_FORM['input'][] = array('title' => '新しいパスワード',
								 'name'  => 'new_passwd',
								 'body'  => get_form('password', $attr));

	$attr = array('name' => 'conf_passwd', value => '', 'size' => 36);
	$SYS_FORM['input'][] = array('title' => '新しいパスワード (再入力)',
								 'name'  => 'conf_passwd',
								 'body'  => get_form('password', $attr));

	$SYS_FORM["action"] = 'passwd_change.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'パスワードを変更';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'ログインパスワードの変更',
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
