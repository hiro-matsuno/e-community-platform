<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/Element.php";
require_once dirname(__FILE__)."/FileboxManager.php";

/**
 * Description of FileboxData
 *
 * @author ikeda
 */
class FileboxData implements MySqlRecord {

	const DATABASE = "filebox_data";

	const WORK_DIR = 'w';
	const ORIGIN_DIR = 'o';
	const THUMB_DIR = 't';
	const PETIT_DIR = 'p';

	const ICON_TEXT = '/image/icons/icon_txt.gif';
	const ICON_DOC = '/image/icons/icon_doc.gif';
	const ICON_XLS = '/image/icons/icon_xls.gif';
	const ICON_PDF = '/image/icons/icon_pdf.gif';

	const DATA_TRASHED = 1;

	static private $IGNORE_EXT = array('.exe','.com','.bat','.vbs');

	private $r_id;
	private $r_pid;
	private $r_name;
	private $r_filename;
	private $r_summary;
	private $r_updymd;
	private $r_filesize;
	private $r_trashed;
	private $r_org_filename;

	private $element;

	private $uploadFile;
	

	public function __construct( $data=null, $connection=null ) {

		if ( null !== $data ) {

			$className = get_class( $this );
			$db_vars = call_user_func( array( $className, "getMemberNames" ), $className );

			if ( !is_array( $data ) ) {

				if ( is_string( $data ) ) { $data = MySqlUtil::decorateText( $data ); }

				$stat = new MySqlPlaneStatement( "select f.id, pid, name, filename, summary,"
												." updymd, filesize, trashed, org_filename,"
												." e.unit, o.uid, o.gid"
												." from ".FileboxData::DATABASE." as f"
												." left join ".Element::DATABASE." as e"
												." on f.id=e.id"
												." left join ".Owner::DATABASE." as o"
												." on f.id=o.id"
												." where f.id=".$data,
												$connection );

				$data = mysql_fetch_assoc( $stat->exec()->getResult() );

				if ( false === $data ) { throw new DataNotFoundException(); }

			}

			foreach ( $db_vars as $key ) {

				if ( !isset( $data[$key] ) ) { continue; }
				$value = $data[$key];

				$methodName = MySqlUtil::getSetterName($className,$key);
				$this->$methodName( $value );

			}

			$this->element = new Element( $data );

		} else {

			$this->element = new Element();

		}

	}

	
	public function getId() { return $this->r_id; }
	public function getPid() { return $this->r_pid; }
	public function getName() { return $this->r_name; }
	public function getFilename() { return $this->r_filename; }
	public function getsummary() { return $this->r_summary; }
	public function getUpdymd() { return $this->r_updymd; }
	public function getFilesize() { return $this->r_filesize; }
	public function getTrashed() { return $this->r_trashed; }
	public function getOrgFilename() { return $this->r_org_filename; }

	public function getElement() { return $this->element; }

	/**
	 * @deprecated
	 * @return Number
	 */
	public function getEid() { return $this->getId(); }

	public function getUid() { return $this->element->getUid(); }
	public function getGid() { return $this->element->getGid(); }

	public function getPermission() { return $this->element->getPermission(); }


	protected function setId( $id ) { $this->r_id = (int)$id; }
	public function setPid( $pid ) { $this->r_pid = (int)$pid; }
	public function setName( $name ) { $this->r_name = $name; }
	public function setFilename( $filename ) { $this->r_filename = $filename; }
	public function setSummary( $summary ) { $this->r_summary = $summary; }
	public function setFilesize( $filesize ) { $this->r_filesize = (int)$filesize; }
	public function setTrashed( $trashed ) { $this->r_trashed = (int)$trashed; }
	public function setUpdymd( $updymd ) { $this->r_updymd = $updymd; }
	public function setOrgFilename( $org_filename ) { $this->r_org_filename = $org_filename; }

	public function setUid( $uid ) { return $this->element->setUid( $uid ); }
	public function setGid( $gid ) { return $this->element->setGid( $gid ); }

	public function setPermission( $permission ) {
		return $this->element->setPermission( $permission );
	}

	public function setUpload( $uploadFile ) { $this->uploadFile = $uploadFile; }

	static public function getMemberNames() {

		return array( "id", "pid", "name", "filename", "summary", "updymd",
					"filesize", "trashed", "org_filename" );
		
	}
	
