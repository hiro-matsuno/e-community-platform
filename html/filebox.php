<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require dirname(__FILE__). '/config/mime.php';

define( 'FILEBOX_OK', 0 );
define( 'FILEBOX_ERROR_FATAL', -1 );
define( 'FILEBOX_ERROR_NOFILE', -2 );
define( 'FILEBOX_ERROR_TOO_LARGE_FILE', -3 );
define( 'FILEBOX_ERROR_EXCEEDS_DISK_QUOTA', -4 );
define( 'FILEBOX_ERROR_EXCEEDS_USER_QUOTA', -5 );
define( 'FILEBOX_ERROR_NOT_LOGGED_IN', -6 );


$result = (Object)array();
$result->code = FILEBOX_OK;

try {

	switch ( $_REQUEST["act"] ) {

	case "upload":
	case "regist":

		if ( isset( $_REQUEST["multi"] ) and "upload" != $_REQUEST["multi"] ) {

			filebox_multi_upload_form();
			return;

		}

		$message = registDataWrap();
		view( $message );
		return;

	case "update_only":
		$result->html = registData();
		break;
	
	case "upload_only":
		try {
			registData();
			print FILEBOX_OK;
		} catch ( Exception $e ) {
			print FILEBOX_ERROR_FATAL;
		}
		return;

	case "delete":
		deleteData();
		break;

	case "trash":
		$result->html = trashData();
		break;

	case "clear_trash":
		clearTrash();
		break;

	case "revert_trash":
		revertTrash( $result );
		break;

	case "move":
		$result->html = moveData();
		break;

	case "exam":
		checkSize( $result );
		print( $result->code );
		return;

	case "copy":
		$result->html = copyData();
		break;

	case "edit":
		setting_form();
		return;

	default:
		view();
		return;

	}

} catch ( PermissionDeniedException $e ) {

	$result->code = FILEBOX_ERROR_NOT_LOGGED_IN;
	$result->message = "その操作を行う権限がありません";

} catch ( Exception $e ) {

	$result->code = FILEBOX_ERROR_FATAL;
	$result->message = "失敗しました".EcomUtil::debugString( $e->__toString() );

}

print json_encode( $result );



function checkPermission( $gid=null ) {

	$me = User::getMe();

	if ( null === $me ) { return false; }

	$result = mysql_exec( "select group_level, user_level from filebox_config" );

	if ( !$result ) { throw new SQLException( mysql_error() ); }

	if ( false !== ( $row = mysql_fetch_array($result) ) ) {

		$user_level = (int)$row["user_level"];
		$group_level = (int)$row["group_level"];

		if ( null !== $gid ) {

			$group = new Group( $gid );

			return ( $user_level <= $me->getLevel()
					and $group_level <= $group->getUserLevel($me) );

		} else {

			return ( $user_level <= $me->getLevel() );

		}

		return ( $user_level <= $me->getLevel()
				and ( null === $gid or $group_level <= $grou->getUserLevel($me) ) );

	} else

		throw new Exception();

}

function registData() {

	$me = User::getMe();
	$gid = ( isset( $_REQUEST["gid"] ) ? $_REQUEST["gid"] 
			: ( isset( $_REQUEST["category"] ) ? $_REQUEST["category"]
			: null ) );

	if ( !checkPermission($gid) ) { throw new PermissionDeniedException(""); }

	$fileData = null;

	if ( isset( $_REQUEST["eid"] ) ) {

		$fileData = new FileboxData( $_REQUEST["eid"] );

		if ( $me->getUid() !== $fileData->getUid()
			and Permission::USER_LEVEL_EDITOR > $fileData->getElement()->getOwnerLevel( $me ) ) {
			throw new PermissionDeniedException("permission denied.");
		}

	} else {

		$fileData = new FileboxData();

		if ( null !== $gid ) {
			$fileData->setUid( $me->getUid() );
			$fileData->setGid( $gid );
		} else {
			$fileData->setUid( $me->getUid() );
			$fileData->setGid( 0 );
		}

	}

	if ( isset( $_REQUEST["name"] ) ) {
		$fileData->setName( $_REQUEST["name"] );
	}

	if ( isset( $_REQUEST["summary"] ) ) {
		$fileData->setSummary( $_REQUEST["summary"] );
	}

	if ( isset( $_FILES ) and 0 < count( $_FILES ) ) {

		//	新規登録の場合はアップロードファイルは必須.
		if ( null === $fileData->getId()
			//	新規登録ではなく、NO_FILE エラーの場合は何もせず先に進む.
			or UPLOAD_ERR_NO_FILE !== $_FILES["upload_file"]["error"] ) {

			if ( !isset( $_REQUEST["name"] ) ) {

				$name = "";

				if ( null === $gid ) {
					$name = changeNameIfDuplicated( $_FILES["upload_file"]["name"], null, $gid );
				} else {
					$name = changeNameIfDuplicated( $_FILES["upload_file"]["name"], $me->getUid(), null );
				}

				$fileData->setName( $name );
				$fileData->setOrgFilename( $_FILES["upload_file"]["name"] );

			}

			$fileData->setUpload( $_FILES["upload_file"] );

		}

	} else if ( null === $fileData->getId() ) {
		throw new Exception("ファイルが送信されませんでした");
	}

	if ( isset( $_REQUEST["unit"] ) ) {
		$fileData->setPermission( (int)$_REQUEST["unit"] );
	}

	$fileData->regist();

	return makeListItem( $fileData );

}

