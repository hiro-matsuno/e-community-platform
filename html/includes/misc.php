<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/MySql.php";

//-----------------------------------------------------
// * パーツや記事などの ID を生成
//-----------------------------------------------------
function get_seqid() {
	$args = func_get_args();
	$type = array_shift($args);

	switch($type) {
		case 'element':
			$table = 'element_sequence';
			break;
		case 'unit':
			$table = 'group_sequence';
			break;
		case 'user':
			$table = 'user_sequence';
			break;
		case 'group':
			$table = 'group_sequence';
			break;
		default:
			$table = 'element_sequence';
	}

	$result = mysql_exec("update %s SET id=LAST_INSERT_ID(id+1);", $table);

	if (!$result) {
		die(mysql_error());
	}

	return mysql_insert_id();
}

//-----------------------------------------------------
// * 文字列の切り詰め
//-----------------------------------------------------
function clip_str($str = null, $size = 24) {
	return mb_strimwidth(strip_tags($str), 0, $size, '...', 'UTF-8');
}

//-----------------------------------------------------
// * ランダム文字列の生成
//-----------------------------------------------------
function rand_str($setLength = NULL, $setKind = NULL ) {
	$rs  = '';

	if(is_null($setLength)) {
		$setLength = 8;
	} elseif (is_numeric($setKind)) {
		$setLength = $setKind;
	}

	$letter = array_merge ( range(a,z), range(A,Z), range(0,9));

	switch($setKind){
		case 'alpha':
			$st = 0;
			$fn = 51;
			break;
		case 'num':
			$st = 51;
			$fn = 61;
			break;
		default:
			$st = 0;
			$fn = 61;
			break;
	}

	for( $n=0; $n<$setLength; $n++ ){
		$rs .= $letter[mt_rand($st, $fn)];
	}
	return $rs;
}

//-----------------------------------------------------
// * 特殊文字のエスケープ
//-----------------------------------------------------
function htmlesc($str = null) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//-----------------------------------------------------
// * 入力された日付から YYYY-MM-DD hh:mm:ss に変換
//-----------------------------------------------------
function post2timestamp($name = null) {
	$fmt = array('Y', 'M', 'D', 'h', 'm', 's');
	$now = split(' ', date('Y m d h i 0'));

	$date = array(); $time = array();
	for ($i = 0; $i < 3; $i++) {
		$value = intval(mb_convert_kana($_POST[$name. '_'. $fmt[$i]], 'n'));
		if (!$value) {
			$date[] = sprintf("%02d", $now[$i]);
		}
		else {
			$date[] = sprintf("%02d", $value);
		}
	}
	for ($i = 3; $i < 6; $i++) {
		$value = intval(mb_convert_kana($_POST[$name. '_'. $fmt[$i]], 'n'));
		if (!$value) {
			$time[] = sprintf("%02d", $now[$i]);
		}
		else {
			$time[] = sprintf("%02d", $value);
		}
	}
	return join('-', $date). ' '. join(':', $time);
}

//-----------------------------------------------------
// * 編集ログに記録
//-----------------------------------------------------
function write_log($str) {
	$q = mysql_exec("insert into logs_edit(log) values(%s)", mysql_str($str));
}

//-----------------------------------------------------
// * システムログに記録
//-----------------------------------------------------
function write_syslog($str) {
	$q = mysql_exec("insert into sys_log(log) values(%s)", mysql_str($str));
}

