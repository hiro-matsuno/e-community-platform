<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

define('SES_NAME', 'mod_sites');

/* 振り分け*/
kick_guest();

list($eid, $pid) = array(0, 0);

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	break;
	case 'modify':
		modify_data($eid, $pid);
	break;
	case 'edit':
		edit_data($eid, $pid);
	break;
	case 'confirm':
		confirm_data($eid, $pid);
	break;
	default:
		;
}
input_data($eid, $pid);

/* modify */
function modify_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$eid       = isset($_POST['modify_eid']) ? intval($_POST['modify_eid']) : 0;
	$gid       = isset($_POST['modify_gid']) ? intval($_POST['modify_gid']) : 0;
	$sitename  = isset($_POST['sitename']) ? strip_tags($_POST['sitename']) : '';
	$site_desc = isset($_POST['description']) ? strip_tags($_POST['description']) : '';

	if ($sitename == '') {
		$SYS_FORM['error']['sitename'] = 'サイト名を入力して下さい。';
	}

	$u = mysql_exec('update page set sitename = %s, description = %s'.
					' where gid = %s',
					mysql_str($sitename), mysql_str($site_desc), mysql_num($gid));
	
//	set_keyword($eid, null, $_SESSION[SES_NAME]['tag']);
	set_pmt(array(eid => $eid, gid =>$gid, unit => $_SESSION[SES_NAME]['pmt']));

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "group_update", array( $gid ) );

	$html = '設定を変更しました。';
	$data = array(title   => 'グループページ基本設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	unset($_SESSION[SES_NAME]);

	show_input($data);

	exit(0);
}

