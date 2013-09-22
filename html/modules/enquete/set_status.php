<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
include_once dirname(__FILE__). '/func.php';

$pid    = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
$target = isset($_REQUEST['t']) ? intval($_REQUEST['t']) : 0;

if (!is_owner($pid)) {
	error_window('この操作を行う権限がありません。');
}

change_status($target, $pid);

header('Location: edit.php?pid='. $pid);

function close_status($eid = null, $pid = null) {
	$d = mysql_exec("delete from enquete_status where pid = %s and eid = %s",
					mysql_num($pid), mysql_num($eid));
}

function change_status($eid = null, $pid = null) {
	if (!$pid) {
		$pid = mod_enquete_get_pid($eid);
	}

	$d = mysql_exec("delete from enquete_status where pid = %s",
					mysql_num($pid));
	$q = mysql_exec("insert into enquete_status (pid, eid) values (%s, %s)",
					mysql_num($pid), mysql_num($eid));
}

?>
