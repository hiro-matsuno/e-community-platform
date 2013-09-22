<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * Description of MySqlRecord
 *
 * @author ikeda
 */
interface MySqlRecord {

	public function regist();
	public function delete();

	//	these method must be implemented by sub classes.
	//	static public function getMemberNames();
	//	static public function getTableName();
	//	static public function getKeyName();

}
?>
