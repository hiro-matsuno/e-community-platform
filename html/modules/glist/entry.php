<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/../../regist_lib.php';

/* 振り分け*/
$id = (intval($_REQUEST["eid"]) > 0) ? $_REQUEST["eid"] : $_REQUEST["pid"];

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($id);
	case 'confirm':
		confirm_date($id);
	default:
		input_data($id);
}

/* 登録*/
function regist_data($id = null) {
	global $COMUNI, $SYS_FORM;

	$gid = get_gid($id);
	$uid = myuid();

	$join_type = app_check($gid, $uid);

	$items = regist_data_get_reqs($gid);
	$error = regist_chk_req($items);
	if($error)input_data($id,$error);
	regist_data_data($uid);

	if ($join_type == 1) {

		$q = mysql_exec("insert into group_app".
						" (gid, uid, initymd)".
						" values (%s, %s, %s)",
						mysql_num($gid), mysql_num($uid),
						mysql_current_timestamp());

		if (!$q) {
			show_error(mysql_error());
		}

		$gname = get_gname($gid);
		$uname = get_handle($uid);
		$url = CONF_URLBASE."/index.php?gid=$gid";
		$message = <<<__MESSG__
「${uname}」　さんからあなたの管理するグループ 「${gname}」に参加申請がありました。

グループページからご確認のうえ差し支えなければ承認を行ってください。
$url
__MESSG__;

		$p = mysql_full("select * from group_member where gid = %s and level >= %s",
						mysql_num($gid), mysql_num(80));
		$to = array();
		while($u = mysql_fetch_assoc($p))
			$to[] = $u['uid'];
		send_message2($to,'グループ参加申請',$message);

		$msg = '参加を申請しました。';
	}else {
		join_group(array(gid => $gid, uid => $uid));
		$msg = '参加しました。';
	}

	$data = array(title   => 'グループへの参加',
				  icon    => 'finish',
				  message => get_gname($gid). 'へ'. $msg,
				  content => create_rform(array(eid => $id, href => home_url($id))));

	show_dialog2($data);

	exit(0);
}

