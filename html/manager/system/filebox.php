<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require dirname(__FILE__). '/../../lib.php';

su_check();

switch ($_REQUEST["action"]) {
	case 'regist':
		$message = regist_data();
		break;
}

select_data( $message );


function regist_data() {
	
	if( !is_numeric( $_REQUEST['disk_quota'] ) || !is_numeric( $_REQUEST['user_quota'] ) )
		return '数値を入力してください';
	
	$disk_quota = $_REQUEST['disk_quota'] * 1024 * 1024;
	$user_quota = $_REQUEST['user_quota'] * 1024 * 1024;
	$youtube_user = $_REQUEST['youtube_user'];
	$youtube_passwd = $_REQUEST['youtube_passwd'];
	$group_level = $_REQUEST['group_level'];
	$user_level = $_REQUEST['user_level'];
	
	$result = mysql_query( "update filebox_config"
							." set disk_quota=$disk_quota, user_quota=$user_quota,"
							." youtube_user='$youtube_user', youtube_passwd='$youtube_passwd',"
							." group_level=$group_level, user_level=$user_level" );
	
	return ( $result ) ? '設定を変更しました。' : '設定の変更に失敗しました。'.mysql_error(); 
	
}

function select_data( $message ) {
	
	global $SYS_FORM;
	
	$disk_quota = 0;
	$user_quota = 0;
	$youtube_user = '';
	$youtube_passwd = '';
	$group_level = null;
	$user_level = null;
	
	$result = mysql_query( "select disk_quota, user_quota, youtube_user, youtube_passwd,"
						." group_level, user_level from filebox_config" );
	
	if( $result && $row = mysql_fetch_array( $result ) ) {
		
		if( is_numeric( $row['disk_quota'] ) ) {
			$disk_quota = $row['disk_quota'];
			//	小数点第一位までのMBfloat値にする。
			$disk_quota /= 1024;
			$disk_quota /= 102.4;
			$disk_quota = floor( $disk_quota );
			$disk_quota /= 10;
		}
		  
		if( is_numeric( $row['user_quota'] ) ) {
			$user_quota = $row['user_quota'];
			//	小数点第一位までのMBfloat値にする。
			$user_quota /= 1024;
			$user_quota /= 102.4;
			$user_quota = floor( $user_quota );
			$user_quota /= 10;
		}
		
		if ( isset( $row['youtube_user'] ) ) {
			$youtube_user = $row['youtube_user'];
		}
		
		if ( isset( $row['youtube_passwd'] ) ) {
			$youtube_passwd = $row['youtube_passwd'];
		}

		if ( isset( $row['group_level'] ) ) {
			$group_level = $row['group_level'];
		}

		if ( isset( $row['user_level'] ) ) {
			$user_level = $row['user_level'];
		}

		
	}
	
	$SYS_FORM["head"][] = $message;
	
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$SYS_FORM["input"][] = array(title => 'ディスクの最大容量',
								 name  => 'disk_quota',
								 body  => get_form("num", array( 'name' => 'disk_quota', 'ahtml' => ' MB', 'value' => $disk_quota )));
	
	$SYS_FORM["input"][] = array(title => 'ユーザの最大容量',
								 name  => 'user_quota',
								 body  => get_form("num", array( 'name' => 'user_quota', 'ahtml' => ' MB', 'value' => $user_quota )));

	//	@TODO Youtube 連携は保留.
//	$SYS_FORM["input"][] = array(title => 'Youtubeアカウント',
//								 name  => 'youtube_user',
//								 body  => get_form("text", array( 'name' => 'youtube_user', 'value' => $youtube_user )));
//
//	$SYS_FORM["input"][] = array(title => 'Youtubeパスワード',
//								 name  => 'youtube_passwd',
//								 body  => get_form("text", array( 'name' => 'youtube_passwd', 'value' => $youtube_passwd )));
	
	$option = array( Permission::USER_LEVEL_ADMIN => "グループ管理者",
					Permission::USER_LEVEL_POWERED => "グループ副管理者",
					Permission::USER_LEVEL_EDITOR => "編集者",
//					Permission::USER_LEVEL_DELETER => "デリーター",
					Permission::USER_LEVEL_AUTHORIZED => "一般利用者" );

	$SYS_FORM["input"][] = array(title => "グループページのフォルダを操作できるユーザレベル",
								 body  => get_form("select",
												   array(name  => "group_level",
														 value => $group_level,
														 option => $option )));
	
	$option = array( Permission::USER_LEVEL_ADMIN => "管理者",
					Permission::USER_LEVEL_POWERED => "パワーユーザー",
					Permission::USER_LEVEL_ANONYMOUS => "ログインユーザー" );

	$SYS_FORM["input"][] = array(title => "ファイル倉庫の操作を行えるユーザレベル",
								 body  => get_form("select",
												   array(name  => "user_level",
														 value => $user_level,
														 option => $option )));
	$SYS_FORM["action"] = 'filebox.php';
	$SYS_FORM["method"] = 'POST';
	
	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';
	
	
	$html = create_form(array(eid => 0));
	
	show_input( array(title   => 'ファイル倉庫の設定',
				  icon    => 'write',
				  content => $html ) );
				  
}

?>