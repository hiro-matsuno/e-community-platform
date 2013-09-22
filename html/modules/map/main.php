<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';
include_once dirname(__FILE__). '/common.php';

function mod_map_main($id) {
	global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_ONLOAD;
	global $SYS_MAP_KML;

	if (!is_layoutmode()) {
		$q = mysql_uniq("select * from map_setting where id = %s",
						mysql_num($id));
		if ($q) {
			$header = $q['header'];
			$footer = $q['footer'];
		}

		$layer_div        = mod_map_layer_chkbox($id);
		$kml_div          = mod_map_kml_chkbox($id);

		if (is_array($SYS_MAP_KML) && count($SYS_MAP_KML) > 0) {
			$k = '&k='. join(',', $SYS_MAP_KML);
		}

		use_map();

		$COMUNI_ONLOAD[]  = 'load_'. $id. '()';
		$COMUNI_HEAD_JS[] = '/map_script.php?id='. $id. '&o=1'. $k;
	}

	$content  =<<<MAPDIV
<div id="map_c_$id" tabindex=30000 onfocus="map_$id.enableScrollWheelZoom();" onblur="map_$id.disableScrollWheelZoom();">
<div id="map_$id" style="width: 100%; height: 500px;"></div></div>
MAPDIV;
	if ($layer_div || $kml_div) {
		$cbox_div = '<div style="width: 100%;">'. $layer_div. $kml_div. '</div>';
	}
	$mbox = '<div id="the_side_bar'. $id. '"></div><div id="messagearea'. $id. '"></div>';

	return $header. $content. $cbox_div. $mbox. $footer;
}

?>