function registDataWrap() {

	$message = null;

	try {

		registData();

	} catch ( FileUploadError $e ) {

		switch ( $e->getErrorCode() ) {
		//   値: 0; エラーはなく、ファイルアップロードは成功しています。
		case UPLOAD_ERR_OK:
			break;

		//   値: 1; アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
		case UPLOAD_ERR_INI_SIZE:
	    //	値: 2; アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
		case UPLOAD_ERR_FORM_SIZE:
			$message = "ファイルサイズが大きすぎます.";
			break;

	    //	値: 4; ファイルはアップロードされませんでした。
		case UPLOAD_ERR_NO_FILE:
			$message = "ファイルを選択してください.";
			break;

		//	値: 3; アップロードされたファイルは一部のみしかアップロードされていません。
		case UPLOAD_ERR_PARTIAL:
	    //	値: 6; テンポラリフォルダがありません。PHP 4.3.10 と PHP 5.0.3 で導入されました。
		case UPLOAD_ERR_NO_TMP_DIR:
	    //	値: 7; ディスクへの書き込みに失敗しました。PHP 5.1.0 で導入されました。
		case UPLOAD_ERR_CANT_WRITE:
		//	値: 8; PHP の拡張モジュールがファイルのアップロードを中止しました。 どの拡張モジュールがファイルアップロードを中止させたのかを突き止めることはできません。 読み込まれている拡張モジュールの一覧を phpinfo() で取得すれば参考になるでしょう。 PHP 5.2.0 で導入されました。
		case UPLOAD_ERR_EXTENSION:
		default:
			$message = "アップロードに失敗しました.";
			break;

		}

	} catch ( PermissionDeniedException $e ) {

		$message = "その操作を行う権限がありません.";

	} catch ( Exception $e ) {

		$message = "失敗しました.";

	}

	return $message;

}

function deleteData() {

	$eid = ( isset( $_REQUEST["eid"] ) ? $_REQUEST["eid"] : null );

	if ( null !== $eid ) {

		$fileData = new FileboxData( $eid );
		$fileData->delete();

	} else {
		throw new InvalidArgumentException("id is null.");
	}

}

function trashData() {

	$eid = ( isset( $_REQUEST["eid"] ) ? $_REQUEST["eid"] : null );

	$me = User::getMe();

	if ( !checkPermission() ) { throw new PermissionDeniedException(""); }

	if ( null !== $eid ) {

		$fileData = new FileboxData( $eid );

		$fileData->setUid( $me->getUid() );

		if ( 0 !== $fileData->getGid()
			and !checkPermission($fileData->getGid()) ) {
			
			throw new PermissionDeniedException("");

		}

		$fileData->setTrashed( FileboxData::DATA_TRASHED );

		$fileData->regist();

		return makeListItem($fileData,true,true);

	} else {
		throw new InvalidArgumentException("id is null.");
	}

}

function clearTrash() {

	$me = User::getMe();
	if ( !checkPermission() ) { throw new PermissionDeniedException(""); }

	$fileDatas = FileboxData::getFileboxDatas( $me->getUid(), null, true );

	foreach ( $fileDatas as $fileData ) {
		$fileData->delete();
	}
	
}

function revertTrash( &$result ) {

	$me = User::getMe();

	$eid = ( isset( $_REQUEST["eid"] ) ? $_REQUEST["eid"] : null );
	
	$fileData = new FileboxData( $eid );

	$trashed = $fileData->getTrashed();

	$gid = $fileData->getGid();

	if ( !checkPermission( ( 0 < $gid ? $gid : null ) ) ) {
		throw new PermissionDeniedException("");
	}

	if ( $trashed != 0 ) {
		$fileData->setTrashed(0);
		$result->folder_id = $gid;
	}

	$fileData->regist();

	$result->html = makeListItem($fileData);

}

function copyData() {

	$me = User::getMe();

	$eid = ( isset( $_REQUEST["eid"] ) ? (int)$_REQUEST["eid"] : null );
	$gid = ( isset( $_REQUEST['folder_id'] ) ? (int)$_REQUEST['folder_id'] : null );

	if ( null === $eid ) { throw new Exception( "no file id is given." ); }

	if ( !checkPermission( $gid ) ) { throw new PermissionDeniedException(""); }

	$fileData = new FileboxData( $eid );

	$copyData = new FileboxData();

	$copyData->setFilename( $fileData->getFilename() );
	$copyData->setSummary( $fileData->getSummary() );
	$copyData->setFilesize( $fileData->getFilesize() );
	$copyData->setUid( $me->getUid() );

	if ( null !== $gid ) {

		$name = changeNameIfDuplicated( $fileData->getName(), null, $gid );
		$copyData->setName( $name );

		$copyData->setGid( $gid );

	} else {

		$me = User::getMe();

		if ( null === $me ) { throw new PermissionDeniedException("user is not logged in."); }

		$name = changeNameIfDuplicated( $fileData->getName(), $me->getUid(), null );
		$copyData->setName( $name );

		$copyData->setUid( $me->getUid() );

	}

	$copyData->setPermission( $fileData->getPermission() );

	$copyData->regist();

	return makeListItem($copyData);
	
}

function moveData() {

	$eid = ( isset( $_REQUEST["eid"] ) ? $_REQUEST["eid"] : null );
	$gid = ( isset( $_REQUEST["folder_id"] ) ) ? $_REQUEST["folder_id"] : null;

	if ( !checkPermission( $gid ) ) { throw new PermissionDeniedException(""); }

	if ( !$eid ) { throw new Exception( "no file id is given." ); }

	$fileData = new FileboxData( $_REQUEST["eid"] );

	$fileData->setTrashed(0);

	$fileData->setGid( $gid );

	$fileData->regist();

	return makeListItem($fileData);

}