//-----------------------------------------------------
// * 更新通知処理
//-----------------------------------------------------
function tell_update($eid = null, $page = 'ページ') {
	if (is_group($eid)) {
		$u = mysql_exec('update page set updymd = NOW() where gid = %s',
						mysql_num(get_gid($eid)));
	}
	else {
		$u = mysql_exec('update page set updymd = NOW() where uid= %s',
						mysql_num(get_uid($eid)));
	}

	$site_id = get_site_id($eid);

	if (!$site_id) {
		return;
	}

	$sitename = get_site_name($site_id);
	$url      = CONF_URLBASE. home_url($eid);

	$subject = CONF_SITENAME. '更新通知'; //CONF_SITENAME. $sitename;

	$body    = <<<_BODY_
${sitename}の${page}が更新されました。
${url}
_BODY_;

	$q = mysql_full('select m.uid, u.email from mail_noti as m'.
					' inner join user as u on m.uid = u.id'.
					' where m.eid = %s',
					mysql_num($site_id));

	$udata = array();
	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$udata[$res['uid']] = $res['email'];
		}
		foreach ($udata as $uid => $email) {
			if (!check_pmt($eid, $uid)) {
				continue;
			}
			$fwd = get_fwd_mail($uid);
			if (isset($fwd) && count($fwd) > 0) {
				$to = $fwd;
			}
			else {
				$to = $email;
			}
			$body_head = get_handle($uid). " 様\n\n";
			sys_fwdmail(array('to' => $to, 'subject' => $subject, 'body' => $body_head. $body));
		}
	}
}

//-----------------------------------------------------
// * 「戻る」タグ
//-----------------------------------------------------
function return_button() {
	return '<div class="input_submit_wrap">'.
		   '<div style="margin: 0px auto; padding: 5px;">'.
		   '<button onClick="history.back(); return false;" class="input_cancel">前に戻る</button>'.
		   '</div></div>'.
		   '<div style="clear: both;"></div>';
}

//-----------------------------------------------------
// * ログインページへ転送
//-----------------------------------------------------
function jump2login() {
	set_return_url();
	header('Location:'. CONF_URLBASE. '/login.php');
	exit(0);
}

//-----------------------------------------------------
// * 戻る場合に遷移させたい URL をセット
//-----------------------------------------------------
function set_return_url($url = null) {
	if (!$url) {
		$url = $_SERVER['REQUEST_URI'];
	}
	$_SESSION['return'] = $url;
}

//-----------------------------------------------------
// * タイムスタンプから YYYY-MM-DD hh:mm:ss に変換
//-----------------------------------------------------
function tm2time($mysql_timestamp = null) {
	if (strlen($mysql_timestamp) == 14) {
		return strtotime(substr_replace(substr(substr($mysql_timestamp, 0, 2).
					chunk_split(substr($mysql_timestamp, 2, 6), 2, "-").
					chunk_split(substr($mysql_timestamp, 8), 2, ":"), 0, 19), " ", 10, 1));
	}
	else {
		return strtotime($mysql_timestamp);
	}
}

//-----------------------------------------------------
// * 特定のセッションを正規表現から捨てる
//-----------------------------------------------------
function unset_session($reg) {
	return unset_array($_SESSION, $reg);
}

//-----------------------------------------------------
// * 特定のセッションを正規表現から捨てる (実処理)
//-----------------------------------------------------
function unset_array(&$array, $reg) {
	foreach ($array as $key => $value) {
		if (preg_match($reg, $key) == true ) {
			unset( $array[$key] );
		}
	}
	return true;
}

//-----------------------------------------------------
// * 未ログインユーザーをキック
//-----------------------------------------------------
function kick_guest() {
	if (is_login()) {
		return;
	}
	else {
		$url = urlencode($_SERVER['REQUEST_URI']);
		header('Location: '. CONF_URLBASE. '/login.php?ref='. $url);
	}
	exit(0);
}

//-----------------------------------------------------
// * stripslashes
//-----------------------------------------------------
function stripslashes_deep($value) {
	if (get_magic_quotes_gpc()) {
		$value = is_array($value) ?
					array_map('stripslashes_deep_exec', $value) :
					stripslashes($value);
	}
	return $value;
}
function stripslashes_deep_exec($value) {
	$value = is_array($value) ?
				array_map('stripslashes_deep_exec', $value) :
				stripslashes($value);
	return $value;
}

//-----------------------------------------------------
// * ページ送りの引数を $_REQUEST から判別
//-----------------------------------------------------
function set_navilink($limit = 30) {
	global $SYS_PAGE_NAVI;

	$SYS_PAGE_NAVI['prev']   = (intval($_REQUEST['page']) > 0) ? (intval($_REQUEST['page']) - 1) : null;
	$SYS_PAGE_NAVI['next']   = intval($_REQUEST['page']) + 1;
	$SYS_PAGE_NAVI['offset'] = (intval($_REQUEST['page']) > 0) ? (intval($_REQUEST['page']) * $limit) : 0;
	$SYS_PAGE_NAVI['limit']  = $limit;
}

