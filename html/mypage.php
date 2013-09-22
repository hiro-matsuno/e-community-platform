<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

$uid   = myuid();

if ($uid == 0) {
	header('Location: /');
}
else {
	$q = mysql_uniq('select * from page where uid = %s', mysql_num($uid));

	if ($q) {
		header('Location: /user.php?uid='. $uid);
	}
	else {
		header('Location: /manager/site/select.php');
	}
}

exit(0);

?>
