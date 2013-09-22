/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
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
