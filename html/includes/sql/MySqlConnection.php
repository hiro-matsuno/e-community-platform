<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/MySqlData.php";
require_once dirname(__FILE__)."/MySqlDatabase.php";

/**
 * MySql サーバとの接続クラス.
 *
 * @author ikeda
 */
class MySqlConnection {

	private $connection;

	private $hostname;
	private $user;
	private $passwd;

	private $database;

	/**
	 * コンストラクタ.
	 * @param string $hostname ホスト名.
	 * @param string $user ユーザ名.
	 * @patam string $passwd パスワード.
	 */
	public function __construct( $hostname, $user, $passwd ) {

		$this->hostname = $hostname;
		$this->user = $user;
		$this->passwd = $passwd;

	}

	public function getConnection() {
		return $this->connection;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function connect() {

		if ( !( $this->connection
				= mysql_connect( $this->hostname, $this->user, $this->passwd ) ) ) {

			throw new SQLException( mysql_error() );

		}

	}

	public function disconnect() {

		if ( !mysql_close() ) {

			throw new SQLException( mysql_error() );

		}

	}

	public function useDatabase( $dbname ) {

		if ( !mysql_select_db( $dbname ) ) {
			throw new SQLException( mysql_error() );
		}

		$this->database = new MySqlDatabase( $dbname );

		return $this->database;

	}

}
?>
