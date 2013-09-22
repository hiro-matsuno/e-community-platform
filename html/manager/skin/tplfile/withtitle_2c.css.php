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
	color: #42210B;
	font-weight: bold;
	padding-top:35px;
	padding-left:25px;
	line-height:1;
	padding-bottom: 15px;
	width: 240px;
}


#header h1 a {
	color: #42210B;
	text-decoration: none;
}

#header h1 a:visited {
	color: #42210B;
}

#header h2 {
	font-size: 13px;
	*font-size: 100%;
	color: #8C6239;
	font-weight:normal;
	margin-top:5px;
	padding-left: 30px;
	width: 235px;
	line-height:1.4;
	height: 58px;
	overflow: hidden;
}


/* wrapper: footerを下寄せするための枠 */

#wrapper {
	margin: 0px auto;
	width: 780px;
	background-color:${bg_color};
}


/* パンくずリスト */

#nav {
	color:#333;
	display:block;
	width:750px;
	margin-left:auto;
	margin-right:auto;
	text-align:center;
	font-size: 13px;


	*font-size: 100%;
	margin-bottom: 8px;
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
	background:url(${filename}/ban.jpg) no-repeat center -262px;
	padding: 0;
	margin: 0;
	padding-bottom: 30px;
}

#space1 div, #space2 div {
	line-height: 1.4em;
	font-size: 10px;
}

/* space_1: 中央ブロック */
#space_1 {
	width: 535px;
	clear: both;
	float:right;
	text-align: center;
	padding: 0;
}
#space_1 .box {
	margin-left: auto;
	text-align: left;
	width: auto;
	margin-right: 15px;
	margin-bottom: 15px;
	background-color: #FFF;
}

#space_1 .box_menu {
	display: block;
	font-weight: bold;
	padding-left: 5px;
	line-height: 19px;
	background-color: ${block_title_bg_color};
}

#space_1 .box_menu span {
	display: block;
	color: ${block_title_color};
	font-size: 14px;
	*font-size: 108%;
	font-weight: bold;
}

#space_1 .box_main {
	margin: 0;
	padding: 10px;
}

/* space_2: 左ブロック */
#space_2 {
	width: 225px;
	float:left;
	text-align: center;
}

#space_2 .box {
	text-align: left;
	margin-left: 15px;
	margin-bottom: 15px;
	background-color: #FFF;
}

#space_2 .box_menu {
	padding-left: 10px;
	line-height: 22px;
	background-color: ${block_title_bg_color};
	padding-left: 28px;
}
#space_2 .box_menu span {
	display: block;
	font-size: 14px;
	*font-size: 108%;
	font-weight: bold;
	color: ${block_title_color};
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
	margin-left: auto;
	margin-right: auto;
	height: 25px;
	width: 780px;
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
	color:#960;
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
	border-bottom: 1px solid #999;
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
	color: #669900;
} 
.mod_blog_main_content a {
	text-decoration:underline;
}
__CSS__;
?>
