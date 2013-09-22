<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

session_start();

$eid    = intval($_GET["eid"]);

$module = $_GET["module"];

$module = 'blog';

$d = mysql_full("select".
				" m.id, m.type, m.lat, m.lon, m.icon, b.subject, b.body".
				",i.path, i.size_x, i.size_y, i.xunit, i.yunit".
				" from map_data as m".
				" left join blog_data as b on m.id = b.id".
				" left join icons as i on m.icon = i.id".
				" where b.pid = %s or b.id = %s;",
				mysql_num($eid), mysql_num($eid));

if (!$d) {
	$d = mysql_exec("select".
				" m.id, m.type, m.lat, m.lon, m.icon, b.subject, b.body".
				",i.path, i.size_x, i.size_y, i.xunit, i.yunit".
				" from map_data as m".
				" left join schedule_data as b on m.id = b.id".
				" left join icons as i on m.icon = i.id".
				" where b.id = %s;",
				mysql_num($eid));
}

echo <<<__KML_HEADER__
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
  <Document>
    <name>e-community platform</name>
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

//		$icons[] = array(id => $m["id"], path => $m["path"]);
		if (!$info[$m["id"]]) {
			$info[$m[id]]  = array(id => $m["id"], type => $m["type"], icon => $m["icon"],
								   subject => $m["subject"], body => $m["body"]);
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
}

//		<hotSpot x="${s["xunit"]}"  y="${s["yunit"]}" xunits="fraction" yunits="fraction"/>
foreach ($style as $s) {
	if (!preg_match('/^http/', $s["path"])) {
		$url = 'http://com.tmm.jp/modules/map/icons/c/'. $s["path"];
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
			break;
		case 'polygon':
			if ($i["icon"] > 0) {
				$style_id = 'polygon_'. $i["icon"];
			}
			else {
				$style_id = 'polygon_default';
			}
			break;
		default:
			$style_id = 'point_default';
	}

	echo <<<__KML_DATA__
    <Placemark>
      <styleUrl>#${style_id}</styleUrl>
      <name>${i["subject"]}</name>
      <description>
        <![CDATA[
${i["body"]}
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
