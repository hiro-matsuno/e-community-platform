<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once "../../version.php";

include_once "../../includes/Module.php";
include_once "../../includes/MySql.php";

ini_set('display_errors', 0);
//ini_set( "log_errors", "On" );
//ini_set( "error_log", "/home/user1/public_html/php.log" );

$currdir = dirname(__FILE__);
$tmp = explode('/',$currdir);
array_pop($tmp);array_pop($tmp);
$basedir = implode('/',$tmp);
ini_set("include_path", $basedir. '/PEAR/'. PATH_SEPARATOR. ini_get("include_path"));
$document_root = dirname(dirname($currdir));

if(!file_exists($basedir. '/lib/Smarty.class.php')){
	print 'ライブラリファイルがアップロードされていません。';
	exit(0);
}

$CONF['dir'] = $basedir.'/config';
$CONF['file'] = $CONF['dir'].'/'.$_SERVER['SERVER_NAME'].'.ini.php';
$conf = parse_ini_file($CONF['file'],true);

$CONF['url'] = $_SERVER["SCRIPT_NAME"];

$conf_sections = array('conf_host'  => 'サイト名・URL・その他',
						'conf_mysql'=>'データベース',
						'conf_post' =>'携帯用投稿メール',
						'conf_dir'  =>'ディレクトリ',
						'conf_map'  =>'Google Map');

session_start();
if(isset($_REQUEST['p'])and isset($conf['conf_host']['passwd'])){
	if($conf['conf_host']['passwd']==$_REQUEST['p']){
		$_SESSION['is_logon']=true;
	}else
		$login_err=true;
}
if(isset($conf['conf_host']['passwd'])){
	if(!isset($_SESSION['is_logon'])){
		$content = '';
		if($login_err)$login_err .= "<div class='input_error'>入力されたパスワードが登録されたものと一致しません。</div>";
		$hidden = '';
		foreach($_REQUEST as $key => $val){
			$key = urlencode($key);
			$val = urlencode($val);
			$hidden .= "<input type='hidden' key='$key' value='$val'>\n";
		}
		$content .= <<<__PW_FORM__

<form method='GET' class="input_form">
$hidden
<h3 class="input_title">設定用パスワードを入力してください。</h3>
$login_err
<div class="input_body"><input type='password' name='p'></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="ログイン" class="input_submit">
</div></div>
</form>
__PW_FORM__;
	show_conf_page(array('id'=>1, 'title'=>'ログインしてください', 'content'=>$content));
	exit(0);
	}
}else{
	$_SESSION['is_logon'] = true;
}

if(!isset($_SERVER['SERVER_NAME']))
	die('お使いのブラウザには対応しておりません。');
if($_SERVER['SCRIPT_NAME']!='/manager/install/setup.php')
	die(<<<__ERR_MESSAGE__
現在のバージョンではドキュメントルート以外での使用はできません。<br>
ドキュメントルート以下にファイルを設置してください。<br>
ドキュメントルートは $_SERVER[DOCUMENT_ROOT] の可能性があります。
__ERR_MESSAGE__
);

if(file_exists($CONF['file'])){
	if(!is_writable($CONF['file'])){
		if(is_writable($CONF['dir']))
			$error = "<div class='input_error'>設定ファイル「${CONF['file']}」が存在し、書き込み不能に設定されています。<br>「${CONF['file']}」を削除するか、書き込み可能に設定してください。</div>";
		else
			$error = "<div class='input_error'>設定ファイル「${CONF['file']}」が存在し、書き込み不能に設定されています。<br>設定ファイルの格納ディレクトリ「${CONF['dir']}」は書き込みできません。<br>「${CONF['file']}」を書き込み可能に設定するか、「${CONF['file']}」を削除し「${CONF['dir']}」を書き込み可能に設定してください。</div>";
	}
}elseif(!is_writable($CONF['dir']))
		$error = "<div class='input_error'>設定ファイル「${CONF['file']}」が存在しません。<br>設定ファイルの格納ディレクトリ「${CONF['dir']}」は書き込みできません。<br>「${CONF['file']}」を作成し書き込み可能に設定するか、「${CONF['dir']}」を書き込みできるように設定してください。</div>";
if($error){
	$error .= '※ファイルを設置したユーザーと異なるユーザーの権限でe-communityプログラムが実行されている場合があります。<br>ファイルを書き込み可能に設定したにもかかわらずこのメッセージが表示される場合は、誰でも書き込める設定にするか、実行時のユーザーを調べそのユーザーが書き込めるように設定してください。';
	show_conf_page(array('id'=>1, 'title'=>'設定ファイルが作成できません', 'content'=>$error));
	exit(0);	
}

$conf_error['conf_host'] = $error;

switch($_REQUEST['act']){
	case 'db_regist':
		db_regist();
	case 'conf_mysql':
		db_input();
		break;
	case 'post_regist':
		post_regist();
	case 'conf_post':
		post_input();
		break;
	case 'map_regist':
		map_regist();
	case 'conf_map':
		map_input();
		break;
	case 'dir_regist':
		dir_regist();
	case 'conf_dir':
		dir_input();
		break;
	case 'host_regist':
		host_regist();
	case 'conf_host':
		host_input();
		break;
	case 'user_regist';
		user_regist();
	case 'user':
		user_input();
		break;
	default:
		top();
		break;
}

