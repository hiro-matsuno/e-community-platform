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

	CREATE TABLE IF NOT EXISTS `mod_menu_data_pos` (
	`id` bigint(20) unsigned NOT NULL default '0',
	`position` smallint(6) default NULL,
	`parent` bigint(20) unsigned NOT NULL default '0',
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

} catch ( Exception $e ) {
	
	throw new Exception( __FILE__." の実行中にエラーが発生しました.<br/>\n" );

}

?>
