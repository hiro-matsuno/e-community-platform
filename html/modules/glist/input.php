<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/../../regist_lib.php';

define('MOD_GLIST_DEFAULT_ENT_MAX', 1000);

/* 振り分け*/
$id = (intval($_REQUEST["eid"]) > 0) ? $_REQUEST["eid"] : $_REQUEST["pid"];

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($id);
	default:
		input_data($id);
}

/* 登録*/
function regist_data($id = null) {
	global $SYS_FORM;

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	$gid = get_gid($id);
	$uid = myuid();

	$SYS_FORM["cache"]["type"]    = intval($_POST["join_type"]);
	$SYS_FORM["cache"]["ent_max"] = intval($_POST["ent_max"]);
	$SYS_FORM["cache"]["terms"] = htmlspecialchars($_POST["terms"]);
	$SYS_FORM["cache"]["byelaw"] = htmlspecialchars($_POST["byelaw"]);
	$SYS_FORM["cache"]["notice"] = htmlspecialchars($_POST["notice"]);

	// settingに登録
	$d = mysql_exec("delete from group_joinable where gid = %s", mysql_num($gid));
	$q = mysql_exec("insert into group_joinable".
					" (gid, type, ent_max, terms, byelaw, notice)".
					" values (%s, %s, %s, %s, %s, %s)",
					mysql_num($gid),
					mysql_num($SYS_FORM["cache"]["type"]),
					mysql_num($SYS_FORM["cache"]["ent_max"]),
					mysql_str($SYS_FORM["cache"]["terms"]),
					mysql_str($SYS_FORM["cache"]["byelaw"]),
					mysql_str($SYS_FORM["cache"]["notice"]));

	if (!$q) {
		die("insert failure...");
	}

	if($SYS_FORM["cache"]["type"]==0){
		$d = mysql_exec("select * from group_app where gid = %s",mysql_num($gid));
		if($d){
			while($app_user=mysql_fetch_array($d)){
				join_group(array('uid'=>$app_user['uid'],'gid'=>$gid,'level'=>10));
			}
		}
		$d = mysql_exec("delete from group_app where gid = %s",mysql_num($gid));
	}

	regist_form_regist($gid);

	$html = '設定完了しました。';
	$data = array(title   => 'グループ参加設定',
				  icon    => 'finish',
				  content => $html. create_rform(array(eid => $id, href => home_url($id))));

	show_dialog2($data);

	exit(0);
}

/* フォーム*/
function input_data($id = null) {
	global $SYS_FORM, $JQUERY;

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	$gid = get_gid($id);
	$uid = myuid();

	$d = mysql_uniq("select * from group_joinable where gid = %s",
					mysql_num($gid));

	// settingからロード
	if ($d) {
		$join_type = $d["type"];
		$ent_max   = $d["ent_max"];
		$terms = $d["terms"];
		$byelaw = $d["byelaw"];
		$notice = $d["notice"];
	}
	else {//初回設定
		$join_type    = 0;
		$ent_max      = MOD_GLIST_DEFAULT_ENT_MAX;
		mysql_exec('insert into prof_add_req'.
					' (gid,type,title,comment,opt_size,opt_list,def_val)'.
					' select %s,type,title,comment,opt_size,opt_list,def_val'.
					' from prof_add_req where gid=10000 order by req_id',
					mysql_num($gid));
		$new_req_id = mysql_insert_id();
		mysql_exec('insert into join_req_info (gid,num,req_id,req_check,def_val)'.
					' select %s,num,req_id-11+%s,req_check,def_val'.
					' from join_req_info where gid = 10000',
					mysql_num($gid),mysql_num($new_req_id));
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$join_type  = $SYS_FORM["cache"]["join_type"];
		$ent_max    = $SYS_FORM["cache"]["ent_max"];
		$terms = $SYS_FORM["cache"]["terms"];
		$byelaw = $SYS_FORM["cache"]["byelaw"];
		$notice = $SYS_FORM["cache"]["notice"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));


	// plain:gname
	$attr = array(value => get_gname($gid));
	$SYS_FORM["input"][] = array(title => 'グループページ名',
								 name  => 'gname',
								 body  => get_form("plain", $attr));

	// radio:join_type
	$option = array(0 => '誰でもすぐに参加',
					1 => 'グループ管理者の承認が必要',
					2 => '参加不可');

	$attr = array(name => 'join_type', value => $join_type, option => $option);
	$SYS_FORM["input"][] = array(title => '参加設定',
								 name  => 'join_type',
								 body  => get_form("radio", $attr));

	// text:ent_max
	$attr = array(name => 'ent_max', value => $ent_max, size => 3);
	$SYS_FORM["input"][] = array(title => '最大登録人数',
								 name  => 'ent_max',
								 body  => get_form("num", $attr));

	// textarea:terms
	$attr = array(name => 'terms', value => $terms);
	$SYS_FORM["input"][] = array(title => "参加条件",
								name => 'terms',
								body => get_form("textarea", $attr));

	// textarea:byelaw
	$attr = array(name => 'byelaw', value => $byelaw);
	$SYS_FORM["input"][] = array(title => "規約・会則・約款",
								name => 'byelaw',
								body => get_form("textarea", $attr));

	// textarea:notice
	$attr = array(name => 'notice', value => $notice);
	$SYS_FORM["input"][] = array(title => "お知らせ",
								name => 'notice',
								body => get_form("textarea", $attr));

	//登録項目の設定
	$SYS_FORM["input"][] = array(title => '追加登録事項のレイアウト',
								name => 'items',
								body => regist_form_create_html(regist_data_get_reqs($gid)));
								 
	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid, pid => $id));

	$data = array(title   => 'グループの参加設定',
				  icon    => 'write',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

?>
