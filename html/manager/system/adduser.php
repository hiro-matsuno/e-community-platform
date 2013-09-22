<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

define('SES_NAME', 'sys_adduser');

admin_check();

switch ($_REQUEST["action"]) {
	case 'regist':
		entry_data();
		break;
	case 'confirm':
		confirm_data();
		break;
	default:
		;
}

input_new();

function entry_data() {
	$ses_data = $_SESSION[SES_NAME];

	if (!$ses_data['nickname']) {
		$SYS_FORM['error']['sitename'] = 'ニックネームを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where handle = %s',
					mysql_str($ses_data['nickname']));
	if ($c) {
		$SYS_FORM['error']['nickname'] = '使用済のニックネームです。別のニックネームにしてください。';
		unset($ses_data['nickname']);
	}
	if (!$ses_data['email']) {
		$SYS_FORM['error']['email'] = 'メールアドレスを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where email = %s',
					mysql_str($ses_data['email']));
	if ($c) {
		$SYS_FORM['error']['email'] = 'すでに登録済のメールアドレスです。';
		unset($ses_data['email']);
	}
	if ( !isValidPasswd( $ses_data['passwd'] )) {
		$SYS_FORM['error']['passwd'] = 'パスワードは８文字以上の英数字を入力してください。';
	}

	$new_id = get_seqid('user');

	$f = mysql_exec("insert into user (id, handle, level, email, password, enable, initymd)".
					" values (%s, %s, %s, %s, %s, %s, %s);",
					mysql_num($new_id), mysql_str($ses_data['nickname']),10,
					mysql_str($ses_data['email']), mysql_str(md5($ses_data['passwd'])),
					mysql_num(1), mysql_current_timestamp());

	create_friend_user($new_id);
	create_friend_extra($new_id);

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "user_insert", array( $new_id ) );

	unset($_SESSION[SES_NAME]);

	$ref = '/manager/system/adduser.php';
	$html = 'ユーザーを追加しました。';
	$data = array(title   => 'ユーザー追加完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'ユーザー追加を続ける',)));

	show_input($data);

	exit(0);
}

function confirm_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$ses_data = array();
	$ses_data['nickname'] = htmlesc($_POST['nickname']);
	$ses_data['email']    = htmlesc($_POST['email']);
	$ses_data['passwd']   = htmlesc($_POST['passwd']);

	$_SESSION[SES_NAME] = $ses_data;

	if ($ses_data['nickname'] == '') {
		$SYS_FORM['error']['nickname'] = 'ニックネームを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where handle = %s',
					mysql_str($ses_data['nickname']));
	if ($c) {
		$SYS_FORM['error']['nickname'] = '使用済のニックネームです。別のニックネームにしてください。';
		unset($ses_data['nickname']);
	}
	if ($ses_data['email'] == '') {
		$SYS_FORM['error']['email'] = 'メールアドレスを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where email = %s',
					mysql_str($ses_data['email']));
	if ($c) {
		$SYS_FORM['error']['email'] = 'すでに登録済のメールアドレスです。';
		unset($ses_data['email']);
	}
	if ($ses_data['passwd'] == '') {
		$SYS_FORM['error']['passwd'] = 'パスワードを記入して下さい。';
	}
	if ($ses_data['passwd'] != htmlesc($_POST['passwd_c'])) {
		$SYS_FORM['error']['passwd'] = 'パスワードが再入力と一致しません。';
	}
	if (isset($SYS_FORM['error'])) {
		return;
	}

	// text:sitename
	$SYS_FORM["header"][] = '下記の内容をご確認下さい。';

	// hidden:regist
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

	// text:sitename
	$SYS_FORM["input"][] = array(title => 'ニックネーム',
								 name  => 'nickname',
								 body  => $ses_data['nickname']);
	$SYS_FORM["input"][] = array(title => 'メールアドレス',
								 name  => 'email',
								 body  => $ses_data['email']);
	$SYS_FORM["input"][] = array(title => 'パスワード',
								 name  => 'passwd',
								 body  => $ses_data['passwd']);

	$SYS_FORM["action"] = 'adduser.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'ユーザーを追加';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'ユーザーの追加',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_new() {
	global $SYS_FORM;

	if (isset($_SESSION[SES_NAME])) {
		$nickname = $_SESSION[SES_NAME]['nickname'];
		$email    = $_SESSION[SES_NAME]['email'];
		$passwd   = $_SESSION[SES_NAME]['passwd'];
	}
	else {
		$nickname = '';
		$email    = '';
		$passwd   = '';
	}

	$attr = array(name => 'action', value => 'confirm');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'nickname', value => $nickname, size => 32, ahtml => $ahtml);
	$SYS_FORM["input"][] = array(title => 'ニックネーム',
								 name  => 'nickname',
								 body  => get_form("text", $attr));

	$attr = array(name => 'email', value => $email, size => 48);
	$SYS_FORM["input"][] = array(title => 'メールアドレス',
								 name  => 'email',
								 body  => get_form("text", $attr));

	$attr = array(name => 'passwd', value => '', size => 24);
	$SYS_FORM["input"][] = array(title => 'パスワード',
								 name  => 'passwd',
								 body  => get_form("text", $attr));

	$attr = array(name => 'passwd_c', value => '', size => 24);
	$SYS_FORM["input"][] = array(title => 'パスワード (再入力)',
								 name  => 'passwd_c',
								 body  => get_form("text", $attr));

	$SYS_FORM["action"] = 'adduser.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '確認画面へ';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'ユーザーの追加',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
