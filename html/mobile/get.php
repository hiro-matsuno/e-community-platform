<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__) . '/../lib.php';

ini_set("include_path", CONF_BASEDIR. '/PEAR/'. PATH_SEPARATOR. ini_get("include_path"));

require_once('Net/UserAgent/Mobile.php');
require_once dirname(__FILE__) . '/lib/Geomobilejp/Converter.php';
require_once dirname(__FILE__) . '/lib/Geomobilejp/Mobile.php';


header('Content-Type: text/html; charset=Shift_JIS');
ob_start();

session_start();

$agent = Net_UserAgent_Mobile::singleton(); 
 
$url = 'http://' . $_SERVER['SERVER_NAME']
     . preg_replace('/\?.+?$/', '', $_SERVER['REQUEST_URI']);
?>
<html>
<head>
<title>現在位置の登録</title>
</head>
<body>

<?php

$mobile = new Geomobilejp_Mobile();

if ($mobile->hasParameter()) {

    $converter = new Geomobilejp_Converter(
        $mobile->getLatitude(),
        $mobile->getLongitude(),
        $mobile->getDatum()
    );

	$cw = $converter->convert('wgs84');
	$ct = $converter->convert('tokyo');

    $c1 = $cw->format('dms');
    $c2 = $cw->format('degree');
    $c3 = $ct->format('dms');
    $c4 = $ct->format('degree');

    $c1d = $c1->getDatum();
    $c2d = $c2->getDatum();
    $c3d = $c3->getDatum();
    $c4d = $c4->getDatum();

	$lat  = $c2->getLatitude();
	$lon  = $c2->getLongitude();
	$name = $c2d->getName();
	$post_id = get_post_id();

	$param = array('lat='. $lat,
				   'lon='. $lon,
				   'name='. $name,
				   'post_id='. $post_id);

	$link = CONF_URLBASE. '/mobile/post.php?'. join('&', $param);
?>

位置を決定しました。<br>
<hr>
<a href="<?php echo htmlspecialchars($link) ?>">記事の入力へ進む</a><br>
<hr>
<ul>
<li>lat:<?php echo htmlspecialchars($lat) ?></li>
<li>lon:<?php echo htmlspecialchars($lon) ?></li>
</ul>
<?php

	$output = ob_get_contents();
	ob_end_clean();
	echo mb_convert_encoding($output, "SJIS", "UTF-8");

	exit(0);
}

$url = CONF_URLBASE. '/mobile/post.php/'. $_GET["post_id"];

switch (true) {
	case ($agent->isDoCoMo()):   // DoCoMoかどうか
//		echo "DoCoMoです。";
		if( $agent->isFOMA() )
			$form = form_docomo($url);
		break;
	case ($agent->isVodafone()): // softbankかどうか
//		echo "softbankです。";
		if( $agent->isType3GC() )
			$form = form_sb($url);
		break;
	case ($agent->isEZweb()):    // ezwebかどうか
//		echo "ezwebです。";
		if( $agent->isWIN() )
			$form = form_au($url);
		break;
	default:
		$form = form_docomo($url);
		break;
}

?>
<center>e-community platform<br>携帯位置投稿</center>
<hr>
ボタンを押して現在位置を送信します。
<?php echo $form; ?>
</body>
</html>
<?

$output = ob_get_contents();
ob_end_clean();
echo mb_convert_encoding($output, "SJIS", "UTF-8");

function get_post_id() {
	$pat = '/^\//';
	return preg_replace($pat, '', get_path_info());
}

function get_path_info() {
	if (array_key_exists('PATH_INFO', $_SERVER)) {
		return $_SERVER['PATH_INFO'];
	}
	else if (array_key_exists('ORIG_PATH_INFO', $_SERVER)) {
		return $_SERVER['ORIG_PATH_INFO'];
	}
	$path_info = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
	if (substr_count($path_info, '?') > 0) {
		$path_info = preg_replace('/\?.*/', '', $path_info);
	}
	return $path_info;
}

function form_au($url) {
	return <<<__FORM__
    <form action="device:gpsone" method="get">
      <input type="hidden" name="url" value="${url}" />
      <input type="hidden" name="datum" value="0" />
      <input type="hidden" name="ver" value="1" />
      <input type="hidden" name="unit" value="0" />
      <input type="hidden" name="acry" value="0" />
      <input type="hidden" name="number" value="0" />
      <input type="submit" value="現在位置の送信" />
    </form>
__FORM__;
	;
}

function form_docomo($url) {
	return <<<__FORM__
    <form action="${url}" method="get" lcs="lcs">
      <input type="submit" value="現在位置の送信" />
    </form>
__FORM__;
	;
}

function form_sb($url) {
	return <<<__FORM__
<a href="location:gps?url=${url}">現在位置の送信</a>
__FORM__;
	;
}

?>
