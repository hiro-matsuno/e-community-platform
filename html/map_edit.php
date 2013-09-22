<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mapedit($id = null, $type = null) {
	global $COMUNI_FOOT_JSRAW;

	$f = mysql_full("select * from map_data where id = %s order by vernum",
					mysql_num($id));

	if ($f) {
		$points = array();
		while ($d = mysql_fetch_array($f, MYSQL_ASSOC)) {
			$cur_type = $d['type'];
			if ($cur_type == 'point') {
				$cur_cmd = 'placeMarker(true);';
				$cur_marker = 'new GLatLng('. $d['lat']. ', '. $d['lon']. ');';
				$i = mysql_uniq('select * from icons where id = %s', mysql_num($d['icon']));
				if ($i) {
					$set_icon = "setIcon('". $i['id']. "', '". $i['path']. "', '". $i['size_x']. "', '". $i['size_y'].
								"', '". $i['xunit']. "', '". $i['yunit']. "');";
				}
			}
			else {
				$cur_marker = 'null';
				if ($cur_type == 'line') {
					$cur_cmd = 'startLine(points);';
					$points[] = array('lat' => $d['lat'], 'lon' => $d['lon']);
				}
				else {
					$cur_cmd = 'startShape(points);';
					$points[] = array('lat' => $d['lat'], 'lon' => $d['lon']);
				}
				$i++;
			}
		}
		if (count($points) > 0) {
			if ($cur_type == 'polygon') {
				$points[] = $points[0];
			}
			$i = 0;
			foreach ($points as $point) {
				$cur_points .= "\t\t". 'points['. $i. '] = new GLatLng('.
								$point['lat']. ', '. $point['lon']. ');'. "\n";
				$i++;
			}
		}
//		$COMUNI_FOOT_JSRAW[] = 'mapedit_finish();';
	}
	else {
		$expoint = '';
		$cur_marker = 'null';
	}

	$style = <<<__STYLE__
<style type="text/css">
<!--
	#mapedit > div {
		float: left;
	}
	#maptool > a {
		cursor: pointer;
		border: solid 1px #aaaaaa;
		padding: 3px;
		float: left;
		width: 120px;
		text-align: center;
		margin-right: 3px;
	}
	#hand_b {
		width: 29px;
		height: 29px;
		background-image: url(/map/image/hand.png);
	}
	#hand_b.selected {
		background-image: url(/map/image/hand_c.png);
	}
	#placemark_b {
		width: 29px;
		height: 29px;
		background-image: url(/map/image/point.png);
	}
	#placemark_b.selected {
		background-image: url(/map/image/point_c.png);
	}
	#line_b {
		width: 29px;
		height: 29px;
		background-image: url(/map/image/line.png);
	}
	#line_b.selected {
		background-image: url(/map/image/line_c.png);
	}

	#shape_b {
		width: 29px;
		height: 29px;
		background-image: url(/map/image/poly.png);
	}
	#shape_b.selected {
		background-image: url(/map/image/poly_c.png);
	}
	#delete_b {
		width: 29px;
		height: 29px;
		background-image: url(/map/image/del.png);
	}
-->
</style>
__STYLE__;

	$script = <<<__SCRIPT__
<script type="text/javascript">
	var COLORS = [["red", "#ff0000"]];

	var options = {};
	var lineCounter_ = 0;
	var shapeCounter_ = 0;
	var markerCounter_ = 0;
	var colorIndex_ = 0;
	var featureTable_;
	var currentPolygon = null;
	var currentMarker = null;
	var currentIcon   = null;

	var listener = null;
	var map_type;

	var points = [];

${cur_points}

