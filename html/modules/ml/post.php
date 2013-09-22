<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once(dirname(__FILE__). '/../../lib.php');
require_once('Net/UserAgent/Mobile.php');
include_once(dirname(__FILE__). '/main.php');

$key = isset($_REQUEST['key']) ? $_REQUEST['key'] : '';

if ($key != '') {
	$c = mysql_uniq("select * from mod_ml_post_key where id = %s", mysql_str($key));

	if ($c) {
		$f = mysql_uniq("select * from user where id = %s", mysql_str($c['uid']));

		$_SESSION['_uid']      = $f[id];
		$_SESSION['_nickname'] = $f[handle];

		if($f['level'] >= 100){
			$_SESSION['_is_superuser'] = true;
			$_SESSION['_is_admin'] = true;
		}

		if (Net_UserAgent_Mobile::isMobile()) {
			print_form_mb($c['eid'], $key);
		}
		else {
			$href = CONF_URLBASE. '/index.php?module=ml&action=post'.
					'&pid='. $c['eid']. '&eid='. $c['eid'];
			header('Location: '. $href);
		}
		exit(0);
	}
}
else {
	die('キーが取得できません。');
}

function print_form_mb($eid = 0, $key = null) {
	if (!$key) {
		die('キーが取得できません。');
	}

	$s = mysql_uniq('select * from mod_ml_setting where id = %s', mysql_num($eid));

	if ($s) {
		$title = $s['title'];
	}

	header('Content-Type: text/html; charset=Shift_JIS');

	$form     = mod_ml_post($eid, 'SJIS');
	$sitename = CONF_SITENAME;

	ob_start();

	echo <<<__HTML__
<html>
<head>
<title>携帯投稿[ML]</title>
</head>
<body>
<center>${title}<br>メッセージ投稿</center>
<hr>
${form}
<hr>
<p align="center">${sitename}</p>
</body>
</html>
__HTML__;
	;

	$output = ob_get_contents();
	ob_end_clean();
	echo mb_convert_encoding($output, "SJIS", "UTF-8");

	exit(0);
}

?>
