<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

global $COMUNI_HEAD_CSSRAW;

$COMUNI_HEAD_CSSRAW[] = <<<___CSS___

ul.mod_fbbs_thread_list {
	margin: 0;
	padding: 0;
}

ul.mod_fbbs_thread_list li {
	list-style-type: none;
	background: url(/modules/fbbs/image/icon_txt.gif) top left no-repeat;
	padding-left: 18px;
	margin-left: 5px;
}


ul.mod_fbbs_tree_list {
	margin: 0;
	padding: 0;
}
ul.mod_fbbs_tree_list li {
	list-style-type: none;
	background: url(/modules/fbbs/image/tree.gif) top left no-repeat;
	padding-left: 15px;
	margin-left: 5px;
}

ul.mod_fbbs_tree_list li.res_top {
	list-style-type: none;
	background-image: url(/modules/fbbs/image/top.png);
	background-position: left 5px;
	background-repeat: no-repeat;
	line-height: 22px;
	padding-left: 17px;
	margin-left: 5px;
}

ul.mod_fbbs_tree_list li.res_comment {
	list-style-type: none;
	background-image: url(/modules/fbbs/image/icon_chat.gif);
	background-position: left 5px;
	background-repeat: no-repeat;
	line-height: 22px;
	padding-left: 17px;
	margin-left: 5px;
}

.mod_fbbs_block_body {
	padding-left: 24px;
	padding-bottom: 5px;
	border-bottom: dashed 1px #666;
	margin-bottom: 5px;
}

.mod_fbbs_block_body_nodata {
	height: 5px;
	border-bottom: dashed 1px #666;
	margin-bottom: 5px;
}

.mod_fbbs_author {
	color: #666;
	font-size: 10px;
}

.mod_fbbs_thread_autor {
	text-align: right;
	padding: 3px 2px 8px 10px;
	color: #666;
}

.mod_fbbs_thread_title_top {
	background: url(/modules/fbbs/image/icon_txt.gif) center left no-repeat;
	padding-left: 20px;
	line-height: 26px;
}

.mod_fbbs_thread_title {
	background: url(/modules/fbbs/image/icon_chat.gif) center left no-repeat;
	padding-left: 20px;
	line-height: 26px;
	border-top: solid 1px #666;
}

.mod_fbbs_thread_body {
	line-height: 1.2em;
}

.mod_fbbs_thread_link {
	text-align: right;
	padding: 3px;
}

.mod_fbbs_tree_wrap {
	clear: both;
	border: solid 1px #ccc;
	margin: 3px;
	padding: 2px;
	float: left;
	width: 48%;
	overflow: scroll;
	position: relative;
}

#mod_fbbs_tree_wrap {
	position: absolue;
	top: 0;
	left: 0;
}

.mod_fbbs_child_wrap {
	border: none;
	margin: 3px;
	padding: 2px;
	float: right;
	width: 48%;
}
___CSS___;

if (isset($_REQUEST['module']) == 'fbbs') {
	$COMUNI_HEAD_CSSRAW[] = <<<___CSSMAIN___
#space_1 .box {
	width: 100%;
	margin: 0;
	padding: 0;
}
___CSSMAIN___;

}

?>
