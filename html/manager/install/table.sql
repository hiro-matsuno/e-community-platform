/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
-- e-comunity 2.0 database table creation data

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `option_key` text NOT NULL,
  `option_value` text NOT NULL,
  `uid` int(11) DEFAULT '0',
  `gid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `block`
--

DROP TABLE IF EXISTS `block`;
CREATE TABLE `block` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `module` text,
  `name` text,
  `del_lock` int(11) NOT NULL default '0',
  `hpos` int(11) NOT NULL default '0',
  `vpos` int(11) NOT NULL default '0',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `block_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `blog_archive_list`
--

DROP TABLE IF EXISTS `blog_archive_list`;
CREATE TABLE `blog_archive_list` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `blog_id` bigint(20) unsigned NOT NULL default '0',
  KEY `blog_archive_list_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `blog_archive_setting`
--

DROP TABLE IF EXISTS `blog_archive_setting`;
CREATE TABLE `blog_archive_setting` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `latest_num` int(11) NOT NULL default '5',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `blog_data`
--

DROP TABLE IF EXISTS `blog_data`;
CREATE TABLE `blog_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `blog_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `blog_setting`
--

DROP TABLE IF EXISTS `blog_setting`;
CREATE TABLE `blog_setting` (
  `id` bigint(20) NOT NULL default '0',
  `view_type` tinyint(4) NOT NULL default '0',
  `view_num` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_auth`
--

