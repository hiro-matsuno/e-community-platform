<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/common.php';

kick_guest();

$profile_uid = intval($_REQUEST['uid']);
$eid = intval($_REQUEST["eid"]);
if($eid==0){// $eid=0ならばユーザー自身ののプロフィールを編集
	$profile_gid = 0;
	if(!$profile_uid)
		$profile_uid = myuid();	
	$profile_data = mysql_uniq('select * from profile_data where uid = %s and gid = %s',
					mysql_num($profile_uid),mysql_num(0));
	if($profile_data){
		$profile_id = $profile_data['id'];
	}else{
		$profile_id = get_seqid();
	}
}else{
	//$eidがプロフィールデータのIDと仮定
	$profile_id = $eid;
	$profile_data = mysql_uniq('select * from profile_data where id=%s',mysql_num($eid));
	$profile_gid = $profile_data['gid'];
	$profile_uid = $profile_data['uid'];
	
	if(!$profile_data){
		//$eidがプロフィールパーツのIDと仮定
		$q = mysql_uniq('select page.gid,page.uid from block left join page on block.pid = page.id'.
						' where block.id = %s',mysql_num($eid));
		if($q){
			$profile_gid = $q['gid'];
			$profile_uid = $q['uid'];
			$profile_data = mysql_uniq('select * from profile_data where uid=%s and gid = %s',
							mysql_num($profile_uid),mysql_num($profile_gid));
			if($profile_data)
				$profile_id = $profile_data['id'];
		}else{
			//$eidはプロフィールデータのIDでもプロフィールパーツのIDでもない
			show_error('指定されたeidに対応するプロフィールデータがありません');
		}
	}
}

if($profile_uid != myuid() and !is_su() and join_level($profile_gid)<80)
	show_error('編集権限がありません');

if(!$profile_data){
	$q = mysql_exec("insert into profile_data (id,uid,gid) values(%s,%s,%s)",
					mysql_num($profile_id),mysql_num($profile_uid),mysql_num($profile_gid));
	$q = mysql_exec("insert into profile_pmt (id) values(%s)",
					mysql_num($profile_id));
	$profile_data = mysql_uniq("select * from profile_data where id = %s", mysql_num($profile_id));
}
$profile_pmt = mysql_uniq("select * from profile_pmt where id = %s", mysql_num($profile_id));

if($profile_gid)$profile_columns = $group_columns;
else $profile_columns = $user_columns;

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_profile($profile_id, $profile_uid, $profile_gid);
		break;
	default:
		input_profile($profile_data,$profile_pmt);
}

/* 登録*/
function regist_profile($profile_id, $profile_uid, $profile_gid) {
	global $SYS_PROFILE;
	
	foreach($SYS_PROFILE['target'] as $key => $type){
		if(!isset($_REQUEST["pmt_$key"]))continue; 
		//!isset($_REQUEST[$key]) とすると birthday に対応できない
		$keys[] = $key;
		switch ($key) {
			case 'thumb':
				switch($_REQUEST['thumb_set']){
					case 0:
						$values[] = mysql_str($_REQUEST['thumb_save']);
						break;
					case 1:
						$values[] = mysql_str(null);
						break;
					default:
						$values[] = mysql_str(create_icon());
						break;
				}
				break;
			default :
				switch ($type){
					case 'timestamp':
						$values[] = mysql_str(form2datetime($key));
						break;
					case 'value':
						$values[] = mysql_num($_REQUEST[$key]);
						break;
					case 'text':
						$values[] = mysql_str($_REQUEST[$key]);
						break;
				}
				break;
		}
		$pmts[] = mysql_num($_REQUEST["pmt_$key"]);
	}
		
	mysql_exec("delete from profile_data where id = %s",mysql_num($profile_id));
	mysql_exec("insert into profile_data (id,uid,gid,%s) values (%s,%s,%s,%s)",
				implode(',',$keys), $profile_id, mysql_num($profile_uid),
				mysql_num($profile_gid), implode(',',$values));

	mysql_exec("delete from profile_pmt where id = %s",mysql_num($profile_id));
	mysql_exec("insert into profile_pmt (id,%s) values (%s,%s)",
				implode(',',$keys), $profile_id, implode(',',$pmts));
				
	$html = '編集完了しました。';
	if($profile_uid){
		if(get_eid_by_mypage($profile_uid))
			$href = CONF_URLBASE."/index.php?uid=$profile_uid";
		else
			$href = CONF_SITEURL;
	}else
		$href = CONF_URLBASE."/index.php?gid=$profile_gid";
	$data = array(title   => 'プロフィールの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $profile_id, href => $href)));

	show_input($data);
}

/* フォーム*/
function input_profile($dataset, $pmt) {
	global $profile_columns;
	global $SYS_FORM,$JQUERY;

	$gid = $dataset['gid'];

	if ($dataset) {
		$SYS_FORM["input"][] = array(body  => get_form("hidden",
													   array(name  => 'action',
															 value => 'regist')));
															 
		foreach ($profile_columns as $column => $title) {
			$val = $dataset[$column];
			switch ($column) {
				case 'thumb':
					if (isset($val) && $val != '') {
						$JQUERY['ready'][] = <<<__READY_FUNCTION__
$('#$column').attr('disabled', 'disabled').css("background-color", "#efefef");
$('#${column}_set_0').click(function() {
	$('#${column}_img').attr('src', '/databox/profile/b/$val');
	$('#$column').attr('disabled', 'disabled').css("background-color", "#efefef");
});
$('#${column}_set_1').click(function() {
	$('#${column}_img').attr('src', '');
	$('#$column').attr('disabled', 'disabled').css("background-color", "#efefef");
});
$('#${column}_set_2').click(function() {
	$('#${column}_img').attr('src', '');
	$('#${column}').removeAttr('disabled').css("background-color", "#ffffff");
});
__READY_FUNCTION__;
						$option = array(0 => 'このまま', 1 => '削除', 2=> '変更');
						$image = '<img id="'.$column.'_img" src="/databox/profile/b/'. $val. '" border="0"><br>';
						$thumb_set = get_form("radio",array(name => $column.'_set',
															value=>0,
															option => $option,
															bhtml => $image));
						$SYS_FORM["input"][] = array(body => get_form("hidden", 
																		array(name => $column.'_save',value => $val)));
					}else{
						$SYS_FORM["input"][] = array(body => get_form("hidden", 
																		array(name => $column.'_set',value => '2')));
					}
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("file",
																   array(name  => $column,
																   		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 bhtml => $thumb_set)));
					break;
				case 'name':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 32)));
					break;
				case 'name_kana':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 32)));
					break;
				case 'zip':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 10)));
					break;
				case 'address':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 42)));
					break;
				case 'tel':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 20)));
					break;
				case 'sex':
					$option = array(1 => '男性', 2 => '女性', 3 => '秘密');
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("select",
																   array(name  => $column,
																		 value => $val,
																		 option => $option,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column])));
					break;
				case 'birthday':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("date",
																   array(name  => $column,
																		 value => strtotime($val),
																		 format=>'西暦Y年M月D日',
																		 gid => $gid,
																		 pmt_val   => $pmt[$column])));
					break;
				case 'blood':
					$option = array(1 => 'A', 2 => 'B', 3 => 'O', 4 => 'AB', 5 => '?');
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("select",
																   array(name  => $column,
																		 value => $val,
																		 option => $option,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column])));
					break;
				case 'birthplace':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("text",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 size  => 24)));
					break;

				case 'hobby':
					break;
				case 'job':
					break;
				case 'profile':
					$SYS_FORM["input"][] = array(title => $profile_columns[$column],
												 body  => get_form("textarea",
																   array(name  => $column,
																		 value => $val,
																		 gid => $gid,
																		 pmt_val   => $pmt[$column],
																		 cols  => 56, rows => 5)));
					break;
				case 'fav1':
				case 'fav2':
				case 'fav3':
					break;
				default:
					continue;
			}
		}
	}
	
	$SYS_FORM["action"] = 'input.php';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'location.href = \''. CONF_URLBASE."/index.php?uid=$dataset[uid]&gid=$dataset[gid]". '\';';

	$html = create_form(array(eid => $dataset['id']));

	$data = array(title   => 'プロフィールの編集',
				  icon    => 'profile',
				  content => $html);

	show_input($data);

	exit(0);
}

