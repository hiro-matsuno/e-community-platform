<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/sql/MySqlData.php";
require_once dirname(__FILE__)."/sql/MySqlStatement.php";
require_once dirname(__FILE__)."/Permission.php";
require_once dirname(__FILE__)."/Exception.php";

//-----------------------------------------------------
// * オートログイン (現在は未使用)
//-----------------------------------------------------
function autologin() {
	global $COMUNI;
/*
	if (isset($_COOKIE["CMNALID"]) && $_COOKIE["CMNALID"] != "" ) {
		$f = mysql_uniq('select * from autologin where cid = %s',
						'mysql_str($_COOKIE["CMNALID"]));
		if (!$f) {
			return;
		}
		$_SESSION[_uid] = $f[uid];
	}
*/
	return;
}

//-----------------------------------------------------
// * システム管理者であるかどうかのチェック
//-----------------------------------------------------
function su_check() {
	global $COMUNI;

 	$COMUNI['manager_mode'] = true;

	if (is_su() == true) {
		return true;
	}
	show_error('管理者ではありません。');

	exit(0);
}

//-----------------------------------------------------
// * 運用管理者以上であるかどうかのチェック
//-----------------------------------------------------
function admin_check() {
	global $COMUNI;

	$COMUNI['manager_mode'] = true;

	if (is_admin() == true) {
		return true;
	}
	show_error('管理者ではありません。');

	exit(0);
}

//-----------------------------------------------------
// * システム管理者であるかどうか
//-----------------------------------------------------
function is_su() {

	global $SYS_IS_SU;

	if(isset($SYS_IS_SU)){
		if($SYS_IS_SU)
			return true;
	}else{

		try {

			$me = User::getMe();

			if ( $me and $me->isSu() ) {
				$SYS_IS_SU = true;
				return true;
			} else {
				$SYS_IS_SU = false;
				return false;
			}

		} catch ( Exception $e ) {
			$SYS_IS_SU = false;
			return false;
		}

	}

}

//-----------------------------------------------------
// * 運用管理者以上であるかどうか
//-----------------------------------------------------
function is_admin() {

	global $SYS_IS_SU,$SYS_IS_ADMIN;

	if(isset($SYS_IS_ADMIN))
		return true;
	else{

		if(isset($SYS_IS_SU)) {

			if($SYS_IS_SU){
				$SYS_IS_ADMIN = true;
				return true;
			}

		}

		try {

			$me = User::getMe();
			if ( $me and $me->isAdmin() ) {
				$SYS_IS_ADMIN = true;
				return true;
			} else {
				$SYS_IS_ADMIN = false;
				return false;
			}

		} catch ( Exception $e ) {
			$SYS_IS_ADMIN = false;
			return false;
		}

	}

}

//-----------------------------------------------------
// * ログイン中であるかどうか
//-----------------------------------------------------
function is_login() {
	global $COMUNI;
	if (isset($COMUNI["is_login"]) && ($COMUNI["is_login"] == true)) {
		return true;
	}
	return false;
}

//-----------------------------------------------------
// * ログイン状態/ログアウト状態で適切な閲覧権限を返す
//-----------------------------------------------------
function public_status($id = null) {
	if (isset($id)) {
		if (is_owner($id, 100)) {
			return PMT_CLOSE;
		}
	}
	if (is_login()){
		return PMT_MEMBER;
	}
	else {
		return PMT_PUBLIC;
	}
}

//-----------------------------------------------------
// * ニックネームの取得
//-----------------------------------------------------
function get_nickname($uid = null) {

	try {

		if ( null === $uid ) { throw new Exception(); }

		$user = new User( $uid );
		return $user->getHandle();

	} catch ( Exception $e ) {
		return false;
	}

}

