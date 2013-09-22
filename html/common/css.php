<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../lib.php';

$skin_id = $_GET["id"] ? intval($_GET["id"]) : 0;

$skin_id = 0;

$c = mysql_uniq('select css from common_css where id = %s', mysql_num($skin_id));

header('Content-Type: text/css');

if ($c) {
	echo $c['css'];
}

?>
