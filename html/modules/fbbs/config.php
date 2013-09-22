<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

define('MOD_FBBS_MAXREC', 1000);

include_once(dirname(__FILE__). '/css.php');

// block config
// $id: block id
function mod_fbbs_block_config($id) {
	$menu   = array();
	if (owner_level($id) > 80) {
		$menu[] = array(title => '機能設定',
						url => '/modules/fbbs/setting.php?eid='. $id,
						inline => false);
	}
	return $menu;
}

// main config
// $id: element id
function mod_fbbs_main_config($id) {
	$menu   = array();
/*
	if (owner_level($id) > 80) {
		$menu[] = array(title => '機能設定',
						url => '/modules/fbbs/setting.php?eid='. $id,
						inline => false);
	}
*/
	return $menu;
}

?>
