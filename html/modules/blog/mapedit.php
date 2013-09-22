<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function m4pedit($id = null) {
	$style = <<<__STYLE__
<style type="text/css">
#mapedit > div {
	float: left;
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
</style>
__STYLE__;

	$script = <<<__SCRIPT__
<script type="text/javascript">
var COLORS = [["red", "#ff0000"]];
/*

var COLORS = [["red", "#ff0000"], ["orange", "#ff8800"], ["green","#008000"],
              ["blue", "#000080"], ["purple", "#800080"]];
*/

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

function startShape() {
	removeDrawObj();
	map_type = 'polygon';

	select("shape_b");
	var color = getColor(false);
	var polygon = new GPolygon([], color, 2, 0.7, color, 0.2);
	startDrawing(polygon, "Shape " + (++shapeCounter_), function() {
		var area = polygon.getArea();
		mapedit_finish();
	}, color);
	currentPolygon = polygon;
}

function startLine() {
	removeDrawObj();
	map_type = 'line';

	select("line_b");
	var color = getColor(false);
	var line = new GPolyline([], color);
	startDrawing(line, "Line " + (++lineCounter_), function() {
		var len = line.getLength();
		mapedit_finish(line);
	}, color);
	currentPolygon = line;
}

function removeDrawObj() {
	map.clearOverlays();
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
	map.addOverlay(poly);
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
	});
}

function placeMarker() {
	select("placemark_b");
	removeDrawObj();
	map_type = 'point';

	listener = GEvent.addListener(map, "click", function(overlay, latlng) {
		if (latlng) {
			select("hand_b");
			GEvent.removeListener(listener);
			var color = getColor(true);
			var marker = new GMarker(latlng, {icon: getIcon(color), draggable: true});
			map.addOverlay(marker);
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
		map.removeOverlay(marker);
		map.addOverlay(new_marker);
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
	}
	if (map_type == 'point') {
		document.getElementById('map_type').value = 'point';
		p = currentMarker.getPoint();
		document.getElementById('lat').value = p.lat();
		document.getElementById('lon').value = p.lng();

	}
	document.getElementById('zoom').value = map.getZoom();
/*
	$('#mpd').text(document.getElementById('map_type').value + '/'
						 + document.getElementById('lat').value + '/'
						 + document.getElementById('lon').value + '/'
						 + document.getElementById('zoom').value);
*/
}

$(document).ready(function() {
	$('#select_icon').css('display', 'none');
	$('#open_map').css('display', 'block');
//	$('#set_map').css('display', 'none');
	$('#delete_map').css('display', 'none');
	$('#mapedit').css('display', 'none');

	$('#expand_map').css('display', 'none');
	$('#shrink_map').css('display', 'none');
	$('#maptool > a').css('cursor', 'pointer');
	$('#maptool > a').css('border', 'solid 1px #aaaaaa');
	$('#maptool > a').css('padding', '3px');
	$('#maptool > a').css('float', 'left');
	$('#maptool > a').css('width', '120px');
	$('#maptool > a').css('text-align', 'center');
	$('#maptool > a').css('margin-right', '3px');

//	$('#set_map').css('background-color', '#ffffcc');
	$('#delete_map').css('background-color', '#ffcccc');

	$('#expand_map').click(function() {
		$('#expand_map').css('display', 'none');
		$('#shrink_map').css('display', 'block');
		$('#map_${pid}').css('width', '500px');
		$('#map_${pid}').css('height', '400px');
		var c = map_${pid}.getCenter();
		map_${pid}.checkResize();
		map_${pid}.setCenter(c);
	});
	$('#shrink_map').click(function() {
		$('#expand_map').css('display', 'block');
		$('#shrink_map').css('display', 'none');
		$('#map_${pid}').css('width', '240px');
		$('#map_${pid}').css('height', '240px');
		var c = map_${pid}.getCenter();
		map_${pid}.checkResize();
		map_${pid}.setCenter(c);
	});
	$('#open_map').click(function() {
		map_${pid}.enableDragging();

		$('#maptool').css('margin-top', '3px');

//		$('#set_map').css('display', 'block');
		$('#select_icon').css('display', 'block');
		$('#open_map').css('display', 'none');
		$('#expand_map').css('display', 'block');

		$('#map_${pid}').css('display', 'block');
		$('#mapedit').css('display', 'block');
		mapedit_initialize();

		var c = map_${pid}.getCenter();
		map_${pid}.checkResize();
		map_${pid}.setCenter(c);
	});
	$('#delete_map').click(function() {
		$('#lat').val('');
		$('#lon').val('');
		$('#zoom').val('');
		$('#icon').val('');

		$('#maptool').css('margin-top', '0');

//		$('#set_map').css('display', 'none');
		$('#open_map').css('display', 'block').text('ínê}ÇäJÇ≠');
		$('#delete_map').css('display', 'none');
		$('#expand_map').css('display', 'none');
		$('#shrink_map').css('display', 'none');
		$('#select_icon').css('display', 'none');

		$('#map_${pid}').css('width', '240px');
		$('#map_${pid}').css('height', '240px');

		$('#map_${pid}').css('display', 'none');
		$('#mapedit').css('display', 'none');

		map_${pid}.clearOverlays();
	});

});

	function setinputmap(lat, lon, zoom) {
		map_${pid}.setCenter(new GLatLng(lat, lon), zoom);
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
<div id="mpd"style="clear: both;"></div>
__TOOLS__;

	return join("\n", array($style, $script, $tools));
}

?>
