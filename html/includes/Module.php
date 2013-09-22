<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/sql/MySqlData.php";

//-----------------------------------------------------
// * インストール済みのパーツを取得
//-----------------------------------------------------
function get_modules($eid = null) {

	$module = array();

	$bit = 0;

	if (is_portal(get_gid($eid))) {
		$bit = Module::PART_OF_PORTAL;
	}
	else if (is_group($eid)) {
		$bit = Module::PART_OF_GROUPPAGE;
	}
	else {
		$bit = Module::PART_OF_MYPAGE;
	}

	$objs = ModuleManager::getInstance()->getModules();

	foreach ( $objs as $mod ) {

		if ( $bit & $mod->getType() ) {
			$module[$mod->getModName()] = $mod->getModTitle();
		}

	}

	return $module;
	
}


//-----------------------------------------------------
// * パーツ名のデフォルト名称の取得
//-----------------------------------------------------
function get_module_name($m = null) {
	
	global $SYS_MODULE_NAME;

	if (isset($SYS_MODULE_NAME[$m])) {
		return $SYS_MODULE_NAME[$m];
	}
	else if ($m) {

		try {

			$mod = Module::createInstanceByModName( $m );
			$SYS_MODULE_NAME[$m] = $mod->getModTitle();
			return $SYS_MODULE_NAME[$m];

		} catch ( Exception $e ) {
			return '!!モジュール名不明!!';
		}

	}
	
}

//-----------------------------------------------------
// * パーツ名の取得 (ユーザーによる変更後)
//-----------------------------------------------------
function get_block_name($id = null) {
	global $SYS_BLOCK_NAME;
	if ($id && isset($SYS_BLOCK_NAME[$id])) {
		return $SYS_BLOCK_NAME[$id];
	}

	if (!isset($id)) {
		return;
	}

	$d = mysql_uniq("select name from block where id = %s", mysql_num($id));
	if ($d) {
		$SYS_BLOCK_NAME[$id] = $d["name"];
	}
	return $SYS_BLOCK_NAME[$id];
}


/**
 * Description of Module
 *
 * @author ikeda
 */
class Module extends MySqlData {

	const DATABASE = "module_setting";

	const PART_OF_PORTAL = 1;
	const PART_OF_GROUPPAGE = 2;
	const PART_OF_MYPAGE = 4;

	private $r_id;
	private $r_mod_title;
	private $r_mod_name;
	private $r_type;
	private $r_addable;
	private $r_multiple;
	private $r_block_inc;

	public function __construct( $id=null, $connection=null ) {

		parent::__construct( $id, $connection );

	}

	protected function setId( $id ) { $this->r_id = (int)$id; }
	public function setModTitle( $mod_title ) { $this->r_mod_title = $mod_title; }
	public function setModName( $mod_name ) { $this->r_mod_name = $mod_name; }
	public function setType( $type ) { $this->r_type = (int)$type; }
	public function setAddable( $addable ) { $this->r_addable = (int)$addable; }
	public function setMultiple( $multiple ) { $this->r_multiple = (int)$multiple; }
	public function setBlockInc( $block_inc ) { $this->r_block_inc = $block_inc; }

	public function getId() { return $this->r_id; }
	public function getModTitle() { return $this->r_mod_title; }
	public function getModName() { return $this->r_mod_name; }
	public function getType() { return $this->r_type; }
	public function getAddable() { return $this->r_addable; }
	public function getMultiple() { return $this->r_multiple; }
	public function getBlockInc() { return $this->r_block_inc; }

	static public function getTableName() { return Module::DATABASE; }
	static public function getKeyName() { return "id"; }

	static function getModules() {

		$state = new MySqlSelectStatement( "Module" );

		return $state->exec()->getDatas();

	}

	static function createInstanceByModName( $modName ) {

		$stat = new MySqlSelectStatement( "Module" );
		$stat->setOtherConditions( "where mod_name="
									.MySqlUtil::decorateText( $modName ) );

		$datas = $stat->exec()->getDatas();

		if ( 0 == count( $datas ) ) { throw new DataNotFoundException(); }

		return $datas[0];

	}