//-----------------------------------------------------
// * 記事所有者のニックネーム取得
//-----------------------------------------------------
function get_writer_name($uid = null, $site_id = null) {
	global $SYS_WRITER_NAME;

	$uniq_id = intval($uid). '_'. intval($site_id);

	if ($SYS_WRITER_NAME[$uniq_id]) {
		return $SYS_WRITER_NAME[$uniq_id];
	}

	$nickname = get_nickname($uid);

	if ($site_id > 0) {
		$d = mysql_uniq("select * from page where id = %s", mysql_num($site_id));
		if ($d) {
			$SYS_WRITER_NAME[$uniq_id] = $nickname. ' ('. $d['sitename']. ')';
		}
		else {
			$SYS_WRITER_NAME[$uniq_id] = $nickname;
		}
	}
	else {
		$SYS_WRITER_NAME[$uniq_id] = $nickname;
	}
	return $SYS_WRITER_NAME[$uniq_id];
}

//-----------------------------------------------------
// * 市民レポーターの管理者かどうか
//-----------------------------------------------------
function is_reporter_admin($eid) {
	$c = mysql_uniq('select * from reporter_block'.
					' inner join blog_data on blog_data.pid = reporter_block.block_id'.
					' where blog_data.id = %s',
					mysql_num($eid));

	if ($c) {
		if (is_owner($c['eid'])) {
			return true;
		}
	}
	return false;
}

//-----------------------------------------------------
// * 防災WEB の管理者かどうか
//-----------------------------------------------------
function is_bosai_web_admin($eid) {
	$c = mysql_uniq('select * from bosai_web_block'.
					' inner join blog_data on blog_data.pid = bosai_web_block.block_id'.
					' where blog_data.id = %s',
					mysql_num($eid));

	if ($c) {
		if (is_owner($c['eid'])) {
			return true;
		}
	}
	return false;
}

//-----------------------------------------------------
// * ページの管理者かどうか
//-----------------------------------------------------
function is_master($param = array()) {
	global $SYS_CACHE;
	global $SYS_VIEW_GID, $SYS_VIEW_UID;
	global $COMUNI_DEBUG;

	if(is_su())return true;

	$check_gid = intval($param['gid']) > 0 ? $param['gid'] : $SYS_VIEW_GID;
	$check_uid = intval($param['uid']) > 0 ? $param['uid'] : $SYS_VIEW_UID;

//	$COMUNI_DEBUG[] = 'SYS_VIEW_GID:'. $SYS_VIEW_GID;
//	$COMUNI_DEBUG[] = 'SYS_VIEW_UID:'. $SYS_VIEW_UID;

	if ($check_gid > 0) {
		if ($SYS_CACHE['is_master'][$check_gid]) {
			return $SYS_CACHE['is_master'][$check_gid];
		}
		$q = mysql_uniq("select * from page where gid = %s", mysql_num($check_gid));
		if ($q) {
			if (is_owner($q['id']) && owner_level($q['id']) == 100) {
				$SYS_CACHE['is_master'][$check_gid] = true;
			}
		}
		return $SYS_CACHE['is_master'][$check_gid];
	}
	else if ($check_uid) {
		if ($SYS_CACHE['is_master'][$check_uid]) {
			return $SYS_CACHE['is_master'][$check_uid];
		}
		$q = mysql_uniq("select * from page where uid = %s", mysql_num($check_uid));
		if ($q) {
			$SYS_CACHE['is_master'][$check_uid] = is_owner($q['id']);
		}
		return $SYS_CACHE['is_master'][$check_uid];
	}
	return false;
}

//-----------------------------------------------------
// * 自身の $uid を返す
//-----------------------------------------------------
function myuid() {
	return get_myuid();
}

//-----------------------------------------------------
// * 自身の $uid を返す
// * 本来 myuid() に統合可
//-----------------------------------------------------
function get_myuid() {
	$me = User::getMe();
	if ( null !== $me ) {
		return $me->getUid();
	} else {
		return 0;
	}
}

/**
 * システムのユーザを表すオブジェクト.
 *
 * @author ikeda
 */
