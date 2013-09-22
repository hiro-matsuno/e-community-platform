<?php

//-----------------------------------------------------
// * レイアウト変更中であるかどうか
//-----------------------------------------------------
function is_layoutmode() {
	global $COMUNI;
	if (isset($COMUNI["layoutmode"])) {
		return $COMUNI["layoutmode"];
	}
	return false;
}

//-----------------------------------------------------
// * テンプレート直指定で表示
//-----------------------------------------------------
function print_page($tpl, $data) {
	$s = new Smarty;

	$s->template_dir = dirname(__FILE__);
	$s->compile_dir  = dirname(__FILE__). CONF_SMARTY_COMPILE;
	$s->config_dir   = dirname(__FILE__). CONF_SMARTY_CONFIG;
	$s->cache_dir    = dirname(__FILE__). CONF_SMARTY_CACHE;

	$s->caching = false;
	$s->compile_check = true;

	foreach ($data as $key => $val) {
		$s->assign($key, $val);
	}

	$s->display($tpl);
}

//-----------------------------------------------------
// * 主カラムのみを表示 (暫定対応時の処理)
//-----------------------------------------------------
function show_1page($param = array()) {
	global $COMUNI;

	$COMUNI["columns"] = 1;
	$data = array('space_1' => array(array('id'      => 0,
										   'title'   => $param["title"],
										   'content' => $param["content"])));

	show_page(0, $data);

	exit(0);
}

//-----------------------------------------------------
// * 記事編集時のページ表示
//-----------------------------------------------------
function show_input($param = array()) {
	global $COMUNI;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

	$id = ($eid == 0) ? $pid : $eid;

	if ($id == 0) {
		$id = get_eid();
	}

	$COMUNI['edit_mode'] = true;

	$data = array();
	$data['space_1'] = array(array('id'      => 1,
								   'title'   => $param["title"],
								   'content' => $param["content"]));
	if ($id > 0 or $COMUNI['manager_mode']) {
		$data['space_2'] = array(array('id'      => 2,
									   'title'   => '編集メニュー',
									   'content' => edit_menu($id)));
	}
	else {
		$data['space_2'] = array(array('id'      => 2,
									   'title'   => 'メインメニュー',
									   'content' => global_menu($id)));
	}

	show_page(get_site_id($id), $data);

	exit(0);
}

//-----------------------------------------------------
// * ページ ID からページ名を取得
//-----------------------------------------------------
function get_site_name($site_id) {
	global $SYS_SITE_NAME;

	if ($SYS_SITE_ID[$id]) {
		return $SYS_SITE_ID[$id];
	}

	$d = mysql_uniq("select * from page where id = %s", mysql_num($site_id));
	if ($d) {
		$SYS_SITE_NAME[$site_id] = $d["sitename"];
		return $d["sitename"];
	}
	else {
		$d = mysql_uniq("select * from page where id = %s", mysql_num($site_id));
		if ($d) {
			$SYS_SITE_NAME[$site_id] = $d["sitename"];
			return $d["sitename"];
		}
	}
	return '[サイト不明:'. $site_id. ']';
}