//${cur_cmd}

	function select(buttonId) {
		document.getElementById("hand_b").className      = "unselected";
		document.getElementById("shape_b").className     = "unselected";
		document.getElementById("line_b").className      = "unselected";
		document.getElementById("placemark_b").className = "unselected";
		document.getElementById(buttonId).className      = "selected";
	}

	function stopEditing() {
		removeDrawObj();
		select("hand_b");
	}

	function getColor(named) {
		return COLORS[(colorIndex_++) % COLORS.length][named ? 0 : 1];
	}

	function getIcon(color) {
		if (currentIcon) {
			return currentIcon;
		}

		var icon = new GIcon();
		icon.image = "http://google.com/mapfiles/ms/micons/" + color + ".png";
		icon.iconSize = new GSize(32, 32);
		icon.iconAnchor = new GPoint(15, 32);
		return icon;
	}

	function setIcon(id, path, size_x, size_y, xunit, yunit) {
		var icon = new GIcon();
		icon.image = path;
		icon.iconSize = new GSize(size_x, size_y);
	//	icon.iconAnchor = new GPoint(Math.ceil(size_x * xunit), Math.ceil(size_y - size_y * yunit));
		icon.iconAnchor = new GPoint(Math.ceil(size_x * 0.5), Math.ceil(size_y - size_y * 0));

		document.getElementById('icon').value = id;

		currentIcon = icon;

	//	alert(id);

		if (currentMarker)
			updateIcon(icon);
	}

	function startShape(p) {
		removeDrawObj();
		map_type = 'polygon';

		select("shape_b");
		var color = getColor(false);
		var polygon;
		if (p) {
			polygon = new GPolygon(p, color, 2, 0.7, color, 0.2);
			startEditing(polygon, "Shape " + (++shapeCounter_), function() {
				var area = polygon.getArea();
				mapedit_finish();
			}, color);
		}
		else {
			polygon = new GPolygon([], color, 2, 0.7, color, 0.2);
			startDrawing(polygon, "Shape " + (++shapeCounter_), function() {
				var area = polygon.getArea();
//				mapedit_finish();
			}, color);
		}
		currentPolygon = polygon;
	}

	function startLine(p) {
		removeDrawObj();
		map_type = 'line';

		select("line_b");
		var color = getColor(false);
		var line;
		if (p) {
			line = new GPolyline(p, color);
			startEditing(line, "Line " + (++lineCounter_), function() {
				var len = line.getLength();
				mapedit_finish();
			}, color);
		}
		else {
			line = new GPolyline([], color);
			startDrawing(line, "Line " + (++lineCounter_), function() {
				var len = line.getLength();
//				mapedit_finish();
			}, color);
		}
		currentPolygon = line;
	}

	function removeDrawObj() {
		map_${id}.clearOverlays();
		if (listener) {
			GEvent.removeListener(listener);
			listner = null;
		}
		if (currentPolygon) {
			currentPolygon.disableEditing();
		}
		if (currentMarker) {
			currentMarker = null;
		}
	}

	function updateValue() {
		return 1;
	}

	function startDrawing(poly, name, onUpdate, color) {
		map_${id}.addOverlay(poly);
		poly.enableDrawing(options);
		poly.enableEditing({onEvent: "mouseover"});
		poly.disableEditing({onEvent: "mouseout"});
		GEvent.addListener(poly, "endline", function() {
			select("hand_b");
			upcheck = updateValue();
			GEvent.bind(poly, "lineupdated", null, onUpdate);
			GEvent.addListener(poly, "click", function(latlng, index) {
				if (typeof index == "number") {
					poly.deleteVertex(index);
				} else {
					var newColor = getColor(false);
					poly.setStrokeStyle({color: newColor, weight: 4});
				}
			});
			mapedit_finish();
		});
	}

	function startEditing(poly, name, onUpdate, color) {
		bounds = poly.getBounds();
		map_${id}.addOverlay(poly);
//		map_${id}.setCenter(bounds.getCenter(), map_${id}.getBoundsZoomLevel(bounds));

		poly.enableEditing({onEvent: "mouseover"});
		poly.disableEditing({onEvent: "mouseout"});
		GEvent.bind(poly, "lineupdated", null, onUpdate);
		GEvent.addListener(poly, "click", function(latlng, index) {
			if (typeof index == "number") {
				poly.deleteVertex(index);
			} else {
				var newColor = getColor(false);
				poly.setStrokeStyle({color: newColor, weight: 4});
			}
//			mapedit_finish();
		});
	}

	function placeMarker(p) {
		select("placemark_b");
		removeDrawObj();
		map_type = 'point';

		if (p) {
			var cur_latlng = ${cur_marker};
			if (cur_latlng) {
				select("hand_b");
				var color = getColor(true);
				var marker = new GMarker(cur_latlng, {icon: getIcon(color), draggable: true});
				map_${id}.addOverlay(marker);
				updateMarker(marker);
				GEvent.addListener(marker, "dragend", function() {
					updateMarker(marker);
				});
				GEvent.addListener(marker, "click", function() {
					updateMarker(marker);
				});
				currentMarker = marker;
			}
		}
		else {
			listener = GEvent.addListener(map_${id}, "click", function(overlay, latlng) {
				if (latlng) {
					select("hand_b");
					GEvent.removeListener(listener);
					var color = getColor(true);
					var marker = new GMarker(latlng, {icon: getIcon(color), draggable: true});
					map_${id}.addOverlay(marker);
					updateMarker(marker);
					GEvent.addListener(marker, "dragend", function() {
						updateMarker(marker);
					});
					GEvent.addListener(marker, "click", function() {
						updateMarker(marker);
					});
					currentMarker = marker;
				}
			});
		}
		currentPolygon = null;
	}

	function updateIcon(newIcon) {
		if (!currentMarker) return;
		updateMarker(currentMarker, newIcon);
	}

	function updateMarker(marker, newIcon) {
		if (newIcon) {
			var current_point = marker.getPoint();
			var new_marker = new GMarker(current_point, {icon: newIcon, draggable: true});
			map_${id}.removeOverlay(marker);
			map_${id}.addOverlay(new_marker);
			currentMarker = new_marker;
			mapedit_finish(new_marker);
			return new_marker;
		}
		currentMarker = marker;
		mapedit_finish(marker);
		return marker;
	}

	function mapedit_initialize() {
		select("hand_b");
	}

	function deleteValue() {
		removeDrawObj();
		document.getElementById('map_type').value = '';
		document.getElementById('lat').value = '';
		document.getElementById('lon').value = '';
		document.getElementById('zoom').value = '';
		select("hand_b");
	}

	function mapedit_finish() {
		if (map_type == 'polygon') {
			document.getElementById('map_type').value = 'polygon';
				document.getElementById('lat').value = '';
			document.getElementById('lon').value = '';

			for (var i=0; i < currentPolygon.getVertexCount(); i++) {
				if (i > 0) {
					document.getElementById('lat').value += ',';
					document.getElementById('lon').value += ',';
				}
				document.getElementById('lat').value += currentPolygon.getVertex(i).lat();
				document.getElementById('lon').value += currentPolygon.getVertex(i).lng();
			}
//			alert(document.getElementById('lat').value);
//			alert(document.getElementById('lon').value);
		}
		if (map_type == 'line') {
			document.getElementById('map_type').value = 'line';
			document.getElementById('lat').value = '';
			document.getElementById('lon').value = '';
			for (var i=0; i < currentPolygon.getVertexCount(); i++) {
				if (i > 0) {
					document.getElementById('lat').value += ',';
					document.getElementById('lon').value += ',';
				}
				document.getElementById('lat').value += currentPolygon.getVertex(i).lat();
				document.getElementById('lon').value += currentPolygon.getVertex(i).lng();
			}
//			alert(document.getElementById('lat').value);
//			alert(document.getElementById('lon').value);
		}
		if (map_type == 'point') {
			document.getElementById('map_type').value = 'point';
			p = currentMarker.getPoint();
			document.getElementById('lat').value = p.lat();
			document.getElementById('lon').value = p.lng();

		}
		document.getElementById('zoom').value = map_${id}.getZoom();
/*
		$('#mpd').text(document.getElementById('map_type').value + '/'
							 + document.getElementById('lat').value + '/'
							 + document.getElementById('lon').value + '/'
							 + document.getElementById('zoom').value);
*/
	}

