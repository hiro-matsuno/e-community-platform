<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

class LoginProcess {

	private $account;
	private $passwd;

	public function __construct( $account, $passwd ) {

		$this->account = $account;
		$this->passwd = $passwd;

	}

	public function exec() {
		
		//	モジュールのコールバック関数を呼び出し.
		$results = ModuleManager::getInstance()
			->execCallbackFunctions( "pre_login", array( $this->account, $this->passwd ) );

		$id = null;

		foreach ( $results as $res ) {

			if ( null !== $res ) { $id = $res; }

		}
		
		$user = null;

		if ( $id !== null ) {

			//	モジュール関数で認証が完了した.
			
			if ( 0 < $id ) {
				$user = new User( $id );
			}

		} else {

			//	モジュール関数で認証されていない.

			//	eコミの認証動作を行なう.
			$user = $this->checkLogin();

		}

		if  ( 0 > $user->getEnable() ) {
			show_error('あなたは現在、諸事情によりアカウント停止中です。');
		}

		if ( null !== $user ) {
			$this->login( $user );
		}

		//	モジュールのコールバック関数を呼び出し.
		ModuleManager::getInstance()
			->execCallbackFunctions( "post_login", array( (int)$id ) );

//あしあと
//			$a = mysql_uniq("select * from access_log_pmt where uid = %s", mysql_num($f[id]));
//			$pmt = isset($a['pmt']) ? $a['pmt'] : 0;
//			if ($pmt > 0) {
//				$_SESSION['_allow_logging'] = false;
//			}
//			else {
//				$_SESSION['_allow_logging'] = true;
//			}
		
		return ( null !== $user );

	}

	function checkLogin() {

		$stat = new MySqlSelectStatement( "User" );
		$stat->setOtherConditions( "where email="
								.MySqlUtil::decorateText( $this->account ) );

		$users = $stat->exec()->getDatas();

		return ( 0 < count( $users )
				and $users[0]->getPassword() == md5( $this->passwd ) )
				? $users[0] : null;
			
	}

	public function login( $user ) {
		
		$_SESSION[_uid]      = $user->getId();
		$_SESSION[_nickname] = $user->getHandle();

		if($user->getLevel() >= 100){
			$_SESSION[_is_superuser] = true;
			$_SESSION[_is_admin] = true;
		}

	}

	public function redirect( $type, $ref ) {

		if ($ref && $type == 'dialog') {
			if (preg_match('/login\.php/', $ref)) {
				echo '<html><body><script type="text/javascript">parent.location.href = \''.
					 CONF_SITEURL. '\';'.
					 '</script>ログイン中...</body></html>';
				return;
			}
			echo '<html><body><script type="text/javascript">parent.location.reload();'.
				 '</script>ログイン中...</body></html>';
			return;
		}

		if ($ref != '') {
			header('Location: '. CONF_URLBASE. $ref);
		}
		else if (isset($_SESSION["return"])) {
			$jump = $_SESSION["return"];
			unset($_SESSION["return"]);
			header('Location: '. $jump);
		}
		else {
			header('Location: '. CONF_SITEURL);
		}
		
	}

}

interface LoginDisplay {

	public function getLostPasswdTag();
	public function getType();
	public function display( $content );

}

class LoginDialog implements LoginDisplay {

	public function __construct() {}

	public function getType() { return "dialog"; }

	public function getLostPasswdTag() {

		$tb_class = '';
		$lost_passwd = Path::makeURL( '/passwd_lost.php' );
		return "<a href=\"${lost_passwd}\"${tb_class}>"
			."パスワードを忘れてしまったら？"
			."</a>";
		
	}

	public function display( $content ) {

		$data = array( 'title'   => 'ログインが必要です。',
					  'icon'    => 'notice',
					  'content' => $content );
		show_dialog2($data);
		
	}

}


class LoginPage implements LoginDisplay {

	private $pageId;

	public function __construct( $pageId ) { $this->pageId = $pageId; }

	public function getType() { return "page"; }

