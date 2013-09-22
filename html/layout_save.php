<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require_once 'Calendar/Month/Weekdays.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

session_start();

$uid = $COMUNI["uid"];

$f = mysql_exec("delete from demo_c where uid = %s", mysql_num($uid));

foreach ($_POST as $key => $value) {
	switch ($key) {
		case 'space_1':
			$vpos = 1;
			break;
		case 'space_2':
			$vpos = 2;
			break;
		case 'space_3':
			$vpos = 3;
			break;
		case 'space_4':
			$vpos = 4;
			break;
		case 'space_5':
			$vpos = 5;
			break;
		default:
			$vpos = 1;
			break;
	}

	if ($vpos > 0) {
		$ids = split(',', $value);
		$hpos = 0;
		foreach ($ids as $id) {
			$r = mysql_exec("insert into demo_c (id, uid, vpos, hpos) values (%s, %s, %s, %s);",
							mysql_num($id), mysql_num($uid), mysql_num($vpos), mysql_num($hpos));
			if (!$r) { die('error...'. "${id}/${uid}/${vpos}/${hpos}"); }
			$hpos++;
		}
	}
}

echo 'saved!!';

?>
