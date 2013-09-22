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

	$skin_id = intval($_REQUEST['skin_id']);
	$p = mysql_uniq("select * from theme_skin where id = %s",
					mysql_num($skin_id));
				
	if(!$p)show_error('指定されたスキンが存在しません');

mysql_exec('lock tables theme_skin');
	$q = mysql_full("select * from page where skin=%s",mysql_num($skin_id));

	if($q)delete_confirm();

	$q =  mysql_exec("delete from theme_skin where id = %s", mysql_num($skin_id));
mysql_exec('unlock tables');
	if(!$q)show_error(mysql_error());

	$html = "<h3>$p[title]($p[filename])の削除</h3>";

	$filehead = CONF_BASEDIR.'/skin/'.$p[filename];

	$ret = rmdirhier($filehead);
	if(!$ret)$html .= "<hr><p>$filehead を消去できませんでした。<p>";
	$ret = rmdirhier($filehead.'.tpl');
	if(!$ret)$html .= "<hr><p>$filehead.tpl を消去できませんでした。<p>";
	$ret = rmdirhier($filehead.'.css');
	if(!$ret)$html .= "<hr><p>$filehead.css を消去できませんでした。<p>";
	
	$ref = '/manager/skin/list.php';

	$data = array(title   => 'スキン削除完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'スキン選択に戻る',)));

	show_input($data);

	exit(0);
}

function delete_confirm() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$skin_id = intval($_REQUEST['skin_id']);
	$p = mysql_uniq("select * from theme_skin where id = %s",
					mysql_num($skin_id));

	if(!$p)show_error('指定されたスキンが存在しません');

	$q = mysql_full("select * from page where skin=%s",mysql_num($skin_id));

	if($q){
		$page = array();
		while($b = mysql_fetch_assoc($q)){
			$page[$b['id']]['id'] = $b['id'];
			$page[$b['id']]['uid'] = $b['uid'];
			$page[$b['id']]['gid'] = $b['gid'];
			$page[$b['id']]['sitename'] = $b['sitename'];
		}
	}
	if(isset($page)){
		$page_list = "<ui>\n";
		foreach($page as $pp){
			$page_list .= '<li>'.mkhref(array('s' => $pp['sitename'],'h' => CONF_SITEURL.'?site_id='.$pp['id'],'t' => '_blank'))."</li>\n";
		}
		$page_list .= "</ui>\n";
		$confirm = '先に以下のページのスキンを変更してください'.$page_list;
	}else{
		$confirm = '削除します';
	}

	$title = $p['title'];
	$filename = $p['filename'];

	// hidden:action
	if(isset($page_list))
		$attr = array(name => 'action', value => 'delete_confirm');
	else
		$attr = array(name => 'action', value => 'delete');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'skin_id', value => $skin_id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$SYS_FORM["input"][] = array(title => 'スキンファイル名',
								 name  => 'filename',
								 body  => $filename);

	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'スキンの名称',
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

	$data = array(title   => 'スキンの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function select_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$s = mysql_full('select * from theme_skin');

	if (!$s) { show_error('システムエラー'. mysql_error()); }

	$select_option = array();
	while ($r = mysql_fetch_array($s)) {
		$title = $r['title'] ? $r['title'] : 'タイトル未設定';
		$select_option[$r['id']] = $title. ' ('. $r['filename']. ')';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'delete_confirm');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin
	$attr = array(title => 'スキン:', name => 'skin_id', value => '', option => $select_option);
	$SYS_FORM["input"][] = array(title => '削除するスキンの選択',
								 name  => 'skin_id',
								 body  => get_form("select", $attr));

	$SYS_FORM["action"] = 'delete.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'スキンの編集',
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
	if(is_dir($file)){
		$dirh = opendir($file);
		while(($f = readdir($dirh)) !== false){
			if($f=='.' or $f=='..')continue;
			rmdirhier($file.'/'.$f);
		}
		$ret = rmdir($file);
	}else
		$ret = unlink($file);
	return $ret;
} 

?>
