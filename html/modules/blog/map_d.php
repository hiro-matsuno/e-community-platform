<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

session_start();

$uid = $COMUNI["uid"];

$_SESSION["return"] = '/user.php?uid='. $uid;
$_SESSION["toppage"] = '/user.php?uid='. $uid;

$COMUNI["use_map"] = true;

$COMUNI_HEAD_CSS[] = '/ui.tabs.css';

$contents = <<<__MAP_FORM__
<div style="padding: 10px;">
<h3 style="padding: 3px 3px 20px 28px; background-image: url(/001_06.png); background-position: top left; background-repeat: no-repeat; font-size: 1.2em; border-bottom: solid 1px #5bace5;">位置の選択</h3>
</div>

<script type="text/javascript">
	function submi_m() {
		var center = self.map.getCenter();
		var zoom = self.map.getZoom();
		$('#lon').val(center.x);
		$('#lat').val(center.y);
		$('#zoom').val(zoom);
	}
</script>
<div style="clear: both; text-align: center; padding: 8px;">
<div id="map" style="width: 100%; height: 380px;"></div>
<br>
<form action="map_input.php" id="map_input" onSubmit="return submi_m();">
<input type="hidden" name="lat" id="lat" value="">
<input type="hidden" name="lon" id="lon" value="">
<input type="hidden" name="zoom" id="zoom" value="">
<input type="submit" value="アイコンの選択へ進む">
</form>
</div>
<br>
__MAP_FORM__;

show_dialog($contents);

?>
