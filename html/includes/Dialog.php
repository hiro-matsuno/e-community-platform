<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
//-----------------------------------------------------
// * ダイアログの表示
//-----------------------------------------------------
function show_dialog($param = array()) {
	show_dialog2($param);
}

//-----------------------------------------------------
// * ダイアログ用の表示
//-----------------------------------------------------
function show_dialog2($param = array()) {
	global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS, $COMUNI_DEBUG;
	global $COMUNI_HEAD_CSSRAW;
	global $JQUERY, $COMUNI_FOOT_JS, $COMUNI_HEAD_JSRAW, $COMUNI_FOOT_JSRAW;

	/* そのうちconfigにまわすarray */
	$icons = array('default'  => '/image/icons/001_09.png',
				   'notice'   => '/image/icons/001_10.png',
				   'error'    => '/image/icons/001_30.png',
				   'warning'  => '/image/icons/001_11.png',
				   'favorite' => '/image/icons/001_15.png',
				   'mail'     => '/image/icons/001_12.png',
				   'finish'   => '/image/icons/001_06.png',
				   'plus'     => '/image/icons/001_01.png',
				   'friend'   => '/image/icons/001_57.png',
				   'write'    => '/image/icons/001_35.png',
				   'comment'  => '/image/icons/001_50.png',
				   'profile'  => '/image/icons/001_54.png');

	$title   = isset($param["title"])   ? $param["title"] : 'NOTICE!!';
	$message = isset($param["message"]) ? $param["message"] : '';
	$content = isset($param["content"]) ? $param["content"] : '';
	$icon    = isset($param["icon"])    ? $param["icon"] : 'default';

	// テンプレート読み込み
	$smarty = new Smarty;

	$smarty->template_dir = CONF_BASEDIR;
	$smarty->compile_dir  = CONF_BASEDIR. '/'. CONF_SMARTY_COMPILE;
	$smarty->config_dir   = CONF_BASEDIR. '/'. CONF_SMARTY_CONFIG;
	$smarty->cache_dir    = CONF_BASEDIR. '/'. CONF_SMARTY_CACHE;

	$smarty->caching = false;
	$smarty->compile_check = true;

	$skin_filename = 'default';

	// スキンの反映
	$JQUERY_FIXED = array('ready' => array());
	foreach ($JQUERY["ready"] as $line) {
		$JQUERY_FIXED["ready"][] = str_replace('$(', 'jQuery(', $line);
	}

	$smarty->assign('jquery_ready_script', $JQUERY_FIXED["ready"]);

	$smarty->assign('site_name', 'e-community platform :: portal');

	$COMUNI_HEAD_CSS[] = '/css/ui.all.css';
	$COMUNI_HEAD_CSS[] = '/skin/_dialog.css';
	$COMUNI_HEAD_CSS[] = '/common/css.php?id=0';

	/*** jQueryのロード ***/
	array_unshift($COMUNI_HEAD_JS, '/js/jquery.pager.js');
	array_unshift($COMUNI_HEAD_JS, '/js/jquery-ui-1.6.custom.min.js');
	array_unshift($COMUNI_HEAD_JS, '/js/jquery-1.2.6.min.js');

	/*** ログインしていたら。 ***/
	if (is_login()) {
		$smarty->assign('is_login', true);
		$smarty->assign('nickname', $COMUNI["nickname"]);
	}

	$smarty->assign('head_js', $COMUNI_HEAD_JS);
	$smarty->assign('head_jsraw', $COMUNI_HEAD_JSRAW);
	$smarty->assign('head_css', $COMUNI_HEAD_CSS);
	$smarty->assign('head_cssraw', $COMUNI_HEAD_CSSRAW);
	if (count($COMUNI_FOOT_JSRAW) > 0) {
		$smarty->assign('foot_jsraw', $COMUNI_FOOT_JSRAW);
	}

	$smarty->assign('title', $title);
	$smarty->assign('icon', $icons[$icon]);
	$smarty->assign('message', $message);
	$smarty->assign('content', $content);

	if (isset($COMUNI["is_top"]) && ($COMUNI["is_top"] == true)) {
		$smarty->assign('is_top', true);
	}

	$smarty->assign('debug', $COMUNI_DEBUG);

	$smarty->display('skin/'. '_dialog.tpl');

	exit(0);
}