//-----------------------------------------------------
// * ページの表示
//-----------------------------------------------------
// $eidをsite_idに限定する
function show_page($eid = 0, $data = array()) {
	global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW, $COMUNI_HEAD_CSS, $COMUNI_DEBUG, $COMUNI_TPATH;
	global $COMUNI_ONLOAD;
	global $JQUERY;
	global $COMUNI_HEAD_CSSRAW;
	global $JQUERY, $COMUNI_FOOT_JS, $COMUNI_FOOT_JSRAW;
	global $COMUNI_FOOT_HTML;

	//	$uid      = isset($COMUNI["uid"]) ? $COMUNI["uid"];
	$visible  = false;
	$is_owner = false;

	// コンテンツの所有チェック
	$owner = array();
	if ($eid == 0) {
		$is_0eid = true;
		$owner["uid"] = 0;
		$owner["gid"] = portal_gid();
		$eid = get_eid(array('gid' => $owner["gid"]));
	}
	else {
		$o = mysql_uniq("select * from owner where id = %s", mysql_num($eid));
		if (!$o) {
			die('cannot find owner information...');
		}
		else {
			$owner["uid"] = $o["uid"];
			$owner["gid"] = $o["gid"];
		}
	}
	// 管理者権限チェック
/*
	if (is_su($uid)) {
		$is_owner = true;
		$visible  = true;
	}
*/
	if (is_owner($eid) == true) {
		$is_owner = true;
		$visible  = true;
	}
	// 閲覧権限チェック
	if (check_pmt($eid) == true) {
		$visible = true;
	}
	if ($visible == false) {
		if(!$is_0eid)
		    die('Permission denied..'. $eid);
	}

	// ページ情報・スキン・レイアウトの選択
	if ($owner["gid"] > 0) {
		$p = mysql_uniq('select g.* from page as g'.
						' where g.gid = %s',
		mysql_num($owner["gid"]));
	}
	else {
		$p = mysql_uniq('select m.* from page as m'.
						' where m.uid = %s',
		mysql_num($owner["uid"]));
	}
	if (!$p) {
		$p = array('skin' => 1, 'layout' => 3);
	}

	if (isset($p["skin"])) {
		$skin = mysql_uniq("select * from theme_skin where id = %s;",
							 mysql_num($p["skin"]));

		if ($skin) {
			$skin_filename = $skin["filename"];
			$p['layout'] = $skin['layout_id'];
		}
	}
	else {
		die('theme_skin not selected.');
	}

	if (isset($COMUNI['columns'])) {
		$layout = mysql_uniq("select * from theme_layout where id = %s;",
							 mysql_num($COMUNI['columns']));

		if ($layout) {
			$layout_filename = $layout["filename"];
		}
	}
	else if (isset($p["layout"])) {
		$layout = mysql_uniq("select * from theme_layout where id = %s;",
							 mysql_num($p["layout"]));

		if ($layout) {
			$layout_filename = $layout["filename"];
		}
	}
	if (!$layout_filename) {
		die('theme_layout not selected.');
	}

	if (isset($COMUNI['edit_mode']) && ($COMUNI['edit_mode'] == true)) {
		$skin_filename   = 'edit';
		$layout_filename = '2column_840';
//		$p['sitename'] = CONF_SITENAME;
		$COMUNI_TPATH[] = array('name' => '編集画面');
	}

	$tplfile = "";
	file_exists( dirname(__FILE__)."/../".( $tplfile = 'skin/'.$skin_filename.'/'.$skin_filename.'.tpl' ) )
	or file_exists( dirname(__FILE__)."/../".( $tplfile = 'skin/'.$skin_filename.'.tpl' ) )
	or file_exists( dirname(__FILE__)."/../".( $tplfile = 'skin/e-community_blue_3c.tpl' ) );

	$cssfile = "";
	file_exists( dirname(__FILE__)."/../".( $cssfile = 'skin/'.$skin_filename.'/'.$skin_filename.'.css' ) )
	or file_exists( dirname(__FILE__)."/../".( $cssfile = 'skin/'.$skin_filename.'.css' ) )
	or file_exists( dirname(__FILE__)."/../".( $cssfile = 'skin/e-community_blue_3c.css' ) );
	$cssfile = "/".$cssfile;

/* 新仕様 */

	// テンプレート読み込み
	$smarty = new Smarty;

	$smarty->template_dir = CONF_BASEDIR;
	$smarty->compile_dir  = CONF_BASEDIR. '/'. CONF_SMARTY_COMPILE;
	$smarty->config_dir   = CONF_BASEDIR. '/'. CONF_SMARTY_CONFIG;
	$smarty->cache_dir    = CONF_BASEDIR. '/'. CONF_SMARTY_CACHE;

	$smarty->caching = false;
	$smarty->compile_check = true;

	if ($is_owner == true) {
		$smarty->assign('is_owner', true);
	}

	if ($data) {
		foreach ($data as $key => $val) {
			$smarty->assign($key, stripslashes_deep($val));
		}
	}

	switch (force_layout()) {
		case 1:
			$layout_filename = '1column_free';
		break;
		default:
			;
	}

	$smarty->assign('eid', $eid);

	$contents = null;

	//	インフォブロック上部を配置
	if ( isset( $data['space_0'] ) and is_array($data['space_0']) ) {
	foreach ( $data['space_0'] as $info ) {

		$contents .= "<div style=\"padding:2px 14px 6px 14px;\">{$info['content']}</div>";

	}
	}

	$contents .= $smarty->fetch('layout/'. $layout_filename. '.tpl');

	//	インフォブロック下部を配置
	if ( isset( $data['space_4'] ) and is_array($data['space_4']) ) {
	foreach ( $data['space_4'] as $info ) {

		$contents .= "<div style=\"padding:6px 14px 2px 14px;clear:both;\">{$info['content']}</div>";

	}
	}

	$smarty->clear_all_assign();

	$smarty->assign('eid', $eid);

	if ($is_owner == true) {
		$smarty->assign('is_owner', true);
	}

	$COMUNI_HEAD_JS[] = '/js/ecom.js.php';
	$COMUNI_HEAD_JS[] = '/js/jquery.droppy.js';
	$COMUNI_HEAD_CSS[] = '/css/droppy.css';
	$COMUNI_HEAD_JS[] = '/js/ui/ui.sortable.js';
	$COMUNI_HEAD_JS[] = '/layout.js';
	$JQUERY["ready"][] = "new BlockMenu();";

	$urlBase = basename( $_SERVER['REQUEST_URI'] );

	//	レイアウトモードで無くても、ページの管理権限を持っていればレイアウト可能.
	if ( $is_owner == true
		and ( preg_match( "/^(?:user.php|group.php|index.php)?(?:\?.*)?$/", $urlBase )
			and !preg_match( "/module=/", $urlBase ) ) ) { /*&& is_layoutmode()) {*/
		$JQUERY["ready"][] = "new Layout( $eid );";
//		$smarty->assign('setting_layout', true);
	}

	EcomGlobal::addHeadJs( "/js/jquery.lightbox-0.5.min.js" );
	EcomGlobal::addHeadCss( "/css/jquery.lightbox-0.5.css" );

	//	jquery.lightbox
	$JQUERY["ready"][] = <<<__JS_CODE__

	jQuery(".box_main").each( function() {
		jQuery(".lightbox_a",this).lightBox({fixedNavigation:true});
	} );
	
__JS_CODE__;


	// スキンの反映
	$JQUERY_FIXED = array('ready' => array());
	foreach ($JQUERY["ready"] as $line) {
		$JQUERY_FIXED["ready"][] = str_replace('$(', 'jQuery(', $line);
	}

	$smarty->assign('jquery_ready_script', $JQUERY_FIXED["ready"]);
//	$smarty->assign('jquery_ready_script', $JQUERY["ready"]);

	if (is_login()) {
		$smarty->assign('is_login', true);
		$mymenu[] = array('title' => 'マイページ', 'url' => '/mypage.php', 'jump' => 1);
		$mymenu[] = array('title' => '個人設定', 'url' => '/setting.php?uid='. $COMUNI["uid"]);
		$mymenu[] = array('title' => 'フレンドリスト', 'url' => '/friend.php?uid='. $COMUNI["uid"]);
		$mymenu[] = array('title' => 'ファイル倉庫', 'url' => '/filebox.php?uid='. $COMUNI["uid"]);
		$mymenu[] = array('title' => 'ログアウト', 'url' => '/logout.php?ref='.urlencode($_SERVER["REQUEST_URI"]), 'jump' => 1);
	}
	if ($is_owner == true) {
		$sitemenu[] = array('title' => 'スキン変更', 'url' => '/skin.php?mode=skin&eid='. $eid);
		$sitemenu[] = array('title' => 'レイアウト変更', 'url' => '/layout.php?eid='. $eid, 'jump' => 1);
	}

	if (isset($p["sitename"])) {
		$smarty->assign('page_title', $p["sitename"]);
	}
	if (isset($p["description"])) {
		$smarty->assign('description', $p["description"]);
	}
	$smarty->assign('page_url', home_url($eid));

	$topic_path[] = array('title' => CONF_SITENAME, 'url' => '/index.php');
	if (isset($COMUNI['manager_mode']) && ($COMUNI['manager_mode'] == true)) {
		$smarty->assign('site_name', CONF_SITENAME);
		$topic_path[] = array(title => '管理者用ツール');
	}
	else if (isset($p["sitename"])) {
		if (is_portal($owner['gid'])) {
			$smarty->assign('site_name', CONF_SITENAME);
		}
		else {
			$smarty->assign('site_name', $p["sitename"]);
		}
		$topic_path[] = array('title' => $p["sitename"], 'url' => home_url($eid));
	}
	else {
		$smarty->assign('site_name', CONF_SITENAME);
	}
	if ($COMUNI_TPATH) {
		foreach ($COMUNI_TPATH as $tpath) {
			$topic_path[] = array(title => $tpath["name"], url => $tpath["url"]);
		}
	}

	if ($owner['gid'] > 0 && !is_portal($owner['gid'])) {
		$smarty->assign('is_group', true);
	}

	$smarty->assign('topic_path', $topic_path);

	$COMUNI_HEAD_CSS[] = '/css/ui.all.css';
	$COMUNI_HEAD_CSS[] = '/common/css.php?id='. $p['skin'];
	$COMUNI_HEAD_CSS[] = $cssfile;
	$COMUNI_HEAD_CSS[] = '/layout/'. $layout_filename. '.css';
	$COMUNI_HEAD_CSS[] = '/css/jquery.contextMenu.css';

	/*** jQueryのロード ***/
	array_unshift($COMUNI_HEAD_JS, '/js/jquery.contextMenu.js');
	array_unshift($COMUNI_HEAD_JS, '/js/jquery.pager.js');
	array_unshift($COMUNI_HEAD_JS, '/js/jquery-ui-1.6.custom.min.js');
	array_unshift($COMUNI_HEAD_JS, '/js/jquery-1.2.6.min.js');

	if (count($COMUNI_ONLOAD) > 0) {
		$inbodytag = 'onload="'. join(';', $COMUNI_ONLOAD). ';"';
		if (is_usemap()) {
			$inbodytag .= 'onunload="GUnload();"';
		}
		$smarty->assign('inbodytag', $inbodytag);
	}

	/*** thickboxのロード ***/
	$COMUNI_HEAD_CSS[] = '/css/thickbox.css';
	$COMUNI_HEAD_JS[] = '/js/thickbox.js';

	/*** ログインしていたら。 ***/
	if (is_login()) {
		$smarty->assign('is_login', true);
		$smarty->assign('uid', $owner["uid"]);
		$smarty->assign('gid', $owner["gid"]);
		$smarty->assign('nickname', $COMUNI["nickname"]);
		$smarty->assign('mymenu', $mymenu);
	}

	if (is_usemap()) {
		$smarty->assign('gmap', true);
		array_unshift($COMUNI_HEAD_JS, '/map_func.js');
		array_unshift($COMUNI_HEAD_JS, '/wms-gs.js');
		array_unshift($COMUNI_HEAD_JS, '/geoxmlfull.js');
		array_unshift($COMUNI_HEAD_JS, 'http://maps.google.co.jp/maps?file=api&hl=ja&v=2&amp;key='. CONF_GMAP_KEY);
	}

	$smarty->assign('menubar', get_menubar(0));

	$smarty->assign('head_js', $COMUNI_HEAD_JS);
	$smarty->assign('head_jsraw', $COMUNI_HEAD_JSRAW);
	$smarty->assign('head_css', $COMUNI_HEAD_CSS);
	$smarty->assign('head_cssraw', $COMUNI_HEAD_CSSRAW);

	$COMUNI_FOOT_JS[] = '/js/url_breaker_plus.user.js';

	$smarty->assign('foot_js', $COMUNI_FOOT_JS);
	if (count($COMUNI_FOOT_JSRAW) > 0) {
		$smarty->assign('foot_jsraw', join("\t\n", $COMUNI_FOOT_JSRAW));
	}
	if (count($COMUNI_FOOT_HTML) > 0) {
		$smarty->assign('foot_html', $COMUNI_FOOT_HTML);
	}
	$smarty->assign('contents', $contents);

	if (isset($COMUNI["is_top"]) && ($COMUNI["is_top"] == true)) {
		$smarty->assign('is_top', true);
	}

	$smarty->assign('footer', 'Copyright &copy; '. CONF_SITENAME);

//	$COMUNI_DEBUG[] = 'UID: '. $COMUNI["uid"];


	$smarty->assign('debug', $COMUNI_DEBUG);

	$smarty->display($tplfile);

	exit(0);
}

