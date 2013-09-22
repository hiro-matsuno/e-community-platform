<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

//-----------------------------------------------------
// * メールの転送
//-----------------------------------------------------
function sys_fwdmail($param = array()) {
	$fwd_to = isset($param['to']) ? $param['to'] : null;
	$subject = isset($param['subject']) ? $param['subject'] : '無題';
	$body    = isset($param['body']) ? strip_tags($param['body']) : '';

	if (is_array($fwd_to) && count($fwd_to) > 0) {
		foreach ($fwd_to as $to) {
			sys_sendmail(array('to' => $to, 'subject' => $subject, 'body' => $body));
		}
	}
	else if (isset($fwd_to) && $fwd_to != '') {
		sys_sendmail(array('to' => $fwd_to, 'subject' => $subject, 'body' => $body));
	}
}

//-----------------------------------------------------
// * メールの送信
//-----------------------------------------------------
function sys_sendmail($param = array()) {
	$to      = isset($param['to']) ? $param['to'] : null;
	$subject = isset($param['subject']) ? $param['subject'] : '無題';
	$body    = isset($param['body']) ? $param['body'] : null;

	if (!$to || !$body) {
		return false;
	}

	if (!preg_match('/\n\n$/', $body)) {
		$body .= "\n";
	}
	$body .= "---\n". CONF_SITENAME. "\n". CONF_SITEURL. "\n";

	$mail_header  = "From: ". mb_encode_mimeheader($sitename, 'iso-2022-jp'). "<". CONF_EMAIL. ">\n";
	$mail_header .= "Reply-To: ". CONF_EMAIL. "\n";
	$mail_header .= "Errors-To: ".CONF_ERRMAIL."\n";
	$mail_header .= "X-Mailer: Comuni MTA\n";

	$body = mb_convert_encoding($body, "JIS", "UTF-8");

	mb_send_mail($to, $subject, $body, $mail_header);
}

//-----------------------------------------------------
// * メールの転送先を取得
//-----------------------------------------------------
function get_fwd_mail($uid = 0) {
	if ($uid == 0) {
		return array();
	}

	$f = mysql_full('select * from fwd_mail where uid = %s order by id',
					mysql_num($uid));

	$fwd = array();
	if ($f) {
		while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
			$fwd[] = $d['mail'];
		}
	}

	return $fwd;
}

/**
 * Description of Mail
 *
 * @author ikeda
 */
class Mail {
    //put your code here
}
?>
