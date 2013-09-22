<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */


require_once dirname(__FILE__). '/../lib.php';

ini_set("include_path", CONF_BASEDIR. '/PEAR/'. PATH_SEPARATOR. ini_get("include_path"));

require_once dirname(__FILE__) . '/lib/Geomobilejp/Converter.php';
require_once dirname(__FILE__) . '/lib/Geomobilejp/Mobile.php';
require_once('Net/UserAgent/Mobile.php');

define('COMSID', 'COMSID');

header('Content-Type: text/html; charset=Shift_JIS');
ob_start();

session_start();
$sid = session_id();

/*
if ($_REQUEST["s"]) {
	$sid = $_REQUEST["COMSID"];
	session_id($sid);
	session_start();
}
else {
	session_start();
	$sid = session_id();
}
*/
//echo 'sid: '. $sid;

$post_id = get_post_id();
if ($post_id) {
	$_SESSION["post_id"] = $post_id;
}
else {
	$post_id = $_SESSION["post_id"];
}

$act = $_REQUEST["a"];

$mobile = new Geomobilejp_Mobile();
if ($mobile->hasParameter()) {
    $converter = new Geomobilejp_Converter(
        $mobile->getLatitude(),
        $mobile->getLongitude(),
        $mobile->getDatum()
    );

	$cw = $converter->convert('wgs84');
	$ct = $converter->convert('tokyo');

    $c1 = $cw->format('dms');
    $c2 = $cw->format('degree');
    $c3 = $ct->format('dms');
    $c4 = $ct->format('degree');

    $c1d = $c1->getDatum();
    $c2d = $c2->getDatum();
    $c3d = $c3->getDatum();
    $c4d = $c4->getDatum();

	$lat  = $c2->getLatitude();
	$lon  = $c2->getLongitude();
	$name = $c2d->getName();

	$_SESSION["lat"]     = $lat;
	$_SESSION["lon"]     = $lon;

	$act     = 'f';
}

if ($_SESSION["lat"] && $_SESSION["lon"]) {
		$lat     = $_SESSION["lat"];
		$lon     = $_SESSION["lon"];
		$gmapkey = CONF_GMAP_KEY;
		$map = <<<___GMAP___
■現在位置<br>
<img src="http://maps.google.com/staticmap?center=${lat},${lon}&size=220x120&markers=${lat},${lon}&key=${gmapkey}">${lat}/${lon}
<a href="/mobile/get.php?post_id=${post_id}&${rdc}">位置の再送信</a><br>
___GMAP___;
	;
}

switch ($act) {
	case 'c':
		$form = get_mpost_blog_confirm($post_id);
		$map = '';
		$next = 'p';
		$submit_str = '投稿';
		break;
	case 'p':
		$eid = mpost_blog($post_id);
		mpost_finish($eid);
		exit(0);
		break;
	case 'f':
		$form = get_mpost_form($post_id);
		$next = 'c';
		$submit_str = '確認';
		break;
	default:
		mpost_select($post_id);
		exit(0);
}
$sitename  = CONF_SITENAME;
$rdc = rand_str(4);
echo <<<__HTML__
<html>
<head>
<title>携帯からの記事入力</title>
</head>
<body>
<center>${sitename}<br>携帯投稿</center>
<hr>
<form action="/mobile/post.php" method="GET">
<input type="hidden" name="a" value="${next}">
<input type="hidden" name="PHPSESSID" value="${sid}">
${form}
${map}
<input type="submit" value="${submit_str}">

</form>
<hr>
<p align="right">${sitename}</p>
</body>
</html>
__HTML__;

$output = ob_get_contents();
ob_end_clean();
echo mb_convert_encoding($output, "SJIS", "UTF-8");

exit(0);

function mpost_select($post_id) {
	session_destroy();

	$q = mysql_uniq("select * from mpost where post_id = %s",
					 mysql_str($post_id));

	$p = get_pinfo($post_id);

	if (!$q) {
		echo '無効な post_id です。';
	}
	else {
		$rdc = rand_str(5);

		$sitename  = CONF_SITENAME;

		echo <<<__HTML__
<html>
<head>
<title>携帯からの記事入力</title>
</head>
<body>
<center>${sitename}<br>携帯投稿</center>
<hr>
携帯からブログへ新規投稿を行います。
<ol>
<li><a href="/mobile/get.php?post_id=${post_id}&${rdc}" accesskey="1">現在位置あり</a></li>
<li><a href="/mobile/post.php/${post_id}?a=f&${rdc}" accesskey="2">現在位置なし</a></li>
</ol>
<hr>

<p align="right">${sitename}</p>
</body>
</html>
__HTML__;
	}
	$output = ob_get_contents();
	ob_end_clean();
	echo mb_convert_encoding($output, "SJIS", "UTF-8");
	exit(0);
}

