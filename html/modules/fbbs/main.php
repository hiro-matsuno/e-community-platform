<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_fbbs_main($id = null) {
	global $SYS_BLOG_ID, $SYS_REPORTER, $SYS_FORM, $COMUNI_HEAD_CSS, $COMUNI_HEAD_CSSRAW;

	$COMUNI_HEAD_CSS[] = CONF_URLBASE. '/modules/fbbs/css/form.css';


	if (isset($_SESSION['mod_fbbs_error'])) {
		$SYS_FORM["error"] = $_SESSION['mod_fbbs_error'];
		$SYS_FORM["cache"] = $_SESSION['mod_fbbs_cache'];
		unset_session('/^mod_fbbs_/');
	}

	if (isset($_REQUEST['msg']) && $_REQUEST['msg'] != '') {
		switch ($_REQUEST['msg']) {
			case 'regist_thread':
				$msg = 'スレッドを作成しました。';
			break;
			case 'regist_response':
				$msg = '記事を投稿しました。';
			break;
			default:
				;
		}
		unset_session('/^mod_fbbs_/');

		return $msg;
	}

	$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : $act_f;

	switch ($act) {
		// スレッド作成
		case 'regist_thread':
			$html = mod_fbbs_regist_thread();
			return $html;
		break;
		case 'input_thread':
			$html = mod_fbbs_input_thread();
			return $html;
		break;
		// レス作成
		case 'regist_response':
			$html = mod_fbbs_regist_response();
			return $html;
		break;
		case 'input_response':
			$html = mod_fbbs_input_response();
			return $html;
		break;
		// スレッド表示
		case 'get_thread':
			$html = mod_fbbs_get_thread();
			return $html;
		break;
		// すべてのスレッド
		case 'get_all_thread':
			$html = mod_fbbs_get_all_thread();
			return $html;
		break;
		// すべてのスレッド
		case 'get_all_backnumber':
			$html = mod_fbbs_get_all_backnumber();
			return $html;
		break;
		default:
			$html = '調整中...';
	}

	return $html;
}

function mod_fbbs_get_all_thread() {
	global $COMUNI_TPATH;

	$id = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

	$q = mysql_full('select * from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e on d.id = e.id'.
					' where e.pid = %s'.
					' order by d.initymd desc',
					mysql_num($id));

	$html .= '<ul class="mod_fbbs_thread_list" style="padding-top: 8px;">';
	while ($res = mysql_fetch_assoc($q)) {
		$b =mysql_uniq('select * from mod_fbbs_backnumber where thread_id = %s', $res['id']);

		if ($b) {
			if ((time() - strtotime($b['initymd'])) > 60 * 60 * 24 * 7) {
				continue;
			}
		}

//		$count = mod_fbbs_count($res['id']);
		$href = '/index.php?module=fbbs&action=get_thread&eid='. $res['id']. '&blk_id='. $id;
		$html .= '<li><a href="'. $href. '">'. $res['title']. '</a><!-- ('. $count. ')--></li>'. "\n";
		$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
			 ' 作成日時: '.
			 date('n月d日 G時i分', tm2time($res['updymd'])).
			 '</div>';

		$html .= '<div style="margin: 8px; padding-bottom: 8px;border-bottom: dashed 1px #ccc;">'. $res['body']. '</div>'. "\n";
//		$html .= mod_fbbs_get_child($res['id'], $res['id'], $view_type);
	}
	$html .= '</ul>';

	$c = mysql_uniq('select * from mod_fbbs_setting where id = %s',
					mysql_num($id));
	if ($c) {
		$backnum_pmt = $c['backnum_pmt'];
		if (is_owner($id)) {
			$backnum_pmt = 0;
		}
	}
	if ($backnum_pmt == 0 || ($backnum_pmt == 1 && is_login())) {
		$href  = '/index.php?module=fbbs&action=get_all_backnumber&eid='. $id;
		$html .= '<div style="text-align: right;">'.
				 make_href('バックナンバー一覧&raquo;', $href).
				 '</div>';
	}

	$COMUNI_TPATH[] = array('name' => 'スレッド一覧');

	return $html;
}

