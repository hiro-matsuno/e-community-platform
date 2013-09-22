<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

//-----------------------------------------------------
// * 現在表示中の page id をロードして $CURRENT_SITE_ID へ保持
//-----------------------------------------------------
function set_current_site_id() {

	global $CURRENT_SITE_ID;

	try {

		if (!isset($CURRENT_SITE_ID)) {
			if (isset($_REQUEST['gid'])) {
				$page = Page::createInstanceFromGid($_REQUEST['gid']);
				$CURRENT_SITE_ID = $page->getId();
			}
			else if (isset($_REQUEST['uid'])) {
				$page = Page::createInstanceFromUid($_REQUEST['uid']);
				$CURRENT_SITE_ID = $page->getId();
			}
			else {
				$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) :
						(isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) :
						(isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0));

				if ($id > 0) {
					$CURRENT_SITE_ID = get_site_id($id);
				}
				else {
					$page = Page::createInstanceFromGid( portal_gid() );
					$CURRENT_SITE_ID = $page->getId();
				}
			}
		}

	} catch ( Exception $e ) {}

	return $CURRENT_SITE_ID;
	
}

//-----------------------------------------------------
// * 記事ID からページIDを取得
//-----------------------------------------------------
function get_site_id($id) {
	
	global $SYS_SITE_ID;

	if ($SYS_SITE_ID[$id]) {
		return $SYS_SITE_ID[$id];
	}

	try {

		$element = new Element( $id );

		if ( $element->getGid() ) {

			$page = Page::createInstanceFromGid( $element->getGid() );

		} else {

			$page = Page::createInstanceFromUid( $element->getUid() );

		}

		$SYS_SITE_ID[$id] = $page->getId();
		return $SYS_SITE_ID[$id];

	} catch ( Exception $e ) {
		return 0;
	}

}

//-----------------------------------------------------
// * グループページ名を直接取得
//-----------------------------------------------------
function get_gname($gid) {

	try {
		$page = Page::createInstanceFromGid( $gid );
		return $page->getSitename();
	} catch ( Exception $e ) {
		return '';
	}

}

//-----------------------------------------------------
// * $gid がポータルページとして設定さてているか
//-----------------------------------------------------
function is_portal($gid = null) {
	if (portal_gid() == $gid) {
		return true;
	}
	return false;
}

//-----------------------------------------------------
// * ポータルページとして設定されている gid を取得
//-----------------------------------------------------
function portal_gid() {

	global $PORTAL_GID;

	if (isset($PORTAL_GID)) {
		return $PORTAL_GID;
	}
	else {

		try {

			$portal = Page::getPortalPage();
			$PORTAL_GID = $portal->getGid();
			return $PORTAL_GID;

		} catch ( Exception $e ) {
			return 0;
		}

	}

}


/**
 * グループページまたはマイページのオブジェクト.
 * @author ikeda
 */
class Page {

	const PAGE_USER_PAGE = 1;
	const PAGE_GROUP_PAGE = 2;
	const PAGE_ALL_PAGE = 3; //Page::PAGE_USER_PAGE | Page::PAGE_GROUP_PAGE;

	/**
	 * ページの情報を格納するデータベーステーブル名称.
	 */
	const DATABASE = "page";

	/**
	 * ポータルページがどのページかを格納するデータベーステーブル名称.
	 */
	const DATABASE_PORTAL = "portal";

	/**
	 * ページID.
	 * @var Number
	 */
	private $id;

	/**
	 * マイページである場合、所有者のユーザID.
	 * @var Number
	 */
	private $uid;

	/**
	 * グループページである場合、所有グループのグループID.
	 * @var Number
	 */
	private $gid;

	/**
	 * 停止されたページである場合は0.
	 * @var Number
	 */
	private $enable;

	/**
	 * ページのタイトル.
	 * @var String
	 */
	private $sitename;

	/**
	 * ページの説明.
	 * @var String
	 */
	private $description;

	/**
	 * スキンID.
	 * データベースの theme_skin.id に対応.
	 * @var Number
	 */
	private $skin;

