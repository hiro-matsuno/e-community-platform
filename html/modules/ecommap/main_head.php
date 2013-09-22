<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/** eコミマップ連携メインページ用のヘッダ出力 */
//JavaScriptとCSS読み込み設定
$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/jquery.form.js';
$COMUNI_HEAD_JS[] = CONF_URLBASE.'/jquery.FCKEditor.js';
//Tips表示用
if (ereg("MSIE", getenv('HTTP_USER_AGENT')) ) {
	$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/excanvas-compressed.js';//IE
	$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/jquery.bgiframe.min.js';//IE6
	$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/hoverIntent.js';//IE6
}
//Tips
$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/jquery.bt.min.js';
$COMUNI_HEAD_CSS[] = CONF_URLBASE.'/css/jquery.bt.css';

//メイン表示の場合は２カラムにする
$COMUNI_ONLOAD[] = "ecommap.setLayoutMain($blk_id);";
$COMUNI_HEAD_CSSRAW[] = "#space_3 {display:none;}";
?>
