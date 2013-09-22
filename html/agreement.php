<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require dirname(__FILE__). '/lib.php';

$q = mysql_uniq('select * from conf_agreement where id = 1');

$title = CONF_SITENAME. '利用規約';
$body  = $q['body'];

$data = array('title'   => CONF_SITENAME. '利用規約',
			  'icon'    => 'notice',
			  'message' => '',
			  'content' => $body);

show_dialog($data);

?>
