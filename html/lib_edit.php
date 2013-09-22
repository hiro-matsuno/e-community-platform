<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

global $SYS_EDIT;

$SYS_EDIT = array();

$SYS_EDIT['setting'] = isset($_REQUEST['setting']) ? $_REQUEST['setting'] : '';
$SYS_EDIT['action']  = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

list($SYS_EDIT['eid'], $SYS_EDIT['pid']) = get_edit_ids();

?>