function mod_fbbs_get_all_backnumber() {
	global $COMUNI_TPATH;

	$id = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

	$q = mysql_full('select * from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e on d.id = e.id'.
					' where e.pid = %s'.
					' order by d.initymd desc',
					mysql_num($id));

	$html .= '<ul class="mod_fbbs_thread_list" style="padding-top: 8px;">';
	while ($res = mysql_fetch_assoc($q)) {
		$b = mysql_uniq('select * from mod_fbbs_backnumber where thread_id = %s', $res['id']);

		if ($b) {
			if ((time() - strtotime($b['initymd'])) < 60 * 60 * 24 * 7) {
				continue;
			}
		}
		else {
			continue;
		}
//		$count = mod_fbbs_count($res['id']);
		$href = '/index.php?module=fbbs&action=get_thread&eid='. $res['id'];
		$html .= '<li><a href="'. $href. '">'. $res['title']. '</a><!-- ('. $count. ')--></li>'. "\n";
		$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
			 ' 作成日時: '.
			 date('n月d日 G時i分', tm2time($res['updymd'])).
			 '</div>';

		$html .= '<div style="margin: 8px; padding-bottom: 8px;border-bottom: dashed 1px #ccc;">'. $res['body']. '</div>'. "\n";
//		$html .= mod_fbbs_get_child($res['id'], $res['id'], $view_type);
	}
	$html .= '</ul>';


	$href  = '/index.php?module=fbbs&action=get_all_thread&eid='. $id;
	$html .= '<div style="text-align: right;">'.
			 make_href('スレッド一覧&raquo;', $href).
			 '</div>';

	$COMUNI_TPATH[] = array('name' => 'バックナンバー一覧');

	return $html;
}


function mod_fbbs_regist_response() {
	global $SYS_FORM, $SYS_VIEW_GID, $SYS_VIEW_UID;

	$eid = isset($_POST['eid']) ? intval($_POST['eid']) : 0;
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	$thread_id = isset($_REQUEST['thread_id']) ? intval($_REQUEST['thread_id']) : 0;

	$top_id = isset($_REQUEST['top_id']) ? intval($_REQUEST['top_id']) : 0;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"] = isset($_POST["title"]) ? $_POST["title"] : '';
	$SYS_FORM["cache"]["body"]  = $_POST["body"];
	$SYS_FORM["cache"]["name"]  = isset($_POST["name"]) ? $_POST["name"] : '';
	$SYS_FORM["cache"]["mail"]  = isset($_POST["mail"]) ? $_POST["mail"] : '';
	$SYS_FORM["cache"]["url"]   = isset($_POST["url"]) ? $_POST["url"] : '';
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["title"] || $SYS_FORM["cache"]["title"] == '') {
		$SYS_FORM["error"]["title"] = 'タイトルが未入力です。';
	}
	if (!is_login()) {
		if (!$SYS_FORM["cache"]["name"] || $SYS_FORM["cache"]["name"] == '') {
			$SYS_FORM["error"]["name"] = 'お名前が未入力です。';
		}
	}
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />' || $SYS_FORM["cache"]["body"] == '&nbsp;' || $SYS_FORM["cache"]["body"] == ' ') {
		$SYS_FORM["error"]["body"] = '本文が未入力です。';
	}
	if ($SYS_FORM["cache"]["url"] == 'http://') {
		$SYS_FORM["cache"]["url"] = '';
	}
	if ($SYS_FORM["error"]) {
		return mod_fbbs_input_response($thread_id, $thread_id);
	}

	// とうろく
	$parent_id = $pid;
	$title   = htmlspecialchars($SYS_FORM["cache"]["title"], ENT_QUOTES);
	$body    = $SYS_FORM["cache"]["body"];
	$uid     = myuid();
	$name    = htmlspecialchars($SYS_FORM["cache"]["name"], ENT_QUOTES);
	$mail    = htmlspecialchars($SYS_FORM["cache"]["mail"], ENT_QUOTES);
	$url     = htmlspecialchars($SYS_FORM["cache"]["url"], ENT_QUOTES);
	$initymd = mysql_current_timestamp();
	$updymd  = mysql_current_timestamp();

	if ($uid == 0) {
		$cookie_str = 'name='. $name. '<>mail='. $mail. '<>url='. $url;
		setcookie ("mod_fbbs", $cookie_str, time() + 30 * 24 * 3600, '/');
	}
	else {
		$cookie_str = 'mail='. $mail. '<>url='. $url;
		setcookie ("mod_fbbs", $cookie_str, time() + 30 * 24 * 3600, '/');
	}

	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec('insert into mod_fbbs_data'.
						' (id, parent_id, top_id, title, body, uid, name, mail, url, initymd, updymd)'.
						' values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
						mysql_num($eid), mysql_num($parent_id), mysql_num($top_id),
						mysql_str($title), mysql_str($body),
						mysql_num($uid), mysql_str($name), mysql_str($mail), mysql_str($url),
						$initymd, $updymd);

		$res_msg = '投稿が完了しました。';
	}

	$o = mysql_uniq('select * from owner where id = %s', $thread_id);
	if ($o) {
		$owner_uid = $o['uid'];
	}

	mod_fbbs_set_pid($eid, $thread_id);
	set_pmt(array('eid' => $eid, 'uid' => $owner_uid, 'gid' =>get_gid($thread_id), 'unit' => get_pmt($thread_id)));
	mod_fbbs_set_owner($eid);

	tell_update($eid, '掲示板（電子会議室）');

	$c = mysql_uniq('select * from mod_fbbs_setting where id = %s',
					mysql_num(mod_fbbs_get_pid($thread_id)));
	if ($c) {
		$rec_num = $c['rec_num'];
	}
	$c = mysql_uniq('select count(*) as count from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e'.
					' on d.id = e.id'.
					' where e.pid = %s',
					mysql_num($thread_id));

	if ($c) {
		$count = $c['count'];
	}
	if ($count >= $rec_num) {
		add_backnumber($thread_id);
	}

