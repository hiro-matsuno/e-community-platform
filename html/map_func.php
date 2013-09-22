<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/* 地点登録 */
function set_point($id = null,$blk_id = null) {
	if (!$id) { return; }

	$error = array();

	$type = $_REQUEST["map_type"];
	$lats = split(',', $_REQUEST["lat"]);
	$lons = split(',', $_REQUEST["lon"]);
	$zoom = $_REQUEST["zoom"];
	$icon = $_POST["icon"];

	// 前情報の消去
	$d = mysql_exec("delete from map_data where id = %s", mysql_num($id));

	$j = 0;
	for ($i = 0; $i < count($lats); $i++) {
//		echo $lats[$i]. '/'. $lons[$i]. '<br>';
		if ($lats[$i] && $lons[$i]) {
			$m = mysql_exec("insert into map_data(id, pid, type, lat, lon, zoom, icon, initymd, vernum)".
							" values(%s, %s, %s, %s, %s, %s, %s, %s, %s);",
							mysql_num($id),mysql_num($blk_id), mysql_str($type),
							mysql_str($lats[$i]), mysql_str($lons[$i] ),
							mysql_str($zoom), mysql_num($icon),
							mysql_current_timestamp(),
							mysql_num($j));
			if (!$m) {
				$error[] = mysql_error();
			}
			$j++;
		}
	}
	if (count($error) > 0) {
		show_error(join("\n", $error));
	}
}

function view_map($id = 0, $w = '100%', $h = '260px') {
	global $COMUNI, $COMUNI_ONLOAD, $COMUNI_HEAD_JS;

	use_map();

	$c = mysql_uniq("select * from map_data where id = %s limit 1", mysql_num($id));
	if (!$c) {
		return;
	}

	$pid = $c['pid'];
	
	$COMUNI_ONLOAD[]  = 'load_'. $pid. '()';
	$COMUNI_HEAD_JS[] = '/map_script.php?id='. $pid. '&m=s&c=s&k='. $id;

	$mbox = '<div id="the_side_bar'. $pid. '" class="mod_map_the_side_bar"></div><div id="messagearea'. $pid. '" class="mod_map_messagearea"></div>';

	return <<<RTN
<div id="map_c_$pid" tabindex=30000 onfocus="map_$pid.enableScrollWheelZoom();" onblur="map_$pid.disableScrollWheelZoom();">
<div id="map_$pid" style="display: block; width: $w; height: $h;"></div></div>$mbox
RTN;
}

/* 地点読込 */
function get_point($id = null) {
	if (!$id) { return; }

	$error = array();

	$result = array();

	$f = mysql_full("select * from map_data where id = %s order by vernum", mysql_num($id));
	if ($f) {
		while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
			if (!$result) {
				$result = array(id => $d['id'], type => $d['type'],
								zoom => $d['zoom'], icon => $d['icon']);
			}
			$result[lat][] = $d['lat'];
			$result[lon][] = $d['lon'];
		}
	}

	return $result;
}

