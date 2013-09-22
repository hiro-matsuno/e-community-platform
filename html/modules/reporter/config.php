<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_reporter_block_config($id) {
	$menu   = array();

	if(is_owner($id,80)){
		$menu[] = array(title => '基本設定',
						url => '/modules/reporter/input.php?pid='. $id,
						inline => false);
	}

	$menu[] = array(title => '記事一覧',
					url => '/modules/reporter/edit.php?pid='. $id,
					inline => false);

	return $menu;
}

// main config
// $id: element id
function mod_reporter_main_config($id) {
	$menu   = array();
/*
	$menu[] = array(title => 'アンケート一覧',
					url => '/modules/enquete/edit.php?pid='. $id,
					inline => false);
*/
	return $menu;
}

?>
