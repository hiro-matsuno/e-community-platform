<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require "common.php";

require_once dirname(__FILE__)."/../../includes/Exception.php";

class FileboxBlock {

	const DATABASE_FILEBOX_BLOCK = "filebox_block_setting";

	const UPLOAD_APPLET = 1;
	const LINK_FILEBOX = 2;
	const DEFAULT_NUM_OF_LIST = 10;

	private $id;
	private $block_id;
	private $setting;
	private $num_elements;

	public function __construct( $id=null, $block_id=null, $setting=null, $num_elements=null ) {

		$this->id = $id;

		if ( null !== $id ) {

			$result = mysql_exec( "select block_id, setting, num_elements"
								." from ".FileboxBlock::DATABASE_FILEBOX_BLOCK
								." where id=%d",
								mysql_num( $id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				$this->block_id = (int)$row["block_id"];
				$this->setting = (int)$row["setting"];
				$this->num_elements = (int)$row["num_elements"];

			} else {
				throw new DataNotFoundException("The block is not found.");
			}

		}

		if ( null !== $block_id ) { $this->block_id = $block_id; }
		if ( null !== $setting ) { $this->setting = $setting; }
		if ( null !== $num_elements ) { $this->num_elements = $num_elements; }

	}

	public function getId() { return $this->id; }
	public function getBlockId() { return $this->block_id; }
	public function getSetting() { return $this->setting; }
	public function getNumElements() { return $this->num_elements; }

	public function setBlockId( $block_id ) { $this->block_id = $block_id; }
	public function setSetting( $setting ) { $this->setting = $setting; }
	public function setNumElements( $num_elements ) { $this->num_elements = $num_elements; }

	public function regist() {

		if ( null === $this->id ) {

			if ( !mysql_exec( "insert into ".FileboxBlock::DATABASE_FILEBOX_BLOCK
							." ( block_id, setting, num_elements )"
							." values( %d, %d, %d )",
							mysql_num( $this->block_id ),
							mysql_num( $this->setting ),
							mysql_num( $this->num_elements ) ) ) {

				throw new SQLException( mysql_error() );

			}

			$this->id = mysql_insert_id();

		} else {

			if ( !mysql_exec( "update ".FileboxBlock::DATABASE_FILEBOX_BLOCK." set "
							." block_id=%d, setting=%d, num_elements=%d"
							." where id=%d",
							mysql_num( $this->block_id ),
							mysql_num( $this->setting ),
							mysql_num( $this->num_elements ),
							mysql_num( $this->id ) ) ) {

				throw new SQLException( mysql_error() );

			}

		}

	}

	public function delete() {

		if ( !mysql_exec( "delete from ".FileboxBlock::DATABASE_FILEBOX_BLOCK
						." where id=%d",
						mysql_num( $this->id ) ) ) {

			throw new SQLException( mysql_error() );

		}

	}

	public function equals( $obj ) {

		return ( $this->id === $obj->id
				and $this->block_id === $obj->block_id
				and $this->setting === $obj->setting
				and $this->num_elements === $obj->num_elements );

	}

	public function createInstanceFromBlockId( $block_id ) {

		$instance = new FileboxBlock();

		if ( null !== $block_id ) {

			$result = mysql_exec( "select id, block_id, setting, num_elements"
								." from ".FileboxBlock::DATABASE_FILEBOX_BLOCK
								." where block_id=%d",
								mysql_num( $block_id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				$instance->id = (int)$row["id"];
				$instance->block_id = (int)$row["block_id"];
				$instance->setting = (int)$row["setting"];
				$instance->num_elements = (int)$row["num_elements"];

				return $instance;

			}

		}

		return null;

	}

}


function mod_filebox_block_config($id)
{
	$menu   = array();
	$menu[] = array(title => '設定', url => "/modules/filebox/setting.php?pid=$id", inline => false);
	return $menu;
}

/** パーツ内HTML
 * index.php?module=モジュール名 でメインパーツ呼び出し
 * @param $id パーツの固有ID */
function mod_filebox_block( $id ) {

	EcomGlobal::addHeadJs( "/filebox.js" );
	EcomGlobal::addHeadJs( "/modules/filebox/block.js" );
	EcomGlobal::addHeadCss( "/modules/filebox/block.css" );
	EcomGlobal::addHeadCss( "./css/jquery.lightbox-0.5.css" );
	EcomGlobal::addHeadJs( "./js/jquery.lightbox-0.5.min.js" );
	EcomGlobal::addHeadCss( "./css/jquery.lightbox-0.5.css" );
	
	$jsCode = <<<__JS_CODE__

	var ListItemDirector = function( item, fileboxBuilder ) {

		var itemBuilder = new ListItemBuilder( item, fileboxBuilder );

		itemBuilder.readyLightbox();
		itemBuilder.readyDeleteTag();
		itemBuilder.readyPmt();

	};

	var FileboxDirector = function( selected_folder, block ) {

		var fileboxBuilder = new FileboxBuilder( selected_folder, block );

		fileboxBuilder.readyList();
		fileboxBuilder.readyDialog();

		block.find(".filebox_div").each( function() {

			new ListItemDirector( jQuery(this), fileboxBuilder );

		} );

	};

__JS_CODE__;

	EcomGlobal::addHeadJsRaw($jsCode);
	EcomGlobal::addJqueryReady( "new FileboxDirector(0,$(\".filebox_block[blk_id=$id]\"));\n" );

	$output = "";

	try {

		$me = User::getMe();

		$uid = ( null !== $me ) ? $me->getUid() : null;

		$applet = false;
		$link_filebox = false;
		$num_elements = 0;

		$fileboxBlock = FileboxBlock::createInstanceFromBlockId( $id );

		if ( null !== $fileboxBlock ) {

			$applet = $fileboxBlock->getSetting() & FileboxBlock::UPLOAD_APPLET;
			$link_filebox = $fileboxBlock->getSetting() & FileboxBlock::LINK_FILEBOX;
			$num_elements = $fileboxBlock->getNumElements();

		} else {

			$applet = FileboxBlock::UPLOAD_APPLET;
			$link_filebox = FileboxBlock::LINK_FILEBOX;
			$num_elements = FileboxBlock::DEFAULT_NUM_OF_LIST;

		}

		$block = new Block( $id );

		$page = new Page( $block->getPid() );

		$inGroupPage = ( 0 === $page->getUid() );
		
		$isOwner = false;

		if ( $inGroupPage ) {

			$isOwner = checkPermission( $page->getGid() );

		} else {

			$isOwner = checkPermission();

		}

		//	ファイル倉庫へのリンク
		if ( $link_filebox and $isOwner ) {

			if ( $inGroupPage ) {

				$output = '<div class="filebox_block_open_filebox">'
						.'<a href="/filebox.php?gid='.$page->getGid()
						.'&keepThis=true&TB_iframe=true&height=480&width=640" class="thickbox">'
						.'ファイル倉庫を開く'
						.'</a>'
						.'</div>';

			} else {

				$output = '<div class="filebox_block_open_filebox">'
						.'<a href="/filebox.php'
						.'?keepThis=true&TB_iframe=true&height=480&width=640" class="thickbox">'
						.'ファイル倉庫を開く'
						.'</a>'
						.'</div>';

			}

		}

		$session_id = session_id();

		if ( $isOwner and $applet ) {

			$output .= <<<__END__

    <div class="filebox_block_open_applet">
	<a>アップロードフォームを開く</a>
	</div>
	<div style="clear: both"></div>
	<div class="filebox_block_upload_applet"></div>
	<br>

__END__;

			EcomGlobal::addJqueryReady("new FileboxBlockBuilder"
									."( $id, \"$session_id\", "
									.( $inGroupPage ? $page->getGid() : "null" ).", "
									." \"{$_SERVER["SERVER_NAME"]}\" );");

		}

		$output .= '<div style="clear: both"></div>';

		if ( 0 < $num_elements ) {

			$output .= '<div class="filebox_block_recent_header" style="float: left">'
						.'最近の投稿';
			$output .= '<select class="filebox_list_select"'
						.' style="font-size:0.8em; float: right; background-color: transparent;">';
			$output .= '<option value="view_thumb">サムネイル</option>';
			$output .= '<option value="view_list">リスト</option>';
			$output .= '</select>';
			$output .= '</div>';

			$output .= '<div style="clear: both"></div>';

			if( !$inGroupPage )

				//	マイページの出力。ユーザのアップロードしたファイル一覧を出力。
				$output .= _block_of_mypage( $page->getUid(), $uid, $num_elements, $isOwner );

			else

				//	グループページの出力。グループに対し公開されたファイルの一覧を出力。
				$output .= _block_of_grouppage( $page->getGid(), $uid, $num_elements, $isOwner );

		}

	} catch ( Exception $e ) {

		$output .= "<p>内部エラーが発生しました</p>\n";

	}

	return "<div class=\"filebox_block\" blk_id=$id>".$output."</div>";

}

function _block_of_mypage( $uid, $visitor_uid, $num_elements, $isOwner ) {
	
	$result = mysql_exec( "select fd.id, fd.name, u.handle, fd.updymd from filebox_data as fd"
							." inner join owner as o on o.id=fd.id and o.uid=%d and o.gid=0"
							." left join element as e on e.id=fd.id"
							." left join user as u on o.uid=u.id"
							." where"
							.( $uid === $visitor_uid ? "" : " unit=0 and" )
							." 0=fd.trashed"
							." order by updymd desc limit %d",
							mysql_num( $uid ), mysql_num( $num_elements ) );
				
	if ( !$result ) 
	
		$output .= "<p>内部エラーが発生しました</p>\n".mysql_error();
	
	else {
		
		$output .= '<div class="filebox_block_listview">'."\n";
		$output .= _file_listup( $result, $isOwner and checkPermission() );
		$output .= '</div>'."\n";

	}
	
	return $output;
	
}

function _block_of_grouppage( $gid, $uid, $num_elements, $isOwner ) {
	
	$result = mysql_exec( "select fd.id, fd.name, u.handle, fd.updymd from filebox_data as fd"
							." inner join owner as o on o.id=fd.id and o.gid!=0 and o.gid=%d"
							." left join element as e on e.id=fd.id"
							." left join user as u on o.uid=u.id"
							." where"
							.( ( $uid and is_joined( $gid ) ) ? "" : " unit=0 and" )
							." 0=fd.trashed"
							." order by updymd desc limit %d",
							mysql_num( $gid ), mysql_num( $num_elements ) );
	
	if ( !$result ) 
	
		$output .= "<p>内部エラーが発生しました</p>\n".mysql_error();
	
	else {

		$output .= '<div class="filebox_block_listview">'."\n";
		$output .= _file_listup( $result, $isOwner and checkPermission($gid) );
		$output .= '</div>'."\n";

	}
		
	return $output;
	
}

function _file_listup( $result, $modEnabled ) {
	
	$output = '';
	
	if ( 0 < mysql_num_rows( $result ) ) {
		
		while( $row = mysql_fetch_array( $result ) ) {

			$fileData = new FileboxData( $row["id"] );

			$output .= makeListItem( $fileData, $modEnabled );

		}

	} else {
		
		$output .= '<div style="clear: both; height: 5px;"></div>'."\n";
		$output .= '<div style="margin: 0; padding: 0">'."\n";
		$output .= '<div style="padding: 3px;">'."\n";
		$output .= "<p>ファイルの投稿はありません</p>\n";
		$output .= '</div></div>'."\n";
		
	}
	
	return $output;
		
}

//	@TODO filebox.php の関数とほとんど同じ。統一して、レイアウト変更は CSS で対処したい.
function makeListItem( $fileData, $modEnabled ) {

	$data = '';
	
	$lightboxClass = '';

	{

		$contentType = mime_content_type_wrap( $fileData->getFilePath() );

		if ( preg_match( "/image/", $contentType ) ) {
			$lightboxClass = 'class="lightbox_a"';
		}

	}

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

	$handle = '';

	if ( 0 < $fileData->getUid() ) {

		$owner = new User( $fileData->getUid() );
		$handle = $owner->getHandle();

	}

	$permission = $fileData->getPermission();

	$mimeType = Mime::getInstance()->getMimeType( $fileData );
	
	$data .= '<div class="filebox_div" file_id="'.$fileData->getEid().'">'.

			'<div class="filebox_thumbbox">'.
			 '<div class="filebox_draggable filebox_title" file_id="'.$fileData->getEid().'"><b>'. $fileData->getName(). '</b></div>'.
			 '<div class="filebox_property">'.
			( $modEnabled ?
				'  <a class="thickbox" href="/filebox.php?act=edit&id='. $fileData->getEid().'&keepThis=true&TB_iframe=true&height=480&width=640">変更</a> | '
				.'  <a class="delete_tag" style="color: blue; cursor: pointer" >'.'削除'.'</a>'
			 : "" ).
			 '</div>'.
			 '<div style="clear: both"></div>'.
			 '<div class="filebox_thumbnail">'.
			 '<a '.$lightboxClass.' href="/fbox.php?eid='.$fileData->getEid().'" target="_blank">'.
			 '<img src="/fbox.php?eid='.$fileData->getEid().'&s=p" '.$sizeStr.' style="border: solid gray 1px">'.
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
			 '<div class="filebox_owner">'.$handle.'</div>'.
			 '<div class="filebox_type">'.$mimeType->getName().'</div>'.
			 '<div class="filebox_size" filesize="'.$fileData->getFilesize().'">'.get_sizestr( $fileData->getFilesize() ).'</div>'.
			 '<div class="filebox_date" filedate="'.strtotime( $fileData->getUpdymd() ).'">'.$fileData->getUpdymd().'</div>'.
			 '</div>'.
			 '<div style="clear: both;"></div>'.
			 '</div>'.

			 '<div class="filebox_listbox">'.
			 '<div class="filebox_draggable filebox_title" file_id="'.$fileData->getEid().'">'. $fileData->getName(). '</div>'.
			 '<div class="filebox_owner">'.$handle.'</div>'.
			 '<div class="filebox_size">'.get_sizestr( $fileData->getFilesize() ).'</div>'.
			 '<div class="filebox_date"filedate="'.strtotime( $fileData->getUpdymd() ).'">'.$fileData->getUpdymd().'</div>'.
			 '<div style="clear: both"></div>'.
			 '</div>'.

			 '</div>';

	return $data;

}

//	@TODO 以下は、filebox.phpからのコピペ。filebox API としてまとめるべき.
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

function get_sizestr( $size ) {

	if ( !$size ) return $size;

	$count = 0;

	while( 1024 < $size && 4 > $count ) {

		$size /= 102.4;
		$size = floor( $size );
		$size /= 10;
		++$count;

	}

	$unitstrs = array( 'Bytes', 'KB', 'MB', 'GB' );

	return $size.$unitstrs[$count];

}
?>