//	$return_url = home_url($eid);
	$return_url = CONF_URLBASE. '/index.php?module=fbbs&action=get_thread&eid='. $thread_id;

	$html = $res_msg. create_form_return(array('eid' => $eid, 'gid' => $gid, 'href' => $return_url));

	return $html;

	return 'mod_fbbs_regist_response';
}

function add_backnumber($thread_id = 0) {
	$d = mysql_exec('delete from mod_fbbs_backnumber where thread_id = %s', mysql_num($thread_id));
	$i = mysql_exec('insert into mod_fbbs_backnumber (thread_id, initymd)'.
					' values (%s, %s)',
					mysql_num($thread_id), mysql_current_timestamp());
}

function mod_fbbs_input_response($arg_eid = null, $arg_thread_id = null) {
	global $SYS_FORM, $COMUNI_TPATH;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$thread_id = isset($_REQUEST['thread_id']) ? intval($_REQUEST['thread_id']) : 0;
	$top_id = isset($_REQUEST['top_id']) ? intval($_REQUEST['top_id']) : 0;

	if ($arg_eid) {
		$eid = $arg_eid;
		$thread_id = $arg_thread_id;
	}






	$q = mysql_uniq('select * from mod_fbbs_data where id = %s', mysql_num($eid));

	if (!$q) {
		return 'スレッドが見つかりません。'. $eid. '/'. $thread_id;
	}



	$c = mysql_uniq('select * from mod_fbbs_setting where id = %s',
					mysql_num(mod_fbbs_get_pid($thread_id)));
	if ($c) {
		$rec_num = $c['rec_num'];
	}

	$c = mysql_uniq('select count(*) as count from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e'.
					' on d.id = e.id'.
					' where e.pid = %s',
					mysql_num($thread_id));

	if ($c) {
		$count = $c['count'];
	}
	if ($count >= $rec_num) {
		return 'これ以上このスレッドへは書き込めません';
	}













	$html .= '<h3 class="mod_fbbs_thread_title">'. $q['title']. '</h3>';
	$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_get_author($q['uid'], $q['name'], $q['mail'], $q['url']).
			 ' 投稿日: '.
			 date('n月d日 G時i分', tm2time($q['updymd'])).
			 '</div>';
	$html .= '<div class="mod_fbbs_thread_body">'. $q['body']. '</div>';
	$html .= '<div class="mod_fbbs_thread_link">';
	$html .= '</div>';
	$html .= '<hr>';

	$html .= '<h3 class="mod_fbbs_thread_title">上記記事への返信</h3>';

	$uid         = myuid();


	$cookie = isset($_COOKIE['mod_fbbs']) ? $_COOKIE['mod_fbbs'] : null;

	if ($cookie) {
		$cookie_array = explode('<>', $cookie);
		foreach ($cookie_array as $c) {
			list($key, $value) = explode('=', $c);
			switch ($key) {
				case 'name':
					$name = $value;
				break;
				case 'mail':
					$mail = $value;
				break;
				case 'url':
					$url = $value;
				break;
			}
		}
	}


	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title     = $SYS_FORM["cache"]["title"];
		$body      = $SYS_FORM["cache"]["body"];
		$name      = $SYS_FORM["cache"]["name"];
		$mail      = $SYS_FORM["cache"]["mail"];
		$url       = $SYS_FORM["cache"]["url"];
	}

	$attr = array('name' => 'module', value => 'fbbs');
	$SYS_FORM["input"][] = array('name'  => 'module',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'action', value => 'regist_response');
	$SYS_FORM["input"][] = array('name'  => 'action',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'thread_id', value => $thread_id);
	$SYS_FORM["input"][] = array('name'  => 'thread_id',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'top_id', value => $top_id);
	$SYS_FORM["input"][] = array('name'  => 'top_id',
								 'body'  => get_form('hidden', $attr));


	$attr = array('name' => 'uid', value => $uid);
	$SYS_FORM["input"][] = array('name'  => 'uid',
								 'body'  => get_form('hidden', $attr));

	// text:subject
	$attr = array(name => 'title', value => $title, size => 40, style => 'width: 80%;');
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	if ($uid == 0) {
		$attr = array(name => 'name', value => $name, size => 32, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("text", $attr));

		$attr = array(name => 'mail', value => $mail, size => 48, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'メールアドレス',
									 name  => 'mail',
									 body  => get_form("text", $attr));
	}
	else {
		$attr = array('name' => 'name', 'value' => get_handle($uid));
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("plain", $attr));

		$attr = array(name => 'mail', value => $mail, size => 48, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'メールアドレス',
									 name  => 'mail',
									 body  => get_form("text", $attr));
	}

	$attr = array(name => 'url', value => $url, size => 64, style => 'width: 80%;');
	$SYS_FORM["input"][] = array(title => 'URL',
								 name  => 'url',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array('name' => 'body', 'value' => $body, 'cols' => 64, 'rows' => 10, 'toolbar' => 'Basic');
	$SYS_FORM["input"][] = array(title => '本文',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = CONF_URLBASE. '/modules/fbbs/post.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["comment"] = false;
	$SYS_FORM["pmt"]     = false;

	$SYS_FORM["submit"] = '投稿';
	$SYS_FORM["cancel"] = '取消';

	$html .= create_form(array('eid' => 0, 'gid' => $gid, 'pid' => $eid));

	$COMUNI_TPATH[] = array('name' => get_block_name(mod_fbbs_get_pid($thread_id)));

	return $html;
}

function mod_fbbs_get_thread() {
	global $COMUNI_TPATH, $COMUNI_HEAD_JS, $JQUERY;
	global $SYS_TREE_TOPID, $blk_id;

	set_layout(1);

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

	$q = mysql_uniq('select * from mod_fbbs_data where id = %s', mysql_num($eid));

	if (!$q) {
		return 'スレッドが見つかりません。';
	}

	$href = '/index.php?module=fbbs&action=input_response&eid='. $eid. '&thread_id='. $eid;

	$html .= '<h3 class="mod_fbbs_thread_title_top">'. $q['title']. '</h3>';
	$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_get_author($q['uid'], $q['name'], $q['mail'], $q['url']).
			 ' 作成日時: '.
			 date('n月d日 G時i分', tm2time($q['updymd'])).
			 '</div>';
	$html .= '<div class="mod_fbbs_thread_body">'. $q['body']. '</div>';
	$html .= '<div class="mod_fbbs_thread_link">';
	$html .= '<a href="'. $href. '">このスレッドに返信</a>';
	if (is_owner($eid) || mod_fbbs_is_owner($eid)) {
		$html .= ' | '. make_href('スレッドの削除', '/modules/fbbs/delete.php?eid='. $eid. '&thread_id='. $eid, true);
	}

	$html .= '</div>';
//	$html .= '<hr>';

//	$html .= '<h3>このスレッドのツリー</h3>';

	$href = '/index.php?module=fbbs&action=get_thread&eid='. $eid. '&thread_id='. $eid. '&blk_id='. $blk_id;

	$html .= '<div class="mod_fbbs_select">';
	$html .= 'ツリー表示 <a href="'. $href. '&o=desc">降順</a> <a href="'. $href. '">昇順</a> | ';
	$html .= '新着順表示 <a href="'. $href. '&o=desc&t=recent">降順</a> <a href="'. $href. '&t=recent">昇順</a>';

	$html .= '</div>';

	$COMUNI_HEAD_JS[] = '/modules/fbbs/js/jquery.scrollfollow.js';

	$JQUERY["ready"][] = <<<___READY_CODE__
$('#mod_fbbs_tree_wrap').scrollFollow();
___READY_CODE__;

	$parent_id = isset($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : null;

	if ($parent_id) {
		$SYS_TREE_TOPID = $parent_id;
	}
	else {
		$SYS_TREE_TOPID = $q['id'];
	}

	$html .= '<div class="mod_fbbs_tree_wrap"><div id="mod_fbbs_tree_wrap">';
	$html .= mod_fbbs_tree_get_thread($q['id'], 1);
	$html .= '</div></div>';

	$html .= '<div class="mod_fbbs_child_wrap">';

	if ($parent_id) {
		$html .= mod_fbbs_main_get_child_once($parent_id, $q['id']);
		$html .= mod_fbbs_main_get_child($parent_id, $q['id']);
	}
	else {
		$html .= mod_fbbs_main_get_child($q['id'], $q['id']);
	}
	$html .= '</div><br clear="all">';

	$COMUNI_TPATH[] = array('name' => get_block_name(mod_fbbs_get_pid($eid)));

	return $html;
}

function mod_fbbs_get_author($uid = 0, $name = null, $mail = null, $url = null) {
	if ($uid == 0) {
		$name = isset($name) ? $name : '名無し';
		if ($mail && $mail != '') {
			$name = '<a href="mailto:'. $mail. '">'. $name. '</a>';
		}
		return $name. mod_fbbs_get_url($url);
	}
	else {
		$name = get_handle($uid);
		if ($mail && $mail != '') {
			$name = '<a href="mailto:'. $mail. '">'. $name. '</a>';
		}
		return $name. mod_fbbs_get_url($url);
	}
	return mod_fbbs_get_href(get_handle($uid), $url);
}

function mod_fbbs_regist_thread() {
	global $SYS_FORM, $SYS_VIEW_GID, $SYS_VIEW_UID;

	$eid = isset($_POST['eid']) ? intval($_POST['eid']) : 0;
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"] = isset($_POST["title"]) ? $_POST["title"] : '';
	$SYS_FORM["cache"]["body"]  = $_POST["body"];
	$SYS_FORM["cache"]["name"]  = isset($_POST["name"]) ? $_POST["name"] : '';
	$SYS_FORM["cache"]["mail"]  = isset($_POST["mail"]) ? $_POST["mail"] : '';
	$SYS_FORM["cache"]["url"]   = isset($_POST["url"]) ? $_POST["url"] : '';
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["title"] || $SYS_FORM["cache"]["title"] == '') {
		$SYS_FORM["error"]["title"] = 'タイトルが未入力です。';
	}
	if (!is_login()) {
		if (!$SYS_FORM["cache"]["name"] || $SYS_FORM["cache"]["name"] == '') {
			$SYS_FORM["error"]["name"] = 'お名前が未入力です。';
		}
	}
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />' || $SYS_FORM["cache"]["body"] == '&nbsp;' || $SYS_FORM["cache"]["body"] == ' ') {
		$SYS_FORM["error"]["body"] = '本文が未入力です。';
	}
	if ($SYS_FORM["cache"]["url"] == 'http://') {
		$SYS_FORM["cache"]["url"] = '';
	}
	if ($SYS_FORM["error"]) {
		return mod_fbbs_input_thread($eid, $pid);
	}
	// とうろく
	$parent_id = 0;
	$title   = htmlspecialchars($SYS_FORM["cache"]["title"], ENT_QUOTES);
	$body    = $SYS_FORM["cache"]["body"];
	$uid     = myuid();
	$name    = htmlspecialchars($SYS_FORM["cache"]["name"], ENT_QUOTES);
	$mail    = htmlspecialchars($SYS_FORM["cache"]["mail"], ENT_QUOTES);
	$url     = htmlspecialchars($SYS_FORM["cache"]["url"], ENT_QUOTES);
	$initymd = mysql_current_timestamp();
	$updymd  = mysql_current_timestamp();

	if ($uid == 0) {
		$cookie_str = 'name='. $name. '<>mail='. $mail. '<>url='. $url;
		setcookie ("mod_fbbs", $cookie_str, time() + 30 * 24 * 3600, '/');
	}
	else {
		$cookie_str = 'mail='. $mail. '<>url='. $url;
		setcookie ("mod_fbbs", $cookie_str, time() + 30 * 24 * 3600, '/');
	}

	// pidはもう使いません。コード整理時に消します。
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec('insert into mod_fbbs_data'.
						' (id, parent_id, title, body, uid, name, mail, url, initymd, updymd)'.
						' values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
						mysql_num($eid), mysql_num($parent_id),
						mysql_str($title), mysql_str($body),
						mysql_num($uid), mysql_str($name), mysql_str($mail), mysql_str($url),
						$initymd, $updymd);

		$res_msg = 'スレッドを作成しました。';
	}
	else {
		$pid = mod_fbbs_get_pid($eid);
		$q = mysql_exec('update mod_fbbs_data set'.
						' title = %s, body = %s, name = %s, mail = %s, url = %s, updymd = %s'.
						' where id = %s',
						mysql_str($title), mysql_str($body), mysql_str($name), mysql_str($mail),
						mysql_str($url), $updymd,
						mysql_num($eid));

		$res_msg = 'スレッドを編集しました。';
	}

	$o = mysql_uniq('select * from owner where id = %s', $pid);
	if ($o) {
		$owner_uid = $o['uid'];
	}

	mod_fbbs_set_pid($eid, $pid);
	set_pmt(array('eid' => $eid, 'uid' => $owner_uid, 'gid' =>get_gid($pid), 'unit' => PMT_PUBLIC));

	mod_fbbs_set_owner($eid);

	tell_update($eid, '掲示板（電子会議室）のスレッド');

