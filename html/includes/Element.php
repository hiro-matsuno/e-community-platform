<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/Exception.php";
require_once dirname(__FILE__)."/User.php";
require_once dirname(__FILE__)."/Group.php";
require_once dirname(__FILE__)."/Permission.php";
require_once dirname(__FILE__)."/misc.php";

//-----------------------------------------------------
// * 記事 ID から所有者のユーザー ID を検索
//-----------------------------------------------------
function get_uid($eid = null) {

	try {

		if ( null !== $eid ) {

			$element = new Element( $eid );
			return $element->getUid();
			
		}

	} catch ( Exception $e ) {}

	return 0;

}

//-----------------------------------------------------
// * 記事 ID から所属するのグループ ID を検索
//-----------------------------------------------------
function get_gid($eid = null) {

	try {

		if ( null !== $eid ) {
			$element = new Element( $eid );
			return ( $element->getGid() );
		}

	} catch ( Exception $e ) {}

	return 0;

}

//-----------------------------------------------------
// * 記事 ID の所有ユーザー、所属グループを検索
//-----------------------------------------------------
function set_eid_info($eid = null) {

	if ( null !== $eid ) {
		new Element( $eid );
	}

}

//-----------------------------------------------------
// * 記事 ID に対する閲覧権限の取得
//-----------------------------------------------------
function get_pmt($eid = null) {

	try {

		if ( null !== $eid ) {
			$element = new Element( $eid );
			return $element->getUnit();
		}

	} catch ( Exception $e ) {}

	// default: internet
	return 0;

}

//-----------------------------------------------------
// * $id の所有者かどうか
//-----------------------------------------------------
function is_owner($eid = 0, $level = 50) {

	if(is_su()){
		return true;
	}

	try {

		if ( 0 == $eid ) { throw new Exception(); }

		$me = User::getMe();
		$element = new Element( $eid );
		return ( null != $me
				and $level <= $element->getOwnerLevel( $me ) );

	} catch ( Exception $e ) {
		return false;
	}

}

//-----------------------------------------------------
// * 記事の所有権限のレベルを返す
//-----------------------------------------------------
function owner_level($eid = 0) {

	if(is_su())return Permission::USER_LEVEL_ADMIN;

	try {

		$element = new Element( $eid );
		return $element->getOwnerLevel( User::getMe() );

	} catch ( Exception $e ) {
		return Permission::USER_LEVEL_ANONYMOUS;
	}

}

//-----------------------------------------------------
// * グループに所属する要素かどうか
// * eコミ2.0ではページの管理方法が整理されているので不要かも
//-----------------------------------------------------
function is_group($eid) {

	try {

		if ( null === $eid ) { throw new Exception(); }

		$element = new Element( $eid );
		return ( 0 < $element->getGid() );

	} catch ( Exception $e ) {
		return false;
	}

}

//-----------------------------------------------------
// * パワーユーザ (グループ副管理者) 以上の権限かどうか
//-----------------------------------------------------
function is_poweruser($eid = 0) {

	try {
		$element = new Element( $eid );
		return Permission::USER_LEVEL_POWERED <= $element->getOwnerLevel( User::getMe() );
	} catch ( Exception $e ) {
		return false;
	}

}


/**
 * システム内で管理されるオブジェクトの基底クラス.
 *
 * @author ikeda
 */
class Element implements MySqlRecord {

	const DATABASE = "element";

	protected $r_id;
	protected $r_unit;

	protected $owner;

	protected $unitSubs;

	/**
	 * Element::permission がPMT_BROWSE_FOR_GROUPの場合に値が入る.
	 * データベースの unit_sub.id に対応する.
	 * @var Number
	 */
	protected $unitSubId;

