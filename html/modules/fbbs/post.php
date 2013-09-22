<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once(dirname(__FILE__). '/../../lib.php');
include_once(dirname(__FILE__). '/main.php');

global $COMUNI_HEAD_CSSRAW;

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : '';
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

$thread_id = isset($_REQUEST['thread_id']) ? intval($_REQUEST['thread_id']) : 0;
$top_id = isset($_REQUEST['top_id']) ? intval($_REQUEST['top_id']) : 0;

switch ($act) {
	case 'regist_thread':
		$html = mod_fbbs_regist_thread();
		$msg  = 'regist_thread';
	break;
	case 'regist_response':
		$html = mod_fbbs_regist_response();
		$msg  = 'regist_response';
	break;
	default:
		$html = '';
		$msg  = '';
}

if (isset($SYS_FORM["error"])) {
	$_SESSION['mod_fbbs_cache'] = $SYS_FORM["cache"];
	$_SESSION['mod_fbbs_error'] = $SYS_FORM["error"];

	switch ($act) {
		case 'regist_thread':
			$href = '&action=input_thread&pid='. $pid. '&blk_id='. $pid;
		break;
		case 'regist_response':
			$href = '&action=input_response&eid='. $pid. '&thread_id='. $thread_id. '&pid='. $thread_id;
			if ($top_id > 0) {
				$href .= '&top_id='. $top_id;
			}
		break;
		default:
			;
	}
	header('Location: '. CONF_URLBASE. '/index.php?module=fbbs'. $href);
	exit(0);
}

header('Location: '. CONF_URLBASE. '/index.php?module=fbbs&pid='. $pid. '&eid='. $pid. '&thread_id='. $thread_id. '&top_id='. $top_id. '&msg='. $msg);

?>
