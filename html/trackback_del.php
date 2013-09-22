<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

$id  = intval($_REQUEST["id"]);
$eid = 0;

$c = mysql_uniq('select * from trackback where id = %s', mysql_num($id));

if ($c) {
	if (!is_owner($c['eid'])) {
		show_error('権限がありません。');
		exit(0);
	}
	$eid = $c['eid'];
}

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'trackback_del.php';
	$SYS_FORM["submit"] = 'トラックバック消去';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$body = get_form("hidden", array(name => 'id', value => $id));
	$SYS_FORM["input"][] = array(body => $body);

	$body = get_form("hidden", array(name => 'sure', value => 1));
	$SYS_FORM["input"][] = array(body => $body);

	$comment = 'このトラックバックを削除してよろしいですか？';

	$data = array(title   => '本当に削除しますか？',
				  icon    => 'warning',
				  content => $comment. create_confirm(array(eid => $eid)));

	show_dialog2($data);

	exit(0);
}

$q = mysql_exec("delete from trackback where id = %s", mysql_num($id));

$data = array(title   => 'トラックバックの削除',
			  icon    => 'finish',
			  content => 'トラックバックを削除しました。'. reload_form(array(eid => $eid)));

show_dialog2($data);

exit(0);

?>
