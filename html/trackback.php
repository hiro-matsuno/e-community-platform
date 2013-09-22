<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require_once 'Service/Trackback.php';
ini_set('display_errors', 0);

$eid = null;

if (preg_match('/([0-9]+)$/', $_SERVER['REQUEST_URI'], $m)) {
	$eid = $m[1];
}

// トラックバック情報
$tbdata = array('id'        => 1,
				'host'      => $_SERVER['SERVER_ADDR'],
				'title'     => 'トラックバックテスト',
				'excerpt'   => 'aaaaaaaaaaaaaaaaaa',
				'url'       => CONF_SITEURL,
				'blog_name' => CONF_SITENAME,
				'trackback_url' => CONF_URLBASE. $_SERVER['REQUEST_URI']);

// Services_Trackbackオブジェクトの作成
$trackback = Services_Trackback::create($tbdata);
 
// 送信されたトラックバック情報を受信
$result = $trackback->receive();

if (!is_trackback($eid)) {
    echo $trackback->getResponseError('No Data.', 1);
	exit(0);
}

if (PEAR::isError($result)) {
    echo $trackback->getResponseError($result->getMessage(), 1);
} else {
    // 送信された各情報を取得する
    $values = array(
		'id'        => $eid,
        'host'      => $trackback->get('host'),
        'title'     => $trackback->get('title'),
        'excerpt'   => clip_str(strip_tags($trackback->get('excerpt')), 300),
        'url'       => $trackback->get('url'),
        'blog_name' => $trackback->get('blog_name'),
    );

	if (!check_blacklist($eid, $values['host'])) {
		echo $trackback->getResponseError('access denied.', 1);
		exit(0);
	}
	if (!check_ngword($eid, $trackback->get('excerpt')) || !check_ngword($eid, $trackback->get('title'))) {
		echo $trackback->getResponseError('NG Word.', 1);
		exit(0);
	}

	$i = mysql_exec('insert into trackback(eid, title, excerpt, url, blog_name, host, date)'.
					' values(%s, %s, %s, %s, %s, %s, %s)',
					mysql_num($eid),
					mysql_str($values['title']), mysql_str($values['excerpt']),
					mysql_str($values['url']), mysql_str($values['blog_name']),
					mysql_str($values['host']), mysql_current_timestamp());
/*
	$s = sprintf('insert into trackback(eid, title, excerpt, url, blog_name, date)'.
					' values(%s, %s, %s, %s, %s, %s)',
					mysql_num($eid),
					mysql_str($values['title']), mysql_str($values['excerpt']),
					mysql_str($values['url']), mysql_str($values['blog_name']),
					mysql_current_timestamp());

*/

	send_noti_trackback($eid);

    echo $trackback->getResponseSuccess();
}

function send_noti_trackback($id = 0) {
//	write_syslog('comment notice on '. $id);

	$site_id = get_site_id($id);

//	write_syslog('comment notice on '. $site_id);
	if (!$site_id || $site_id == 0) {
		return;
	}

	$q = mysql_full('select * from mail_noti_ct as m'.
					' inner join user as u on m.uid = u.id'.
					' where m.type in (1, 3) and m.eid = %s',
					mysql_num($site_id));

	$sitename = get_site_name($site_id);
	$url      = CONF_URLBASE. home_url($id);

	$subject = CONF_SITENAME. 'トラックバック通知';

	$body    = <<<_BODY_
${sitename}にトラックバックがありました。
${url}
_BODY_;

	$udata = array();
	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$udata[$res['uid']] = $res['email'];
		}
		foreach ($udata as $uid => $email) {
//			if (!check_pmt($eid, $uid)) {
//				continue;
//			}
			$fwd = get_fwd_mail($uid);
			if (isset($fwd) && count($fwd) > 0) {
				$to = $fwd;
			}
			else {
				$to = $email;
			}

			send_message( 0, $uid, 0, $subject, $body );

//			$body_head = get_handle($uid). " 様\n\n";
//			sys_fwdmail(array('to' => $to, 'subject' => $subject, 'body' => $body_head. $body));
		}
	}
}

?>
