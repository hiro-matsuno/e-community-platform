<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../lib.php';

kick_guest();

$uid = myuid();
$gid = intval($_REQUEST['gid']);

if ($gid > 0) {
	$q = mysql_uniq('select * from page where gid = %s', mysql_num($gid));
	$sitename = $q['sitename'];
}
else {
	$q = mysql_uniq('select * from page where uid= %s', mysql_num($uid));
	$sitename = $q['sitename'];
}

if (isset($_REQUEST['email'])) {
	$email = $_REQUEST['email'];

	if (!preg_match('/[a-zA-Z0-9_.￥-]+@[a-zA-Z0-9_.￥-]+/', $email)) {
		show_error('メールアドレスを正しく入力してください。');
	}
	if ($email != $_REQUEST['email_c']) {
		show_error('再入力されたメールアドレスが一致しません。');
	}

	$i = mysql_uniq('select post_key from mobile_update_setting'.
					' where uid = %s and gid = %s and email = %s',
					mysql_num($uid), mysql_num($gid), mysql_str($email));
	if($i){

		$post_key = $i['post_key'];

	}else{

		$post_key = make_postkey(array(uid => $uid,
									   gid => $gid,
									   email => $email));

		$i = mysql_exec('insert into mobile_update_setting'.
						' (uid, gid, email, post_key)'.
						' values (%s, %s, %s, %s)',
						mysql_num($uid), mysql_num($gid),
						mysql_str($email), mysql_str($post_key));

	}

	send_postkey($post_key, $email);

	$url   = CONF_URLBASE. '/mobile/mbpost.php/'. $post_key;
	$qrimg = CONF_URLBASE. '/qrcode/index.php?d='. urlencode($url);

	$html = '携帯投稿用のURLを送信しました。<br><br>'.
			'届いたメールのURLにアクセス、もしくは下記のQRコードを読み取ってアクセスして下さい。<br>'.
			'URL: '. $url. '<br><br><br>'.
			'<div align="center"><img src="'. $qrimg. '"></div>';

	$data = array(title   => '携帯からの更新設定',
				  icon    => 'finish',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

$SYS_FORM["head"][] = '<strong>'. $sitename. '</strong> を携帯から更新するためのURLを発行します。';

$attr = array(name => 'uid', value => $uid);
$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));
$attr = array(name => 'gid', value => $gid);
$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

$ahtml = '<br>メールアドレスは間違えないようにお気を付けください。';
$attr = array(name => 'email', value => '', size => 64, ahtml => $ahtml);
$SYS_FORM["input"][] = array(title => '携帯のメールアドレス',
							 name  => 'email',
							 body  => get_form("text", $attr));

$attr = array(name => 'email_c', value => '', size => 64);
$SYS_FORM["input"][] = array(title => '携帯のメールアドレス (再入力)',
							 name  => 'email_c',
							 body  => get_form("text", $attr));

$SYS_FORM["action"] = 'setting.php';
$SYS_FORM["method"] = 'POST';

$SYS_FORM["submit"]  = 'URLの発行';
$SYS_FORM["cancel"]  = 'キャンセル';
$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

$html = create_form(array(eid => $eid, pid => $pid));

$data = array(title   => '携帯更新URLの発行',
			  icon    => 'write',
			  content => $html);

show_dialog2($data);

function send_postkey($post_id = null, $email = null) {
	global $uid, $gid, $sitename;

	mb_language("Japanese");
	mb_internal_encoding('UTF-8');

	$portalname = mb_convert_encoding(CONF_SITENAME, "JIS", "UTF-8");

	$mail_header  = "From: ". mb_encode_mimeheader($portalname). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n"; 
	$mail_header .= "X-Mailer: e-community platform 2.0 MTA\n";

	$url = CONF_URLBASE. '/mobile/mbpost.php/'. $post_id;

	$nickname = get_nickname($uid);

	$bodytpl = <<<___END___
${nickname} 様

お使いのﾒｰﾙｱﾄﾞﾚｽから「${sitename}」を更新するためには下記へｱｸｾｽして下さい。

%s

---
%s
%s
___END___;

	$body = sprintf($bodytpl, $url, CONF_SITENAME, CONF_SITEURL);

	$subject = $sitename. '更新専用アドレス';

	$body = mb_convert_encoding($body, "JIS", "UTF-8");
	mb_send_mail($email, $subject, $body, $mail_header);
}

function make_postkey($param = array()) {
	$uid    = $param["uid"];
	$gid    = $param["gid"];
	$email  = $param["email"];

	return md5(join('/', array($uid, $gid, $email, time())));
}

?>
