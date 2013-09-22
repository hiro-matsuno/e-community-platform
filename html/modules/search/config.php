<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_search_block_config($id = null) {
	$menu   = array();

	return $menu;
}

// main config
// $id: element id
function mod_seach_main_config($id = null) {
	$menu   = array();
/*
	$menu[] = array(title => 'アンケート一覧',
					url => '/modules/enquete/edit.php?pid='. $id,
					inline => false);
*/
	return $menu;
}

?>
