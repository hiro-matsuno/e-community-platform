<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja-JP" lang="ja-JP">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
{section name=n loop=$head_meta}
<link rel="stylesheet" href="{$head_meta[n]}" type="text/css">
{/section}
<title>{$site_name}</title>
{section name=n loop=$head_css}
<link rel="stylesheet" href="{$head_css[n]}" type="text/css">
{/section}
{section name=n loop=$head_js}
<script type="text/javascript" src="{$head_js[n]}"></script>
{/section}

{if $gmap}
{literal}
<script type="text/javascript">
    //<![CDATA[

	var map;
	var geoSpace;
	var geoSpacePhoto;

	var x_icon      = new GIcon();
	x_icon.image    = "http://com.tmm.jp/cross.png";
	x_icon.iconSize = new GSize(35, 35);
	x_icon.iconAnchor = new GPoint(17, 17);
	var x_icon_option = { icon : x_icon, clickable : false };
	var x_marker = null;

	function load()
	{
		if (GBrowserIsCompatible()) {
			map = new GMap2(document.getElementById("map"), {mapTypes : [G_NORMAL_MAP]});
			map.setCenter(new GLatLng(36.081084, 140.113964), 15);

			map.addControl(new GLargeMapControl());
			map.addControl(new GMenuMapTypeControl());

			x_marker = new GMarker(map.getCenter(), x_icon);
			map.addOverlay(x_marker); 

			GEvent.addListener(map, "move", function() {
				drawCrossScope(map);
			});
			drawCrossScope(map);

			var bMap= new GTileLayer(new GCopyrightCollection(""),1,17);
			addWMSPropertiesToLayer(bMap,
				'http://www.geographynetwork.ne.jp/ogc/wms?ServiceName=basemap_wms&request=GetMap&SERVICE=WMS&SRS=EPSG:54004&LAYERS=0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17&FORMAT=image/png&reaspect=true',
				'', 
				'',
				'image/png',
				'',
				'',
				false);
			bMap.getTileUrl=getTileUrlForWMS;

			var bMapMapType = new GMapType([bMap],
    		new GMercatorProjection(23), "数値地図", { shortName: "数値"});
			map.addMapType(bMapMapType);

			var geoSpace = new GTileLayer(new GCopyrightCollection("ココにCopyright"), 1, 22);

			geoSpace.getTileUrl = function(tile, zoom) {
					return "http://del.service-section.com/geowebcache/service/gmaps?layers=GeoSpace&zoom=" + zoom +
							"&x=" + tile.x + "&y=" + tile.y;
			};

			var geoSpaceMapType = new GMapType([geoSpace],
    		new GMercatorProjection(23), "geoSpace", { shortName: "geo"});
			map.addMapType(geoSpaceMapType);

			var geoSpacePhoto = new GTileLayer(new GCopyrightCollection("ココにCopyright"), 1, 22);

			geoSpacePhoto.getTileUrl = function(tile, zoom) {
					return "http://del.service-section.com/geowebcache/service/gmaps?layers=GeoSpacePhotoGmap&zoom=" + zoom +
							"&x=" + tile.x + "&y=" + tile.y;
			};

			var geoSpacePhotoMapType = new GMapType([geoSpacePhoto],
    		new GMercatorProjection(23), "航空写真", { shortName: "geoP"});
			map.addMapType(geoSpacePhotoMapType);


			var orthoPhoto= new GTileLayer(new GCopyrightCollection(""),1,17);
			addWMSPropertiesToLayer(orthoPhoto,
				'http://orthophoto.mlit.go.jp:8888/wms/service/wmsRasterTileMap?VERSION=1.3.0&REQUEST=GetMap&LAYERS=ORTHO&STYLES=&CRS=EPSG:4612&FORMAT=image/png&BGCOLOR=OxFFFFFF&',
				'', 
				'',
				'image/png',
				'[south],[west],[north],[east]',
				'',
				true);
			orthoPhoto.getTileUrl=getTileUrlForWMS;

			var orthoPhotoMapType = new GMapType([orthoPhoto],
    		new GMercatorProjection(23), "orthoPhoto", { shortName: "ortho"});
			map.addMapType(orthoPhotoMapType);
/*
			geoSpace = new GTileLayerOverlay(
	  			new GTileLayer(null, null, null, {
					tileUrlTemplate: 'http://del.service-section.com/geowebcache/service/gmaps?layers=GeoSpace&zoom={Z}&x={X}&y={Y}',
					isPng:true,
					opacity:1.0 }
					)
			);
			map.addOverlay(geoSpace);
*/

			geoSpacePhoto = new GTileLayerOverlay(
	  			new GTileLayer(null, null, null, {
					tileUrlTemplate: 'http://del.service-section.com/geowebcache/service/gmaps?layers=GeoSpacePhotoGmap&zoom={Z}&x={X}&y={Y}',
					isPng:true,
					opacity:0.85 }
					)
			);

			//map.addOverlay(geoSpacePhoto);
		}
	}
	function showLayerOverlay(overlay, show)
	{
		if (show) {
			map.removeOverlay(overlay);
			map.addOverlay(overlay);
		}
		else {
			map.removeOverlay(overlay);
		}
	}
	function tohome() {
		map.setCenter(new GLatLng(36.081084, 140.113964), 15);
	}
	function loadSampleKML() {
		map.setCenter(new GLatLng(36.910372, 138.240967), 8);

//		var url = 'http://agora.ex.nii.ac.jp/digital-typhoon/kml/amedas/location/40336.ja.kml';
//		var url = 'http://agora.ex.nii.ac.jp/digital-typhoon/kml/active.ja.kml';
		var url = 'http://lsweb1.ess.bosai.go.jp/jisuberi/GoogleEarth/kiou01.kml';
		var gx = new GGeoXml(url);
		map.addOverlay(gx);
	}
	function delOverlays() {
		map.clearOverlays();
	}
	function drawCrossScope(map){
		var mapCenter = map.getCenter();
		x_marker.setPoint(mapCenter);
	}
	$(window).bind('resize', function() {
		drawCrossScope(map);
	});
</script>
{/literal}
{/if}

{if $jquery_ready_script}
{literal}
<script type="text/javascript">
    //<![CDATA[
	$(document).ready(function() {
{/literal}
{section name=n loop=$jquery_ready_script}
{$jquery_ready_script[n]}

{/section}
{literal}
	});
// ]]>
</script>
{/literal}
{/if}

</head>

{if $gmap}
<body onload="load()" onunload="GUnload()">
{else}
<body>
{/if}

{$contents}

</body>
</html>