function changeNameIfDuplicated( $name, $uid, $gid ) {
	
	while( true ) {

		$result = mysql_exec( "select f.id from filebox_data as f"
							." left join owner as o on f.id=o.id"
							." where f.name=".mysql_str( $name )
							.( null !== $uid ? " and o.uid=".mysql_num( $uid ) : "" )
							.( null !== $gid ? " and o.gid=".mysql_num( $gid ) : "" ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( 0 < mysql_num_rows( $result ) ) {

			$n = $name;
			$ext = '';

			if ( preg_match( '/^(.+?)(\.[^\.]+)$/', $name, $match ) ) {
				$n = $match[1];
				$ext = $match[2];
			}

			if ( preg_match( '/^(.+)?_(\d+)$/', $n, $match ) )
				$n = $match[1]."_".( intval( $match[2] ) + 1 );
			else
				$n .= "_1";

			$name = $n.$ext;

		} else

			break;

	}

	return $name;

}

function checkSize( &$result) {

	if ( !checkPermission() ) { throw new PermissionDeniedException(""); }

	$me = User::getMe();

	$size = $_REQUEST["size"];

	$fileManager = FileboxManager::getInstance();

	$max_filesize = min(return_bytes(ini_get('post_max_size')),
						return_bytes(ini_get('upload_max_filesize')));

	if ( $max_filesize < $size ) {

		$result->code = FILEBOX_ERROR_TOO_LARGE_FILE;
		$result->message = "アップロードできるファイルのサイズは"
						.get_sizestr( $max_filesize )."までです";

	} if ( !$fileManager->checkDiskQuota( $size ) ) {

		$result->code = FILEBOX_ERROR_EXCEEDS_DISK_QUOTA;

	} else if ( null !== $me and !$fileManager->checkUserQuota( $me, $size ) ) {

		$result->code = FILEBOX_ERROR_EXCEEDS_USER_QUOTA;
		
	} else {

		$result->code = FILEBOX_OK;

	}

}

function view( $message=null ) {

	$me = User::getMe();

	$gid = 0;

	if ( isset( $_REQUEST["gid"] ) ) {

		$gid = (int)$_REQUEST["gid"];

	} else if ( isset( $_REQUEST["eid"] ) ) {

		$fileData = new FileboxData( (int)$_REQUEST["eid"] );

		$gid = $fileData->getGid();

	}

	$contents=null;

	if ( null !== $me ) {

		EcomGlobal::addHeadJs( "./js/ui/ui.draggable.js" );
		EcomGlobal::addHeadJs( "./js/ui/ui.droppable.js" );
		EcomGlobal::addHeadJs( "./js/jquery.lazyload.js" );
		EcomGlobal::addHeadJs( "./js/jquery.lightbox-0.5.min.js" );
		EcomGlobal::addHeadCss( "./css/jquery.lightbox-0.5.css" );
		EcomGlobal::addHeadCss( "./filebox.css" );
		EcomGlobal::addHeadJs( "./filebox.js" );

		$jsCode = <<<__JS_CODE__

	var ListItemDirector = function( item, fileboxBuilder ) {

		var itemBuilder = new ListItemBuilder( item, fileboxBuilder );

		itemBuilder.readyLazy();
		itemBuilder.readyLightbox();
		itemBuilder.readyDraggable();
		itemBuilder.readyClearTag();
		itemBuilder.readyDeleteTag();
		itemBuilder.readyRevertTag();
		itemBuilder.readyPmt();

	};

	var FileboxDirector = function( selected_folder ) {

		var fileboxBuilder = new FileboxBuilder( selected_folder );

		fileboxBuilder.readyUpload();
		fileboxBuilder.readyPmt();
		fileboxBuilder.readyFolder();
		fileboxBuilder.readyDroppable();
		fileboxBuilder.readySort();
		fileboxBuilder.readyList();
		fileboxBuilder.readyDialog();

		jQuery(".filebox_div").each( function() {

			new ListItemDirector( jQuery(this), fileboxBuilder );

		} );

	};

__JS_CODE__;

		EcomGlobal::addHeadJsRaw($jsCode);
		EcomGlobal::addJqueryReady( "new FileboxDirector($gid);\n" );
 
		$tab_folder = null;
		$tab_page = null;

		//	タブページの作成。
		make_tabpage( $tab_folder, $tab_page, $me->getUid() );

		$contents = array('title'   => 'ファイル倉庫',
					  'icon'    => 'write',
					  'content' => make_page( $uid, $tab_folder, $tab_page, $message ) );

	} else {

		$contents = array('title'   => 'ファイル倉庫',
					  'icon'    => 'write',
					  'content' => "ログインしていません" );

	}

	show_dialog2($contents);

}

function make_tabpage ( &$tab_folder, &$tab_page, $uid ) {

	$i = 0;

	//	マイフォルダページの作成。
	{

		$title = 'マイフォルダ';
		$tab_folder  .= '<div class="filebox_droppable filebox_my_folder filebox_folder" folder_id="0"><a style="padding: 4px;">'
						.'<img src="/image/icons/001_20.png" height=12 />'. $title. '</a></div>'."\n";
		$tab_page .= '<div class="filebox_page filebox_my_page" folder_id="0">'
				. data_myfolder().
				'</div>'."\n";
		++$i;

		$tab_folder  .= '<hr>'."\n";

	}

	//	ユーザが所属するグループを全て表示。
	{

		$result = mysql_exec( "select gid, sitename as name from page as p"
								." inner join unit as u on p.gid=u.id"
								." where p.uid=0 and u.uid=%d"
								." union select gid, name from friend_group as f"
								." inner join unit as u on f.gid=u.id"
								." where u.uid=%d",
								mysql_num( $uid ), mysql_num( $uid ) );

		while ( $r = mysql_fetch_array( $result ) ) {

			$title = $r["title"] ? $r["title"] : '無題倉庫';
			$tab_folder  .= '<div class="filebox_droppable filebox_group_folder filebox_folder" folder_id="'.$r["gid"].'" tab_id="'.$i.'">'
							.'<a style="padding: 4px;"><img src="/image/icons/001_57.png" height=12 />'. $r['name']. '</a></div>'."\n";
			$tab_page .= '<div class="filebox_page filebox_group_page" folder_id="'.$r["gid"].'">'.
					data_groupfolder( $r["gid"] ).
					'</div>'."\n";

			++$i;

		}

	}

	//	ごみ箱の作成。
	{

		$tab_folder  .= '<hr>'."\n";

		$title = 'ごみ箱';
		$tab_folder  .= '<div class="filebox_droppable filebox_trash_folder filebox_folder" folder_id="1">'
						.'<a style="padding: 4px;"><img src="/image/icons/001_49.png" height=12 />'. $title. '</a></div>'."\n";
		$tab_page .= '<div class="filebox_page filebox_trash_page" folder_id="1">'
				. data_trashed().
				'</div>'."\n";
		++$i;

	}

}

function make_page( $uid, $tab_folder, $tab_page, $message ) {

	$pmt_form = pmt_form_filebox( $uid );

	if ( $message ) {

		$message = <<<__MESSAGE__

<script type="text/javascript">
//<!--
	showMessage( "$message", true );
//-->
</script>

__MESSAGE__;

	}

	$max_filesize = return_bytes( ini_get( "upload_max_filesize" ) );

	$add_hid = '';
	if (isset($_REQUEST['f'])) {
		$add_hid = '<input type="hidden" name="f" value="'. htmlesc($_REQUEST['f']). '">';
	}

	$html = <<<__CONTENTS__

$message

<div id="upload" title="ファイルアップロード">
<form action="/filebox.php?act=upload" method="POST" enctype="multipart/form-data">
$add_hid
<input type='hidden' name='MAX_FILE_SIZE' value="$max_filesize">
ファイル: <input type="file" name="upload_file" size="16" class="upload_input">
<div style="padding: 4px">
<input type="radio" name="unit" value="0" checked="checked">公開
<input type="radio" name="unit" value="2">非公開
</div>
<div style="padding: 4px">
<input type="submit" value="アップロード" class="upload_submit"><br/>
<i id="upload_to_folder" style="color: darkgray; font-size: 0.7em"></i>
</div>
<div style="padding: 8px">
<input type="submit" name="multi" value="一括選択・アップロードフォームを開く" class="upload_submit">
</div>
</form>
</div>

<div class="filebox_toolbar">
	<div style="padding: 12px;">
		<button id="upload_button" style="float: left; cursor: pointer;">ファイルアップロード</button>
		<select class="filebox_list_select" style="float: right;">
			<option value="view_thumb">サムネイル</option>
			<option value="view_list">リスト</option>
		</select>
	</div>
</div>

<div style="clear: both;"></div>

<table class="filebox_view">
	<tr>
		<td class="filebox_folderlist" height="360px"
			valign="top" width="25%" style="padding: 8px; border: solid 1px #cfcfcf;">
			<div style="height: 100%; overflow: auto;">
			${tab_folder}
			</div>
		</td>
		<td class="filebox_filelist" valign="top"
			width="75%" height="360px" style="border: solid 1px #cfcfcf;">
			<div class="filebox_folderproperty" style="width: 100%; height: 32px;">
				<div style="padding: 8px;">
					<div id="filebox_foldername"></div>
					<div style="clear:both"></div>
				</div>
			</div>
			<div class="filebox_filelisthead" style="width: 100%">
				<div class="filebox_sort" id="sort_title_button" style="width: 234px; float: left;">ファイル名</div>
				<div class="filebox_sort" id="sort_owner_button" style="width: 78px; float: left;">投稿者</div>
				<div class="filebox_sort" id="sort_size_button" style="width: 50px; float: left;">サイズ</div>
				<div class="filebox_sort" id="sort_date_button" style="width: 62px; float: left;">投稿日</div>
				<div style="clear: both;"></div>
			</div>
			<div class="filebox_filelistbody" style="height: 320px; overflow: auto;">
				${tab_page}
			</div>
		</td>
	</tr>
</table>

__CONTENTS__;

	return $html;

}


function setting_form($eid = null) {

	$max_filesize = return_bytes( ini_get( "upload_max_filesize" ) );

	if (!$eid) {
		$eid = intval($_REQUEST["id"]);
	}

	$fileData = new FileboxData( $eid );

	$name    = $fileData->getName();
	$summary = $fileData->getSummary();

	$sizeStr = '';

	//	縮小するが拡大はしない.
	if( !preg_match( "/^yt:/", $fileData->getFilename() ) ) {

		$imageSize = getimagesize( $fileData->getThumbPath( true ) );

		if ( $imageSize ) {

			if ( $imageSize[0] > $imageSize[1] ) {
				if ( 80 < $imageSize[0] ) { $sizeStr = 'width=80'; }
			} else {
				if ( 80 < $imageSize[1] ) { $sizeStr = 'height=80'; }
			}

		}

	}

//	$tag = get_tag_image($q, 't');
	$tag = '<a href="/fbox.php?eid='.$eid.'"'
			.'target="_blank"><img src="/fbox.php?eid='.$eid.'&s=p" border="0" '.$sizeStr.'></a>';

	$pmt = pmt_form($eid);

	$add_hid = '';
	if (isset($_REQUEST['f'])) {
		$add_hid = '<input type="hidden" name="f" value="'. htmlesc($_REQUEST['f']). '">';
		$add_q = 'f='. htmlesc($_REQUEST['f']);
	}

	$html =  <<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
	background-color: #ffffff;
	font-size: 0.9em;
}
.form_table th {
	width: 10em;
	background-color: #f4f4f4;
	padding: 4px;
	font-size: 0.9em;
	text-align: center;
}
.input_text {
	border: solid 1px #cccccc;
	font-size: 0.9em;
}
#dated > input {
	border: solid 1px #cccccc;
}
#upload_file {
	border: solid 1px #cccccc;
	background: #fff;
}
#summary {
	border: solid 1px #cccccc;
}
a { font-size: 0.9em; }
</style>
<div style="padding: 2px;">
<h3 style="padding: 3px 3px 20px 28px; background-image: url(/001_06.png); background-position: top left; background-repeat: no-repeat; font-size: 1.2em; border-bottom: solid 1px #5bace5;">ファイル情報の変更</h3>

