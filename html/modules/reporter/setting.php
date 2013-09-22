<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

session_start();

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

//var_dump($_SESSION);
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$msg = $_POST['block_msg'];
	$auth_mode = intval($_POST['auth_mode']);

	$d = mysql_exec('delete from reporter_setting where id = %s',
					mysql_num($eid));
	$q = mysql_exec('insert into reporter_setting'.
					' (id, msg, auth_mode) values (%s, %s, %s)',
					mysql_num($eid), mysql_str($msg), mysql_num($auth_mode));

	$target_g = $_POST['target_g'];
	$target_m = $_POST['target_m'];

	$target = array_merge($target_g, $target_m);

	$c = mysql_full('select * from reporter_block where eid = %s', $eid);
	$cache = array();
	if ($c) {
		while ($r = mysql_fetch_array($c)) {
			$cid = $r['eid']. '_'. $r['site_id'];
			$cache[$cid] = $r['block_id'];
		}
	}

	$d = mysql_exec('delete from reporter_block where eid = %s', $eid);
	foreach ($target as $t) {
		if (isset($cache[$eid. '_'. $t])) {
			$block_id = $cache[$eid. '_'. $t];
		}
		else {
			$block_id = create_block($t);
		}
		$q = mysql_exec('insert into reporter_block'.
						' (eid, site_id, block_id)'.
						' values(%s, %s, %s)',
						mysql_num($eid), mysql_num($t), $block_id);
	}

	set_keyword($eid,$pid);

	$html = '基本設定を登録しました。';
	$data = array(title   => '市民レポーター基本設定完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function create_block($site_id = null) {
	$mod_name = 'blog';
	$name     = '市民レポーター';

	$c = mysql_uniq("select max(hpos) from block where pid = %s and vpos = 1",
					mysql_num($site_id));

	if ($c) {
		$hpos = intval($c["max(hpos)"]) + 1;
	}
	else {
		$hpos = 0;
	}

	$new_id = get_seqid();

	$q = mysql_exec("insert into block (id, pid, module, name, hpos, vpos)".
					" values(%s, %s, %s, %s, %s, %s)",
					mysql_num($new_id), mysql_num($site_id), mysql_str($mod_name),
					mysql_str($name),mysql_num($hpos), mysql_num(1));
	if (!$q) {
		die("update failure...".mysql_error());
	}

	set_pmt(array(eid  => $new_id, uid => get_uid($site_id), gid => get_gid($site_id), name => "pmt_0"));

	return $new_id;
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$eid = $pid;

	if ($pid > 0) {
		$d = mysql_uniq("select * from reporter_setting where id = %s",
						mysql_num($pid));
	}

	if ($d) {
		$msg = $d["msg"];
		$auth_mode = $d["auth_mode"];
	}
	else {
		$auth_mode = 0;
		$msg = '';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:subject
	$a = mysql_full('select * from reporter_block where eid = %s',
					mysql_num($eid));
	while ($r = mysql_fetch_array($a)) {
		$value[$r['site_id']] = $r['block_id'];
	}

	$q = mysql_full('select * from page where gid > 0 order by updymd');
	while ($r = mysql_fetch_array($q)) {
		$option[$r['id']] = $r['sitename'];
	}
	$bhtml = 'レポーター用投稿ブログを設置するグループを選択して下さい。<br>';
	$attr = array(name => 'target_g', value => $value, option => $option, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => '投稿ブログインストール先 (グループ)',
								 name  => 'target_g',
								 body  => get_form("checkbox", $attr));

	unset($option);

	// text:subject
	$q = mysql_full('select * from page where uid > 0 order by updymd');
	while ($r = mysql_fetch_array($q)) {
		$option[$r['id']] = $r['sitename'];
	}
	$bhtml = 'レポーター用投稿ブログを設置するマイページを選択して下さい。<br>';
	$attr = array(name => 'target_m', value => $value, option => $option, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => '投稿ブログインストール先 (マイページ)',
								 name  => 'target_m',
								 body  => get_form("checkbox", $attr));

	$bhtml = 'レポーター用投稿ブログで、投稿者に表示するメッセージを入力してください。<br>';
	$attr = array(name => 'block_msg', value => $msg, rows => 5, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => '投稿ブログブロック表示メッセージ',
								 name  => 'block_msg',
								 body  => get_form("textarea", $attr));

	$option = array('1' => '自動で承認する');
	$bhtml = '投稿記事を自動的に承認する場合は下記にチェックを入れて下さい。<br>';
	$attr = array(name => 'auth_mode', value => $auth_mode, option => $option, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => '記事の自動承認',
								 name  => 'auth_mode',
								 body  => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["keyword"] = true;
	$SYS_FORM["pmt"]     = false;
	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '市民レポーター基本設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
