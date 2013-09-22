<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/**
 * eコミマップ連携モジュール
 * メインページ
 */
function _mod_ecommap_main($blk_id)
{
	global $COMUNI_HEAD_CSS, $COMUNI_HEAD_CSSRAW, $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW, $COMUNI_ONLOAD;
	
	//ヘッダへの出力設定
	include_once(MOD_ECOMMAP_PATH.'/head.php');
	include_once(MOD_ECOMMAP_PATH.'/main_head.php');
	
	include_once("classes/EcomMapDB.php");
	
	//パラメータ読み込み

	
	//各ページ内で利用する変数
	$mainpage_url = CONF_URLBASE."/index.php?module=ecommap&blk_id=".$_REQUEST['blk_id'];
	$gid = get_gid($blk_id);
	$uid = get_myuid();
	
	//グループのユーザレベル
	$level = join_level($gid);
	$isEditor = $level >= 50;
	$isMember = $level > 0;

	
	//各ページの標準出力をバッファで文字列化
	ob_start();
?>
<div class="ecommap_block">
<?
	try {
		
		
		
	} catch (Exception $e) { echo $e, ob_get_clean(); }
?>
</div>
<?
	return ob_get_clean();
}
?>
