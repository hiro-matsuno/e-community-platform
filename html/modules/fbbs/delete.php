<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
//require_once dirname(__FILE__). '/main.php';

$eid       = intval($_REQUEST["eid"]);
$thread_id = intval($_REQUEST["thread_id"]);

if (!$eid) {
	die('please set eid...');
}
if (!is_owner($eid) && !is_owner($thread_id) && !mod_fbbs_is_owner($eid) && !mod_fbbs_is_owner($thread_id)) {
	die('You are not owner of '. $eid);
}

$d = mysql_uniq("select d.* from mod_fbbs_data as d".
				' inner join mod_fbbs_element_relation as e on d.id = e.id'.
				" where d.id = %s", mysql_num($eid));

if (!$d) {
	die($eid. " is not exist.");
}

$str .= '<div style="padding: 10px;">';
$str .= '<h3 class="mod_fbbs_thread_title">'. $d['title']. '</h3>';
$str .= '<div style="font-size: 0.9em; padding: 5px; text-align: right;">'.
		' 投稿者: '. 
		mod_fbbs_get_author($d['uid'], $d['name'], $d['mail'], $d['url']).
		' 投稿日: '.
		date('n月d日 G時i分', tm2time($d['updymd'])).
		'</div>';
$str .= '<div class="mod_fbbs_thread_body">'. $d['body']. '</div>';
$str .= '</div>';

$add_msg = ''; $detele_res = false;
if ($d['parent_id'] == 0) {
	$add_msg = 'スレッドへのレスもすべて削除されます。';
	$detele_res = true;
}
$parent_id = intval($d['parent_id']);

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'delete.php';
	$SYS_FORM["submit"] = '記事の消去';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'sure',
														 value => 1)));


	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'thread_id',
														 value => $thread_id)));


	$comment = 'この記事を削除してよろしいですか？<br>'. $add_msg. $str;
	$data = array(title   => '本当に削除しますか？',
				  icon    => 'warning',
				  content => $comment. create_confirm(array(eid => $eid)));

	show_dialog($data);

	exit(0);
}

$back_href = home_url($eid);

$q = mysql_exec("delete from mod_fbbs_data".
				" where id = %s", mysql_num($eid));

$u = mysql_exec('update mod_fbbs_data set parent_id = %s'.
				' where parent_id = %s',
				mysql_num($parent_id), mysql_num($eid));

/*
if ($detele_res == true) {
	$q = mysql_exec("delete from mod_fbbs_data".
					" where parent_id = %s", mysql_num($eid));
}
*/

if ($parent_id == 0) {
	global $SYS_FORM;

	$SYS_FORM["submit"]   = "ページへ戻る";
	$SYS_FORM["onSubmit"] = "parent.location.href = '${back_href}'; return false;";

	$form = create_form();
}
else {
	$form = reload_form();
}

$data = array(title   => '記事を削除しました。',
			  icon    => 'finish',
			  content => $form);

show_dialog($data);

exit(0);

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

function mod_fbbs_get_author($uid = 0, $name = null, $mail = null, $url = null) {
	if ($uid == 0) {
		$name = isset($name) ? $name : '名無し';
		if ($mail && $mail != '') {
			$name = '<a href="mailto:'. $mail. '">'. $name. '</a>';
		}
		return $name. mod_fbbs_get_url($url);
	}
	return mod_fbbs_get_href(get_handle($uid), $url);
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


?>
