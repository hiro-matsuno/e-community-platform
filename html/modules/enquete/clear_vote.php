<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$module = htmlspecialchars(strip_tags($_REQUEST["module"]), ENT_QUOTES);
$eid    = intval($_REQUEST["eid"]);

if (!$eid) {
	die('please set eid...');
}
if (!is_owner($eid)) {
	die('You are not owner of '. $eid);
}

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'clear_vote.php';
	$SYS_FORM["submit"] = '回答結果のクリア';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'module',
														 value => $module)));

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'sure',
														 value => 1)));

	$comment = '回答結果をクリアしてよろしいですか？';
	$data = array(title   => '本当に削除しますか？',
				  icon    => 'warning',
				  content => $comment. create_confirm(array(eid => $eid)));

	show_dialog2($data);

	exit(0);
}

$d = mysql_uniq("select * from enquete_vote_data".
				" where eid = %s", mysql_num($eid));

if (!$d) {
	show_error('現在回答結果は空です。');
}

$q = mysql_exec("delete from enquete_vote_data".
				" where eid = %s", mysql_num($eid));

$q = mysql_exec("delete from mod_enquete_vcheck".
				" where eid = %s", mysql_num($eid));

$past_id = time();
$f = mysql_full("select * from mod_enquete_csv".
				" where eid = %s", mysql_num($eid));

if ($f) {
	while ($r = mysql_fetch_assoc($f)) {
		$i = mysql_exec('insert into mod_enquete_csv_past (eid, data, initymd, past_id)'.
						' values (%s, %s, %s, %s)',
						mysql_num($r['eid']), mysql_str($r['data']),
						mysql_str($r['initymd']), mysql_num($past_id));
	}
}

$q = mysql_exec("delete from mod_enquete_csv".
				" where eid = %s", mysql_num($eid));


$data = array(title   => '回答結果を削除しました。',
			  icon    => 'finish',
			  content => reload_form());

show_dialog2($data);

exit(0);

?>
