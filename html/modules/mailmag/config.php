<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_mailmag_block_config($id = 0) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '基本設定',
						url => '/modules/mailmag/input.php?eid='. $id,
						inline => false);
	}
	$menu[] = array(title => '一覧',
					url => '/modules/mailmag/edit.php?eid='. $id,
					inline => false);
	return $menu;
}

// main config
// $id: element id
function mod_mailmag_main_config($id = 0) {
	$menu   = array();
/*
	$menu[] = array(title => '編集',
					url => '/modules/mailmag/input.php?eid='. $id,
					inline => false);
*/
	return $menu;
}

?>
