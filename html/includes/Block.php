<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/Element.php";

/**
 * ページに追加されているパーツブロック
 * ブロックIDは Elementクラスの$eidに格納される
 *
 * @author ikeda
 */
class Block implements MySqlRecord {

	const DATABASE = "block";

	protected $element;

	protected $r_id;

	/** ページID */
	protected $r_pid;

	/** モジュールID */
	protected $r_module;

	/** ブロック表示名 */
	protected $r_name;

	/** 削除不可フラグ */
	protected $r_del_lock;

	/** 横位置 space1=0 */
	protected $r_hpos;

	/** 縦位置 */
	protected $r_vpos;

	/** 更新日時 */
	protected $r_updymd;

	/**
	 * コンストラクタ.
	 * 引数 $data が ID の場合は、該当レコードをデータベースからロードする.
	 * 連想配列の場合はメンバに値を代入する.
	 * 
	 * @param mixed $data Block ID または連想配列.
	 * @param MySqlConnection $connection データベース操作に利用する接続.
	 */
	public function __construct( $data=null, $connection=null ) {

		if ( null !== $data ) {

			$className = get_class( $this );
			$db_vars = call_user_func( array( $className, "getMemberNames" ), $className );

			if ( !is_array( $data ) ) {

				if ( is_string( $data ) ) { $data = MySqlUtil::decorateText( $data ); }

				$stat = new MySqlPlaneStatement( "select b.id, pid, module, name, del_lock, hpos, vpos,"
												." e.unit, o.gid, o.uid from ".Block::DATABASE." as b"
						
												//	$this->element のカラムも一緒に引き出す.
												." left join ".Element::DATABASE." as e"
												." on b.id=e.id"
												." left join ".Owner::DATABASE." as o"
												." on b.id=o.id"
						
												." where b.".$this->getKeyName()."=$data",
												$connection );

				$data = mysql_fetch_assoc( $stat->exec()->getResult() );

				if ( false === $data ) { throw new DataNotFoundException(); }

			}

			foreach ( $db_vars as $key ) {

				if ( !isset( $data[$key] ) ) { continue; }
				$value = $data[$key];

				$methodName = MySqlUtil::getSetterName($className,$key);
				$this->$methodName( $value );

			}

			$this->element = new Element( $data );

		} else {

			$this->element = new Element();

		}

	}

	public function getElement() { return $this->element; }

	/**
	 * @deprecated
	 * @return Number
	 */
	public function getEid() { return $this->getId(); }

	public function getId() { return $this->r_id; }
	public function getPid() { return $this->r_pid; }
	public function getModule() { return $this->r_module; }
	public function getName() { return $this->r_name; }
	public function getDelLock() { return $this->r_del_lock; }
	public function getHpos() { return $this->r_hpos; }
	public function getVpos() { return $this->r_vpos; }
	public function getUpdymd() { return $this->r_updymd; }

	public function getUid() { return $this->element->getUid(); }
	public function getGid() { return $this->element->getGid(); }

	protected function setId( $id ) { $this->r_id = (int)$id; }
	public function setPid( $pid ) { $this->r_pid = (int)$pid; }
	public function setModule( $module ) { $this->r_module = $module; }
	public function setName( $name ) { $this->r_name = $name; }
	public function setDelLock( $del_lock ) { $this->r_del_lock = (int)$del_lock; }
	public function setHpos( $hpos ) { $this->r_hpos = (int)$hpos; }
	public function setVpos( $vpos ) { $this->r_vpos = (int)$vpos; }
	protected function setUpdymd( $updymd ) { $this->r_updymd = $updymd; }

	static public function getMemberNames() {

		return array( "id", "pid", "module", "name", "del_lock", 
					"hpos", "vpos", "updymd" );
		
	}

	static public function getTableName() { return Block::DATABASE; }
	static public function getKeyName() { return "id"; }

	/**
	 * レコードをテーブルに登録する.
	 * getKeyName で与えられるキー値で検索し、すでに登録されていれば update,
	 * 未登録なら insert を行なう.
	 *
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function regist( $connection=null ) {

		if ( null === $this->r_id ) { $this->setId( get_seqid() ); }

		$stat = new MySqlRegistStatement( $this, $connection );
		$stat->exec();

		$this->element->setId( $this->r_id );
		$this->element->regist();

	}

	/**
	 * このレコードの削除を行なう.
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function delete( $connection=null ) {

		$stat = new MySqlDeleteStatement( $this, $connection );
		$stat->exec();

		$this->element->delete();

	}

	/**
	 * オブジェクトの比較.
	 * テーブルレコード変数のみ、型も含めて等しいかどうかを返す.
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

		return $this->element->equals( $obj->element );

	}

	public function getPage() {

		return new Page( $this->r_pid );

	}
	
	/**
	 * ページに追加済みのパーツのブロック情報を格納した配列を返す.
	 * 表示順はカラム順に上から (通常スキンでは中央カラムが先頭になる)
	 * @param int $pid ページID
	 * @return array(Block) ページ内のパーツのブロック情報を格納した配列
	 */
	static public function getPageBlocks($pid) {

		$stat = new MySqlSelectStatement( "Block",
				"select b.id, pid, module, name, del_lock, hpos, vpos,"
				." e.unit, o.gid, o.uid from ".Block::DATABASE." as b"

				//	$this->element のカラムも一緒に引き出す.
				." left join ".Element::DATABASE." as e"
				." on b.id=e.id"
				." left join ".Owner::DATABASE." as o"
				." on b.id=o.id"

				."where pid=".$pid." order by hpos,vpos" );

		return $stat->exec()->getDatas();

	}

}
?>
