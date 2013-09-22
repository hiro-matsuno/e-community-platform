<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_fbbs_install(){
	$sql = <<< __SQL__
CREATE TABLE `mod_fbbs_setting` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `view_type` tinyint(4) NOT NULL default '0',
  `view_num` tinyint(4) NOT NULL default '0',
  `rec_num` smallint(6) default NULL,
  `backnumber` tinyint(4) NOT NULL default '0',
  `backnum_pmt` tinyint(2) NOT NULL default '0',
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

	$sql = <<< __SQL__
CREATE TABLE `mod_fbbs_elm_owner` (
  `eid` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
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
CREATE TABLE `mod_fbbs_data` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `parent_id` bigint(20) unsigned NOT NULL default '0',
  `top_id` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `body` text,
  `uid` bigint(20) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `mail` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `initymd` datetime default NULL,
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
CREATE TABLE `mod_fbbs_backnumber` (
  `thread_id` bigint(20) unsigned NOT NULL default '0',
  `initymd` datetime default NULL,
  PRIMARY KEY  (`thread_id`)
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
CREATE TABLE `mod_fbbs_allow` (
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
CREATE TABLE `mod_fbbs_element_relation` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
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