DROP TABLE IF EXISTS `bosai_web_auth`;
CREATE TABLE `bosai_web_auth` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_block`
--

DROP TABLE IF EXISTS `bosai_web_block`;
CREATE TABLE `bosai_web_block` (
  `eid` bigint(20) NOT NULL default '0',
  `site_id` bigint(20) NOT NULL default '0',
  `block_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`block_id`),
  KEY `eid` (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_category`
--

DROP TABLE IF EXISTS `bosai_web_category`;
CREATE TABLE `bosai_web_category` (
  `eid` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `num` bigint(20) NOT NULL default '0',
  `name` text,
  PRIMARY KEY  (`eid`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_count`
--

DROP TABLE IF EXISTS `bosai_web_count`;
CREATE TABLE `bosai_web_count` (
  `id` bigint(20) NOT NULL default '0',
  `count` bigint(20) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_setting`
--

DROP TABLE IF EXISTS `bosai_web_setting`;
CREATE TABLE `bosai_web_setting` (
  `id` bigint(20) NOT NULL default '0',
  `msg` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_template`
--

DROP TABLE IF EXISTS `bosai_web_template`;
CREATE TABLE `bosai_web_template` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `num` bigint(20) NOT NULL default '0',
  `category` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `num` (`num`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_template_bysite`
--

DROP TABLE IF EXISTS `bosai_web_template_bysite`;
CREATE TABLE `bosai_web_template_bysite` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `num` bigint(20) NOT NULL default '0',
  `category` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `num` (`num`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `bosai_web_template_rel`
--

DROP TABLE IF EXISTS `bosai_web_template_rel`;
CREATE TABLE `bosai_web_template_rel` (
  `eid` bigint(20) NOT NULL default '0',
  `site_id` bigint(20) NOT NULL default '0',
  KEY `eid` (`eid`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `comment_allow`
--

DROP TABLE IF EXISTS `comment_allow`;
CREATE TABLE `comment_allow` (
  `eid` bigint(20) NOT NULL default '0',
  `unit` bigint(20) default '0',
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `comment_alt`
--

DROP TABLE IF EXISTS `comment_alt`;
CREATE TABLE `comment_alt` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `name` text,
  `url` text,
  `msg` text,
  `host` text,
  `public` smallint(6) NOT NULL default '0',
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `comment_alt_eid_idx` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8;

--
-- Table structure for table `common_css`
--

DROP TABLE IF EXISTS `common_css`;
CREATE TABLE `common_css` (
  `id` bigint(20) NOT NULL default '0',
  `css` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `conf_agreement`
--

DROP TABLE IF EXISTS `conf_agreement`;
CREATE TABLE `conf_agreement` (
  `id` bigint(20) NOT NULL default 0,
  `title` varchar(255) default NULL,
  `body` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Table structure for table `conf_group_level`
--

DROP TABLE IF EXISTS `conf_group_level`;
CREATE TABLE `conf_group_level` (
  `id` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `is_admin` smallint(6) NOT NULL default '1',
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `conf_user_level`
--

DROP TABLE IF EXISTS `conf_user_level`;
CREATE TABLE `conf_user_level` (
  `id` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `is_admin` smallint(6) NOT NULL default '1',
  `name` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `element`
--

DROP TABLE IF EXISTS `element`;
CREATE TABLE `element` (
  `id` bigint(20) NOT NULL default '0',
  `unit` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `element_sequence`
--

DROP TABLE IF EXISTS `element_sequence`;
CREATE TABLE `element_sequence` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `filebox_data`
--

DROP TABLE IF EXISTS `filebox_data`;
CREATE TABLE `filebox_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `name` text,
  `filename` text,
  `summary` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `filesize` int(11) DEFAULT NULL,
  `trashed` int(11) DEFAULT 0,
  `org_filename` text,
  PRIMARY KEY  (`id`),
  KEY `filebox_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `filebox_config`
--

DROP TABLE IF EXISTS `filebox_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filebox_config` (
  `disk_quota` bigint(20) NOT NULL DEFAULT '0',
  `user_quota` bigint(20) NOT NULL DEFAULT '0',
  `youtube_user` text,
  `youtube_passwd` text,
  `group_level` int(11) NOT NULL DEFAULT '50',
  `user_level` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filebox_setting`
--

DROP TABLE IF EXISTS `filebox_setting`;
CREATE TABLE `filebox_setting` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `title` text,
  `summary` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `filebox_setting_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `friend_extra`
--

DROP TABLE IF EXISTS `friend_extra`;
CREATE TABLE `friend_extra` (
  `gid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `name` text,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `friend_group`
--

DROP TABLE IF EXISTS `friend_group`;
CREATE TABLE `friend_group` (
  `gid` bigint(20) NOT NULL default '0',
  `owner` bigint(20) NOT NULL default '0',
  `pid` bigint(20) default NULL,
  `name` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`gid`),
  KEY `friend_group_owner_idx` (`owner`),
  KEY `friend_group_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `friend_tmp`
--

DROP TABLE IF EXISTS `friend_tmp`;
CREATE TABLE `friend_tmp` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `to_uid` bigint(20) unsigned default NULL,
  `from_uid` bigint(20) unsigned default NULL,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Table structure for table `friend_user`
--

DROP TABLE IF EXISTS `friend_user`;
CREATE TABLE `friend_user` (
  `gid` bigint(20) NOT NULL default '0',
  `owner` bigint(20) NOT NULL default '0',
  `pid` bigint(20) default NULL,
  `name` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`gid`),
  KEY `friend_user_owner_idx` (`owner`),
  KEY `friend_user_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `fwd_mail`
--

DROP TABLE IF EXISTS `fwd_mail`;
CREATE TABLE `fwd_mail` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` bigint(20) unsigned NOT NULL default '0',
  `mail` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fwd_mail_uid_idx` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

--
-- Table structure for table `group_app`
--

DROP TABLE IF EXISTS `group_app`;
CREATE TABLE `group_app` (
  `gid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `comment` text,
  `initymd` datetime default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `group_joinable`
--

DROP TABLE IF EXISTS `group_joinable`;
CREATE TABLE `group_joinable` (
  `gid` bigint(20) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `ent_max` int(11) NOT NULL default '0',
  `terms` text,
  `byelaw` text,
  `notice` text,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `group_member`
--

DROP TABLE IF EXISTS `group_member`;
CREATE TABLE `group_member` (
  `gid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  KEY `group_member_gid_idx` (`gid`),
  KEY `group_member_uid_idx` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `group_sequence`
--

DROP TABLE IF EXISTS `group_sequence`;
CREATE TABLE `group_sequence` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `icons`
--

DROP TABLE IF EXISTS `icons`;
CREATE TABLE `icons` (
  `id` bigint(20) NOT NULL default '0',
  `name` text,
  `summary` text,
  `path` text,
  `size_x` int(11) default NULL,
  `size_y` int(11) default NULL,
  `xunit` float default NULL,
  `yunit` float default NULL,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `kml_cache`
--

DROP TABLE IF EXISTS `kml_cache`;
CREATE TABLE `kml_cache` (
  `id` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `rowdata` text,
  PRIMARY KEY  (`id`),
  KEY `kml_cache_uid_idx` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `kml_url`
--

DROP TABLE IF EXISTS `kml_url`;
CREATE TABLE `kml_url` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `url` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `kml_url_data`
--

DROP TABLE IF EXISTS `kml_url_data`;
CREATE TABLE `kml_url_data` (
  `id` bigint(20) NOT NULL default '0',
  `kml_id` bigint(20) NOT NULL default '0',
  `vpos` int(11) default '0',
  `visible` int(11) default '0',
  KEY `kml_url_data_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `logs_edit`
--

DROP TABLE IF EXISTS `logs_edit`;
CREATE TABLE `logs_edit` (
  `id` bigint(20) NOT NULL auto_increment,
  `log` text,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=460 DEFAULT CHARSET=utf8;

--
-- Table structure for table `mailmag_data`
--

DROP TABLE IF EXISTS `mailmag_data`;
CREATE TABLE `mailmag_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `mailmag_setting`
--

DROP TABLE IF EXISTS `mailmag_setting`;
CREATE TABLE `mailmag_setting` (
  `eid` bigint(20) NOT NULL default '0',
  `header` text,
  `footer` text,
  `disp_num` text,
  `disp_body` text,
  `write_level` smallint(6) default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_base`
--

DROP TABLE IF EXISTS `map_base`;
CREATE TABLE `map_base` (
  `id` bigint(20) NOT NULL default '0',
  `map_type` int(11) default NULL,
  `base_url` text,
  `bbox_format` text,
  `use_geo` int(11) default NULL,
  `cp_name` text,
  `cp_name_short` text,
  `cp_text` text,
  `min_scale` int(11) default NULL,
  `max_scale` int(11) default NULL,
  `opacity` text,
  `ispng` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_base_data`
--

DROP TABLE IF EXISTS `map_base_data`;
CREATE TABLE `map_base_data` (
  `id` bigint(20) NOT NULL default '0',
  `map_id` bigint(20) default NULL,
  `vpos` int(11) default NULL,
  KEY `map_base_data_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_data`
--

DROP TABLE IF EXISTS `map_data`;
CREATE TABLE `map_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) unsigned NOT NULL default '0',
  `type` text,
  `lat` text,
  `lon` text,
  `zoom` text,
  `icon` bigint(20) default NULL,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `vernum` int(11) default '0',
  KEY `map_data_id` (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_kml`
--

DROP TABLE IF EXISTS `map_kml`;
CREATE TABLE `map_kml` (
  `id` bigint(20) NOT NULL default '0',
  `kml` bigint(20) default NULL,
  KEY `map_kml_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_kml_data`
--

DROP TABLE IF EXISTS `map_kml_data`;
CREATE TABLE `map_kml_data` (
  `id` bigint(20) NOT NULL default '0',
  `kml_id` bigint(20) NOT NULL default '0',
  `vpos` int(11) default '0',
  `visible` int(11) default '0',
  KEY `map_kml_data_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_layer`
--

DROP TABLE IF EXISTS `map_layer`;
CREATE TABLE `map_layer` (
  `id` bigint(20) NOT NULL default '0',
  `map_type` int(11) default NULL,
  `base_url` text,
  `bbox_format` text,
  `use_geo` int(11) default NULL,
  `cp_name` text,
  `cp_name_short` text,
  `cp_text` text,
  `min_scale` int(11) default NULL,
  `max_scale` int(11) default NULL,
  `opacity` text,
  `ispng` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_layer_data`
--

DROP TABLE IF EXISTS `map_layer_data`;
CREATE TABLE `map_layer_data` (
  `id` bigint(20) NOT NULL default '0',
  `layer_id` bigint(20) NOT NULL default '0',
  `vpos` int(11) default '0',
  `visible` int(11) default '0',
  KEY `map_layer_data_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `map_setting`
--

DROP TABLE IF EXISTS `map_setting`;
CREATE TABLE `map_setting` (
  `id` bigint(20) NOT NULL default '0',
  `title` text,
  `header` text,
  `footer` text,
  `home_point` bigint(20) default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `menu_data`
--

DROP TABLE IF EXISTS `menu_data`;
CREATE TABLE `menu_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) unsigned NOT NULL default '0',
  `title` text,
  `href` text,
  `target` text,
  `inline` int(11) default NULL,
  `hpos` int(11) default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `mod_menu_data_pos`
--

DROP TABLE IF EXISTS `mod_menu_data_pos`;
CREATE TABLE IF NOT EXISTS `mod_menu_data_pos` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `position` smallint(6) default NULL,
  `parent` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `menubar`
--

DROP TABLE IF EXISTS `menubar`;
CREATE TABLE `menubar` (
  `id` bigint(20) NOT NULL default '0',
  `menubar` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `menubar_css`
--

DROP TABLE IF EXISTS `menubar_css`;
CREATE TABLE `menubar_css` (
  `id` int(11) NOT NULL auto_increment,
  `title` text,
  `css` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Table structure for table `message_data`
--

DROP TABLE IF EXISTS `message_data`;
CREATE TABLE `message_data` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `from_uid` bigint(20) unsigned NOT NULL default '0',
  `to_uid` bigint(20) unsigned NOT NULL default '0',
  `is_new` smallint(6) NOT NULL default '1',
  `subject` varchar(128) default NULL,
  `message` text,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13846 DEFAULT CHARSET=utf8;

--
-- Table structure for table `mobile_update_setting`
--

DROP TABLE IF EXISTS `mobile_update_setting`;
CREATE TABLE `mobile_update_setting` (
  `id` bigint(20) NOT NULL auto_increment,
  `uid` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0',
  `email` text NOT NULL,
  `post_key` text,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

--
-- Table structure for table `mod_bbs_comment`
--

DROP TABLE IF EXISTS `mod_bbs_comment`;
CREATE TABLE `mod_bbs_comment` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `body` text,
  `uid` bigint(20) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `mail` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `initymd` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `mod_bbs_setting`
--

DROP TABLE IF EXISTS `mod_bbs_setting`;
CREATE TABLE `mod_bbs_setting` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `view_type` tinyint(4) NOT NULL default '0',
  `view_num` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `mod_bbs_thread`
--

DROP TABLE IF EXISTS `mod_bbs_thread`;
CREATE TABLE `mod_bbs_thread` (
  `id` bigint(20) unsigned NOT NULL default '0',
  `pid` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(255) default NULL,
  `body` text,
  `uid` bigint(20) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `mail` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `initymd` datetime default NULL,
  `updymd` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table structure for table `module_setting`
--

DROP TABLE IF EXISTS `module_setting`;
CREATE TABLE `module_setting` (
  `id` int(11) NOT NULL auto_increment,
  `mod_title` text,
  `mod_name` text,
  `type` int(11) default NULL,
  `addable` tinyint(4) NOT NULL default '1',
  `multiple` int(11) default NULL,
  `block_inc` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Table structure for table `mpost`
--

DROP TABLE IF EXISTS `mpost`;
CREATE TABLE `mpost` (
  `id` int(11) NOT NULL auto_increment,
  `post_id` text,
  `eid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0',
  `module` text,
  `hash_id` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8;

--
-- Table structure for table `mpost_mailq`
--

DROP TABLE IF EXISTS `mpost_mailq`;
CREATE TABLE `mpost_mailq` (
  `post_id` text,
  `eid` bigint(20) default NULL,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (
  `id` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0',
  `enable` tinyint(1) default '1',
  `sitename` text,
  `description` text,
  `skin` text,
  `initymd` datetime NOT NULL default '0000-00-00 00:00:00',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `mypage_uid_idx` (`uid`),
  KEY `gpage_gid_idx` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `owner`
--

DROP TABLE IF EXISTS `owner`;
CREATE TABLE `owner` (
  `id` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `owner_uid_idx` (`uid`),
  KEY `owner_gid_idx` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `page_data`
--

DROP TABLE IF EXISTS `page_data`;
CREATE TABLE `page_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `page_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `page_setting`
--

DROP TABLE IF EXISTS `page_setting`;
CREATE TABLE `page_setting` (
  `id` bigint(20) NOT NULL default '0',
  `title` text,
  `summary` text,
  `disp_type` int(11) default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `portal`
--

DROP TABLE IF EXISTS `portal`;
CREATE TABLE `portal` (
  `gid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `profile_data`
--

DROP TABLE IF EXISTS `profile_data`;
CREATE TABLE `profile_data` (
  `id` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0',
  `thumb` text,
  `name` text,
  `name_kana` text,
  `place` text,
  `sex` bigint(20) default NULL,
  `birthday` datetime,
  `blood` bigint(20) default NULL,
  `birthplace` text,
  `hobby` text,
  `job` text,
  `profile` text,
  `fav1` text,
  `fav2` text,
  `fav3` text,
  `zip` text,
  `address` text,
  `tel` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `profile_pmt`
--

DROP TABLE IF EXISTS `profile_pmt`;
CREATE TABLE `profile_pmt` (
  `id` bigint(20) NOT NULL default '0',
  `thumb` bigint(20) NOT NULL default '2',
  `name` bigint(20) NOT NULL default '2',
  `name_kana` bigint(20) NOT NULL default '2',
  `place` bigint(20) NOT NULL default '2',
  `sex` bigint(20) default NULL default '2',
  `birthday` bigint(20) NOT NULL default '2',
  `blood` bigint(20) default NULL default '2',
  `birthplace` bigint(20) NOT NULL default '2',
  `hobby` bigint(20) NOT NULL default '2',
  `job` bigint(20) NOT NULL default '2',
  `profile` bigint(20) NOT NULL default '2',
  `fav1` bigint(20) NOT NULL default '2',
  `fav2` bigint(20) NOT NULL default '2',
  `fav3` bigint(20) NOT NULL default '2',
  `zip` bigint(20) NOT NULL default '2',
  `address` bigint(20) NOT NULL default '2',
  `tel` bigint(20) NOT NULL default '2',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `regist_setting`
--

DROP TABLE IF EXISTS `regist_setting`;
CREATE TABLE `regist_setting` (
  `id` bigint(20) NOT NULL default '0',
  `use_confirm` smallint(6) default '0',
  `app_level` int(11) default '100',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `regist_temp`
--

DROP TABLE IF EXISTS `regist_temp`;
CREATE TABLE `regist_temp` (
  `uid` bigint(20) NOT NULL default '0',
  `auth_code` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `reporter_auth`
--

DROP TABLE IF EXISTS `reporter_auth`;
CREATE TABLE `reporter_auth` (
  `id` bigint(20) NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `reporter_block`
--

DROP TABLE IF EXISTS `reporter_block`;
CREATE TABLE `reporter_block` (
  `eid` bigint(20) NOT NULL default '0',
  `site_id` bigint(20) NOT NULL default '0',
  `block_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`block_id`),
  KEY `eid` (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `reporter_setting`
--

DROP TABLE IF EXISTS `reporter_setting`;
CREATE TABLE `reporter_setting` (
  `id` bigint(20) NOT NULL default '0',
  `msg` text,
  `auth_mode` tinyint(4) NOT NULL default '0',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `rss_crawl_time`
--

DROP TABLE IF EXISTS `rss_crawl_time`;
CREATE TABLE `rss_crawl_time` (
  `eid` bigint(20) NOT NULL default '0',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `rss_data`
--

DROP TABLE IF EXISTS `rss_data`;
CREATE TABLE `rss_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `sitename` text,
  `title` text,
  `url` text,
  `body` text,
  `initymd` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `rss_data_eid` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=897619 DEFAULT CHARSET=utf8;

--
-- Table structure for table `rss_data_tmp`
--

DROP TABLE IF EXISTS `rss_data_tmp`;
CREATE TABLE `rss_data_tmp` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `sitename` text,
  `title` text,
  `url` text,
  `body` text,
  `initymd` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `rss_data_tmp_eid` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=973811 DEFAULT CHARSET=utf8;

--
-- Table structure for table `rss_setting`
--

DROP TABLE IF EXISTS `rss_setting`;
CREATE TABLE `rss_setting` (
  `eid` bigint(20) NOT NULL default '0',
  `header` text,
  `footer` text,
  `keyword` text,
  `disp_type` tinyint(4) NOT NULL default '1',
  `disp_num` tinyint(4) NOT NULL default '10',
  `disp_title` tinyint(4) NOT NULL default '0',
  `disp_body` tinyint(4) NOT NULL default '0',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `rss_url`
--

DROP TABLE IF EXISTS `rss_url`;
CREATE TABLE `rss_url` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `num` int(11) NOT NULL default '1',
  `url` text,
  PRIMARY KEY  (`id`),
  KEY `rss_url_eid` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=537 DEFAULT CHARSET=utf8;

--
-- Table structure for table `schedule_data`
--

DROP TABLE IF EXISTS `schedule_data`;
CREATE TABLE `schedule_data` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  `subject` text,
  `body` text,
  `startymd` datetime default NULL,
  `endymd` datetime default NULL,
  `initymd` datetime default NULL,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `tag_data_pid_idx` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `sys_log`
--

DROP TABLE IF EXISTS `sys_log`;
CREATE TABLE `sys_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `log` text,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5977 DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag_data`
--

DROP TABLE IF EXISTS `tag_data`;
CREATE TABLE `tag_data` (
  `pid` bigint(20) NOT NULL default '0',
  `tag_id` bigint(20) NOT NULL default '0',
  `blk_id` bigint(20) NOT NULL default '0',
  KEY `tag_data_pid_idx` (`pid`),
  KEY `tag_data_tag_id_idx` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag_setting`
--

DROP TABLE IF EXISTS `tag_setting`;
CREATE TABLE `tag_setting` (
  `id` bigint(20) NOT NULL default '0',
  `keyword` text,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `tagreader_crawl_time`
--

DROP TABLE IF EXISTS `tagreader_crawl_time`;
CREATE TABLE `tagreader_crawl_time` (
  `eid` bigint(20) NOT NULL default '0',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `tagreader_data`
--

DROP TABLE IF EXISTS `tagreader_data`;
CREATE TABLE `tagreader_data` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` int(20) NOT NULL default '0',
  `blk_id` int(20) NOT NULL default '0',
  `article_id` int(20) NOT NULL default '0',
  `sitename` text,
  `title` text,
  `url` text,
  `initymd` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `tagreader_data_eid` (`eid`),
  KEY `tagreader_data_article_id` (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=361701 DEFAULT CHARSET=utf8;

--
-- Table structure for table `tagreader_data_tmp`
--

DROP TABLE IF EXISTS `tagreader_data_tmp`;
CREATE TABLE `tagreader_data_tmp` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` int(20) NOT NULL default '0',
  `blk_id` int(20) NOT NULL default '0',
  `article_id` int(20) NOT NULL default '0',
  `sitename` text,
  `title` text,
  `url` text,
  `initymd` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=329969 DEFAULT CHARSET=utf8;

--
-- Table structure for table `tagreader_setting`
--

DROP TABLE IF EXISTS `tagreader_setting`;
CREATE TABLE `tagreader_setting` (
  `eid` bigint(20) NOT NULL default '0',
  `mod_target` text,
  `header` text,
  `footer` text,
  `keyword` text,
  `disp_num` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `tb_test`
--

DROP TABLE IF EXISTS `tb_test`;
CREATE TABLE `tb_test` (
  `id` bigint(20) NOT NULL auto_increment,
  `str` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Table structure for table `testdb`
--

DROP TABLE IF EXISTS `testdb`;
CREATE TABLE `testdb` (
  `id` int(11) NOT NULL default '0',
  `name` text,
  `upd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `testtable333`
--

DROP TABLE IF EXISTS `testtable333`;
CREATE TABLE `testtable333` (
  `id` bigint(20) NOT NULL default '0',
  `coll` text,
  `sss5` tinyint(4) NOT NULL default '2',
  `test4` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `theme_layout`
--

DROP TABLE IF EXISTS `theme_layout`;
CREATE TABLE `theme_layout` (
  `id` bigint(20) NOT NULL auto_increment,
  `filename` text,
  `title` text,
  `column` tinyint(4) NOT NULL default '2',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Table structure for table `theme_skin`
--

DROP TABLE IF EXISTS `theme_skin`;
CREATE TABLE `theme_skin` (
  `id` bigint(20) NOT NULL auto_increment,
  `filename` text,
  `thumb` text,
  `title` text,
  `description` text,
  `layout_id` bigint(20) NOT NULL default '0',
  `pmt` int(11) NOT NULL default '0',
  `parent_skin_id` bigint(20) NOT NULL default '0',
  `var_title` text,
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

--
-- Table structure for table `trackback`
--

DROP TABLE IF EXISTS `trackback`;
CREATE TABLE `trackback` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `title` text,
  `excerpt` text,
  `url` text,
  `blog_name` text,
  `host` text,
  `date` datetime default NULL,
  `initymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `trackback_eid_idx` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

--
-- Table structure for table `trackback_allow`
--

DROP TABLE IF EXISTS `trackback_allow`;
CREATE TABLE `trackback_allow` (
  `eid` bigint(20) NOT NULL default '0',
  `unit` bigint(20) default '0',
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `trackback_url`
--

DROP TABLE IF EXISTS `trackback_url`;
CREATE TABLE `trackback_url` (
  `id` bigint(20) NOT NULL auto_increment,
  `eid` bigint(20) NOT NULL default '0',
  `url` text,
  PRIMARY KEY  (`id`),
  KEY `trackback_url_eid_idx` (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit` (
  `id` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  KEY `unit_id_idx` (`id`),
  KEY `unit_uid_idx` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `unit_sub`
--

DROP TABLE IF EXISTS `unit_sub`;
CREATE TABLE `unit_sub` (
  `id` bigint(20) NOT NULL default '0',
  `gid` bigint(20) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) NOT NULL default '0',
  `level` int(11) default '1',
  `handle` text,
  `email` text,
  `password` text,
  `enable` tinyint(1) default NULL,
  `initymd` datetime NOT NULL default '0000-00-00 00:00:00',
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `user_detail`
--

DROP TABLE IF EXISTS `user_detail`;
CREATE TABLE `user_detail` (
  `uid` bigint(20) unsigned NOT NULL default '0',
  `fullname` text,
  `fullname_kana` text,
  `zip` text,
  `address` text,
  `tel` text,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `user_sequence`
--

DROP TABLE IF EXISTS `user_sequence`;
CREATE TABLE `user_sequence` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

drop table if exists `mail_noti`;
create table mail_noti (
   `id` int8 unsigned not null auto_increment primary key,
  `eid` int8 unsigned not null,
  `uid` int8 unsigned not null
);
create index mail_noti_eid_idx on mail_noti (eid);

DROP TABLE IF EXISTS `mail_noti_ct`;
CREATE TABLE `mail_noti_ct` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `eid` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
  `type` int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

drop table if exists `site_template`;
create table `site_template`(
    `id` bigint(20) key auto_increment,
    `uid` bigint(20) default 0,
    `gid` bigint(20) default 0,
    `name` text,
    `ord` int(11),
    `type` int(11) default 1
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
 
DROP TABLE IF EXISTS `prof_add_data`;
create table `prof_add_data` (
	`uid` bigint NOT NULL default '0',
	`req_id` bigint unsigned NOT NULL default '0',
	`idx` bigint,
	`data` text,
	key (`uid`) ,
	key(`req_id`),
	 key(`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `schedule_data_add_ical`;
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

DROP TABLE IF EXISTS `rel_cal_blk_rel`;
create table `rel_cal_blk_rel`(
  `gid` bigint(20) NOT NULL default '0',
  `uid` bigint(20) NOT NULL default '0',
  `blk_id` bigint(20) NOT NULL default '0',
  KEY `tag_g_blk` (`gid`),
  KEY `tag_m_blk` (`uid`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `core_blacklist_ip_group`;
CREATE TABLE `core_blacklist_ip_group` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `gid` bigint(20) unsigned NOT NULL default '0',
  `ip` text,
  PRIMARY KEY  (`id`),
  KEY `core_blacklist_ip_group_idx` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_blacklist_ip_master`;
CREATE TABLE `core_blacklist_ip_master` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ip` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_blacklist_ip_user`;
CREATE TABLE `core_blacklist_ip_user` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` bigint(20) unsigned NOT NULL default '0',
  `ip` text,
  PRIMARY KEY  (`id`),
  KEY `core_blacklist_ip_user_idx` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_captcha_setting_group`;
CREATE TABLE `core_captcha_setting_group` (
  `gid` bigint(20) unsigned NOT NULL default '0',
  `type` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_captcha_setting_master`;
CREATE TABLE `core_captcha_setting_master` (
  `type` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_captcha_setting_user`;
CREATE TABLE `core_captcha_setting_user` (
  `uid` bigint(20) unsigned NOT NULL default '0',
  `type` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_ngword_group`;
CREATE TABLE `core_ngword_group` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `gid` bigint(20) unsigned NOT NULL default '0',
  `word` text,
  PRIMARY KEY  (`id`),
  KEY `core_ngword_group_idx` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_ngword_master`;
CREATE TABLE `core_ngword_master` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `word` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `core_ngword_user`;
CREATE TABLE `core_ngword_user` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `uid` bigint(20) unsigned NOT NULL default '0',
  `word` text,
  PRIMARY KEY  (`id`),
  KEY `core_ngword_user_idx` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mod_fbbs_setting`;
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

DROP TABLE IF EXISTS `mod_fbbs_elm_owner`;
CREATE TABLE `mod_fbbs_elm_owner` (
  `eid` bigint(20) unsigned NOT NULL default '0',
  `uid` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mod_fbbs_data`;
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

DROP TABLE IF EXISTS `mod_fbbs_backnumber`;
CREATE TABLE `mod_fbbs_backnumber` (
  `thread_id` bigint(20) unsigned NOT NULL default '0',
  `initymd` datetime default NULL,
  PRIMARY KEY  (`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mod_fbbs_allow`;
CREATE TABLE `mod_fbbs_allow` (
  `eid` bigint(20) unsigned NOT NULL default '0',
  `type` smallint(6) default NULL,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mod_fbbs_element_relation`;
CREATE TABLE `mod_fbbs_element_relation` (
  `id` bigint(20) NOT NULL default '0',
  `pid` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