<div style="margin: 10px auto; text-align: center; width: 85%;">
<div style="padding: 10px;"></div>
<form action="/filebox.php" id="input" method="POST" enctype="multipart/form-data">
<input type="hidden" name="act" value="regist">
	${add_hid}
<input type="hidden" name="eid" value="${eid}">

<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<th>タイトル</th>
<td><input type="text" name="name" class="input_text" size="36" value="${name}"></td>
</tr>
<tr>
<th>概要</th>
<td>
  <textarea name="summary" id="summary" cols="50" rows="4">${summary}</textarea>
</td>
</tr>
<tr>
<th>ファイルの変更</th>
<td>
${tag}<br>
<input type='hidden' name='MAX_FILE_SIZE' value="$max_filesize">
  <input type="file" name="upload_file" id="upload_file" size="24">
</td>
</tr>

</table>
<div style="padding: 10px;"><input type="submit" value="上記の内容で変更"> <input type="submit" value="キャンセル" onClick="location.href='/filebox.php?$add_q'; return false;"></div>

</form>
</div>
</div>
__HTML__;
;

$contents = array('title'   => 'ファイル情報の変更',
				  'icon'    => 'write',
				  'content' => $html);

show_dialog2($contents);

exit(0);
}

function add_category() {
	global $uid;

	$input_category = trim($_REQUEST["input_category"]);

	if ( !$uid || $input_category == '' ) {
		return;
	}

	$new_id = get_seqid();
	$q = mysql_exec("insert into filebox_setting (id, pid, title) values (%s, %s, %s);",
	mysql_num($new_id), 0, mysql_str( strip_tags( $input_category ) ));

	if (!$q) {
		die("missing upload...". mysql_error());
	}

	set_pmt(array(eid => $new_id, uid => $uid));//, unit => PMT_CLOSE));
}

