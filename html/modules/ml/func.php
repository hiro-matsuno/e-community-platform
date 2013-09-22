<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_ml_is_join($eid = 0) {
	$q = mysql_uniq('select * from mod_ml_member'.
					' where ml_id = %s and uid = %s',
					mysql_num($eid), mysql_num(myuid()));

	if ($q && $q['status'] > 0) {
		return true;
	}
	return false;
}

?>