function top(){
	global $CONF,$content,$conf,$conf_sections,$complete;
	if(isset($conf['conf_host']['site_name'])){
		$site_name = $conf['conf_host']['site_name'];
		$content =  "<h3>$site_name(${_SERVER['SERVER_NAME']})の設定</h3>";
	}
	else{
		$site_name = $_SERVER['SERVER_NAME'];
		$content = "<h3>$_SERVER[SERVER_NAME]の設定</h3>";
	}
	
	$content .= conf_url_list();
	
	if($complete)$content .= '<hr><h3>設定完了</h3><a href="'.$conf['conf_host']['site_url'].'">'.$conf['conf_host']['site_name'].'を表示</a>';
	
	show_conf_page(array('id' => 1,'title'=>'サイト基本設定','content'=>$content));
	exit(0);	
}
function str_escape($str){
	if (get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return mysql_real_escape_string($str);
}
	
function user_regist(){
	global $conf,$error,$user,$CONF;
	$user = array(
					'nickname' => $_REQUEST['nickname'],
					'mail'	   => $_REQUEST['mail'],
	                'password' => $_REQUEST['password']
	              );
	if($user['nickname']=='')$error['user']['nickname']='ニックネームを入力してください';
	if($user['mail']=='')$error['user']['mail']='メールアドレスを入力してください';
	if($user['password']=='')$error['user']['password']='パスワードを入力してください';
	if($user['password']!=$_REQUEST['password_c']){
		unset($user['password']);
		$error['user']['password']='パスワードが一致しません。';
	}
	if($error)return;
	mysql_connect($conf['conf_mysql']['server'], $conf['conf_mysql']['user'], $conf['conf_mysql']['passwd']);
	mysql_select_db($conf['conf_mysql']['database']);
	$res = mysql_query("select * from user limit 1");
	$esc_user = array_map('str_escape',$user);
	$esc_user['password'] = md5($user['password']);
	if(mysql_num_rows($res)==0){
		mysql_query('set names utf8');
		print(mysql_error());
		mysql_query("insert into user (id, level, handle, email, password, enable, initymd)".
					" values (1001, 100, '$esc_user[nickname]', '$esc_user[mail]', '$esc_user[password]', 1, now())");
		print(mysql_error());
		mysql_query("insert into friend_user (gid, owner, pid, name) values (10001, 1001, 10001, '全てのフレンド')");
		print(mysql_error());
		mysql_query("insert into friend_extra (gid, uid, name) values (10002, 1001, '友達の友達（仮称）')");
		print(mysql_error());
		
		mysql_query("insert into group_member (gid, uid, level) values (10003, 1001, 100)");
		print(mysql_error());
		mysql_query("insert into unit(id, uid) values(10003, 1001)");
		print(mysql_error());
		mysql_query("insert into friend_group (gid, owner, pid, name)".
						" values (10004, 10003, 10004, '全てのフレンドグループ');");
		print(mysql_error());
		
		mysql_query("insert into page (gid, uid, id, sitename, skin, initymd)".
							" values (10003, 1001, 10001, 'ポータルページ', 1, now());");
		print(mysql_error());
		mysql_query("insert into element (id, unit) values (10001, 0)");
		print(mysql_error());
		mysql_query("insert into owner (id, uid, gid) values (10001, 1001, 10003)");
		print(mysql_error());
		mysql_query("insert into portal (gid) values (10003)");
		print(mysql_error());
		$content = 'ユーザーを登録しました。'.
		'<div class="input_submit_wrap">'.
		'<div style="margin: 0px auto; padding: 5px;">'.
		'<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		'</div></div>'.
		'<div style="clear: both;"></div>';
	}else{
		$content = '既にユーザーが登録されています。';
	}
	mysql_close();
	show_conf_page(array('id'=>1, 'title'=>'初期ユーザー登録', 'content'=>$content));
	exit(0);
}
function user_input(){
	global $user,$error;
	
	$e = array_map('enclose_input_error',$error['user']);

	$content .= <<< __HTML__
ｅコミ２．０にユーザーを登録します。<br>
ここで登録したメールアドレス・パスワードを使用してｅコミ２．０に管理者としてログインすることができます。<br>
登録したユーザーの編集は初期設定画面ではなくｅコミ２．０から行ってください。
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='user_regist' name='act'>
<div style="clear: both"></div>
<h3 class="input_title">ニックネーム</h3>
<div class="input_body"><input type='text' name='nickname' class="input_text" value="$user[nickname]"></div>
<h3 class="input_title">メールアドレス</h3>
<div class="input_body"><input type='text' size=40 name='mail' class="input_text" value="$user[mail]"></div>
<h3 class="input_title">パスワード</h3>
${e['password']}
<div class="input_body"><input type='password' name='password' class="input_text" value="$user[password]"></div>
<div class="input_body"><input type='password' name='password_c' class="input_text" value="$user[password]"></div>
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'初期ユーザー登録', 'content'=>$content));
	exit(0);
}
//
// 基本設定
//
function host_regist(){
	global $CONF,$content,$conf_host,$conf,$conf_sections,$error;
	$conf_host = array(
						'site_name'  => $_REQUEST['site_name'],
						'publish'=> isset($_REQUEST['publish']),
						'site_url' => $_REQUEST['site_url'],
						'url_base'     => $_REQUEST['url_base'],
						'email'     => $_REQUEST['email'],
						'err_email'     => $_REQUEST['err_email'],
						'smtp_server'     => $_REQUEST['smtp_server'],
						'random_seed'     => $_REQUEST['random_seed'],
						'passwd' => $_REQUEST['passwd']
						);
	if($_REQUEST['passwd'] != $_REQUEST['passwd2']){
		$error['conf_host']['passwd']="パスワードが一致しません";
		return;
	}
	if(!check_conf_host($conf_host))return;
	if($conf_host['passwd']=='')unset($conf_host['passwd']);
	$conf['conf_host'] = $conf_host;
	if(write_ini($conf))
		$content = "$conf_sections[conf_host]の設定を完了しました。"	.
		    '<div class="input_submit_wrap">'.
		    '<div style="margin: 0px auto; padding: 5px;">'.
		    '<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		    '</div></div>'.
		    '<div style="clear: both;"></div>';
	else
		$content = "設定の保存に失敗しました。";
	show_conf_page(array('id'=>1, 'title'=>$conf_sections['conf_host'].'の設定', 'content'=>$content));
	exit(0);
}