class User extends MySqlData {

	const DATABASE = "user";

	/**
	 * ユーザID.
	 * @var Number
	 */
	private $r_id;

	/**
	 * 管理者レベル.
	 * Permission::USER_LEVEL_*
	 * @var Number
	 * @see Permission
	 */
	private $r_level;

	/**
	 * ニックネーム.
	 * @var String
	 */
	private $r_handle;

	/**
	 * E-mail アドレス.
	 * @var string
	 */
	private $r_email;

	/**
	 * パスワードの md5 ハッシュ値.
	 * @var String
	 */
	private $r_password;

	/**
	 * 有効かどうか.
	 * @var Number
	 * 0 or 1
	 */
	private $r_enable;

	/**
	 * 登録した時間
	 */
	private $r_initymd;

	/**
	 * 更新した時間.
	 */
	private $r_updymd;

	/**
	 * コンストラクタ.
	 * @param number $uid uidを指定した場合、該当するレコードを読み込んでインスタンス化する.
	 *						null の場合は初期値を代入してインスタンス化.
	 */
	public function __construct( $uid=null, $connection=null ) {
		parent::__construct( $uid, $connection );
	}

	/**
	 * ユーザIDを設定する.
	 * @deprecated User::setId を利用してください.
	 * @param Number $uid
	 */
	public function setUid( $uid ) { $this->setId( $uid ); }

	public function setId( $id ) { $this->r_id = (int)$id; }
	public function setLevel( $level ) { $this->r_level = (int)$level; }
	public function setHandle( $handle ) { $this->r_handle = $handle; }
	public function setEmail( $email ) { $this->r_email = $email; }
	public function setPassword( $password ) { $this->r_password = $password; }
	public function setEnable( $enable ) { $this->r_enable = (int)$enable; }
	public function setInitymd( $initymd ) { $this->r_initymd = $initymd; }
	public function setUpdymd( $updymd ) { $this->r_updymd = $updymd; }

	/**
	 * ユーザIDを取得する.
	 * @deprecated User::getId を利用してください.
	 * @return Number uid
	 */
	public function getUid() {
		return $this->getId();
	}

	public function getId() { return $this->r_id; }
	public function getLevel() { return $this->r_level; }
	public function getHandle() { return $this->r_handle; }
	public function getEmail() { return $this->r_email; }
	public function getPassword() { return $this->r_password; }
	public function getEnable() { return $this->r_enable; }
	public function getInitymd() { return $this->r_initymd; }
	public function getUpdymd() { return $this->r_updymd; }

	static public function getTableName() { return User::DATABASE; }
	static public function getKeyName() { return "id"; }

	public function regist() {

		if ( null === $this->r_id ) {
			$this->r_id = get_seqid("user");
		}

		parent::regist();

	}

	/**
	 * このユーザがシステム管理者かどうかを返す.
	 * @return bool
	 */
	public function isSu() {
		return ( Permission::USER_LEVEL_ADMIN === $this->r_level );
	}

	/**
	 * このユーザが運用管理者以上かどうかを返す.
	 * @return bool
	 */
	public function isAdmin() {
		return ( Permission::USER_LEVEL_POWERED <= $this->r_level );
	}

	/**
	 * ログインユーザの uid オブジェクトを取得する.
	 * @return ログインユーザのユーザオブジェクト.
	 * ログイン状態でない場合は null.
	 */
	static public function getMe() {

		global $COMUNI;

		static $me = null;

		if ( null !== $me ) {
			return $me;
		} else if ( isset( $COMUNI["uid"] ) ) {
			return ( $me = new User( $COMUNI["uid"] ) );
		} else {
			return null;
		}

	}

	/**
	 * 登録された全ユーザの配列を返す.
	 * @return array or User
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public static function getUsers() {
		
		$state = new MySqlSelectStatement( "User" );

		return $state->exec()->getDatas();

	}

}
?>