//-----------------------------------------------------
// * ダイアログ用「戻る」フォームの生成
//-----------------------------------------------------
function create_rform($param) {
	global $SYS_FORM;
	$SYS_FORM["submit"]   = "作業を終了";
	$SYS_FORM["onSubmit"] = "parent.tb_remove(); parent.location.href='${param["href"]}'; return false;";

	return create_form($param);
}

//-----------------------------------------------------
// * ダイアログ用「閉じる」フォームの生成
//-----------------------------------------------------
function create_form_remove($param = array()) {
	global $SYS_FORM;
	$SYS_FORM["submit"]   = "閉じる";
	$SYS_FORM["onSubmit"] = "parent.tb_remove(); return false;";

	return create_form($param);
}

//-----------------------------------------------------
// * ダイアログ用「再読み込み」フォームの生成
//-----------------------------------------------------
function reload_form($param = null) {
	global $SYS_FORM;

	if ( null !== $param and is_array( $param ) ) {

		$string = $param['string'] ? $param['string'] : 'ページの再読込';

		$SYS_FORM["submit"]   = $string;
		$SYS_FORM["onSubmit"] = "parent.tb_remove(); parent.location.reload(); return false;";

	} else {

		$SYS_FORM["submit"]   = "了解";
		$SYS_FORM["onSubmit"] = "parent.tb_remove(); parent.history.back(); return false;";

	}

	return create_form($param);
}

//-----------------------------------------------------
// * 作業終了時の指定 URL へ遷移するフォームの生成
//-----------------------------------------------------
function create_form_return($param = array()) {
	global $SYS_FORM;

	$string = $param['string'] ? $param['string'] : 'ページを表示';

	$SYS_FORM["submit"]   = $string;
	$SYS_FORM["onSubmit"] = "parent.location.href='${param["href"]}'; return false;";

	return create_form($param);
}

//-----------------------------------------------------
// * ダイアログ用「一つ前に戻る」のフォーム生成
//-----------------------------------------------------
function return_dialog($param = array()) {
	global $SYS_FORM;

	$string = isset($param['string']) ? $param['string'] : '戻る';
	$href   = isset($param['href']) ? $param['href'] : '';

	if ($href != '') {
		$script = "location.href='${href}';";
	}
	else {
		$script = "history.back();";
	}

	$SYS_FORM["submit"]   = $string;
	$SYS_FORM["onSubmit"] = $script. " return false;";

	return create_form($param);
}

//-----------------------------------------------------
// * ログインページの表示
//-----------------------------------------------------
function show_login($type = '') {
	switch ($type) {
		case 'dialog':
			$jump = '/login.php?type=dialog';
			break;
		default:
			if (!$_SESSION['return']) {
				$_SESSION['return'] = $_SERVER['REQUEST_URI'];
			}
			$jump = '/login.php';
	}
	header("Location: ". $jump);
	exit(0);
}

//-----------------------------------------------------
// * エラーの表示 (ダイアログ)
//-----------------------------------------------------
function show_error($msg = '') {
	global $COMUNI;

/*
	ダイアログかページで分けてエラーを出すべき。
	switch ($COMUNI["page_type"]) {
		case 'dialog':
			show_dialog();
			break;
		default:
			show_page();
	}
*/

//	$msg .= '<div style="text-align: center;"><a href="#" onClick="history.back(); return false;">ひとつ前に戻る</a></div>';

	$data = array('title'   => 'エラーウインドウ',
				  'icon'    => 'error',
				  'message' => '',
				  'content' => $msg);

	show_dialog2($data);

	exit(0);
}

//-----------------------------------------------------
// * エラーの表示 (ページ)
//-----------------------------------------------------
function error_window($msg = '') {
	$b = create_form_return(array('string' => CONF_SITENAME. 'へ',
								  'href'   => CONF_SITEURL));

	$data = array('title'   => 'エラーウインドウ',
				  'icon'    => 'error',
				  'message' => '',
				  'content' => $msg. $b);

	show_1page($data);

	exit(0);
}


/**
 * Description of Dialog
 *
 * @author ikeda
 */
class Dialog {
    //put your code here
}
?>
