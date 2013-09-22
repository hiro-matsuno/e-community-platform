<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_enquete_install(){
	$sql = <<< __SQL__
CREATE TABLE `enquete_data` (
  `id` bigint(20) NOT NULL default '0',
  `subject` text,
  `note` text,
  `type` tinyint(11) default NULL,
  `dup` int(2) default '0',
  `result` tinyint(4) NOT NULL default '0',
  `thxmsg` text,
  `tell_vote` tinyint(4) default '1',
  `startymd` datetime default NULL,
  `endymd` datetime default NULL,
  `initymd` datetime default NULL,
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
CREATE TABLE `enquete_form_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `num` int(11) NOT NULL default '0',
  `uniq_id` bigint(20) unsigned NOT NULL default '0',
  `type` text,
  `title` text,
  `req_check` smallint(6) NOT NULL default '0',
  `admin_only` smallint(6) NOT NULL default '0',
  `comment` text,
  `opt_size` text,
  `opt_list` text,
  `def_val` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=872 DEFAULT CHARSET=utf8;
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
CREATE TABLE `enquete_status` (
  `pid` bigint(20) NOT NULL default '0',
  `eid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`pid`)
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
CREATE TABLE `enquete_vote_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `uniq_id` text,
  `cookie` text,
  `num` bigint(20) default NULL,
  `data` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `eid` (`eid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=16687 DEFAULT CHARSET=utf8;
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
CREATE TABLE `mod_enquete_allow` (
  `eid` bigint(20) unsigned NOT NULL default '0',
  `type` smallint(6) default NULL,
  PRIMARY KEY  (`eid`)
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
CREATE TABLE `mod_enquete_csv` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) unsigned NOT NULL default '0',
  `data` text,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=255 DEFAULT CHARSET=utf8;
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
CREATE TABLE `mod_enquete_csv_past` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) unsigned NOT NULL default '0',
  `data` text,
  `initymd` datetime default NULL,
  `past_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
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
CREATE TABLE `mod_enquete_vcheck` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `eid` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
  `cookie` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;
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
CREATE TABLE `mod_enquete_element_relation` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mod_enquete_element_relation` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
__SQL__;
	$d = mysql_exec($sql);
	if($d){
		return 'パーツのインストールに成功しました。';
	}else{
		$error_messg = mysql_error();
		return <<< __MSG__
<h2>SQL error</h2>
<h4>Query</h4>
$sql
<h4>Error message</h4>
$error_messg;
__MSG__;
	}
}
?>
