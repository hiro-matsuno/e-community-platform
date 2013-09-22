<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_menu_block_config($id) {
	$menu   = array();
	$menu[] = array(title => '追加',
					url => '/modules/menu/input.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '編集',
					url => '/modules/menu/edit.php?pid='. $id,
					inline => false);
	return $menu;
}

// main config
// $id: element id
function mod_menu_main_config($id) {
	$menu   = array();
	return $menu;
}

?>
