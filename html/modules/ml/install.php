<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_ml_install(){
	$sql = <<< __SQL__
CREATE TABLE IF NOT EXISTS `mod_ml_backnumber` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `ml_id` bigint(20) unsigned NOT NULL default '0',
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
CREATE TABLE IF NOT EXISTS `mod_ml_data` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `ml_id` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
  `subject` text,
  `message` text,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
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
CREATE TABLE IF NOT EXISTS `mod_ml_member` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ml_id` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned default '0',
  `status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mod_ml_member_idx` (`ml_id`)
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
CREATE TABLE IF NOT EXISTS `mod_ml_post_key` (
  `id` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `eid` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
  `updymd` datetime default NULL,
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
CREATE TABLE IF NOT EXISTS `mod_ml_setting` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `title` text,
  `desc` text,
  `ml_prefix` text,
  `archive_pmt` int(2) default NULL,
  `header` text,
  `footer` text,
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

	return 'パーツのインストールに成功しました。';
}
?>