function edit_category( $category_id ) {

	global $uid;

	$input_category = trim($_REQUEST["input_category"]);

	if ( !$uid || $input_category == '' ) {
		return;
	}

	$q = mysql_exec( "update filebox_setting set title=%s where id=$category_id",
						mysql_str( strip_tags( $input_category ) ));

	if (!$q) {
		die("missing upload...". mysql_error());
	}

}

function delete_category( $category_id ) {

	mysql_exec( "delete from filebox_setting where id=%d", mysql_num( $category_id ) )
		or die( mysql_error() );

}

function data_myfolder() {
	
	$me = User::getMe();

	$fileDatas = FileboxData::getFileboxDatas( $me->getUid(), 0 );

	if ( 0 < count( $fileDatas ) ) {

		$data = '';

		foreach ( $fileDatas as $fileData ) {
			$data .= makeListItem( $fileData,checkPermission() );
		}

		return $data;

	} else {

		return '';

	}

}

function data_groupfolder( $gid ) {

	$me = User::getMe();

	$fileDatas = FileboxData::getFileboxDatas( null, $gid );

	if ( 0 < count( $fileDatas ) ) {
		
		$data = '';

		foreach ( $fileDatas as $fileData ) {
			$data .= makeListItem( $fileData, checkPermission($gid) );
		}

		return $data;

	} else {

		return '';

	}

}

function data_trashed() {

	$me = User::getMe();

	$fileDatas = FileboxData::getFileboxDatas( $me->getUid(), null, true );

	if ( 0 < count( $fileDatas ) ) {

		$data = '';

		foreach ( $fileDatas as $fileData ) {
			$data .= makeListItem( $fileData, checkPermission(), true );
		}

		return $data;

	} else {

		return '';

	}

}