function host_input(){
	global $CONF,$content,$conf_host,$conf,$conf_error,$error,$conf_sections;
	
	$default = array(
					'site_name'=>"eコミュニティ・プラットフォーム2.0",
					'publish'=>true,
					'site_url'=>"http://$_SERVER[SERVER_NAME]/",
					'url_base'=>"http://$_SERVER[SERVER_NAME]",
					'email'=>"",
					'err_email'=>"",
					'smtp_server'=>"localhost",
					'passwd' => ""
					);

	if(isset($conf['conf_host'])){
		if(!isset($conf_host))$conf_host = $conf['conf_host'];
		foreach($conf['conf_host'] as $key => $val)$c_conf[$key]="(現在の設定値:$val)";
	}
	if(!$conf_host)$conf_host = $default;
	
	if($conf['conf_host']['publish'])$c_conf['publish'] = "(現在の設定値：公開する)";
	elseif(isset($conf['conf_host']['publish']))
		$c_conf['publish'] = "(現在の設定値：公開しない)";
	if($conf_host['publish'])$publish_checked = 'checked';
	
	$e = array_map('enclose_input_error',$error['conf_host']);
	if(isset($e['passwd']))$conf_host['passwd']="";

	$content .= <<< __HTML__
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='host_regist' name='act'>
<div style="clear: both"></div>
<h3 class="input_title">サイト名$c_conf[site_name]</h3>
<div class="input_body"><input type='text' size=64 name='site_name' class="input_text" value="$conf_host[site_name]"></div>
<h3 class="input_title">公開設定$c_conf[publish]</h3>
<div class="input_body"><input type='checkbox' name='publish' $publish_checked value="1">公開する</div>
<h3 class="input_title">サイトURL$c_conf[site_url]</h3>
<div class="input_body"><input type='text' size=64 name='site_url' class="input_text" value="$conf_host[site_url]"></div>
<h3 class="input_title">URL BASE$c_conf[url_base]</h3>
<div class="input_body"><input type='text' size=64 name='url_base' class="input_text" value="$conf_host[url_base]"></div>
<h3 class="input_title">送信メールの差出人アドレス$c_conf[email]</h3>
<div class="input_body"><input type='text' size=64 name='email' class="input_text" value="$conf_host[email]"></div>
<h3 class="input_title">エラー通知メールアドレス$c_conf[err_email]</h3>
<div class="input_body"><input type='text' size=64 name='err_email' class="input_text" value="$conf_host[err_email]"></div>
<h3 class="input_title">SMTPサーバー$c_conf[smtp_server]</h3>
$e[smtp_server]
<div class="input_body"><input type='text' size=64 name='smtp_server' class="input_text" value="$conf_host[smtp_server]"></div>
<h3 class="input_title">乱数生成用の文字列$c_conf[random_seed]</h3>
<i style="font-size: 0.8em; color: darkgray">乱数生成の種となる文字列です。
	この文字列を記憶しておく必要はありませんが、他の人に予想されないものを設定してください</i>
<div class="input_body"><input type='text' size=64 name='random_seed' class="input_text" value="$conf_host[random_seed]"></div>
<h3 class="input_title">設定用パスワード</h3>
<i style="font-size: 0.8em; color: darkgray">この設定画面のためのパスワードです。ｅコミ２．０稼動時に使用するパスワードではありません。</i>
$e[passwd]
<div class="input_body"><input type='password' size=20 name='passwd' class="input_text" value="$conf_host[passwd]"></div>
確認のためもう一度入力してください
<div class="input_body"><input type='password' size=20 name='passwd2' class="input_text" value="$conf_host[passwd]"></div>
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'サイト名・URL・その他の設定', 'content'=>$content));
	exit(0);
}