//-----------------------------------------------------
// * 強制的にレイアウトを 1 カラムに変更するかどうか
//-----------------------------------------------------
function force_layout() {
	global $SYS_FORCE_LAYOUT;

	if (isset($SYS_FORCE_LAYOUT) && $SYS_FORCE_LAYOUT > 0) {
		return $SYS_FORCE_LAYOUT;
	}
	else {
		return 0;
	}
}

//-----------------------------------------------------
// * 強制的にレイアウトを 1 カラムに変更
//-----------------------------------------------------
function set_layout($column = 0) {
	global $SYS_FORCE_LAYOUT;

	$SYS_FORCE_LAYOUT = $column;
}


//-----------------------------------------------------
// * ページ名の取得
//-----------------------------------------------------
function get_sitename($eid = 0) {
	global $SITENAME;

	if (!isset($SITENAME)) {
		$SITENAME = array();
	}
	if (!isset($SITENAME[$eid])) {
		$SITENAME[$eid] = get_site_name(get_site_id($eid));
	}

	return $SITENAME[$eid];
}

//-----------------------------------------------------
// * グループ ID、ユーザー ID からページ ID を取得
// * eコミ整理時に page で統一されたためもっと簡単に取得できます。
//-----------------------------------------------------
function get_eid($param = array()) {
	global $SYS_VIEW_GID, $SYS_VIEW_UID;

	if (!isset($param['gid']) && !isset($param['uid'])) {
		$param['gid'] = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;
		if ($param['gid'] == 0) {
			unset($param['gid']);
			$param['uid'] = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;
		}
	}

	if (isset($param['gid'])) {
		$SYS_VIEW_GID = $param['gid'];
		return get_eid_by_group($param['gid']);
	}
	if (isset($param['uid'])) {
		$SYS_VIEW_UID = $param['uid'];
		return get_eid_by_mypage($param['uid']);
	}
	return 0;
}

