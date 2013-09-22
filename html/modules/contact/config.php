<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_contact_block_config($id) {
	$menu   = array();

	if (owner_level($id) > 80) {
		$menu[] = array('title'  => '編集',
						'url'    => '/modules/contact/input.php?eid='. $id,
						'inline' => false);

		$menu[] = array('title'  => '機能設定',
						'url'    => '/modules/contact/setting.php?eid='. $id,
						'inline' => false);
	}

	return $menu;
}

function mod_contact_main_config($id) {
	$menu   = array();

	if (owner_level($id) > 80) {
		$menu[] = array('title'  => '編集',
						'url'    => '/modules/contact/input.php?eid='. $id,
						'inline' => false);

		$menu[] = array('title'  => '機能設定',
						'url'    => '/modules/contact/setting.php?eid='. $id,
						'inline' => false);
	}

	return $menu;
}

?>