function check_conf_host($conf_host){
	global $error;
	return true;
}

//
// ディレクトリの設定
//
function dir_regist(){
	global $CONF,$content,$dir_conf,$conf,$conf_sections,$document_root;
	$dir_conf = array(
						'smarty_compile'  => $_REQUEST['smarty_compile'],
						'smarty_cache'    => $_REQUEST['smarty_cache'],
						'smarty_config'   => $_REQUEST['smarty_config'],
						'databox_urlbase' => $_REQUEST['databox_urlbase'],
						'databox_dir'     => $_REQUEST['databox_dir'],
						'convert_path'    => $_REQUEST['convert_path']
						);
	if(!check_conf_dir($dir_conf))return;
	$dirs = array('/filebox',
				  '/profile',
				  '/profile/b',
				  '/profile/m',
				  '/profile/n',
				  '/guest',
				  '/guest/o',
				  '/guest/t'
				);
	foreach ($dirs as $d) {
		if (!file_exists($document_root.'/'.$dir_conf['databox_dir']. $d)) {
			mkdir($document_root.'/'.$dir_conf['databox_dir']. $d, 0777);
		}
		chmod($document_root.'/'.$dir_conf['databox_dir']. $d, 0777);
		
	}
				
	if(!check_conf_dir($dir_conf))return;

	$conf['conf_dir'] = $dir_conf;
	if(write_ini($conf))
		$content = "$conf_sections[conf_dir]の設定を完了しました。".
		    '<div class="input_submit_wrap">'.
		    '<div style="margin: 0px auto; padding: 5px;">'.
		    '<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		    '</div></div>'.
		    '<div style="clear: both;"></div>';
	else
		$content = "設定の保存に失敗しました。";
	show_conf_page(array('id'=>1, 'title'=>$conf_sections['conf_dir'].'の設定', 'content'=>$content));
	exit(0);
}

function dir_input(){
	global $CONF,$content,$dir_conf,$conf,$conf_error,$error,$conf_sections,$basedir;

	$default = array(
					'smarty_compile'  => '_tpl_compile',
					'smarty_cache'    => '_tpl_cache',
					'smarty_config'   => '_tpl_config',
					'databox_urlbase' => '',
					'databox_dir'     => 'databox',
					'convert_path'    => ''
					);
	
	$convp = array('/usr/bin/convert','/usr/X11R6/bin/convert','/usr/local/bin/convert');
	foreach($convp as $c){
  		exec($c,$aaa,$stat);
  		unset($aaa);
  		if($stat == 0){
    		$default['convert_path'] = $c;
    		break;
  		}
	}

	if(isset($conf['conf_host']['url_base']))
		$default['databox_urlbase']=$conf['conf_host']['url_base'];
	else
		$default['databox_urlbase']="http://$_SERVER[SERVER_NAME]";

	if(isset($conf['conf_dir'])){
		if(!isset($dir_conf))$dir_conf = $conf['conf_dir'];
		foreach($conf['conf_dir'] as $key => $val)$c_conf[$key]="(現在の設定値:$val)";
	}
	if(!isset($dir_conf))$dir_conf = $default;

	if(isset($error['conf_dir']))$e = $error['conf_dir'];
	elseif(isset($conf_error['conf_dir']))$e = $conf_error['conf_dir'];
	$e = array_map('enclose_input_error',$e);

	if(!is_writable($basedir.'/modules'))
		$pmt_note .= "'$basedir/modules'　への書き込みができません。<br>\nこのままではパーツ追加機能が使用できません。<br>\n";
	if(!is_writable($basedir.'/skin'))
		$pmt_note .= "'$basedir/skin'　への書き込みができません。<br>\nこのままではスキンの簡易作成機能が使用できません。<br>\n";
	if($pmt_note)'<h3 class="input_title">パーミッションの設定を確認してください</h3>'.enclose_input_error($pmt_note);

	$content .= <<< __HTML__
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='dir_regist' name='act'>
<div style="clear: both"></div>

<h3 class="input_title">Smarty コンパイルディレクトリ$c_conf[smarty_compile]</h3>
$e[smarty_compile]
<div class="input_body"><input type='text' name='smarty_compile' class="input_text" value="$dir_conf[smarty_compile]"></div>
<h3 class="input_title">Smarty キャッシュディレクトリ$c_conf[smarty_cache]</h3>
$e[smarty_cache]
<div class="input_body"><input type='text' name='smarty_cache' class="input_text" value="$dir_conf[smarty_cache]"></div>
<h3 class="input_title">Smarty コンフィグディレクトリ$c_conf[smarty_config]</h3>
$e[smarty_config]
<div class="input_body"><input type='text' name='smarty_config' class="input_text" value="$dir_conf[smarty_config]"></div>
<h3 class="input_title">データ格納場所のURLプレフィックス$c_conf[databox_urlbase]</h3>
ファイル倉庫にアップロードしたファイルおよびプロフィールのアイコンとしてアップロードしたファイルにアクセスする場合にeコミ本体とは異なるURLでアクセスする必要がある場合はここに設定します。
$e[databox_urlbase]
<div class="input_body"><input type='text' size=64 name='databox_urlbase' class="input_text" value="$dir_conf[databox_urlbase]"></div>
<h3 class="input_title">ファイル倉庫およびプロフィールのアイコンのファイル格納場所$c_conf[databox_dir]</h3>
$e[databox_dir]
<div class="input_body"><input type='text' name='databox_dir' class="input_text" value="$dir_conf[databox_dir]"></div>
<h3 class="input_title">ImageMagicのconvertコマンドの場所$c_conf[convert_path]</h3>
$e[convert_path]
<div class="input_body"><input type='text' name='convert_path' class="input_text" value="$dir_conf[convert_path]"></div>
$pmt_note
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'ディレクトリの設定', 'content'=>$content));
	exit(0);
}