function map_form($id = null, $type = null) {
	require_once dirname(__FILE__). '/map_edit.php';

	global $COMUNI, $COMUNI_ONLOAD, $COMUNI_HEAD_JS;

	$COMUNI['use_map'] = true;

	$COMUNI_ONLOAD[]  = 'load_'. $id. '()';
	$COMUNI_HEAD_JS[] = '/map_func.js';
	$COMUNI_HEAD_JS[] = '/map_script.php?id='. $id. '&m=b&c=s';

	$code = mapedit($id, $type);

	$d = mysql_full('select * from map_data where id = %s order by vernum',
					mysql_num($id));

	$lat = ''; $lon = ''; $icon = ''; $zoom = ''; $maptype = '';
	if ($d) {
		$lats = array(); $lons = array();
		while ($r = mysql_fetch_array($d)) {
			$maptype = $r['type'];
			$lats[] = $r['lat'];
			$lons[] = $r['lon'];
			$icon  = $r['icon'];
			$zoom  = $r['zoom'];
		}
		if ($maptype == 'point') {
			$lat = array_shift($lats);
			$lon = array_shift($lons);
		}
		else {
			$lat = implode(',', $lats);
			$lon = implode(',', $lons);
		}
	}

	$form = <<<__MAP_FORM__
<input type="hidden" id="map_type" name="map_type" value="${maptype}">
<input type="hidden" id="lat" name="lat" value="${lat}">
<input type="hidden" id="lon" name="lon" value="${lon}">
<input type="hidden" id="zoom" name="zoom" value="${zoom}">
<input type="hidden" id="icon" name="icon" value="${icon}">
${code}
<div id="map_c_$id" tabindex=30000 onfocus="map_$id.enableScrollWheelZoom();" onblur="map_$id.disableScrollWheelZoom();">
<div id="map_${id}" style="width: 100%; height: 320px; display: none;"></div></div>
<div id="maptool">
  <a id="open_map">地図を開く</a>
  <a id="close_map">地図を閉じる</a>
  <a id="delete_map">位置情報を消去</a>
  <a href="/set_icon.php?keepThis=true&TB_iframe=true&height=480&width=640" id="select_icon" class="thickbox">アイコン選択</a>
</div>
<div style="clear: both;"></div>
__MAP_FORM__;
	;

	return $form;
}

function load_map_base($id) {
	$data = mysql_full("select md.* from map_base_data as mbd ".
					   " inner join map_base as md".
					   " on mbd.map_id = md.id".
					   " where mbd.id = %s".
					   " order by mbd.vpos",
					   mysql_num($id));

	$script = array();
	if ($data) {
		if (mysql_num_rows($data) > 1) {
			$script[] = 'var myMapTypes = new Array();';
			while ($col = mysql_fetch_array($data)) {
				if ($col["map_type"] == 0) {
					$script[] = 'myMapTypes.push('. $col["base_url"]. ');';
					continue;
				}
				if (intval($col["use_geo"]) == 0) {
					$use_geo = 'false';
				}
				else {
					$use_geo = 'true';
				}
				if ($col["map_type"] == 1) {
					$load_func = 'loadTileLayer';
				}
				else {
					$load_func = 'loadWMSLayer';
				}
				$script[] = 'myMapTypes.push('. $load_func. '(\'base_'. $col["id"]. '\', \''.
							$col["base_url"]. '\','.
							' \''. $col["bbox_format"]. '\', '. $use_geo. ', \''. $col["cp_name"]. '\', \''.
							$col["cp_name_short"]. '\', \''. $col["cp_text"]. '\', '. $col["min_scale"].
							', '. $col["max_scale"]. ', '. $col["opacity"]. ', '. $col["ispng"]. '));';
			}
		}
		else {
			$col = mysql_fetch_array($data);

			if ($col["map_type"] == 1) {
				$load_func = 'loadTileLayer';
			}
			else {
				$load_func = 'loadWMSLayer';
			}
			$script[] = 'var myMapTypes = '. $load_func. '(\'base_'. $col["id"]. '\', \''.
						$col["base_url"]. '\', \''. $col["bbox_format"]. '\', '. $use_geo. ', \''.
						$col["cp_name"]. '\', \''. $col["cp_name_short"]. '\', \''. $col["cp_text"]. '\', '.
						$col["min_scale"]. ', '. $col["max_scale"]. ', '. $col["opacity"]. ', '. $col["ispng"]. ');';
		}
	}
	else {
		$script[] = 'var myMapTypes = new Array();';
		$script[] = 'myMapTypes.push(G_NORMAL_MAP);';
		$script[] = 'myMapTypes.push(G_SATELLITE_MAP);';
	}

	return join("\n\t\t\t", $script);
}

