<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_page_block_config($id) {
	$menu   = array();
	$menu[] = array(title => '編集',
					url => '/modules/page/input.php?pid='. $id,
					inline => false);

	return $menu;
}

// main config
// $id: element id
function mod_page_main_config($id) {
	$menu   = array();
	$menu[] = array(title => '編集',
					url => '/modules/page/input.php?eid='. $id,
					inline => false);

	$menu[] = array('title'  => '削除',
					'url'    => '/modules/page/delete.php?eid='. $id,
					'inline' => true);

	return $menu;
}

?>