	static public function getTableName() { return FileboxData::DATABASE; }
	static public function getKeyName() { return "id"; }


	/**
	 * レコードをテーブルに登録する.
	 * getKeyName で与えられるキー値で検索し、すでに登録されていれば update,
	 * 未登録なら insert を行なう.
	 *
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function regist( $connection=null ) {

		if ( null === $this->r_id ) { $this->setId( get_seqid() ); }

		if ( null !== $this->uploadFile ) {

			$this->upload( $this->uploadFile );
			$this->uploadFile = null;

		}

		$stat = new MySqlRegistStatement( $this, $connection );
		$stat->exec();

		$this->element->setId( $this->r_id );
		$this->element->regist();

		$this->setUpdymd( date("Y-m-d H:i:s") );

	}

	/**
	 * このレコードの削除を行なう.
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function delete( $connection=null ) {

		$stat = new MySqlDeleteStatement( $this, $connection );
		$stat->exec();

		$this->element->delete();

		$this->deleteFileIfNotRefrected( $this->r_filename );

	}

	private function deleteFileIfNotRefrected( $filename ) {

		if ( null === $filename ) { return; }

		$result = mysql_exec( "select id from ".FileboxData::DATABASE
							." where filename=%s",
							mysql_str( $filename ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( 0 === mysql_num_rows($result) ) {

			if ( preg_match( "/^yt:(.*)/", $filename, $match ) ) {

				$this->deleteFromYoutube( $match[1] );

			} else {

				$subDir = substr( $filename, 0, 1 );

				$path = CONF_BASEDIR.CONF_FILEBOX_DIR.FileboxData::ORIGIN_DIR
						."/".$subDir."/".$filename;

				if ( file_exists( $path ) ) { unlink( $path ); }

			}

		}

	}

	/**
	 * オブジェクトの比較.
	 * テーブルレコード変数のみ、型も含めて等しいかどうかを返す.
	 * @param Object $obj
	 * @return boolean
	 */
	public function equals( $obj ) {

		$className = get_class( $this );
		$db_vars = call_user_func( array( $className, "getMemberNames" ), $className );

		foreach ( $db_vars as $var ) {

			$getter = MySqlUtil::getGetterName( $className, $var );

			if ( $this->$getter() !== $obj->$getter() ) {
				return false;
			}

		}

		return $this->element->equals( $obj->element );

	}

	public function getFilePath() {

		$subDir = substr( $this->r_filename, 0, 1 );

		return CONF_BASEDIR.CONF_FILEBOX_DIR.FileboxData::ORIGIN_DIR
				."/".$subDir."/".$this->r_filename;

	}

	public function getThumbPath( $isPetit=false ) {

		$subDir = substr( $this->r_filename, 0, 1 );

		$filename = $this->r_filename;
		$orig_filename = $filename;
		if ( !preg_match( "/\..+$/", $filename ) ) { $filename .= ".jpg"; }
		$filename = preg_replace( "/\..+$/", ".jpg", $filename );

		$path = CONF_BASEDIR.CONF_FILEBOX_DIR.( $isPetit ? FileboxData::PETIT_DIR : FileboxData::THUMB_DIR )
				."/".$subDir."/".$filename;

		if ( !file_exists( $path )
			and !file_exists( ( $path = dirname( $path )."/".$orig_filename ) ) ) {

			$mimeType = Mime::getInstance()->getMimeType( $this );
			$path = CONF_BASEDIR.$mimeType->getIcon();

		}

		return $path;

	}

	private function upload( $file ) {

		if ( isset( $file["error"] ) and 0 !== $file["error"] ) {
			throw new FileUploadError( $file["error"], "upload is failed." );
		}

		if ( 0 == $file["size"] ) { return; }

		if ( preg_match( "/\.[^\.]+$/", $file["name"], $match ) ) {

			$ext = $match[0];
			if ( array_search( $ext, FileboxData::$IGNORE_EXT ) ) {
				throw new Exception( "Invalid file type." );
			}

		}

		$oldFilename = $this->r_filename;

		FileboxData::makeDirectory();

		$tempFile = $file['tmp_name'];
		$contentType = mime_content_type_wrap( $tempFile );

		if ( null === $this->r_name ) { $this->setName( $file["name"] ); }
		$this->setFilesize( $file["size"] );

		//	@TODO Youtube 連携はとりあえず保留.
		if ( false ) {	//preg_match( "/video/", $contentType ) ) {
			$this->uploadToYoutube( $tempFile );
		} else {
			$this->uploadToLocalFolder( $this->r_name, $tempFile );
		}

		$this->deleteFileIfNotRefrected( $oldFilename );
		
	}

