<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once(dirname(__FILE__). '/../../lib.php');
require_once('Net/UserAgent/Mobile.php');
include_once(dirname(__FILE__). '/main.php');

global $COMUNI_HEAD_CSSRAW;

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : '';
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

switch ($act) {
	case 'post_exec':
		$html = mod_ml_post_exec($eid);
		header('Location: '. CONF_URLBASE. '/index.php?module=ml&pid='. $pid. '&eid='. $eid. '&action=finish');
	break;
	default:
		$html = '';
}

?>
