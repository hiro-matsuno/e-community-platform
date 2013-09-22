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
		header('Location: /modules/profile/input.php?uid='.$uid);
}

exit(0);

?>
