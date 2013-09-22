<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
function mod_blog_calendar_install(){
	$message = '';
	$error = false;
	$q = mysql_exec("
CREATE TABLE IF NOT EXISTS `blog_calendar_setting` (
	`id` bigint(20) unsigned NOT NULL default '0',
	`view_type` tinyint(4) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
	");
	if(!$q){
		$error = true;
		$message .= '<h3>DBテーブル"blog_calendar_setting"作成失敗</h3>';
		$message .= mysql_error();
	}

	$q = mysql_exec("
CREATE TABLE IF NOT EXISTS `blog_calendar_list` (
	`id` bigint(20) unsigned NOT NULL default '0',
	`blog_id` bigint(20) unsigned NOT NULL default '0',
	KEY `blog_archive_list_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
	");
	if(!$q){
		$error = true;
		$message .= '<h3>DBテーブル"blog_calendar_list"作成失敗</h3>';
		$message .= mysql_error();
	}
	if(!$error){
		$message = "ブログカレンダーパーツの導入が正常に完了しました。";
	}
	return $message;
}
?>