//	tell_update($eid, '掲示板（電子会議室）');

	$html = $res_msg. create_form_return(array('eid' => $eid, 'gid' => get_gid($pid), 'href' => home_url($eid)));

	return $html;
}

function mod_fbbs_input_thread($arg_eid = null, $arg_pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;
	global $SYS_BOX_TITLE;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

	if ($arg_eid) {
		$eid = $arg_eid;
		$pid = $arg_pid;
	}

	$d = mysql_uniq('select * from mod_fbbs_data where id = %s',
					mysql_num($eid));

	if ($d) {
		$SYS_BOX_TITLE = 'スレッドの編集';
		$title       = $d["title"];
		$body        = $d["body"];
		$uid         = $d["uid"];
		$name        = $d["name"];
		$mail        = $d["mail"];
		$url         = $d["url"];
	}
	else {
		$SYS_BOX_TITLE = '新規スレッドの作成';
		$subject     = '';
		$body        = '';
		$uid         = myuid();
		$name        = '';
		$mail        = '';
		$url         = 'http://';
	}

	$cookie = isset($_COOKIE['mod_fbbs']) ? $_COOKIE['mod_fbbs'] : null;
	if ($cookie) {
		$cookie_array = explode('<>', $cookie);
		foreach ($cookie_array as $c) {
			list($key, $value) = explode('=', $c);
			switch ($key) {
				case 'name':
					$name = $value;
				break;
				case 'mail':
					$mail = $value;
				break;
				case 'url':
					$url = $value;
				break;
			}
		}
	}

	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title     = $SYS_FORM["cache"]["title"];
		$body      = $SYS_FORM["cache"]["body"];
		$name      = $SYS_FORM["cache"]["name"];
		$mail      = $SYS_FORM["cache"]["mail"];
		$url       = $SYS_FORM["cache"]["url"];
	}

	$attr = array('name' => 'module', value => 'fbbs');
	$SYS_FORM["input"][] = array('name'  => 'module',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'action', value => 'regist_thread');
	$SYS_FORM["input"][] = array('name'  => 'action',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'uid', value => $uid);
	$SYS_FORM["input"][] = array('name'  => 'uid',
								 'body'  => get_form('hidden', $attr));

	// text:subject
	$attr = array(name => 'title', value => $title, size => 40, style => 'width: 80%;');
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	if ($uid == 0) {
		$attr = array(name => 'name', value => $name, size => 32, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("text", $attr));

		$attr = array(name => 'mail', value => $mail, size => 48, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'メールアドレス',
									 name  => 'mail',
									 body  => get_form("text", $attr));
	}
	else {
		$attr = array('name' => 'name', 'value' => get_handle($uid));
		$SYS_FORM["input"][] = array(title => 'お名前',
									 name  => 'name',
									 body  => get_form("plain", $attr));

		$attr = array(name => 'mail', value => $mail, size => 48, style => 'width: 80%;');
		$SYS_FORM["input"][] = array(title => 'メールアドレス',
									 name  => 'mail',
									 body  => get_form("text", $attr));
	}

	$attr = array(name => 'url', value => $url, size => 64, style => 'width: 80%;');
	$SYS_FORM["input"][] = array(title => 'URL',
								 name  => 'url',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array('name' => 'body', 'value' => $body, 'cols' => 64, 'rows' => 10, 'toolbar' => 'Basic');
	$SYS_FORM["input"][] = array(title => '本文',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = CONF_URLBASE. '/modules/fbbs/post.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["comment"] = false;
	$SYS_FORM["pmt"]     = false;

	$SYS_FORM["submit"] = '送信';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, gid => get_gid($pid), pid => $pid));

	return $html;
}

