<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_enquete_block_config($id) {
	$menu   = array();

	if (owner_level($id) > 80) {
		$menu[] = array(title => '新規登録',
						url => '/modules/enquete/input.php?pid='. $id,
						inline => false);

		$menu[] = array(title => '編集',
						url => '/modules/enquete/edit.php?pid='. $id,
						inline => false);
	}

	return $menu;
}

// main config
// $id: element id
function mod_enquete_main_config($id) {
	$menu   = array();
/*
	$menu[] = array(title => 'アンケート一覧',
					url => '/modules/enquete/edit.php?pid='. $id,
					inline => false);
*/
	return $menu;
}

?>