//-----------------------------------------------------
// * グループ ID からページ ID を取得
//-----------------------------------------------------
function get_eid_by_group($gid = null) {
	global $SYS_CACHE;

	if (isset($SYS_CACHE['get_eid_by_group'][$gid])) {
		return $SYS_CACHE['get_eid_by_group'][$gid];
	}

	if (!$gid) {
		$gid = portal_gid();
	}

	$q = mysql_uniq('select id from page where gid = %s', mysql_num($gid));

	if ($q) {
		$SYS_CACHE['get_eid_by_group'][$gid] = $q['id'];
		return $SYS_CACHE['get_eid_by_group'][$gid];
	}

	return 0;
}

//-----------------------------------------------------
// * ユーザー ID からページ ID を取得
//-----------------------------------------------------
function get_eid_by_mypage($uid = null) {
	global $SYS_CACHE;

	if (isset($SYS_CACHE['get_eid_by_mypage'][$uid])) {
		return $SYS_CACHE['get_eid_by_mypage'][$uid];
	}

	if (!$uid) {
		$uid = myuid();
	}

	$q = mysql_uniq('select id from page where uid = %s', mysql_num($uid));

	if ($q) {
		$SYS_CACHE['get_eid_by_mypage'][$uid] = $q['id'];
		return $SYS_CACHE['get_eid_by_mypage'][$uid];
	}

	return 0;
}

