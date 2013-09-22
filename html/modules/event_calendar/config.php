<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_event_calendar_block_config($id) {
	$menu   = array();
	$menu[] = array(title => '新規登録',
					url => '/modules/event_calendar/input.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '編集',
					url => '/modules/event_calendar/edit.php?pid='. $id,
					inline => false);

	return $menu;
}

// main config
// $id: element id
function mod_event_calendar_main_config($id) {
	$menu   = array();
	$menu[] = array(title => '編集',
					url => '/modules/event_calendar/input.php?eid='. $id,
					inline => false);

	$menu[] = array('title'  => '削除',
					'url'    => '/del_content.php?module=schedule&eid='. $id,
					'inline' => true);

	return $menu;
}

?>
