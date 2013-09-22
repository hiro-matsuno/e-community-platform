<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require dirname(__FILE__). '/regist_lib.php';

if (get_regist_level() == 0) {
	show_error('現在登録は受け付けておりません。');
}

session_start();

$act = $_POST[action];
$a   = $_GET[a];

if($act and !$_COOKIE['PHPSESSID'])
	show_error('お使いのブラウザではcookieがOFFになっているようです。<br>cookieをONに設定してしてやりなおしてください。');

$add_items = regist_data_get_reqs();

if (isset($a)) {
	if (get_regist_level() == 0) {
		show_error('現在登録できません。');
	}

	$u = mysql_uniq("select * from regist_temp where auth_code = %s;",
						 mysql_str($a));

	if (!$u) {
		header("Location: ". CONF_SITEURL);
		exit(0);
	}

//	echo 'temp register : '. $u[uid]; exit(0);

	$f = mysql_exec("update user set enable = 1 where id = %s;",
					mysql_num($u["uid"]));

	$d = mysql_exec("delete from regist_temp where auth_code = %s;", mysql_str($a));

/* フレンドリスト作成 */
	$u2 = mysql_uniq("select * from user where id = %s;", mysql_num($u["uid"]));

	$COMUNI['nickname'] = $u2["handle"];

	send_ok_mail(array('name' => $u2["handle"], 'mail' => $u2['email']));

	create_friend_user($u["uid"]);
	create_friend_extra($u["uid"]);

	$content =<<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #ffffff;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
</style>
<div style="margin: 10px auto; text-align: center; width: 70%;">
正式に登録完了しました。<br><br>
以下からログインしてください
<form action="login.php" method="POST">
<input type="hidden" name="action" value="login">
<input type="hidden" name="type" value="${type}">
<input type="hidden" name="ref" value="${ref}">
<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<th>メールアドレス</th>
<td><input type="text" name="email" class="input_text" size="40" style="ime-mode:disabled"></td>
</tr>
<tr>
<th>パスワード</th>
<td><input type="password" name="password" class="input_text" size="30" style="ime-mode:disabled"></td>
</tr>
</table>
<br>
<input type="submit" value="ログイン" class="input_submit">
</form>
</div>
__HTML__;

}
else if ($act == 'regist'  and !isset($_POST['rewrite'])) {
	if(!$_SESSION['email'] or !$_SESSION['nickname'])show_error('エラーが発生しました。');
	$q = mysql_uniq('select * from user where email = %s',
					mysql_str($_SESSION['email']));

	if ($q) {
		show_error('既に登録済です。');
	}

	$new_id = get_seqid('user');

	$f = mysql_exec("insert into user (id, handle, level, email, password, enable, initymd)".
					" values (%s, %s, %s, %s, %s, %s, %s);",
					mysql_num($new_id), mysql_str($_SESSION[nickname]),10,
					mysql_str($_SESSION[email]), mysql_str(md5($_SESSION[password])),
					mysql_num(0), mysql_current_timestamp());

	if (!$f) {
		die(mysql_error());
	}

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "user_insert", array( $new_id ) );

	do{
		$uniq_id = rand_str(24);
		$q = mysql_uniq('select uid from regist_temp where auth_code = %s',
						mysql_num($uniq_id)); 
	}while($q);

	$f2 = mysql_exec("insert into regist_temp (uid, auth_code)".
					" values (%s, %s);",
					mysql_num($new_id), mysql_str($uniq_id));

	if (!$f2) {
		die(mysql_error());
	}
