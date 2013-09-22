<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$eid    = intval($_REQUEST['eid']);
$uniqid = floatval($_REQUEST['uniqid']);

if ($_REQUEST['action'] == 'regist') {
	$opt_list = $_POST['c_list'];
	$opt_list = ereg_replace("\r\n", "-_-", $opt_list);
	$opt_list = ereg_replace("\r", "-_-", $opt_list);
	$opt_list = ereg_replace("\n", "-_-", $opt_list);

	$opt_list = preg_replace('/-_-$/', '', $opt_list);

	$u = mysql_exec('update enquete_form_data set'.
					' type = %s, title = %s, req_check = %s, admin_only = %s, comment = %s, opt_size = %s, opt_list = %s'.
					' where eid = %s and uniq_id = %s',
					mysql_str($_POST['type']), mysql_str($_POST['c_title']),
					mysql_num(intval($_POST['req_check'])), mysql_num(intval($_POST['admin_only'])),
					mysql_str($_POST['c_note']),
					mysql_str($_POST['c_size']), mysql_str($opt_list),
					mysql_num($eid), $uniqid);

	$SYS_FORM["submit"]  = '編集完了';
	$SYS_FORM["cancel"]  = '取消';
//	$SYS_FORM["onCancel"]  = "location.href = 'input.php?eid=". $eid. "'; return false;";
	$SYS_FORM["onCancel"]  = 'parent.tb_remove(); return false;';

	$href = 'input.php?action=canvas&reload=1&eid='. $eid;

	$data = array(title   => 'データを変更しました。',
				  icon    => 'finish',
				  content => create_rform(array('href'=>$href)));

	show_dialog($data);

	exit(0);
}

$q = mysql_uniq('select * from enquete_form_data where eid = %s and uniq_id = %s',
				mysql_num($eid), $uniqid);

if (!$q) {
	show_error('新規で追加されたフォームは、一度登録を行うまで再編集できません。');
}


	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'uniqid', value => $uniqid);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'type', value => $q['type']);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'c_title', value => $q['title'], size => 32);
	$SYS_FORM["input"][] = array('title' => '題名', 'body' => get_form("text", $attr));

	$attr = array(name => 'req_check', value => $q['req_check'], option => array(1 => '必須項目'));
	$SYS_FORM["input"][] = array('body' => get_form("checkbox", $attr));

	$attr = array(name => 'c_note', value => $q['comment'], size => 48);
	$SYS_FORM["input"][] = array('title' => 'コメント', 'body' => get_form("text", $attr));

	if ($q['type'] == 'text') {
		$attr = array(name => 'c_size', value => $q['opt_size'], size => 4);
		$SYS_FORM["input"][] = array('title' => '入力サイズ', 'body' => get_form("text", $attr));
	}
	else {
		$attr = array(name => 'c_list', value => preg_replace('/-_-/', "\n", $q['opt_list']));
		$SYS_FORM["input"][] = array('title' => 'リスト入力 (改行区切り)', 'body' => get_form("textarea", $attr));
	}

	$attr = array(name => 'admin_only', value => $q['admin_only'], option => array(1 => 'この項目の結果はアンケート作成者だけに表示'));
	$SYS_FORM["input"][] = array('body' => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'subedit.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = '編集完了';
	$SYS_FORM["cancel"]  = '取消';
//	$SYS_FORM["onCancel"]  = "location.href = 'input.php?eid=". $eid. "'; return false;";
	$SYS_FORM["onCancel"]  = 'parent.tb_remove(); return false;';

	$form_html .= create_form(array(eid => $eid));

	$data = array(title   => 'アンケートフォームの編集',
				  icon    => 'write',
				  content => $form_html);

	show_dialog($data);
?>

