<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

//-----------------------------------------------------
// * ページのホーム URL を取得
//-----------------------------------------------------
function home_url($eid = null) {
	if (is_group($eid)) {
		$gid = get_gid($eid);
		if (is_portal($gid)) {
			return '/index.php?top';
		}
		else {
			return '/group.php?gid='. get_gid($eid);
		}
	}
	else {
		return '/user.php?uid='. get_uid($eid);
	}
	return '/index.php';
}

//-----------------------------------------------------
// * パーツ名、記事ID,パーツID から URL の生成
//-----------------------------------------------------
function main_href($module = null, $eid = null, $blk_id = null) {
	return "/index.php?module=$module&eid=$eid&blk_id=$blk_id";
}

//-----------------------------------------------------
// * リンクタグの生成A (thickbox対応)
//-----------------------------------------------------
function mkhref($param = array()) {
	$str    = isset($param['s']) ? strip_tags($param['s']) : '無題';
	$href   = isset($param['h']) ? htmlspecialchars($param['h'], ENT_QUOTES) : '';
	$class  = isset($param['c']) ? strip_tags($param['c']) : '';
	$target = isset($param['t']) ? strip_tags($param['t']) : '';

	if ($target != '') {
		$target = ' target="'. $target. '"';
	}
	if ($class != '') {
		$class = ' class="'. $class. '"';
		if (preg_match('/thickbox/', $class)) {
			$href = thickbox_href($href);
		}
	}

	if ($href != '') {
		return '<a href="'. $href. '"'. $class. $target. '><span>'. $str. '</span></a>';
	}
	else {
		return '<span>'. $str. '</span>';
	}
}

//-----------------------------------------------------
// * リンクタグの生成B (thickbox対応)
//-----------------------------------------------------
function make_href($name = null, $href = null, $inline = null, $target = null, $size = null) {
	$name  = strip_tags($name);
	$href  = htmlspecialchars($href, ENT_QUOTES);
	$class = '';
	if ($target) {
		$target = ' target="'. $target. '"';
	}
	if (!$size) {
		$size = 24;
	}
	if (!$name && $href) {
		$name = mb_strimwidth(strip_tags($href), 0, $size, '...', 'UTF-8');
	}
	if ($href) {
		if ($inline == true) {
			$href = thickbox_href($href);
			$class = ' class="thickbox"';
		}
		return '<a href="'. $href. '"'. $class. $target. '><span>'. $name. '</span></a>';
	}
	else {
		return '<span>'. $name. '</span>';
	}
}

//-----------------------------------------------------
// * thickbox 用 URL の生成
//-----------------------------------------------------
function thickbox_href($href = null) {
	$delimiter = '&';
	if (!preg_match('/\?/', $href, $match)) {
		$delimiter = '?';
	}
	return $href. $delimiter. 'keepThis=true&TB_iframe=true&height=480&width=640';
}

/**
 * Description of Path
 *
 * @author ikeda
 */
class Path {

	/**
	 * 与えられたパスについて BASEURL からのものとして URL を返す.
	 *
	 * URLが与えられた場合はURLをそのまま返す.
	 * 絶対パスが与えられた場合は BASEURL をつなげてURLを返す.
	 * 相対パスが与えられた場合は $_SERVER["REQUEST_URI"] とつなげてURLを返す.
	 *
	 * @param string $path
	 * 
	 */
	static public function makeURL( $path ) {

		if ( preg_match( "/^[^\/]*:[^\/]*/", $path ) ) {

			return $path;

		} else if ( preg_match( "/^\//", $path ) ) {

			$urlBase = null;

			if ( preg_match( "/^(.*)\/$/", CONF_URLBASE, $match ) ) {
				$urlBase = $match[1];
			} else {
				$urlBase = CONF_URLBASE;
			}

			return $urlBase.$path;

		} else {

			$pwd = null;

			if ( preg_match( "/\/$/", $_SERVER["REQUEST_URI"] ) ) {
				$pwd = $_SERVER["REQUEST_URI"];
			} else {
				$pwd = dirname( $_SERVER["REQUEST_URI"] )."/";
			}

			return $pwd.$path;

		}

	}

	public function _makeURL( $path ) {

		return Path::makeURL( $path );

	}

}
?>
