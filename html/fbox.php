<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

define('FBOX_ORIGIN_DIR', 'o');
define('FBOX_THUMB_DIR',  't');
define('FBOX_PETIT_DIR',  'p');

$eid = $_REQUEST["eid"] ? intval($_REQUEST["eid"]) : 0;

try {

	if ( !$eid ) { throw new DataNotFoundException(); }

	if ( !check_permission( $eid ) ) { throw new PermissionDeniedException(); }

	$fileData = new FileboxData( $eid );

	//	自分のファイルじゃなくて、ごみ箱に入っているファイルは見えない.
	{

		$me = User::getMe();

		if ( 0 < $fileData->getTrashed() and 
			( null === $me or $fileData->getUid() !== $me->getUid() ) ) {
			
			throw new PermissionDeniedException();
			
		}

	}

	$source = '';

	switch( $_REQUEST["s"] ) {

	case 'p':
		$source = $fileData->getThumbPath( true );
		break;

	case 't':
		$source = $fileData->getThumbPath();
		break;

	default:
	case 'o':
		$source = $fileData->getFilePath();
		break;

	}

	$mime_type = mime_content_type_wrap($source);

	header("Content-type: {$mime_type}");

	$filename = $fileData->getName();

	if ( preg_match( "/MSIE/", $_SERVER['HTTP_USER_AGENT'] ) ) {
		$filename = mb_convert_encoding($filename, "SJIS", "auto");
	}

	if ( !preg_match( "/(image|pdf)/", $mime_type ) ) {
		header("Content-Disposition: attachment; filename=\"{$filename}\"");
	} else {
		header("Content-Disposition: inline; filename=\"{$filename}\"");
	}

	header("Content-Length: ".filesize($source));
	header('Pragma: private');
	header('Cache-Control: private');

	readfile($source);
	
} catch ( DataNotFoundException $e ) {
		
	header("HTTP/1.0 404 Not Found");
	header("Content-type: text/html");

	$url = $_SERVER["REQUEST_URI"];

	print <<<__END__
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL '$url' was not found on this server.</p>
</body>
</html>
__END__;

} catch ( PermissionDeniedException $e ) {
	
	header("HTTP/1.0 403 Forhidden");
	header("Content-type: text/html");
	
	$url = $_SERVER["REQUEST_URI"];
	
	print <<<__END__
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>403 Forhidden</title>
</head><body>
<h1>Forhidden</h1>
<p>Permission denied, you are not allowed to access the URL '$url'.</p>
</body>
</html>
__END__;

} catch ( Exception $e ) {

	header("HTTP/1.0 500 InternalServerError");
	header("Content-type: text/html");

	$url = $_SERVER["REQUEST_URI"];

	print <<<__END__
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>501 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
<p>Internal Server Error has occured in access the URL '$url'.</p>
</body>
</html>
__END__;

}


function check_permission( $eid ) {
	
	$uid = myuid();

	try {
		
		$result;
	
		//	ファイルの公開権限を調べる。
		$result = mysql_query( "select e.unit, o.uid, o.gid from filebox_data as fd"
								." left join element as e on fd.id=e.id"
								." left join owner as o on o.id=e.id"
								." where fd.id=$eid" );

		$row;						
		
		if ( !$result or !( $row = mysql_fetch_array( $result ) ) ) throw new Exception();
		
		$gid = $row["gid"];
		
		//	ログインしており、自分の所有ファイルか所有グループに参加していれば閲覧可能。
		if ( $uid and ( $uid == $row['uid'] 
							or ( $gid and is_joined( $gid ) ) ) ) 
			return true;
			
		//	未ログイン、グループ参加者以外は公開状態でなければ閲覧できない。
		if ( 0 != $row['unit'] ) return false;
		//	個人所有で、かつ公開状態になっていれば閲覧可能。
		else if ( 0 == $row['gid'] ) return true;
		
		//	グループ所有の場合はさらにグループの公開権限が必要。
		
		//	グループの公開権限を調べる。
		$result = mysql_query( "select e.unit from page as p"
								." left join element as e on p.id=e.id"
								." where p.gid=$gid" );
								
		if ( !$result or !( $row = mysql_fetch_array( $result ) ) ) throw new Exception();

		//	グループが公開状態になっていれば閲覧可能。
		if ( 0 == $row['unit'] ) return true;

	} catch ( Exception $e ) {
	
		if ( !$result ) print mysql_error();
		
	}
	
	return false;
	
}

function load_scat_path($path = null) {
//	write_syslog('[scat path] '. $path);
	$initial = '_';
	if (preg_match('/\/([a-z0-9])[^\/]+$/i', $path, $m)) {
		$initial = $m[1];
	}
	$scat_dir = implode('/', array(dirname($path), $initial));
	$scat_path = preg_replace('/\/([^\/]+)$/', '/'. $initial. '/$1', $path);
	return $scat_path;
}

function get_thumb_from_youtube( $yt_id ) {
	
	$fileManager = FileboxManager::getInstance();

	$youtube_user = $fileManager->getYoutubeUser();
	$youtube_passwd = $fileManager->getYoutubePasswd();

	if ( !$youtube_user or !$youtube_passwd ) return false;

	require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
	Zend_Loader::loadClass('Zend_Gdata_YouTube');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	
	try {
		
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

		$thumbnails = $entry->getVideoThumbnails();
		
		if ( 0 < count( $thumbnails ) )
		
			return $thumbnails[0]['url'];
			
	} catch ( Exception $e ) {}
		
	return '';
	
}

function get_watch_from_youtube( $yt_id ) {
	
	$youtube_user;
	$youtube_passwd;

	{

		$result = mysql_exec( "select youtube_user, youtube_passwd from filebox_config" );
		if ( $result and $row = mysql_fetch_array( $result ) ) {
			$youtube_user = $row['youtube_user'];
			$youtube_passwd = $row['youtube_passwd'];
		}

		if ( !$youtube_user or !$youtube_passwd ) return false;

	}

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
	
	return $entry->getVideoWatchPageUrl();
		
}
?>