function makeListItem( $fileData, $modEnabled=true, $inTrash=false ) {

	$data = ''; $opt_tag = '';

	$mimeType = Mime::getInstance()->getMimeType( $fileData );

	if (isset($_REQUEST["f"])) {

		$elem = preg_replace('/[^_a-zA-Z0-9]/', '', $_REQUEST["f"]);

		if ( "youtube" == $mimeType->getType() ) {

			$opt_tag = '<div style="text-align: center">';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_youtube('".
			$elem. "', '". substr( $fileData->getFilename(), 3 ). "'); return false;\">".
					   '動画ファイルを貼付け</a>';
			$opt_tag .= '</div>';

		} else if ( "video" == substr( $mimeType->getType(), 0, 5 ) ) {

			$opt_tag .= '<div style="text-align: center; border: 1px solid darkgray;">';
			$opt_tag .= '<div style="background-color: lightblue;">イメージの貼り付け</div>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_video('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "', 'o', true); return false;\">".
					   '動画を埋め込み</a><br/>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_text('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\">".
					   'リンクを貼付け</a>';
			$opt_tag .= '</div>';

		} else if ( "image" == substr( $mimeType->getType(), 0, 5 ) ) {

			$opt_tag .= '<div style="text-align: center; border: 1px solid darkgray;">';
			$opt_tag .= '<div style="background-color: lightblue;">イメージの貼り付け</div>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "', 'o', true); return false;\">".
					   'オリジナル</a><br/>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "', 't', true); return false;\">".
					   '320px</a><br/>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "', 'p', true); return false;\">".
					   '160px</a>';
			$opt_tag .= '</div>';

		} else if ( "application/pdf" == substr( $mimeType->getType(), 0, 15 ) ) {

			$opt_tag .= '<div style="text-align: center; border: 1px solid darkgray;">';
			$opt_tag .= '<div style="background-color: lightblue;">PDFの貼り付け</div>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_pdf('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\">".
					   'PDFファイルを埋め込み</a><br/>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_pdfthumb('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "', 'p'); return false;\">".
					   'サムネイルを貼付け</a><br/>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_pdftext('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\">".
					   'リンクを貼付け</a>';
			$opt_tag .= '</div>';

			//				$opt_js = "onClick=\"filebox2fck('". $elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\"";

		} else {

			$opt_tag .= '<div style="text-align: center; border: 1px solid darkgray;">';
			$opt_tag .= '<div style="background-color: lightblue;">ファイルの貼り付け</div>';
			$opt_tag .= "<a href=\"#\" onClick=\"filebox2fck_text('".
			$elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\">".
					   'リンクを貼付け</a>';
			$opt_tag .= '</div>';

			//				$opt_js = "onClick=\"filebox2fck('". $elem. "', '". $fileData->getEid(). "', '". $fileData->getName(). "'); return false;\"";
		}

	}

	$lightboxClass = '';

	{

		$contentType = mime_content_type_wrap( $fileData->getFilePath() );

		if ( preg_match( "/image/", $contentType ) ) {
			$lightboxClass = 'class="lightbox_a"';
		}

	}

	$sizeStr = '';

	//	縮小するが拡大はしない.
	if( "youtube" != $mimeType->getType() ) {

		$imageSize = getimagesize( $fileData->getThumbPath( true ) );

		if ( $imageSize ) {

			if ( $imageSize[0] > $imageSize[1] ) {
				if ( 80 < $imageSize[0] ) { $sizeStr = 'width=80'; }
			} else {
				if ( 80 < $imageSize[1] ) { $sizeStr = 'height=80'; }
			}

		}

	}

	$add_q = '';
	if (isset($_REQUEST['f'])) {
		$add_q = '&f='. htmlesc($_REQUEST['f']);
	}

	$handle = '';

	if ( 0 < $fileData->getUid() ) {

		$owner = new User( $fileData->getUid() );
		$handle = $owner->getHandle();

	}

	$permission = $fileData->getPermission();

	$data .= '<div class="filebox_div" file_id="'.$fileData->getEid().'">'.

			'<div class="filebox_thumbbox">'.
			 '<div class="filebox_draggable filebox_title" file_id="'.$fileData->getEid().'">'. $fileData->getName(). '</div>'.
			 '<div class="filebox_property">'.
			( $modEnabled ?
			 ( !$inTrash ? '  <a href="/filebox.php?act=edit&id='. $fileData->getEid(). $add_q. '">変更</a> | ' : '' ).
//			 '  <a href="/filebox.php?act=delete&id='. $fileData->getEid(). $add_q. '">'.'削除'.'</a>'.
			 ( !$inTrash ? '  <a class="delete_tag" style="color: blue; cursor: pointer" >'.'削除'.'</a>' : '' ).
			 ( $inTrash
				? '  <a class="revert_tag" style="color: blue; cursor: pointer">'.'元に戻す'.'</a>'
				.' | <a class="clear_tag" style="color: blue; cursor: pointer">'.'完全削除'.'</a>'
				: '' )
			 : "" ).
			 '</div>'.
			 '<div style="clear: both"></div>'.
			 '<div class="filebox_thumbnail">'.
			 '<a '.$lightboxClass.' href="/fbox.php?eid='.$fileData->getEid().'" target="_blank">'.
			 '<img original="/fbox.php?eid='.$fileData->getEid().'&s=p" '.$sizeStr.' style="border: solid gray 1px">'.
			 '</a>'.
			 '</div>'.
			 '<div class="filebox_summary">'. ($fileData->getSummary() ? $fileData->getSummary() : '(説明はありません)'). '</div>'.
			 '<div class="filebox_misc">'.
			( $modEnabled
				? '<div class="filebox_pmtselector">'.
					( ( Permission::PMT_BROWSE_PUBLIC === $permission )
					? '<a class="filebox_pmt" pmt=0 file_id='.$fileData->getEid().' style="color: darkcyan; cursor: pointer;">公開</a>'
					: '<a class="filebox_pmt" pmt=2 file_id='.$fileData->getEid().' style="color: magenta; cursor: pointer;">非公開</a>' ).
					'</div>'
				: '' ).
			( !isset( $_REQUEST["f"] )
				 ? '<div class="filebox_owner">'.$handle.'</div>'.
					 '<div class="filebox_type">'.$mimeType->getName().'</div>'.
					 '<div class="filebox_size" filesize="'.$fileData->getFilesize().'">'.get_sizestr( $fileData->getFilesize() ).'</div>'.
					 '<div class="filebox_date" filedate="'.strtotime( $fileData->getUpdymd() ).'">'.$fileData->getUpdymd().'</div>'
				 : $opt_tag ).
			 '</div>'.
			 '<div style="clear: both;"></div>'.
			 '</div>'.

			 '<div class="filebox_listbox">'.
			 '<div class="filebox_draggable filebox_title" file_id="'.$fileData->getEid().'">'. $fileData->getName(). '<br/></div>'.
			 '<div class="filebox_owner">'.$handle.'<br/></div>'.
			 '<div class="filebox_size" filesize="'.$fileData->getFilesize().'">'.get_sizestr( $fileData->getFilesize() ).'<br/></div>'.
			 '<div class="filebox_date" filedate="'.strtotime( $fileData->getUpdymd() ).'">'.$fileData->getUpdymd().'<br/></div>'.
			 '<div style="clear: both"></div>'.
			 '</div>'.

			 '</div>';

	return $data;
	
}

