<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_profile_block_config($id) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '編集',
						url => '/modules/profile/input.php?eid='. $id,
						inline => false);
	}

	return $menu;
}

// main config
// $id: element id
function mod_profile_main_config($id) {
	$menu   = array();
	if(is_owner($id,80)){
		$menu[] = array(title => '編集',
						url => '/modules/profile/input.php?eid='. $id,
						inline => false);
	}

	return $menu;
}

?>
