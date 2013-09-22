<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

ini_set("mbstring.internal_encoding","UTF-8");

require_once dirname(__FILE__). '/../lib_cron.php';

require_once 'Net/POP3.php';
require_once 'Mail/mimeDecode.php';

mail2fbox();

function mail2fbox() {
	$pop3 =& new Net_POP3;
	if (!$pop3->connect(CONF_POST_MAIL_POP3SERVER, 110)) {
		echo 'cannot connect...'. CONF_POST_MAIL_POP3SERVER. "<br>\n";
	}
	if (!$pop3->login(CONF_POST_MAIL_USERNAME, CONF_POST_MAIL_PASSWORD,"USER")) {
		echo "cannot login...<br>\n";
	}

	$maillist = $pop3->getListing();
	if (is_array($maillist) && count($maillist) > 0) {
		foreach ($maillist as $mailinfo) {
			$mail = $pop3->getMsg($mailinfo['msg_id']);

			$params = array();
			$params['include_bodies'] = true;
			$params['decode_bodies'] = true;
			$params['decode_headers'] = true;

			$decoder = new Mail_mimeDecode($mail);
			$structure = $decoder->decode($params);

			echo "Subject: ". $structure->headers["subject"]. "<br>\n";

			if (preg_match('/^[a-zA-Z0-9]+$/', $structure->headers["subject"], $match)) {
//				echo strlen($match[0]). "<br>\n";
				if (strlen($match[0]) == 24) {
					$post_id = $match[0];
					echo $post_id. "<br>\n";
					$q = mysql_uniq('select * from mpost_mailq where post_id = %s',
									mysql_str($post_id));

					if (!$q) {
						echo "cannot find post_id from mpost_mailq<br>\n";
						continue;
					}
					$eid = $q["eid"];
					if (!$eid) {
						echo "cannot find eid<br>\n";
						continue;
					}

					switch(strtolower($structure->ctype_primary)){
						case "text":
							$body = $structure->body;
							break;
						case "multipart":
							$add_body = '';
							foreach($structure->parts as $part){
								switch(strtolower($part->ctype_primary)){
									case "text":
										$body = $part->body;
										break;
									case "image":
										$type = strtolower($part->ctype_secondary);
										if (!preg_match('/jpeg|jpg|gif|png/i', $type, $match)) {
											continue;
										}
										switch ($match[0]) {
											case 'jpeg': 
												$type = 'jpg';
												break;
											default:
												;
										}
										$tmp_filepath = CONF_BASEDIR. '/databox/guest/'. md5(uniqid(rand(), true)).
														"." . $type;
										if (!($fp = fopen($tmp_filepath, "w"))) {
											echo "oops!! -> ". $tmp_filepath. "<br>\n";
										}
										$length = strlen($part->body);
										fwrite($fp, $part->body, $length);
										fclose($fp);

										$file_name = md5_file($tmp_filepath). "." . $type;

										$new_filepath   = CONF_BASEDIR. '/databox/guest/o/'. $file_name;
										$thumb_filepath = CONF_BASEDIR. '/databox/guest/t/'. $file_name;

										rename($tmp_filepath, $new_filepath);

										exec('"'.CONF_CONVERT.'"'." -geometry 240\\>x320\\> $new_filepath $thumb_filepath ");
										chmod($thumb_filepath, 0666);

										$add_body .= add_atattch($file_name);

										echo "Upload -> ". $file_name. "<br>\n";
										break;
								}
							}
							add_entry($post_id, $eid, $add_body);
							break;
						default:
							$body = "";
					}
				}
				else {
					echo "...spam?<br>\n";
				}
			}
			$d = $pop3->deleteMsg($mailinfo['msg_id']);
		}
	}
	else {
		echo 'no mail';
	}

	$pop3->disconnect();

	return;
}

function add_atattch($file_name) {
	return <<<___ATDIV___
<div style="float: left;">
<a href="/databox/guest/o/${file_name}"><img src="/databox/guest/t/${file_name}" border="0"></a>
</div>
___ATDIV___
	;
}

function add_entry($post_id, $eid, $add_body) {
	$q = mysql_uniq("select * from mpost where post_id = %s", mysql_str($post_id));

	if (!$q["module"]) { return; }

	$table = $q["module"]. '_data';

	$f = mysql_uniq("select * from ${table} where id = %s", mysql_num($eid));

	if ($f) {
		$body = $f["body"]. $add_body. '<br clear="all">';

		$u = mysql_exec("update ${table} set body = %s where id = %s",
						mysql_str($body), mysql_num($eid));
	}

	$q = mysql_exec("delete from mpost_mailq where post_id = %s and eid = %s;",
					mysql_str($post_id), mysql_num($eid));
}

?>
