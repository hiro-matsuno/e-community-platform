<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../lib.php';
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

$p = get_pinfo($post_id);

if ($p['gid'] > 0) {
	$s = mysql_uniq('select * from page where gid = %s', mysql_num($p['gid']));
	$sitename = $s['sitename'];
}
else {
	$s = mysql_uniq('select * from page where uid= %s', mysql_num($p['uid']));
	$sitename = $s['sitename'];
}

$portalname  = CONF_SITENAME;

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
<p align="center">${portalname}</p>
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

		if ($p['gid'] > 0) {
			$s = mysql_uniq('select * from page where gid = %s', mysql_num($p['gid']));
			$sitename = $s['sitename'];
		}
		else {
			$s = mysql_uniq('select * from page where uid= %s', mysql_num($p['uid']));
			$sitename = $s['sitename'];
		}

		$portalname  = CONF_SITENAME;

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

<p align="center">${portalname}</p>
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
			return get_mpost_blog($param);
			break;
		default:
			break;
	}
}

function get_mpost_blog($param = array()) {
	$eid = $param['eid'];

	$q = mysql_uniq('select * from reporter_block'.
					' where block_id = %s',
					mysql_num($eid));

	$tags = '';
	if ($q) {
		$f = mysql_full("select * from tag_data where pid = %s",
						mysql_num($q['eid']));

		$cur_tag = array();
		if ($f) {
			while ($r = mysql_fetch_array($f)) {
				$cur_tag[$r['tag_id']] = true;
			}
		}

		$t = mysql_full("select ts.* from tag_setting as ts".
						" inner join element".
						" on ts.id = element.id");
		if ($t) {
			while ($r = mysql_fetch_array($t)) {
				if ($cur_tag[$r['id']] == true) {
					$value .= $r["keyword"]. ' ';
						$tags .= '<input type="checkbox" name="tag_0[]" value="'. $r["keyword"]. '">'. $r["keyword"]. ' ';
				}
			}
		}
		$addf = '<input type="hidden" name="reporter" value="1">';
	}
	unset($q);

	$q = mysql_uniq('select * from bosai_web_block'.
					' where block_id = %s',
					mysql_num($eid));
	if ($q) {
		$tags = '';
		$f = mysql_full("select * from tag_data where pid = %s",
						mysql_num($q['eid']));

		$cur_tag = array();
		if ($f) {
			while ($r = mysql_fetch_array($f)) {
				$cur_tag[$r['tag_id']] = true;
			}
		}

		$t = mysql_full("select ts.* from tag_setting as ts".
						" inner join element".
						" on ts.id = element.id");
		if ($t) {
			while ($r = mysql_fetch_array($t)) {
				if ($cur_tag[$r['id']] == true) {
					$value .= $r["keyword"]. ' ';
						$tags .= '<input type="checkbox" name="tag_0[]" value="'. $r["keyword"]. '">'. $r["keyword"]. ' ';
				}
			}
		}
		$addf = '<input type="hidden" name="bosai_web" value="1">';
	}
	if ($tags != '') {
		$tags = '■ｷｰﾜｰﾄﾞ<br>'. $tags. '<br>';
	}

	$date_m = date('n');
	$date_d = date('j');

	global $post_id;

	return <<<___FORM___
■日時<br>
(投稿した日時)<br>
■題名<br>
${addf}
<input type="hidden" name="ttpid" value="${post_id}">
<input type="text" name="subject" value=""><br>
■内容<br>
<textarea name="body" rows="4" cols="20"></textarea><br>
${tags}
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

	$tags = '';
	if ($_REQUEST["tag_0"]) {
		$tags = join(' ', $_REQUEST["tag_0"]);
	}

	if ($_REQUEST['reporter']) {
		$_SESSION["reporter"] = 1;
	}
	else {
		unset($_SESSION["reporter"]);
	}
	if ($_REQUEST['bosai_web']) {
		$_SESSION["bosai_web"] = 1;
	}
	else {
		unset($_SESSION["bosai_web"]);
	}

	$_SESSION["date"]    = $tm;
	$_SESSION["subject"] = mb_convert_encoding($_REQUEST["subject"], "UTF-8", "SJIS");
	$_SESSION["body"]    = mb_convert_encoding($_REQUEST["body"], "UTF-8", "SJIS");
//	$_SESSION["subject"] = $_REQUEST["subject"];
//	$_SESSION["body"]    = $_REQUEST["body"];
	$_SESSION["tag_0"]   = mb_convert_encoding($tags, "UTF-8");
	
	if ($tags != '') {
		$tags = '■ｷｰﾜｰﾄﾞ<br>'. $_SESSION["tag_0"]. '<br>';
	}
	$subject = mb_convert_encoding($_REQUEST["subject"], "UTF-8", "SJIS");
	$body= mb_convert_encoding($_REQUEST["body"], "UTF-8", "SJIS");
	return <<<___FORM___
■題名<br>
<input type="hidden" name="ttpid" value="${post_id}">
${subject}<br>
■内容<br>
${body}<br>
${tags}
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

	$pmt = 0;

	if ($_SESSION['bosai_web'] == 1) {
		$a = mysql_exec('insert into bosai_web_auth (id, display) values (%s, %s)',
						mysql_num($new_id), mysql_num(1));
		$pmt = PMT_CLOSE;
	}
	if ($_SESSION['reporter'] == 1) {
		$c = mysql_uniq('select * from reporter_block as rb'.
						' left join reporter_setting as rs'.
						' on rb.eid = rs.id'.
						' where rb.block_id = %s',
						mysql_num($eid));

		$auth_mode = 1;
		if ($c) {
			if ($c['auth_mode'] == 1) {
				$auth_mode = 2;
				$pmt = PMT_PUBLIC;
			}
			else {
				$pmt = PMT_CLOSE;
			}
		}

		$a = mysql_exec('insert into reporter_auth (id, display) values (%s, %s)',
						mysql_num($new_id), mysql_num($auth_mode));
	}

	if (!$f) {
		die(mysql_error());
	}

	$zoom = 16;
	if ($_SESSION["lat"] && $_SESSION["lon"]) {
		$m = mysql_exec("insert into map_data(id, pid, type, lat, lon, zoom, icon, initymd)".
						" values(%s, %s, %s, %s, %s, %s, %s, %s);",
						mysql_num($new_id), mysql_num($eid), mysql_str('point'),
						mysql_str($_SESSION["lat"]), mysql_str($_SESSION["lon"]),
						mysql_str($zoom), mysql_num(0), mysql_current_timestamp());
		if (!$m) {
			die(mysql_error());
		}
	}

	set_pmt(array(eid  => $new_id,
				  uid  => $uid,
				  gid  => $gid,
				  unit => $pmt));

	set_keyword($new_id, $eid, $_SESSION["tag_0"]);

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

	$p = get_pinfo($post_id);

	if ($p['gid'] > 0) {
		$s = mysql_uniq('select * from page where gid = %s', mysql_num($p['gid']));
		$sitename = $s['sitename'];
	}
	else {
		$s = mysql_uniq('select * from page where uid = %s', mysql_num($p['uid']));
		$sitename = $s['sitename'];
	}


	$portalname  = CONF_SITENAME;
	$keitaipost='';
	if(defined('CONF_POST_MAIL')){
		$post_address = CONF_POST_MAIL;
		$keitaipost=<<<__HTML__
さらに画像等を添付したい場合は、下記から添付してメール送信して下さい。<br>
送信が完了したらこの画面を閉じて下さい。<br>
<br>
<a href="mailto:${post_address}?subject=${post_id}">画像を添付</a>
__HTML__;
	}

	echo <<<__HTML__
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
  <title>Geomobilejp_Converter Sample</title>
</head>
<body>
<center>${sitename}<br>画像投稿</center>
<hr>
携帯からの記事投稿が完了しました。<br>
$keitaipost
<hr>
<p align="center">${portalname}</p>
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

