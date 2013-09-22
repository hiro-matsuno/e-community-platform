<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_tagreader_block_config($id) {
	$menu   = array();
	if (owner_level($id) > 80) {
		$menu[] = array(title => '機能設定',
						url => '/modules/tagreader/input.php?pid='. $id,
						inline => false);
	}
	return $menu;
}

// main config
// $id: element id
function mod_tagreader_main_config($id) {
	$menu   = array();
	if (owner_level($id) > 80) {
		$menu[] = array(title => '機能設定',
						url => '/modules/tagreader/input.php?pid='. $id,
						inline => false);
	}
	return $menu;
}

?>
