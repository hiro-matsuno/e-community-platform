<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_contact_install(){
	$sql = <<< __SQL__
CREATE TABLE `mod_contact_setting` (
  `id` bigint(20) NOT NULL default '0',
  `subject` text,
  `note` text,
  `href` text,
  `mail` text,
  `thxmsg` text,
  `css` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
__SQL__;
	$d = mysql_exec($sql);
	if(!$d) {
		$error_messg = mysql_error();
		return <<< __MSG__
<h2>SQL error</h2>
<h4>Query</h4>
$sql
<h4>Error message</h4>
$error_messg;
__MSG__;
	}

	$sql = <<< __SQL__
CREATE TABLE `mod_contact_form_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `num` int(11) NOT NULL default '0',
  `type` text,
  `title` text,
  `req_check` smallint(6) NOT NULL default '0',
  `comment` text,
  `opt_size` text,
  `opt_list` text,
  `def_val` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
__SQL__;
	$d = mysql_exec($sql);
	if(!$d) {
		$error_messg = mysql_error();
		return <<< __MSG__
<h2>SQL error</h2>
<h4>Query</h4>
$sql
<h4>Error message</h4>
$error_messg;
__MSG__;
	}

	$sql = <<< __SQL__
CREATE TABLE `mod_contact_form_pos` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `position` smallint(6) default NULL,
  `parent` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
__SQL__;
	$d = mysql_exec($sql);
	if(!$d) {
		$error_messg = mysql_error();
		return <<< __MSG__
<h2>SQL error</h2>
<h4>Query</h4>
$sql
<h4>Error message</h4>
$error_messg;
__MSG__;
	}



	$sql = <<< __SQL__
CREATE TABLE `mod_contact_send_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `data` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `eid` (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
__SQL__;
	$d = mysql_exec($sql);
	if(!$d) {
		$error_messg = mysql_error();
		return <<< __MSG__
<h2>SQL error</h2>
<h4>Query</h4>
$sql
<h4>Error message</h4>
$error_messg;
__MSG__;
	}

	return 'パーツのインストールに成功しました。';
}
?>
