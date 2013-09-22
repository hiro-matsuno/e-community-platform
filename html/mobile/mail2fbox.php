<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once 'Net/POP3.php';
require_once 'Mail/mimeDecode.php';

function mail2fbox() {
	$accounts = array();
	if(defined('CONF_POST_MAIL_POP3SERVER')){
	$accounts[] = array('server'=>CONF_POST_MAIL_POP3SERVER,
						'user'  =>CONF_POST_MAIL_USERNAME,
						'password'=>CONF_POST_MAIL_PASSWORD
						);
	}
	if(defined('CONF_POST_MAIL_OLD_POP3SERVER')){
	$accounts[] = array('server'=>CONF_POST_MAIL_OLD_POP3SERVER,
						'user'  =>CONF_POST_MAIL_OLD_USERNAME,
						'password'=>CONF_POST_MAIL_OLD_PASSWORD
						);
	}
	foreach($accounts as $account){
	
		$pop3 =& new Net_POP3;
		$pop3->connect($account['server'], 110);
		$c= $pop3->login($account['user'], $account['password'],"USER");
	
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
	
	//			echo "Subject: ". $structure->headers["subject"]. "\n";
	
				if (preg_match('/^[a-zA-Z0-9]+$/', $structure->headers["subject"], $match)) {
	//				echo strlen($match[0]). "\n";
					if (strlen($match[0]) == 24) {
						$post_id = $match[0];
	//					echo $post_id;
						$q = mysql_uniq('select * from mpost_mailq where post_id = %s',
										mysql_str($post_id));
	
						if (!$q) {
							continue;
						}
						$eid = $q["eid"];
						if (!$eid) {
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
											$fp = fopen($tmp_filepath, "w");
											$length = strlen($part->body);
											fwrite($fp, $part->body, $length);
											fclose($fp);
	
											$file_name = md5_file($tmp_filepath). "." . $type;
	
											$new_filepath   = CONF_BASEDIR. '/databox/guest/o/'. $file_name;
											$thumb_filepath = CONF_BASEDIR. '/databox/guest/t/'. $file_name;
	
											rename($tmp_filepath, $new_filepath);
	
											exec('"'.CONF_CONVERT.'"'. " -geometry 240\\>x320\\> $new_filepath $thumb_filepath ");
											chmod($thumb_filepath, 0666);
	
											$add_body .= add_atattch($file_name);
	
	//										echo "Upload -> ". $file_name. "\n";
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
	//					echo "spam?\n";
					}
				}
				$d = $pop3->deleteMsg($mailinfo['msg_id']);
			}
		}
		else {
	//		echo 'no mail';
		}
	
		$pop3->disconnect();
	}
	return;
}

function add_atattch($file_name) {
	return <<<___ATDIV___
<div>
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
		$body = $f["body"]. $add_body;

		$u = mysql_exec("update ${table} set body = %s where id = %s",
						mysql_str($body), mysql_num($eid));
	}

	$q = mysql_exec("delete from mpost_mailq where post_id = %s and eid = %s;",
					mysql_str($post_id), mysql_num($eid));
}

?>
