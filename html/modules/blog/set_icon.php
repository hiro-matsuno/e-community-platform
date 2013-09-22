<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

session_start();

$uid = $COMUNI["uid"];

$COMUNI_HEAD_CSS[] = '/ui.tabs.css';

$q = mysql_exec("select * from icons");

$divs = '';
while ($r = mysql_fetch_array($q)) {
	$divs .= <<<__IMGDIV__
<div style="vertial-align: top; text-align: center; padding: 5px; float: left;">
  <a href="#" onClick= "parent.setIcon('${r["id"]}', '${r["path"]}', '${r["size_x"]}', '${r["size_y"]}', '${r["xunit"]}', '${r["yunit"]}'); parent.tb_remove(); return false;"><img src="${r["path"]}" width="${r["size_x"]}" height="${r["size_y"]}" alt="${r["summary"]}" border="0"></a>
</div>
__IMGDIV__;
	;
}

$contents = <<<__MAP_FORM__
<div style="padding: 10px;">
<h3 style="padding: 3px 3px 20px 28px; background-image: url(/001_06.png); background-position: top left; background-repeat: no-repeat; font-size: 1.2em; border-bottom: solid 1px #5bace5;">アイコンの選択</h3>
</div>
<div style="clear: both; text-align: center; padding: 8px;">
${divs}
</div>
<br clear="all">
</form>
</div>
<br>
__MAP_FORM__;

show_dialog($contents);

?>
