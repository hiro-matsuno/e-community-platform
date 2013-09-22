<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

$icons = <<<__ICONS__
http://maps.google.co.jp/mapfiles/ms/icons/blue-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/red-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/green-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/ltblue-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/yellow-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/purple-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/pink-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/orange-dot.png
http://maps.google.co.jp/mapfiles/ms/icons/blue.png
http://maps.google.co.jp/mapfiles/ms/icons/red.png
http://maps.google.co.jp/mapfiles/ms/icons/green.png
http://maps.google.co.jp/mapfiles/ms/icons/lightblue.png
http://maps.google.co.jp/mapfiles/ms/icons/yellow.png
http://maps.google.co.jp/mapfiles/ms/icons/purple.png
http://maps.google.co.jp/mapfiles/ms/icons/pink.png
http://maps.google.co.jp/mapfiles/ms/icons/orange.png
http://maps.google.co.jp/mapfiles/ms/icons/blue-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/red-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/grn-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/ltblu-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/ylw-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/purple-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/pink-pushpin.png
http://maps.google.co.jp/mapfiles/ms/icons/restaurant.png
http://maps.google.co.jp/mapfiles/ms/icons/coffeehouse.png
http://maps.google.co.jp/mapfiles/ms/icons/bar.png
http://maps.google.co.jp/mapfiles/ms/icons/snack_bar.png
http://maps.google.co.jp/mapfiles/ms/icons/drinking_water.png
http://maps.google.co.jp/mapfiles/ms/icons/lodging.png
http://maps.google.co.jp/mapfiles/ms/icons/wheel_chair_accessible.png
http://maps.google.co.jp/mapfiles/ms/icons/shopping.png
http://maps.google.co.jp/mapfiles/ms/icons/movies.png
http://maps.google.co.jp/mapfiles/ms/icons/grocerystore.png
http://maps.google.co.jp/mapfiles/ms/icons/convienancestore.png
http://maps.google.co.jp/mapfiles/ms/icons/arts.png
http://maps.google.co.jp/mapfiles/ms/icons/homegardenbusiness.png
http://maps.google.co.jp/mapfiles/ms/icons/electronics.png
http://maps.google.co.jp/mapfiles/ms/icons/mechanic.png
http://maps.google.co.jp/mapfiles/ms/icons/pharmacy-us.png
http://maps.google.co.jp/mapfiles/ms/icons/realestate.png
http://maps.google.co.jp/mapfiles/ms/icons/salon.png
http://maps.google.co.jp/mapfiles/ms/icons/dollar.png
http://maps.google.co.jp/mapfiles/ms/icons/parkinglot.png
http://maps.google.co.jp/mapfiles/ms/icons/gas.png
http://maps.google.co.jp/mapfiles/ms/icons/cabs.png
http://maps.google.co.jp/mapfiles/ms/icons/bus.png
http://maps.google.co.jp/mapfiles/ms/icons/truck.png
http://maps.google.co.jp/mapfiles/ms/icons/rail.png
http://maps.google.co.jp/mapfiles/ms/icons/plane.png
http://maps.google.co.jp/mapfiles/ms/icons/ferry.png
http://maps.google.co.jp/mapfiles/ms/icons/helicopter.png
http://maps.google.co.jp/mapfiles/ms/icons/question.png
http://maps.google.co.jp/mapfiles/ms/icons/info.png
http://maps.google.co.jp/mapfiles/ms/icons/flag.png
http://maps.google.co.jp/mapfiles/ms/icons/earthquake.png
http://maps.google.co.jp/mapfiles/ms/icons/webcam.png
http://maps.google.co.jp/mapfiles/ms/icons/postoffice-us.png
http://maps.google.co.jp/mapfiles/ms/icons/police.png
http://maps.google.co.jp/mapfiles/ms/icons/firedept.png
http://maps.google.co.jp/mapfiles/ms/icons/hospitals.png
http://maps.google.co.jp/mapfiles/ms/icons/landmarks-jp.png
http://maps.google.co.jp/mapfiles/ms/icons/phone.png
http://maps.google.co.jp/mapfiles/ms/icons/caution.png
http://maps.google.co.jp/mapfiles/ms/icons/postoffice-jp.png
http://maps.google.co.jp/mapfiles/ms/icons/hotsprings.png
http://maps.google.co.jp/mapfiles/ms/icons/tree.png
http://maps.google.co.jp/mapfiles/ms/icons/campfire.png
http://maps.google.co.jp/mapfiles/ms/icons/picnic.png
http://maps.google.co.jp/mapfiles/ms/icons/campground.png
http://maps.google.co.jp/mapfiles/ms/icons/rangerstation.png
http://maps.google.co.jp/mapfiles/ms/icons/toilets.png
http://maps.google.co.jp/mapfiles/ms/icons/POI.png
http://maps.google.co.jp/mapfiles/ms/icons/hiker.png
http://maps.google.co.jp/mapfiles/ms/icons/cycling.png
http://maps.google.co.jp/mapfiles/ms/icons/motorcycling.png
http://maps.google.co.jp/mapfiles/ms/icons/horsebackriding.png
http://maps.google.co.jp/mapfiles/ms/icons/sportvenue.png
http://maps.google.co.jp/mapfiles/ms/icons/golfer.png
http://maps.google.co.jp/mapfiles/ms/icons/trail.png
http://maps.google.co.jp/mapfiles/ms/icons/water.png
http://maps.google.co.jp/mapfiles/ms/icons/snowflake_simple.png
http://maps.google.co.jp/mapfiles/ms/icons/marina.png
http://maps.google.co.jp/mapfiles/ms/icons/fishing.png
http://maps.google.co.jp/mapfiles/ms/icons/sailing.png
http://maps.google.co.jp/mapfiles/ms/icons/swimming.png
http://maps.google.co.jp/mapfiles/ms/icons/waterfalls.png
__ICONS__;

$icon_array = split("\r\n", $icons);

$num = 1;
foreach ($icon_array as $ic) {
	$name   = str_replace("http://maps.google.co.jp/mapfiles/ms/icons/", '', $ic);
	$summary = 'Google Mapsアイコン'. $num;
	$path   = $ic;
	$size_x = 32;
	$size_y = 32;
	$xunit  = 0.5;
	$yunit  = 1;

	$new_id = get_seqid();

	$f =  mysql_exec("insert into icons (id, name, summary, path, size_x, size_y, xunit, yunit)".
					" values(%s, %s, %s, %s, %s, %s, %s, %s);",
					mysql_num($new_id), mysql_str($name), mysql_str($summary),
					mysql_str($path), mysql_num($size_x), mysql_num($size_y), 
					mysql_num($xunit), mysql_num($yunit));

	set_pmt(array(eid => $new_id,
				  uid => 0,
				  gid => 0,
				  pmt => 0));

	$num++;
}

echo 'end';

?>