/*
	$fullname      = htmlesc($_SESSION['fullname']);
	$fullname_kana = htmlesc($_SESSION['fullname_kana']);
	$zip           = htmlesc($_SESSION['zip']);
	$address       = htmlesc($_SESSION['address']);
	$tel           = htmlesc($_SESSION['tel']);
	$new_profile_id = get_seqid();

	$u = mysql_exec('insert into profile_data'.
					' (id, uid, gid, name, name_kana, zip, address, tel)'.
					' values'.
					' (%s, %s, %s, %s, %s, %s, %s, %s)',
					mysql_num($new_profile_id),mysql_num($new_id),mysql_num(0), 
					mysql_str($fullname), mysql_str($fullname_kana), 
					mysql_str($zip), mysql_str($address), mysql_str($tel));
*/				
	foreach($_SESSION['add_form'] as $req_id => $value){
		if(!$value)continue;
		if(is_array($value))$value = implode("\n",$value);
		else $value = str_replace("\r","\n",str_replace("\r\n","\n",$value));
		mysql_exec('insert into prof_add_data (uid,req_id,data) values (%s,%s,%s)',
					mysql_num($new_id),mysql_num($req_id),mysql_str($value));
	}

	$regist_level = get_regist_level();

	switch ($regist_level) {
		case 0:
			show_error('現在登録は受け付けておりません。');
		break;
		case 1:
			$approver = get_approver();
			if (count($approver) > 0) {
				foreach ($approver as $handle => $mail) {
					send_app_mail(array('name' => $handle, 'mail' => $mail, 'reg_key' => $uniq_id));
				}
			}

			$message = '登録申請を受付ました。正式登録時にはメールでお知らせいたします。';
		break;
		case 2:
		default:
			send_regist_mail(array('reg_key' => $uniq_id));
			$message = '登録確認メールを送りました。届いたメールに従って手続きを続行してください。';
	}

	$data = array('title'   => '仮登録完了',
				  'icon'    => 'notice',
				  'content' => $message);

	show_1page($data);

	exit(0);
}else{
	$error = '';
	if ($act == 'confirm') {
		$_SESSION[nickname] = $_POST[nickname];
		$_SESSION[email]    = mb_convert_kana( $_POST[email] , 'a' );
		$_SESSION[password] = $_POST[password];
		$_SESSION['fullname']      = $_POST['fullname'];
		$_SESSION['fullname_kana'] = $_POST['fullname_kana'];
		$_SESSION['zip'] = $_POST['zip'];
		$_SESSION['address'] = $_POST['address'];
		$_SESSION['tel']     = $_POST['tel'];
		$_SESSION['add_form'] = $_POST['add_form'];
	
		$ac = isset($_REQUEST['ac']) ? intval($_REQUEST['ac']) : 0;
	
		if ($ac == 0) {
			$error .= '利用規約に同意してください。<br>';
		}
	
		if (!$_SESSION['nickname'] || $_SESSION['nickname'] == '') {
			$error .= 'ニックネームを入力して下さい。<br>';
		}
		if (!$_SESSION['email'] || $_SESSION['email'] == '') {
			$error .= 'メールアドレスを入力して下さい。<br>';
		}
/*
		if (preg_match('/^0/', $_SESSION['password'])) {
			$error .= '先頭にゼロは付けられません。<br>';
		}
*/
		if ( !isValidPasswd( $_SESSION['password'] ) ) {
			$error .= 'パスワードは８文字以上の英数字を入力してください。<br>';
		}
		if (strlen($_SESSION['password']) < 4 || strlen($_SESSION['password']) > 16 ) {
			$error .= 'パスワードは半角英数字4-16文字で入力して下さい。<br>';
		}
		if ($_SESSION['password'] != $_POST['password_c']) {
			$error .= '再入力されたパスワードが一致しません。<br>';
		}
		/*
		if (!$_SESSION['fullname'] || $_SESSION['fullname'] == '') {
			show_error('お名前(漢字)を入力して下さい。');
		}
		if (!$_SESSION['fullname_kana'] || $_SESSION['fullname_kana'] == '') {
			show_error('お名前(カタカナ)を入力して下さい。');
		}
		if (!$_SESSION['zip'] || $_SESSION['zip'] == '') {
			show_error('郵便番号を入力して下さい。');
		}
		if (!$_SESSION['address'] || $_SESSION['address'] == '') {
			show_error('ご住所を入力して下さい。');
		}
		if (!$_SESSION['tel'] || $_SESSION['tel'] == '') {
			show_error('電話番号を入力して下さい。');
		}
		*/
	
		$q = mysql_uniq('select * from user where email = %s',
						mysql_str($_SESSION['email']));
	
		if ($q) {
			$error .= "'$_SESSION[email]'は既に登録済のメールアドレスです。<br>";
		}
	
		$q = mysql_uniq('select * from user where handle = %s',
						mysql_str($_SESSION['nickname']));
	
		if ($q) {
			$error .= "'$_SESSION[nickname]'は既に登録済のニックネームです。<br>";
		}
	
		
		$error .= regist_chk_req($add_items);
	}

	if($act == 'confirm' and !$error){
			$add_form = regist_form($add_items,true);
		
			$content =<<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #ffffff;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
</style>
<div style="margin: 10px auto; text-align: center; width: 65%;">
登録情報の確認<br><br>
<form action="regist.php" method="POST">
<input type="hidden" name="action" value="regist">
<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<th>ニックネーム</th>
<td><input type="text" name="nickname" class="input_text" size="30" value="$_SESSION[nickname]" readonly></td>
</tr>
<tr>
<th>メールアドレス</th>
<td><input type="text" name="email" class="input_text" size="40" value="$_SESSION[email]" readonly></td>
</tr>
<tr>
<th>パスワード</th>
<td>(表示されません)</td>
</tr>
<!--
<tr>
<th>お名前(漢字)</th>
<td><input type="text" name="fullname" class="input_text" size="40" value="${_SESSION['fullname']}" readonly></td>
</tr>
<tr>
<th>お名前(カタカナ)</th>
<td><input type="text" name="fullname_kana" class="input_text" size="40" value="${_SESSION['fullname_kana']}" readonly></td>
</tr>
<tr>
<th>住所</th>
<td>〒<input type="text" name="zip" class="input_text" size="15" value="${_SESSION['zip']}" readonly><br>
    <input type="text" name="address" class="input_text" size="40" value="${_SESSION['address']}" readonly></td>
</tr>
<tr>
<th>電話番号</th>
<td><input type="text" name="tel" class="input_text" size="40" value="${_SESSION['tel']}" readonly></td>
</tr>
-->
$add_form
</table>
<br>
<input type="submit" value="登録申請" class="input_submit">
<input type="submit" name='rewrite' value="戻る" class="input_submit">
</form>
</div>
__HTML__;

	}else {
	$nickname = $_POST[nickname];
	$email    = $_POST[email];

	$href = thickbox_href('/agreement.php');

	$req_sym = '<span class="required">*</span>';

	$add_form = regist_form($add_items);

	$error_str = "";
	foreach(split('<br>',$error) as $e){
		if(trim($e))
			$error_str.="・$e<br>\n";
	}

	$content =<<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #cccccc;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
span.required {
	color: #FF0000;
}
</style>
<div style="margin: 10px auto; text-align: center; width: 65%;">
<div style='text-align:left'>
下記のフォームに記入の上、確認画面へ進んでください。<br><br>
「${req_sym}」印は必須項目です<br>
<div style="color:red;">
$error_str
</div>
</div>
<form action="regist.php" method="POST">
<input type="hidden" name="action" value="confirm">
<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<th>ニックネーム$req_sym</th>
<td><input type="text" name="nickname" class="input_text" size="30" value='$nickname'></td>
</tr>
<tr>
<th>メールアドレス$req_sym</th>
<td><input type="text" name="email" class="input_text" size="40" value='$email'  style="ime-mode:disabled"></td>
</tr>
<tr>
<th>パスワード$req_sym</th>
<td><input type="password" name="password" class="input_text" size="30"></td>
</tr>
<tr>
<th>パスワード(再入力)$req_sym</th>
<td><input type="password" name="password_c" class="input_text" size="30"></td>
</tr>
<!--
<tr>
<th>お名前(漢字)$req_sym</th>
<td><input type="text" name="fullname" class="input_text" size="32"></td>
</tr>
<tr>
<th>お名前(カタカナ)$req_sym</th>
<td><input type="text" name="fullname_kana" class="input_text" size="32"></td>
</tr>
<tr>
<th>住所$req_sym</th>
<td>〒<input type="text" name="zip" class="input_text" size="9"><br><input type="text" name="address" class="input_text" size="40"></td>
</tr>
<tr>
<th>電話番号$req_sym</th>
<td><input type="text" name="tel" class="input_text" size="15"></td>
</tr>
-->
$add_form
<tr>
<td>&nbsp;</td>
<td><input type="checkbox" id="accept" name="ac" value="1"> <label for="accept"><a href="${href}" class="thickbox">利用規約</a><span style="font-size: 0.9em;">に同意する。</span></label></td>
</tr>
</table>
<input type="submit" value="確認画面へ進む" class="input_submit">
</form>
</div>
<hr>
<div style="margin: 10px auto; text-align: center; width: 95%;">
既にユーザー登録が済んでいる方は下記よりログインください
<form action="login.php" method="POST">
<input type="hidden" name="action" value="login">
<input type="hidden" name="type" value="">
<input type="hidden" name="ref" value="/">
<table class="form_table" style="margin: 0 auto; text-align: center; width: 70%;">
<tr>
<th>メールアドレス</th>
<td><input type="text" name="email" class="input_text" size="40"></td>
</tr>
<tr>
<th>パスワード</th>
<td><input type="password" name="password" class="input_text" size="30"></td>
</tr>
</table>
<br>
<input type="submit" value="ログイン" class="input_submit">
</form>
<br>
</div>

__HTML__;

	}
}