	private function uploadToLocalFolder( $name, $temp_file ) {

		$ext = '';
		if ( preg_match( "/\.[^\.]+$/", $name, $match ) ) { $ext = $match[0]; }

		$hashName = md5( getmypid().time() );
		
		$this->setFilename( $hashName.$ext );

		$dir = CONF_BASEDIR.CONF_FILEBOX_DIR.FileboxData::ORIGIN_DIR
				."/".substr( $this->r_filename, 0, 1 );

		if ( !file_exists( $dir ) ) {
			mkdir( $dir );
			chmod( $dir, 0777 );
       	}

		$path = $dir."/".$this->r_filename;

		if ( file_exists( $path )
			or !move_uploaded_file( $temp_file, $path ) ) {
			throw new Exception( "failed to upload." );
		}

		$this->makeThumb();

	}

	private function makeThumb() {

		$filePath = $this->getFilePath();
		
		$contentType = mime_content_type_wrap( $filePath );

		$filename = $this->r_filename;
		if ( !preg_match( "/\..+$/", $filename ) ) { $filename .= ".jpg"; }
		$filename = preg_replace( "/\..+$/", ".jpg", $filename );

		$subDir = substr( $this->r_filename, 0, 1 );
		$thumbDir = CONF_BASEDIR.CONF_FILEBOX_DIR.FileboxData::THUMB_DIR
					."/".$subDir;

		if ( !file_exists( $thumbDir ) ) {
			mkdir( $thumbDir );
			chmod( $thumbDir, 0777 );
		}

		$petitDir = CONF_BASEDIR.CONF_FILEBOX_DIR.FileboxData::PETIT_DIR
					."/".$subDir;

		if ( !file_exists( $petitDir ) ) {
			mkdir( $petitDir );
			chmod( $petitDir, 0777 );
		}

		if ( preg_match( "/image/", $contentType ) ) {

			//	320x320
			exec('"'.CONF_CONVERT.'"'." -geometry 320\\>x320\\>"
					." $filePath $thumbDir/$filename");
			chmod("$thumbDir/$filename", 0666);

			//160x160
			exec('"'.CONF_CONVERT.'"'." -geometry 160\\>x160\\>"
					." $filePath $petitDir/$filename");
			chmod("$petitDir/$filename", 0666);

		} else if ( preg_match( "/pdf/", $contentType ) ) {

			//	320x320
			exec('"'.CONF_CONVERT.'"'." -geometry 320\\>x320\\>"
					." {$filePath}[0] $thumbDir/$filename");
			chmod("$thumbDir/$filename", 0666);

			//	160x160
			exec('"'.CONF_CONVERT.'"'." -geometry 160\\>x160\\>"
					." {$filePath}[0] $petitDir/$filename");
			chmod("$thumbDir/$filename", 0666);

		}

	}

	private function uploadToYoutube( $temp_file ) {

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

		// create a new Zend_Gdata_YouTube_VideoEntry object
		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

		// create a new Zend_Gdata_App_MediaFileSource object
		$filesource = $yt->newMediaFileSource($temp_file);
		$mime = mime_content_type_wrap( $temp_file );
		if ( "" == $mime ) { $mime = "video/x-msvideo"; }
		$filesource->setContentType( $mime );
		// set slug header
		$filesource->setSlug($temp_file);

		// add the filesource to the video entry
		$myVideoEntry->setMediaSource($filesource);

		// create a new Zend_Gdata_YouTube_MediaGroup object
		$mediaGroup = $yt->newMediaGroup();
		$mediaGroup->title = $yt->newMediaTitle()->setText('My Test Movie');
		$mediaGroup->description = $yt->newMediaDescription()->setText('My description');

		// the category must be a valid YouTube category
		// optionally set some developer tags (see Searching by Developer Tags for more details)
		$mediaGroup->category = array(
		  $yt->newMediaCategory()->setText('Autos')->setScheme('http://gdata.youtube.com/schemas/2007/categories.cat'),
		  $yt->newMediaCategory()->setText('mydevelopertag')->setScheme('http://gdata.youtube.com/schemas/2007/developertags.cat'),
		  $yt->newMediaCategory()->setText('anotherdevelopertag')->setScheme('http://gdata.youtube.com/schemas/2007/developertags.cat')
		  );

		// set keywords
		$mediaGroup->keywords = $yt->newMediaKeywords()->setText('cars, funny');
		$myVideoEntry->mediaGroup = $mediaGroup;

		// set video location
		$yt->registerPackage('Zend_Gdata_Geo');
		$yt->registerPackage('Zend_Gdata_Geo_Extension');
		$where = $yt->newGeoRssWhere();
		$position = $yt->newGmlPos('37.0 -122.0');
		$where->point = $yt->newGmlPoint($position);
		$myVideoEntry->setWhere($where);


		// upload URL for the currently authenticated user
		$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/users/default/uploads';

		try {
		  $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
		} catch (Zend_Gdata_App_Exception $e) {
			echo $e->getMessage();
		}

		$id = $newEntry->getId();

		if ( preg_match( '/.*\/(.*)$/', $id->getText(), $matche ) ) {

			$this->setFilename( "yt:".$matche[1] );
			return true;

		} else
			return false;

	}

