<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

//var_dump($_SESSION);
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	case 'canvas':
		canvas_edit($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["type"]   = 'point';
	$SYS_FORM["cache"]["lat"]    = $_POST["lat"];
	$SYS_FORM["cache"]["lon"]    = $_POST["lon"];
	$SYS_FORM["cache"]["zoom"]   = $_POST["zoom"];
	$SYS_FORM["cache"]["icon"]   = 0;
	$SYS_FORM["cache"]["vernum"] = 0;

	$d = mysql_exec("delete from map_data where id = %s", mysql_num($eid));
	$q = mysql_exec("insert into map_data".
					" (id, pid, type, lat, lon, zoom, icon, initymd, vernum)".
					" values (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num(0),
					mysql_str($SYS_FORM["cache"]["type"]),
					mysql_str($SYS_FORM["cache"]["lat"]),
					mysql_str($SYS_FORM["cache"]["lon"]),
					mysql_str($SYS_FORM["cache"]["zoom"]),
					mysql_num($SYS_FORM["cache"]["icon"]),
					mysql_current_timestamp(),
					mysql_num($SYS_FORM["cache"]["vernum"]));
	if (!$q) {
		show_error('中心地点の設定に失敗しました。');
	}

	// map_baseへ登録
	$d = mysql_exec("update map_setting set home_point = %s where id = %s",
					mysql_num($eid), mysql_num($eid));

	$html = '登録を完了しました。';
	$data = array(title   => 'マップ中心点の登録',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_ONLOAD, $COMUNI_HEAD_JS;

	use_map();

	$COMUNI_ONLOAD[]  = 'load_'. $eid. '()';
	$COMUNI_HEAD_JS[] = '/map_script.php?s=1&id='. $eid;

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'lat', value => '');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	$attr = array(name => 'lon', value => 'lon');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	$attr = array(name => 'zoom', value => 'zoom');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$div  = '<div id="map_c_'.$eid.'" tabindex=30000 onfocus="map_'.$eid.'.enableScrollWheelZoom();" onblur="map_'.$eid.'.disableScrollWheelZoom();">';
	$div .= '<div id="map_'. $eid. '" style="width: 100%; height: 350px;"></div>';
	$div .= '</div>';
	$SYS_FORM["input"][] = array(title => '中心地点の選択',
								 name  => 'map_div',
								 body  => $div);

	$JQUERY['ready'][] = <<<_JQ_
$('#submit_0').click(function() {
	var latlng = map_${eid}.getCenter();
	$('#lat').val(latlng.y);
	$('#lon').val(latlng.x);
	$('#zoom').val(map_${eid}.getZoom());
});
_JQ_;
	;

	$SYS_FORM["action"] = 'center.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'マップの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}


?>