function load_map_layer($id) {
	$data = mysql_full("select ld.* from map_layer_data as mld ".
					   " inner join map_layer as ld".
					   " on mld.layer_id = ld.id".
					   " where mld.id = %s".
					   " order by mld.vpos",
					   mysql_num($id));

	$vars = array(); $script = array();
	if ($data) {
		if (mysql_num_rows($data) > 1) {
			$script[] = 'var myMapTypes = new Array();';
			while ($col = mysql_fetch_array($data)) {
				if (intval($col["use_geo"]) == 0) {
					$use_geo = 'false';
				}
				else {
					$use_geo = 'true';
				}
				if ($col["map_type"] == 1) {
					$load_func = 'loadTileLayerOverlay';
					$vars[] = 'var overlay_'. $col["id"]. ';';
				}
				else {
					$load_func = 'loadWMSLayerOverlay';
				}
				$script[] = 'overlay_'.$col["id"] . ' = '. $load_func. '(\'base_'. $col["id"]. '\', \''.
							$col["base_url"]. '\','.
							' \''. $col["bbox_format"]. '\', '. $use_geo. ', \''. $col["cp_name"]. '\', \''.
							$col["cp_name_short"]. '\', \''. $col["cp_text"]. '\', '. $col["min_scale"].
							', '. $col["max_scale"]. ', '. $col["opacity"]. ', '. $col["ispng"]. ');';
			}
		}
		else {
			$col = mysql_fetch_array($data);
			if (intval($col["use_geo"]) == 0) {
				$use_geo = 'false';
			}
			else {
				$use_geo = 'true';
			}
			if ($col["map_type"] == 1) {
				$load_func = 'loadTileLayerOverlay';
			}
			else {
				$load_func = 'loadWMSLayerOverlay';
			}

			$vars[] = 'var overlay_'. $col["id"]. ';';
			$script[] = 'overlay_'.$col["id"] . ' = '. $load_func. '(\'base_'. $col["id"]. '\', \''.
						$col["base_url"]. '\','.
						' \''. $col["bbox_format"]. '\', '. $use_geo. ', \''. $col["cp_name"]. '\', \''.
						$col["cp_name_short"]. '\', \''. $col["cp_text"]. '\', '. $col["min_scale"].
						', '. $col["max_scale"]. ', '. $col["opacity"]. ', '. $col["ispng"]. ');';
		}
	}
	else {
		return array();
	}

	$ret = array();
	$ret[] = join("\n\t", $vars);
	$ret[] = join("\n\t\t\t", $script);

	return $ret;
}

function load_map_kml_byid($id, $kml) {
	$base_href = CONF_URLBASE. '/modules/kml/get.php/'. rand_str(4, 'alpha');
//	$base_href = 'http://61.121.247.180/kml.cgi/'. $base_href;

	$vars[]   = 'var kml_'. $id. ';';
	$script[] = 'kml_'. $id. ' = loadKMLLayer_'. $id. '(map_'. $id. ', '. '\''. $base_href. $kml. '.kml\', '. $id. ');';

	$ret = array();
	$ret[] = join("\n\t", $vars);
	$ret[] = join("\n\t\t\t", $script);

	return $ret;
}

