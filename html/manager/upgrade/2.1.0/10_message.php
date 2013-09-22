<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

try {

	MySqlPlaneStatement::execNow( <<<__SQL_CODE__

    alter table message_data modify column initymd
	timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

__SQL_CODE__
	);

} catch ( Exception $e ) {
	throw new Exception( __FILE__." の実行中にエラーが発生しました.<br/>\n" );
}

?>
