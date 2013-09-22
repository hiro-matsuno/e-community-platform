<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/*
 * Google Maps JS生成
 */
	require dirname(__FILE__). '/lib.php';

	$map_id = intval($_REQUEST["id"]);
	$scope  = intval($_REQUEST["s"]);
	$ctrler = $_REQUEST["c"];
	$mode   = $_REQUEST["m"];
	$oview  = $_REQUEST["o"];

	header("Content-Type: text/javascript");

	$i = mysql_uniq("select * from map_setting where id = %s", mysql_num($map_id));

	if (!$i) {
//		echo "\t". 'function load_'. $map_id. '() { $(\'#map_'. $map_id. '\').text(\'';
//		echo '始めにマップ初期設定を行ってください。\'); }';
//		exit(0);
		set_default_map_setting($map_id);
	}

	if (!$i['home_point']) {
		$home["lat"]  = CONF_ASSNS_Y;
		$home["lon"]  = CONF_ASSNS_X;
		$home["zoom"] = CONF_ASSNS_Z;
	}
	else {
		$m = mysql_uniq("select * from map_data where id = %s",
						mysql_num($i['home_point']));

		if ($m) {
			$home["lat"]  = $m['lat'];
			$home["lon"]  = $m['lon'];
			$home["zoom"] = $m['zoom'];
		}
		else {
			$home["lat"]  = CONF_ASSNS_Y;
			$home["lon"]  = CONF_ASSNS_X;
			$home["zoom"] = CONF_ASSNS_Z;
		}
	}

	switch ($mode) {
		case 'b':
			$map_base   = load_map_base($map_id);
			break;
		case 's':
			$map_base   = load_map_base($map_id);
			$map_kml    = load_map_kml_byid($map_id, intval($_REQUEST["k"]));
			if ($_REQUEST["k"]) {
				$map_kml_f  = 'loadKMLLayers_'. $map_id. '(map_'. $map_id. ', '. $map_id. ');';
			}
			break;
		default:
			$map_base   = load_map_base($map_id);
			$map_layer  = load_map_layer($map_id);
			$map_kml    = load_map_kml($map_id);
			if ($_REQUEST["k"]) {
				$map_kml_f  = 'loadKMLLayers_'. $map_id. '(map_'. $map_id. ', '. $map_id. ');';
			}
			else if ($map_kml != '') {
				$map_kml_f  = 'loadKMLLayers_'. $map_id. '(map_'. $map_id. ', '. $map_id. ');';
			}
	}

	switch ($ctrler) {
		case 's':
			$map_ctrl = 'GSmallMapControl';
			break;
		case 'z':
			$map_ctrl = 'GSmallZoomControl';
			break;
		default:
			$map_ctrl = 'GLargeMapControl';
	}
?>
// Google Maps JavaScript
	var map_<? echo $map_id ?>;
	var gxml_<? echo $map_id ?>;
	var gml_<? echo $map_id ?>;
	var kmls_<? echo $map_id ?> = new Array();

	var use_cscope_<? echo $map_id ?> = <? echo $scope ?>;
	var reticule_<? echo $map_id ?>   = null;

	<? echo $map_layer[0] ?>

	<? echo $map_kml[0] ?>

	function load_<? echo $map_id ?>() {
		if (GBrowserIsCompatible()) {
			<? echo $map_base ?>

			map_<? echo $map_id ?> = new GMap2(document.getElementById("map_<? echo $map_id ?>"), { mapTypes : myMapTypes } );
			map_<? echo $map_id ?>.setCenter(new GLatLng(<? echo $home["lat"] ?>, <? echo $home["lon"] ?>), <? echo $home["zoom"] ?>);

			map_<? echo $map_id ?>.addControl(new <? echo $map_ctrl ?>());
			map_<? echo $map_id ?>.addControl(new GMenuMapTypeControl());
			<? if ($oview) echo 'map_'. $map_id. '.addControl(new GOverviewMapControl());'; ?>

			<? echo $map_layer[1] ?>

			if (use_cscope_<? echo $map_id ?> == 1) {
				drawCrossScope(map_<? echo $map_id ?>, reticule_<? echo $map_id ?>);
				drawCrossScopeBind(map_<? echo $map_id ?>);
			}

			<? echo $map_kml[1] ?>
			<? echo $map_kml_f ?>

			map_<? echo $map_id ?>.enableGoogleBar();
			GEvent.addListener(map_<? echo $map_id ?>, "click", function (){
				document.getElementById("map_c_<? echo $map_id ?>").focus();
			});
		}
	}

	function loadKMLLayer_<? echo $map_id ?> (mapObj, url, gxmlObjId) {
		var proxy = '/modules/map/proxy.php?url=';
//		var proxy = '/modules/map/kpxy.cgi?url=';
		var url = proxy + escape(url);

		kmls_<? echo $map_id ?>.push(url);
	}
	function loadKMLLayers_<? echo $map_id ?>(mapObj, map_id) {
		sidebar_id = 'the_side_bar' + map_id;
		mbox_id    = 'messagearea' + map_id;
	    gml_<? echo $map_id ?> = new GeoXml("gml_<? echo $map_id ?>", mapObj, kmls_<? echo $map_id ?>, {
			iwwidth: 250,
//			descstyle: 'style = "font-family: arial, sans-serif;font-size: small;padding-bottom:1.7em;',
			sidebarid:sidebar_id,
			allfoldersopen:true,
			nozoom:false,
			messagebox:document.getElementById(mbox_id)});

	    gml_<? echo $map_id ?>.parse();
	}


	function tohome_<? echo $map_id ?>() {
		map_<? echo $map_id ?>.setCenter(new GLatLng(<? echo $home["lat"] ?>, <? echo $home["lon"] ?>), <? echo $home["zoom"] ?>);
	}

// EOF
