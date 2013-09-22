<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/User.php";
require_once dirname(__FILE__)."/Exception.php";

//-----------------------------------------------------
// * 閲覧権限のカスタムグループにユーザーを追加
//-----------------------------------------------------
function set_unit($gid = null, $uid = null) {

	if (!$gid || !$uid) {
		return;
	}

	try {

		$group = new Group( $gid );
		$user = new User( $uid );

		$group->addVisitor($user);

	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}

}

//-----------------------------------------------------
// * 閲覧権限のカスタムグループからユーザーを削除
//-----------------------------------------------------
function unset_unit($gid = null, $uid = null) {

	if (!$gid || !$uid) {
		return;
	}

	try {

		$group = new Group( $gid );
		$user = new User( $uid );

		$group->removeVisitor($user);

	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}

}

//-----------------------------------------------------
// * グループにユーザーを追加
//-----------------------------------------------------
function join_group($param) {

	$gid   = $param["gid"];
	$uid   = $param["uid"];
	$level = isset($param["level"]) ? $param["level"] : Permission::USER_LEVEL_AUTHORIZED;

	try {

		$group = new Group( $gid );
		$user = new User( $uid );

		if ( !$group->addMember($user,$level) ) {
			die("すでに参加しています。");
		}

		$group->addVisitor($user);

		//	モジュールコールバックを呼び出し.
		ModuleManager::getInstance()
			->execCallbackFunctions( "group_join", array( $uid, $gid ) );

	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}

}


//-----------------------------------------------------
// * グループ参加中かどうか
//-----------------------------------------------------
function is_joined($gid = 0) {
	if (join_level($gid) > Permission::USER_LEVEL_ANONYMOUS) {
		return true;
	}
	return false;
}

//-----------------------------------------------------
// * グループ参加レベルの取得
//-----------------------------------------------------
function join_level($gid = 0) {

	if(is_su())return Permission::USER_LEVEL_ADMIN;

	if ($gid == 0) {
		return Permission::USER_LEVEL_ANONYMOUS;
	}
	
	try {
		$group = new Group( $gid );
		return $group->getUserLevel( User::getMe() );
	} catch ( Exception $e ) {
		return Permission::USER_LEVEL_ANONYMOUS;
	}

}

//-----------------------------------------------------
// * グループから脱退
//-----------------------------------------------------
function disjoin_group($param) {

	$gid   = $param["gid"];
	$uid   = $param["uid"];

	if (!$gid || !$uid) {
		return;
	}

	try {

		$group = new Group( $gid );
		$user = new User( $uid );

		$group->removeMember($user);

		$group->removeVisitor($user);

		//	モジュールコールバックを呼び出し.
		ModuleManager::getInstance()
			->execCallbackFunctions( "group_leave", array( $uid, $gid ) );

	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}

}


/**
 * グループを表すオブジェクト.
 *
 * @author ikeda
 */
class Group {

	/**
	 * グループメンバを格納するデータベーステーブル名称.
	 */
	const DATABASE_UNIT = "unit";

	/**
	 * グループメンバのグループ内権限を格納するデータベーステーブル名称.
	 */
	const DATABASE_MEMBER = "group_member";

	/**
	 * グループID.
	 * @var Number
	 */
	private $gid;

	/**
	 * コンストラクタ.
	 * @param Number $gid
	 */
	public function __construct( $gid ) {
		$this->gid = $gid;
	}

	/**
	 * グループIDを取得する.
	 * @return Number
	 */
	public function getGid() {
		return $this->gid;
	}

	/**
	 * 比較関数.
	 * @param Group $obj
	 * @return bool
	 */
	public function equals( $obj ) {
		return ( $this->gid === $obj->gid );
	}

