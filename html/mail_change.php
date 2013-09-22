<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

if (!$act && !is_login()) {
	show_error('ログインして下さい。');
}

switch ($act) {
	case 'change':
		change_mail();
		break;
	case 'send':
		send_mail();
	default:
		print_form();
}

exit(0);

function change_mail(){
	global $SYS_FORM;

	$key = $_REQUEST['key'];

	if(strlen($key)<10)show_error('無効な入力です');

	$mail = base64_decode(substr($key,8));
	
	$q = mysql_uniq('select * from regist_temp where auth_code = %s',
					mysql_str($key));

	if(!$q){
		$q = mysql_uniq('select * from user where email = %s',mysql_str($mail));
		if($q)show_error('既にメールアドレスの変更は完了しています。');
		else show_error('無効な入力です');
	}
	
	mysql_exec('delete from regist_temp where auth_code = %s',
				mysql_str($key));
				
	mysql_exec('update user set email = %s where id = %s',
				mysql_str($mail),$q['uid']);
				
	$data = array('title'   => 'メールアドレス変更完了',
				  'icon'    => 'notice',
				  'content' => 'メールアドレスの変更が完了しました。');

	show_1page($data);
	exit(0);			
}
function send_mail() {
	global $SYS_FORM;

	$passwd  = isset($_POST['passwd'])  ? $_POST['passwd']  : '';
	$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
	$new_mail = isset($_POST['new_mail']) ? $_POST['new_mail'] : '';
	
	
	$q = mysql_uniq('select * from user where id = %s',mysql_num(myuid()));
	
	if (!$passwd || $passwd == '') {
		$SYS_FORM['error']['passwd'] = 'パスワードを入力して下さい。';
	}elseif($q['password'] != md5($passwd)){
		$SYS_FORM['error']['passwd'] = 'パスワードが間違っています';
	}
	
	if (!$mail|| $mail == '') {
		$SYS_FORM['error']['mail'] = '現在のメールアドレスを入力して下さい。';
	}elseif($q['email'] != $mail){
		$SYS_FORM['error']['mail'] = '登録されたメールアドレスと一致しません';
	}
	
	if (!$new_mail|| $new_mail == '') {
		$SYS_FORM['error']['new_mail'] = '新しいメールアドレスを入力して下さい。';
	}else{
		$p = mysql_uniq('select * from user where email = %s',mysql_str($new_mail));
		if($p)$SYS_FORM['error']['new_mail'] = 'すでに登録済みのメールアドレスです';
	}

	if (isset($SYS_FORM['error'])) {
		return;
	}

	$key = rand_str(8). base64_encode($new_mail);
	mysql_exec('insert into regist_temp (uid,auth_code) values (%s,%s)',
				mysql_num(myuid()),mysql_str($key));	
	
	$subject = CONF_SITENAME. 'メールアドレス変更';
	$url = CONF_URLBASE.'/mail_change.php?action=change&key='.urlencode($key);
	$body = <<<___END___
$q[nickname] ($q[email]) 様

  メールアドレス変更の申請がありました。

  旧メールアドレス  $mail
  新メールアドレス  $new_mail

 メールアドレスの変更を完了するために下記のページへwebブラウザにてアクセスしてください。

 $url

___END___;
	
	sys_sendmail(array('to' => $new_mail.",".$mail, 'subject' => $subject, 'body' => $body));
	
	$html  = <<<__HTML__
確認メールを送信しました。<br>
新しいメールアドレスに送信されたメールのとおりに操作してください。<br>
操作が完了した時点でメールアドレスの変更が有効になります。<br>
それまでは現在のメールアドレスが使用されます。<br>
__HTML__;
	$html .= create_form_remove();

	$data = array('title'   => 'メールアドレスの変更',
				  'icon'    => 'finish',
				  'content' => $html);

	show_dialog($data);
}

function print_form() {
	global $SYS_FORM;

	$attr = array('name' => 'action', 'value' => 'send');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	// text:sitename
	$attr = array('name' => 'mail', 'size' => 42);
	$SYS_FORM['input'][] = array('title' => '現在のメールアドレス',
								 'name'  => 'mail',
								 'body'  => get_form('mail',$attr));

	$attr = array('name' => 'passwd', value => '', 'size' => 36);
	$SYS_FORM['input'][] = array('title' => 'パスワード',
								 'name'  => 'passwd',
								 'body'  => get_form('password', $attr));

	$attr = array('name' => 'new_mail', 'size' => 42);
	$SYS_FORM['input'][] = array('title' => '新しいメールアドレス',
								 'name'  => 'new_mail',
								 'body'  => get_form('mail',$attr));
	
	$SYS_FORM["action"] = 'mail_change.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'メールアドレスを変更';
	$SYS_FORM["cancel"] = '閉じる';
	$SYS_FORM["onCancel"] = 'parent.tb_remove();';

//	$html = 'form';
	$html = create_form();

	$data = array('title'   => 'メールアドレスの変更',
				  'icon'    => 'notice',
				  'content' => $html);

	show_dialog($data);
}

?>