//-----------------------------------------------------
// * 特定のカラムのみを表示 (未使用)
//-----------------------------------------------------
function show_content($param = array()) {
	$data = get_space_data($param['eid'], array(2, 3));

	$data = array(space_1 => array(array(id      => $param['eid'],
										title   => $param['title'],
										content => $param['content'])));

	show_page($param['eid'], $data);
}

//-----------------------------------------------------
// * 特定のカラムのみを表示 (未使用)
//-----------------------------------------------------
function get_space_data($eid = null, $ids = array()) {
	;
}

//-----------------------------------------------------
// * マップ表示を有効にする
//-----------------------------------------------------
function use_map() {
	global $COMUNI;
	$COMUNI['use_map'] = true;
}

//-----------------------------------------------------
// * マップ表示が有効かどうか
//-----------------------------------------------------
function is_usemap() {
	global $COMUNI;
	if (isset($COMUNI['use_map'])) {
		return $COMUNI['use_map'];
	}
	return false;
}

//あしあと
//function is_logging() {
//	return isset($_SESSION['_allow_logging']) ? $_SESSION['_allow_logging'] : true;
//}

//-----------------------------------------------------
// * パーツを非表示にする
//-----------------------------------------------------
function hide_block($id = 0) {
	global $SYS_HIDDEN_BLOCK;
	if ($id == 0) {
		return;
	}
	$SYS_HIDDEN_BLOCK[$id] = true;
}


