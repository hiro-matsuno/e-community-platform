<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

$eid = intval($_POST["eid"] ? $_POST["eid"] : $_GET["eid"]);

$f = mysql_uniq("select * from owner where id = %s;", mysql_num($eid));

if ($f["id"] == 0) {
	$jump = '/index.php';
}
else if ($f["gid"] > 0) {
	$jump = '/group.php?gid='. $f["gid"];
}
else {
	$jump = '/user.php?uid='. $f["uid"];
}

if (isset($_GET["nosave"])) {
	header("Location: ". $jump);
	exit(0);
}

if (!$_POST["save"]) {
	$f = mysql_uniq("select * from owner where id = %s;", mysql_num($eid));

	if ($f["id"] == 0) {
		$jump = '/index.php?setting=layout';
	}
	else if ($f["gid"] > 0) {
		$jump = '/group.php?setting=layout&gid='. $f["gid"];
	}
	else {
		$jump = '/user.php?setting=layout&uid='. $f["uid"];
	}

	header("Location: ". $jump);
	exit(0);
}

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
			$r = mysql_exec("update block set vpos = %s, hpos = %s where id = %s;",
							mysql_num($vpos), mysql_num($hpos), mysql_num($id));
			$hpos++;
		}
	}
}

echo $jump;

?>
