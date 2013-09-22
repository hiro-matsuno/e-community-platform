<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/Element.php";

define( "INFO_HTML_DATABASE", 'info_html' );

define( "INFO_HTML_POS_TOP", 0 ); 
define( "INFO_HTML_POS_LEFT", 2 ); 
define( "INFO_HTML_POS_CENTER", 1 ); 
define( "INFO_HTML_POS_RIGHT", 3 ); 
define( "INFO_HTML_POS_BOTTOM", 4 ); 

/**
 * 登録された info ブロックの描画データを得る。
 * @param data 連想配列。取得したデータが追加で格納される。
 */
function InfoHtmlGetHtml( &$data ) {

	try {

		$entries = InfoHtmlEntry::getEntries();

		foreach ( $entries as $entry ) {

			if ( !$entry->getEnabled() ) { continue; }
			
			$array = array( 'id'=>$entry->getEid(),
							'title'=>$entry->getName(),
							'content'=>$entry->getHtml(),
//							 'editlink'=>
//							 "<div style=\"text-align: right;\" class=\"edit_menu\">"
//							."<a href=\"/modules/notice/module.php?act=setting&id={$row['id']}\">設定</a>"
//							."</div>"
							);

			$data['space_'.$entry->getPos()][] = $array;

			//	タイトルバー、枠線を消すCSSを出力
			$data['css'][] = <<<__EOF__
#box_{$row['id']}.box {
	border-style: none;
}
#box_{$row['id']} .box_menu {
	display: none;
}
#box_{$row['id']} .box_main {
	padding: 0px;
}
__EOF__;

		}

	} catch ( Exception $e ) {
		return;
	}
	
}

function InfoHtmlSetEntry( $id, $name, $module, $html, $pos, $unit, $enabled ) {
	
	if ( !is_su() ) return false;
	
	try {

		$entry = new InfoHtmlEntry( $id, $name, $module, $html, $pos, $enabled );
		$entry->setPermission( $unit );
		$entry->regist();

		return $entry->getEid();

	} catch ( Exception $e ) {
		return false;
	}

}

function InfoHtmlInsertEntry( $name, $module, $html, $pos, $unit, $enabled ) {
	
	if ( !is_su() ) return false;
	
	try {

		$entry = new InfoHtmlEntry( null, $name, $module, $html, $pos, $enabled );
		$entry->setPermission( $unit );
		$entry->regist();

		return $entry->getEid();

	} catch ( Exception $e ) {
		return false;
	}

}

function InfoHtmlUpdateEntry( $id, $name, $module, $html, $pos, $unit, $enabled ) {
	
	if ( !is_su() ) return false;

	try {

		$entry = new InfoHtmlEntry( $id, $name, $module, $html, $pos, $enabled );
		$entry->setPermission( $unit );
		$entry->regist();

		return $entry->getEid();

	} catch ( Exception $e ) {
		return false;
	}
	
}

function InfoHtmlDeleteEntry( $id ) {
	
	if ( !is_su() ) return false;

	try {

		$entry = new InfoHtmlEntry($id);
		$entry->delete();

		return true;

	} catch ( Exception $e ) {
		return false;
	}

}

function InfoHtmlGetEntries( $id=null, $module=null ) {

	$data = array();

	try {

		if ( null !== $id ) {

			$entry = new InfoHtmlEntry($id);
			$data[] = $entry->serialize();

		} else if ( null !== module ) {

			$entries = InfoHtmlEntry::getEntries($module);

			foreach ( $entries as $entry ) {
				$data[] = $entry->serialize();
			}

		}

	} catch ( Exception $e ) {
		return false;
	}

	return $data;
	
}

class InfoHtmlEntry extends Element {

	const DATABASE		= INFO_HTML_DATABASE;

	const POS_TOP		= INFO_HTML_POS_TOP;
	const POS_LEFT		= INFO_HTML_POS_LEFT;
	const POS_CENTER	= INFO_HTML_POS_CENTER;
	const POS_RIGHT		= INFO_HTML_POS_RIGHT;
	const POS_BOTTOM	= INFO_HTML_POS_BOTTOM;

