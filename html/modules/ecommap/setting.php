<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/**
 * eコミマップ連携モジュール
 * 初期設定画面

//手動登録
SET character_set_client = utf8;
SET character_set_results = utf8;
SET character_set_connection = utf8;
DELETE FROM module_setting WHERE mod_name='ecommap';
INSERT INTO module_setting (mod_title, mod_name, type, addable, multiple) VALUES ('eコミマップ連携', 'ecommap', 7, 1, 1);
*/

require dirname(__FILE__). '/../../lib.php';
include_once(dirname(__FILE__). '/config.php');

admin_check();

//actionに対応する処理呼び出し
switch ($_REQUEST["action"]) {
	case 'initdb':
		mod_ecommap_init_db(); break;
	case 'regist':
		mod_ecommap_regist(); break;
	default:
		mod_ecommap_form(); break;
}

/** 初期設定フォーム input情報 */
function mod_ecommap_form_inputs()
{
	$inputs = array();
	$inputs[] = array('title'=>'eコミマップ連携サーバ設定');
	$inputs[] = array('name'=>'ecommap_url', 'title'=>'eコミマップ連携サーバURL <br/>※最後に/map/が必要 ( 例: http://map.ecom-plat.jp/map/ )',
		'type'=>'text', 'attr'=>array('style'=>"width:99%"),
		'default'=>'');
	$inputs[] = array('name'=>'ecommap_cid', 'title'=>'eコミマップコミュニティID<br/>（パーツ毎にコミュニティを指定する場合、指定がない場合はこのコミュニティが利用されます）',
		'type'=>'text', 'attr'=>array('style'=>"width:40px"), 'default'=>'');
	
	$inputs[] = array('name'=>'ecommap_group', 'title'=>'パーツブロックに対応するeコミマップコミュニティ', 'type'=>'select',
		'attr'=>array('option'=>array(MOD_ECOMMAP_GROUP_SINGLE_COMMUNITY=>"１つのコミュニティを共用", MOD_ECOMMAP_GROUP_IN_SERVER=>"パーツ毎にコミュニティを指定 ")
	));
	
	$inputs[] = array('name'=>'sync_user_level', 'title'=>'ブロックの連携設定', 'type'=>'select',
		'attr'=>array('option'=>array(Permission::USER_LEVEL_ADMIN=>"システム管理者のみ可能 ", Permission::USER_LEVEL_POWERED=>"グループ管理者も可能")
	));
	
	$inputs[] = array('name'=>'admin_user_level', 'title'=>'eコミマップ管理画面へのメニュー表示', 'type'=>'select',
		'attr'=>array('option'=>array(Permission::USER_LEVEL_ADMIN=>"システム管理者のみ可能 ", Permission::USER_LEVEL_POWERED=>"グループ管理者も可能")
	));
	
	return $inputs;
}

/** 初期設定フォーム */
function mod_ecommap_form($msg='')
{
	global $SYS_FORM, $JQUERY, $COMUNI, $COMUNI_ONLOAD, $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW, $COMUNI_HEAD_CSSRAW;;
	
	//共通ヘッダ読み込み
	include_once(MOD_ECOMMAP_PATH.'/head.php');
	
	//メッセージ追加
	//メッセージ追加
	$html = '';
	if (!empty($msg)) {
		$html .= '<div id="setting_msg" style="margin:4px;padding:4px;border:1px solid #DDDD66;">'.$msg.'</div>';
		//$COMUNI_ONLOAD[] = "setTimeout('clearSettingMsg()',3000);";
		$COMUNI_HEAD_JSRAW[] = "function clearSettingMsg(){document.getElementById('setting_msg').style.display='none';}";
		$COMUNI_HEAD_CSSRAW[] = "#setting_msg .error { color:#CC0000; font-weight: bold;}";
	}
	
	include_once("classes/EcomMapDB.php");
	
	$has_table = EcomMapDB::hasTable();
	//オプション取得
	if (!is_array($options)) $options = EcomMapDB::getOptions();
	
	// テーブル初期化リンク
	//TODO バージョンチェック
	
	$html .= '<div style="padding:4px;">';
	$html .= $has_table
			? 'テーブルは作成済みです<br/>DBバージョン: '.EcomMapDB::getOption('VERSION')
			: 'テーブルが作成されていません<br/>・<a href="'.MOD_ECOMMAP_URL.'/setting.php?action=initdb">テーブル生成</a>';
	$html .= '</div>';
	
	//フォーム生成
	if ($has_table) {
		foreach (mod_ecommap_form_inputs() as $input) {
			$name = $input['name'];
			if (empty($name)) {
				$SYS_FORM["input"][] = array('title'=>'<div style="padding:4px;margin:-2px -4px 0px -4px;font-weight:bold;background-color:#CCC;border-top:4px solid white;">'.$input['title'].'</div>');
			} else {
				$value = $options[$name];
				if (strlen($value)==0) $value = $input['default'];
				$attr = $input['attr'];
				$attr['name'] = $name;
				$attr['value'] = $value;
				$SYS_FORM["input"][] = array( 'title'=>$input['title'], 'name'=>$name, 'body'=>get_form($input['type'], $attr) );
			}
		}
		$SYS_FORM["onSubmit"] = "ecommap.ajaxSubmit('/setting.php?action=regist', j$(document.forms)[0], '".MOD_ECOMMAP_URL."/setting.php'); return false;";
		$SYS_FORM["pmt"] = false;
	}
	//CSRF対策
	$SYS_FORM["input"][] = array( type=>"hidden", name=>FormBuildId::PARAM_NAME, value=>FormBuildId::getFormBuildId());
	$SYS_FORM["method"] = 'POST';
	$SYS_FORM["submit"] = '更新';
	$html .= create_form();
	
	$data = array(title=>'eコミマップ共通設定', icon=>'write', content=>$html);
	$COMUNI['manager_mode'] = true;
	show_input($data);
	exit(0);
}

/** DBに登録 Ajax */
function mod_ecommap_regist()
{
	//CSRF対策
	if (false === FormBuildId::checkFormBuildId()) {
		echo "セッションが無効になった可能性があります\nもういちど編集画面からやり直して下さい"; return;
	}
		
	admin_check();
	include_once("classes/EcomMapDB.php");
	foreach (mod_ecommap_form_inputs() as $input) {
		$name = $input['name'];
		if (!empty($name)) {
			//DB更新処理
			$result = EcomMapDB::setOption($name, trim($_POST[$name]));
			
			if (!$result) break;//エラーなら抜ける
		}
	}
	
	if (!$result) {
		$msg = "更新できませんでした。";
	} else {
		$msg = '更新しました。';
	}
	echo $msg;
}


/** DB初期化 */
function mod_ecommap_init_db()
{
	admin_check();
	
	include_once("classes/EcomMapDB.php");
		
	//テーブルがなければ作成
	$has_table = EcomMapDB::hasTable();
	
	ob_start();
	//if ($has_table) { echo "<li>テーブルは作成済みです</li>"; }
	//else {
		EcomMapDB::createTables();
	//}
	
	$msg = '<ul style="margin:0px;padding:0px;">'.ob_get_clean().'</ul>';
	
	//フォーム呼び出し
	mod_ecommap_form($msg);
}
?>