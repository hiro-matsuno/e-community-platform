<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/../tagreader/common.php';
require dirname(__FILE__). '/../rss/common.php';

/* 振り分け*/
$eid = intval($_POST['eid']);

switch ($_REQUEST["action"]) {
	default:
		regist_data($eid);
}

function regist_data($eid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;

	$auth_mode = intval($_POST['auth_mode']);
	$correct   = $_POST['correct'];
	if ($correct == '<br />') {
		$correct = '';
	}

	$f = mysql_uniq("select rb.eid, rb.block_id, ra.display".
					" from blog_data as d".
					" inner join bosai_web_block as rb".
					" on rb.block_id = d.pid".
					" left join bosai_web_auth as ra".
					" on d.id = ra.id".
					" where d.id = %s",
					mysql_num($eid));

	if (!$f) {
		show_error(mysql_error());
	}

	$block_id = $f['block_id'];
	$pid = $f['eid'];

	switch ($auth_mode) {
		case 1:
			$q = mysql_uniq('select max(c.count)'.
							' from blog_data as b'.
							' left join bosai_web_count as c on b.id = c.id'.
							' where b.pid = %s',
							mysql_num($block_id));

			if ($q && isset($q['max(c.count)'])) {
				$count = $q['max(c.count)'] + 1;
			}
			else {
				$count = 1;
			}
			$d = mysql_exec('delete from bosai_web_count where id = %s', mysql_num($eid));
			$i = mysql_exec('insert into bosai_web_count (id, count) values (%s, %s)',
							mysql_num($eid), mysql_num($count));

			$u = mysql_exec('update bosai_web_auth set display = 2 where id = %s',
							mysql_num($eid));
			set_pmt(array(eid => $eid, gid =>get_gid($eid), name => 'pmt_0', not_owner => 1));
			break;
		default:
			$i = mysql_exec('update bosai_web_auth set comment = %s, display = 0'.
				' where id = %s',
				mysql_str($correct), mysql_num($eid));
			set_pmt(array(eid => $eid, gid =>get_gid($eid), unit => PMT_CLOSE, not_owner => 1));
			if (!$i) {
				show_error(mysql_error());
			}
	}

	reload_tagreader($eid);
	reload_rss($eid);

	$ref  = '/modules/bosai_web/edit.php?pid='. $pid;
	$html = '編集完了しました。';
	$data = array(title   => '校正/承認完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => $ref, string => '一覧に戻る')));


	show_input($data);

	exit(0);
}

function reload_rss($id = null) {
	$target_gid = get_gid($id);
	$target_uid = myuid();

	if ($target_gid > 0) {
		$q = mysql_full("select rs.* from rss_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.gid = %s", mysql_num($target_gid));
	}
	else {
		$q = mysql_full("select rs.* from rss_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.uid = %s", mysql_num($target_uid));
	}

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			mod_rss_crawl($r['eid']);
		}
	}
}

function reload_tagreader($id = null) {
	$target_gid = get_gid($id);
	$target_uid = myuid();

	if ($target_gid > 0) {
		$q = mysql_full("select rs.* from tagreader_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.gid = %s", mysql_num($target_gid));
	}
	else {
		$q = mysql_full("select rs.* from tagreader_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.uid = %s", mysql_num($target_uid));
	}

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			mod_tagreader_crawl($r['eid']);
		}
	}
}

?>