//-----------------------------------------------------
// * メインメニューの生成
//-----------------------------------------------------
function global_menu($id = null) {
	global $COMUNI;
	$global_menu = array();

	$global_menu[] = array('name' => CONF_SITENAME, 'href' => '/index.php');

	return create_menu_div($global_menu);
}

//-----------------------------------------------------
// * 編集メニューの生成
//-----------------------------------------------------
function edit_menu($id) {
	global $COMUNI;

	$edit_menu = array();

	//管理者用メニュー
	if ($COMUNI['manager_mode'] == true) {
		include_once dirname(__FILE__).("/../manager/edit_menu.php");
		return create_menu_div($edit_menu);
	}

	if (is_portal(get_gid($id))) {
		$edit_menu[] = array(name => 'ポータルページに戻る', href => '/index.php', 'class'=>"portal");
	}
	else if (get_gid($id)) {
		$edit_menu[] = array(name => 'グループに戻る', href => '/group.php?gid='. get_gid($id));
	}
	else {
		$edit_menu[] = array(name => 'マイページに戻る', href => '/user.php?uid='. get_uid($id));
	}

	$site_id = get_site_id($id);

	$l = mysql_full("select * from block where pid = %s order by hpos",
					mysql_num($site_id));

	if ($l) {
		while ($b = mysql_fetch_array($l)) {
			@include_once dirname(__FILE__). '/../modules/'. $b['module']. '/config.php';
			$func_name_edit = 'mod_'. $b["module"]. '_block_config';
			if (function_exists($func_name_edit)) {
				$sub_menu = call_user_func_array($func_name_edit, array($b["id"]));
				if (count($sub_menu) > 0) 
					$edit_menu[] = array(id => $r['id'], name => $b["name"], sub => $sub_menu);
			}
		}
	}

	return create_menu_div($edit_menu);
}

