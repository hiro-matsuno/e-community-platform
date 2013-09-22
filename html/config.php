<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

//-----------------------------------------------------
// config.php
//-----------------------------------------------------
include "version.php";

//ini_set('display_errors', 0); //Fatal Errorが表示されなくなるので廃止
//ini_set('upload_max_filesize', "100M"); //ini_setでは設定できない

/* ini_setのパラメータは/config/*.ini.phpに記述することで設定可能(PHPの定数も使用可)
 * ※Warningを出したい場合
 * [conf_ini_set]
 * display_errors=1
 * error_reporting=E_ERROR|E_WARNING
 */
ini_set( "error_reporting", E_ERROR);//デフォルト値を先に設定

mb_language("Japanese");
mb_internal_encoding("utf-8");
mb_http_output("pass");

$server_name = $_SERVER['SERVER_NAME'];
if(!isset($server_name))die('お使いのブラウザでは表示することができません。');

if (isset($cron_server_name)) {
	$server_name = $cron_server_name;
}

if(file_exists(dirname(__FILE__)."/config/${server_name}.ini.php")){
	$conf = parse_ini_file(dirname(__FILE__)."/config/${server_name}.ini.php",true);
	//if(!$conf['conf_host']['publish'])die($conf['conf_host']['site_name'].'は公開停止中です');
	if(!$conf['conf_host']['publish']){
		$message = $conf['conf_host']['site_name'].'は公開停止中です<br>';
		$message .= '<p style="font-size: 80%;"><a href="/manager/install/setup.php">管理者専用ページ</a></p>';
		error_exit($message);
	}
	define('CONF_MYSQL_DB', $conf['conf_mysql']['database']);
	define('CONF_MYSQL_USER', $conf['conf_mysql']['user']);
	define('CONF_MYSQL_PASSWD', $conf['conf_mysql']['passwd']);
	define('CONF_MYSQL_HOST', $conf['conf_mysql']['server']);
	define('CONF_DOMAIN', $server_name);
	define('CONF_RANDOM_SEED', $conf['conf_host']['random_seed']);
	define('CONF_SITEURL', $conf['conf_host']['site_url']);
	define('CONF_URLBASE', $conf['conf_host']['url_base']);
	define('CONF_BASEDIR', dirname(__FILE__).'/');
	define('CONF_EMAIL', $conf['conf_host']['email']);
	define('CONF_ERRMAIL', $conf['conf_host']['err_email']);
	define('CONF_POST_MAIL', $conf['conf_post']['email']);
	define('CONF_POST_MAIL_POP3SERVER', $conf['conf_post']['server']);
	define('CONF_POST_MAIL_USERNAME', $conf['conf_post']['user']);
	define('CONF_POST_MAIL_PASSWORD', $conf['conf_post']['passwd']);
	define('CONF_SMTP', $conf['conf_host']['smtp_server']);
	define('CONF_SITENAME', $conf['conf_host']['site_name']);
	define('CONF_ASSNS_X', $conf['conf_map']['longitude']);
	define('CONF_ASSNS_Y', $conf['conf_map']['latitude']);
	define('CONF_ASSNS_Z', $conf['conf_map']['zoom']);
	define('CONF_GMAP_KEY', $conf['conf_map']['api_key']);
	define('CONF_SMARTY_COMPILE', $conf['conf_dir']['smarty_compile']);
	define('CONF_SMARTY_CONFIG', $conf['conf_dir']['smarty_config']);
	define('CONF_SMARTY_CACHE', $conf['conf_dir']['smarty_cache']);
	define('CONF_PMT_TITLE', "公開範囲");
	define('CONF_MAP_TITLE', "位置情報");
	define('CONF_KEYWORD_TITLE', "キーワード");
	
	add_endslash($conf['conf_dir']['databox_dir']);
	add_endslash($conf['conf_dir']['databox_urlbase']);
	define('CONF_CONVERT', $conf['conf_dir']['convert_path']);
	define('CONF_DATABOX_URLBASE', $conf['conf_dir']['databox_urlbase']);
	define('CONF_DATABOX_DIR', $conf['conf_dir']['databox_dir']);
	define('CONF_FILEBOX_DIR', $conf['conf_dir']['databox_dir'].'filebox/');
	define('CONF_PROFILE_DIR', $conf['conf_dir']['databox_dir'].'profile/');
	
	//[conf_ini_set]以下をすべてini_setする
	if (is_array($conf['conf_ini_set'])) {
		foreach ($conf['conf_ini_set'] as $key=>$value) {
			ini_set($key, $value);
		}
	}

	//[debug] デバッグコード
	if (is_array($conf['debug'])) {
		foreach ($conf['debug'] as $key=>$value) {
			define( strtoupper( $key ), $value );
		}
	}

}else{
	include_once(dirname(__FILE__). "/config/hosts.php");
	define('CONF_DATABOX_DIR','databox');
	if (isset($server_name) && ($_vhosts[$server_name] == true)) {
		include_once(dirname(__FILE__). "/config/${server_name}.php");
	
		if (!defined('CONF_DOMAIN')) {
			forward_setup();
		}
	}
	else {
		forward_setup();
	}
}

ini_set("include_path", CONF_BASEDIR. '/PEAR/'. PATH_SEPARATOR. ini_get("include_path"));

//includeされる前に $NO_SESSION=true; が設定されていたらセッションは開始しない
//セッションを毎回開いて閉じるように全体を修正したら以下は削除する
if (session_id() == '' && !$NO_SESSION) {
	session_start();
}

//-----------------------------------------------------
// functions
//-----------------------------------------------------
function forward_setup(){
	header("Location: ". '/manager/install/setup.php');
	exit();
}

function error_exit($message) {
	echo <<<__ERROR__
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja-JP" lang="ja-JP">
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<head>
<title>Error</title>
</head>
<body>
$message
</body>
</html>
__ERROR__;

	exit();
}

function add_endslash(&$var){
	if(substr($var,-1)!='/')$var .= '/';
}
//-----------------------------------------------------
// end of script
//-----------------------------------------------------
?>
