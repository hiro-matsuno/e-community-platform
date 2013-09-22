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

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

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

	$d = mysql_exec('delete from bosai_web_setting where id = %s',
					mysql_num($eid));
	$q = mysql_exec('insert into bosai_web_setting'.
					' (id, msg) values (%s, %s)',
					mysql_num($eid), mysql_str($msg));

	$target_g = ($_POST['target_g']? $_POST['target_g']: array());
	$target_m = ($_POST['target_m']? $_POST['target_m']: array());

	$target = array_merge($target_g, $target_m);

	$c = mysql_full('select * from bosai_web_block where eid = %s', $eid);
	$cache = array();
	if ($c) {
		while ($r = mysql_fetch_array($c)) {
			$cid = $r['eid']. '_'. $r['site_id'];
			$cache[$cid] = $r['block_id'];
		}
	}

	mysql_query('lock tables block, bosai_web__block');
	$d = mysql_exec('update block inner join bosai_web_block'.
					' on block.id=bosai_web_block.block_id'.
					' set block.del_lock=0 where eid=%s',$eid);
	
	$d = mysql_exec('delete from bosai_web_block where eid = %s', $eid);
	foreach ($target as $t) {
		if (isset($cache[$eid. '_'. $t])) {
			$block_id = $cache[$eid. '_'. $t];
		}
		else {
			$block_id = create_block($t);
		}
		$q = mysql_exec('insert into bosai_web_block'.
						' (eid, site_id, block_id)'.
						' values(%s, %s, %s)',
						mysql_num($eid), mysql_num($t), mysql_num($block_id));
	}
	$d = mysql_exec('update block inner join bosai_web_block'.
					' on block.id=bosai_web_block.block_id'.
					' set block.del_lock=1 where eid=%s',$eid);
	mysql_exec('unlock tables');
	
	set_keyword($eid, $pid);

	$html = '基本設定を登録しました。';
	$data = array(title   => '防災Web基本設定完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function create_block($site_id = null) {
	$mod_name = 'blog';
	$name     = '防災Web投稿パーツ';

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
					mysql_str($name), mysql_num($hpos), mysql_num(1));
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
		$d = mysql_uniq("select * from bosai_web_setting where id = %s",
						mysql_num($pid));
	}

	if ($d) {
		$msg = $d["msg"];
	}
	else {
		$msg = '';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:subject
	$a = mysql_full('select * from bosai_web_block where eid = %s',
					mysql_num($eid));
	if ($a) {
		while ($r = mysql_fetch_array($a)) {
			$value[$r['site_id']] = $r['block_id'];
		}
	}

	$q = mysql_full('select * from page where gid > 0 order by updymd');
	while ($r = mysql_fetch_array($q)) {
		$option[$r['id']] = $r['sitename'];
	}
	$bhtml = '記事投稿パーツを設置するグループを選択して下さい。<br>';
	$attr = array(name => 'target_g', value => $value, option => $option, bhtml => $bhtml, break_num => 1);
	$SYS_FORM["input"][] = array(title => '記事投稿パーツインストール先 (グループ)',
								 name  => 'target_g',
								 body  => get_form("checkbox", $attr));

	unset($option);

	// text:subject
	$q = mysql_full('select * from page where uid > 0 order by updymd');
	while ($r = mysql_fetch_array($q)) {
		$option[$r['id']] = $r['sitename'];
	}
	$bhtml = '記事投稿パーツを設置するマイページを選択して下さい。<br>';
	$attr = array(name => 'target_m', value => $value, option => $option, bhtml => $bhtml, break_num => 1);
	$SYS_FORM["input"][] = array(title => '記事投稿パーツインストール先 (マイページ)',
								 name  => 'target_m',
								 body  => get_form("checkbox", $attr));

	$bhtml = '記事投稿パーツで、投稿者に表示するメッセージを入力してください。<br>';
	$attr = array(name => 'block_msg', value => $msg, rows => 5, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => '記事投稿パーツ表示メッセージ',
								 name  => 'block_msg',
								 body  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["keyword"] = true;
	$SYS_FORM["pmt"]     = false;
	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '防災Web基本設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