$data = array(
			space_1 => array(
							array(id => 12334, title => '新規登録', content => $content)
					)
		);

$COMUNI["columns"] = 1;

show_page(0, $data);

exit(0);

function get_regist_level() {
	$id = 1;

	$q = mysql_uniq('select * from regist_setting where id = %s', mysql_num($id));
	if (!$q) {
		return 0;
	}
	if (isset($q['use_confirm']) && $q['use_confirm'] > 0) {
		return $q['use_confirm'];
	}
	return 0;
}

function get_approver() {
	$id = 1;
	$q = mysql_uniq('select * from regist_setting where id = %s', mysql_num($id));

	if ($q) {
		$app_level = $q['app_level'];
	}
	else {
		return array();
	}

	$q = mysql_full('select * from user where level = %s',
					mysql_num($app_level));
					
	$approver = array();
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$approver[$r['handle']] = $r['email'];
		}
	}
	return $approver;
}

function send_app_mail($param = array()) {
	$sitename = mb_convert_encoding(CONF_SITENAME, "JIS", "UTF-8");

	$mail_header  = "From: ". mb_encode_mimeheader($sitename). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n"; 
	$mail_header .= "X-Mailer: Comuni MTA\n";

	$url = CONF_URLBASE. '/regist.php?a='. $param["reg_key"];

	$bodytpl = <<<___END___
%s (%s) 様

　%s (%s) さんから『%s』への会員登録申請がありました。
　このユーザーの申請を受理する場合は、次のアドレスにアクセスしてください。

%s

※仮登録から24時間を越えますと、自動で拒否となります。

%s
%s
___END___;

	$body = sprintf( $bodytpl ,
		 $param["name"], $param["mail"], $_SESSION["nickname"], $_SESSION["email"],
		 CONF_SITENAME , $url, CONF_SITENAME, CONF_SITEURL);

	$subject = CONF_SITENAME. 'への登録申請 ['. $_SESSION["nickname"]. ']';

	$body = mb_convert_encoding($body, "JIS", "UTF-8");

	mb_send_mail($param["mail"], $subject, $body, $mail_header);
}