/************************************************************
 * 以下色々
 ************************************************************/
function create_icon() {
	require_once dirname(__FILE__). '/../../lib/class.upload.php';

	$image_file = $_FILES['thumb'];
	$handle = new Upload($image_file);

	$upload_dir       = CONF_BASEDIR. "/databox/profile/";
	$upload_orgin_dir = CONF_BASEDIR. "/databox/profile/b/";
	$upload_thumb_dir = CONF_BASEDIR. "/databox/profile/n/";
	$upload_petit_dir = CONF_BASEDIR. "/databox/profile/m/";

	if(!$handle->uploaded)
		return false;

	$file_ext  = $handle->file_src_name_ext;
	$tmp_filename = rand_str(). getmypid(). time();

	$handle->file_overwrite     = true;
	$handle->file_auto_rename   = false;
	$handle->file_src_name_body = $tmp_filename;
	$handle->Process($upload_dir);

	if (!$handle->processed)
		return false;

	$tmp_filepath = $upload_dir. $tmp_filename. '.'. $file_ext;

	$file_md5 = md5_file($tmp_filepath);
	$re_md5   = md5(rand_str(64). $file_md5. $tmp_filename);

	$new_filename = $re_md5. "." . $file_ext;

	$new_filepath = $upload_orgin_dir. $new_filename;
	//サムネイル画像
	exec('"'.CONF_CONVERT.'"'. " -geometry 128\\>x128\\> $tmp_filepath $new_filepath ");
	chmod($new_filepath, 0666);

	$thumb_filepath = $upload_thumb_dir. $new_filename;
	//サムネイル画像
	exec('"'.CONF_CONVERT.'"'. " -geometry 64\\>x64\\> $new_filepath $thumb_filepath ");
	chmod($thumb_filepath, 0666);

	$petit_filepath = $upload_petit_dir. $new_filename;
	//サムネイル画像
	exec('"'.CONF_CONVERT.'"'. " -geometry 32\\>x32\\> $new_filepath $petit_filepath ");
	chmod($petit_filepath, 0666);

	unlink($tmp_filepath);

	return $new_filename;
}

function form2datetime($prefix = null) {
	$d = array();
	$pat = '/^'. $prefix. '_([a-z])/i';

	foreach ($_REQUEST as $key => $value) {
		if (preg_match($pat, $key, $match)) {
			switch ($match[1]) {
				case 'Y':
				case 'M':
				case 'D':
				case 'h':
				case 'm':
				case 's':
					$d[$match[1]] = $value;
					break;
				default:
					break;
			}
		}
	}

	$utime = mktime(intval($d['h']), intval($d['m']), intval($d['s']),
					intval($d['M']), intval($d['D']), intval($d['Y']));

	if(!($d['Y'] and $d['D'] and $d['M']))return null;
	return date('Y-m-d H:i:s', $utime);
}


?>
