<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
/**
 * eコミマップ連携モジュール
 * ブロック設定画面
 * 
 */

require_once dirname(__FILE__). '/../../lib.php';
include_once(dirname(__FILE__). '/config.php');

//ID取得
$blk_id = $_REQUEST["blk_id"];
//権限チェック
if (!is_poweruser($blk_id)) {
	echo "権限がありません"; return;
}

//actionに対応する処理呼び出し
switch ($_REQUEST["action"]) {
	case 'regist':
		mod_ecommap_block_regist($blk_id); break;
	default:
		mod_ecommap_block_form($blk_id); break;
}

/** フォームinput情報取得関数 */
function mod_ecommap_block_form_inputs($blk_id)
{
	$groupType = EcomMapDB::getOption("ecommap_group");
	$syncUserLevel = EcomMapDB::getOption("sync_user_level");
	$syncEditable = is_su();
	if ($syncUserLevel == Permission::USER_LEVEL_POWERED && is_poweruser($blk_id)) $syncEditable = true;
	
	$groupInAny = false;//$groupType==MOD_ECOMMAP_GROUP_IN_ANY;  //未対応
	$groupInServer = $groupType==MOD_ECOMMAP_GROUP_IN_SERVER;
	$groupSameCommunity = $groupType==MOD_ECOMMAP_GROUP_SINGLE_COMMUNITY;
	$groupInCommunity = $groupType==MOD_ECOMMAP_GROUP_IN_COMMUNITY;
	
	$inputs = array();
	$inputs[] = array('title'=>'eコミマップ連携設定');
	$editable = $syncEditable && $groupInAny;
	$inputs[] = array('name'=>'ecommap_url', 'title'=>'ブロックが連携する eコミマップ連携サーバURL',
		'type'=>$editable?"text":"plain",
		'attr'=>array('style'=>"width:99%"),
		'default'=>EcomMapDB::getOption("ecommap_url"));
	$editable = $syncEditable && $groupInServer;
	$inputs[] = array('name'=>'ecommap_cid', 'title'=>'ブロックが連携する eコミマップコミュニティID',
		'type'=>$editable?"text":"plain",
		'attr'=>array('value'=>$editable?null:EcomMapDB::getOption("ecommap_cid"), 'style'=>"width:80px"));
	/*$editable = $syncEditable && $groupInCommunity;
	$inputs[] = array('name'=>'ecommap_gid', 'title'=>'ブロックが連携する eコミマップグループID',
		'type'=>$editable?"text":"plain",
		'attr'=>array('style'=>"width:80px"),
		'default'=>'0');
	*/
	
	$inputs[] = array('title'=>'eコミマップ表示設定');
	
	$inputs[] = array(title=>'地図表示タイプ', name =>'map_type', type=>"select",
		attr=>array(
			option=>array(
				MOD_ECOMMAP_TYPE_LIST=>"地図一覧(作成が新しい順)",
				MOD_ECOMMAP_TYPE_MODIFIED=>"地図一覧(登録情報の更新が新しい順)",
				MOD_ECOMMAP_TYPE_MAP=>"地図を１つだけ表示"
			),
			onChange=>"setSettingMode(this.value);"
		),
	);
	
	$inputs[] = array(title=>'地図プレビュー画像の配置', name =>'preview_pos',
		type=>"select", attr=>array(option=>array("left"=>"左", "right"=>"右", "top"=>"説明の上")) );
	$inputs[] = array(title=>'地図プレビュー画像の幅', name =>'preview_width',
		type=>"text", attr=>array(size=>4), 'default'=>80);
	
	$inputs[] = array(title=>'表示する地図ID', name =>'map_id', type=>"text", attr=>array(size=>4) );
	
	$inputs[] = array('name'=>'map_num', 'title'=>'一覧で表示する地図の最大件数', 'type'=>'select',
		'attr'=>array('option'=>array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,20=>20,25=>25)),
		'default'=>'5' );
	
	//$inputs[] = array('name'=>'layers_num', 'title'=>'登録情報 更新状況表示件数', 'type'=>'select',
	//	'attr'=>array('option'=>array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,20=>20,25=>25)) );
	//$inputs[] = array('name'=>'list_height', 'title'=>'ブロックの高さ制限 (px)', 'type'=>'text', 'attr'=>array('style'=>"width:80px"));
	
	return $inputs;
}
/** フォーム表示 */
function mod_ecommap_block_form($blk_id)
{
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW, $COMUNI_ONLOAD;

	$COMUNI_HEAD_JSRAW[] = <<<_EOT_
function setSettingMode(maptype)
{
	var ids = ['map_num','map_period','preview_pos','preview_width','description_length',
		'layers_num','layers_period','layers', 'list_height', 'map_id'];
	switch (parseInt(maptype)) {
	case 1:
		setInputVisible(ids,[0,0,1,1,1, 0,1,1,0,1]);
	break;
	case 10:
		setInputVisible(ids,[1,0,1,1,1, 1,1,1,1,0]);
	break;
	case 20:
		setInputVisible(ids,[1,1,1,1,1, 1,1,1,1,0]);
	break;
	}
}
function setInputVisible(ids, visibles)
{
	for (var i=0; i<ids.length; i++) {
		var input = document.getElementById(ids[i]);
		if (input) {
			var d = (visibles[i]==1?'':'none');
			input.style.display = d;
			input.parentNode.style.display=d;
			input.parentNode.previousSibling.style.display=d;
		}
	}
}
_EOT_;

	$COMUNI_ONLOAD[] = "setSettingMode(document.getElementById('map_type').value)";
	
	//共通ヘッダ読み込み
	include_once(MOD_ECOMMAP_PATH.'/head.php');
	
	//メッセージ追加
	$html = '';
	//if (!empty($msg)) $html .= '<div style="margin:4px;padding:4px;border:1px solid #DDDD66;">'.$msg.'</div>';
	
	//DB検索
	include_once("classes/EcomMapDB.php");
	//オプション取得
	$options = EcomMapDB::getBlockOptions($blk_id);
	
	$has_table = EcomMapDB::hasTable();
	
	$return_url = home_url($blk_id);
	
	//Input出力
	if ($has_table) {
		$inputs = mod_ecommap_block_form_inputs($blk_id);
		foreach ($inputs as $input) {
			$name = $input['name'];
			if (empty($name)) {
				$SYS_FORM["input"][] = array('title'=>'<div style="padding:4px;margin:-2px -4px 0px -4px;font-weight:bold;background-color:#CCC;border-top:4px solid white;">'.$input['title'].'</div>');
			} else {
				$value = $options[$name];
				if (strlen($value)==0) $value = $input['default'];
				$attr = $input['attr'];
				$attr['name'] = $name;
				if (empty($attr['value'])) $attr['value'] = $value;
				$SYS_FORM["input"][] = array( 'title'=>$input['title'], 'name'=>$name, 'body'=>get_form($input['type'], $attr) );
			}
		}
		//フォーム設定
		$SYS_FORM["onSubmit"] = "ecommap.ajaxSubmit('/block_setting.php?blk_id=".$blk_id."&action=regist', j$(document.forms)[0], '".$return_url."'); return false;";
		$SYS_FORM["pmt"] = false;
	}
	//CSRF対策
	$SYS_FORM["input"][] = array( type=>"hidden", name=>FormBuildId::PARAM_NAME, value=>FormBuildId::getFormBuildId());
	$SYS_FORM["method"] = 'POST';
	$SYS_FORM["submit"] = '更新';
	$SYS_FORM["cancel"] = 'ページに戻る';
	$SYS_FORM["onCancel"] = "location.href='".$return_url."'; return false;";
	
	$html .= create_form(array(blk_id=>$blk_id));
	
	$data = array(title=>'eコミマップ連携設定', icon=>'write', content=>$html);

	show_input($data);
	exit(0);
}

/** 登録処理 */
function mod_ecommap_block_regist($blk_id)
{
	//CSRF対策
	if (false === FormBuildId::checkFormBuildId()) {
		echo "セッションが無効になった可能性があります\nもういちど編集画面からやり直して下さい"; return;
	}
	
	//POSTの内容を$optionsに入れて、DB更新
	$options = array();
	include_once("classes/EcomMapDB.php");
	
	$inputs = mod_ecommap_block_form_inputs($blk_id);
	
	foreach ($inputs as $input) {
		$name = $input['name'];
		if (!empty($name)) {
			$options[$name] = trim($_POST[$name]);	
			$result = EcomMapDB::setBlockOption($name, $options[$name], $blk_id);
			if (!$result) break;//エラーなら抜ける
		}
	}
	
	if (!$result) {
		$msg = "更新できませんでした。".mysql_error();
	} else {
		$msg = '更新しました。';
	}
	
	echo $msg;
	//フォーム呼び出し
	//mod_ecommap_block_form($blk_id);
}

?>
