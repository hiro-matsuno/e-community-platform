<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/../../lib.php";
require_once dirname(__FILE__)."/classes/MemoGet.php";

$instance = new MemoGet( $_REQUEST );
echo $instance->get();

?>