function get_pinfo($post_id) {
	if (!$post_id) {
		$post_id = $_REQUEST['ttpid'];
	}

	$q = mysql_uniq("select * from mpost where post_id = %s",
					mysql_str($post_id));

	if (!$q) {
		die("!!cannot find post_id". $post_id);
	}

	return array(eid => $q["eid"],
				 uid => $q["uid"],
				 gid => $q["gid"],
				 module => $q["module"]);
}

function get_mpost_form($post_id) {
	$param = get_pinfo($post_id);
	return get_form_by_module($param);
}

function get_form_by_module($param) {
	switch($param["module"]) {
		case 'blog':
			return get_mpost_blog();
			break;
		default:
			break;
	}
}

function get_mpost_blog() {
	$date_m = date('n');
	$date_d = date('j');

	global $post_id;

	return <<<___FORM___
■日時<br>
(投稿した日時)<br>
■題名<br>
<input type="hidden" name="ttpid" value="${post_id}"><br>
<input type="text" name="subject" value=""><br>
■内容<br>
<textarea name="body" rows="4" cols="20"></textarea><br>
___FORM___;
	;
}

function get_mpost_blog_confirm() {
	$year = date('Y');
	$tm = mktime(0,0,0,$_REQUEST["date_m"],$_REQUEST["date_d"],$year);

	global $post_id;

	$post_id = $_REQUEST['ttpid'];

	$date_str = date("n月j日", $tm);
	$date = $tm;

	$_SESSION["date"]    = $tm;
	$_SESSION["subject"] = mb_convert_encoding($_REQUEST["subject"], "UTF-8");
	$_SESSION["body"]    = mb_convert_encoding($_REQUEST["body"], "UTF-8");

	return <<<___FORM___
■題名<br>
<input type="hidden" name="ttpid" value="${post_id}"><br>
${_SESSION["subject"]}<br>
■内容<br>
${_SESSION["body"]}<br>
___FORM___;
	;
}

function mpost_blog() {
	$new_id = get_seqid();

	$post_id = $_SESSION["post_id"];

	$pinfo = get_pinfo($post_id);

	$eid = $pinfo["eid"];
	$uid = $pinfo["uid"];
	$gid = $pinfo["gid"];

	$tm = $_SESSION["date"];

	$pat = '/\n/';
	$body =  preg_replace($pat, '<br>', $_SESSION["body"]);

	$f = mysql_exec("insert into blog_data (id, pid, subject, body, initymd)".
					" values (%s, %s, %s, %s, %s);",
					mysql_num($new_id), mysql_num($eid),
					mysql_str($_SESSION["subject"]), mysql_str($body),
					mysql_current_timestamp());

	if (!$f) {
		die(mysql_error());
	}

	$zoom = 16;
	if ($_SESSION["lat"] && $_SESSION["lon"]) {
		$m = mysql_exec("insert into map_data(id, pid, type, lat, lon, zoom, icon, initymd)".
						" values(%s, %s, %s, %s, %s, %s, %s, %s);",
						mysql_num($new_id), mysql_num($pid), mysql_str('point'),
						mysql_str($_SESSION["lat"]), mysql_str($_SESSION["lon"]),
						mysql_str($zoom), mysql_num(0), mysql_current_timestamp());
		if (!$m) {
			die(mysql_error());
		}
	}

	$pmt = 0;

	set_pmt(array(eid => $new_id,
				  uid => $uid,
				  gid => $gid,
				  pmt => $pmt));

	return $new_id;
}

function mpost_finish($eid) {
	global $post_id;
	if (!$post_id) {
		$post_id = $_REQUEST['ttpid'];
	}

	$q = mysql_exec("insert into mpost_mailq (post_id, eid)".
					" values(%s, %s);",
					mysql_str($post_id), mysql_num($eid));

	global $post_id;

	if (!$post_id) {
		$post_id = $_REQUEST['ttpid'];
	}

	$sitename  = CONF_SITENAME;
	$ketaipost = CONF_POST_MAIL;

	echo <<<__HTML__
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
  <title>Geomobilejp_Converter Sample</title>
</head>
<body>
<center>${sitename}<br>携帯投稿</center>
<hr>
携帯からの投稿が完了しました。<br>
さらに画像等を添付したい場合は、下記から添付してﾒｰﾙ送信して下さい。<br>
<br>
<a href="mailto:${ketaipost}?subject=${post_id}">画像を添付</a>
<hr>
<p align="right">${sitename}</p>
</body>
</html>
__HTML__;
	;
	$output = ob_get_contents();
	ob_end_clean();
	echo mb_convert_encoding($output, "SJIS", "UTF-8");
	exit(0);
}

function get_post_id() {
	$pat = '/^\//';
	return preg_replace($pat, '', get_path_info());
}

function get_path_info() {
	if (array_key_exists('PATH_INFO', $_SERVER)) {
		return $_SERVER['PATH_INFO'];
	}
	else if (array_key_exists('ORIG_PATH_INFO', $_SERVER)) {
		return $_SERVER['ORIG_PATH_INFO'];
	}
	$path_info = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
	if (substr_count($path_info, '?') > 0) {
		$path_info = preg_replace('/\?.*/', '', $path_info);
	}
	return $path_info;
}