function mod_fbbs_main_get_child($id = 0, $thread_id = 0) {
	global $THREAD_OWNER;
	global $SYS_TREE_TOPID;

	$q = mysql_full('select * from mod_fbbs_data where parent_id = %s'.
					' order by initymd desc',
					mysql_num($id));

	if (mysql_num_rows($q) < 1) {
		return;
	}

	if (!isset($THREAD_OWNER)) {
		if (is_owner($thread_id)) {
			$THREAD_OWNER[$thread_id] = true;
		}
		else {
			$THREAD_OWNER[$thread_id] = false;
		}
	}

	$res_id = isset($_REQUEST['res_id']) ? intval($_REQUEST['res_id']) : $q['id'];

	while ($res = mysql_fetch_assoc($q)) {
		$html .= '<a name="res'. $res['id']. '"></a>';
		$html .= '<h3 class="mod_fbbs_thread_title">'. $res['title']. '</h3>';
		$html .= '<div class="mod_fbbs_thread_autor">'.
				 ' 投稿者: '. 
				 mod_fbbs_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
				 ' 投稿日: '.
				 date('n月d日 G時i分', tm2time($res['updymd'])).
				 '</div>';
		$html .= '<div class="mod_fbbs_thread_body">'. $res['body']. '</div>';
		$html .= '<div class="mod_fbbs_thread_link">';

		$top_id = $res['top_id'];
		if ($top_id == 0) {
			$top_id = $res['id'];
		}

		$href = '/index.php?module=fbbs&action=input_response&eid='. $thread_id. '&thread_id='. $thread_id. '&pid='. $thread_id;
		$html .= '<a href="'. $href. '">スレッドに返信</a> | ';
		$href = '/index.php?module=fbbs&action=input_response&eid='. $res['id']. '&top_id='. $top_id. '&thread_id='. $thread_id. '&pid='. $thread_id;
		$html .= '<a href="'. $href. '">このレスに返信</a> | ';
		$html .= '<a href="#tree">ツリー表示</a>';
		if (mod_fbbs_is_owner($res['id']) || $THREAD_OWNER[$thread_id] == true) {
			$html .= ' | '. make_href('削除', '/modules/fbbs/delete.php?eid='. $res['id']. '&thread_id='. $thread_id, true);
		}
		$html .= '</div>';
		$html .= '<hr size="3">';
		$html .= mod_fbbs_main_get_child($res['id'], $thread_id);
	}

	return $html;
}

