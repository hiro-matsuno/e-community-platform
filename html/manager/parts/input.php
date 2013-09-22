<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/*
TODO

*/
require dirname(__FILE__). '/../../lib.php';

admin_check();

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data();
	case 'edit':
		input_data();
	case 'entry':
		entry_data();
	case 'new':
		input_new();
	default:
		select_data();
}

/* 登録*/
function regist_data() {
	global $SYS_FORM;

	$parts_id = intval($_REQUEST['parts_id']);
	$p = mysql_uniq("select * from module_setting where id = %s",
					mysql_num($parts_id));
				
	if(!$p)show_error('指定されたパーツが存在しません');

	$title = htmlspecialchars($_REQUEST['title']);
	$multiple = intval($_REQUEST['multiple']);
	$type = intval($_REQUEST['type']);

	if(!$title)$SYS_FORM['error']['title'] = 'パーツの名称を設定してください';
	
	if($SYS_FORM['error'])input_data();

	$p = mysql_exec("update module_setting set mod_title=%s, multiple=%s, type=%s".
					" where id = %s",
					mysql_str($title), mysql_num($multiple), mysql_num($type), mysql_num($parts_id));
	if(!$p)show_error(mysql_error());
	
	$ref = '/manager/parts/list.php';

	$html = '編集完了しました。';
	$data = array(title   => 'パーツ編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'パーツ選択に戻る',)));

	show_input($data);

	exit(0);
}

function input_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;
	
	
	
	$parts_id = intval($_REQUEST['parts_id']);
	$p = mysql_uniq("select * from module_setting where id = %s",
					mysql_num($parts_id));
					
	if(!$p)show_error('指定されたパーツが存在しません');

	$title = $p['mod_title'];
	$multiple = $p['multiple'];
	$type = $p['type'];
	$filename = $p['mod_name'];

	if(isset($SYS_FORM['cache'])){
		$title = $SYS_FORM["cache"]["title"];
		$multiple = $SYS_FORM['cache']['multiple'];
		$type = $SYS_FORM['cache']['type'];
	}
					
	
	
	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'parts_id', value => $parts_id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$SYS_FORM["input"][] = array(title => 'パーツファイル名',
								 name  => 'filename',
								 body  => $filename);

	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'パーツの名称',
								 name  => 'title',
								 body  => get_form("text", $attr));

	//配置可能ページの選択

	//複数配置可否
	$attr = array(name => 'multiple', option => array(1 => '同一ページに複数配置可能'), value => array(1 => $multiple));
	$SYS_FORM["input"][] = array(title => '複数配置可否',
								 name  => 'multiple',
								 body  => get_form("checkbox", $attr));

	$attr = array(name => 'type', option => get_skin_level(), 'value' => $type);
	$SYS_FORM["input"][] = array(title => '表示タイプ',
								 name  => 'type',
								 body  =>get_form('select', $attr));
	

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'パーツの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function select_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$s = mysql_full('select * from module_setting');

	if (!$s) { show_error('システムエラー'. mysql_error()); }

	$select_option = array();
	while ($r = mysql_fetch_array($s)) {
		$select_option[$r['id']] = $r['mod_title']. ' ('. $r['mod_name']. ')';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'edit');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin
	$attr = array(title => 'パーツ:', name => 'parts_id', value => '', option => $select_option);
	$SYS_FORM["input"][] = array(title => '編集するパーツの選択',
								 name  => 'parts_id',
								 body  => get_form("select", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'パーツの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_new() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$MODULE_DIR = CONF_BASEDIR.'/modules';

	$p = mysql_full("select * from module_setting");
	$modules = array();
	while($m = mysql_fetch_assoc($p)){
		$modules[$m['mod_name']] = $m;
	}

	$mod_opt = array();
	if(is_writable($MODULE_DIR))
		$mod_opt[0] = 'アップロード';
	$mod_dir = opendir($MODULE_DIR);
	while(($f = readdir($mod_dir)) !== false){
		if($f=='.' or $f=='..')continue;
		if( !file_exists($MODULE_DIR.'/'.$f.'/block.php')
			and !file_exists($MODULE_DIR.'/'.$f.'/module.php') ) {
			continue;
		}
		if(!isset($modules[$f])){
			$mod_opt[$f] = $f;
		}
	}

	if($SYS_FORM['cache']['multiple']){
		$multiple = $SYS_FORM['cache']['multiple'];
	}else{
		$multiple = true;
	}	

	$JQUERY['ready'][] = <<<__JS__
	if($('#filename').val() != '0')
		$('#mod_file').attr('disabled', 'disabled').css("background-color", "#efefef");
	else
		$('#mod_file').removeAttr('disabled').css("background-color", "#ffffff");
	$('#filename').change(function() {
		if($('#filename').val() != '0')
			$('#mod_file').attr('disabled', 'disabled').css("background-color", "#efefef");
		else
			$('#mod_file').removeAttr('disabled').css("background-color", "#ffffff");
});
__JS__;

	// hidden:action
	$attr = array(name => 'action', value => 'entry');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	//module_file
	if(!is_writable($MODULE_DIR))
		$bhtml = $MODULE_DIR.'が書き込み可能に設定されていないため、パーツのアップロード機能は使用できません。<br>'.
				'書き込み可能に設定するか、あらかじめパーツをアップロードしておいてください。<br>';
	$attr = array(name => 'filename', value => $SYS_FORM["cache"]["filename"], option =>$mod_opt , bhtml => $bhtml);
	$file_form = get_form("file",array(name => 'mod_file'));
	$SYS_FORM["input"][] = array(title => 'パーツ選択',
								 name  => 'filename',
								 body  => get_form("select", $attr).$file_form);

	$attr = array(name => 'title', value => $SYS_FORM["cache"]["title"], size => 32);
	$SYS_FORM["input"][] = array(title => 'パーツの名称',
								 name  => 'title',
								 body  => get_form("text", $attr));

	//配置可能ページの選択

	//複数配置可否
	$attr = array(name => 'multiple', option => array(1 => '同一ページに複数配置可能'), value => array(1 => $multiple));
	$SYS_FORM["input"][] = array(title => '複数配置可否',
								 name  => 'multiple',
								 body  => get_form("checkbox", $attr));

	$attr = array(name => 'type', option => get_skin_level(), 'value' => $SYS_FORM["cache"]["type"]);
	$SYS_FORM["input"][] = array(title => '表示タイプ',
								 name  => 'type',
								 body  =>get_form('select', $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'パーツの追加',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function entry_data() {
	global $SYS_FORM;
	$MODULE_DIR = CONF_BASEDIR.'/modules';

	$filename    = $_REQUEST['filename'];
	$title       = htmlspecialchars($_REQUEST['title']);
	$multiple    = intval($_REQUEST['multiple']);
	$type        = intval($_REQUEST['type']);

	$max_filesize = min(return_bytes(ini_get('post_max_size')),return_bytes(ini_get('upload_max_filesize')));

	if($filename == '0'){
		switch($_FILES['mod_file']['error']){
			case UPLOAD_ERR_OK:
				//アップロード成功:
				break;
        	case UPLOAD_ERR_INI_SIZE:
        		show_error( 'ファイルサイズの制限'.ini_get('upload_max_filesize').'バイトを超過しています。');
        		break;
        	case UPLOAD_ERR_FORM_SIZE:
        		show_error('ファイルサイズの制限'.$max_filesize.'バイトを超過しています。');
        		break;
        	case UPLOAD_ERR_NO_FILE:
        		show_error('ファイルを選択してください。');
        		break;
        	default:
        		show_error('ファイルのアップロード中にエラーが発生しました。');
        		break;
        }

		if(!is_writable($MODULE_DIR))show_error($MODULE_DIR.'に書き込みできません。');

		$pos = strrpos($_FILES['mod_file']['name'], '.');
		$filename = substr($_FILES['mod_file']['name'],0,$pos);

//		if(file_exists($MODULE_DIR.'/'.$filename)){
//			$SYS_FORM["error"]["filename"] = "モジュール${filename}は既にアップロードされているようです";
//		}
		
		mkdir($MODULE_DIR.'/'.$filename);

		$zip = new ZipArchive;
		if ($zip->open($_FILES['mod_file']['tmp_name']) === TRUE) {
			$block_exists = false;
			for($i = 0; $i < $zip->numFiles; $i++){
				if($zip->getNameIndex($i) == 'block.php'){
					$block_exists = true;
					break;
				}
			}
			if($block_exists)
				$zip->extractTo($MODULE_DIR.'/'.$filename);
			else
				$SYS_FORM['error']['filename'] = 'アップロードされたZIPファイルはｅコミ２．０のパーツではないようです';
			$zip->close();
		} else {
    		$SYS_FORM["error"]["filename"] = 'ZIPファイルの読み取りに失敗しました。ファイルが正しいか確認してください';
    		$filename = '0';
		}
	}

	$SYS_FORM["cache"]["title"] = $title;
	if($filename != '0')
		$SYS_FORM["cache"]["filename"] = $filename;
	if($multiple)
		$SYS_FORM["cache"]["multiple"] = true;
	else
		$SYS_FORM["cache"]["multiple"] = false;

	if (!strlen($filename)) {
		show_error('ファイル名が取得できませんでした。');
	}

	if(!$title)$SYS_FORM["error"]["title"] = "パーツの名称を入力してください";

	if($SYS_FORM["error"])input_new();

	$ret_val = true;

	$module = new Module;
	$module->setModName( $filename );
	$module->execCallbackFunction( "install", array(), $ret_val );

	if ( false !== $ret_val ) {

		$p = mysql_exec("delete from module_setting where mod_name = %s",
						mysql_str($filename));
		$p = mysql_exec("insert into module_setting".
						" (mod_title, mod_name, type, addable, multiple)".
						" values (%s, %s, %s, 1, %s)",
						mysql_str($title), mysql_str($filename),mysql_num($type),
						mysql_num($multiple));

		if(!$p)show_error(mysql_error());

		if(isset($ret_val)) $html = $ret_val;
		else $html = '編集完了しました。';

	} else {
		$html = 'インストールに失敗しました.';
	}

	$data = array(title   => 'パーツ追加完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'パーツ選択に戻る',)));

	show_input($data);

	exit(0);
}
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // 'G' は PHP 5.1.0 以降で使用可能です
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function get_skin_level() {
	return array('7' => '特に指定しない',
				 '3' => 'ポータルページ&amp;グループページ',
				 '6' => 'グループページ&amp;マイページ',
				 '5' => 'ポータルページ&amp;マイページ',
				 '1' => 'ポータルページのみ',
				 '2' => 'グループページのみ',
				 '4' => 'マイページのみ',
				 '0' => '管理者のみ使用可能',
				 '-1'=> '使用不可'
	);
}

?>
