<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

define('MOD_BBS_MAXREC', 1000);

// block config
// $id: block id
function mod_bbs_block_config($id) {
	$menu   = array();
	$menu[] = array(title => '新規スレッド',
					url => '/modules/bbs/post.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '一覧',
					url => '/modules/bbs/edit.php?pid='. $id,
					inline => false);
	return $menu;
}

// main config
// $id: element id
function mod_bbs_main_config($id) {
	$menu   = array();
	$p = mysql_uniq("select * from mod_bbs_thread where id = %s",
					mysql_num($id));
	$pid = $p['pid'];
	$menu[] = array(title => '新規スレッド',
					url => '/modules/bbs/post.php?pid='. $pid,
					inline => false);

	$menu[] = array(title => '一覧',
					url => '/modules/bbs/edit.php?pid='. $pid,
					inline => false);
	return $menu;
}

?>