/* edit */
function edit_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$gid = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;

	if ($gid == 0) {
		show_error('グループIDが不明です。');
	}

	$g = mysql_uniq('select g.* from page as g'.
					' where g.gid = %s',
					mysql_num($gid));
	
	$eid = $g['id'];

	if (!is_owner($eid)) {
		show_error('管理者ではありません。');
	}

	$sitename     = $g['sitename'];
	$description  = $g['description'];

	// header
	$SYS_FORM["header"][] = 'グループページの名前を説明を変更します。';

	// hidden:action
	$attr = array(name => 'action', value => 'modify');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

	// hidden:action
	$attr = array(name => 'modify_gid', value => $gid);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	// hidden:action
	$attr = array(name => 'modify_eid', value => $eid);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:sitename
	$attr = array(name => 'sitename', value => $sitename, size => 64);
	$SYS_FORM["input"][] = array(title => 'サイト名',
								 name  => 'sitename',
								 body  => get_form("text", $attr));

	// textarea:description
	$attr = array(name => 'description', value => $description, rows => 4);
	$SYS_FORM["input"][] = array(title => 'サイトの説明',
								 name  => 'description',
								 body  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'group_profile.php';
	$SYS_FORM["method"] = 'POST';

//	$SYS_FORM["keyword"] = true;
	$SYS_FORM["pmt"] = true;

	$SYS_FORM["submit"] = '設定の変更';

	$html = create_form(array(eid => $eid));

	$data = array(title   => '設定の変更',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

/* 
 * regist_data
 */
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$tmpl = $_SESSION[SES_NAME]['tmpl'];

	$p = mysql_uniq(" select id from theme_skin where filename='e-community_blue_2c'");
	if($p)$skin_2col = $p['id'];
	else $skin_2col = 30;

	$p = mysql_uniq(" select id from theme_skin where filename='e-community_blue_3c'");
	if($p)$skin_3col = $p['id'];
	else $skin_3col = 31;

	switch($tmpl){
		case -2:
			$skin = $skin_2col;
			break;
		case -3:
			$skin = $skin_3col;
			break;
		default:
		$skin = $skin_3col;
		$p = mysql_uniq("select * from site_template where id=%s",mysql_num($tmpl));
		if($p){
			$tmpl_uid = $p['uid'];
			$tmpl_gid = $p['gid'];
			$q = mysql_uniq("select * from page where gid = %s",mysql_num($tmpl_gid));
			if($q)$skin = $q['skin'];
		}
	}

//	$skin = $_SESSION[SES_NAME]['skin'];
//
//	$s = mysql_uniq('select s.* from theme_skin as s'.
//					' where s.id = %s',
//					mysql_num($skin));
//
//	if ($s) {
//		$layout = $s['layout_id'];
//		$l = mysql_uniq('select * from theme_layout'.
//						' where id = %s',
//						mysql_num($layout));
//
//		if ($l) {
//			$column = $l['column'];
//		}
//	}

	$new_id  = get_seqid();
	$new_gid = get_seqid('group');

	$uid = myuid();
	$enable = 1;

	$f = mysql_exec("insert into page (gid, uid, id, sitename, description, skin, enable, initymd)".
					" values (%s, %s, %s, %s, %s, %s, %s, %s);",
					mysql_num($new_gid), mysql_num(0), mysql_num($new_id), 
					mysql_str($_SESSION[SES_NAME]['sitename']), mysql_str($_SESSION[SES_NAME]['description']),
					mysql_num($skin), mysql_num($enable),
					mysql_current_timestamp());
	
	if (!$f) {
		die(mysql_error());
	}

	create_friend_group($new_gid);

	$q = mysql_exec("insert into group_joinable".
					" (gid, type, ent_max)".
					" values (%s, %s, %s)",
					mysql_num($new_gid),
					mysql_num($_SESSION[SES_NAME]["joinable"]),
					mysql_num(1000));

	join_group(array(gid   => $new_gid,
					 uid   => $uid,
					 level => 100));

	set_pmt(array(eid  => $new_id,
				  uid  => $uid,
				  gid  => $new_gid,
				  unit => $_SESSION[SES_NAME]['pmt']));

/* 初期配置 */
//	switch($column) {
//		case "2":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,schedule,glist,blog_archive,search';
//			break;
//		case "3":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'profile,glist,blog_archive';
//			$TPL[3] = 'login,schedule,search';
//			break;
//		case "5":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'profile,menu,glist,blog_archive,rss';
//			$TPL[3] = 'login,schedule,rss';
//			break;
//		default:
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,schedule,blog_archive';
//	}

	$f = mysql_exec("delete from block where pid = %s", mysql_num($new_id));

	if($tmpl>0){
		$p = mysql_full("select * from block inner join page on block.pid=page.id".
						" where page.gid = %s",mysql_num($tmpl_gid));
		while($b = mysql_fetch_assoc($p)){
			$new_block_id = get_seqid();
			$r = mysql_exec("insert into block (id, pid, module, name, vpos, hpos, del_lock)".
							" values (%s, %s, %s, %s, %s, %s, %s);",
							mysql_num($new_block_id),mysql_num($new_id),
							mysql_str($b['module']),mysql_str($b['name']),
							$b['vpos'],$b['hpos'],$b['del_lock']);
			set_pmt(array(eid  => $new_block_id,
						  uid  => $uid,
						  gid  => $new_gid,
						  unit => get_pmt($b['id'])));
		}
	}else{
		if($tmpl == -2){
			$TPL[1] = 'blog';
			$TPL[2] = 'login,profile,schedule,glist,blog_archive,search';
		}else{
			$TPL[1] = 'blog';
			$TPL[2] = 'profile,glist,blog_archive';
			$TPL[3] = 'login,schedule,search';
		}
		foreach ($TPL as $vpos => $mods) {
			if ($vpos > 0) {
				$modules = split(',', $mods);
				$hpos = 0;
				foreach ($modules as $module) {
					$name = get_module_name($module);
					$new_block_id = get_seqid();
					if($module == 'profile' or $module == 'login' or $module == 'glist')
						$del_lock = 1;
					else
						$del_lock = 0;
					$r = mysql_exec("insert into block (id, pid, module, name, vpos, hpos, del_lock) values (%s, %s, %s, %s, %s, %s, %s);",
									mysql_num($new_block_id), mysql_num($new_id), mysql_str($module),
									mysql_str($name), mysql_num($vpos), mysql_num($hpos), mysql_num($del_lock));
					if (!$r) { die('error...'. "${id}/${uid}/${vpos}/${hpos}"); }
	
					if($module == 'glist')
						$unit = PMT_MEMBER;
					else
						$unit = 0;
					set_pmt(array(eid  => $new_block_id,
								  uid  => myuid(),
								  gid  => $new_gid,
								  unit => $unit));
	
					$hpos++;
				}
			}
		}
	}

//	foreach ($TPL as $vpos => $mods) {
//		if ($vpos > 0) {
//			$modules = split(',', $mods);
//			$hpos = 0;
//			foreach ($modules as $module) {
//				$name = get_module_name($module);
//				$new_block_id = get_seqid();
//				$r = mysql_exec("insert into block (id, pid, module, name, vpos, hpos) values (%s, %s, %s, %s, %s, %s);",
//								mysql_num($new_block_id), mysql_num($new_id), mysql_str($module),
//								mysql_str($name), mysql_num($vpos), mysql_num($hpos));
//				if (!$r) { die('error...'. "${id}/${uid}/${vpos}/${hpos}"); }
//
//				if($module == glist)
//					$unit = PMT_MEMBER;
//				else
//					$unit = 0;
//				set_pmt(array(eid  => $new_block_id,
//							  uid  => myuid(),
//							  gid  => $new_gid,
//							  unit => $unit));
//
//				$hpos++;
//			}
//		}
//	}

//	set_keyword($new_id, null, $_SESSION[SES_NAME]['tag']);
	set_pmt(array(eid => $new_id, gid =>$new_gid, unit => $_SESSION[SES_NAME]['pmt']));

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "group_insert", array( $new_gid ) );

	$html = 'グループページを作りました。';
	$data = array(title   => 'グループページ作成完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($new_id))));

	unset($_SESSION[SES_NAME]);

	show_input($data);

	exit(0);
}

function confirm_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$ses_data = array();
	$ses_data['sitename']    = htmlesc($_POST['sitename']);
	$ses_data['description'] = nl2br(htmlesc($_POST['description']));
	$ses_data['joinable'] = intval($_POST['joinable']);
	$ses_data['tmpl']     = intval($_POST['tmpl']);
	$ses_data['tag']      = $_POST['tag_0_i'];
	$ses_data['pmt']      = $_POST['pmt_0'];

	$_SESSION[SES_NAME] = $ses_data;

	if ($ses_data['sitename'] == '') {
		$SYS_FORM['error']['sitename'] = 'サイト名は入れてください。';
	}
	if (isset($SYS_FORM['error'])) {
		return;
	}

	switch($ses_data['pmt']) {
		case "2":
			$pmt = '非公開';
			break;
		case "1":
			$pmt = '登録ユーザーのみ';
			break;
		case "0":
			$pmt = 'インターネット';
			break;
		case "3":
			$pmt = 'フレンドリスト';
			break;
		default:
			$pmt = '非公開';
	}

	switch($ses_data["joinable"]) {
		case "2":
			$joinable = '参加不可能';
			break;
		case "1":
			$joinable = '管理者の承認が必要';
			break;
		case "0":
			$joinable = '誰でも参加可能';
			break;
		default:
			$joinable = '参加不可能';
	}

	$tmpl = $ses_data['tmpl'];

	switch($tmpl){
		case -2:
			$tmpl_name = 	'グループページ用標準２カラム';
		break;			
		case -3:
			$tmpl_name = 	'グループページ用標準３カラム';
		break;			
		default:
			$s = mysql_uniq('select s.* from site_template as s'.
							' where s.id = %s',
							mysql_num($tmpl));
		
			$tmpl_name = $s['name'];
	}
	
	if ($ses_data['tag'] == '') {
		$tag = '(無し)';
	}
	else {
		$tag = $ses_data['tag'];
	}

	if (isset($_SESSION[SES_NAME])) {
		$sitename    = $_SESSION[SES_NAME]['sitename'];
		$description = $_SESSION[SES_NAME]['description'];
	}
	else {
		$sitename    = '';
		$description = '';
	}

	// text:sitename
	$SYS_FORM["header"][] = '下記の内容をご確認下さい。';

	// hidden:regist
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

	// text:sitename
	$SYS_FORM["input"][] = array(title => 'サイト名',
								 name  => 'sitename',
								 body  => $ses_data['sitename']);
	$SYS_FORM["input"][] = array(title => 'サイトの説明',
								 name  => 'description',
								 body  => $ses_data['description']);
	$SYS_FORM["input"][] = array(title => '参加形態',
								 name  => 'joinable',
								 body  => $joinable);
	$SYS_FORM["input"][] = array(title => 'ページテンプレート',
								 name  => 'tmpl',
								 body  => $tmpl_name);
	$SYS_FORM["input"][] = array(title => '登録キーワード',
								 name  => 'tag',
								 body  => $tag);
	$SYS_FORM["input"][] = array(title => '公開範囲',
								 name  => 'pmt',
								 body  => $pmt);

	$SYS_FORM["action"] = 'group_profile.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'グループページを作成';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'グループページの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if (isset($_SESSION[SES_NAME])) {
		$sitename    = $_SESSION[SES_NAME]['sitename'];
		$description = strip_tags($_SESSION[SES_NAME]['description']);
		$joinable    = $_SESSION[SES_NAME]['joinable'];
		$tmpl        = $_SESSION[SES_NAME]['tmpl'];
	}
	else {
		$sitename    = '';
		$description = '';
		$joinable    = 0;
		$tmpl = -3;
	}

	$p = mysql_exec("select st.id, st.name from site_template as st".
					" inner join page on st.gid = page.gid".
					" where st.gid > 0 and st.type >= %s",(is_admin()? 0 : 1));
	$tmpl_opt = array();
	$tmpl_opt[-2] = 'グループページ用標準２カラム';
	$tmpl_opt[-3] = 'グループページ用標準３カラム';

	if ( false !== p ) {
		while($t = mysql_fetch_assoc($p)){
			$tmpl_opt[$t['id']] = $t['name'];
		}
	}

	// text:sitename
	$SYS_FORM["header"][] = 'グループページを作成するために、下記のフォームへ情報を入力してください。';

	// hidden:confirm
	$attr = array(name => 'action', value => 'confirm');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

	// text:sitename
	$attr = array(name => 'sitename', value => $sitename, size => 64);
	$SYS_FORM["input"][] = array(title => 'サイト名',
								 name  => 'sitename',
								 body  => get_form("text", $attr));

	// textarea:description
	$attr = array(name => 'description', value => $description, rows => 4);
	$SYS_FORM["input"][] = array(title => 'サイトの説明',
								 name  => 'description',
								 body  => get_form("textarea", $attr));

	// radio:joinable
	$option = array(0 => '誰でも参加OK', 1 => '承認制', 2 => '参加不可');
	$attr = array(name => 'joinable', value => $joinable, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => '参加形態',
								 name  => 'joinable',
								 body  => get_form("radio", $attr));

/*	// radio:skin
	$option = array(79 => '２カラム', 78 => '３カラム');
	$attr = array(name => 'skin', value => $skin, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => 'スキン',
								 name  => 'skin',
								 body  => get_form("radio", $attr));
*/
	$attr = array(name => 'tmpl', value => $tmpl, option => $tmpl_opt, break_num => 1);
	$body = get_form("radio", $attr);
	$SYS_FORM["input"][] = array(title => 'ページテンプレート',
								 name  => 'tmpl',
								 body  => $body);
/*
	// radio:layout
	$option = array(0 => 'ブログ中心', 1 => 'コミュニティ中心');
	$attr = array(name => 'layout', value => $layout, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => '初期パーツの配置',
								 name  => 'layout',
								 body  => get_form("radio", $attr));
*/
	$SYS_FORM["action"] = 'group_profile.php';
	$SYS_FORM["method"] = 'POST';

//	$SYS_FORM["keyword"] = true;
	$SYS_FORM["input"][] = array(title => CONF_PMT_TITLE,
								 body  => pmt_form_group());

	$SYS_FORM["submit"] = '確認画面へ進む';
	$SYS_FORM["cancel"] = '修正';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'グループページの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}
?>
