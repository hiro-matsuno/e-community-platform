<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

define('MOD_RSS_RELOAD', 300);

// block config
// $id: block element id
function mod_rss_block_config($id) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '機能設定',
						url => '/modules/rss/input.php?eid='. $id,
						inline => false);
	}
	
	return $menu;
}

// main config
// $id: element id
function mod_rss_main_config($id) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '機能設定',
						url => '/modules/rss/input.php?eid='. $id,
						inline => false);
	}
	return $menu;
}

?>
