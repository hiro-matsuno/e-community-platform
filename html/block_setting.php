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
		break;
	default:
		input_setting($_REQUEST["eid"]);
}

function regist_setting($eid) {
	global $SYS_FORM;

	$eid = intval($eid);
	if (!is_owner($eid)) {
		die('You are not owner of '. $eid);
	}

	$name = htmlspecialchars($_POST["name"], ENT_QUOTES);

	if (!$name) {
		$SYS_FORM["error"]["name"] = 'パーツ名は何かいれてください。';
	}

	if (isset($SYS_FORM["error"])) {
		return;
	}

	$q = mysql_exec("update block set name = %s where id = %s",
					mysql_str($name),mysql_num($eid));

	if (!$q) {
		die("update failure...");
	}

	set_pmt(array(eid  => $eid, name => "pmt_0"));

	$data = array(title   => 'パーツの編集が完了しました。',
				  icon    => 'finish',
				  content => create_rform(array(eid => $eid, href => home_url($eid))));

	show_dialog2($data);
}

// $eid is block
function input_setting($eid) {
	global $SYS_FORM;

	$eid = intval($eid);
	if (!is_owner($eid)) {
		die('You are not owner of '. $eid);
	}

	$name = get_block_name($eid);

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'action',
														value => 'regist')));
	$SYS_FORM["input"][] = array(title => 'パーツのタイトル',
								 body => get_form("text",
												  array(name  => 'name',
														value => $name,
														size  => 32)));

	$SYS_FORM["action"] = 'block_setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"] = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'パーツの編集',
				  icon    => 'profile',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

?>
