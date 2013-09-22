<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require_once dirname(__FILE__). '/../../lib.php';

$id = intval($_REQUEST["id"]);

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'delete.php';
	$SYS_FORM["submit"] = '入力フォームの消去';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array('body' => get_form("hidden", array('name'  => 'id', 'value' => $id)));
	$SYS_FORM["input"][] = array('body' => get_form("hidden", array('name'  => 'sure', 'value' => 1)));

	$comment = 'このフォームを削除してよろしいですか？';
	$data = array('title'   => '本当に削除しますか？',
				  'icon'    => 'warning',
				  'content' => $comment. create_confirm(array('eid' => $eid)));

	show_dialog($data);

	exit(0);
}

$d = mysql_uniq("select * from mod_contact_form_data where id = %s", mysql_num($id));

if (!$d) {
	show_error('フォームの指定に誤りがあります。');
}

if (!is_owner($d['eid'])) {
	show_error('削除する権限がありません。');
}

$q = mysql_exec("delete from mod_contact_form_data where id = %s", mysql_num($id));
$q = mysql_exec("delete from mod_contact_form_pos where id = %s", mysql_num($id));

$data = array('title'   => '記事を削除しました。',
			  'icon'    => 'finish',
			  'content' => reload_form(array('string'=>'了解')));

show_dialog2($data);

exit(0);

?>
