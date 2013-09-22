<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../lib.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

session_start();

if (isset($_POST["mobile_mail"])) {
	$post_id = mobpost_issue_id(array(uid => $COMUNI["uid"],
								gid => $_SESSION["issue_gid"],
								eid => $_SESSION["issue_eid"],
								module => $_SESSION["issue_module"]));

	send_mpost_setting($post_id);

	$message = '携帯投稿用のURLを送信しました。<br><br>発行URL: '.
			   $url = CONF_URLBASE. '/mobile/post.php/'. $post_id;
}
else {
	$uid    = $COMUNI["uid"];
	$eid    = $_GET["eid"];
	$gid    = get_gid($eid);
	$module = $_GET["module"];

	$_SESSION["issue_eid"]    = $eid;
	$_SESSION["issue_gid"]    = $gid;
	$_SESSION["issue_module"] = $module;

	$message =<<<___FFF___
選択したブロックの携帯更新用URLを入力したメールアドレスへ発行します。<br>
<br>
<form action="/mobile/issue.php" method="POST">
携帯メールアドレス <input type="text" name="mobile_mail" id="mobile_mail" value="" size="36" style="border: solid 1px #999999; font-size: 1.0em;">
<br><br>
<div style="text-align: center; margin: 0 auto;">
<input type="submit" value="携帯投稿URLを発行する" style="font-size: 0.9em;">
</div>
</form>
___FFF___;
	;
}

$contents = <<<__MAP_FORM__
<div style="padding: 10px;">
<h3 style="padding: 3px 3px 20px 28px; background-image: url(/001_06.png); background-position: top left; background-repeat: no-repeat; font-size: 1.2em; border-bottom: solid 1px #5bace5;">携帯投稿URLの発行</h3>
</div>
<div style="padding: 18px; font-size: 0.9em;">
${message}
</div>

<br>
__MAP_FORM__;
	;

show_dialog($contents);

/*
 *
 */

function send_mpost_setting($post_id) {
	global $COMUNI;

	mb_language("Japanese");
	mb_internal_encoding('UTF-8');

	$mail_header  = "From: ". mb_encode_mimeheader(CONF_SITENAME). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n"; 
	$mail_header .= "X-Mailer: Comuni MTA\n";

	$url = CONF_URLBASE. '/mobile/post.php/'. $post_id;

	$bodytpl = <<<___END___
%s 様

%sのブログを更新するための携帯投稿URLを発行しました。
下記からアクセスして投稿することで更新が行えます。

%s

※上記のURLから更新可能な期限は1週間です。
---
%s
%s
___END___;

	$body = sprintf( $bodytpl ,
			 $COMUNI["nickname"] , $COMUNI["mpost_sitename"] ,
			 $url, CONF_SITENAME, CONF_SITEURL);

	$subject = '携帯投稿URL';

	mb_send_mail($_POST["mobile_mail"], $subject, $body, $mail_header);
}

function mobpost_issue_id($param = array()) {
	$uid    = $param["uid"];
	$gid    = $param["gid"];
	$eid    = $param["eid"];
	$module = $param["module"];

	$hash_id = md5(join('/', array($uid, $gid, $eid, $module)));

	$cq = mysql_uniq("select * from mpost where hash_id = %s",
					 mysql_str($hash_id));

	$new_id = rand_str(24);

	if ($cq) {
		$f = mysql_exec("update mpost set post_id = %s where hash_id = %s;",
						mysql_str($new_id), mysql_str($hash_id));
	}
	else {
		$f = mysql_exec("insert into mpost (post_id, eid, uid, gid, module, hash_id)".
						" values(%s, %s, %s, %s, %s, %s)",
						mysql_str($new_id), mysql_num($eid), mysql_num($uid), mysql_num($gid),
						mysql_str('blog'), mysql_str($hash_id));
	}
	if (!$f) {
		die("cannot issue post_id". mysql_error());
		return false;
	}

	return $new_id;
}

function get_info($eid = 0, $module = null) {
	return array(
		type  => 1,
		title => mb_convert_kana('メインブログ', 'k'),
	);
}

?>
