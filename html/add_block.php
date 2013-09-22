<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_setting($_REQUEST["eid"]);
	default:
		input_setting($_REQUEST["eid"]);
}

function regist_setting($eid) {
	global $SYS_FORM;

	$eid = intval($eid);
	if (!is_owner($eid)) {
		die('You are not owner of '. $eid);
	}

	$name     = htmlspecialchars($_POST["name"], ENT_QUOTES);
	$mod_name = htmlspecialchars($_POST["mod_name"], ENT_QUOTES);
	$column   = intval($_POST['column']);
	if ($column == 0) {
		$column = 1;
	}

	$ms = mysql_uniq("select * from module_setting where mod_name = %s",
						 mysql_str($mod_name));
	if ($name == '') {
		$name = get_module_name($mod_name);
		if(!$name){
			show_error("パーツが不明です。");
		}
	}

	$c = mysql_uniq("select count(*) from block where pid = %s and module = %s",
					mysql_num($eid), mysql_str($mod_name));

	if (($ms["multiple"] == 0) && intval($c["count(*)"]) > 0) {
		show_error("このモジュールは１つしか設置できません。");
	}

	$c = mysql_uniq("select max(hpos) from block where pid = %s and vpos = 1",
					mysql_num($eid));

	if ($c) {
		$hpos = intval($c["max(hpos)"]) + 1;
	}
	else {
		$hpos = 0;
	}

	$new_id = get_seqid();

	$q = mysql_exec("insert into block (id, pid, module, name, hpos, vpos)".
					" values(%s, %s, %s, %s, %s, %s)",
					mysql_num($new_id), mysql_num($eid), mysql_str($mod_name),
					mysql_str($name), mysql_num($hpos), mysql_num($column));
	if (!$q) {
		die("update failure...");
	}

	set_pmt(array(eid  => $new_id, gid => get_gid($eid), name => "pmt_0"));

	ModuleManager::getInstance()->getModule( $mod_name )
		->execCallbackFunction( "add_block", array( (int)$new_id ), $result );

	$data = array(title   => 'パーツを追加しました。',
				  icon    => 'finish',
				  content => create_rform(array(eid => $eid,
										  href => home_url($eid))));

	show_dialog2($data);

	exit(0);
}

function input_setting($eid) {
	global $SYS_FORM;

	$eid = intval($eid);
	if (!is_owner($eid)) {
		die('You are not owner of '. $eid);
	}

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'action',
														value => 'regist')));

	$ahtml = '指定しない場合はパーツ標準のタイトルになります。';
	$SYS_FORM["input"][] = array(title => 'パーツのタイトル',
								 name  => 'name',
								 body  => get_form("text",
												   array(name  => 'name',
														 value => '',
														 size  => 32,
														 ahtml => $ahtml)));

	// portal 1 
	// group  2
	// mypage 4
	if (is_portal(get_gid($eid))) {
		$subq = array(1, 3, 5, 7);
	}
	else if (is_group($eid)) {
		$subq = array(2, 3, 6, 7);
	}
	else {
		$subq = array(4, 5, 6, 7);
	}
	if(is_admin())
		array_push($subq,0);
	$m = mysql_full("select * from module_setting where type in %s and addable > 0",
					mysql_numin($subq));
	while ($d = mysql_fetch_array($m)) {
		$option[$d["mod_name"]] = $d["mod_title"];
	}

	$SYS_FORM["input"][] = array(title => '設置パーツ',
								 body => get_form("select",
												  array(name  => 'mod_name',
														option => $option)));

	$b = mysql_uniq('SELECT page.skin, t.*'.
					' FROM page'.
					' INNER JOIN theme_skin AS l ON page.skin = l.id'.
					' INNER JOIN theme_layout AS t ON l.layout_id = t.id'.
					' WHERE page.id = %s',
					mysql_num($eid));

	$option = array();
	$value  = 1;
	if ($b) {
		$column = $b['column'];
		switch ($column) {
			case 5:
				$option = array(2 => '左カラム', 1 => '中央上カラム', 3 => '右カラム',
								4 => '中央下左カラム', 5 => '中央下右カラム');
				break;
			case 3:
				$option = array(2 => '左カラム', 1 => '中央カラム', 3 => '右カラム');
				break;
			case 2:
				$option = array(2 => '左カラム', 1 => '右カラム');
				break;
			default:
				$option = array(1 => '中央カラム');
		}
		$value = 1;
	}
	else {
		$option = array(1 => '中央カラム');
	}

	$SYS_FORM["input"][] = array(title => '設置場所',
								 body => get_form("radio",
												  array(name   => 'column',
														value  => $value,
														option => $option,)));

	$SYS_FORM["action"] = 'add_block.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"] = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'パーツの追加',
				  icon    => 'plus',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

?>