function check_conf_dir($dir_conf){
	global $error,$document_root;
	$chk_dirs=array('smarty_compile','smarty_cache','smarty_config','databox_dir');
	foreach($chk_dirs as $key){
		$dir = $dir_conf[$key];
		if(!file_exists($document_root.'/'.$dir))
			$error['conf_dir'][$key]="'$dir'(${document_root}/${dir})は存在しません。";
		elseif(!is_dir($document_root.'/'.$dir))$error['conf_dir'][$key]="$dir'はディレクトリではありません。";
		elseif(!is_writable($document_root.'/'.$dir))$error['conf_dir'][$key]="'$dir'は書き込みできません。";
	}
	
	$convert = str_replace(' ','\ ',$conf['conf_dir']['convert_path']);
	exec($convert,$aaa,$stat);
  	unset($aaa);
  	if($stat == 0){
  		$conf['conf_dir']['convert_path'] = $convert;
  	}else{
		$convert = "'".$conf['conf_dir']['convert_path']."'";
		exec($convert,$aaa,$stat);
	  	unset($aaa);
	  	if($stat == 0)
	  		$conf['conf_dir']['convert_path'] = $convert;
    	else
    		$error['conf_dir']['convert_path'] = "$dir_conf[convert_path]は実行可能プログラムではありません。";
  	}

//	if(!is_executable($dir_conf['convert_path']))$error['conf_dir']['convert_path']="$dir_conf[convert_path]は実行可能プログラムではありません。";
	if(isset($error['conf_dir']))return false;
	return true;
}
//
// Google Mapの設定
//
function map_regist(){
	global $CONF,$content,$conf_map,$conf,$conf_sections;
	$conf_map = array(
						'api_key'  => $_REQUEST['api_key'],
						'longitude'=> $_REQUEST['longitude'],
						'latitude' => $_REQUEST['latitude'],
						'zoom'     => $_REQUEST['zoom']
						);
	if(!check_conf_map($conf_map))return;
	$conf['conf_map'] = $conf_map;
	if(write_ini($conf))
		$content = "$conf_sections[conf_map]の設定を完了しました。".
		    '<div class="input_submit_wrap">'.
		    '<div style="margin: 0px auto; padding: 5px;">'.
		    '<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		    '</div></div>'.
		    '<div style="clear: both;"></div>';
	else
		$content = "設定の保存に失敗しました。";
	show_conf_page(array('id'=>1, 'title'=>$conf_sections['conf_map'].'の設定', 'content'=>$content));
	exit(0);
}

function map_input(){
	global $CONF,$content,$conf_map,$conf,$conf_error,$error,$conf_sections;

	$default = array('longitude'=>"140.1112174987793",
					'latitude' =>"36.082748448481766",
					'zoom'     =>"15");
	
	if(isset($conf['conf_map'])){
		if(!isset($conf_map))$conf_map = $conf['conf_map'];
		foreach($conf['conf_map'] as $key => $val)$c_conf[$key]="(現在の設定値:$val)";
	}
	if(!$conf_map)$conf_map = $default;

	if(isset($error['conf_map']))$content .= $error['conf_map'];
	elseif(isset($conf_error['conf_map']))$content .= $conf_error['conf_map'];
	
	$content .= <<< __HTML__
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='map_regist' name='act'>
<div style="clear: both"></div>
<h3 class="input_title">Google Maps APIキー$c_conf[api_key]</h3>
Google Maps APIキーの取得は<a href='http://code.google.com/intl/ja/apis/maps/signup.html' target='_blank'>Googleの該当ページ</a>より。
<div class="input_body"><input type='text' size=80 name='api_key' class="input_text" value="$conf_map[api_key]"></div>
緯度経度情報の取得は<a href='http://www.geocoding.jp/' target='_blank'>Geocoding</a>を使わせていただくと便利です。
<h3 class="input_title">中心経度(10進法 世界測地系)$c_conf[longitude]</h3>
<div class="input_body"><input type='text' size=64 name='longitude' class="input_text" value="$conf_map[longitude]"></div>
<h3 class="input_title">中心緯度(10進法 世界測地系)$c_conf[latitude]</h3>
<div class="input_body"><input type='text' size=64 name='latitude' class="input_text" value="$conf_map[latitude]"></div>
<h3 class="input_title">縮尺(0から19まで)$c_conf[zoom]</h3>
<div class="input_body"><input type='text' name='zoom' class="input_text" value="$conf_map[zoom]"></div>
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'Google Mapの設定', 'content'=>$content));
	exit(0);
}

