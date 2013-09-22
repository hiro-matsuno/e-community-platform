<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_rel_cal_block_config($id) {
	$menu   = array();
	if(!get_gid($id)){
		$menu[] = array(title => '機能設定',
						url => '/modules/rel_cal/setting.php?pid='. $id,
						inline => false);
	}
	$menu[] = array(title => '新規登録',
					url => '/modules/rel_cal/input.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '編集',
					url => '/modules/rel_cal/edit.php?pid='. $id,
					inline => false);

	return $menu;
}

// main config
// $id: element id
function mod_rel_cal_main_config($id) {
	$menu   = array();
	if(!get_gid($id)){
		$menu[] = array(title => '機能設定',
						url => '/modules/rel_cal/setting.php?pid='. $id,
						inline => false);
	}
	$menu[] = array(title => '編集',
					url => '/modules/rel_cal/input.php?eid='. $id,
					inline => false);

	$menu[] = array('title'  => '削除',
					'url'    => '/del_content.php?module=rel_cal&eid='. $id,
					'inline' => true);

	return $menu;
}

?>
