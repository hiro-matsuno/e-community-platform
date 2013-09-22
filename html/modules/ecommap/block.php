<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once('config.php');

/** ブロック
 * @param $blk_id ブロックの固有ID */
function _mod_ecommap_block($blk_id = 0, $emap2010=false)
{
	session_write_close();
	
	//ブロックの編集権限取得
	$element = new Element($blk_id);
	$level = $element->getOwnerLevel(get_myuid());
	$blk_editable  = $level >= Permission::USER_LEVEL_EDITOR;
	$blk_member = $level >= Permission::USER_LEVEL_AUTHORIZED;
	
	global $COMUNI_HEAD_CSS, $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW, $COMUNI_ONLOAD;
	
	//ヘッダへの出力設定
	include_once(dirname(__FILE__). '/head.php');
	
	include_once("classes/EcomMapDB.php");
	include_once("classes/EcomMapAuth.php");
	
	//設定読み込み
	$options = EcomMapDB::getOptions();
	$blockOptions = EcomMapDB::getBlockOptions($blk_id);
	
	//サーバ共通設定読み込み
	$ecommap_url = $options['ecommap_url'];
	if (empty($ecommap_url)) { return "管理設定の「eコミマップ連携設定」でeコミマップサーバのURLを設定してください"; }
	
	$ecommap_cid = $options['ecommap_cid'];
	//ブロック個別設定を反映
	$ecommap_gid = 0;
	$groupType = $options["ecommap_group"];
	switch ($groupType) {
	case MOD_ECOMMAP_GROUP_IN_SERVER:
		//パーツブロック側のコミュニティIDを設定 設定がなければ管理設定もコミュニティIDを利用
		if (!empty($blockOptions['ecommap_cid'])) $ecommap_cid = $blockOptions['ecommap_cid'];
		if (empty($ecommap_cid)) { return "パーツメニューの「連携設定」でeコミマップのコミュニティIDを設定してください"; }
		$ecommap_gid = $blockOptions['ecommap_gid'];
		break;
	case MOD_ECOMMAP_GROUP_SINGLE_COMMUNITY:
		if (empty($ecommap_cid)) { return "管理設定の「eコミマップ連携設定」でeコミマップのコミュニティIDを設定してください"; }
		break;
	case MOD_ECOMMAP_GROUP_IN_COMMUNITY:
		if (empty($ecommap_cid)) { return "管理設定の「eコミマップ連携設定」でeコミマップのコミュニティIDを設定してください"; }
		$ecommap_gid = $blockOptions['ecommap_gid'];
		break;
	}
	if (empty($ecommap_gid)) $ecommap_gid = 0;
	
	if (!$ecommap_url) {
		if (is_su()) return 'システム編集ページで、<a href="'.MOD_ECOMMAP_URL.'/setting.php">eコミマップの連携設定</a>をしてください';
		return;
	}
	
	//認証キー設定
	$uid = myuid();
	$auth_url = null;
	if ($uid) {
		$auth_key = EcomMapAuth::createAuthKey($uid);
		$auth_url = EcomMapAuth::getAuthUrlParam($uid, $auth_key, $blk_id);//Callback認証用URL
		
		//DBに登録
		EcomMapAuth::setAuthKey($uid, $auth_key);
		
		//延長用にセッションにも登録
		session_start();
		$_SESSION['ecommap_auth_key'] = $auth_key;
		$_SESSION['ecommap_auth_url'] = $auth_url;
		session_write_close();
		
		//9分後に再読み込み
		$COMUNI_HEAD_JSRAW[] = "setInterval(function(){ecommap.setAuthExpiry();}, 300000)";
	}
	
	//ユーザオプション
	$userOptions = EcomMapDB::getUserOptions($uid, $blk_id);
	//ユーザ初期表示位置 TODO Ajax化した場合は関数の引数に入れるように修正
	$lon = $userOptions['MAP_LON'];
	$lat = $userOptions['MAP_LAT'];
	$scale = $userOptions['MAP_SCALE'];
	$visibleRefLayerId = $userOptions['MAP_VISIBLEREF'];
	if (isset($userOptions['MAP_LON']) && isset($userOptions['MAP_LAT']) && -180 <= $lon && $lon <= 180 && -90 <= $lat && $lat <= 90)
		$COMUNI_HEAD_JSRAW[] = "ecommap.userLon=$lon; ecommap.userLat=$lat; ";
	if ($scale > 0) $COMUNI_HEAD_JSRAW[] = "ecommap.userScale=$scale;";
	if (!empty($visibleRefLayerId)) $COMUNI_HEAD_JSRAW[] = 'ecommap.userVisibleRefLayerId="'.$visibleRefLayerId.'";';
	
	
	//JavaScriptにeコミ連携用のグループページの情報を設定
	$COMUNI_HEAD_JSRAW[] = "ecommap.init('".$ecommap_url."',".$ecommap_cid.",".$ecommap_gid.",'".CONF_URLBASE.home_url($eid)."'".($auth_url ? ",'".$auth_url."'" : "").")";
	
	//表示オプション
	$map_type = $blockOptions['map_type']? $blockOptions['map_type'] : MOD_ECOMMAP_TYPE_LIST;
	$preview_pos = $blockOptions['preview_pos'] ? $blockOptions['preview_pos'] : "left";
	$preview_width = $blockOptions['preview_width'];
	
	//表示パラメータ
	$info_params = array(blk_id=>$blk_id, type=>$map_type);
	if ($blockOptions['map_num']) $info_params['num'] = $blockOptions['map_num'];
	if ($blockOptions['map_id']) $info_params['mid'] = $blockOptions['map_id'];
	if ($blockOptions['layer_num']) $info_params['lnum'] = $blockOptions['layer_num'];
	
	//パラメータと認証情報をURLに追加
	$ecommap_json_url .= $ecommap_url.MOD_ECOMMAP_BLOCK_JSP."?cid=".$ecommap_cid;
	$ecommap_json_url .= "&gid=".$ecommap_gid;
	$ecommap_json_url .= "&".http_build_query($info_params);
	//echo $ecommap_json_url;
	if ($uid) {
		$ecommap_json_url .= "&authid=".$uid; //初回の認証後eコミマップのセッションに登録されるID
		$ecommap_json_url .= "&auth=".$auth_url;//エンコード済みCallback認証URL
	}
	//地図情報をJSONで取得
	try {
		require_once dirname(__FILE__)."/../../PEAR/HTTP/Request.php";
		$option = array( 
			"timeout"=>10, // タイムアウトの秒数指定
			"allowRedirects"=>true, // リダイレクトの許可設定(true/false)
			"maxRedirects"=>3 // リダイレクトの最大回数
		);
		$http = new HTTP_Request($ecommap_json_url, $option);
		//送信
		$response = $http->sendRequest();
		if (!PEAR::isError($response) && $http->getResponseCode()<400) { 
			$map_json = $http->getResponseBody();
		} else {
			return "地図情報を取得できませんでした";//.$ecommap_url;
		}
	} catch (Exception $e) {}
	
	$mapInfoArray = json_decode($map_json);
	if (!is_array($mapInfoArray)) {
		return $map_json;
	}
	
	if (count($mapInfoArray) == 0) return "該当するマップはありません";
	
	
	//return $map_json;
	
	//ブロック出力
	ob_start();
?>
<div class="ecommap_block">
<?
	foreach ($mapInfoArray as $mapInfo) {
?>
<div class="maplist_div" id="maplist_div_<?=$mapInfo->id?>">
<div class="info_div">
<div class="map_title">
<? if ($mapInfo->editable) {?><div style="float:right;"><a href="#" class="editIcon" onclick="ecommap.mapEdit(<?=$mapInfo->id?>);return false;">名前と説明の変更</a></div><? } ?>
	<?=htmlspecialchars($mapInfo->title)?>
</div>
<div class="preview_div" style="float:<?=$preview_pos?>;<?if ("left" == $preview_pos) {echo("margin-right:4px;");}?>"
	><a href="#" onclick="ecommap.openMapWindow(<?=$mapInfo->id?>);return false;"
	><img class="preview_img" src="<?=$ecommap_url?>preview?mid=<?=$mapInfo->id?>&width=<?=$preview_width?>"/></a>
</div>

<div class="map_date"><?=htmlspecialchars($mapInfo->date)?> 作成</div>
<div class="map_description"><?=XssFilter::filter($mapInfo->description)?></div>
	
<div class="submenu_div">
<? if ($emap2010 && $blk_member) { ?>
<div style="padding:2px 0px;">
	<a href="#" class="mapIcon" onclick="ecommap.openMapWindow(<?=$mapInfo->id?>);return false;">マップを開く</a>
</div>
<div style="padding:6px 0px 4px 0px;" align="right">
	<div id="button_regist_<?=$mapInfo->id?>" class="button_regist"
		<? if ($blk_editable) { ?> style="cursor:pointer;" onclick="emap2010Map.registOnClick(<?=$blk_id?>,<?=$mapInfo->id?>, this, '<?=htmlspecialchars($mapInfo->title, ENT_QUOTES)?>');"<? } ?>
	></div>
</div>
	<? } else { ?>
<div style="padding:2px 0px">
	<a href="#" class="mapIcon" onclick="ecommap.openMapWindow(<?=$mapInfo->id?>);return false;">マップを開く</a>
</div>
<? } ?>
</div>

</div>
</div>
<?
	}
?>
</div>
<?
	session_start();
	return ob_get_clean();
	
	//ページをAjaxで出力させる
	//ブロック内HTML取得URLパラメータ
	//$params = "";
	//$COMUNI_ONLOAD[] = "ecommap.loadBlock(".$blk_id.",'".$params."')";
	//return '<div class="ecommap_block" id="ecommap_'.$blk_id.'"></div>';
}
?>
