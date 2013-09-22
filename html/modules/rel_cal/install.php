<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
function mod_rel_cal_install(){
	$sql = <<< __SQL__
CREATE TABLE `schedule_data_add_ical` (
  `id` bigint(20) NOT NULL default '0',
  `ical_uid` text,
  `location` text,
  `organizer` text,
  `class` text,
  `alarmDisp` text,
  `alarmEmail` text,
  `attendee` text,
  `sequence` int not null default '0',
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`),
  kEY `tag_ical_uid` (`ical_uid`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

create table `rel_cal_blk_rel`(
  `gid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `blk_id` bigint(20) NOT NULL default '0',
  KEY `tag_g_blk` (`gid`),
  KEY `tag_m_blk` (`uid`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
