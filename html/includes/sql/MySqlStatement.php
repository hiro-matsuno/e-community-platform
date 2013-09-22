<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/MySqlUtil.php";
require_once dirname(__FILE__)."/MySqlConnection.php";
require_once dirname(__FILE__)."/MySqlData.php";
require_once dirname(__FILE__)."/MySqlResult.php";
require_once dirname(__FILE__)."/../Exception.php";

/**
 * MySql 命令文抽象化クラス.
 *
 * @author ikeda
 */
interface MySqlStatement {

	/**
	 * 命令文クエリを取得する.
	 */
	public function query();

	/**
	 * 命令文クエリを実行する.
	 */
	public function exec();

}

/**
 * 直接クエリを指定して命令発行できる命令文クラス.
 */
class MySqlPlaneStatement implements MySqlStatement {

	/**
	 * 利用するMySql接続.
	 * @var MySqlConnection
	 */
	private $connection;

	/**
	 * SQL命令文.
	 * @var string
	 */
	private $query;

	/**
	 * コンストラクタ.
	 * @param string $query
	 * @param MySqlConnection
	 */
	public function __construct( $query, $connection=null ) {
		$this->query = $query;
		$this->connection = $connection;
	}

	public function query() { return $this->query; }

	public function exec() {

		$result = null;
		
		if ( null !== $this->connection ) {
			$result = mysql_query( $this->query(), $this->connection->getConnection() );
		} else {
			$result = mysql_query( $this->query() );
		}

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		return new MySqlResult( $result );

	}

	static public function execNow( $query, $connection=null ) {
		$state = new MySqlPlaneStatement( $query, $connection );
		return $state->exec();
	}

}

/**
 * オブジェクト型を指定してテーブルからレコードを抽出する命令文クラス.
 */
class MySqlSelectStatement implements MySqlStatement {

	private $connection;
	private $query;
	private $dataClass;

	private $condition;

	/**
	 * コンストラクタ.
	 * @param string $dataClass 抽出したレコードを格納する MySqlData 派生クラスの型.
	 * @param <type> $query select 命令文クエリを指定する. null とした場合は $dataClass
	 *								から自動で命令文を作成する.
	 * @param <type> $connection 利用する接続.
	 */
	public function __construct( $dataClass, $query=null, $connection=null ) {
		$this->connection = $connection;
		$this->query = $query;
		$this->dataClass = $dataClass;
	}

	/**
	 * 命令文の末尾につける条件式を取得する.
	 */
	public function getOtherConditions() {
		return $this->condition;
	}

	/**
	 * 命令文の末尾につける条件式を指定する.
	 */
	public function setOtherConditions( $condition ) {
		$this->condition = $condition;
	}

	public function query() {

		if ( null === $this->query ) {

			$db_vars = call_user_func( array( $this->dataClass, "getMemberNames" ),
									$this->dataClass );

			$query = "select ";

			$first = true;

			foreach ( $db_vars as $var ) {

				if ( !$first ) { $query .= ", "; } else { $first = false; }

				$query .= $var;

			}

			$query .= " from ".call_user_func( array( $this->dataClass, "getTableName" ) );

			$query .= " ".$this->getOtherConditions();

			return $query;

		} else {

			return $this->query;

		}

	}

	public function exec() {

		$result = null;

		if ( null !== $this->connection ) {
			$result = mysql_query( $this->query(), $this->connection->getConnection() );
		} else {
			$result = mysql_query( $this->query() );
		}

		if ( !$result ){ throw new SQLException( mysql_error() ); }

		return new MySqlSelectResult( $result, $this->dataClass );

	}

}

/**
 * オブジェクトを指定してテーブルにレコード登録する命令文クラス.
 */
class MySqlInsertStatement implements MySqlStatement {

	private $connection;

	private $data;

	/**
	 * コンストラクタ.
	 * @param MySqlData $data レコード登録する MySqlData 派生型オブジェクト.
	 * @param MySqlConnection $connection
	 */
	public function __construct( $data, $connection=null ) {
		$this->connection = $connection;
		$this->data = $data;
	}

	public function query() {

		$dataClass = get_class( $this->data );
		$db_vars = call_user_func( array( $dataClass, "getMemberNames" ), $dataClass );

		$query = "insert into ".$this->data->getTableName()." (";

		$first = true;

		foreach ( $db_vars as $var ) {

			$getter = MySqlUtil::getGetterName($dataClass,$var);
			$value = $this->data->$getter();
			
			if ( null !== $value ) {

				if ( !$first ) { $query .= ", "; } else { $first = false; }
				$query .= $var;

			}

		}

		$query .= ") values(";

		$first = true;

		foreach ( $db_vars as $var ) {

			$getter = MySqlUtil::getGetterName($dataClass,$var);
			$value = $this->data->$getter();
			if ( is_string( $value ) ) { 
				$value = MySqlUtil::decorateText( $value );
			} else if ( is_a( $value, "Serializable" ) ) {
				$value = MySqlUtil::decorateText( $value->serialize() );
			} else if ( is_object( $value ) or is_array( $value ) ) {
				$value = MySqlUtil::decorateText( serialize( $value ) );
			}

			if ( null !== $value ) {
				
				if ( !$first ) { $query .= ", "; } else { $first = false; }
				$query .= $value;

			}

		}

		$query .= ")";

		return $query;

	}