function check_conf_map($conf_map){
	global $error;
	return true;
}

//
// 投稿用メールアドレスの設定
//
function post_regist(){
	global $CONF,$content,$conf_post,$conf,$conf_sections;
	$conf_post = array(
						'email' => $_REQUEST['email'],
						'server'   => $_REQUEST['pop_server'],
						'user'     => $_REQUEST['pop_user'],
						'passwd'   => $_REQUEST['pop_passwd']
						);
	
	if(!check_conf_post($conf_post))return;
	$conf['conf_post'] = $conf_post;
	if(write_ini($conf))
		$content = "$conf_sections[conf_post]の設定を完了しました。".
		    '<div class="input_submit_wrap">'.
		    '<div style="margin: 0px auto; padding: 5px;">'.
		    '<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		    '</div></div>'.
		    '<div style="clear: both;"></div>';
	else
		$content = "設定の保存に失敗しました。";
	show_conf_page(array('id'=>1, 'title'=>$conf_sections['conf_post'].'の設定', 'content'=>$content));
	exit(0);
}

function post_input(){
	global $CONF,$content,$conf_post,$conf,$conf_error,$error,$conf_sections;

	$content = '携帯から画像などを投稿する際に使用するあて先メールを設定します。<br>メールによる投稿機能を使用しない場合は、全て空欄にしてください。';
	
	if(isset($conf['conf_post'])){
		if(!isset($conf_post))$conf_post = $conf['conf_post'];
		foreach($conf['conf_post'] as $key => $val)$c_conf[$key]="(現在の設定値:$val)";
	}

	if(isset($error['conf_post']))$content .= $error['conf_post'];
	elseif(isset($conf_error['conf_post']))$content .= $conf_error['conf_post'];
	
	$content .= <<< __HTML__
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='post_regist' name='act'>
<div style="clear: both"></div>
<h3 class="input_title">投稿用メールアドレス$c_conf[email]</h3>
<div class="input_body"><input type='text' size=64 name='email' class="input_text" value="$conf_post[email]"></div>
<h3 class="input_title">POP サーバー名$c_conf[server]</h3>
<div class="input_body"><input type='text' size=64 name='pop_server' class="input_text" value="$conf_post[server]"></div>
<h3 class="input_title">POP アカウント$c_conf[user]</h3>
<div class="input_body"><input type='text' size=64 name='pop_user' class="input_text" value="$conf_post[user]"></div>
<h3 class="input_title">POP パスワード$c_conf[passwd]</h3>
<div class="input_body"><input type='text' name='pop_passwd' class="input_text" value="$conf_post[passwd]"></div>
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
※投稿用メールアドレスの検証は行われません。お間違えの無いよう、お確かめください。
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'携帯用投稿メールの設定', 'content'=>$content));
	exit(0);
}

function check_conf_post($conf_post){
	global $error;
	
	if(implode('',$conf_post) == '')return true;
	
	require_once 'Net/POP3.php';
	$pop3 =& new Net_POP3;
	
	if(!$pop3->connect($conf_post['server'], 110)){
		$error['conf_post'] .= "<div class='input_error'>POP サーバーに接続できません。<br>サーバー名が正しいか確認してください。</div>\n";
		return false;
	}
	if( ( $err = $pop3->login($conf_post['user'], $conf_post['passwd'],'USER') )!==true){
		$error['conf_post'] .= "<div class='input_error'>POP サーバーにログインできません。<br>ユーザ名およびパスワードが正しいか確認してください。</div>\n";
		return false;
	}
	return true;
}
//
// データベースの設定
//
function db_regist(){
	global $CONF,$content,$conf_mysql,$conf,$conf_sections;
	$conf_mysql = array(
						'server'   => $_REQUEST['db_server'],
						'user'     => $_REQUEST['db_user'],
						'passwd'   => $_REQUEST['db_passwd'],
						'database' => $_REQUEST['db_database']
						);
	if(!check_conf_mysql($conf_mysql))return;
	$conf['conf_mysql'] = $conf_mysql;
	if(write_ini($conf))
		$content = "$conf_sections[conf_mysql]の設定を完了しました。".
		    '<div class="input_submit_wrap">'.
		    '<div style="margin: 0px auto; padding: 5px;">'.
		    '<button onClick="location.href='."'".$CONF['url']."'".';" class="input_cancel">設定メニューへ</button>'.
		    '</div></div>'.
		    '<div style="clear: both;"></div>';
	else
		$content = "設定の保存に失敗しました。";
	
		mysql_connect($conf['conf_mysql']['server'], $conf['conf_mysql']['user'], $conf['conf_mysql']['passwd']);
		mysql_select_db($conf['conf_mysql']['database']);
		
		if($_REQUEST['db_reset']){
		$table_file = fopen('table.sql','r');
		$buf = '';
		while($line = fgets($table_file)){
			$buf .= $line; 
			if(preg_match("/;[[:space:]]*$/",$line)){
				mysql_query($buf);
				$buf='';
			}
		}
		
		$table_file = fopen('initdata.sql','r');
		$buf = '';
		while($line = fgets($table_file)){
			$buf .= $line; 
			if(preg_match("/;[[:space:]]*$/",$line)){
				mysql_query($buf);
				$buf='';
			}
		}

		//	各種モジュールの初期化処理
		$modules = array( "memo", "contact", "ml", "enquete", "filebox" );

		foreach ( $modules as $mod ) {

			ModuleManager::getInstance()->getModule( $mod )
				->execCallBackFunction( "install", array(), $result );

		}
		
		mysql_query( "INSERT INTO options ( option_key, option_value )"
					." VALUES ( 'version', '".SOURCE_VERSION."' )" );
							
		mysql_query( "INSERT INTO filebox_config (disk_quota,user_quota)"
					." VALUES (65535*65535, 100*1024*1024)" );
							
		mysql_query('insert into element_sequence (id) values(10001)');
		mysql_query('insert into user_sequence (id) values(1001)');
		mysql_query('insert into group_sequence (id) values(10004)');
	
		mysql_query('insert into element (id, unit) values(0, 0)');
		mysql_query('insert into owner (id, uid, gid) values(0, 0, 0)');
		mysql_close();
	}
	show_conf_page(array('id'=>1, 'title'=>'データベースの設定', 'content'=>$content));
	exit(0);
}

