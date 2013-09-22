<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

//-----------------------------------------------------
// core
//-----------------------------------------------------
include_once dirname(__FILE__). '/config.php';

require_once dirname(__FILE__). '/lib/Smarty.class.php';
require_once dirname(__FILE__). '/comment_func.php';
require_once dirname(__FILE__). '/map_func.php';
require_once dirname(__FILE__). '/keyword.php';
require_once dirname(__FILE__). '/mobile/mail2fbox.php';

require_once dirname(__FILE__).'/includes/Block.php';
require_once dirname(__FILE__).'/includes/Comment.php';
require_once dirname(__FILE__).'/includes/Content.php';
require_once dirname(__FILE__).'/includes/Dialog.php';
require_once dirname(__FILE__).'/includes/Display.php';
require_once dirname(__FILE__).'/includes/EcomGlobal.php';
require_once dirname(__FILE__).'/includes/Element.php';
require_once dirname(__FILE__).'/includes/Exception.php';
require_once dirname(__FILE__).'/includes/FileboxData.php';
require_once dirname(__FILE__).'/includes/Form.php';
require_once dirname(__FILE__).'/includes/FormBuildId.php';
require_once dirname(__FILE__).'/includes/Friend.php';
require_once dirname(__FILE__).'/includes/Group.php';
require_once dirname(__FILE__).'/includes/HtmlList.php';
require_once dirname(__FILE__)."/includes/InfoHtml.php";
require_once dirname(__FILE__).'/includes/Mail.php';
require_once dirname(__FILE__).'/includes/Message.php';
require_once dirname(__FILE__).'/includes/Mime.php';
require_once dirname(__FILE__).'/includes/Module.php';
require_once dirname(__FILE__).'/includes/MySql.php';
require_once dirname(__FILE__).'/includes/Page.php';
require_once dirname(__FILE__).'/includes/Path.php';
require_once dirname(__FILE__).'/includes/Permission.php';
require_once dirname(__FILE__).'/includes/Serializable.php';
require_once dirname(__FILE__).'/includes/Trackback.php';
require_once dirname(__FILE__).'/includes/User.php';
require_once dirname(__FILE__).'/includes/XssFilter.php';
require_once dirname(__FILE__).'/includes/json_wrapper.php';
require_once dirname(__FILE__).'/includes/misc.php';

//-----------------------------------------------------
// connect db
//-----------------------------------------------------
mysql_connect_ecom();

//-----------------------------------------------------
// user check
//-----------------------------------------------------
global $COMUNI;

$COMUNI = array();

autologin();
mail2fbox();

//-----------------------------------------------------
// INITIALIZE
//-----------------------------------------------------
$JQUERY = array('ready' => array());
$COMUNI_HEAD_JS = array();
$COMUNI_HEAD_JSRAW = array();
$COMUNI_FOOT_JS = array();
$COMUNI_FOOT_JSRAW = array();
$COMUNI_HEAD_CSS = array();
$COMUNI_HEAD_CSSRAW = array();
$COMUNI_FOOT_CSS = array();
$COMUNI_FOOT_CSSRAW = array();
$COMUNI_HEAD_HTML = array();
$COMUNI_FOOT_HTML = array();
$COMUNI_TPATH = array();

//-----------------------------------------------------
// SYS_***
//-----------------------------------------------------
$SYS_FORM = array('head'     => array(),
				  'input'    => array(),
				  'foot'     => array(),
				  'submit'   => '',
				  'cancel'   => '',
				  'onCancel' => '');

$SYS_CACHE    = array();
$SYS_VIEW_GID = 0;
$SYS_VIEW_UID = 0;
$SYS_PAGE_NAVI = array();
$SYS_NICKNAME = array();
$SYS_WRITER_NAME = array();
$SYS_SITE_NAME = array();
$SYS_BLOCK_NAME = array();
$CURRENT_SITE_ID = null;
$SYS_OWNER_LEVEL = array();
$SYS_HIDDEN_BLOCK = array();
$SYS_IS_SU;
$SYS_IS_ADMIN;

if (isset($_SESSION['_uid'])) {
	$COMUNI['uid']      = $_SESSION['_uid'];
	$COMUNI['nickname'] = $_SESSION['_nickname'];
	$COMUNI['is_login'] = true;
	$COMUNI['login_status'] = 1;
}
else {
	$COMUNI['login_status'] = 0;
}
if (isset($_SESSION['_is_admin'])) {
	$COMUNI['is_admin'] = true;
	$COMUNI['login_status'] = 1;
}
if (isset($_SESSION['_is_superuser'])) {
	$COMUNI['is_superuser'] = true;
}
if (isset($_SESSION['_is_deleter'])) {
	$COMUNI['is_deleter'] = true;
}

if (isset($_GET['setting']) && $_GET['setting'] == 'layout') {
	$COMUNI['mode'] = 'layout';
	$COMUNI['layoutmode'] = true;
}

if (preg_match('/(setting|edit|input)\.php/', $_SERVER['REQUEST_URI'])) {
	$COMUNI['edit_mode'] = true;
}

$COMUNI_HEAD_JS  = array();
$COMUNI_HEAD_CSS = array();

//デフォルトCSSを読み込み
$COMUNI_HEAD_CSS[] = CONF_URLBASE."/css/ecom.css";

set_current_site_id();

?>
