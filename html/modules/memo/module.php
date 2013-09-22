<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/classes/MemoModule.php";

function mod_memo_block($blk_id) {

	ob_start();
	include dirname(__FILE__)."/browse/block.php";
	return ob_get_clean();

}

function mod_memo_block_config($blk_id) {

	$menu   = array();
	$menu[] = array(title => '設定', url => Path::makeURL( "/modules/memo/browse/setting.php?blk_id=$blk_id" ), inline => false);
	$menu[] = array(title => '大きく表示', url => Path::makeURL( "/index.php?module=memo&blk_id=$blk_id" ), inline => false);
	return $menu;

}

function mod_memo_main($blk_id) {

	ob_start();
	include dirname(__FILE__)."/browse/block.php";
	return ob_get_clean();
	
}

function mod_memo_main_config($blk_id) {

	$menu   = array();
	$menu[] = array(title => '設定', url => Path::makeURL( "/modules/memo/browse/setting.php?pid=$blk_id" ), inline => false);
	return $menu;

}

function mod_memo_editmenu() {
}

function mod_memo_install() {

	try {
		MemoModule::getInstance()->install();
		return "インストールされました.";
	} catch ( Exception $e ) {
		return false;
	}

}

function mod_memo_uninstall() {
	
	try {
		MemoModule::getInstance()->uninstall();
	} catch ( Exception $e ) {}

}

?>