function db_input(){
	global $CONF,$content,$conf_mysql,$conf,$conf_error,$error,$conf_sections;

	if(isset($conf['conf_mysql'])){
		if(!isset($conf_mysql))$conf_mysql = $conf['conf_mysql'];
		foreach($conf['conf_mysql'] as $key => $val)$c_conf[$key]="(現在の設定値:$val)";
	}

	if(isset($error['conf_mysql']))$content .= $error['conf_mysql'];
	elseif(isset($conf_error['conf_mysql']))$content .= $conf_error['conf_mysql'];
	
	$content .= <<< __HTML__
<div class="input_wrap"><div class="input">
<form method='GET' class="input_form">
<input type='hidden' value='db_regist' name='act'>
<div style="clear: both"></div>
<h3 class="input_title">mysql サーバー名$c_conf[server]</h3>
<div class="input_body"><input type='text' name='db_server' class="input_text" value="$conf_mysql[server]"></div>
<h3 class="input_title">mysql 接続ユーザー名$c_conf[user]</h3>
<div class="input_body"><input type='text' name='db_user' class="input_text" value="$conf_mysql[user]"></div>
<h3 class="input_title">mysql 接続パスワード$c_conf[passwd]</h3>
<div class="input_body"><input type='text' name='db_passwd' class="input_text" value="$conf_mysql[passwd]"></div>
<h3 class="input_title">mysql データベース名$c_conf[database]</h3>
<div class="input_body"><input type='text' name='db_database' class="input_text" value="$conf_mysql[database]"></div>
<h3 class="input_title">データベースの初期化</h3>
新しくサイトを立ち上げるときは、必ず初期化してください。<BR>以前に同じバージョンのｅコミを使用したことがあり、そのデータを利用したい場合は、初期化しないでください。
<div class="input_body"><input type='checkbox' name='db_reset' class="input_text" value="1" selected>データベースを初期化する</div>
<div style="clear: both"></div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
	<input type="submit" id="submit_0" value="設定" class="input_submit">
	<button onClick="location.href='${CONF['url']}'; return false;" class="input_cancel">設定メニューへ</button>
</div></div>
</form>
</div></div>
__HTML__;

	show_conf_page(array('id'=>1, 'title'=>'データベースの設定', 'content'=>$content));
	exit(0);
}

function check_conf_mysql($conf_mysql){
	global $error;
	if(!mysql_connect($conf_mysql['server'], $conf_mysql['user'], $conf_mysql['passwd'])){
		$error['conf_mysql'] .= "<div class='input_error'>mysqlサーバーに接続できません。<br>サーバー名・ユーザー名・パスワードが正しいか確認してください。</div>\n";
		return false;
	}
	if(!mysql_select_db($conf_mysql['database'])){
		$error['conf_mysql'] .= "<div class='input_error'>データベースにアクセスできません。<br>データベース名が正しいか、指定したmysql接続ユーザ名でデータベースにアクセスできるか確認してください。</div>\n";
		mysql_close();
		return false;
	}
	mysql_close();
	return true;
}
function write_ini($data){
	global $error,$CONF;
	$fh = fopen($CONF['file'],'w');
	if($fh === false){
		$error .= "「${CONF['file']}」を作成できません";
		return false;
	}
	fwrite($fh,";<?/*\n");
	foreach($data as $sec_name => $sec_data){
		fwrite($fh,"[$sec_name]\n");
		foreach($sec_data as $key => $val)
			fwrite($fh, $key.'="'.addslashes($val).'"'."\n");
	}
	fwrite($fh,";*/?>\n");
	fclose($fh);
	chmod($CONF['file'],0777);
	return true;
}

