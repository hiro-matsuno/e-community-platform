<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

if(!require_once dirname(__FILE__). '/../../lib/class_http.php'){
	header("HTTP/1.0 501 Script Error");
	echo "aaaInternal Server Error";
	exit();
	; 
}

$url = isset($_GET['url']) ? $_GET['url'] : false;

if (!$url) {
	header("HTTP/1.0 400 Bad Request");
	echo "Bad Request";
	exit();
}

if (!$h = new http()) {
	header("HTTP/1.0 501 Script Error");
	echo "Internal Server Error";
	exit();
}

$h->url = $url;
$h->postvars = $_POST;
if (!$h->fetch($h->url)) {
	header("HTTP/1.0 501 Script Error");
	echo "Internal Server Error (2)";
    exit();
}

header('Content-Type: application/xml');

echo $h->body;

?>