function get_sizestr( $size ) {

	if ( !$size ) return $size;

	$count = 0;

	while( 1024 < $size && 4 > $count ) {

		$size /= 102.4;
		$size = floor( $size );
		$size /= 10;
		++$count;

	}

	$unitstrs = array( 'B', 'KB', 'MB', 'GB' );

	return $size.$unitstrs[$count];

}

function get_tag_image($param = array(), $thumb_type = 't') {
	global $JQUERY;

	$eid    = $param["id"];
	$pid    = $param["pid"];

	$thumb_type = $thumb_type ? $thumb_type : 't';

	if (is_owner($pid) == true) {
		//		$link   = '/u/m/o/'. $param["filename"];
		//		$thumb  = '/u/m/'. $thumb_type. '/'. $param["filename"];
		$link   = '/fbox.php?eid='. $eid;
		$thumb  = '/fbox.php?eid='. $eid. '&s='. $thumb_type;
	}
	else {
		$link   = '/fbox.php?eid='. $eid;
		$thumb  = '/fbox.php?eid='. $eid. '&s='. $thumb_type;
	}

//	$JQUERY['ready'][] = ' $(\'#img_'. $eid. '\').css(\'background-image\', \'url('. $thumb. ')\');';
//
//	return <<<__TAG___
//<a href="${link}" target="_blank" id="img_${eid}" style="display: block; width: 80px; height: 80px; text-align: center; color: #cfcfcf; font-size: 0.8em; background-position: center; background-repeat: no-repeat; "></a>
//
//__TAG___
//	;

	return <<<__TAG___
<a href="${link}" target="_blank"><img src="${thumb}" border="0" width="80px"></a>
__TAG___
	;
}

function get_tag_pdf($param = array()) {
	return <<<__TAG___
<a href="/fbox.php?eid=${param["id"]}" target="_blank"><img src="/image/icons/pdf.gif"></a>
__TAG___
	;
}

function get_tag_youtube( $yt_id ) {

	$fileManager = FileboxManager::getInstance();

	$youtube_user = $fileManager->getYoutubeUser();
	$youtube_passwd = $fileManager->getYoutubePasswd();

	if ( !$youtube_user or !$youtube_passwd ) return false;

	require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
	Zend_Loader::loadClass('Zend_Gdata_YouTube');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

	$authenticationURL= 'https://www.google.com/youtube/accounts/ClientLogin';
	$httpClient = Zend_Gdata_ClientLogin::getHttpClient(
										  $username = $youtube_user,
										  $password = $youtube_passwd,
										  $service = 'youtube',
										  $client = null,
										  $source = 'MySource', // a short string identifying your application
										  $loginToken = null,
										  $loginCaptcha = null,
										  $authenticationURL);

	$myDeveloperKey = 'AI39si6ONq8xLclISJ5q7_7pioyBUqj2vDoLRXWHwST2IS_Gf0JWb2ZPSlfuO6Brj4xPW_i8fKLLEx8AJ5FNsguDahjKb1lDVQ';
	$httpClient->setHeaders('X-GData-Key', "key=${myDeveloperKey}");
	$yt = new Zend_Gdata_YouTube($httpClient);

	$entry = $yt->getVideoEntry( $yt_id, null, true );

	if ( 0 < count( $thumbnails ) )

		return '<img width=80 src="'.$thumbnails[0]['url'].'"/>';

	else

		return '';

}

function get_tag_default($param = array()) {
	return <<<__TAG___
<a href="/fbox.php?eid=${param["id"]}" target="_blank">ダウンロード</a>
__TAG___
	;
}

function create_filebox($param) {
	$uid   = $param["uid"];
	$gid   = $param["gid"];
	$title = $param["title"];

	$new_id = get_seqid();

	$q = mysql_exec("insert into filebox_setting (id, pid, title) values (%s, %s, %s);",
					mysql_num($new_id),
					mysql_num($new_id),
					mysql_str( strip_tags( $title ) ));

	if (!$q) {
		return false;
	}

	// 初期作成時は非公開
	set_pmt(array(eid => $new_id, gid => $gid, unit => PMT_CLOSE));

	return $new_id;
}

