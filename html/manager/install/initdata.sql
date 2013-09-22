/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
-- e-comunity 2.0 database initial data

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

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
-- Dumping data for table `common_css`
--

LOCK TABLES `common_css` WRITE;
/*!40000 ALTER TABLE `common_css` DISABLE KEYS */;
INSERT INTO `common_css` VALUES (0,'.box_main {\r\n/*	overflow: hidden;*/\r\n}\r\n.box_main p {\r\nmargin: 1.12em 0;\r\n}\r\n.box_main ul, .box_main ol {\r\nmargin: 0.5em 10px;\r\npadding: 0.5em 30px;\r\n}\r\n.box_main ul {\r\nlist-style-type: disc;\r\n}\r\n.box_main blockquote {\r\nmargin: 0;\r\npadding: 10px 40px;\r\n}\r\n/* でかアンケート用 */\r\n.enquete_title {\r\n	background-image: url(/skin/common/diamondblue.png);\r\n	background-position: left 4px;\r\n	background-repeat: no-repeat;\r\n	padding-left: 15px;\r\n}\r\n.enquete_block {\r\n	padding-left: 15px;\r\n}\r\n\r\n/* ミニアンケート用 */\r\n.enquete_tb {\r\n	font-size: 1em;\r\n	font-weight: bold;\r\n}\r\n.enquete_nb {\r\n	font-size: 0.9em;\r\n}\r\n.enquete_fwb {\r\n	width: 100%;\r\n	padding: 1px;\r\n	text-align: left;\r\n}\r\n.enquete_form {\r\n	margin: 0;\r\n	padding: 0;\r\n	width: 100%;\r\n}\r\n.enquete_srb {\r\n	color: #f00;\r\n	font-size: 0.9em;\r\n}\r\n.enquete_stb {\r\n	font-size: 0.9em;\r\n	font-weight: bold;\r\n	background-image: url(/skin/common/diamondblue.png);\r\n	background-position: left 1px;\r\n	background-repeat: no-repeat;\r\n	padding-left: 15px;\r\n}\r\n.enquete_errb {\r\n	font-size: 0.9em;\r\n	padding-left: 15px;\r\n	color: #f00;\r\n}\r\n.enquete_scb {\r\n	font-size: 0.9em;\r\n	font-weight: normal;\r\n	padding-left: 15px;\r\n}\r\n.enquete_seb {\r\n	padding-left: 15px;\r\n}\r\n.enquete_sib {\r\n	border: solid 1px #ccc;\r\n}\r\n.enquete_sswb {\r\n	text-align: center;\r\n	padding-top: 4px;\r\n}\r\n.enquete_sbb {\r\n	background-color: #fcfcfc;\r\n	border: solid 2px #e3f5fd;\r\n	font-size: 1.1em;\r\n	font-weight: bold;\r\n	padding: 3px 8px;\r\n}\r\n\r\n/* マイページ一覧用 */\r\n.list_u_block {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 20px;\r\n	padding-top: 2px;\r\n}\r\n.list_u_block_owner {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person_owner.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 20px;\r\n	padding-top: 2px;\r\n}\r\n.list_u_block span, .list_u_block_owner span {\r\n	display: block;\r\n	padding: 2px;\r\n}\r\n.list_u_block_more {\r\n	text-align: right;\r\n	font-size: 0.8em;\r\n	padding: 2px;\r\n}\r\n/* グループページ一覧用 */\r\n.list_g_block {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.list_g_block_owner {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.list_g_block span, .list_g_block_owner span {\r\n	display: block;\r\n	padding: 2px;\r\n}\r\n.list_g_block_more {\r\n	text-align: right;\r\n	font-size: 0.8em;\r\n	padding: 2px;\r\n}\r\n.list_g_main_desc {\r\n	padding-left: 18px;\r\n}\r\n\r\n#nav_admin {\r\n	clear: both;\r\n	width: 100%;\r\n	background-color: #dadada;\r\n	padding: 3px;\r\n	text-align: right;\r\n	height: 26px;\r\n	margin-bottom: 0px;\r\n	margin-right: 0px;\r\n	padding-right: 0px;\r\n	border-bottom: solid 2px #555555;\r\n}\r\n#nav_admin a {\r\n	display: block;\r\n	padding: 4px 0 0 20px;\r\n	margin-right: 5px;\r\n	margin-left: auto;\r\n	background-image: url(/image/add_block.png);\r\n	background-repeat: no-repeat;;\r\n	background-position: 6px 6px;\r\n	background-color: #FDFFD2;\r\n	border: solid 1px #cccccc;\r\n	width: 120px;\r\n	height: 20px;\r\n	color: #000000;\r\n	font-size: 0.8em;\r\n	text-align: left;\r\n	float: right;\r\n}\r\n\r\n/* 一般的な。 */\r\n.common_href {\r\n	display: block;\r\n	background-image: url(/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.common_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.common_href span {\r\n	display: block;\r\n	padding: 1px 2px 1px 3px;\r\n}\r\n.common_body {\r\n	padding: 3px 3px 3px 18px;\r\n	font-size: 0.8em;\r\n}\r\n.common_date {\r\n	padding: 1px 3px 1px 1px;\r\n	text-align: right;\r\n	font-size: 0.8em;\r\n	color: #aaaaaa;\r\n	border-bottom: dashed 1px #dfdfdf;\r\n}\r\n.common_feed {\r\n	margin-top: 3px;\r\n	text-align: right;\r\n}\r\n.common_feed img {\r\n	margin-right: 0px;\r\n}\r\n\r\n.correct_order {\r\n	background-image: url(/image/red_icon.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n\r\n.correct_auth {\r\n	background-image: url(/image/brue_icon.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n\r\n.correct_not_auth {\r\n	background-image: url(/image/green_icon.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n\r\n.reporter_block_note, .bosai_web_block_note {\r\n	background-color: #eeeeee;\r\n	font-weight: normal;\r\n	padding: 3px;\r\n}\r\n.reporter_block_msg, .bosai_web_block_msg {\r\n	padding: 3px;\r\n}\r\n\r\n/******* GLIST *******/\r\n.glist_main {\r\n	;\r\n}\r\n.glist_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.glist_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.glist_href span {\r\n	display: block;\r\n	padding: 4px 2px 1px 3px;\r\n}\r\n.glist_nohref {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.glist_nohref span {\r\n	display: block;\r\n	padding: 4px 2px 1px 3px;\r\n}\r\n.glist_entry {\r\n	color: #ffffff;\r\n	border-top: solid 4px #a8e1ff;\r\n	background-color: #71d8e6;\r\n	padding :4px;\r\n	font-weight: bold;\r\n	text-align: center;\r\n}\r\n.glist_entry a {\r\n	color: #ffffff;\r\n}\r\n.glist_bye {\r\n	color: #333333;\r\n	border-top: solid 2px #d4d4d4;\r\n	background-color: #bdbdbd;\r\n	padding :4px;\r\n	font-weight: bold;\r\n	text-align: center;\r\n}\r\n.glist_bye a {\r\n	color: #ffffff;\r\n}\r\n/******* MAP *******/\r\n.map_layer_cbox {\r\n	width: 100%;\r\n	background-color: #efefef;\r\n	font-size: 0.9em;\r\n	padding: 0px;\r\n	margin: 0px;\r\n}\r\n\r\n\r\n/******* TAGREADR *******/\r\n.tagreader_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n	border-top: dashed 1px #dfdfdf;\r\n}\r\n.tagreader_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.tagreader_href span {\r\n	display: block;\r\n	padding: 1px 2px 1px 3px;\r\n}\r\n.tagreader_header, .tagreader_footer {\r\n	font-size: 0.9em;\r\n	padding: 2px;\r\n}\r\n/******* SCHEDULE *******/\r\n.schedule_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 6px;\r\n	padding-left: 18px;\r\n	padding-top: 2px;\r\n	text-align: left;\r\n}\r\n.schedule_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.schedule_href span {\r\n	display: block;\r\n	padding: 2px 2px 1px 3px;\r\n}\r\n.schedule_body {\r\n	padding: 3px 3px 3px 18px;\r\n	font-size: 0.8em;\r\n}\r\n.schedule_date {\r\n	padding: 1px 3px 1px 4px;\r\n	text-align: left;\r\n	font-size: 0.9em;\r\n	color: #666666;\r\n}\r\n\r\n.schedule_title { \r\n	font-size: 1.2em;\r\n	padding: 4px;\r\n	border: solid 1px #a2bfe4;\r\n	background-color: #dbe8f9;\r\n	margin: 3px;\r\n}\r\n\r\n\r\n/******* プロフィール *******/\r\n.profile_icon {\r\n	text-align: center;\r\n	padding: 4px;\r\n}\r\n.profile_icon > img {\r\n	margin: 0 auto;\r\n	border: solid 1px #efefef;\r\n}\r\n.profile_title {\r\n	padding: 3px;\r\n	font-size: 0.8em;\r\n	background-color: #f9f9f9;\r\n}\r\n.profile_body {\r\n	padding: 3px;\r\n	font-size: 0.9em;\r\n	background-color: #ffffff;\r\n}\r\n\r\n/******* メニュー *******/\r\n.menu_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 6px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.menu_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.menu_href span {\r\n	display: block;\r\n	padding: 1px 2px 1px 3px;\r\n}\r\n/******* RSS *******/\r\n#rss_add_input {\r\n	cursor: pointer;\r\n	padding: 2px 2px 2px 22px;\r\n	background-image: url(/image/fr.gif);\r\n	background-repeat: no-repeat;\r\n	background-position: 2px center;\r\n	font-size: 0.8em;\r\n	color: #6c9cc5;\r\n}\r\n\r\n.rss_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n	border-top: dashed 1px #dfdfdf;\r\n}\r\n.rss_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.rss_href span {\r\n	display: block;\r\n	padding: 1px 2px 1px 3px;\r\n}\r\n.rss_body {\r\n	padding: 3px 3px 3px 18px;\r\n	font-size: 0.8em;\r\n}\r\n.rss_date {\r\n	padding: 1px 3px 1px 1px;\r\n	text-align: right;\r\n	font-size: 0.8em;\r\n	color: #aaaaaa;\r\n}\r\n.rss_header, .rss_footer {\r\n	font-size: 0.9em;\r\n	padding: 2px;\r\n}\r\n\r\n.rss_title {\r\n	font-size: 0.9em;\r\n	padding: 3px;\r\n}\r\n.rss_list {\r\n	margin: 0px;\r\n	padding: 0px;\r\n	list-style: none;\r\n}\r\n.rss_list > li {\r\n	padding: 2px;\r\n	font-size: 0.9em;\r\n}\r\n.rss_list > a {\r\n	display: inline;\r\n}\r\n.rss_more {\r\n	font-size: 0.8em;\r\n	text-align: right;\r\n	padding: 2px;\r\n}\r\n.bosai_web_block_note {\r\n	font-size: 0.8em;\r\n}\r\n.reporter_block_note {\r\n	font-size: 0.8em;\r\n}\r\n\r\n.bwt_title {\r\n	display: block;\r\n	background-image: url(/skin/default/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 1px 4px;\r\n	padding-left: 16px;\r\n	padding-top: 2px;\r\n}\r\n.bwt_title_bysite {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 1px 3px;\r\n	padding-left: 16px;\r\n	padding-top: 2px;\r\n}\r\n.bwt_body {\r\n	padding-left: 16px;\r\n	padding-top: 2px;\r\n}\r\n.bwt_edit {\r\n	color: #666666;\r\n	padding: 3px;\r\n	font-size: 0.9em;\r\n	line-height: 1.2em;\r\n}\r\n.bwt_edit a {\r\n	color: #66abcd;\r\n}\r\n.bwt_edit a:visited {\r\n	color: #66abcd;\r\n}\r\n.bwt_edit a:hover{\r\n	color: #bcd1dc;\r\n}\r\n\r\n/* add 2009.3.9 */\r\n.mod_tag_keyword {\r\n	display: inline;\r\n	margin: 5px;\r\n	line-height: 1.2em;\r\n	text-decoration: underline;\r\n}\r\n.mod_tag_wrap {\r\n	padding: 3px;\r\n}\r\n\r\n#pager ul.pages {\r\n	display:block;\r\n	border:none;\r\n	text-transform:uppercase;\r\n	font-size:10px;\r\n	margin:10px 0 50px;\r\n	padding:0;\r\n}\r\n\r\n#pager ul.pages li {\r\n	list-style:none;\r\n	float:left;\r\n	border:1px solid #ccc;\r\n	text-decoration:none;\r\n	margin:0 5px 0 0;\r\n	padding:5px;\r\n}\r\n\r\n#pager ul.pages li:hover {\r\n	border:1px solid #003f7e;\r\n}\r\n\r\n#pager ul.pages li.pgEmpty {\r\n	border:1px solid #eee;\r\n	color:#eee;\r\n}\r\n\r\n#pager ul.pages li.pgCurrent {\r\n	border:1px solid #003f7e;\r\n	color:#000;\r\n	font-weight:700;\r\n	background-color:#eee;\r\n}\r\n\r\n.comment_form form {\r\n	margin: 0;\r\n	padding: 0;\r\n}\r\n.comment_form input,\r\n.comment_form textarea {\r\n	padding: 2px;\r\n	border: solid 1px #aaaaaa;\r\n	background-color: #ffffff;\r\n}\r\n.comment_form .no_border {\r\n	border: none;\r\n}\r\n\r\n#menu_layout {\r\n	clear: both;\r\n	width: 100%;\r\n	background-color: #e6f5f8;\r\n	text-align: right;\r\n	height: 26px;\r\n	margin-bottom: 0px;\r\n	margin-right: 0px;\r\n	padding: 2px 0 2px 0; \r\n	border-top: solid 1px #aaaaaa;\r\n	border-bottom: solid 1px #aaaaaa;\r\n}\r\n#menu_layout a {\r\n	display: block;\r\n	padding: 4px 0 0 20px;\r\n	margin-left: auto;\r\n	margin-right: 4px;\r\n	background-image: url(/image/add_block.png);\r\n	background-repeat: no-repeat;;\r\n	background-position: 6px 7px;\r\n	background-color: #FDFFD2;\r\n	border: solid 1px #cccccc;\r\n	width: 120px;\r\n	height: 20px;\r\n	color: #000000;\r\n	font-size: 0.8em;\r\n	text-align: left;\r\n	float: right;\r\n}\r\n#post_status {\r\n	clear: both;\r\n}\r\n\r\n.glist_main {\r\n	;\r\n}\r\n.glist_href {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.glist_href:hover {\r\n	background-color: #eaffe8;\r\n}\r\n.glist_href span {\r\n	display: block;\r\n	padding: 4px 2px 1px 3px;\r\n}\r\n.glist_nohref {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 18px;\r\n	padding-top: 4px;\r\n}\r\n.glist_nohref span {\r\n	display: block;\r\n	padding: 4px 2px 1px 3px;\r\n}\r\n.glist_entry {\r\n	color: #ffffff;\r\n	border-top: solid 4px #a8e1ff;\r\n	background-color: #71d8e6;\r\n	padding :4px;\r\n	font-weight: bold;\r\n	font-size: 14px;\r\n	text-align: center;\r\n}\r\n.glist_entry a {\r\n	color: #ffffff;\r\n}\r\n.glist_bye {\r\n	color: #333333;\r\n	border-top: solid 2px #d4d4d4;\r\n	background-color: #ffffff;\r\n	padding :3px;\r\n	font-weight: normal;\r\n	font-size: 12px;\r\n	text-align: center;\r\n}\r\n.glist_bye a {\r\n	color: #333333;\r\n}\r\n\r\n.ulist_block {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 20px;\r\n	padding-top: 2px;\r\n}\r\n.ulist_block_owner {\r\n	display: block;\r\n	background-image: url(/skin/default/image/person_owner.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 8px;\r\n	padding-left: 20px;\r\n	padding-top: 2px;\r\n}\r\n.ulist_block span, .ulist_block_owner span {\r\n	display: block;\r\n	padding: 2px;\r\n}\r\n.ulist_block_more {\r\n	text-align: right;\r\n	font-size: 0.8em;\r\n	padding: 2px;\r\n}\r\n\r\n.navi_pager {\r\nwidth: 100%;\r\n}\r\n.navi_pager ul.pages {\r\ndisplay:block;\r\nborder:none;\r\ntext-transform:uppercase;\r\nfont-size:10px;\r\nmargin:0 auto;\r\npadding:0;\r\n}\r\n\r\n.navi_pager ul.pages li {\r\nlist-style:none;\r\nfloat:left;\r\nborder:1px solid #ccc;\r\ntext-decoration:none;\r\nmargin:0 5px 0 0;\r\npadding:5px;\r\n}\r\n\r\n.navi_pager ul.pages li:hover {\r\nborder:1px solid #003f7e;\r\n}\r\n\r\n.navi_pager ul.pages li.pgEmpty {\r\nborder:1px solid #eee;\r\ncolor:#eee;\r\n}\r\n\r\n.navi_pager ul.pages li.pgCurrent {\r\nborder:1px solid #003f7e;\r\ncolor:#000;\r\nfont-weight:700;\r\nbackground-color:#eee;\r\n}\r\n\r\n.search_btn, input.input_text {\r\nborder: solid 1px #aaa;\r\n}\r\n\r\n.add_input {\r\n	cursor: pointer;\r\n	padding: 2px 2px 2px 22px;\r\n	background-image: url(/image/fr.gif);\r\n	background-repeat: no-repeat;\r\n	background-position: 2px center;\r\n	font-size: 0.8em;\r\n	color: #6c9cc5;\r\n}\r\n\r\n.mod_map_messagearea {\r\n	display: none;\r\n}\r\n.mod_map_the_side_bar ul {\r\n	margin: 0;\r\n	padding: 0;\r\n	list-style-type: none;\r\n}\r\n.mod_map_the_side_bar li{\r\n	margin: 0;\r\n	padding: 0;\r\n	line-height: 1.2em;\r\n}\r\n\r\n.mod_timeline_subject {\r\n	display: block;\r\n	background-image: url(/image/arw.png);\r\n	background-repeat: no-repeat;\r\n	background-position: 3px 5px;\r\n	padding-left: 18px;\r\n	font-weight: normal;\r\n	padding-top: 4px;\r\n}\r\n.mod_timeline_baseline {\r\ntext-align: right;\r\n}\r\n.mod_timeline_summary {\r\npadding-left: 20px;\r\n}\r\n.mod_timeline_table {\r\nwidth: 100%;\r\nborder-collapse: collapse;\r\nborder: solid 3px #3b73c0;\r\n}\r\n.mod_timeline_table td {\r\nborder: solid 1px #5b86c2;\r\npadding: 3px;\r\n}\r\n.mod_timeline_zone {\r\nbackground: #f2f2f2;\r\n}');
/*!40000 ALTER TABLE `common_css` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `conf_group_level`
--

LOCK TABLES `conf_group_level` WRITE;
/*!40000 ALTER TABLE `conf_group_level` DISABLE KEYS */;
INSERT INTO `conf_group_level` VALUES (1,100,1,'グループ管理者'),(2,80,1,'グループ副管理者'),(3,50,1,'編集者'),(5,10,0,'一般利用者');
/*!40000 ALTER TABLE `conf_group_level` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `conf_user_level`
--

LOCK TABLES `conf_user_level` WRITE;
/*!40000 ALTER TABLE `conf_user_level` DISABLE KEYS */;
INSERT INTO `conf_user_level` VALUES (1,100,1,'システム管理者'),(2,80,1,'運用管理者'),(5,10,0,'ユーザー');
/*!40000 ALTER TABLE `conf_user_level` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `icons`
--

LOCK TABLES `icons` WRITE;
/*!40000 ALTER TABLE `icons` DISABLE KEYS */;
INSERT INTO `icons` VALUES (1780,'blue-dot.png','Google Mapsアイコン1','http://maps.google.co.jp/mapfiles/ms/icons/blue-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1781,'red-dot.png','Google Mapsアイコン2','http://maps.google.co.jp/mapfiles/ms/icons/red-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1782,'green-dot.png','Google Mapsアイコン3','http://maps.google.co.jp/mapfiles/ms/icons/green-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1783,'ltblue-dot.png','Google Mapsアイコン4','http://maps.google.co.jp/mapfiles/ms/icons/ltblue-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1784,'yellow-dot.png','Google Mapsアイコン5','http://maps.google.co.jp/mapfiles/ms/icons/yellow-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1785,'purple-dot.png','Google Mapsアイコン6','http://maps.google.co.jp/mapfiles/ms/icons/purple-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1786,'pink-dot.png','Google Mapsアイコン7','http://maps.google.co.jp/mapfiles/ms/icons/pink-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1787,'orange-dot.png','Google Mapsアイコン8','http://maps.google.co.jp/mapfiles/ms/icons/orange-dot.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1788,'blue.png','Google Mapsアイコン9','http://maps.google.co.jp/mapfiles/ms/icons/blue.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1789,'red.png','Google Mapsアイコン10','http://maps.google.co.jp/mapfiles/ms/icons/red.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1790,'green.png','Google Mapsアイコン11','http://maps.google.co.jp/mapfiles/ms/icons/green.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1791,'lightblue.png','Google Mapsアイコン12','http://maps.google.co.jp/mapfiles/ms/icons/lightblue.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1792,'yellow.png','Google Mapsアイコン13','http://maps.google.co.jp/mapfiles/ms/icons/yellow.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1793,'purple.png','Google Mapsアイコン14','http://maps.google.co.jp/mapfiles/ms/icons/purple.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1794,'pink.png','Google Mapsアイコン15','http://maps.google.co.jp/mapfiles/ms/icons/pink.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1795,'orange.png','Google Mapsアイコン16','http://maps.google.co.jp/mapfiles/ms/icons/orange.png',32,32,0.5,0,'2008-10-16 01:37:51'),(1796,'blue-pushpin.png','Google Mapsアイコン17','http://maps.google.co.jp/mapfiles/ms/icons/blue-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1797,'red-pushpin.png','Google Mapsアイコン18','http://maps.google.co.jp/mapfiles/ms/icons/red-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1798,'grn-pushpin.png','Google Mapsアイコン19','http://maps.google.co.jp/mapfiles/ms/icons/grn-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1799,'ltblu-pushpin.png','Google Mapsアイコン20','http://maps.google.co.jp/mapfiles/ms/icons/ltblu-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1800,'ylw-pushpin.png','Google Mapsアイコン21','http://maps.google.co.jp/mapfiles/ms/icons/ylw-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1801,'purple-pushpin.png','Google Mapsアイコン22','http://maps.google.co.jp/mapfiles/ms/icons/purple-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1802,'pink-pushpin.png','Google Mapsアイコン23','http://maps.google.co.jp/mapfiles/ms/icons/pink-pushpin.png',32,32,0.3,0,'2008-10-16 01:37:51'),(1803,'restaurant.png','Google Mapsアイコン24','http://maps.google.co.jp/mapfiles/ms/icons/restaurant.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1804,'coffeehouse.png','Google Mapsアイコン25','http://maps.google.co.jp/mapfiles/ms/icons/coffeehouse.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1805,'bar.png','Google Mapsアイコン26','http://maps.google.co.jp/mapfiles/ms/icons/bar.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1806,'snack_bar.png','Google Mapsアイコン27','http://maps.google.co.jp/mapfiles/ms/icons/snack_bar.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1807,'drinking_water.png','Google Mapsアイコン28','http://maps.google.co.jp/mapfiles/ms/icons/drinking_water.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1808,'lodging.png','Google Mapsアイコン29','http://maps.google.co.jp/mapfiles/ms/icons/lodging.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1809,'wheel_chair_accessible.png','Google Mapsアイコン30','http://maps.google.co.jp/mapfiles/ms/icons/wheel_chair_accessible.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1810,'shopping.png','Google Mapsアイコン31','http://maps.google.co.jp/mapfiles/ms/icons/shopping.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1811,'movies.png','Google Mapsアイコン32','http://maps.google.co.jp/mapfiles/ms/icons/movies.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1812,'grocerystore.png','Google Mapsアイコン33','http://maps.google.co.jp/mapfiles/ms/icons/grocerystore.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1813,'convienancestore.png','Google Mapsアイコン34','http://www.c-bosai.jp/icons/p/convienancestore.png',32,32,0.5,0.3,'2009-01-13 05:43:15'),(1814,'arts.png','Google Mapsアイコン35','http://maps.google.co.jp/mapfiles/ms/icons/arts.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1815,'homegardenbusiness.png','Google Mapsアイコン36','http://maps.google.co.jp/mapfiles/ms/icons/homegardenbusiness.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1816,'electronics.png','Google Mapsアイコン37','http://maps.google.co.jp/mapfiles/ms/icons/electronics.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1817,'mechanic.png','Google Mapsアイコン38','http://maps.google.co.jp/mapfiles/ms/icons/mechanic.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1818,'pharmacy-us.png','Google Mapsアイコン39','http://maps.google.co.jp/mapfiles/ms/icons/pharmacy-us.png',32,32,0.5,0.3,'2008-10-16 01:46:57'),(1819,'realestate.png','Google Mapsアイコン40','http://maps.google.co.jp/mapfiles/ms/icons/realestate.png',32,32,0.1,0,'2008-10-16 01:37:51'),(1820,'salon.png','Google Mapsアイコン41','http://maps.google.co.jp/mapfiles/ms/icons/salon.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1821,'dollar.png','Google Mapsアイコン42','http://maps.google.co.jp/mapfiles/ms/icons/dollar.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1822,'parkinglot.png','Google Mapsアイコン43','http://maps.google.co.jp/mapfiles/ms/icons/parkinglot.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1823,'gas.png','Google Mapsアイコン44','http://maps.google.co.jp/mapfiles/ms/icons/gas.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1824,'cabs.png','Google Mapsアイコン45','http://maps.google.co.jp/mapfiles/ms/icons/cabs.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1825,'bus.png','Google Mapsアイコン46','http://maps.google.co.jp/mapfiles/ms/icons/bus.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1826,'truck.png','Google Mapsアイコン47','http://maps.google.co.jp/mapfiles/ms/icons/truck.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1827,'rail.png','Google Mapsアイコン48','http://maps.google.co.jp/mapfiles/ms/icons/rail.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1828,'plane.png','Google Mapsアイコン49','http://maps.google.co.jp/mapfiles/ms/icons/plane.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1829,'ferry.png','Google Mapsアイコン50','http://maps.google.co.jp/mapfiles/ms/icons/ferry.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1830,'helicopter.png','Google Mapsアイコン51','http://maps.google.co.jp/mapfiles/ms/icons/helicopter.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1831,'question.png','Google Mapsアイコン52','http://maps.google.co.jp/mapfiles/ms/icons/question.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1832,'info.png','Google Mapsアイコン53','http://maps.google.co.jp/mapfiles/ms/icons/info.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1833,'flag.png','Google Mapsアイコン54','http://maps.google.co.jp/mapfiles/ms/icons/flag.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1834,'earthquake.png','Google Mapsアイコン55','http://maps.google.co.jp/mapfiles/ms/icons/earthquake.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1835,'webcam.png','Google Mapsアイコン56','http://maps.google.co.jp/mapfiles/ms/icons/webcam.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1836,'postoffice-us.png','Google Mapsアイコン57','http://maps.google.co.jp/mapfiles/ms/icons/postoffice-us.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1837,'police.png','Google Mapsアイコン58','http://maps.google.co.jp/mapfiles/ms/icons/police.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1838,'firedept.png','Google Mapsアイコン59','http://maps.google.co.jp/mapfiles/ms/icons/firedept.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1839,'hospitals.png','Google Mapsアイコン60','http://maps.google.co.jp/mapfiles/ms/icons/hospitals.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1840,'landmarks-jp.png','Google Mapsアイコン61','http://maps.google.co.jp/mapfiles/ms/icons/landmarks-jp.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1841,'phone.png','Google Mapsアイコン62','http://maps.google.co.jp/mapfiles/ms/icons/phone.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1842,'caution.png','Google Mapsアイコン63','http://maps.google.co.jp/mapfiles/ms/icons/caution.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1843,'postoffice-jp.png','Google Mapsアイコン64','http://maps.google.co.jp/mapfiles/ms/icons/postoffice-jp.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1844,'hotsprings.png','Google Mapsアイコン65','http://maps.google.co.jp/mapfiles/ms/icons/hotsprings.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1845,'tree.png','Google Mapsアイコン66','http://maps.google.co.jp/mapfiles/ms/icons/tree.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1846,'campfire.png','Google Mapsアイコン67','http://maps.google.co.jp/mapfiles/ms/icons/campfire.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1847,'picnic.png','Google Mapsアイコン68','http://maps.google.co.jp/mapfiles/ms/icons/picnic.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1848,'campground.png','Google Mapsアイコン69','http://maps.google.co.jp/mapfiles/ms/icons/campground.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1849,'rangerstation.png','Google Mapsアイコン70','http://maps.google.co.jp/mapfiles/ms/icons/rangerstation.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1850,'toilets.png','Google Mapsアイコン71','http://maps.google.co.jp/mapfiles/ms/icons/toilets.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1851,'POI.png','Google Mapsアイコン72','http://maps.google.co.jp/mapfiles/ms/icons/POI.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1852,'hiker.png','Google Mapsアイコン73','http://maps.google.co.jp/mapfiles/ms/icons/hiker.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1853,'cycling.png','Google Mapsアイコン74','http://maps.google.co.jp/mapfiles/ms/icons/cycling.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1854,'motorcycling.png','Google Mapsアイコン75','http://maps.google.co.jp/mapfiles/ms/icons/motorcycling.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1855,'horsebackriding.png','Google Mapsアイコン76','http://maps.google.co.jp/mapfiles/ms/icons/horsebackriding.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1856,'sportvenue.png','Google Mapsアイコン77','http://maps.google.co.jp/mapfiles/ms/icons/sportvenue.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1857,'golfer.png','Google Mapsアイコン78','http://maps.google.co.jp/mapfiles/ms/icons/golfer.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1858,'trail.png','Google Mapsアイコン79','http://maps.google.co.jp/mapfiles/ms/icons/trail.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1859,'water.png','Google Mapsアイコン80','http://maps.google.co.jp/mapfiles/ms/icons/water.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1860,'snowflake_simple.png','Google Mapsアイコン81','http://maps.google.co.jp/mapfiles/ms/icons/snowflake_simple.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1861,'marina.png','Google Mapsアイコン82','http://maps.google.co.jp/mapfiles/ms/icons/marina.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1862,'fishing.png','Google Mapsアイコン83','http://maps.google.co.jp/mapfiles/ms/icons/fishing.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1863,'sailing.png','Google Mapsアイコン84','http://maps.google.co.jp/mapfiles/ms/icons/sailing.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1864,'swimming.png','Google Mapsアイコン85','http://maps.google.co.jp/mapfiles/ms/icons/swimming.png',32,32,0.5,0.3,'2008-10-16 01:47:12'),(1865,'waterfalls.png','Google Mapsアイコン86','http://maps.google.co.jp/mapfiles/ms/icons/waterfalls.png',32,32,0.5,0.3,'2008-10-16 01:47:12');
/*!40000 ALTER TABLE `icons` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `map_base`
--

LOCK TABLES `map_base` WRITE;
/*!40000 ALTER TABLE `map_base` DISABLE KEYS */;
INSERT INTO `map_base` VALUES (2533,0,'G_NORMAL_MAP','',0,'Google 地図','','',0,19,'1',0),(2534,0,'G_SATELLITE_MAP','',0,'Google 航空写真','','',0,19,'1',0),(2535,0,'G_HYBRID_MAP','',0,'Google 地図 + 写真','','',0,19,'1',0),(2536,0,'G_PHYSICAL_MAP','',0,'Google 地形図','','',0,19,'1',0),(2537,2,'http://www.geographynetwork.ne.jp/ogc/wms?ServiceName=basemap_wms&request=GetMap&SERVICE=WMS&SRS=EPSG:54004&LAYERS=0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17&FORMAT=image/png&reaspect=true&WIDTH=256&HEIGHT=256','',0,'数値地図','数値','(c) Geography Network Japan',4,17,'1',0),(2718,2,'http://asp5.service-section.com/MyMap/servlet/iNetGISWMS?VERSION=1.1.0&USERPROFILEKEY=1113871772532&MAPDIR=2&DEPTH=24&ANTIALIASING=OFF&CENTERCROSS=OFF&SCALEGAUGE=OFF&DBACHE=ON&DEPTH=24&CENTERCROSS=OFF&SCALEGAUGE=OFF&DBACHE=ON&LAYERS=2599001&REQUEST=GetMap&SERVICE=WMS&SRS=EPSG:4326&FORMAT=image/png&WIDTH=256&HEIGHT=256&STYLES=','',1,'数値地図25000','数値25000','承認番号　平18総複、第376号',0,19,'1.0',0);
/*!40000 ALTER TABLE `map_base` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `menubar`
--

LOCK TABLES `menubar` WRITE;
/*!40000 ALTER TABLE `menubar` DISABLE KEYS */;
INSERT INTO `menubar` VALUES (0,4);
/*!40000 ALTER TABLE `menubar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menubar_css`
--

DROP TABLE IF EXISTS `menubar_css`;
CREATE TABLE `menubar_css` (
  `id` int(11) NOT NULL auto_increment,
  `title` text,
  `css` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menubar_css`
--

LOCK TABLES `menubar_css` WRITE;
/*!40000 ALTER TABLE `menubar_css` DISABLE KEYS */;
INSERT INTO `menubar_css` VALUES (1,'eコミュニティつくば(普通)','#menubar {\r\n	position: relative;\r\n	width: 100%;\r\n	height: 24px;\r\n	background-color: #d8d8d8;\r\n	background-image: url(/skin/menubar/2/bg.png);\r\n	background-repeat: repeat-x;\r\n	line-height: 1em;\r\n	margin: 0 auto;\r\n	padding: 0;\r\n}\r\n#menubar_logotxt {\r\n	display: none;\r\n}\r\n#menubar_logoimg {\r\n	float: left;\r\n	width: 160px;\r\n	height: 24px;\r\n	background-image: url(/skin/menubar/2/logo.png);\r\n	background-repeat: no-repeat;\r\n}\r\n#menubar_logoimg a {\r\n	display: block;\r\n	width: 160px;\r\n	height: 24px;\r\n}\r\n#menubar_menutxt {\r\n	float: right;\r\n	color: #000;\r\n	padding: 3px 5px 1px 1px;\r\n	font-size: 0.8em;\r\n}\r\n#menubar_menutxt a {\r\n	color: #000;\r\n	text-decoration: none;\r\n}\r\n#menubar_menutxt a:hover {\r\n	color: #111;\r\n	text-decoration: underline;\r\n}\r\n'),(2,'eコミュニティつくば(シンプル)','#menubar {\r\n	width: 100%;\r\n	height: 24px;\r\n	background-color: #ffffff;\r\n	border-bottom: solid 1px #6372ab;\r\n}\r\n#menubar_logotxt {\r\n	display: none;\r\n}\r\n#menubar_logoimg {\r\n	float: left;\r\n	width: 176px;\r\n	height: 24px;\r\n	background-image: url(/skin/menubar/3/logo.png);\r\n	background-repeat: no-repeat;\r\n}\r\n#menubar_logoimg a {\r\n	display: block;\r\n	width: 176px;\r\n	height: 24px;\r\n}\r\n\r\n#menubar_menutxt {\r\n	float: right;\r\n	color: #000;\r\n	padding: 3px 5px 1px 1px;\r\n	font-size: 0.8em;\r\n}\r\n#menubar_menutxt a {\r\n	color: #001ab4;\r\n	text-decoration: none;\r\n}\r\n#menubar_menutxt a:hover {\r\n	color: #001ab4;\r\n	text-decoration: underline;\r\n}\r\n'),(3,'地域防災キット標準','#menubar {\r\n	width: 100%;\r\n	height: 24px;\r\n	background-color: #d8d8d8;\r\n	background-image: url(/skin/menubar/1/bg.png);\r\n	background-repeat: repeat-x;\r\n}\r\n#menubar_logotxt {\r\n	display: none;\r\n}\r\n#menubar_logoimg {\r\n	float: left;\r\n	width: 126px;\r\n	height: 24px;\r\n	background-image: url(/skin/menubar/1/logo.png);\r\n	background-repeat: no-repeat;\r\n}\r\n#menubar_logoimg a {\r\n	display: block;\r\n	width: 126px;\r\n	height: 24px;\r\n}\r\n\r\n#menubar_menutxt {\r\n	float: right;\r\n	color: #000;\r\n	padding: 3px 5px 1px 1px;\r\n	font-size: 0.8em;\r\n}\r\n#menubar_menutxt a {\r\n	color: #000;\r\n	text-decoration: none;\r\n}\r\n#menubar_menutxt a:hover {\r\n	color: #111;\r\n	text-decoration: underline;\r\n}\r\n'),(4,'eコミュニティ2.0','#menubar {\r\n	position: relative;\r\n	width: 100%;\r\n	height: 24px;\r\n	background-color: #d8d8d8;\r\n	background-image: url(/skin/menubar/1/haikei.gif);\r\n	background-repeat: repeat-x;\r\n	line-height: 1em;\r\n	margin: 0 auto;\r\n	padding: 0;\r\n}\r\n#menubar_logotxt {\r\n	display: none;\r\n}\r\n#menubar_logoimg {\r\n	float: left;\r\n	width: 263px;\r\n	height: 24px;\r\n	background-image: url(/skin/menubar/1/icon.gif);\r\n	background-repeat: no-repeat;\r\n}\r\n#menubar_logoimg a {\r\n	display: block;\r\n	width: 263px;\r\n	height: 24px;\r\n}\r\n#menubar_menutxt {\r\n	float: right;\r\n	color: #000;\r\n	padding: 3px 5px 1px 1px;\r\n	font-size: 0.8em;\r\n}\r\n#menubar_menutxt a {\r\n	color: #000;\r\n	text-decoration: none;\r\n}\r\n#menubar_menutxt a:hover {\r\n	color: #111;\r\n	text-decoration: underline;\r\n}\r\n');
/*!40000 ALTER TABLE `menubar_css` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `module_setting`
--

LOCK TABLES `module_setting` WRITE;
/*!40000 ALTER TABLE `module_setting` DISABLE KEYS */;
INSERT INTO `module_setting` VALUES 
(1,'ブログ','blog',7,1,1,''),
(2,'ブログアーカイブ','blog_archive',7,1,1,''),
(3,'RSS','rss',7,1,1,''),
(4,'KML','kml',7,0,1,''),
(5,'プロフィール','profile',7,1,0,''),
(6,'ブロックHTML','page',7,1,1,''),
(7,'マップ','map',7,1,0,NULL),
(8,'カレンダー','schedule',7,1,0,NULL),
(9,'メニュー','menu',7,1,1,NULL),
(10,'グループ参加と参加者リスト','glist',3,1,0,NULL),
(11,'タグリーダー','tagreader',7,1,1,NULL),
(13,'市民レポーター','reporter',3,1,1,NULL),
(14,'検索','search',7,1,1,NULL),
(15,'防災Web','bosai_web',3,1,1,NULL),
(18,'マイページ一覧','list_u',7,1,0,NULL),
(19,'グループページ一覧','list_g',7,1,0,NULL),
(20,'ログイン／ログアウト','login',7,1,0,NULL),
(22,'メッセージ配信','mailmag',3,1,1,NULL),
(23,'掲示板（電子会議室）','fbbs',7,1,1,NULL),
(24,'eコミマップ連携','ecommap',7,1,1,NULL),
(25,'ブログカレンダー','blog_calendar',7,1,1,NULL),
(26,'イベントカレンダー','event_calendar',7,1,1,NULL),
(27,'連携カレンダー','rel_cal',7,1,1,NULL),
(28,'メモ','memo',7,1,1,NULL),
(29,'お問い合わせ','contact',7,1,1,NULL),
(30,'メッセージングリスト','ml',7,1,1,NULL),
(31,'アンケート','enquete',7,1,1,NULL),
(32,'ファイル倉庫','filebox',7,1,0,NULL);
/*!40000 ALTER TABLE `module_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skin_menubar`
--

DROP TABLE IF EXISTS `skin_menubar`;
CREATE TABLE `skin_menubar` (
  `id` bigint(20) NOT NULL default '0',
  `title` text,
  `css` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `skin_menubar`
--

LOCK TABLES `skin_menubar` WRITE;
/*!40000 ALTER TABLE `skin_menubar` DISABLE KEYS */;
/*!40000 ALTER TABLE `skin_menubar` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `theme_layout`
--

LOCK TABLES `theme_layout` WRITE;
/*!40000 ALTER TABLE `theme_layout` DISABLE KEYS */;
INSERT INTO `theme_layout` VALUES (1,'1column_free','1column_free',1,'2009-03-25 02:34:38'),(2,'2column_free','2Column',2,'2009-03-25 02:34:38'),(3,'3column_free','3column_free',3,'2009-03-25 02:34:38'),(4,'2column_780','2 Columns width 780px',2,'2009-03-25 02:34:38'),(5,'2column_nocss','2column_nocss',2,'2009-03-25 02:34:38'),(6,'3column_nocss','3column_nocss',3,'2009-03-25 02:34:38'),(7,'2column_840','2column_840',2,'2009-03-25 02:34:38'),(8,'5column_e298','5column_e298',5,'2009-03-25 02:34:38'),(9,'3column_e298','3columns for e298',3,'2009-03-25 08:46:37');
/*!40000 ALTER TABLE `theme_layout` ENABLE KEYS */;
UNLOCK TABLES;

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
  `updymd` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `layout_id` bigint(20) NOT NULL default '0',
  `pmt` int(11) NOT NULL default '0',
  `parent_skin_id` bigint(20) NOT NULL default '0',
  `var_title` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `theme_layout2`
--

LOCK TABLES `theme_skin` WRITE;
/*!40000 ALTER TABLE `theme_skin` DISABLE KEYS */;
INSERT INTO `theme_skin` VALUES 
(1,'default','default.gif','地域防災キット標準','e-community platform標準スキン','2009-08-28 02:05:37',3,1,1,'３カラム'),
(2,'simple','simple.gif','SIMPLE THEME','SIMPLE THEME','2009-06-29 04:05:46',3,7,2,'３カラム'),
(3,'blue','blue.gif','サンプルブルー','ブルースキン','2009-06-29 04:05:46',4,4,3,'２カラム'),
(6,'black1','black1.gif','シンプル 黒（葉っぱ）','シンプル 黒（葉っぱ）の2カラムです。','2009-06-29 04:05:46',5,7,6,'２カラム'),
(7,'simple_beige','simple_beige.gif','シンプル ピンク','シンプル ピンク','2009-06-29 04:05:46',5,7,7,'２カラム'),
(8,'simple_check1','simple_check1.gif','シンプル チェック1','シンプル チェック1\r\n','2009-06-29 04:05:46',5,7,8,'２カラム'),
(9,'simple_check2','simple_check2.gif','シンプル チェック2','シンプル チェック2','2009-06-29 04:05:46',5,7,9,'２カラム'),
(10,'simple_darkgreen','simple_darkgreen.gif','シンプル 渋い緑','シンプル 渋い緑','2009-06-29 04:05:46',5,7,10,'２カラム'),
(11,'simple_green','simple_green.gif','シンプル 緑','シンプル 緑','2009-06-29 04:05:46',5,7,11,'２カラム'),
(12,'sky','sky.gif','木と空','木と空','2009-06-29 04:05:46',5,7,12,'２カラム'),
(13,'black_3c','black1.gif','シンプル 黒（葉っぱ）','シンプル 黒（葉っぱ）です。','2009-06-29 04:05:46',6,7,6,'３カラム'),
(14,'simple_beige_3c','simple_beige.gif','シンプル ピンク','シンプル ピンク','2009-06-29 04:05:46',6,7,7,'３カラム'),
(15,'simple_check1_3c','simple_check1.gif','シンプル チェック1','シンプル チェック1','2009-06-29 04:05:46',6,7,8,'３カラム'),
(16,'simple_check2_3c','simple_check2.gif','シンプル チェック2','シンプル チェック2','2009-06-29 04:05:46',6,7,9,'３カラム'),
(17,'simple_darkgreen_3c','simple_darkgreen.gif','シンプル 渋い緑','シンプル 渋い緑\r\n','2009-06-29 04:05:46',6,7,10,'３カラム'),
(18,'simple_green_3c','simple_green.gif','シンプル 緑','シンプル 緑\r\n','2009-06-29 04:05:46',6,7,11,'３カラム'),
(19,'sky_3c','sky.gif','木と空','木と空','2009-06-29 04:05:46',6,7,12,'３カラム'),
(20,'flower_ume_2c','flower_ume.gif','梅','梅','2009-06-29 04:05:46',5,7,20,'２カラム'),
(21,'flower_ume_3c','flower_ume.gif','梅','梅','2009-06-29 04:05:46',6,7,20,'３カラム'),
(22,'green_monstera_2c','green_monstera.gif','モンステラ','モンステラ','2009-06-29 04:05:46',5,7,22,'２カラム'),
(23,'green_monstera_3c','green_monstera.gif','モンステラ','モンステラ','2009-06-29 04:05:46',6,7,22,'３カラム'),
(24,'photo_julien_2c','photo_julien.gif','写真（ジュリアン）','写真（ジュリアン）の２カラム','2009-06-29 04:05:46',5,7,24,'２カラム'),
(25,'photo_julien_3c','no_image.gif','写真（ジュリアン）','写真（ジュリアン）の３カラム','2009-06-29 04:05:46',6,7,24,'３カラム'),
(26,'photo_pig_2c','photo_pig.gif','写真（3匹の子豚）','写真（3匹の子豚）','2009-06-29 04:05:46',5,7,26,'２カラム'),
(27,'photo_pig_3c','photo_pig.gif','写真（3匹の子豚）','写真（3匹の子豚）','2009-06-29 04:05:46',6,7,26,'３カラム'),
(28,'photo_pot_2c','photo_pot.gif','写真（植木用ポット）','写真（植木用ポット）','2009-06-29 04:05:46',5,7,28,'２カラム'),
(29,'photo_pot_3c','photo_pot.gif','写真（植木用ポット）','写真（植木用ポット）','2009-06-29 04:05:46',6,7,28,'３カラム'),
(30,'photo_sea_2c','photo_sea.gif','写真（海）','写真（海）','2009-06-29 04:05:46',5,7,30,'２カラム'),
(31,'photo_sea_3c','photo_sea.gif','写真（海）','写真（海）','2009-06-29 04:05:46',6,7,30,'３カラム'),
(32,'photo_viola_2c','photo_viola.gif','写真（ビオラ）','写真（ビオラ）','2009-06-29 04:05:46',5,7,32,'２カラム'),
(33,'photo_viola_3c','no_image.gif','写真（ビオラ）','写真（ビオラ）','2009-06-29 04:05:46',6,7,32,'３カラム'),
(34,'yellow_leaf_2c','yellow_leaf.gif','ハート型の葉っぱ','ハート型の葉っぱ','2009-06-29 04:05:46',5,7,34,'２カラム'),
(35,'yellow_leaf_3c','yellow_leaf.gif','ハート型の葉っぱ','ハート型の葉っぱ\r\n','2009-06-29 04:05:46',6,7,34,'３カラム'),
(36,'clover_2c','clover.gif','四つ葉のクローバー','四つ葉のクローバー（2カラム）','2009-06-29 04:05:46',5,7,36,'２カラム'),
(37,'contrail_2c','contrail.gif','飛行機雲','飛行機雲(２カラム)','2009-06-29 04:05:46',5,7,37,'２カラム'),
(38,'goldfish_2c','goldfish.gif','金魚','金魚（2カラム）\r\n','2009-06-29 04:05:46',5,7,38,'２カラム'),
(39,'ityo_2c','ityo.gif','イチョウ','イチョウ','2009-06-29 04:05:46',5,7,39,'２カラム'),
(40,'ityo_3c','ityo.gif','イチョウ','イチョウ（３カラム）','2009-06-29 04:05:46',6,7,39,'３カラム'),
(41,'photo_ship_2c','yacht.gif','海とヨット','海とヨット　２カラム\r\nオーストラリアの港の写真です','2009-06-29 04:05:46',5,7,41,'２カラム '),
(42,'photo_ship_3c','yacht.gif','海とヨット','海とヨット　３カラム\r\nオーストラリアの港の写真です','2009-06-29 04:05:46',6,7,41,'３カラム'),
(64,'photo_park_2c','photo_park.gif','万博記念公園の桜','','2009-06-29 04:05:46',5,7,64,'２カラム'),
(65,'photo_park_3c','photo_park.gif','万博記念公園の桜','','2009-06-29 04:05:46',6,7,64,'３カラム'),
(66,'bosai_2c','bosai.gif','防災（避難）','防災（避難）スキンです。','2009-06-29 04:05:46',5,7,66,'２カラム'),
(67,'photo_roapway_2c','photo_roapway.gif','（写真）筑波山ロープウェイ','筑波山ロープウェイ','2009-06-29 04:05:46',5,7,67,'２カラム'),
(68,'photo_roapway_3c','photo_roapway.gif','（写真）筑波山ロープウェイ','筑波山ロープウェイ','2009-06-29 04:05:46',6,7,67,'３カラム'),
(69,'photo_tsukubasan_2c','photo_tsukubasan.gif','（写真）筑波山','','2009-06-29 04:05:46',5,7,69,'２カラム'),
(70,'photo_tsukubasan_3c','photo_tsukubasan.gif','（写真）筑波山','（写真）筑波山','2009-06-29 04:05:46',6,7,69,'３カラム'),
(78,'e-community_blue_3c','e-community_blue_3c.gif','Ripple（水色）','Ripple（水色）','2009-07-23 07:09:10',6,7,78,'３カラム'),
(79,'e-community_blue_2c','e-community_blue_2c.gif','Ripple（水色）','Ripple（水色）','2009-07-23 07:10:51',5,7,78,'２カラム'),
(82,'ripple_green_2c','ripple_green_2c.gif','Ripple（緑）','Ripple（緑）','2009-08-21 07:31:46',5,7,82,'２カラム'),
(83,'ripple_green_3c','ripple_green_3c.jpg','Ripple（緑）','Ripple（緑）','2009-08-21 07:31:46',6,7,82,'３カラム'),
(84,'ripple_orange_2c','ripple_orange_2c.gif','Ripple（オレンジ）','Ripple（オレンジ）','2009-08-21 08:21:03',5,7,84,'２カラム'),
(85,'ripple_orange_3c','ripple_orange_3c.jpg','Ripple（オレンジ）','Ripple（オレンジ）','2009-08-21 08:21:03',6,7,84,'３カラム'),
(86,'ripple_pink_2c','ripple_pink_2c.gif','Ripple（ピンク）','Ripple（ピンク）','2009-08-21 08:09:52',5,7,86,'２カラム'),
(87,'ripple_pink_3c','ripple_pink_3c.jpg','Ripple（ピンク）','Ripple（ピンク）','2009-08-21 08:09:52',6,7,86,'３カラム'),
(88,'break_time_2c','break_time_2c.jpg','ブレイクタイム','ブレイクタイム','2009-08-21 08:09:52',5,7,88,'２カラム'),
(89,'break_time_3c','break_time_3c.jpg','ブレイクタイム','ブレイクタイム','2009-08-21 08:09:52',6,7,88,'３カラム'),
(90,'earth_2c','earth_2c.jpg','地球','地球','2009-08-21 08:09:52',5,7,90,'２カラム'),
(91,'earth_3c','earth_3c.jpg','地球','地球','2009-08-21 08:09:52',6,7,90,'３カラム'),
(92,'fuji_2c','fuji_2c.jpg','富士山','富士山','2009-08-21 08:09:52',5,7,92,'２カラム'),
(93,'fuji_3c','fuji_3c.jpg','富士山','富士山','2009-08-21 08:09:52',6,7,92,'３カラム'),
(94,'night_sky_2c','night_sky_2c.jpg','夜空','夜空','2009-08-21 08:09:52',5,7,94,'２カラム'),
(95,'night_sky_3c','night_sky_3c.jpg','夜空','夜空','2009-08-21 08:09:52',6,7,94,'３カラム'),
(96,'photo_ajisai_2c_L','photo_ajisai.gif','あじさい','あじさい','2010-06-21 14:00:00',5,7,96,'２カラム'),
(97,'photo_ajisai_2c_r','photo_ajisai.gif','あじさい','あじさい','2010-06-21 14:00:00',5,7,96,'２カラム(右メイン)'),
(98,'photo_ajisai_3c','photo_ajisai.gif','あじさい','あじさい','2010-06-21 14:00:00',6,7,96,'３カラム'),
(99,'photo_momiji_2c_L','photo_momiji.gif','もみじ','もみじ','2010-06-21 14:00:00',5,7,99,'２カラム'),
(100,'photo_momiji_2c_r','photo_momiji.gif','もみじ','もみじ','2010-06-21 14:00:00',5,7,99,'２カラム(右メイン)'),
(101,'photo_momiji_3c','photo_momiji.gif','もみじ','もみじ','2010-06-21 14:00:00',6,7,99,'３カラム'),
(102,'photo_sakura_2c_L','photo_sakura.gif','桜','桜','2010-06-21 14:00:00',5,7,102,'２カラム'),
(103,'photo_sakura_2c_r','photo_sakura.gif','桜','桜','2010-06-21 14:00:00',5,7,102,'２カラム(右メイン)'),
(104,'photo_sakura_3c','photo_sakura.gif','桜','桜','2010-06-21 14:00:00',6,7,102,'３カラム');
/*!40000 ALTER TABLE `theme_skin` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `prof_add_req`;
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

drop table if exists `join_req_info`;
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

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-28  4:02:58


--
-- Table structure for table `info_html`
--

DROP TABLE IF EXISTS `info_html`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `info_html` (
  `id` bigint,
  `name` text,
  `module` text,
  `html` text,
  `pos` text,
  `enabled` tinyint
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