function mod_fbbs_main_get_child_once($id = 0, $thread_id = 0) {
	global $THREAD_OWNER;

	$q = mysql_uniq('select d.*, e.pid from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e'.
					' on d.id = e.id where d.id = %s',
					mysql_num($id));

	if (!$q) {
		return;
	}

//	$thread_id = $q['pid'];

	$top_id = $q['top_id'];
	if ($top_id == 0) {
		$top_id = $q['id'];
	}

	$html .= '<a name="res'. $q['id']. '"></a>';
	$html .= '<h3 class="mod_fbbs_thread_title">'. $q['title']. '</h3>';
	$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_get_author($q['uid'], $q['name'], $q['mail'], $q['url']).
			 ' 投稿日: '.
			 date('n月d日 G時i分', tm2time($q['updymd'])).
			 '</div>';
	$html .= '<div class="mod_fbbs_thread_body">'. $q['body']. '</div>';
	$html .= '<div class="mod_fbbs_thread_link">';

	$href = '/index.php?module=fbbs&action=input_response&eid='. $thread_id. '&thread_id='. $thread_id. '&pid='. $thread_id;
	$html .= '<a href="'. $href. '">スレッドに返信</a> | ';
	$href = '/index.php?module=fbbs&action=input_response&eid='. $q['id']. '&top_id='. $top_id. '&thread_id='. $thread_id. '&pid='. $thread_id;
	$html .= '<a href="'. $href. '">このレスに返信</a> | ';
	$html .= '<a href="#tree">ツリー表示</a>';
	if (mod_fbbs_is_owner($q['id']) || $THREAD_OWNER[$thread_id] == true) {
		$html .= ' | '. make_href('削除', '/modules/fbbs/delete.php?eid='. $q['id']. '&thread_id='. $thread_id, true);
	}
	$html .= '</div>';

	return $html;
}


