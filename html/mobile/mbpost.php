<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../lib.php';

header('Content-Type: text/html; charset=Shift_JIS');
ob_start();

$post_key = get_post_id();

$q = mysql_uniq('select * from mobile_update_setting'.
				' where post_key = %s',
				mysql_str($post_key));

if ($q) {
	$uid = $q['uid'];
	$gid = $q['gid'];
}
else {
	echo '更新キーが違います。';
	print_html();
}

if ($gid > 0) {
	$q = mysql_uniq('select * from page where gid = %s', mysql_num($gid));
	$sitename = $q['sitename'];
	$site_id = $q['id'];
}
else {
	$q = mysql_uniq('select * from page where uid= %s', mysql_num($uid));
	$sitename = $q['sitename'];
	$site_id = $q['id'];
}

echo $sitename. 'の更新<hr>更新するコンテンツを選択して下さい。<br>';

$c = mysql_full('select b.* from block as b'.
				' where b.pid = %s',
				mysql_num($site_id));

$block = array();
if ($c) {
	while ($r = mysql_fetch_array($c)) {
		switch ($r['module']) {
			case 'blog':
				$post_id = mobpost_issue_id(array(uid => $uid,
												  gid => $gid,
												  eid => $r['id'],
												  module => 'blog'));

				$name = $r['name'] ? $r['name'] : 'ブログ';
				$block[] = array(id   => $r['id'],
								 post_id => $post_id,
								 name => $name);
				break;
			default:
				;
		}
	}
}

echo '<ul>';
foreach ($block as $b) {
	echo '<li><a href="/mobile/post.php/'. $b['post_id']. '">'. $b['name']. '</a>';
	$p = mysql_uniq('select * from bosai_web_block as rb'.
					' left join bosai_web_setting as rs'.
					' on rb.eid = rs.id'.
					' where rb.block_id = %s',
					mysql_num($b['id']));
	if ($p) {
		$parent_sitename = get_site_name(get_site_id($p['id']));
		$bname = get_block_name($p['id']);

		echo '<br>';
		echo $parent_sitename. '/'. $bname. 'への投稿ブロックです。<br>';
		echo $b['msg'];
	}
	unset($p);
	$p = mysql_uniq('select * from reporter_block as rb'.
					' left join reporter_setting as rs'.
					' on rb.eid = rs.id'.
					' where rb.block_id = %s',
				mysql_num($b['id']));
	if ($p) {
		$parent_sitename = get_site_name(get_site_id($p['id']));
		$bname = get_block_name($p['id']);

		echo '<br>';
		echo $parent_sitename. '/'. $bname. 'への投稿ブロックです。<br>';
		echo $b['msg'];
	}
	echo '</li>';
}
echo '</ul><hr>';
echo '<center>'. CONF_SITENAME. '</center>';

print_html();

function print_html() {
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
