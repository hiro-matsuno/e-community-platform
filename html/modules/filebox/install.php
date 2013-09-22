<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

function mod_filebox_install() {
	
	$result = mysql_query( "create table if not exists filebox_block_setting("
							." id bigint primary key auto_increment,"
							." block_id bigint,"
							." setting tinyint,"
							." num_elements tinyint )" );
	
	if ( !$result ) return false;
	
	return "インストールしました.";

}

?>