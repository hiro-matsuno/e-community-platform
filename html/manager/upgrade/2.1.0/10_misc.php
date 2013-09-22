<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

try {

	$result = MySqlPlaneStatement::execNow("show tables like 'prof_add_req'");

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement::execNow( <<<__SQL_CODE__

		CREATE TABLE `prof_add_req` (
			`gid` bigint not null default '0',
			`req_id` bigint(20) unsigned NOT NULL auto_increment,
			`type` text,
			`title` text,
			`comment` text,
			`opt_size` text,
			`opt_list` text,
			`def_val` text,
			PRIMARY KEY  (`req_id`),
			key (`gid`)
		) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

__SQL_CODE__
		);

		MySqlPlaneStatement::execNow( <<<__SQL_CODE__

		INSERT INTO `prof_add_req` VALUES
		(0, 1, 'text', '名前（漢字）', null, null, null, null),
		(0, 2, 'text', '名前（全角カナ）', null, null, null, null),
		(0, 3, 'text', '郵便番号', null, null, null, null),
		(0, 4, 'text', '住所', null, null, null, null),
		(0, 5, 'text', '電話番号', null, null, null, null),
		(10000, 11, 'text', '名前（漢字）', null, null, null, null),
		(10000, 12, 'text', '名前（全角カナ）', null, null, null, null),
		(10000, 13, 'textarea', '参加希望動機', null, null, null, null),
		(10000, 14, 'textarea', 'その他連絡事項', null, null, null, null);

__SQL_CODE__
		);

	}

	$result = MySqlPlaneStatement::execNow("show tables like 'join_req_info'");

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement::execNow( <<<__SQL_CODE__

		create table `join_req_info`(
			`gid` bigint not null default '0',
			`num` int not null default '1',
			`req_id` bigint not null default '0',
			`req_check` smallint not null default '0',
			`def_val` text,
			`del_lock` smallint not null default '0',
			key (`gid`),
			key (`req_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
		);

		MySqlPlaneStatement::execNow( <<<__SQL_CODE__

		insert into `join_req_info` values
		(1, 0, 1, 1, null, 0),
		(1, 1, 2, 1, null, 0),
		(1, 2, 3, 1, null, 0),
		(1, 3, 4, 1, null, 0),
		(1, 4, 5, 1, null, 0),
		(10000, 0, 11, 1, null, 0),
		(10000, 1, 12, 1, null, 0),
		(10000, 2, 13, 1, null, 0),
		(10000, 3, 14, 0, null, 0);

__SQL_CODE__
		);
	}

	$result = MySqlPlaneStatement::execNow("show tables like 'prof_add_data'");

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement::execNow( <<<__SQL_CODE__

		create table `prof_add_data` (
			`uid` bigint NOT NULL default '0',
			`req_id` bigint unsigned NOT NULL default '0',
			`idx` bigint,
			`data` text,
			key (`uid`) ,
			key(`req_id`),
			 key(`idx`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
		);
	}


	$result = MySqlPlaneStatement
				::execNow( "SHOW COLUMNS FROM trackback LIKE 'host'" );

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement
			::execNow( "alter table trackback add host text after blog_name;" );

	}

	$result = MySqlPlaneStatement
				::execNow( "SHOW COLUMNS FROM comment_alt LIKE 'host'" );

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement
			::execNow( "ALTER TABLE comment_alt ADD host text AFTER msg" );

	}

	$result = MySqlPlaneStatement
				::execNow( "SHOW COLUMNS FROM group_joinable LIKE 'terms'" );

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement
			::execNow( "ALTER TABLE group_joinable ADD terms text" );

	}

	$result = MySqlPlaneStatement
				::execNow( "SHOW COLUMNS FROM group_joinable LIKE 'byelaw'" );

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement
			::execNow( "ALTER TABLE group_joinable ADD byelaw text" );

	}

	$result = MySqlPlaneStatement
				::execNow( "SHOW COLUMNS FROM group_joinable LIKE 'notice'" );

	if ( 0 == mysql_num_rows( $result->getResult() ) ) {

		MySqlPlaneStatement
			::execNow( "ALTER TABLE group_joinable ADD notice text" );

	}

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS mail_noti_ct (

		id bigint(20) unsigned NOT NULL auto_increment,
		eid bigint(20) unsigned NOT NULL default '0',
		uid bigint(20) unsigned NOT NULL default '0',
		type int(4) unsigned NOT NULL default '0',
		PRIMARY KEY (id)

	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
		);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_blacklist_ip_group` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `gid` bigint(20) unsigned NOT NULL default '0',
	  `ip` text,
	  PRIMARY KEY  (`id`),
	  KEY `core_blacklist_ip_group_idx` (`gid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_blacklist_ip_master` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `ip` text,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_blacklist_ip_user` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `uid` bigint(20) unsigned NOT NULL default '0',
	  `ip` text,
	  PRIMARY KEY  (`id`),
	  KEY `core_blacklist_ip_user_idx` (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_captcha_setting_group` (
	  `gid` bigint(20) unsigned NOT NULL default '0',
	  `type` tinyint(4) unsigned NOT NULL default '0',
	  PRIMARY KEY  (`gid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_captcha_setting_master` (
	  `type` tinyint(4) unsigned NOT NULL default '0',
	  PRIMARY KEY  (`type`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_captcha_setting_user` (
	  `uid` bigint(20) unsigned NOT NULL default '0',
	  `type` tinyint(4) unsigned NOT NULL default '0',
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_ngword_group` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `gid` bigint(20) unsigned NOT NULL default '0',
	  `word` text,
	  PRIMARY KEY  (`id`),
	  KEY `core_ngword_group_idx` (`gid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_ngword_master` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `word` text,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

	MySqlPlaneStatement::execNow(
<<<__SQL_CODE__

	CREATE TABLE IF NOT EXISTS `core_ngword_user` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `uid` bigint(20) unsigned NOT NULL default '0',
	  `word` text,
	  PRIMARY KEY  (`id`),
	  KEY `core_ngword_user_idx` (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

__SQL_CODE__
	);

} catch ( Exception $e ) {
	throw new Exception( __FILE__." の実行中にエラーが発生しました.<br/>\n" );
}
?>
