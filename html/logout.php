<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__)."/lib.php";

$ref  = isset($_REQUEST['ref'])  ? urldecode($_REQUEST['ref']) : '/';

session_start();

if ( null !== ( $me = User::getMe() ) ) {

	$results = ModuleManager::getInstance()
	->execCallbackFunctions( "pre_logout", array( $me->getId() ) );

	$logout = true;

	foreach ( $results as $res ) {
		if ( !$res ) { $logout = false; break; }
	}

	if ( $logout ) { session_unset(); }

	ModuleManager::getInstance()
	->execCallbackFunctions( "post_logout", array( $logout ? $me->getId() : 0 ) );

} else {
	//	ログインしていなかった.
}

header("Location: ".$ref);

exit(0);
	
?>