//-----------------------------------------------------
// * 編集メニューのHTML生成
//-----------------------------------------------------
function create_menu_div($edit_menu = array()) {
	$menu = '';

	foreach ($edit_menu as $e) {
		$class = "edit_menu_title icon";
		if ($e['class']) $class .= " ".$e['class'];
		$menu .= '<div class="'.$class.'">'. make_href($e['name'], $e['href']). '</div>';
		if (isset($e['sub'])) {
			foreach ($e['sub'] as $s) {
				$class = "edit_menu_sub icon";
				if ($s['class']) $class .= " ".$s['class'];
				$menu .= '<div class="'.$class.'">'. make_href($s['title'], $s['url'], $s['inline']). '</div>';
			}
		}
	}
	return $menu;
}

//-----------------------------------------------------
// * ページ上部のメニューバーを取得
//-----------------------------------------------------
function get_menubar($page_id = 0) {
	global $COMUNI_HEAD_CSSRAW, $CURRENT_SITE_ID;

	$q = mysql_uniq('select * from menubar as m'.
					' inner join menubar_css as mc on m.menubar = mc.id'.
					' where m.id = %s',
					mysql_num(0));

	if ($q) {
		$COMUNI_HEAD_CSSRAW[] = $q['css'];
	}

	$url       = CONF_SITEURL;
	$sitename  = CONF_SITENAME;
	$menu_str  = menu_str();

	$html = <<<__HTML__
<div id="menubar">
<div id="menubar_logoimg"><a href="${url}"></a></div>
<div id="menubar_logotxt">${sitename}</div>
<div id="menubar_menutxt">
${menu_str}
</div>
</div>
<div id="menubar_clear"></div>
__HTML__;

	if (is_layoutmode()) {
		$href = array();
		$href['action']    = 'layout.php';
		$href['no_save']   = 'layout.php?nosave=1&eid='. $CURRENT_SITE_ID;
		$href['save']      = 'layout.php?save=1&eid='. $CURRENT_SITE_ID;
		$href['add_block'] = thickbox_href('add_block.php?eid='. $CURRENT_SITE_ID);

		$html .= <<<__HTML__
<div id="menu_layout">
  <form id="layout_setting" action="${href['action']}">
  <input type="hidden" name="save" value="1">
  <input type="hidden" name="eid" value="${CURRENT_SITE_ID}">
  <a href="${href['no_save']}" id="layout_no_save" title="保存しないで終了">保存しないで終了</a>
  <a href="${href['save']}" id="layout_save" title="保存して終了" onClick="return false;">保存して終了</a>
  <a href="${href['add_block']}" id="layout_add_block" title="パーツの追加" class="thickbox">パーツを追加</a>
  </form>
</div><!-- /#nav_admin -->
<div id="post_status"></div>
__HTML__;
		;
	}

	return $html;
}

//-----------------------------------------------------
// * ページ上部のメニューバーの文字列を取得
//-----------------------------------------------------
function menu_str() {
	global $SYS_VIEW_GID, $SYS_VIEW_UID;

	$ref = urlencode($_SERVER['REQUEST_URI']);

	if (is_login() == true) {
		$myuid = myuid();

		if ($SYS_VIEW_GID > 0) {
			$add_query = 'gid='. $SYS_VIEW_GID;
		}
		else {
			$add_query = 'uid='. $SYS_VIEW_UID;
		}

		$mbox = '';

		$m = mysql_uniq('select count(*) from message_data as n'.
						' where n.to_uid = %s and is_new = 1',
						mysql_num(myuid()));
		if ($m) {
			if ($m['count(*)'] > 0) {
				$mbox = make_href('新着メッセージがあります ('. $m['count(*)']. '件)', '/mbox.php?mode=new', true). ' - ';
			}
		}

		$me = User::getMe();

		$result = "${mbox}<a href=\"/mypage.php\">マイページ</a> -"
				."<a href=\"/favorite.php?${myuid}&keepThis=true&TB_iframe=true&height=480&width=640\" class=\"thickbox\" title=\"参加中のページ\">参加中のページ</a> -"
				."<a href=\"/setting.php?${add_query}&keepThis=true&TB_iframe=true&height=480&width=640\" class=\"thickbox\" title=\"各種設定\">各種設定</a> -"
				.( ( $me and $me->isAdmin() )
					? "<a href=\"/manager/system/default.php?${add_query}\" title=\"管理設定\">管理設定</a> -"
					: "" )
				."<a href=\"/logout.php?ref=".urlencode($_SERVER["REQUEST_URI"])."\">ログアウト</a>";

		return $result;
				
	}
	else {
		$href = make_href('ログイン', '/login.php?type=dialog&ref='. $ref, true);

		return <<<__MENU__
<a href="/regist.php">ユーザー登録</a> -
${href}
__MENU__;
	}
}

