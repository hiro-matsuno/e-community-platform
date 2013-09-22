<?

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */


$css_file_content = <<<__CSS__
/* e-community platform auto generated skin */

* {
	margin: 0;
	padding: 0;
	word-break: break-all;
}

html, body, #wrapper {
	height: 100%;
}

body > #wrapper {
	height: auto;
	min-height: 100%;
}

body {
	color: #333;
	font-family: "ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", "メイリオ", Meiryo, Verdana, "ＭＳ Ｐゴシック", sans-serif;
	background:${bg_color} url(${filename}/kabe.gif) ;
	font-size: 13px;
	*font-size: small;
	*font: x-small;
}

/* 背景対策 */
*:first-child+html body {
	padding-left: 1px;
}
* html body {
	padding-left: 1px;
}
body,x:-moz-broken {
	margin-left: -1px;
}

a {
	color: ${link_color};
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}
a:visited {
	color: ${link_visited_color};
}


/* ヘッダ */

#header {
	height: ${bannar_height}px;
	width:780px;
	margin:0 auto;
	background:url(${bannar_file}) no-repeat center top;
}

#header h1 {
	font-size: 22px;
	*font-size: 167%;
	color: #666;
	font-weight: bold;
	padding-top:160px;
	padding-right:20px;
	text-align:right;
	line-height:1;
}


#header h1 a {
	color: #fff;
	text-decoration: none;
}

#header h1 a:visited {
	color: #fff;
}

#header h2 {
	font-size: 13px;
	*font-size: 100%;
	color: #fff;
	font-weight:normal;
	text-align:right;
	margin-top:5px;
	padding-right:20px;
	line-height:1.4;
}


/* wrapper: footerを下寄せするための枠 */

#wrapper {
	margin: 0 auto;
	width: 780px;
	background-color:${bg_color};
}


/* パンくずリスト */

#nav {
	color:#333;
	display:block;
	width:752px;
	margin-left:auto;
	margin-right:auto;
	text-align:center;
	font-size: 13px;
	*font-size: 100%;
}

#nav .nav_tp {
	text-align:right;
	margin-top: 5px;
}

#nav ul {
	margin: 0px;
	padding: 0px;
	list-style: none;
	height: 15px;
	color:#333;
}

#nav li {
	width: 95%;
	height: 15px;
	margin: 0;
	padding: 0;
	color: #ffffff;
	font-size: 0.9em;
}


/* ブロック用コンテナ */

#container {
	position: relative;
	clear: both;
	width: 780px;
	margin-left: auto;
	margin-right: auto;
	text-align: left;
	padding-bottom: 30px;
	margin-top: 10px;
}

#space1 div, #space2 div {
	line-height: 1.4em;
	font-size: 10px;
}

/* space_1: 中央ブロック */
#space_1 {
	width: 500px;
	float:right;
	text-align: center;
}
#space_1 .box {
	margin-left: auto;
	text-align: left;
	width: auto;
	margin-bottom: 15px;
	background-color: #FFF;
}

#space_1 .box_menu {
	display: block;
	font-weight: bold;
	background-color: ${block_title_bg_color};
	line-height: 25px;
}

#space_1 .box_menu span {
	display: block;
	color: ${block_title_color};
	font-size: 14px;



	*font-size: 108%;
	font-weight: bold;
	padding-left: 10px;
}

#space_1 .box_main {
	margin: 0;
	padding: 10px;
}

/* space_2: 左ブロック */
#space_2 {
	width: 240px;
	float:left;
	text-align: center;
}

#space_2 .box {
	margin-right: auto;
	text-align: left;
	width: 240px;
	margin-bottom: 15px;
	background-color: #FFF;
}

#space_2 .box_menu {
	padding-left: 2px;
	line-height: 21px;
	background-color: ${block_title_bg_color};
}

#space_2 .box_menu span {
	display: block;
	font-size: 14px;
	*font-size: 108%;
	font-weight: bold;
	color: ${block_title_color};
	padding-left:8px;
}

#space_2 .box_main {
	margin: 0;
	padding: 5px 5px;
}

/* フッタ */

#footer {
	clear: both;
	position: relative;
	padding: 0;
	margin-top: -25px;
	margin-left: auto;
	margin-right: auto;
	height: 25px;
	width: 782px;
	text-align: center;
	background-color: ${footer_bg_color};
}

#footer .footer_content {
	color: ${footer_color};
	font-size: 10px;
	padding-top: 5px;
}

#footer a, #footer a:visited {
	text-decoration: underline;
	color:#ffffff;
}

/* clearfix */
.clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}
.clearfix {
	display: inline-block;
}
/* Hides from IE-mac \*/
* html .clearfix {
	height: 1%;
}
.clearfix {
	display: block;
}
/* End hide from IE-mac */


/* トップページ（ブロック状態）*/ 
.mod_blog_block_content a {
	text-decoration:underline;
}

.mod_blog_block_content{

	padding-bottom: 10px;
}

.mod_blog_block_title a {
	font-size: 16px;
	*font-size: 100%;
	margin-bottom:10px;
	margin-top:10px;
	text-decoration:none;
}
.mod_blog_block_title {
	font-size: 16px;
	*font-size: 123%;
	margin-bottom:10px;
	margin-top:10px;
}

/* 個別記事 */ 
.mod_blog_main_title {
	font-size: 16px;
	*font-size: 123%;
	margin-bottom:10px;
	margin-top:10px;
	color: #996600;
} 
.mod_blog_main_content a {
	text-decoration:underline;
}
__CSS__;
?>
