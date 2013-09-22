<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

$style = array();
$info  = array();
$data  = array();

$path_info = $_SERVER["REQUEST_URI"];
if(!$path_info)$path_info = $_SERVER["REQUEST_URI"];
if(!$path_info)$path_info = $_SERVER["ORIG_PATH_INFO"];

preg_match('/([0-9]+)/', $path_info, $match);

$eid    = intval($match[1]);

$module = $_GET["module"];

$module = 'blog';

$b = mysql_uniq("select * from block where id = %s", mysql_num($eid));

if ($b) {//$eidで示されているのはパーツである
	$module = $b['module'];
	$block = $eid;	
	switch($module) {
		case 'reporter':
		case 'bosai_web':
			$d = mysql_full("select".
							" m.id, m.type, m.lat, m.lon, m.icon, d.subject, d.body".
							",i.path, i.size_x, i.size_y, i.xunit, i.yunit".
							" from map_data as m".
							" left join icons as i on m.icon = i.id".
							" left join blog_data as d on m.id = d.id".
							" inner join element on d.id = element.id".
							" left join unit on element.unit = unit.id".
							" inner join ${module}_block as rb".
							" on rb.block_id = el.pid".
							" inner join ${module}_auth as ra".
							" on d.id = ra.id".
							" where (element.unit <= %s or unit.uid = %s)".
							" and rb.eid = %s".
							" and ra.display = 2".
//							" order by m.vernum",
							" order by d.initymd",
							mysql_num(public_status()), mysql_num(myuid()),
							mysql_num($eid));
			$module = 'blog';
			break;
		case 'schedule':
			$d = mysql_full("select".
							" m.id, m.type, m.lat, m.lon, m.icon, d.subject, d.body".
							",i.path, i.size_x, i.size_y, i.xunit, i.yunit".
							" from map_data as m".
							" inner join ${module}_data as d".
							" on m.id = d.id".
							" inner join element on d.id = element.id".
							" left join unit on element.unit = unit.id".
							" left join icons as i on m.icon = i.id".
							" where (element.unit <= %s or unit.uid = %s)".
							" and d.pid = %s".
							" and TO_DAYS(d.endymd) >= TO_DAYS(now())".
//							" order by m.vernum",
							" order by d.startymd",
							mysql_num(public_status()), mysql_num(myuid()), mysql_num($eid));
			break;
		default: 
			$d = mysql_full("select".
							" m.id, m.type, m.lat, m.lon, m.icon, d.subject, d.body".
							",i.path, i.size_x, i.size_y, i.xunit, i.yunit".
							" from map_data as m".
							" inner join ${module}_data as d".
							" on m.id = d.id".
							" inner join element on d.id = element.id".
							" left join unit on element.unit = unit.id".
							" left join icons as i on m.icon = i.id".
							" where (element.unit <= %s or unit.uid = %s)".
							" and d.pid = %s".
//							" order by m.vernum",
							" order by d.initymd",
							mysql_num(public_status()), mysql_num(myuid()), mysql_num($eid));
	}
	
}
if (!$d) {//$eidで示されているのはパーツでないか、パーツだが権限がない
	//$eidがblogの記事であると仮定してみる
	$d = mysql_full("select".
					" m.id, m.type, m.lat, m.lon, m.icon, b.subject, b.body".
					",i.path, i.size_x, i.size_y, i.xunit, i.yunit, b.pid as block".
					" from map_data as m".
					" left join blog_data as b on m.id = b.id".
					" inner join element on b.id = element.id".
					" left join unit on element.unit = unit.id".
					" left join icons as i on m.icon = i.id".
					" where (element.unit <= %s or unit.uid = %s)".
					" and b.id = %s order by m.vernum;",
					mysql_num(public_status()), mysql_num(myuid()), mysql_num($eid));
	$dd = mysql_uniq("select * from blog_data where id = %s",mysql_num($eid));
	$block = $dd['pid'];
}

if (!$d) {
	//$eidがscheduleの記事であると仮定してみる
	$d = mysql_full("select".
				" m.id, m.type, m.lat, m.lon, m.icon, b.subject, b.body".
				",i.path, i.size_x, i.size_y, i.xunit, i.yunit, b.pid as block".
				" from map_data as m".
				" left join schedule_data as b on m.id = b.id".
				" inner join element on b.id = element.id".
				" left join unit on element.unit = unit.id".
				" left join icons as i on m.icon = i.id".
				" where (element.unit <= %s or unit.uid = %s)".
				" and b.id = %s order by m.vernum;",
				mysql_num(public_status()), mysql_num(myuid()), mysql_num($eid));
	$module = 'schedule';
	$dd = mysql_uniq("select * from schedule_data where id = %s",mysql_num($eid));
	$block = $dd['pid'];
}

$bn = mysql_uniq("select name from block where id = %s", mysql_num($block));
$kml_subject = $bn['name']? $bn['name'] : 'ｅコミュニティ・プラットフォーム２．０';

header('Content-Type: application/xml');