function show_conf_page($data){
	global $CONF,$basedir,$conf,$conf_error,$conf_sections;

	$space_1[] = $data;

	if(isset($conf['conf_host']['site_name'])){
		$site_name = $conf['conf_host']['site_name'];
		$content =  "<h3>$site_name<br>(${_SERVER['SERVER_NAME']})</h3>";
	}
	else{
		$site_name = $_SERVER['SERVER_NAME'];
		$content = "<h3>$_SERVER[SERVER_NAME]</h3>";
	}

	$content .= conf_url_list();
	
	$space_2[] = array('id'=>2, 'title'=>'サイト基本設定メニュー', 'content'=>$content);
	
	require_once $basedir. '/lib/Smarty.class.php';
	
	// smartyのためのディレクトリを用意
	$tmpname = tempnam('','ecomsetup');//ユニークなディレクトリ名を生成するために使用。ファイル自体は使わない。
	if(mkdir($tmpname.'_dir')){
		mkdir($tmpname.'_dir/compile');
		mkdir($tmpname.'_dir/config');
		mkdir($tmpname.'_dir/cache');
	}else{
		$tmpname = tempnam($CONF['dir'],'ecomsetup');
		if(mkdir($tmpname.'_dir')){
			mkdir($tmpname.'_dir/compile');
			mkdir($tmpname.'_dir/config');
			mkdir($tmpname.'_dir/cache');
		}else{
			die('テンポラリディレクトリの作成に失敗しました');
		}
	}

	$smarty = new Smarty;
	
	//上で作成したディレクトリを指定
	$smarty->template_dir = $basedir;
	$smarty->compile_dir  = $tmpname.'_dir/compile';
	$smarty->config_dir   = $tmpname.'_dir/config';
	$smarty->cache_dir    = $tmpname.'_dir/cache';
	
	$smarty->caching = false;
	$smarty->compile_check = true;
	
	//space_1,space_2の内容を設定
	$smarty->assign('space_1',$space_1);
	$smarty->assign('space_2',$space_2);
	
	//contentsを設定
	$contents = $smarty->fetch($basedir.'/layout/2column_840.tpl');
	
	$smarty->clear_all_assign();
	
	$smarty->assign('contents',$contents);
	
	$smarty->assign('page_title','e-community サイト初期設定');
	$smarty->assign('site_name',$site_name);
	
	//スキンの設定
	$skin_filename   = 'edit';
	$layout_filename = '2column_840';
	
	$COMUNI_HEAD_CSS[] = '../../skin/edit/edit.css';
	$COMUNI_HEAD_CSS[] = '../../layout/2column_840.css';
	$smarty->assign('head_css', $COMUNI_HEAD_CSS);
	
	
	
	//表示
	$smarty->display($basedir.'/skin/edit/edit.tpl');
	
	//smarty用ディレクトリの削除
	rmhier($tmpname);
	rmhier($tmpname.'_dir');
}
function conf_url_list(){
	global $conf,$conf_sections,$conf_error,$complete;
	$ok = true;
	$content = "<ul>\n";
	foreach($conf_sections as $section => $name){
		$content .= "<li><a href='?act=$section'>${name}の設定</a><br>";
		if(!$conf[$section]){
			$content .= '&nbsp;未設定<br>';
			if($section != 'conf_post')
				$ok=false;
		}elseif($conf_error[$section]){
			$content .= '&nbsp;設定エラー<br>';
			$ok=false;
		}else {
			$content .= '&nbsp;設定済み<br>';
		}
		$content .= "</li>\n"; 
	}
	if($ok and !check_user())$content .= "<li><a href='?act=user'>初期ユーザー登録</a><br>&nbsp;未設定<br>";
	elseif($ok)$content .= "<li>初期ユーザー登録<br>&nbsp;設定済み<br>";
	else $content .= "<li>初期ユーザー登録<br>&nbsp;他の設定を先に行ってください<br>";
	$content .= "</ul>\n";
	if($ok and check_user())$complete = true;
	else $complete = false;
	return $content;
}
function check_user(){
	global $conf;
	mysql_connect($conf['conf_mysql']['server'], $conf['conf_mysql']['user'], $conf['conf_mysql']['passwd']);
	mysql_select_db($conf['conf_mysql']['database']);
	$res = mysql_query("select * from user limit 1");
	mysql_close();
	if(mysql_num_rows($res))return true;
	else return false;
}
function rmhier($file){
	if(is_dir($file)){
		$dir = opendir($file);
		while(($f = readdir($dir)) !== false)if($f!='.' and $f!='..')rmhier($file.'/'.$f);
		rmdir($file);
	}else{
		unlink($file);
	}
}
function enclose_input_error($str){
	return "<div class='input_error'>$str</div>";	
}
?>
