<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/** 認証キーの有効期限を再設定する セッションに登録されている認証キーを延長する */
mb_http_output("UTF-8");

//セッションに認証キーが設定されている場合のみ実行
session_start();
$auth_key = $_SESSION['ecommap_auth_key'];
session_write_close();

if (!$auth_key) return;


//DBへ接続 (lib.phpを使わない セッションも使わない)
$NO_SESSION = true;
include_once(dirname(__FILE__).'/../../config.php');
include_once(dirname(__FILE__).'/../../includes/MySql.php');
mysql_connect_ecom();

include_once(dirname(__FILE__).'/classes/EcomMapDB.php');
include_once(dirname(__FILE__).'/classes/EcomMapAuth.php');

//延長
EcomMapAuth::setAuthExpiry($auth_key);
?>