echo <<<__KML_HEADER__
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
  <Document>
    <name>${kml_subject}</name>
    <description>sample</description>
    <Style id="point_default">
      <IconStyle>
        <Icon>
          <href>http://maps.google.com/mapfiles/kml/paddle/red-stars.png</href>
        </Icon>
		<hotSpot x="0.5"  y="0" xunits="fraction" yunits="fraction"/>
      </IconStyle>
    </Style>
    <Style id="line_default">
      <LineStyle>
        <width>4</width>
        <color>b20000ff</color>
      </LineStyle>
      <PolyStyle>
        <color>510000ff</color>
      </PolyStyle>
    </Style>
    <Style id="polygon_default">
      <LineStyle>
        <color>b20000ff</color>
        <width>2</width>
      </LineStyle>
      <PolyStyle>
		<fill>1</fill>
		<outline>1</outline>
        <color>510000ff</color>
      </PolyStyle>
    </Style>
__KML_HEADER__;

if ($d) {
	while ($m = mysql_fetch_array($d)) {
		if (!$m["lat"] || !$m["lon"]) {
			continue;
		}
		if (!$style[$m["icon"]] && $m["icon"] > 0) {
			$style[$m["icon"]] = array(id     => $m["icon"],
									   path   => $m["path"],
									   size_x => $m["size_x"],
									   size_y => $m["size_y"],
									   xunit => $m["xunit"],
									   yunit => $m["yunit"]);
		}

		$body = mb_strimwidth(strip_tags($m["body"]), 0, 100, '...', 'UTF-8');

//		$icons[] = array(id => $m["id"], path => $m["path"]);
		if (!$info[$m["id"]]) {
			$info[$m[id]]  = array(id => $m["id"], type => $m["type"], icon => $m["icon"],
								   subject => $m["subject"], body => $body);
		}
		$data[$m[id]][]  = array(lat => $m["lat"], lon => $m["lon"]);
	}
}
else {
	echo <<<__KML_FOOTER__
   </Document>
</kml>
__KML_FOOTER__;
	;
	exit(0);
}

//		<hotSpot x="${s["xunit"]}"  y="${s["yunit"]}" xunits="fraction" yunits="fraction"/>
foreach ($style as $s) {
	if (!preg_match('/^http/', $s["path"])) {
		$url = 'http://'.CONF_DOMAIN. $s["path"];
	}
	else {
		$url = $s["path"];
	}
	echo <<<__KML_ICON__
    <Style id="point_{$s["id"]}">
      <IconStyle>
        <Icon>
          <href>${url}</href>
        </Icon>
		<hotSpot x="${s["xunit"]}"  y="${s["yunit"]}" xunits="fraction" yunits="fraction"/>
      </IconStyle>
    </Style>
__KML_ICON__;
	;
}

foreach ($info as $i) {
	switch ($i["type"]) {
		case 'point':
			if ($i["icon"] > 0) {
				$style_id = 'point_'. $i["icon"];
			}
			else {
				$style_id = 'point_default';
			}
			break;
		case 'line':
			if ($i["icon"] > 0) {
				$style_id = 'line_'. $i["icon"];
			}
			else {
				$style_id = 'line_default';
			}
			$style_id = 'line_default';
			break;
		case 'polygon':
			if ($i["icon"] > 0) {
				$style_id = 'polygon_'. $i["icon"];
			}
			else {
				$style_id = 'polygon_default';
			}
			$style_id = 'polygon_default';
			break;
		default:
			$style_id = 'point_default';
	}

	$href = CONF_URLBASE. "/index.php?module=$module&eid=$i[id]&blk_id=$block";

	echo <<<__KML_DATA__
    <Placemark>
      <styleUrl>#${style_id}</styleUrl>
      <name>${i["subject"]}</name>
      <description>
        <![CDATA[
${i["body"]}
<div style="text-align: right; padding-bottom: 0.7em;"><a href="${href}" target="_top" class="MyLink" onClick="location.href = '${href}; return false;'">もっと読む &raquo;&raquo;</a></div>
        ]]>
      </description>
__KML_DATA__;
	;

	switch ($i["type"]) {
		case 'point':
			echo "      <Point>\n";
			break;
		case 'line':
			echo "      <LineString>\n";
			echo "          <extrude>1</extrude>\n";
			echo "          <tessellate>1</tessellate>\n";
			echo "          <altitudeMode>absolute</altitudeMode>\n";
			break;
		case 'polygon':
			echo "      <Polygon>\n";
			echo "        <outerBoundaryIs>\n";
			echo "          <LinearRing>\n";
			echo "            <extrude>1</extrude>\n";
			echo "            <tessellate>1</tessellate>\n";
//			echo "            <altitudeMode>absolute</altitudeMode>\n";
			break;
		default:
			echo "      <Point>\n";
	}

	echo "        <coordinates>";
	foreach ($data[$i["id"]] as $sub) {
		echo $sub["lon"]. ",". $sub["lat"]. ",10\n";
	}
	echo "</coordinates>\n";

	switch ($i["type"]) {
		case 'point':
			echo "      </Point>\n";
			break;
		case 'line':
			echo "      </LineString>\n";
			break;
		case 'polygon':
			echo "              </LinearRing>\n";
			echo "          </outerBoundaryIs>\n";
			echo "      </Polygon>\n";
			break;
		default:
			echo "      </Point>\n";
	}

	echo <<<__KML_DATA__
    </Placemark>
__KML_DATA__;
	;
}

echo <<<__KML_FOOTER__
   </Document>
</kml>
__KML_FOOTER__;

exit(0);

?>
