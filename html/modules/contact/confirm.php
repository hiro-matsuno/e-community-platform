<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

unset_session('/^mod_contact/');

$eid = intval($_REQUEST['eid']);

$q = mysql_full('select d.*, p.position from mod_contact_form_data as d'.
				' inner join mod_contact_form_pos as p on d.id = p.id'.
				' where d.eid = %s order by p.position',
				mysql_num($eid));

if ($q) {
	while ($res = mysql_fetch_assoc($q)) {
		$data = $_POST['q'. $eid. '_'. $res['position']];
		$_SESSION['mod_contact_data'][$res['position']] = $_POST['q'. $eid. '_'. $res['position']];
		if ($res['req_check'] > 0) {
			if (!isset($data) || $data == '') {
				$_SESSION['mod_contact_error'][$res['position']] = true;
			}
		}
	}
}

if (count($_SESSION['mod_contact_error']) > 0) {
	header('Location: '. CONF_URLBASE. '/index.php?module=contact&eid='. $eid. '&blk_id='. $eid. '&rewrite=1');
}
else {
	$_SESSION['mod_contact_confirm'] = true;
	header('Location: '. CONF_URLBASE. '/index.php?module=contact&eid='. $eid. '&blk_id='. $eid. '&confirm=1');
}

?>