	public function exec() {

		$result = null;

		if ( null !== $this->connection ) {
			$result = mysql_query( $this->query(), $this->connection->getConnection() );
		} else {
			$result = mysql_query( $this->query() );
		}

		if ( !$result ){ throw new SQLException( mysql_error() ); }

		return new MySqlInsertResult( $result );
		
	}

}

/**
 * オブジェクトを指定してテーブルにレコード上書きする命令文クラス.
 */
class MySqlUpdateStatement implements MySqlStatement {

	private $connection;

	private $data;

	/**
	 * コンストラクタ.
	 * @param MySqlData $data レコード登録する MySqlData 派生型オブジェクト.
	 * @param MySqlConnection $connection
	 */
	public function __construct( $data, $connection=null ) {
		$this->connection = $connection;
		$this->data = $data;
	}

	public function query() {
		
		$dataClass = get_class( $this->data );
		$db_vars = call_user_func( array( $dataClass, "getMemberNames" ), $dataClass );

		$query = "update ".$this->data->getTableName()." set ";

		$first = true;

		foreach ( $db_vars as $var ) {

			$getter = MySqlUtil::getGetterName($dataClass,$var);
			$value = $this->data->$getter();
			if ( is_string( $value ) ) {
				$value = MySqlUtil::decorateText( $value );
			} else if ( is_object( $value ) or is_array( $value ) ) {
				$value = MySqlUtil::decorateText( serialize( $value ) );
			}

			if ( null !== $value ) {

				if ( !$first ) { $query .= ", "; } else { $first = false; }
				$query .= $var."=".$value;

			}

		}

		$key = $this->data->getKeyName();
		$getter = MySqlUtil::getGetterName($dataClass,$key);
		$keyValue = $this->data->$getter();
		if ( is_string( $keyValue ) ) {
			$keyValue = MySqlUtil::decorateText( $keyValue );
		} else if ( is_object( $keyValue ) or is_array( $keyValue ) ) {
			$keyValue = MySqlUtil::decorateText( serialize( $keyValue ) );
		}

		$query .= " where ".$key."=".$keyValue;

		return $query;

	}

	public function exec() {
		
		$result = null;

		if ( null !== $this->connection ) {
			$result = mysql_query( $this->query(), $this->connection->getConnection() );
		} else {
			$result = mysql_query( $this->query() );
		}

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		return new MySqlResult( $result );

	}

}

/**
 * 与えられたデータにしたがい、insert と update を使い分ける.
 */
class MySqlRegistStatement implements MySqlStatement {
	
	private $connection;

	private $data;

	private $stat;

	/**
	 * コンストラクタ.
	 * @param MySqlData $data レコード登録する MySqlData 派生型オブジェクト.
	 * @param MySqlConnection $connection
	 */
	public function __construct( $data, $connection=null ) {

		$this->connection = $connection;
		$this->data = $data;

		$className = get_class( $this->data );
		$key = $this->data->getKeyName();
		$keyGetter = MySqlUtil::getGetterName($className,$key);
		$keyValue = $this->data->$keyGetter();

		$isNew = null;

		if ( null === $keyValue ) {

			$isNew = true;

		} else {

			$stat = new MySqlSelectStatement( $className );
			$stat->setOtherConditions( "where $key=".MySqlUtil::decorateText( $keyValue ) );

			if ( 0 < count( $stat->exec()->getDatas() ) ) {
				$isNew = false;
			} else {
				$isNew = true;
			}

		}

		if ( $isNew ) {
			$this->stat = new MySqlInsertStatement($data);
		} else {
			$this->stat = new MySqlUpdateStatement($data);
		}
		
	}

	public function query() {
		return $this->stat->query();
	}

	public function exec() {
		return $this->stat->exec();
	}

}

/**
 * オブジェクトを指定してテーブルにレコード削除する命令文クラス.
 */
class MySqlDeleteStatement implements MySqlStatement {
	
	private $connection;
	private $data;

	/**
	 * コンストラクタ.
	 * @param MySqlData $data レコード削除する MySqlData 派生型オブジェクト.
	 * @param MySqlConnection $connection
	 */
	public function __construct( $data, $connection=null ) {
		$this->connection = $connection;
		$this->data = $data;
	}

	public function query() {

		$dataClass = get_class( $this->data );

		$key = $this->data->getKeyName();
		$getter = MySqlUtil::getGetterName($dataClass,$key);
		$keyValue = $this->data->$getter();
		if ( is_string( $keyValue ) ) { $keyValue = MySqlUtil::decorateText( $keyValue ); }

		$query = sprintf( "delete from %s where %s=%s",
						$this->data->getTableName(),
						$key,
						$keyValue );

		return $query;
		
	}

	public function exec() {

		$result = null;

		if ( null !== $this->connection ) {
			$result = mysql_query( $this->query(), $this->connection->getConnection() );
		} else {
			$result = mysql_query( $this->query() );
		}

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		return new MySqlResult( $result );
		
	}

}
?>
