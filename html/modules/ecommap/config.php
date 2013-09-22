<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.

 * Name: eコミマップ連携
 * Description: eコミマップ連携用モジュール
 * Version: 2.0.0
 * Author: haru@digitalearth.co.jp
 * Multiple: true
 */

/** モジュールのバージョン TODO DBが古い場合はアップグレード処理をする */
define('MOD_ECOMMAP_VERSION', '2.0.0');

/** モジュールのローカルパス */
define('MOD_ECOMMAP_PATH', dirname(__FILE__));
/** モジュールのURL CONF_URLBASE と合わせる */
define('MOD_ECOMMAP_URL', CONF_URLBASE.'/modules/ecommap');

/** キャッシュ用書き込み可能パス */
define('MOD_ECOMMAP_CACHE_PATH', MOD_ECOMMAP_PATH."/.cache");
/** キャッシュパスのURL */
define('MOD_ECOMMAP_CACHE_URL', MOD_ECOMMAP_URL."/.cache");

/** eコミマップ側 ブロック出力JSP */
define('MOD_ECOMMAP_BLOCK_JSP', "ecom/block.jsp");


//定数
define('MOD_ECOMMAP_TYPE_MAP', 1);
define('MOD_ECOMMAP_TYPE_MAPS', 2);
define('MOD_ECOMMAP_TYPE_LIST', 10);
define('MOD_ECOMMAP_TYPE_MODIFIED', 20);

/** グループは同一コミュニティ内グループに対応して同期させる */
define('MOD_ECOMMAP_GROUP_IN_COMMUNITY', 1);
/** グループは１つのコミュニティを共用 */
define('MOD_ECOMMAP_GROUP_SINGLE_COMMUNITY', 5);
/** グループはサーバ内コミュニティに対応 */
define('MOD_ECOMMAP_GROUP_IN_SERVER', 10);
/** グループは個別設定で対応 */
define('MOD_ECOMMAP_GROUP_IN_ANY', 100);


//クラス読み込み
include_once(MOD_ECOMMAP_PATH."/classes/EcomMapDB.php");
include_once(MOD_ECOMMAP_PATH."/classes/EcomMapAuth.php");

/* 汎用関数 */
?>