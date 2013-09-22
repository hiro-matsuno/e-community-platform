<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/** eコミマップ連携ブロック用のヘッダ出力.
 * include_onceで複数のブロック表示時も１回のみ実行
 * $ecommap_url と $auth_url は呼び出す前に設定しておく
 *  */
//JavaScriptとCSS読み込み設定
$COMUNI_HEAD_CSS[] = MOD_ECOMMAP_URL.'/style.css';
$COMUNI_HEAD_JS[] = MOD_ECOMMAP_URL.'/EcomMap.js';

$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/jquery.form.js';

$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/ui/ui.datepicker.js';
$COMUNI_HEAD_JS[] = CONF_URLBASE.'/js/ui/i18n/ui.datepicker-ja.js';

$COMUNI_HEAD_JSRAW[] = "var ecommap = new EcomMap('".MOD_ECOMMAP_URL."')";
?>