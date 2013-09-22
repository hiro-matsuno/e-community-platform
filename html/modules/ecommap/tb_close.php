<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/** thickboxを閉じる */
$reload = $_REQUEST["reload"];
?>
<html>
<head>
</head>
<body onload="parent.tb_remove();<?=$reload?"parent.location.reload();":""?>">
</body>
</html>