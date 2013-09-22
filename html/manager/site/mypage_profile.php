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
	$sitename  = isset($_POST['sitename']) ? strip_tags($_POST['sitename']) : '';
	$site_desc = isset($_POST['description']) ? strip_tags($_POST['description']) : '';

	if ($sitename == '') {
		$SYS_FORM['error']['sitename'] = 'サイト名を入力して下さい。';
	}

	$u = mysql_exec('update page set sitename = %s, description = %s'.
					' where id = %s',
					mysql_str($sitename), mysql_str($site_desc), mysql_num($eid));

//	set_keyword($eid, null, $_SESSION[SES_NAME]['tag']);
	set_pmt(array(eid => $eid, unit => $_SESSION[SES_NAME]['pmt']));

	$html = '設定を変更しました。';
	$data = array(title   => 'マイページ基本設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	unset($_SESSION[SES_NAME]);

	show_input($data);

	exit(0);
}

/* edit */
function edit_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;

	if ($uid == 0) {
		show_error('ユーザーIDが不明です。');
	}

	$m = mysql_uniq('select m.* from page as m'.
					' where m.uid = %s',
					mysql_num($uid));

	$eid = $m['id'];

	if (!is_owner($eid)) {
		show_error('管理者ではありません。');
	}

	$sitename     = $m['sitename'];
	$description  = $m['description'];

	// header
	$SYS_FORM["header"][] = 'マイページの名前を説明を変更します。';

	// hidden:action
	$attr = array(name => 'action', value => 'modify');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

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

	$SYS_FORM["action"] = 'mypage_profile.php';
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

	if (mysql_uniq('select * from page where uid= %s', mysql_num(myuid()))) {
		error_window('すでにマイページ作成済です');
	}

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
		$skin = $skin_2col;
		$p = mysql_uniq("select * from site_template where id=%s",mysql_num($tmpl));
		if($p){
			$tmpl_uid = $p['uid'];
			$tmpl_gid = $p['gid'];
			$q = mysql_uniq("select * from page where gid = %s",mysql_num($tmpl_gid));
			if($q)$skin = $q['skin'];
		}
	}

//	$s = mysql_uniq('select s.* from theme_skin as s'.
//					' where s.id = %s',
//					mysql_num($skin));

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
	$uid     = myuid();

	$f = mysql_exec("insert into page (id, uid, gid, sitename, description, skin, initymd)".
					" values (%s, %s, %s, %s, %s, %s, %s);",
					mysql_num($new_id), mysql_num($uid), mysql_num(0), 
					mysql_str($_SESSION[SES_NAME]['sitename']), mysql_str($_SESSION[SES_NAME]['description']),
					mysql_num($skin),
					mysql_current_timestamp());

	if (!$f) {
		die(mysql_error());
	}

	set_pmt(array(eid  => $new_id,
				  uid  => $uid,
				  unit => $_SESSION[SES_NAME]['pmt']));