jQuery(document).ready(function() {
	jQuery('#mapedit').css('display', 'none');
	jQuery('#open_map').css('display', 'block');
	jQuery('#close_map').css('display', 'none');
	jQuery('#delete_map').css('display', 'none');
	jQuery('#select_icon').css('display', 'none');

	jQuery('#delete_map').css('background-color', '#ffcccc');

	jQuery('#close_map').click(function() {
		jQuery('#map_${id}').css('display', 'none');
		jQuery('#mapedit').css('display', 'none');
		jQuery('#open_map').css('display', 'block');
		jQuery('#close_map').css('display', 'none');
		map_${id}.checkResize();
	});

	jQuery('#open_map').click(function() {
		jQuery('#map_${id}').css('display', 'block');
		jQuery('#map_${id}').css('width', '100%');
		jQuery('#map_${id}').css('height', '320px');

		jQuery('#maptool').css('margin-top', '3px');
		jQuery('#select_icon').css('display', 'block');
		jQuery('#open_map').css('display', 'none');
		jQuery('#close_map').css('display', 'block');

		jQuery('#map_${id}').css('display', 'block');
		jQuery('#mapedit').css('display', 'block');

		mapedit_initialize();

		${set_icon}
		${cur_cmd}

		var c = map_${id}.getCenter();

		map_${id}.checkResize();
		map_${id}.enableDragging();

		map_${id}.setCenter(c);

		map_${id}.enableGoogleBar();

		mapedit_finish();
	});
	jQuery('#delete_map').click(function() {
		jQuery('#lat').val('');
		jQuery('#lon').val('');
		jQuery('#zoom').val('');
		jQuery('#icon').val('');

		jQuery('#maptool').css('margin-top', '0');

//		jQuery('#set_map').css('display', 'none');
		jQuery('#open_map').css('display', 'block').text('地図を開く');
		jQuery('#delete_map').css('display', 'none');
//		jQuery('#expand_map').css('display', 'none');
//		jQuery('#shrink_map').css('display', 'none');
		jQuery('#select_icon').css('display', 'none');

		jQuery('#map_${id}').css('width', '240px');
		jQuery('#map_${id}').css('height', '240px');

		jQuery('#map_${id}').css('display', 'none');
		jQuery('#mapedit').css('display', 'none');

		map_${id}.clearOverlays();
	});

});
	function setinputmap(lat, lon, zoom) {
		map_${id}.setCenter(new GLatLng(lat, lon), zoom);
	}
</script>
__SCRIPT__;

	$tools = <<<__TOOLS__
<div id="mpd"></div>
<div id="mapedit">
<div id="hand_b" onclick="stopEditing()"></div>
<div id="placemark_b" onclick="placeMarker()"></div>
<div id="line_b" onclick="startLine()"></div>
<div id="shape_b" onclick="startShape()"></div>
<div id="delete_b" onclick="deleteValue()"></div>
</div>
<div style="clear: both;"></div>
__TOOLS__;

	return join("\n", array($style, $script, $tools));
}

?>
