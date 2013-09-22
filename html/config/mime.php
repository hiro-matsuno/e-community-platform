<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */


global $SYS_MTYPE_IMG, $SYS_MTYPE;

/* convertでサムネイルを試行するファイル */
$SYS_MTYPE_IMG = array(
	'png'  => 'image/png',
	'gif'  => 'image/gif',
	'jpg'  => 'image/jpeg',
	'jpeg' => 'image/jpeg',
	'bmp'  => 'image/bmp',
	'tif'  => 'image/tiff',
	'tiff' => 'image/tiff'
);

/* encodeでflvを試行するファイル */
$SYS_MTYPE_IMG = array(
	'png'  => 'image/png',
	'gif'  => 'image/gif',
	'jpg'  => 'image/jpeg',
	'jpeg' => 'image/jpeg',
	'bmp'  => 'image/bmp',
	'tif'  => 'image/tiff',
	'tiff' => 'image/tiff'
);

/* アップできる方 */
/*2010年4月のバージョンアップによりfilebox.php内で拡張子によって判定するよう変更*/
/*この記述は無効に*/
$SYS_MTYPE_FILE = array(
	'pot'=>'application/mspowerpoint',
	'pps'=>'application/mspowerpoint',
	'ppt'=>'application/mspowerpoint',
	'ppz'=>'application/mspowerpoint',
	'pdf'=>'application/pdf',
	'ai'=>'application/postscript',
	'eps'=>'application/postscript',
	'ps'=>'application/postscript',
	'rtf'=>'application/rtf',
	'bz2'=>'application/x-bzip2',
	'Z'=>'application/x-compress',
	'xls'=>'application/x-excel',
	'gz'=>'application/x-gzip',
	'lha'=>'application/x-lzh',
	'lzh'=>'application/x-lzh',
	'swf'=>'application/x-shockwave-flash',
	'sit'=>'application/x-stuffit',
	'tar'=>'application/x-tar',
	'tgz'=>'application/x-tar',
	'xdm'=>'application/x-xdma',
	'xdma'=>'application/x-xdma',
	'zip'=>'application/zip',
	'au'=>'audio/basic',
	'mid'=>'audio/midi',
	'midi'=>'audio/midi',
	'mp2'=>'audio/mpeg',
	'mp3'=>'audio/mpeg',
	'mpga'=>'audio/mpeg',
	'ra'=>'audio/vnd.rn-realaudio',
	'aif'=>'audio/x-aiff',
	'aiff'=>'audio/x-aiff',
	'ram'=>'audio/x-pn-realaudio',
	'rm'=>'audio/x-pn-realaudio',
	'wav'=>'audio/x-wav',
	'mcf'=>'image/vasa',
	'xbm'=>'image/x-xbitmap',
	'xpm'=>'image/x-xpixmap',
	'css'=>'text/css',
//	'htm'=>'text/html',
//	'html'=>'text/html',
	'txt'=>'text/plain',
	'xml'=>'text/xml',
	'xsl'=>'text/xsl',
	'3gp' => 'video/3gp',
	'3g2' => 'video/3gpp2',
	'mpe'=>'video/mpeg',
	'mpeg'=>'video/mpeg',
	'mpg'=>'video/mpeg',
	'mov'=>'video/quicktime',
	'qt'=>'video/quicktime',
	'rv'=>'video/vnd.rn-realvideo',
	'vba'=>'video/x-bamba',
	'asf'=>'video/x-ms-asf',
	'asx'=>'video/x-ms-asf',
	'avi'=>'video/x-msvideo',
	'qm'=>'video/x-qmsys',
	'movie'=>'video/x-sgi-movie',
	'doc'=>'application/msword'
);

$SYS_MTYPE_FILE = array_merge($SYS_MTYPE_FILE, $SYS_MTYPE_IMG);

//var_dump($SYS_MTYPE_FILE);

?>
