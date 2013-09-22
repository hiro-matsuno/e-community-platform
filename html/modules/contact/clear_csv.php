<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$eid  = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

if (!is_owner($id)) {
	error_window('この機能はサイト管理者のみ使用できます。');
	exit(0);
}

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'clear_csv.php';
	$SYS_FORM["submit"] = 'CSVデータの削除';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array(body => get_form("hidden", array('name'  => 'sure', 'value' => 1)));

	$comment = 'CSVデータを削除してよろしいですか？';
	$data = array('title'   => '本当に削除しますか？',
				  'icon'    => 'warning',
				  'content' => $comment. create_confirm(array('eid' => $eid)));

	show_dialog($data);

	exit(0);
}

$q = mysql_exec("delete from mod_contact_send_data where eid = %s",
				mysql_num($eid));

$data = array('title'   => 'CSVデータを削除しました。',
			  'icon'    => 'finish',
			  'content' => reload_form(array('string'=>'了解')));

show_dialog($data);

exit(0);

?>
