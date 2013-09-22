/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
--
-- Table structure for table `enquete_data`
--

DROP TABLE IF EXISTS `enquete_data`;
CREATE TABLE `enquete_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `subject` text,
  `note` text,
  `type` tinyint(11) default NULL,
  `result` tinyint(4) NOT NULL default '0',
  `thxmsg` text,
  `startymd` datetime default NULL,
  `endymd` datetime default NULL,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `enquete_form_data`
--

DROP TABLE IF EXISTS `enquete_form_data`;
CREATE TABLE `enquete_form_data` (
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
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

--
-- Table structure for table `enquete_status`
--

DROP TABLE IF EXISTS `enquete_status`;
CREATE TABLE `enquete_status` (
  `pid` bigint(20) NOT NULL default '0',
  `eid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `enquete_vote_data`
--

DROP TABLE IF EXISTS `enquete_vote_data`;
CREATE TABLE `enquete_vote_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `num` int(11) NOT NULL default '0',
  `data` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `eid` (`eid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

INSERT INTO `module_setting` VALUES
(12,'アンケート','enquete',7,1,1,NULL);