	public function getLostPasswdTag() {

		$tb_class = 'class=" thickbox"';
		$lost_passwd = thickbox_href(CONF_URLBASE. '/passwd_lost.php');
		return "<a href=\"${lost_passwd}\"${tb_class}>"
			."パスワードを忘れてしまったら？"
			."</a>";

	}

	public function display( $content ) {

		$data = array( "space_1" => array( array('title'   => 'ログインが必要です。',
											  'icon'    => 'notice',
											  'content' => $content ) ) );
		
		global $COMUNI;
		$COMUNI["columns"] = 1;
		show_page( $this->pageId, $data );

	}

}

class LoginForm {

	private $message;
	private $ref;

	public function __construct( $ref, $message=
			"ログインするためにはメールアドレスとパスワードを入力してください。" ) {
		$this->message = $message;
		$this->ref = $ref;
	}

	public function display( $display ) {

		$lostPasswdTag = $display->getLostPasswdTag();
		$type = $display->getType();

		$content = <<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #ffffff;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
</style>
{$this->message}
<div style="margin: auto; text-align: center;" align="center">
<form action="login.php" method="POST">
<input type="hidden" name="action" value="login">
<input type="hidden" name="type" value="${type}">
<input type="hidden" name="ref" value="{$this->ref}">
<table class="form_table" style="margin: 0 auto; text-align: center;">
<tr>
<th>メールアドレス</th>
<td><input type="text" name="email" class="input_text" size="40"></td>
</tr>
<tr>
<th>パスワード</th>
<td><input type="password" name="password" class="input_text" size="30"></td>
</tr>
</table>
<br>
<input type="submit" value="ログイン" class="input_submit">
</form>
</div>
<br>
<div style="text-align: right;">
&raquo; ${lostPasswdTag}
</div>
__HTML__;

		$display->display( $content );

	}

}

class LoginWait {

	private $ref;

	public function __construct( $ref ) {
		$this->ref = $ref;
	}

	public function display( $display ) {

		if ($ref && $type == 'dialog') {
			if (preg_match('/login\.php/', $ref)) {
				echo '<html><body><script type="text/javascript">parent.location.href = \''.
					 CONF_SITEURL. '\';'.
					 '</script>ログイン中...</body></html>';
				exit(0);
			}
			echo '<html><body><script type="text/javascript">parent.location.reload();'.
				 '</script>ログイン中...</body></html>';
			exit(0);
		}

		if ($ref != '') {
			header('Location: '. CONF_URLBASE. $ref);
		}
		else if (isset($_SESSION["return"])) {
			$jump = $_SESSION["return"];
			unset($_SESSION["return"]);
			header('Location: '. $jump);
		}
		else {
			header('Location: '. CONF_SITEURL);
		}

	}

}


$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$a   = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;

$type = isset($_REQUEST['type']) ? htmlesc($_REQUEST['type']) : '';
$ref  = isset($_REQUEST['ref'])  ? urldecode($_REQUEST['ref']) : '';

if (is_login()) {
	header('Location: '. CONF_SITEURL);
	exit(0);
}

$display = null;

if ( "dialog" == $type ) {
	$display = new LoginDialog();
} else {

	$page = Page::getPortalPage();

	try {

		if ( preg_match( "/gid=(\d+)/", $ref, $match ) ) {
			$page = Page::createInstanceFromGid( $match[1] );
		} else if ( preg_match( "/uid=(\d+)/", $ref, $match ) ) {
			$page = Page::createInstanceFromUid( $match[1] );
		}

	} catch ( Exception $e ) {}

	$display = new LoginPage( $page->getId() );

}

if ($act == 'login') {

	$account = $_POST[email];
	$passwd = $_POST[password];

	$login = new LoginProcess( $account, $passwd );

	if ( $login->exec() ) {

//		$timer = new LoginWait( $ref );
//		$timer->display( new LoginDialog() );

		$login->redirect( $type, $ref );

	} else {

		$message = "登録されていないユーザー、もしくはメールアドレス、パスワードが違います。<br><br>"
				."以下から正しいメールアドレスとパスワードを入力してログインしてください<br>";
		$form = new LoginForm( $ref, $message );
		$form->display( $display );

	}

} else {

	$form = new LoginForm( $ref );
	$form->display( $display );

}
?>