function mod_fbbs_tree_get_thread($id = 0, $view_type = 1) {
	global $SYS_TREE_TOPID;

	$order = isset($_REQUEST['o']) ? $_REQUEST['o'] : '';
	$t     = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;

	switch ($order) {
		case 'desc':
			$orberby = 'initymd desc';
		break;
		default:
			$orberby = 'initymd';
	}

	if ($t) {
		$q = mysql_full('select d.*, er.pid from mod_fbbs_data as d'.
						' inner join mod_fbbs_element_relation as er'.
						' on d.id = er.id'.
						' where er.pid = %s'.
						' order by d.%s',
						mysql_num($id), $orberby);
	}
	else {
		$q = mysql_full('select d.*, er.pid from mod_fbbs_data as d'.
						' inner join mod_fbbs_element_relation as er'.
						' on d.id = er.id'.
						' where d.parent_id = %s'.
						' order by d.%s',
						mysql_num($id), $orberby);
	}

	if (mysql_num_rows($q) == 0) {
		return 'レスは１件もありません。';
	}

	$html .= '<a name="tree"></a><ul class="mod_fbbs_tree_list">';
	while ($res = mysql_fetch_assoc($q)) {
		if ($res['parent_id'] == $res['pid']) {
			$SYS_TREE_TOPID = $res['id'];
			$class = 'res_top';
		}
		else {
			$class = 'res_comment';
		}

		$parent_id = $res['id'];
		if ($res['top_id'] > 0) {
			$parent_id = $res['top_id'];
		}

		$href  = '/index.php?module=fbbs&action=get_thread&eid='. $id. '#res'. $res['id'];
		$href  = '/index.php?module=fbbs&action=get_thread&eid='. $id. '&thread_id='. $id. '&parent_id='. $parent_id. '#res'. $res['id'];

		$html .= '<li class="'. $class. '"><a href="'. $href. '">'. $res['title']. '</a>'. "\n";
		$html .= '<span class="mod_fbbs_author"> by '. mod_fbbs_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
				 ' at '. date('n月d日 G時i分', tm2time($res['updymd'])). "</span>\n";

		if (!$t) {
			$html .= mod_fbbs_tree_get_child($res['id'], $id);
			$html .= '</li>';
		}
	}
	$html .= '</ul>';
//	$html .= '<hr>';

	return $html;
}