function load_map_kml($id) {
	global $SYS_MAP_KML;

	$vars = array(); $script = array();

	$k = mysql_full('select d.*, k.title, k.url from kml_url_data as d'.
					' inner join kml_url as k on d.kml_id = k.id'.
					' where d.id = %s', mysql_num($id));
	if ($k) {
		while ($r = mysql_fetch_array($k)) {
			$vars[] = 'var kml_'. $r['kml_id']. ';';
			$script[] = 'kml_'. $r['kml_id']. ' = '.
						'loadKMLLayer_'. $id. '(map_'. $id. ', '. '\''. $r['url']. '\', '. $id. ');';
		}
	}

	if ($_REQUEST['k']) {
		$SYS_MAP_KML = explode(',', $_REQUEST['k']);
		$base_href = CONF_URLBASE. '/modules/kml/get.php/'. rand_str(4, 'alpha');
//		$base_href = 'http://61.121.247.180/kml.cgi/'. $base_href;

		foreach ($SYS_MAP_KML as $kml) {
			$vars[] = 'var kml_'. $kml. ';';
			$script[] = 'kml_'. $kml. ' = loadKMLLayer_'. $id. '(map_'. $id. ', '. '\''. $base_href. $kml. '.kml\', '. $id. ');';
		}

		$ret = array();
		$ret[] = join("\n\t", $vars);
		$ret[] = join("\n\t\t\t", $script);

		return $ret;
	}

	$p = mysql_uniq("select * from block where id = %s", mysql_num($id));

	$pid = $p["pid"];

	$data = mysql_full("select * from block where module = 'blog' and pid = %s",
						mysql_num($pid));

	$base_href = CONF_URLBASE. '/modules/kml/get.php/'. rand_str(4, 'alpha');
//	$base_href = 'http://61.121.247.180/kml.cgi/'. $base_href;

//	$base_href = CONF_URLBASE. '/modules/kml/get.php/'. rand_str(4). '?id=';
//	$base_href = CONF_URLBASE. '/modules/kml/get.php/'. rand_str(4, 'alpha');
//	$base_href = 'http://61.121.247.180/kml.cgi/'. rand_str(12, 'alpha'). '_';

	if ($data) {
		if (mysql_num_rows($data) > 1) {
			while ($col = mysql_fetch_array($data)) {
				$vars[] = 'var kml_'. $col['id']. ';';
				$script[] = 'kml_'. $col['id']. ' = '.
							'loadKMLLayer_'. $id. '(map_'. $id. ', '. '\''. $base_href. $col['id']. '.kml\', '. $id. ');';
			}
		}
		else {
			$col = mysql_fetch_array($data);
				$vars[] = 'var kml_'. $col['id']. ';';
				$script[] = 'kml_'. $col['id']. ' = '.
							'loadKMLLayer_'. $id. '(map_'. $id. ', '. '\''. $base_href. $col['id']. '.kml\', '. $id. ');';
			}
	}

	if (count($vars) > 0) {
		$ret = array();
		$ret[] = join("\n\t", $vars);
		$ret[] = join("\n\t\t\t", $script);

		return $ret;
	}
	return;
}

function add_map_base($param) {
	$q = mysql_exec("insert into map_base (id, map_type, base_url, bbox_format, use_geo,".
					" cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity)".
					" values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
					mysql_num($param["id"]), mysql_num($param["map_type"]),
					mysql_str($param["base_url"]), mysql_str($param["bbox_format"]),
					mysql_bool($param["use_geo"]), mysql_str($param["cp_name"]),
					mysql_str($param["cp_name_short"]),	mysql_str($param["cp_text"]),
					mysql_num($param["mix_scale"]), mysql_num($param["max_scale"]),
					mysql_str($param["opacity"]));

	if (!$q) {
		die(mysql_error());
	}

	set_pmt(array(eid => $param["id"], uid => myuid(), unit => 0));
}

function add_map_layer($param) {
	$q = mysql_exec("insert into map_layer (id, map_type, base_url, bbox_format, use_geo,".
					" cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity)".
					" values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
					mysql_num($param["id"]), mysql_num($param["map_type"]),
					mysql_str($param["base_url"]), mysql_str($param["bbox_format"]),
					mysql_bool($param["use_geo"]), mysql_str($param["cp_name"]),
					mysql_str($param["cp_name_short"]),	mysql_str($param["cp_text"]),
					mysql_num($param["mix_scale"]), mysql_num($param["max_scale"]),
					mysql_str($param["opacity"]));

	if (!$q) {
		die(mysql_error());
	}

	set_pmt(array(eid => $param["id"], uid => myuid(), unit => 0));
}

function set_default_map_setting($id) {
	$m = mysql_uniq('select * from map_data'.
					' where id = %s',
					mysql_num(get_site_id($id)));

	$home_point = '';			
	if ($m) {
		$home_point = $m['id'];
	}

	$q = mysql_exec("insert into map_setting (id, home_point) values(%s, %s)",
					mysql_num($id), mysql_num($home_point));

	$default_map_base = array();

	$m = mysql_full('select * from map_base');
	if ($m) {
		while ($d = mysql_fetch_array($m)) {
			$default_map_base[] = $d['id'];
		}
	}

	for ($i = 0; $i < count($default_map_base); $i++) {
		$q = mysql_exec("insert into map_base_data (id, map_id, vpos) values(%s, %s, %s)",
					mysql_num($id), mysql_num($default_map_base[$i]), mysql_num($i));
	}
}

?>
