<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_map_block_config($id = 0) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title  => '中心設定',
						url    => '/modules/map/center.php?eid='. $id,
						inline => false);
	
		$menu[] = array(title  => '機能設定',
						url    => '/modules/map/setting.php?eid='. $id,
						inline => false);
	}
	return $menu;
}

// main config
// $id: element id
function mod_map_main_config($id = 0) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '機能設定',
						url => '/modules/map/setting.php?eid='. $id,
						inline => false);
	}
	return $menu;
}

?>
