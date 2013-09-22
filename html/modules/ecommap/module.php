<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/*
 * Name: eコミマップ連携モジュール
 * Version: 2.0
 * Description:  eコミの地図一覧を表示するパーツ
 */
include_once(dirname(__FILE__). '/config.php');

/** 管理者設定メニュー */
function mod_ecommap_editmenu()
{
	global $COMUNI_HEAD_CSS;
	$COMUNI_HEAD_CSS[] = MOD_ECOMMAP_URL.'/images/icons.css';
	$sub_menu = array();
	$sub_menu[] = array(title => 'eコミマップ連携設定', url => MOD_ECOMMAP_URL.'/setting.php', 'class'=>'ecommap');
	return array(name => 'eコミマップ連携', sub => $sub_menu);
}

/** ブロック用メニュー
 * @param $blk_id ブロックの固有ID */
function mod_ecommap_block_config($blk_id)
{
	$menu = array();
	$menu[] = array(title => 'マップ項目一覧', url=>"javascript:ecommap.mapFeatureType()", inline => false);
	$options = EcomMapDB::getOptions();
	if (($options[sync_user_level] == Permission::USER_LEVEL_ADMIN && is_su()) || ($options[sync_user_level] == Permission::USER_LEVEL_POWERED && is_poweruser($blk_id))) {
		$menu[] = array(title => '連携設定', url=>MOD_ECOMMAP_URL."/block_setting.php?blk_id=".$blk_id, inline=>false);
	}
	if (($options[admin_user_level] == Permission::USER_LEVEL_ADMIN && is_su()) || ($options[admin_user_level] == Permission::USER_LEVEL_POWERED && is_poweruser($blk_id))) {
		$menu[] = array(title => 'eコミマップ管理画面', url=>"javascript:ecommap.mapAdmin()", inline => false);
	}
	return $menu;
}

/** メイン用メニュー
 * @param $blk_id ブロックの固有ID */
function mod_ecommap_main_config($blk_id)
{
	return null;
}


/** ブロック
 * @param $blk_id ブロックの固有ID */
function mod_ecommap_block($blk_id)
{
	include_once (dirname(__FILE__).'/block.php');
	return _mod_ecommap_block($blk_id);
}

/** メインページ
 * index.php?module=モジュール名で呼ばれる
 * @param $blk_id ブロックの固有ID */
function mod_ecommap_main($blk_id)
{
	include_once ('main.php');
	return _mod_ecommap_main($blk_id);
}

?>
