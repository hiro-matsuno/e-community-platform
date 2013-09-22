<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

//-----------------------------------------------------
// * コメントの閲覧権限を設定
//-----------------------------------------------------
function set_comment($eid = 0) {

	try {

		if ( 0 == $eid ) { throw new Exception(); }

		$unit = isset($_REQUEST['comment_'. $eid]) ?
					intval($_REQUEST['comment_'. $eid]) :
					intval($_REQUEST['comment_0']);

		$comment = new Comment( $eid );
		$comment->setPermission($unit);
		$comment->regist();

	} catch ( Exception $e ) {}

}


/**
 * Description of Comment
 *
 * @author ikeda
 */
class Comment {

	const DATABASE_ALLOW = "comment_allow";

	public $eid;

	public $permission;

	public function __construct( $eid=null ) {

		$this->eid = $eid;

		//	初めて参照しようとしたときにデータベースから取得する.
		$this->permission = null;

	}

	public function getEid() {
		return $this->eid;
	}

	public function getPermission() {

		if ( null === $this->eid ) {
			$this->permission = Permission::PMT_BROWSE_PRIVATEPMT_CLOSE;
		} else if ( null === $this->permission ) {

			$result = mysql_exec( "select unit from ".Comment::DATABASE_ALLOW." where eid=%d",
								mysql_num( $this->eid ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array( $result ) ) ) {
				$this->permission = $row["unit"];
			} else {
				$this->permission = Permission::PMT_BROWSE_PRIVATE;
			}

		}

		return $this->permission;

	}

	public function setPermission( $unit ) {
		$this->permission = $unit;
	}

	public function regist() {

		$unit = $this->getPermission();

		$result = mysql_exec( "select eid, unit from ".Comment::DATABASE_ALLOW." where eid=%d",
							mysql_num( $this->eid ) );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		if ( 0 < mysql_num_rows( $result ) ) {

			$row = mysql_fetch_array($result);

			if ( $unit !== $row["unit"] ) {

				if ( false === mysql_exec( "update ".Comment::DATABASE_ALLOW." set unit=%d"
										." where eid=%d",
										mysql_num( $unit ), mysql_num( $this->eid ) ) ) {
					throw new SQLException( mysql_error() );
				}

			}

		} else {

			if ( false === mysql_exec( "insert into ".Comment::DATABASE_ALLOW
									." ( eid, unit )"
									." values( %d, %d )",
									mysql_num( $this->eid ), mysql_num( $unit ) ) ) {
				throw new SQLException( mysql_error() );
			}

		}

	}

}
?>
