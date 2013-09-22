<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * MySqlStatement の結果.
 *
 * @author ikeda
 */
class MySqlResult {
	
	private $result;

	/**
	 * コンストラクタ.
	 * @param Object $result mysql_query 関数の戻り値.
	 */
	public function __construct( $result ) {
		$this->result = $result;
	}

	public function getResult() { return $this->result; }

}

/**
 * MySqlInsertStatement の結果.
 */
class MySqlInsertResult extends MySqlResult {

	private $id;

	/**
	 * コンストラクタ.
	 * @param Object $result mysql_query 関数の戻り値.
	 */
	public function __construct( $result ) {

		parent::__construct($result);
		$this->id = mysql_insert_id();

	}

	/**
	 * 挿入されたレコードの ID が返る.
	 * @return number primary key auto_increment のレコードを追加した場合、
	 * レコードの id の値が返る. id を直接指定して挿入した場合は、0 が返る.
	 */
	public function getInsertId() { return $this->id; }

}

/**
 * MySqlSelectStatement の結果.
 */
class MySqlSelectResult extends MySqlResult {

	private $dataClass;

	/**
	 * コンストラクタ.
	 * @param Object $result mysql_query 関数の戻り値.
	 * @param string $dataClass 取得されたデータを格納する MySqlData 派生クラスの名前.
	 */
	public function __construct( $result, $dataClass ) {

		parent::__construct( $result );

		$this->dataClass = $dataClass;

	}

	/**
	 * 取得されたレコードオブジェクトの配列を取得する.
	 * @return array
	 */
	public function getDatas() {

		$datas = array();

		while ( false !== ( $row = mysql_fetch_assoc( $this->getResult() ) ) ) {
			$datas[] = new $this->dataClass( $row );
		}

		return $datas;

	}

	public function getNumDatas() {
		return mysql_num_rows( $this->getResult() );
	}

}

?>