	private $name;
	private $module;
	private $html;
	private $pos;
	private $enabled;

	public function __construct( $id = null, $name=null, $module=null,
								$html=null, $pos=null, $enabled=null ) {

		$this->name = null;
		$this->module = null;
		$this->html = null;
		$this->pos = null;
		$this->enabled = null;

		if ( null !== $id ) {

			parent::__construct( $id );

			$result = mysql_exec( "select name, module, html, pos, enabled"
								." from ".InfoHtmlEntry::DATABASE
								." where id=%d",
								mysql_num( $id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				$this->name = $row["name"];
				$this->module = $row["module"];
				$this->html = $row["html"];
				$this->pos = $row["pos"];
				$this->enabled = $row["enabled"];

			} else {
				throw new DataNotFoundException();
			}

		}

		if ( null !== $name )	$this->name = $name;
		if ( null !== $module ) $this->module = $module;
		if ( null !== $html )	$this->html = $html;
		if ( null !== $pos )	$this->pos = $pos;
		if ( null !== $enabled ) $this->enabled = $enabled;

	}

	public function getId() { return $this->eid; }
	public function getName() { return $this->name; }
	public function getModule() { return $this->module; }
	public function getHtml() { return $this->html; }
	public function getPos() { return $this->pos; }
	public function getEnabled() { return $this->enabled; }

	public function regist() {

		if ( null === $this->eid ) {

			$this->eid = get_seqid();

			$result = mysql_exec( "insert into ".INFO_HTML_DATABASE." ( id, name, module, html, pos, enabled )"
									." values( %d, %s, %s, %s, %d, %d )",
									mysql_num( $this->eid ),
									mysql_str( $this->name ),
									mysql_str( $this->module ),
									mysql_str( $this->html ),
									mysql_num( $this->pos ),
									mysql_num( ( false!=$this->enabled ? 1 : 0 ) ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			set_pmt( array( "eid" => $this->eid, "unit" => $this->getPermission() ) );

		} else {

			$result = mysql_exec( "update ".INFO_HTML_DATABASE." set"
									." name=%s, module=%s, html=%s, pos=%d, enabled=%d"
									." where id=%d",
									mysql_str( $this->name ),
									mysql_str( $this->module ),
									mysql_str( $this->html ),
									mysql_num( $this->pos ),
									mysql_num( ( false!=$this->enabled ? 1 : 0 ) ),
									mysql_num( $this->eid ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			$result = mysql_exec( "select id from owner where id=%d", mysql_num( $id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( 0 < mysql_num_rows( $result ) )
				set_pmt( array( "eid" => $this->eid, "unit" => $this->getPermission() ) );

		}

	}

	public function delete() {

		if ( !mysql_exec( "delete from ".INFO_HTML_DATABASE
						." where id=%d", mysql_num( $this->eid ) ) ) {
			throw new SQLException( mysql_error() );
		}

	}

	public static function getEntries( $module=null ) {

		$result = mysql_exec( "select i.id, uid, gid, name, module, html, pos, enabled"
							." from ".InfoHtmlEntry::DATABASE." as i"
							." left join owner as o on i.id=o.id"
							.( ( null !== $module ) ? " where module=%s" : "" ),
							mysql_str( $module ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		$array = array();

		while( false !== ( $row = mysql_fetch_array($result) ) ) {

			$entry = new InfoHtmlEntry();

			$entry->eid = $row["id"];
			$entry->uid = $row["uid"];
			$entry->gid = $row["gid"];
			$entry->name = $row["name"];
			$entry->module = $row["module"];
			$entry->html = $row["html"];
			$entry->pos = $row["pos"];
			$entry->enabled = $row["enabled"];

			$array[] = $entry;

		}

		return $array;

	}

	public function serialize() {

		$obj = (Object)array();

		$obj->eid = $this->eid;
		$obj->uid = $this->uid;
		$obj->gid = $this->gid;
		$obj->unit = $this->getPermission();
		$obj->name = $this->name;
		$obj->module = $this->module;
		$obj->html = $this->html;
		$obj->pos = $this->pos;
		$obj->enabled = $this->enabled;

		return (Array)$obj;

	}

}

?>