function search_parent_id($id, $parent_id) {


	return $res['id'];
}

function mod_fbbs_tree_get_child($id = 0, $thread_id = 0) {
	global $SYS_TREE_TOPID;

	$t = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;

	$q = mysql_full('select * from mod_fbbs_data where parent_id = %s'.
					' order by initymd ',
					mysql_num($id));

	if (mysql_num_rows($q) < 1) {
		return;
	}

	if (!$t) {
		$class = 'res_tree';
	}
	else {
		$class = 'res_comment';
	}

	$html = '<ul class="mod_fbbs_tree_list">';
	while ($res = mysql_fetch_assoc($q)) {
		$href  = '/index.php?module=fbbs&action=get_thread&eid='. $thread_id. '#res'. $res['id'];
		$href  = '/index.php?module=fbbs&action=get_thread&eid='. $thread_id. '&thread_id='. $thread_id.
			     '&parent_id='. $SYS_TREE_TOPID. '#res'. $res['id'];;

		$html .= '<li class="'. $class. '"><a href="'. $href. '">'. $res['title']. '</a>'. "\n";
		$html .= '<span class="mod_fbbs_author"> by '. mod_fbbs_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
				 ' at '. date('n月d日 G時i分', tm2time($res['updymd'])). "</span>\n";

		if (!$t) {
			$html .= mod_fbbs_tree_get_child($res['id'], $thread_id);
			$html .= '</li>';
		}
		else {
			$html .= '</li>';
			$html .= mod_fbbs_tree_get_child($res['id'], $thread_id);
		}
	}
	$html .= '</ul>';

	return $html;
}

function mod_fbbs_get_href($str = '名無し', $url = null) {
	if ($url) {
		return '&nbsp;<a href="'. $url. '" target="_blank">'. $str. '</a>';
	}
	else {
		return $str;
	}
}

function mod_fbbs_get_url($url = null) {
	if ($url) {
		return '&nbsp;<a href="'. $url. '" target="_blank">URL</a>';
	}
}

function mod_fbbs_set_owner($eid = 0) {
	$d = mysql_exec('delete from mod_fbbs_elm_owner where eid = %s', mysql_num($eid));
	$i = mysql_exec('insert into mod_fbbs_elm_owner (eid, uid) values (%s, %s);',
					mysql_num($eid), mysql_num(myuid()));
}

function mod_fbbs_is_owner($eid) {
	$q = mysql_uniq('select * from mod_fbbs_elm_owner where eid = %s', mysql_num($eid));

	if ($q['uid'] == 0) {
		return false;
	}
	if ($q['uid'] == myuid()) {
		return true;
	}
	return false;
}

function mod_fbbs_get_pid($eid = null) {
	global $SYS_PID_CACHE;

	if (isset($SYS_PID_CACHE[$eid])) {
		return $SYS_PID_CACHE[$eid];
	}

	$d = mysql_uniq("select * from mod_fbbs_element_relation where id = %s",
					mysql_num($eid));
	if ($d) {
		$SYS_PID_CACHE[$eid] = $d['pid'];
		return $d['pid'];
	}
	else {
		return false;
	}
	$SYS_PID_CACHE[$eid] = $eid;
	return $eid;
}

function mod_fbbs_set_pid($eid = null, $pid = null) {
	if ($pid == null) {
		return;
	}
	$d = mysql_exec("delete from mod_fbbs_element_relation where id = %s",
					mysql_num($eid));
	if (!$d) { die("Fatal error. [set_pid:d]"); }

	$i = mysql_exec("insert into mod_fbbs_element_relation (id, pid)".
					" values (%s, %s)",
					mysql_num($eid), mysql_num($pid));
	if (!$i) { die("Fatal error. [set_pid:i]"); }

	return;
}


?>
