<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'resend':
		resend_passwd();
	break;
	default:
		print_form();
}

exit(0);

function resend_passwd() {
	$mail = isset($_POST['mail']) ? $_POST['mail'] : null;

	if (!$mail) {
		show_error('メールアドレスを入力して下さい。');
	}

	$q = mysql_uniq('select * from user where email = %s',
					mysql_str($mail));

	if (!$q) {
		show_error('メールアドレスが認識できません。');
	}

	$nickname = $q['handle'];
	$to       = $q['email'];
	$password = rand_str(8);

	$q = mysql_exec('update user set password = %s where email = %s',
				mysql_str(md5($password)), mysql_str($mail));
	if(!$q)show_error(mysql_error());
	
	$subject = CONF_SITENAME. 'パスワード再発行';

	$conf_urlbase = CONF_URLBASE;
	$bodytpl = <<<___END___
%s (%s) 様

  再発行フォームから申請がありましたので、パスワードを再発行します。
  以前のパスワードはご使用できませんのでご注意ください。

  メールアドレス  %s
  新しいパスワード  %s

 パスワードを変更する場合は、ログインページ
 $conf_urlbase/login.php
 にて新しいパスワードを入力してログイン後、パスワード変更ページ
 $conf_urlbase/passwd_change.php
 よりご希望のパスワードに変更してください。
___END___;

	$body = sprintf($bodytpl, $nickname, $to, $to, $password);

	sys_sendmail(array('to' => $to, 'subject' => $subject, 'body' => $body));

	$html  = '新しいパスワードを送信しました。ご確認下さい。';
	$html .= create_form_remove();

	$data = array('title'   => 'パスワードの再発行',
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
	$SYS_FORM['input'][] = array('title' => '登録時のメールアドレス',
								 'name'  => 'mail',
								 'body'  => get_form('text', $attr));

	$SYS_FORM["action"] = 'passwd_lost.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'パスワードを再発行';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'パスワードを忘れた場合',
				  'icon'    => 'notice',
				  'content' => $html);

	show_dialog($data);
}

?>