/* 初期配置 */
//	switch($column) {
//		case "2":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,schedule,blog_archive,search';
//			break;
//		case "3":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,blog_archive';
//			$TPL[3] = 'schedule,search';
//			break;
//		case "5":
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,menu,blog_archive';
//			$TPL[3] = 'schedule';
//			break;
//		default:
//			$TPL[1] = 'blog';
//			$TPL[2] = 'login,profile,schedule,blog_archive';
//	}

	$f = mysql_exec("delete from block where pid = %s", mysql_num($new_id));

	if($tmpl>0){
		$p = mysql_full("select * from block inner join page on block.pid=page.id".
						" where page.uid = %s",mysql_num($tmpl_uid));
		while($b = mysql_fetch_assoc($p)){
			$new_block_id = get_seqid();
			$r = mysql_exec("insert into block (id, pid, module, name, vpos, hpos, del_lock)".
							" values (%s, %s, %s, %s, %s, %s, %s);",
							mysql_num($new_block_id),mysql_num($new_id),
							mysql_str($b['module']),mysql_str($b['name']),
							$b['vpos'],$b['hpos'],$b['del_lock']);
			set_pmt(array(eid  => $new_block_id,
						  uid  => myuid(),
						  unit => get_pmt($b['id'])));
		}
	}else{
		if($tmpl == -2){
			$TPL[1] = 'blog';
			$TPL[2] = 'login,profile,schedule,blog_archive,search';
		}else{
			$TPL[1] = 'blog';
			$TPL[2] = 'login,profile,blog_archive';
			$TPL[3] = 'schedule,search';
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

//	$f = mysql_exec("delete from block where pid = %s", mysql_num($new_id));
//
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
//				set_pmt(array(eid  => $new_block_id,
//							  uid  => myuid(),
//							  unit => 0));
//
//				$hpos++;
//			}
//		}
//	}

//	set_keyword($new_id, null, $_SESSION[SES_NAME]['tag']);
	set_pmt(array(eid => $new_id, unit => $_SESSION[SES_NAME]['pmt']));

	$html = 'マイページを作りました。';
	$data = array(title   => 'マイページ作成完了',
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

	$tmpl = $ses_data['tmpl'];

	switch($tmpl){
		case -2:
			$tmpl_name = 	'マイページ用標準２カラム';
		break;			
		case -3:
			$tmpl_name = 	'マイページ用標準２カラム';
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
	$SYS_FORM["input"][] = array(title => 'ページテンプレート',
								 name  => 'tmpl',
								 body  => $tmpl_name);
	$SYS_FORM["input"][] = array(title => '登録キーワード',
								 name  => 'tag',
								 body  => $tag);
	$SYS_FORM["input"][] = array(title => '公開範囲',
								 name  => 'pmt',
								 body  => $pmt);

	$SYS_FORM["action"] = 'mypage_profile.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'マイページを作成';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'マイページの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if (mysql_uniq('select * from page where uid= %s', mysql_num(myuid()))) {
		error_window('すでにマイページ作成済です');
	}

	if (isset($_SESSION[SES_NAME])) {
		$sitename    = $_SESSION[SES_NAME]['sitename'];
		$description = strip_tags($_SESSION[SES_NAME]['description']);
		$tmpl        = $_SESSION[SES_NAME]['tmpl'];
	}
	else {
		$sitename    = get_handle(myuid()). 'のマイページ';
		$description = '';
		$tmpl = -2;
	}
	
	$p = mysql_exec("select st.id, st.name from site_template as st".
					" inner join page on st.uid = page.uid".
					" where st.uid > 0 and st.type >= %s",(is_admin()? 0 : 1));
	$tmpl_opt = array();
	$tmpl_opt[-2] = 'マイページ用標準２カラム';
	$tmpl_opt[-3] = 'マイページ用標準３カラム';

	if ( false !== $p ) {

		while($t = mysql_fetch_assoc($p)){
			$tmpl_opt[$t['id']] = $t['name'];
		}

	}

	// text:sitename
	$SYS_FORM["header"][] = 'マイページを作成するために、下記のフォームへ情報を入力してください。';

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

	// radio:skin
//    $option = array(79 => '２カラム', 78 => '３カラム');
//	$attr = array(name => 'skin', value => $skin, option => $option, break_num => 1);
//	$SYS_FORM["input"][] = array(title => 'スキン',
//								 name  => 'skin',
//								 body  => get_form("radio", $attr));
	$attr = array(name => 'tmpl', value => $tmpl, option => $tmpl_opt, break_num => 1);
	$body = get_form("radio", $attr);
	$SYS_FORM["input"][] = array(title => 'ページテンプレート',
								 name  => 'tmpl',
								 body  => $body);
								 

	$SYS_FORM["action"] = 'mypage_profile.php';
	$SYS_FORM["method"] = 'POST';

//	$SYS_FORM["keyword"] = true;
	$SYS_FORM["pmt"] = true;

	$SYS_FORM["submit"] = '確認画面へ進む';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'マイページの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}
?>