function send_regist_mail($param = array()) {
	$sitename = mb_convert_encoding(CONF_SITENAME, "JIS", "UTF-8");

	$mail_header  = "From: ". mb_encode_mimeheader($sitename). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n"; 
	$mail_header .= "X-Mailer: Comuni MTA\n";

	$url = CONF_URLBASE. '/regist.php?a='. $param["reg_key"];

	$bodytpl = <<<___END___
%s (%s) 様

　この度は、『%s』へ会員登録して頂き、誠にありがとうございました！
　以下のアドレスにアクセスして会員登録の手続きを完了した上で、マイページを作成してください

%s

※仮登録から24時間を越えますと、本登録ができませんのでご注意ください。

  なお、24時間を越えて、上記アドレスでの最終手続きに失敗する場合は、
  %s
  からもう一度仮登録を行ってください。

%s
%s
___END___;

	$body = sprintf($bodytpl,
					$_SESSION["nickname"] , $_SESSION["email"] ,
					CONF_SITENAME , $url, CONF_URLBASE. '/regist.php',
					CONF_SITENAME, CONF_SITEURL);

	$subject = CONF_SITENAME. 'への登録確認メール';

	$body = mb_convert_encoding($body, "JIS", "UTF-8");

	mb_send_mail($_SESSION['email'], $subject, $body, $mail_header);
}

function send_ok_mail($param = array()) {
	$sitename = mb_convert_encoding(CONF_SITENAME, "JIS", "UTF-8");

	$mail_header  = "From: ". mb_encode_mimeheader($sitename). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n"; 
	$mail_header .= "X-Mailer: Comuni MTA\n";

	$bodytpl = <<<___END___
%s (%s) 様

　この度は、『%s』へ会員登録して頂き、誠にありがとうございました！
　このたび、正式にユーザー登録が完了致しましたのでお知らせいたします。

　以下のアドレスにアクセスして、マイページを作成してください。
  %s

  今後とも %s をよろしくお願いします。

%s
%s
___END___;

	$body = sprintf($bodytpl,
					$param["name"] , $param["mail"] ,
					CONF_SITENAME, CONF_SITEURL, CONF_SITENAME, CONF_SITENAME, CONF_SITEURL);

	$subject = CONF_SITENAME. 'への登録が完了しました。';

	$body = mb_convert_encoding($body, "JIS", "UTF-8");

	mb_send_mail($param['mail'], $subject, $body, $mail_header);
}

?>
