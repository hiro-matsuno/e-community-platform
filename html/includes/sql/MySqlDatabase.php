<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * Description of MySqlDatabase
 *
 * @author ikeda
 */
class MySqlDatabase {

	private $dbname;

	public function __construct( $dbname ) {

		$this->dbname = $dbname;

	}

}
?>
