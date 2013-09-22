<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/../../../includes/sql/MySqlData.php";

class MemoModule {

	const DATABASE = "mod_memo";

	static private $instance;

	private function __construct() {
	}

	static public function getInstance() {

		if ( null === MemoModule::$instance ) {
			MemoModule::$instance = new MemoModule();
		}

		return MemoModule::$instance;

	}

	public function install() {
		
		MySqlPlaneStatement
			::execNow( "create table if not exists ".MemoModule::DATABASE."("
					." id bigint primary key auto_increment,"
					." block_id bigint,"
					." bgcolor text,"
					." fgcolor text,"
					." data text )"
					." ENGINE=MyISAM DEFAULT CHARSET=utf8" );

	}

	public function uninstall() {

		MySqlPlaneStatement
			::execNow( "drop table if exists ".MemoModule::DATABASE );

	}

}

class MemoData extends MySqlData {

	private $r_id;
	private $r_block_id;
	private $r_fgcolor="000000";
	private $r_bgcolor="FFFFE0";
	private $r_data;

	public function __construct( $id=null, $connection=null ) {
		parent::__construct( $id, $connection );
	}

	static public function createInstanceByBlockId( $blockId ) {

		$stat = new MySqlSelectStatement( "MemoData" );
		$stat->setOtherConditions( "where block_id=".$blockId );

		$datas = $stat->exec()->getDatas();

		if ( 0 === count( $datas ) ) return null;

		return $datas[0];

	}

	protected function setId( $id ) { $this->r_id = $id; }
	public function setBlockId( $block_id ) { $this->r_block_id = $block_id; }
	public function setBgcolor( $bgcolor ) { $this->r_bgcolor = $bgcolor; }
	public function setFgcolor( $fgcolor ) { $this->r_fgcolor = $fgcolor; }
	public function setData( $data ) { $this->r_data = $data; }

	public function getId() { return $this->r_id; }
	public function getBlockId() { return $this->r_block_id; }
	public function getBgcolor() { return $this->r_bgcolor; }
	public function getFgcolor() { return $this->r_fgcolor; }
	public function getData() { return $this->r_data; }

	static public function getTableName() { return MemoModule::DATABASE; }
	static public function getKeyName() { return "id"; }

}
?>
