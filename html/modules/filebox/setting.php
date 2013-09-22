<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require "../../lib.php";
require "./common.php";

$uid = myuid();
$block_id = $_REQUEST['pid'];

if ( !$block_id ) show_error( "パラメータが不正です" );

switch ( $_POST['act'] ) {
	
	case 'regist':
		{
			$message = ( regist_setting( $block_id, $_POST['setting'], $_POST['num_elements'] ) ) 
				? "設定を変更しました" 
				: '<div>設定の変更に失敗しました</div>'
				.'<i style="font-size: 0.8em; color: #cfcfcf;">'.mysql_error().'</i>';

			//show_setting( $block_id, $message );

			$block = new Block( $block_id );
			$page = new Page( $block->getPid() );

			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$page->getUrl());

		}
		break;
		
	default:
		show_setting( $block_id );
		break;
		
}

function regist_setting( $block_id, $setting, $num_elements ) {

	if ( !is_owner( $block_id ) ) return false;
	
	$setting_value = 0;

	if ( is_array( $setting ) ) {
		foreach ( $setting as $value ) $setting_value |= $value;
	}
	
	$result = mysql_exec( "select s.id from block as b"
					." left join filebox_block_setting as s"
					." on b.id=s.block_id"
					." where b.id=%d",
					mysql_num( $block_id ) );
		
	if ( !$result ) return false;
	
	if ( !( $row = mysql_fetch_array( $result ) ) or !$row['id'] ) {
		
		$result = mysql_exec( "insert into filebox_block_setting ( block_id, setting, num_elements )"
								." values( %d, %d, %d )",
								mysql_num( $block_id ),
								mysql_num( $setting_value ),
								mysql_num( $num_elements ) );
								
		if( !$result ) return false;
		
	} else {
	
		$setting_id = $row['id'];
	
		$result = mysql_exec( "update filebox_block_setting set setting=$setting_value"
							.", num_elements=$num_elements"
							." where id=$setting_id",
							mysql_num( $setting_value ),
							mysql_num( $num_elements ),
							mysql_num( $setting_id ) );
							
		if ( !$result ) return false;
		
	}
	
	return true;
	
}

function show_setting( $block_id, $message=null ) {

	global $SYS_FORM;
	
	$html = "<div style=\"padding: 4px; font-size: 0.8em; color: #7fafff;\">$message</div>";
	
	$allow = MODULE_FILEBOX_ALLOW_VIEW;
	get_setting( $block_id, $setting, $num_elements );

	$attr = array(name => 'act', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'id', value => $block_id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array( name => 'setting', 
					option => array( MODULE_FILEBOX_LINK_FILEBOX => 'ファイル倉庫へのリンク', 
										MODULE_FILEBOX_UPLOAD_APPLET => 'アップロードフォーム' ), 
					value => array( MODULE_FILEBOX_LINK_FILEBOX => 0 != ($setting & MODULE_FILEBOX_LINK_FILEBOX),
									MODULE_FILEBOX_UPLOAD_APPLET => 0 != ($setting & MODULE_FILEBOX_UPLOAD_APPLET) ) );
	$SYS_FORM["input"][] = array(title => '表示する要素',
								 name  => 'setting',
								 body  => get_form("checkbox", $attr));
								 
	$SYS_FORM["input"][] = array(title => '「最近の投稿」表示件数',
								 name  => 'num_elements',
								 body  => get_form("num", array( 'name' => 'num_elements', 'value' => $num_elements )));
	
	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';
	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html .= create_form(array(pid => $block_id));

	$data = array(title   => "ファイル倉庫パーツの設定",
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);

}

function get_setting( $block_id, &$setting, &$num_elements ) {
	
	$result = mysql_exec( "select setting, num_elements from filebox_block_setting"
							." where block_id=$block_id",
							mysql_num( $block_id ) );
							
	if( !$result or !( $row = mysql_fetch_array( $result ) ) ) {
	
		$setting = MODULE_FILEBOX_UPLOAD_APPLET | MODULE_FILEBOX_LINK_FILEBOX;
		$num_elements = MODULE_FILEBOX_DEFAULT_NUM_OF_LIST;
		
		return false;
		
	} else {
		
		$setting = $row['setting'];
		$num_elements = $row['num_elements'];
		
		return true;
		
	}
	
}
?>