function filebox_multi_upload_form(){
	$max_filesize = min(return_bytes(ini_get('post_max_size')),
						return_bytes(ini_get('upload_max_filesize')));
	$sid = session_id();
	$gid = $_REQUEST['gid'];
	$url = urlencode(CONF_URLBASE.'/filebox.php');

	$title_text = "マイフォルダに";

	if ( $gid ) {

		$page = Page::createInstanceFromGid($gid);
		$title_text = "「{$page->getSitename()}」のグループフォルダに";
		
	}

	$add_q = '';
	if (isset($_REQUEST['f'])) {
		$add_q = '&f='. htmlesc($_REQUEST['f']);
	}

	$html = <<<__HTML__
選択されたファイルを{$title_text}アップロードします。<br>
<div align="right"><a href="?gid=$gid$add_q">ファイル倉庫へ戻る</a></div>
<hr>
<div id='progress'></div>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="600" height="260" id="aexternal" align="middle">
<param name="allowScriptAccess" value="always" />
<param name="movie" value="multiup.swf" />
<param name="bgcolor" value="#ffffff" />
<param name=flashvars value="url=$url&category=$gid&sid=$sid&max_filesize=${max_filesize}" />
<embed src="multiup.swf" quality="high" bgcolor="#ffffff" width="600" height="260"
name="aexternal" align="middle" allowScriptAccess="always"
type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"
flashvars='url=$url&category=$gid&sid=$sid&max_filesize=${max_filesize}'/>
</object>
__HTML__;
show_dialog2(array('title'   => 'ファイル倉庫',
			  		'icon'    => 'write',
			  		'content' => $html));
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

function pmt_form_filebox( $uid, $gid=null ) {

	global $COMUNI, $JQUERY;

	$current_pmt = get_pmt($eid);

	if (!$COMUNI["__pmt_num"]) {
		$COMUNI["__pmt_num"] = 0;
	}

	$uid = $COMUNI["uid"];
	$num = $COMUNI["__pmt_num"];

	//	グループ
	$groups = mysql_exec( "select gid, sitename from page as p"
							." left join unit as u on p.gid=u.id"
							." where p.enable=1 and u.uid=%d",
							mysql_num( $uid ) );

	// 友達の友達
	$fx = mysql_uniq("select * from friend_extra where uid = %s",
					 mysql_num($uid));

	// 友達
	$f = mysql_exec("select * from friend_user where owner = %s",
					mysql_num($uid));

	$friends = array();
	while ($r = mysql_fetch_array($f)) {
		if ($r["gid"] == $r["pid"]) {
			$friend_all = $r["gid"];
		}
		else {
			$friends[] = array(gid => $r["gid"], name => $r["name"]);
		}
	}

	if ($current_pmt == $fx["gid"]) {
		$checked_id = $current_pmt. ':radio';
		$opt_ready_code = "\$('#pmt_${num}_sub').hide();";
	}
	else if ($current_pmt > 2) {
		$checked_id = 'sub_'. $current_pmt. ':checkbox';
		$opt_ready_code = "\$('#pmt_${num}_3:radio').attr('checked', true);";
	}
	else {
		$checked_id = $current_pmt. ':radio';
		$opt_ready_code = "\$('#pmt_${num}_sub').hide();";
	}

	$JQUERY["ready"][] = <<<__PMT_FORM__
/* pmt_ready num = ${num} */
//	\$("#pmt_${num}_${checked_id}").attr('checked', true);
	${opt_ready_code}
	\$('#pmt_${num}_0').click(function() { \$('#pmt_${num}_sub0').hide('fast'); \$('#pmt_${num}_sub1').hide('fast'); });
	\$('#pmt_${num}_1').click(function() { \$('#pmt_${num}_sub0').hide('fast'); \$('#pmt_${num}_sub1').hide('fast'); });
	\$('#pmt_${num}_2').click(function() { \$('#pmt_${num}_sub0').hide('fast'); \$('#pmt_${num}_sub1').hide('fast'); });
	\$('#pmt_${num}_3').click(function() { \$('#pmt_${num}_sub0').show('fast'); \$('#pmt_${num}_sub1').hide('fast'); });
	\$('#pmt_${num}_4').click(function() { \$('#pmt_${num}_sub1').show('fast'); \$('#pmt_${num}_sub0').hide('fast'); });
	\$('#pmt_${num}_2').click();
	\$('#pmt_${num}_2').attr( 'checked', true );

	\$('#pmt_${num}_sub_${friend_all}').click(function() {
		if (\$("#pmt_${num}_sub_${friend_all}:checkbox").attr('checked') == true) {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('checked', false);
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', true);
		}
		else {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', false);
		}
	});
/* pmt_ready num = ${num} */
__PMT_FORM__;

	$form = <<<__PMT_FORM__
<div id="pmt_${num}_div">
<input type="radio" name="pmt_${num}" value="2" id="pmt_${num}_2">
  <label for="pmt_${num}_2">非公開 (自分だけ)</label><br>
<input type="radio" name="pmt_${num}" value="1" id="pmt_${num}_1">
  <label for="pmt_${num}_1">登録ユーザーのみ</label><br>
<input type="radio" name="pmt_${num}" value="0" id="pmt_${num}_0">
  <label for="pmt_${num}_0">インターネット</label><br>

<input type="radio" name="pmt_${num}" value="3" id="pmt_${num}_3">
  <label for="pmt_${num}_3">グループから選択</label><br>
<div id="pmt_${num}_sub0" style="padding-left: 2em;">
  <div>
__PMT_FORM__;

	if ( 0 < mysql_num_rows( $groups ) ) {

		$form .= "<select name=\"pmt_${num}_sub\" size=\"1\">";

		while ( $row = mysql_fetch_array( $groups ) ) {
			$form .= "<option value=\"".$row['gid']."\">".$row['sitename']."</option>";
		}

		$form .= "</select>";

	}

	$form .= <<<__PMT_FORM__
  </div>

</div>

<input type="radio" name="pmt_${num}" value="3" id="pmt_${num}_4">
  <label for="pmt_${num}_4">フレンドリストから選択</label><br>
<div id="pmt_${num}_sub1" style="padding-left: 2em;">
  <input type="checkbox" name="pmt_${num}_sub" value="${friend_all}" id="pmt_${num}_sub_${friend_all}">
    <label for="pmt_${num}_sub_${friend_all}">フレンドリスト全員</label><br>
  <div id="pmt_0_sub_${friend_all}_c">
__PMT_FORM__;

	$i = 1;
	foreach ($friends as $f) {
		$c = '';
		if ($current_pmt == $f["gid"]) {
			$c = ' checked';
		}
		$form .= "  <input type=\"checkbox\" name=\"pmt_${num}_sub\" value=\"". $f["gid"]. "\" id=\"pmt_${num}_sub_${i}\"${c}>";
		$form .= "    <label for=\"pmt_${num}_sub_${i}\">". $f["name"]. "</label><br>";
		$i++;
	}

	$form .= <<<__PMT_FORM__
  </div>

</div>
<!--
<input type="radio" name="pmt_${num}" value="${fx["gid"]}" id="pmt_${num}_${fx["gid"]}">
  <label for="pmt_${num}_${fx["gid"]}">フレンドリストつながり全員</label><br>
-->
</div>
<!-- /pmt_form -->
__PMT_FORM__;

	$COMUNI["__pmt_num"]++;

	return $form;

}
?>