<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_bbs_main($id = null) {
	global $SYS_BLOG_ID, $SYS_REPORTER, $SYS_FORM;

	$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	switch ($act) {
		case 'regist':
			$html = mod_bbs_regist_data();
			if ($SYS_FORM["error"]) {
				$html = mod_bbs_input_data();
			}
			return $html;
		break;
		case 'input':
			$html = mod_bbs_input_data();
			return $html;
		break;
		default:
			;
	}

	if (isset($_REQUEST['show'])and $_REQUEST['show']=='all') {
		return mod_bbs_thread_list();
	}

	$q = mysql_uniq('select * from mod_bbs_thread where id = %s',
					mysql_num($id));

	$title = $q['title'];
	$body  = $q['body'];

	$uid   = $q['uid'];
	$name  = $q['name'];
	$mail  = $q['mail'];
	$url   = $q['url'];
	if ($uid > 0) {
		$name = get_handle($uid);
	}
	if ($mail != '') {
		$name = "<a href=\"mailto:${mail}\">${name}</a>";
	}
	$url_str = '';
	if ($url != '') {
		$url_str = ' '. make_href('URL', $url, false, '_blank');
	}

	$date  = date('Y年n月d日 G:i:s', tm2time($q['updymd']));

	$comment = load_comment($id);

	$html = <<<__BLOG_BODY__
<div class="mod_blog_main">
<div class="mod_blog_main_content">

<h4 class="mod_blog_main_title">${title}</h4>
<div class="common_date" style="border: none; margin-bottom: 5px;">名前：${name}${url_str} 日時:${date}</div>

${body}
${comment}
</div></div>
<br clear="all">
__BLOG_BODY__;
	;

	return $html;
}

function mod_bbs_thread_list() {
	$id = isset($_REQUEST['blk_id']) ? intval($_REQUEST['blk_id']) : 0;

	$f = mysql_fullpmt($id, 'mod_bbs_thread');

	if (!$f) {
		return '現在スレッドはありません。';
	}
	while ($res = mysql_fetch_assoc($f)) {
		$title = $res['title'];
		$href  = "/index.php?module=bbs&eid=$res[id]&blk_id=$res[pid]";
		$body  = clip_str($res['body'], 128);
		$date  = date('n月d日 G時i分', tm2time($res['updymd']));

		$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
				 '" class="common_href"><span>'.
				 htmlspecialchars($title, ENT_QUOTES). '</span></a>';
		$html .= '<div class="common_body">'. $body. '</div>';
		$html .= '<div class="common_date">作成日:'. $date. '</div>';
	}

	return $html;
}