	/**
	 * コンストラクタ.
	 *
	 * @param Number $id
	 * @param Number $uid
	 * @param Number $gid
	 * @param String $enable
	 * @param String $sitename
	 * @param String $description
	 * @param Number $skin
	 *
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 * @throws DataNotFroundException 指定したIDのデータは存在しない.
	 *
	 */
	public function __construct( $id=null, $uid=null, $gid=null, $enable=null,
								$sitename=null, $description=null, $skin=null ) {

		$this->id = $id;
		$this->uid = null;
		$this->gid = null;
		$this->enable = null;
		$this->sitename = null;
		$this->description = null;
		$this->skin = null;

		if ( null !== $id ) {

			$result = mysql_exec( "select id, uid, gid, enable, sitename, description, skin"
								." from ".Page::DATABASE
								." where id=%d",
								mysql_num( $id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				$this->id = (int)$row["id"];
				$this->uid = (int)$row["uid"];
				$this->gid = (int)$row["gid"];
				$this->enable = (int)$row["enable"];
				$this->sitename = $row["sitename"];
				$this->description = $row["description"];
				$this->skin = $row["skin"];

			} else {
				throw new DataNotFoundException("The page is not found.");
			}

		}

		if ( null !== $uid ) { $this->uid = $uid; }
		if ( null !== $gid ) { $this->gid = $gid; }
		if ( null !== $enable ) { $this->enable = $enable; }
		if ( null !== $sitename ) { $this->sitename = $sitename; }
		if ( null !== $description ) { $this->description = $description; }
		if ( null !== $skin ) { $this->skin = $skin; }

	}

	/**
	 * 該当するグループのページを返す.
	 * @param Number $gid グループID.
	 * @return Page
	 * @throws InvalidArgumentException gid が null.
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 * @throws DataNotFroundException 指定したIDのデータは存在しない.
	 */
	public static function createInstanceFromGid( $gid ) {

		if ( null === $gid ) { throw new InvalidArgumentException("invalid gid."); }

		$page = new Page();

		$result = mysql_exec( "select id, uid, gid, enable, sitename, description, skin"
							." from ".Page::DATABASE
							." where gid=%d",
							mysql_num( $gid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$page->id = (int)$row["id"];
			$page->uid = (int)$row["uid"];
			$page->gid = (int)$row["gid"];
			$page->enable = (int)$row["enable"];
			$page->sitename = $row["sitename"];
			$page->description = $row["description"];
			$page->skin = $row["skin"];

		} else {
			throw new DataNotFoundException("The page is not found.");
		}

		return $page;

	}

	/**
	 * 指定したユーザのマイページを取得する.
	 * @param Number $uid
	 * @return Page
	 * @throws InvalidArgumentException uid が null.
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 * @throws DataNotFroundException 指定したIDのデータは存在しない.
	 */
	public static function createInstanceFromUid( $uid ) {

		if ( null === $uid ) { throw new InvalidArgumentException("invalid uid"); }

		$page = new Page();

		$result = mysql_exec( "select id, uid, gid, enable, sitename, description, skin"
							." from ".Page::DATABASE
							." where uid=%d",
							mysql_num( $uid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$page->id = (int)$row["id"];
			$page->uid = (int)$row["uid"];
			$page->gid = (int)$row["gid"];
			$page->enable = (int)$row["enable"];
			$page->sitename = $row["sitename"];
			$page->description = $row["description"];
			$page->skin = $row["skin"];

		} else {
			throw new DataNotFoundException("Data is not found.");
		}

		return $page;

	}

	public function getId() { return $this->id; }
	public function getUid() { return $this->uid; }
	public function getGid() { return $this->gid; }
	public function getEnable() { return $this->enable; }
	public function getSitename() { return $this->sitename; }
	public function getDescription() { return $this->description; }
	public function getSkin() { return $this->skin; }

	/**
	 * 比較関数.
	 * @param Page $obj
	 * @return bool
	 */
	public function equals( $obj ) {

		return ( $this->id === $obj->id 
				and $this->uid === $obj->uid
				and $this->gid === $obj->gid
				and $this->enable === $obj->enable
				and $this->sitename === $obj->sitename
				and $this->description === $obj->description
				and $this->skin === $obj->skin );
		
	}

	/**
	 * コのオブジェクトがポータルページかどうか.
	 * @return bool
	 */
	public function isPortal() {
		return ( $this->equals( Page::getPortalPage() ) );
	}

	/**
	 * このページのURLを取得.
	 * @return String URL
	 */
	public function getUrl() {

		if ( 0 < $this->uid ) {
			return Path::makeURL( "/index.php?uid=".$this->uid );
		} else if ( 0 < $this->gid ) {
			return Path::makeURL( "/index.php?gid=".$this->gid );
		} else
			return "";

	}

	/**
	 * ポータルページを取得する.
	 * @staticvar Page $portalPage
	 * @return Page
	 */
	static public function getPortalPage() {

		static $portalPage = null;

		if ( null === $portalPage ) {

			$result = mysql_exec( "select gid from ".Page::DATABASE_PORTAL );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				return ( $portalPage = Page::createInstanceFromGid($row["gid"]) );

			} else {

				throw new DataNotFoundException("portal page is not found.");

			}

		} else {
			return $portalPage;
		}

	}

	/**
	 * 登録されているすべてのページを配列で返す.
	 * @return Array of Page
	 */
	static public function getPages( $get=Page::PAGE_ALL_PAGE ) {

		$array = array();

		$where = "";

		if ( Page::PAGE_ALL_PAGE === $get ) {

			$where = "";

		} else {

			if ( $get === Page::PAGE_USER_PAGE ) {
				$where = "where 0<uid";
			} else if ( $get === Page::PAGE_GROUP_PAGE ) {
				$where = "where 0<gid";
			}

		}

		$result = mysql_exec( "select id, uid, gid, enable, sitename, description, skin"
							." from ".Page::DATABASE
							." ".$where );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		while ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$page = new Page();

			$page->id = (int)$row["id"];
			$page->uid = (int)$row["uid"];
			$page->gid = (int)$row["gid"];
			$page->enable = (int)$row["enable"];
			$page->sitename = $row["sitename"];
			$page->description = $row["description"];
			$page->skin = $row["skin"];

			$array[] = $page;

		}

		return $array;

	}

}
?>