	private function getModuleFile( $func=null, $legacy=false ) {

		if ( !$legacy ) {

			if ( file_exists( $filename = dirname(__FILE__). '/../modules/'.$this->getModName().'/module.php' ) ) {
				return $filename;
			} else {
				return false;
			}
			
		} else {

			$legacyFiles = ModuleManager::getInstance()->getLegacyFiles();

			if ( null !== $func and isset( $legacyFiles[ $func ] )
				and file_exists( $filename = dirname(__FILE__).'/../modules/'.$this->getModName().'/'.$legacyFiles[ $func ] ) ) {
				return $filename;
			} else {
				return false;
			}

		}

	}

	/**
	 * モジュールのコールバック関数を実行する.
	 *
	 * @param string $funcSuffix モジュールコールバック関数名のサフィックス.
	 * 例: mod_blog_main の場合は "main", mod_blog_main_config の場合は "main_config"
	 * @param array $params コールバック関数に渡される引数の配列.
	 * @param mixed $result コールバックの結果を格納する変数への参照.
	 *
	 * @return bool コールバックが存在し、実行されたかどうか.
	 *
	 */
	public function execCallBackFunction( $funcSuffix, $params, &$result ) {

		$filenames = array( $this->getModuleFile( $funcSuffix ),
							$this->getModuleFile( $funcSuffix, true ) );

		foreach ( $filenames as $filename ) {

			if ( $filename ) {

				require_once $filename;

				$funcName = "mod_".$this->getModName()."_".$funcSuffix;

				//	古い規約で、install関数とuninstall関数のみ例外.
				if ( basename( $filename ) == "install.php"
					and !function_exists( $funcName ) ) {
						$funcName = "mod_install";
				}
				if ( basename( $filename ) == "uninstall.php"
					and !function_exists( $funcName ) ) {
						$funcName = "mod_uninstall";
				}

				if ( function_exists( $funcName ) ) {
					$result = call_user_func_array( $funcName, $params );
					return true;
				}

			}

		}

		return false;

	}

}

class ModuleManager {

	static private $instance;

	private $legacyFiles;

	private $modules;

	private function __construct() {

		$this->legacyFiles = array( "block" => "block.php",
								"block_config" => "block.php",
								"main" => "main.php",
								"main_config" => "main.php",
								"editmenu" => "config.php",
								"install" => "install.php",
								"uninstall" => "uninstall.php");

		$this->modules = Module::getModules();

	}

	static public function getInstance() {

		if ( null === ModuleManager::$instance ) {
			ModuleManager::$instance = new ModuleManager();
		}

		return ModuleManager::$instance;

	}

	public function getModule( $modName ) {

		foreach ( $this->modules as $module ) {

			if ( $modName == $module->getModName() ) {
				return $module;
			}

		}

		throw new ModuleNotFoundException( $modName );

	}

	public function getModules() {
		return $this->modules;
	}

	public function getLegacyFiles() {
		return $this->legacyFiles;
	}

	/**
	 * 登録された全てのモジュールのコールバック関数を実行する.
	 *
	 * @param string $funcSuffix モジュールコールバック関数名のサフィックス.
	 * 例: mod_blog_main の場合は "main", mod_blog_main_config の場合は "main_config"
	 * @param array $params コールバック関数に渡される引数の配列.
	 *
	 * @return array コールバックが実行されたモジュール名をキーとし、関数の戻り値を
	 * 値とする連想配列.
	 *
	 */
	public function execCallbackFunctions( $funcSuffix, $params=array() ) {

		$results = array();

		foreach ( $this->modules as $module ) {

			if ( $module->execCallbackFunction( $funcSuffix, $params, $result ) )
				$results[ $module->getModName() ] = $result;

		}

		return $results;

	}

}

class ModuleNotFoundException extends Exception {

	public function __construct( $message ) {
		parent::__construct( $message );
	}

}

?>