	/**
	 * コンストラクタ.
	 * 引数 $data が ID の場合は、該当レコードをデータベースからロードする.
	 * 連想配列の場合はメンバに値を代入する.
	 *
	 * @global Array $SYS_UID eid をキーとする配列. 所有者 uid のキャッシュを保持.
	 * @global Array $SYS_GID eid をキーとする配列. 所有者 gid のキャッシュを保持.
	 *
	 * @param mixed $data Block ID または連想配列.
	 * @param MySqlConnection $connection データベース操作に利用する接続.
	 *
	 * @throws SQLException データベースからのデータ取得に失敗した.
	 */
	public function __construct( $data=null, $connection=null ) {

		if ( null !== $data ) {

			$className = get_class( $this );
			$db_vars = call_user_func( array( $className, "getMemberNames" ), $className );

			if ( !is_array( $data ) ) {

				if ( is_string( $data ) ) { $data = MySqlUtil::decorateText( $data ); }

				$stat = new MySqlPlaneStatement( "select e.id, unit, uid, gid from ".Element::DATABASE." as e"
													." left join ".Owner::DATABASE." as o"
													." on o.id=e.id"
													." where e.".$this->getKeyName()."=".$data );

				$data = mysql_fetch_assoc( $stat->exec()->getResult() );

				if ( false === $data ) { throw new DataNotFoundException(); }

			}

			foreach ( $db_vars as $key ) {

				if ( !isset( $data[$key] ) ) { continue; }
				$value = $data[$key];

				$methodName = MySqlUtil::getSetterName($className,$key);
				$this->$methodName( $value );

			}

			if ( 0 != ( $this->r_unit & ~Permission::PMT_BROWSE_MASK ) ) {
				$this->unitSubId = $this->r_unit;
			}

			$this->owner = new Owner( $data );

			$this->unitSubs = $this->createUnitSubs();

			global $SYS_UID, $SYS_GID;

			if (count($SYS_UID) > 256) {
				unset($SYS_UID);
				unset($SYS_GID);
			}

			if ( null !== $this->r_id
					and null !== ( $uid = $this->owner->getUid() )
					and null !== ( $gid = $this->owner->getGid() ) ) {

				$SYS_UID[$this->r_id] = $uid;
				$SYS_GID[$this->r_id] = $gid;

			}

		} else {

			$this->owner = new Owner();
			$this->unitSubs = array();

		}

	}
	
	private function createUnitSubs() {

		if ( 0 != $this->r_unit ) {

			$stat = new MySqlSelectStatement( "UnitSub" );
			$stat->setOtherConditions( "where id=".$this->r_unit );

			return $stat->exec()->getDatas();

		} else {

			return array();
			
		}

	}

	public function getId() { return $this->r_id; }
	public function getUnit() { return $this->r_unit; }

	/**
	 * @deprecated Element::getId() を利用してください.
	 */
	public function getEid() { return $this->getId(); }

	/**
	 * uid を取得する.
	 * @return Number
	 */
	public function getUid() { return $this->owner->getUid(); }

	/**
	 * gid を取得する.
	 * @return Number
	 */
	public function getGid() { return $this->owner->getGid(); }

	/**
	 * 公開権限を取得する.
	 * @return Number Permission::PMT_BROWSE_*
	 * @see Permission
	 */
	public function getPermission() {

		if ( null !== $this->r_unit ) {

			if ( Permission::PMT_BROWSE_PUBLIC !== $this->r_unit
				and Permission::PMT_BROWSE_PRIVATE !== $this->r_unit
				and Permission::PMT_BROWSE_FOR_AUTHORIZED !== $this->r_unit ) {

				return (int)Permission::PMT_BROWSE_FOR_GROUP;

			} else

				return $this->r_unit;

		} else {

			//	@TODO DBから取得できなかった場合、エラーか非公開にするべきでは
			// default: internet
			return (int)Permission::PMT_BROWSE_PUBLIC;

		}

	}

	public function getAllowGroups() {

		$array = array();

		foreach ( $this->unitSubs as $unitSub ) {
			if ( !$unitSub->getDelete() ) {
				$array[] = $unitSub->getGid();
			}
		}

		return $array;
	}

	public function setId( $id ) { $this->r_id = (int)$id; }
	public function setUnit( $unit ) { $this->r_unit = (int)$unit; }

	public function setUid( $uid ) { $this->owner->setUid( $uid ); }
	public function setGid( $gid ) { $this->owner->setGid( $gid ); }
	public function setPermission( $permission ) {

		if ( 0 == ( $permission & ~Permission::PMT_BROWSE_MASK ) ) {
			$this->setUnit( $permission );
		} else if ( null === $this->unitSubId ) {
			$this->setUnit( get_seqid() );
			$this->unitSubId = $this->getUnit();
		} else {
			$this->setUnit( $this->unitSubId );
		}

	}

	static public function getMemberNames() { return array( "id", "unit" ); }
	static public function getTableName() { return Element::DATABASE; }
	static public function getKeyName() { return "id"; }

	public function addAllowGroup( $group ) {

		$gid = ( is_a( $group, "Group" ) ) ? $group->getGid() : $group;

		if ( 0 > $this->searchAllowGroup( $gid ) ) {

			if ( null === $this->unitSubId ) { $this->unitSubId = get_seqid(); }

			$unitSub = new UnitSub( array( "id" => $this->unitSubId,
											"gid" => $gid ) );
			$unitSub->setNew( true );

			$this->unitSubs[] = $unitSub;

			return true;

		} else {

			return false;

		}
		
	}