	/**
	 * グループメンバを加える.
	 * @param mixed $user user id または User object.
	 * @return bool すでにメンバであった場合は false.
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function addVisitor( $user ) {

		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

		$result = mysql_exec( "select * from ".Group::DATABASE_UNIT
							." where id = %s and uid = %s",
							mysql_num($gid), mysql_num($uid) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( 0 == mysql_num_rows( $result ) ) {

			if ( !mysql_exec( "insert into ".Group::DATABASE_UNIT
							." ( id, uid ) values ( %d, %d )",
							mysql_num($gid), mysql_num($uid) ) ) {
				throw new SQLException( mysql_error() );
			}

			return true;

		} else {
			return false;
		}
		
	}

	/**
	 * グループメンバを削除する.
	 * @param mixed $user User id または User object.
	 * @attention 該当ユーザがメンバでなく、削除失敗したことを検出できない.
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function removeVisitor( $user ) {
		
		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

		if ( !mysql_exec( "delete from ".Group::DATABASE_UNIT
						." where id=%d and uid=%d",
						mysql_num( $gid ), mysql_num( $uid ) ) ) {
			throw new SQLException( mysql_error() );
		}

	}

	/**
	 * グループメンバ権限の設定を行う.
	 * @param mixed $user user id または User object.
	 * @param Number $level
	 * @return bool true
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function addMember( $user, $level ) {
		
		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

		$result = mysql_exec( "select uid from ".Group::DATABASE_MEMBER
							." where gid = %d and uid = %d",
							mysql_num($gid), mysql_num($uid) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( 0 === mysql_num_rows( $result ) ) {

			if ( !mysql_exec( "insert into ".Group::DATABASE_MEMBER
								." ( gid, uid, level )"
								." values ( %d, %d, %d );",
								mysql_num($gid), mysql_num($uid), mysql_num($level) ) ) {
				throw new SQLException( mysql_error() );
			}

			return true;

		} else {

			if ( !mysql_exec( "update ".Group::DATABASE_MEMBER
								." set level=%d"
								." where gid=%d and uid=%d",
								mysql_num($level), mysql_num($gid), mysql_num($uid) ) ) {
				throw new SQLException( mysql_error() );
			}

			return false;

		}

	}

	/**
	 * 該当ユーザのグループ権限を削除する.
	 * @param mixed $user user id または User object.
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function removeMember( $user ) {

		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

		if ( !mysql_exec( "delete from ".Group::DATABASE_MEMBER
							." where gid=%d and uid=%d",
							mysql_num( $gid ), mysql_num( $uid ) ) ) {
			throw new SQLException( mysql_error() );
		}

	}

	/**
	 * このグループ内での、Userの権限を返す.
	 * Permission::USER_LEVEL_* のいずれかの値.
	 * @param mixed $user userid または User
	 * @return Number
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function getUserLevel( $user ) {

		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

		$result = mysql_exec( "select level from ".Group::DATABASE_MEMBER
							." where gid=%d and uid=%d",
							mysql_num( $gid ), mysql_num( $uid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array( $result ) ) ) {
			return (int)$row["level"];
		} else {
			return Permission::USER_LEVEL_ANONYMOUS;
		}

	}

	public function hasUser( $user ) {

		$gid = $this->getGid();
		$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;
		
		$result = mysql_exec( "select id from ".Group::DATABASE_UNIT
							." where id=%d and uid=%d",
							mysql_num( $gid ),
							mysql_num( $uid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		return ( 0 < mysql_num_rows($result) );

	}

	/**
	 * このグループが所有するPageオブジェクトを返す.
	 * @return Page または false
	 */
	public function getPage() {

		try {
			$page = Page::createInstanceFromGid( $this->gid );
			return $page;
		} catch ( Exception $e ) {
			return false;
		}

	}

	/**
	 * このグループに属するUserの配列を返す.
	 * @return Array of User
	 */
	public function getMembers() {

		$array = array();

		$result = mysql_exec( "select u.id, u.level, u.handle, u.email, u.password, u.enable"
							." from group_member as m"
							." left join ".User::DATABASE." as u"
							." on u.id=m.uid"
							." where m.gid=%d",
							mysql_num( $this->gid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		while ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$user = new User();

			$user->setUid( $row["id"] );
			$user->setLevel( $row["level"] );
			$user->setHandle( $row["handle"] );
			$user->setEmail( $row["email"] );
			$user->setPassword( $row["password"] );
			$user->setEnable( $row["enable"] );

			$array[] = $user;

		}

		return $array;

	}

}

?>
