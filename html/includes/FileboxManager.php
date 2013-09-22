<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * Description of FileboxManager
 *
 * @author ikeda
 */
class FileboxManager {

	const DATABASE = "filebox_config";

	const DEFAULT_DISK_QUOTA = 4294967296;
	const DEFAULT_USER_QUOTA = 104857600;

	private $disk_quota;
	private $user_quota;
	private $youtube_user;
	private $youtube_passwd;
	private $group_level;
	private $user_level;

	private static $instance;

	private function __construct() {

		$result = mysql_exec( "select disk_quota, user_quota, youtube_user , youtube_passwd,"
							."group_level, user_level"
							." from ".FileboxManager::DATABASE );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$this->disk_quota = (int)$row["disk_quota"];
			$this->user_quota = (int)$row["user_quota"];
			$this->youtube_user = $row["youtube_user"];
			$this->youtube_passwd = $row["youtube_passwd"];
			$this->group_level = (int)$row["group_level"];
			$this->user_level = (int)$row["user_level"];

		} else {

			$this->disk_quota = FileboxManager::DEFAULT_DISK_QUOTA;
			$this->user_quota = FileboxManager::DEFAULT_USER_QUOTA;
			$this->youtube_user = "";
			$this->youtube_passwd = "";
			$this->group_level = 50;
			$this->user_level = 0;

		}

	}

	public static function getInstance() {

		if ( null === FileboxManager::$instance ) { FileboxManager::$instance = new FileboxManager(); }

		return FileboxManager::$instance;

	}

	public function getDiskQuota() { return $this->disk_quota; }
	public function getUserQuota() { return $this->user_quota; }
	public function getYoutubeUser() { return $this->youtube_user; }
	public function getYoutubePasswd() { return $this->youtube_passwd; }
	public function getGroupLevel() { return $this->group_level; }
	public function getUserLevel() { return $this->user_level; }

	public function setDiskQuota( $disk_quota ) { $this->disk_quota = $disk_quota; }
	public function setUserQuota( $user_quota ) { $this->user_quota = $user_quota; }
	public function setYoutubeUser( $youtube_user ) { $this->youtube_user = $youtube_user; }
	public function setYoutubePasswd( $youtube_passwd ) { $this->youtube_passwd = $youtube_passwd; }
	public function setGroupLevel( $group_level ) { $this->group_level = $group_level; }
	public function setUserLevel( $user_level ) { $this->user_level = $user_level; }

	public function regist() {

		if ( !mysql_exec( "update ".FileboxManager::DATABASE." set"
						." disk_quota=%d, user_quota=%d,"
						." youtube_user=%s, youtube_passwd=%s,"
						." group_level=%d, user_level=%d,",
						mysql_num( $this->disk_quota ),
						mysql_num( $this->user_quota ),
						mysql_str( $this->youtube_user ),
						mysql_str( $this->youtube_passwd ),
						mysql_num( $this->group_level ),
						mysql_num( $this->user_level ) ) ) {

			throw new SQLException( mysql_error() );

		}

	}

	public function checkDiskQuota( $size ) {

		$result = mysql_exec( "select sum(filesize) as size from filebox_data" );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array( $result ) ) ) {

			if ( !$row['size'] ) $row['size'] = 0;

			return ( $this->disk_quota > $row['size'] + $size );

		} else {
			return false;
		}

	}

	public function checkUserQuota( $user, $size ) {

		$uid = ( !is_a( $user, "User" ) ? $user->getUid() : $user );

		$result = mysql_exec( "select sum(filesize) as size from filebox_data as f"
							." left join owner as o on o.id=f.id where o.uid=%d",
							mysql_num( $uid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( false !== ( $row = mysql_fetch_array( $result ) ) ) {

			if ( !$row['size'] ) $row['size'] = 0;

			return ( $this->user_quota > $row['size'] + $size );

		} else {
			return false;
		}

	}

}
?>