//-----------------------------------------------------
// * 編集用のスタイルシートを取得 (現在は非推奨)
//-----------------------------------------------------
function load_editcss() {
	global $COMUNI_HEAD_CSS;
	$COMUNI_HEAD_CSS[] = '/layout/edit.css';
}

/**
 * mime_content_type のラップ関数
 * @note PHP 5.0.x で mime_content_type の挙動が怪しいので処理を差し替える.
 */
function mime_content_type_wrap( $file ) {

	//return mime_content_type( $file );
	return trim( `file -bi '$file'` );

}

/**
 * 有効なパスワードかどうか.
 */
function isValidPasswd( $passwd ) {

	return ( $passwd and !preg_match('/[^a-zA-Z0-9]/', $passwd)
				and 7 < strlen( $passwd ) );

}

if(!function_exists('get_called_class')) {
/**
 * PHP >= 5.3.0 で導入される get_called_class の補完関数.
 * PHP Manual の Users' note
 * http://jp.php.net/manual/ja/function.get-called-class.php
 * から取得した.
 * 
 * @attention call_user_func などで呼んだ関数では機能しない.
 * 
 * @param backtrace $bt
 * @param number $l
 * @return string
 */
function get_called_class($bt = false,$l = 1) {
    if (!$bt) $bt = debug_backtrace();
    if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
    if (!isset($bt[$l]['type'])) {
        throw new Exception ('type not set');
    }
    else switch ($bt[$l]['type']) {
        case '::':
            $lines = file($bt[$l]['file']);
            $i = 0;
            $callerLine = '';
            do {
                $i++;
                $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
            } while (stripos($callerLine,$bt[$l]['function']) === false);
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                        $callerLine,
                        $matches);
            if (!isset($matches[1])) {
                // must be an edge case.
                throw new Exception ("Could not find caller class: originating method call is obscured.");
            }
            switch ($matches[1]) {
                case 'self':
                case 'parent':
                    return get_called_class($bt,$l+1);
                default:
                    return $matches[1];
            }
            // won't get here.
        case '->': switch ($bt[$l]['function']) {
                case '__get':
                    // edge case -> get class of calling object
                    if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                    return get_class($bt[$l]['object']);
                default: return $bt[$l]['class'];
            }

        default: throw new Exception ("Unknown backtrace method type");
    }
}
}

/**
 * method_exists は PHP 5.0.x ではクラス名を引数に取った場合正常動作しない.
 * そのための補完関数.
 * @param mixed $class
 * @param string $method
 * @return boolean
 */
function method_exists50( $class, $method ) {

	try {
		new ReflectionMethod($class, $method);
		return true;
	} catch ( ReflectionException $e ) {
		return false;
	}

}


/**
 * ユーティリティ関数のクラス.
 * すべてのメンバ関数は static で定義して下さい.
 */
class EcomUtil {

	public function __construct() {
		throw new BadMethodCallException();
	}

	/**
	 * 直接アクセスを意図しないスクリプトの先頭に記述する.
	 * @throws IllegalAccessException 関数呼び出し元のファイルに、直接 URL 指定して
	 * アクセスしていた場合に発生する.
	 */
	public static function denyDirectAccess() {

		$bt = debug_backtrace();

		$filepath = $bt[0]["file"];
		$uri = $_SERVER['REQUEST_URI'];

		if ( $uri == substr( $filepath, strlen( $filepath ) - strlen( $uri ) ) )
			throw new IllegalAccessException();

	}

	/**
	 * デバッグモード時には文字列をそのまま返す、そうでなければ空文字列を返す.
	 * @param string $string
	 * @return string
	 */
	public static function debugString( $string ) {
		return ( 0 != DEBUG_MODE ? $string : "" );
	}

	static public function defaultFilter( $string ) {

		$string = preg_replace( "/\r?\n\r?\n/", "<p>\r\n", $string );
		$string = preg_replace( "/\r?\n/", "<br>\r\n", $string );

		return $string;

	}
	
}
?>