//-----------------------------------------------------
// * パーツ個別の編集メニューのリンクタグ生成
//-----------------------------------------------------
function main_edit_menu($eid = null, $menu = array()) {
	if (!is_array($menu)) return '';
	$href = '';
	foreach ($menu as $m => $v) {
		if ($href != '') {
			$href .= ' | ';
		}
		$href .= '<a href="'. $v["url"];
		if ($v["inline"] == true) {
			$href .= '&keepThis=true&TB_iframe=true&height=480&width=640" class="thickbox"';
		}
		else {
			$href .= '"';
		}
		$href .= '>'. $v["title"]. '</a>';
	}
	return <<<___EDIT_MENU___
<div style="text-align: right; display: none" class="edit_menu">
${href}
</div>
___EDIT_MENU___;
	;
}

//-----------------------------------------------------
// * パーツ個別の編集メニューのHTML生成
//-----------------------------------------------------
function block_edit_menu($eid = null, $menu = array(), $unit=null ) {
	if (is_poweruser($eid)) {
		$menu[] = array(title => '表示設定', url => '/block_setting.php?eid='. $eid, inline => true);
		$d = mysql_uniq("select del_lock from block where id = %s", mysql_num($eid));
		if ($d) {
			if(!$d['del_lock'])
				$menu[] = array(title => 'パーツ削除', url => '/del_block.php?eid='. $eid, inline => true);
		}
	}

	if ( null === $unit ) {
		//	@TODO del_lock と合わせて取得するようにリファクタリングする.
		$block = new Block( $eid );
		$unit = $block->getElement()->getUnit();
	}

	$href = '';

	switch ( $unit ) {

	case Permission::PMT_BROWSE_PUBLIC:
		$href .= '<div class="ecom_block_pmt_icon ecom_block_pmt_public"></div>';
		break;

	case Permission::PMT_BROWSE_FOR_AUTHORIZED:
		$href .= '<div class="ecom_block_pmt_icon ecom_block_pmt_authorized"></div>';
		break;

	case Permission::PMT_BROWSE_PRIVATE:
		$href .= '<div class="ecom_block_pmt_icon ecom_block_pmt_private"></div>';
		break;

	default:
		{
		
			$result = MySqlPlaneStatement
				::execNow("select gid from friend_user where gid=$unit");

			if ( 0 < mysql_num_rows( $result->getResult() ) ) {
				$href .= '<div class="ecom_block_pmt_icon ecom_block_pmt_friend"></div>';
			} else {
				$href .= '<div class="ecom_block_pmt_icon ecom_block_pmt_group"></div>';
			}

		}
		break;

	}

	if (is_array($menu)) {
	foreach ($menu as $m => $v) {
		if ($href != '') {
			$href .= ' | ';
		}
		$href .= '<a href="'. $v["url"];
		if ($v["inline"] == true) {
			$href .= '&keepThis=true&TB_iframe=true&height=480&width=640" class="thickbox"';
		}
		else {
			$href .= '"';
		}
		$href .= '>'. $v["title"]. '</a>';
	}
	}
	return <<<___EDIT_MENU___
<div style="text-align: right; display: none" class="edit_menu">
${href}
</div>
___EDIT_MENU___;
	;
}

?>
