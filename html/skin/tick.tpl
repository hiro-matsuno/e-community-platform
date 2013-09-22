<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
{literal}
<script type="text/javascript">
		// initialise plugins
		jQuery(function(){
			jQuery('ul.sf-menu').superfish({
				delay: 0,
				speed: 'fast',
				dropShadows: false,
				disableHI: true,
				autoArrows: false
			});
		});
</script>
{/literal}
{if $gmap}
{literal}
<script type="text/javascript">
    //<![CDATA[

	var map;
	var geoSpace;
	var geoSpacePhoto;

/*
	var x_icon      = new GIcon();
	x_icon.image    = "http://com.tmm.jp/cross.png";
	x_icon.iconSize = new GSize(35, 35);
	x_icon.iconAnchor = new GPoint(17, 17);
	var x_icon_option = { icon : x_icon, clickable : false };
	var x_marker = null;
*/
	var reticule = null;

	function load()
	{
		if (GBrowserIsCompatible()) {
			map = new GMap2(document.getElementById("map"), {mapTypes : [G_NORMAL_MAP,G_SATELLITE_MAP]});
			map.setCenter(new GLatLng(36.081084, 140.113964), 15);

			map.addControl(new GSmallZoomControl());
			map.addControl(new GMenuMapTypeControl());

//			x_marker = new GMarker(map.getCenter(), x_icon);
//			map.addOverlay(x_marker); 

			drawCrossScope();

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
{/literal}
{section name=n loop=$kml}
			loadKMLLayer('{$kml[n]}');
{/section}
{literal}
			//map.addOverlay(geoSpacePhoto);
		}
	}
	function loadKMLLayer(url) {
		var gx = new GGeoXml(url, function() {
			if (gx.loadedCorrectly()) {
				var c = gx.getDefaultSpan();
				if (c.x == 360 && c.y == 180) {
					;
				}
				else {
					gx.gotoDefaultViewport(map);
				}
			}

		});
		map.addOverlay(gx);
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
//		var url = 'http://lsweb1.ess.bosai.go.jp/jisuberi/GoogleEarth/kiou01.kml';
		var url = 'http://com.tmm.jp/kiou01.kml';
		var gx = new GGeoXml(url);
		map.addOverlay(gx);
	}
	function delOverlays() {
		map.clearOverlays();
	}
	function drawCrossScope(){
/*
		if (reticule) map.remoVeOverlay(reticule);
		reticule = new GScreenOverlay('http://com.tmm.jp/cross.png', 
						new GScreenPoint(0.5, 0.5, 'fraction', 'fraction'),
						new GScreenPoint(17, 17),
						new GScreenSize(35, 35 )
					);
		map.addOverlay(reticule);
*/
	}
	function startSetMap() {
		map.enableDragging();
	}
	function endSetMap() {
		var c = map.getCenter();
		var z = map.getZoom();

		document.getElementById('lat').value = c.y;
		document.getElementById('lon').value = c.x;
		document.getElementById('zoom').value = z;

		map.disableDragging();
	}
	$(window).bind('resize', function() {
		drawCrossScope();
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

<div id="wrapper">

<div id="header">
  <a href="/"><img src="/skin/default/image/title.png" alt="地域防災キット" style="float: left; border: none;"></a>
  <img src="/skin/default/image/ecom_logo.png" alt="e-community platform" style="float: right; margin-right: 30px;">
  <br style="clear: both;">
</div><!-- /#header -->

<div id="menu">
  <div class="search_field">
    <form action="/search.php" method="GET">
      <input type="text" id="input_text" value="キーワード検索" onFocus="this.value='';"><input type="image" src="/skin/default/image/search.png" id="submit_button" value="検索">
    </form>
  </div><!-- /#search_field -->
  <ul class="sf-menu">
    <li><a href="/">ポータル</a></li>
    <li><a href="/group_list.php">グループ一覧</a>
      <ul>
        <li><a>テーマから探す</a></li>
        <li><a>キーワードから探す</a></li>
        <li><a>グループ一覧</a></li>
      </ul>
    </li>
    <li><a href="/user_list.php">ユーザー一覧</a>
      <ul>
        <li><a>キーワードから探す</a></li>
        <li><a>ユーザー一覧</a></li>
      </ul>
    </li>
{if $is_login}
    <li><a>マイメニュー</a>
{if $mymenu}
      <ul>
{section name=n loop=$mymenu}
{if $mymenu[n].jump}
        <li><a href="{$mymenu[n].url}">{$mymenu[n].title}</a></li>
{else}
        <li><a href="{$mymenu[n].url}&keepThis=true&TB_iframe=true&height=480&width=640" class="thickbox">{$mymenu[n].title}</a></li>
{/if}
{/section}
      </ul>
{/if}
    </li>
{/if}
    </li>

{if $is_owner}
    <li><a>ページ設定</a>
      <ul>
{section name=n loop=$sitemenu}
{if $sitemenu[n].jump}
        <li><a href="{$sitemenu[n].url}">{$sitemenu[n].title}</a></li>
{else}
        <li><a href="{$sitemenu[n].url}&keepThis=true&TB_iframe=true&height=300&width=500" class="thickbox">{$sitemenu[n].title}</a></li>
{/if}
{/section}
      </ul>
    </li>
{/if}

  </ul>
</div><!-- /#menu -->

<div id="nav">
  <div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} &gt; {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
{/section}
  </div>
  <div class="nav_mn">
    <ul>
{if $is_login}
{if $is_mypage}
      <li><a href="/user.php?uid={$uid}">マイページ</a></li>
{/if}
      <li><a href="/help.php">ヘルプ</a></li>
      <li><a href="/logout.php">ログアウト</a></li>
{else}
<!--      <li><a href="/ad.php">デモ用</a></li>-->
      <li><a href="/login.php">ログイン</a></li>
      <li><a href="/regist.php"><strong>新規登録</strong></a></li>
{/if}
    </ul>
  </div>
</div><!-- /#nav -->

{if $setting_layout}
<div id="post_status"></div>
<div id="nav_admin">
  <form id="layout_setting" action="/layout.php" style="margin: 0;">
  <input type="hidden" name="save" value="1">
  <input type="hidden" name="eid" value="{$eid}">
  <a href="/layout.php?nosave=1&eid={$eid}" title="">保存しないで終了</a>
  <a href="/layout.php?save=1&eid={$eid}" id="layout_save" title="" onClick="return false;">保存して終了</a>
  <a href="/add_block.php?eid={$eid}&keepThis=true&TB_iframe=true&height=480&width=640" title="" class="thickbox">ブロックを追加</a>
  </form>
</div><!-- /#nav_admin -->
{/if}

<div id="container">
{$contents}
　<div id="container_foot"></div>
</div><!-- /#container -->

<div id="footer_push"></div>
</div><!-- /#wrapper -->

<div id="footer">
  <div class="footer_content">Copyright &copy; {$smarty.server.SERVER_NAME} All Rights Reserved.</div>
</div><!-- /#footer -->

{section name=n loop=$after_js}
<script type="text/javascript" src="{$head_js[n]}">
{/section}

<div id="debug">
{section name=n loop=$debug}
{$debug[n]}<br>
{/section}
</div>
<script type="text/javascript" src="/url_breaker_plus.user.js"></script>
</body>
</html>
