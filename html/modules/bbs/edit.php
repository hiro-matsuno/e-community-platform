<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	default:
		input_data($eid, $pid);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;
	global $SYS_BOX_TITLE;

	$SYS_BOX_TITLE = 'スレッド一覧';

	$f = mysql_fullpmt($pid, 'mod_bbs_thread');

	$list = array();

	$list[] = array(id      => '',
					subject => 'タイトル',
//					body    => '内容',
					initymd => '投稿日');
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$href = "/index.php?module=bbs&eid=$r[id]&blk_id=$r[pid]";
			$title = $r['title'] ? $r['title'] : '無題';
			$list[] = array(id      => $r['id'],
							subject => make_href($title, $href, null, '_blank', 32),
//							body    => clip_str($r['body'], 50),
							initymd => date('Y年n月d日 G時i分', strtotime($r['initymd'])));
//							updymd  => date('Y年m月d日 H時i分', tm2time($r['updymd'])));
		}
	}

	set_return_url();

	$edit_url = '/modules/bbs/post.php?eid=';
	$del_url  = '/del_content.php?module=bbs&eid=';
	$html = create_edit_list($edit_url, $del_url, $list);

	$data = array(title   => $SYS_BOX_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