/* 登録*/
function mod_bbs_regist_data() {
	global $SYS_FORM, $SYS_VIEW_GID, $SYS_VIEW_UID;

	$eid = isset($_POST['eid']) ? intval($_POST['eid']) : 0;
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;

	if ($eid == 0) {
		$o = mysql_uniq('select * from owner where id = %s', mysql_num($pid));
	}
	else {
		$o = mysql_uniq('select * from owner where id = %s', mysql_num($eid));
	}
	if ($o) {
		$uid = $o['uid'];
		$gid = $o['gid'];
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"] = isset($_POST["title"]) ? $_POST["title"] : '無題';
	$SYS_FORM["cache"]["body"]  = $_POST["body"];
	$SYS_FORM["cache"]["name"]  = isset($_POST["name"]) ? $_POST["name"] : '名無し';
	$SYS_FORM["cache"]["mail"]  = isset($_POST["mail"]) ? $_POST["mail"] : '';
	$SYS_FORM["cache"]["url"]   = isset($_POST["url"]) ? $_POST["url"] : '';
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["title"] || $SYS_FORM["cache"]["title"] == '') {
		$SYS_FORM["error"]["title"] = 'タイトルが未入力です。';
	}
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />') {
		$SYS_FORM["error"]["body"] = '本文が未入力です。';
	}
	if ($SYS_FORM["cache"]["url"] == 'http://') {
		$SYS_FORM["cache"]["url"] = '';
	}
	if ($SYS_FORM["error"]) {
		return;
	}
	// 登録
	$title   = htmlspecialchars($SYS_FORM["cache"]["title"], ENT_QUOTES);
	$body    = $SYS_FORM["cache"]["body"];
	$uid     = myuid();
	$name    = htmlspecialchars($SYS_FORM["cache"]["name"], ENT_QUOTES);
	$mail    = htmlspecialchars($SYS_FORM["cache"]["mail"], ENT_QUOTES);
	$url     = htmlspecialchars($SYS_FORM["cache"]["url"], ENT_QUOTES);
	$initymd = mysql_current_timestamp();
	$updymd  = mysql_current_timestamp();

	// pidはもう使いません。コード整理時に消します。
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec('insert into mod_bbs_thread'.
						' (id, pid, title, body, uid, name, mail, url, initymd, updymd)'.
						' values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
						mysql_num($eid), mysql_num($pid), mysql_str($title), mysql_str($body),
						mysql_num($uid), mysql_str($name), mysql_str($mail), mysql_str($url),
						$initymd, $updymd);

		$res_msg = 'スレッドを作成しました。';
	}
	else {
		$p = mysql_uniq("select * from mod_bbs_thread where id = %s",
						mysql_num($eid));
		$pid = $p['pid'];
		$q = mysql_exec('update mod_bbs_thread set'.
						' title = %s, body = %s, name = %s, mail = %s, url = %s, updymd = %s, pid = %s'.
						' where id = %s',
						mysql_str($title), mysql_str($body), mysql_str($name), mysql_str($mail),
						mysql_str($url), $updymd, mysql_num($pid),
						mysql_num($eid));

		$res_msg = 'スレッドを編集しました。';
	}

	set_pmt(array('eid' => $eid, 'gid' =>get_gid($pid)));
	set_comment($eid);

	tell_update($eid, '掲示板');

	$html = $res_msg. create_form_return(array('eid' => $eid, 'gid' => $gid, 'href' => home_url($eid)));

	return $html;
}

function mod_bbs_input_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;
	global $SYS_BOX_TITLE;

	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

	if ($eid == 0) {
		$o = mysql_uniq('select * from owner where id = %s', mysql_num($pid));
	}
	else {
		$o = mysql_uniq('select * from owner where id = %s', mysql_num($eid));
	}
	if ($o) {
		$uid = $o['uid'];
		$gid = $o['gid'];
	}

	$d = mysql_uniq('select * from mod_bbs_thread'.
					' where id = %s',
					mysql_num($eid));

	// Y-m-d H:i:s
	if ($d) {
		$SYS_BOX_TITLE = 'スレッドの編集';
		$title       = $d["title"];
		$body        = $d["body"];
//		$initymd     = tm2time($d["initymd"]);
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
//		$initymd     = time();
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title     = $SYS_FORM["cache"]["title"];
		$body      = $SYS_FORM["cache"]["body"];
		$name      = $SYS_FORM["cache"]["name"];
		$mail      = $SYS_FORM["cache"]["mail"];
		$url       = $SYS_FORM["cache"]["url"];
	}

	$attr = array('name' => 'uid', value => $uid);
	$SYS_FORM["input"][] = array('name'  => 'uid',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'module', value => 'bbs');
	$SYS_FORM["input"][] = array('name'  => 'module',
								 'body'  => get_form('hidden', $attr));

	$attr = array('name' => 'action', value => 'regist');
	$SYS_FORM["input"][] = array('name'  => 'action',
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

	$SYS_FORM["action"] = 'index.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["comment"] = true;
	$SYS_FORM["pmt"]     = true;

	$SYS_FORM["submit"] = 'スレッド作成';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, gid => $gid, pid => $pid));

	return $html;
}

?>
