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
require_once dirname(__FILE__)."/MySqlStatement.php";
require_once dirname(__FILE__)."/MySqlRecord.php";
require_once dirname(__FILE__)."/../Exception.php";

/**
 * MySql レコード抽象化クラス.
 *
 * MySql のテーブルレコードの抽象化クラスです.
 * このクラスを継承して、具体的なレコードのカラムをメンバとして追加して利用します.
 *
 * 
 * * 継承クラスで定義するべきメンバ
 *
 * 継承クラスでテーブルレコードをメンバ変数として追加する場合は、$r_{カラム名}
 * という形にしてください. また、setter, getter メソッドを各テーブルレコード変数に
 * ついて必ず定義して下さい.
 * アクセシビリティは protected あるいは public にしてください.
 * テーブルレコード変数には直にアクセスされることはなく、必ず setter, getter を
 * 通してアクセスされます.
 *
 * setter, getter メソッドは以下の命名規則で定義して下さい.
 * function set_{カラム名}()
 * function get_{カラム名}()
 * あるいは、
 * function set{先頭を大文字にしたカラム名}()
 * function get{先頭を大文字にしたカラム名}()
 *
 * また、オブジェクト、配列などのメンバをシリアライズしてテーブルに登録する機能に
 * 対応しています.
 * このようなメンバを扱う場合は、setter メソッドでシリアル化文字列から
 * unserialize 関数などでオブジェクトに復元する機能を実装しておいてください.
 * (モジュール内部から setter が呼ばれる場合は、必ずシリアル化文字列を引数に
 * とって呼ばれることになります)
 * getter メソッドでシリアル化文字列を返す必要はありません. オブジェクトまたは
 * 配列が返された場合は必要に応じてモジュール側でシリアライズして利用します.
 * Serializable インターフェイスを実装するインスタンスはメンバ関数 serialize() で
 * シリアライズされます. またその他のオブジェクトはグローバル関数 serialize()
 * 関数によってシリアライズされます.
 *
 * このレコードを保存するテーブル名を返す関数を定義して下さい.
 * static public function getTableName
 *
 * このレコードのキーとなるカラムの名前を返す関数を定義して下さい.
 * static public function getKeyName
 *
 * 継承クラスの作成例は MySqlDataTest.php を参照してください.
 *
 * @author ikeda
 * 
 */
class MySqlData implements MySqlRecord {

	/**
	 * コンストラクタ.
	 * 引数として連想配列が与えられた場合、カラム名=>値としてサブクラスの各変数に
	 * 値を代入する. またそれ以外の型で与えられた場合、getKeyName で与えられる
	 * カラム名についてその値を検索し、該当のレコードを引き出してオブジェクトを
	 * 生成する.
	 *
	 * @param mixed $data 連想配列あるいは他の型.
	 * @param MySqlConnection $connection 利用する接続.
	 */
	public function __construct( $data=null, $connection=null ) {

		if ( null !== $data ) {

			$className = get_class( $this );
			$db_vars = call_user_func( array( $className, "getMemberNames" ), $className );
			
			if ( !is_array( $data ) ) {

				if ( is_string( $data ) ) { $data = MySqlUtil::decorateText( $data ); }
				
				$stat = new MySqlSelectStatement( $className, null, $connection );
				$stat->setOtherConditions( " where ".$this->getKeyName()."=".$data );

				$data = mysql_fetch_assoc( $stat->exec()->getResult() );

				if ( false === $data ) { throw new DataNotFoundException(); }

			}

			foreach ( $db_vars as $key ) {

				if ( !isset( $data[$key] ) ) { continue; }
				$value = $data[$key];

				$methodName = MySqlUtil::getSetterName($className,$key);
				$this->$methodName( $value );

			}

		}

	}

	/**
	 * 参照するテーブルの名称を返す.
	 */
	//static public function getTableName();

	/**
	 * キーとなるカラムの名称を返す.
	 */
	//static public function getKeyName();

	/**
	 * サブクラスに定義されたテーブルレコード変数名の配列を返す.
	 * @attention public となっているが、モジュール外からアクセスの必要は無い.
	 */
	public static function getMemberNames( $className=null ) {

		if ( null === $className ) { $className = get_called_class(); }

		$reflection = new ReflectionClass( $className );
        $vars = array_keys($reflection->getdefaultProperties());

		$db_vars = array();

		foreach ( $vars as $v ) {

			if ( "r_" == substr( $v, 0, 2 ) ) {
				$db_vars[] = substr( $v, 2 );
			}

		}

		return $db_vars;

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

		return true;
		
	}

	/**
	 * レコードをテーブルに登録する.
	 * getKeyName で与えられるキー値で検索し、すでに登録されていれば update,
	 * 未登録なら insert を行なう.
	 *
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function regist( $connection=null ) {

		$stat = new MySqlRegistStatement( $this, $connection );
		$result = $stat->exec();

		if ( is_a( $result, "MySqlInsertResult" ) ) {

			if ( 0 < $result->getInsertId() ) {

				$key = $this->getKeyName();
				$keySetter = MySqlUtil::getSetterName( get_class($this), $key );
				$this->$keySetter( $result->getInsertId() );

			}

		}

	}

	/**
	 * このレコードの削除を行なう.
	 * @param MySqlConnection $connection
	 * @return MySqlResult
	 */
	public function delete( $connection=null ) {

		$stat = new MySqlDeleteStatement( $this, $connection );
		return $stat->exec();

	}

}
?>