function confirm_date($id = null){
	global $COMUNI, $SYS_FORM;

	$gid = get_gid($id);
	$uid = myuid();

	$join_type = app_check($gid, $uid);
	$a = mysql_uniq("select * from group_joinable".
					" where gid = %s",
					mysql_num($gid));
	if($a['terms'] and !$_REQUEST['terms'])
		$SYS_FORM['error']['terms'] = '参加条件に同意していただけない場合は参加することはできません。';
	if($a['byelaw'] and !$_REQUEST['byelaw'])
		$SYS_FORM['error']['byelaw'] = '規約・会則・約款に同意していただけない場合は参加することはできません。';

	$items = regist_data_get_reqs($gid);
	if($join_type == 1)
		$error .= regist_chk_req($items);
	if($SYS_FORM['error'] or $error)input_data($id,$error);
	if(!$items)regist_data($id);

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(value => get_gname($gid));
	$SYS_FORM["input"][] = array(title => '参加グループページ',
								 name  => 'gpage_name',
								 body  => get_form("plain", $attr));


	if($items){
		$body = '<table width="100%">'.regist_form($items,true).'</table>';
		$SYS_FORM["input"][] = array(title => '登録情報入力',
									 name  => 'comment',
									 body  => $body);
	}
/*
		// text:comment
		$attr = array(name => 'comment', value => $header,
					  cols => 64, rows => 8);
		$SYS_FORM["input"][] = array(title => 'コメント(あれば)',
									 name  => 'comment',
									 body  => get_form("textarea", $attr));
*/

	$SYS_FORM["action"] = 'entry.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '参加申請';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $id));

	$data = array(title   => 'グループ参加申請',
				  icon    => 'write',
				  message => 'このグループへ参加しますか？',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

/* フォーム*/
function input_data($id = null,$error = '') {
	global $COMUNI, $SYS_FORM, $COMUNI_HEAD_CSSRAW;

	$gid = get_gid($id);
	$uid = myuid();

	if (!is_login()) {
		$_SESSION["return"] = '/modules/glist/entry.php?eid='. $id;
		show_login('dialog');
	}

	$join_type = app_check($gid, $uid);
	$a = mysql_uniq("select * from group_joinable".
					" where gid = %s",
					mysql_num($gid));
	$terms = $a['terms'];
	$byelaw = $a['byelaw'];
	$notice = $a['notice'];

	// hidden:action
	$attr = array(name => 'action', value => 'confirm');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(value => get_gname($gid));
	$SYS_FORM["input"][] = array(title => '参加グループページ',
								 name  => 'gpage_name',
								 body  => get_form("plain", $attr));

	if($terms){
		$bhtml = nl2br($terms).'<hr>';
		$attr = array('name' => 'terms', 'option' => array(1 => '参加条件を確認'), 'value' => array(1 => $_REQUEST['terms']), 'bhtml' => $bhtml);
		$SYS_FORM["input"][] = array(title => '参加条件',
									name => 'terms',
									body => get_form("checkbox",$attr));
	}

	if($byelaw){
		$bhtml = nl2br($byelaw).'<hr>';
		$attr = array('name' => 'byelaw', 'option' => array(1 => '規約・会則・約款に同意'), 'value' => array(1 => $_REQUEST['byelaw']), 'bhtml' => $bhtml);
		$SYS_FORM["input"][] = array(title => '規約・会則・約款',
									name => 'byelaw',
									body => get_form("checkbox",$attr));
	}

	if($notice){
		$attr = array('value' => nl2br($notice));
		$SYS_FORM["input"][] = array(title => 'お知らせ',
									name => 'notice',
									body => get_form("plain",$attr));
	}

	if ($join_type == 1 or $join_type == 0) {
		$items = regist_data_get_reqs($gid);
		if($join_type == 0){
			foreach($items as &$item)
				$item['req'] = false;
		}
		if($items){
			$body = '<div style="color:red;">'.$error.'</div>'.
					'<table class="form_table">'.regist_form($items).'</table>';
			if($join_type != 0)
				$body = '<p>「<span class="required">*</span>」印は必須項目です</p>'.$body;
			$SYS_FORM["input"][] = array(title => '登録情報入力',
										 name  => 'comment',
										 body  => $body);
		}
/*
		// text:comment
		$attr = array(name => 'comment', value => $header,
					  cols => 64, rows => 8);
		$SYS_FORM["input"][] = array(title => 'コメント(あれば)',
									 name  => 'comment',
									 body  => get_form("textarea", $attr));
*/
	}

	$SYS_FORM["action"] = 'entry.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '参加申請';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $id));

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
.form_table{
	width: 100%;
}
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
__CSS__;

	$data = array(title   => 'グループ参加申請',
				  icon    => 'write',
				  message => 'このグループへ参加しますか？',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

function app_check($gid = null, $uid = null) {
	// 参加可能チェック
	$a = mysql_uniq("select * from group_joinable".
					" where gid = %s",
					mysql_num($gid));
	if(!$a){
		show_error("現在このグループには参加できません。");
	}
	$n = mysql_uniq("select count(uid) from group_member".
					" where gid = %s",
					mysql_num($gid));
	if ($n["count(uid)"] > $a["ent_max"]) {
		show_error("現在このグループは満員です。");
	}
	
	// 参加済チェック
	$b = mysql_uniq("select * from group_member".
					" where gid = %s and uid = %s",
					mysql_num($gid), mysql_num($uid));
	if ($b) {
		show_error("すでに参加済です。");
	}
	
	// 申請済みチェック
	$c = mysql_uniq("select * from group_app".
					" where gid = %s and uid = %s",
					mysql_num($gid), mysql_num($uid));
	if ($c) {
		show_error("すでに参加申請中です。管理者の処理をお待ち下さい。");
	}
	
	return $a["type"];
}

?>