	public function deleteFromYoutube( $id ) {

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

		$entry = $yt->getVideoEntry( $id, null, true );

		$yt->delete( $entry );

	}

	public function isVisible( $user ) {

		if ( null === $user ) {

			return Permission::PMT_BROWSE_PUBLIC == $this->getPermission();

		}

		if ( 0 !== $this->getGid() ) {

			$group = new Group( $this->getGid() );

			return ( $group->hasUser( $user )
					or Permission::PMT_BROWSE_PUBLIC == $this->getPermission() );

		} else {

			return ( ( $user->getUid() === $this->getUid() )
					or ( Permission::PMT_BROWSE_PUBLIC == $this->getPermission() ) );

		}

	}

	public static function getFileboxDatas( $uid=null, $gid=null, $trashed=false ) {

		$query = "select f.id, f.pid, f.name, f.filename, f.summary, f.updymd, f.filesize,"
							." f.trashed, f.org_filename,"
							." o.uid, o.gid,"
							." e.unit"
							." from ".FileboxData::DATABASE." as f"
							." left join ".Owner::DATABASE." as o on o.id=f.id"
							." left join ".Element::DATABASE." as e on e.id=f.id"
							." where"
							.( null !== $uid ? " o.uid=".mysql_num( $uid ) : "" )
							.( ( null !== $uid and null !== $gid ) ? " and" : "" )
							.( null !== $gid ? " o.gid=".mysql_num( $gid ) : "" )
							.( ( null !== $uid or null !== $gid ) ? " and" : "" )
							.( $trashed ? " f.trashed!=0" : " f.trashed=0" )
							." order by f.updymd desc";

		$stat = new MySqlSelectStatement( "FileboxData", $query );
		return $stat->exec()->getDatas();

	}

	///	@TODO インストール処理に移行するべき内容.
	public static function makeDirectory() {

		// 作業用ディレクトリ
		$up_dir_tmp = CONF_FILEBOX_DIR.FileboxData::WORK_DIR;
		if (!file_exists($up_dir_tmp)) {
			mkdir($up_dir_tmp);
			chmod($up_dir_tmp, 0777);
		}
		// オリジナル画像
		$up_dir_orgin = CONF_FILEBOX_DIR.FileboxData::ORIGIN_DIR;
		if (!file_exists($up_dir_orgin)) {
			mkdir($up_dir_orgin);
			chmod($up_dir_orgin, 0777);
		}
		// 320*320 サムネイル
		$up_dir_thumb = CONF_FILEBOX_DIR.FileboxData::THUMB_DIR;
		if (!file_exists($up_dir_thumb)) {
			mkdir($up_dir_thumb);
			chmod($up_dir_thumb, 0777);
		}
		// 160*160 サムネイル
		$up_dir_petit = CONF_FILEBOX_DIR.FileboxData::PETIT_DIR;
		if (!file_exists($up_dir_petit)) {
			mkdir($up_dir_petit);
			chmod($up_dir_petit, 0777);
		}

	}

}

class FileUploadError extends Exception {

	private $errorCode;

	public function __construct( $errorCode, $message ) {
		parent::__construct( $message );
		$this->errorCode = $errorCode;
	}

	public function getErrorCode() { return $this->errorCode; }

}

?>