	public function removeAllowGroup( $group ) {

		$gid = ( is_a( $group, "Group" ) ) ? $group->getGid() : $group;

		if ( 0 <= ( $index = $this->searchAllowGroup($gid) ) ) {

			$this->unitSubs[ $index ]->setDelete( true );
			return true;

		} else {

			return false;
			
		}

	}

	public function searchAllowGroup( $gid ) {

		$i = 0;

		foreach ( $this->unitSubs as $unitSub ) {

			if ( $unitSub->getGid() === $gid ) { return $i; }

			++$i;

		}

		return -1;

	}

	public function regist( $connection=null ) {

		if ( null === $this->r_id ) { $this->setId( get_seqid() ); }

		$stat = new MySqlRegistStatement( $this, $connection );
		$stat->exec();

		$this->owner->setId( $this->r_id );
		$this->owner->regist( $connection );

		foreach ( $this->unitSubs as $unitSub ) {

			if ( $unitSub->getDelete() ) {
				$unitSub->delete( $connection );
			} else if ( $unitSub->getNew() ) {
				$unitSub->regist( $connection );
				$unitSub->setNew( false );
			}

		}

	}

	public function delete() {

		$stat = new MySqlDeleteStatement( $this );
		$stat->exec();

		$this->owner->delete();

		if ( null !== $this->unitSubId ) {

			MySqlPlaneStatement::execNow( "delete from ".UnitSub::DATABASE
											." where id=".$this->unitSubId );
			
		}

	}

	/**
	 * user がこのオブジェクトに対して持っている権限レベルを返す.
	 * Permission::USER_LEVEL_* のいずれかの値が返る.
	 * @param mixed $user user id あるいは User オブジェクト.
	 * @return Number
	 * @see Permission
	 */
	public function getOwnerLevel( $user ) {

		if ( isset( $SYS_OWNER_LEVEL[$this->eid] ) ) {
			return $SYS_OWNER_LEVEL[$this->eid];
		} else {

			$uid = ( is_a( $user, "User" ) ) ? $user->getUid() : $user;

			$level = null;

			if ( $this->getUid() === $uid ) {
				$level = Permission::USER_LEVEL_ADMIN;
			} else {
				$group = new Group( $this->getGid() );
				$level = $group->getUserLevel( $uid );
			}

			if ( null != $this->r_id ) {
				$SYS_OWNER_LEVEL[$this->r_id] = $level;
			}

			return $level;

		}

	}

	/**
	 * オブジェクトの比較.
	 * 
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

		return $this->owner->equals( $obj->owner );

	}

}

class Owner extends MySqlData {

	const DATABASE = "owner";

	protected $r_id;
	protected $r_uid;
	protected $r_gid;

	public function getId() { return $this->r_id; }
	public function getUid() { return $this->r_uid; }
	public function getGid() { return $this->r_gid; }

	public function setId( $id ) { $this->r_id = (int)$id; }
	public function setUid( $uid ) { $this->r_uid = (int)$uid; }
	public function setGid( $gid ) { $this->r_gid = (int)$gid; }

	static public function getTableName() { return Owner::DATABASE; }
	static public function getKeyName() { return "id"; }

}

class UnitSub extends MySqlData {

	const DATABASE = "unit_sub";

	protected $r_id;
	protected $r_gid;

	private $delete;
	private $new;

	public function getId() { return $this->r_id; }
	public function getGid() { return $this->r_gid; }

	protected function setId( $id ) { $this->r_id = (int)$id; }
	protected function setGid( $gid ) { $this->r_gid = (int)$gid; }

	static public function getTableName() { return UnitSub::DATABASE; }
	static public function getKeyName() { return "id"; }

	public function setDelete( $delete ) { $this->delete = $delete; }
	public function setNew( $new ) { $this->new = $new; }

	public function getDelete() { return $this->delete; }
	public function getNew() { return $this->new; }

	public function regist( $connection=null) {

		$stat = new MySqlSelectStatement( "UnitSub", $connection );
		$stat->setOtherConditions( "where id={$this->r_id} and gid={$this->gid}" );

		if ( 0 == $stat->exec()->getNumDatas() ) {

			MySqlPlaneStatement::execNow( "insert into ".UnitSub::DATABASE
											." values( id, gid )"
											." ( {$this->r_id}, {$this->gid} )",
											$connection );

		}

	}

	public function delete( $connection=null ) {

		MySqlPlaneStatement::execNow( "delete from ".UnitSub::DATABASE
										."where id={$this->r_id} and gid={$this->gid}",
										$connection );

	}

}

?>
