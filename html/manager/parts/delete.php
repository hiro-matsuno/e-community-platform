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
	case 'delete':
		delete_data();
	case 'delete_confirm':
		delete_confirm();
	default:
		select_data();
}

/* 登録*/
function delete_data() {
	global $SYS_FORM;

	$parts_id = intval($_REQUEST['parts_id']);
	$p = mysql_uniq("select * from module_setting where id = %s",
					mysql_num($parts_id));
				
	if(!$p)show_error('指定されたパーツが存在しません');

	$q = mysql_full("select page.id,page.uid,page.gid,page.sitename,block.name".
					" from block".
					" inner join page on block.pid = page.id".
					" where module=%s",
					mysql_str($p['mod_name']));

	if($q)delete_confirm();

	ModuleManager::getInstance()->getModule( $p[mod_name] )
		->execCallBackFunction( "uninstall", array(), $ret_val );

	$q =  mysql_exec("delete from module_setting where id = %s", mysql_num($parts_id));
	if(!$q)show_error(mysql_error());

	$html = "<h3>$p[mod_title]($p[mod_name])の削除</h3>";

	if(isset($ret_val))
		$html .= $ret_val;

	//	なにもソース自体消す必要は無いのでは.
	//$ret = rmdirhier($mod_dir);
	//if(!$ret)$html .= "<hr><p>$mod_dir を消去できませんでした。<p>";

	$ref = '/manager/parts/delete.php';

	$data = array(title   => 'パーツ削除完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'パーツ選択に戻る',)));

	show_input($data);

	exit(0);
}

function delete_confirm() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$parts_id = intval($_REQUEST['parts_id']);
	$p = mysql_uniq("select * from module_setting where id = %s",
					mysql_num($parts_id));

	if(!$p)show_error('指定されたパーツが存在しません');

	$q = mysql_full("select page.id,page.uid,page.gid,page.sitename,block.name".
					" from block".
					" inner join page on block.pid = page.id".
					" where module=%s",
					mysql_str($p['mod_name']));

	if($q){
		$page = array();
		while($b = mysql_fetch_assoc($q)){
			$page[$b['id']]['id'] = $b['id'];
			$page[$b['id']]['uid'] = $b['uid'];
			$page[$b['id']]['gid'] = $b['gid'];
			$page[$b['id']]['sitename'] = $b['sitename'];
			$page[$b['id']]['block'][] = $b['name'];
		}
	}
	if(isset($page)){
		$page_list = "<ui>\n";
		foreach($page as $pp){
			$page_list .= '<li>'.mkhref(array('s' => $pp['sitename'],'h' => CONF_SITEURL.'?site_id='.$pp['id'],'t' => '_blank')).'<ul>';
			foreach($pp['block'] as $bb){
				$page_list .= '<li>'.$bb.'</li>';
			}
			$page_list .= "</ul></li>\n";
		}
		$page_list .= "</ui>\n";
		$confirm = '先に以下のパーツを削除してください'.$page_list;
	}else{
		$confirm = '削除します';
	}

	$title = $p['mod_title'];
	$filename = $p['mod_name'];

	// hidden:action
	if(isset($page_list))
		$attr = array(name => 'action', value => 'delete_confirm');
	else
		$attr = array(name => 'action', value => 'delete');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'parts_id', value => $parts_id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$SYS_FORM["input"][] = array(title => 'パーツファイル名',
								 name  => 'filename',
								 body  => $filename);

	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'パーツの名称',
								 name  => 'title',
								 body  => $title);

	$SYS_FORM["input"][] = array(title => '確認',
								 name  => 'confirm',
								 body  => $confirm);
	

	$SYS_FORM["action"] = 'delete.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '削除';
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

	if (!$s) { show_error('謎エラー'. mysql_error()); }

	$select_option = array();
	while ($r = mysql_fetch_array($s)) {
		$select_option[$r['id']] = $r['mod_title']. ' ('. $r['mod_name']. ')';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'delete_confirm');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin
	$attr = array(title => 'パーツ:', name => 'parts_id', value => '', option => $select_option);
	$SYS_FORM["input"][] = array(title => '編集するパーツの選択',
								 name  => 'parts_id',
								 body  => get_form("select", $attr));

	$SYS_FORM["action"] = 'delete.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'パーツの編集',
				  icon    => 'write',
				  content => $html);

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

function rmdirhier($file) {
	if(is_dir($dir.'/'.$file)){
		$dirh = opendir($dir.'/'.$file);
		while(($f = readdir($dirh)) !== false){
			if($f=='.' or $f=='..')continue;
			rmdirhier($dir.'/'.$file.'/'.$f);
		}
		$ret = rmdir($dir.'/'.$file);
	}else
		$ret = unlink($dir.'/'.$file);
	return $ret;
} 

?>
