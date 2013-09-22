<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/block.php';

$id = $_REQUEST["blk_id"];

$data = mod_blog_calendar_block($id);

echo $data["content"];

?>
