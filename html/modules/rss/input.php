<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/common.php';

define('MOD_RSS_DEFAULT_NUM', 10);
define('MOD_RSS_MAX_NUM', 30);

list($eid, $pid) = get_edit_ids();

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["rss_header"] = $_POST["rss_header"];
	$SYS_FORM["cache"]["rss_footer"] = $_POST["rss_footer"];
	$SYS_FORM["cache"]["keyword"]    = $_POST["keyword"];
	$SYS_FORM["cache"]["disp_type"]  = intval($_POST["disp_type"]);
	$SYS_FORM["cache"]["disp_num"]   = intval($_POST["disp_num"]);
	$SYS_FORM["cache"]["disp_title"] = intval($_POST["disp_title"]);
	$SYS_FORM["cache"]["disp_body"]  = intval($_POST["disp_body"]);
	foreach ($_POST as $key => $value) {
		if (preg_match('/^url_\d+/', $key, $match)) {
			if ($value != '') {
				$SYS_FORM["cache"]["url"][] = $value;
			}
		}
	}
	$SYS_FORM["cache"]["keyword"] = mb_convert_encoding($SYS_FORM["cache"]["keyword"], 'UTF-8', 'auto');
	$SYS_FORM["cache"]["keyword"] = mb_ereg_replace("　", " ", $SYS_FORM["cache"]["keyword"]);
	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["url"]) {
		$SYS_FORM["error"]["url"] = '最低１つのURLを入力してください。';
	}
	if (!$SYS_FORM["cache"]["disp_type"]) {
		$SYS_FORM["cache"]["disp_type"] = 1;
	}
	if (!$SYS_FORM["cache"]["disp_num"]) {
		$SYS_FORM["cache"]["disp_num"] = MOD_RSS_DEFAULT_NUM;
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	// settingに登録
	$d = mysql_exec("delete from rss_setting where eid = %s", mysql_num($eid));
	$q = mysql_exec("insert into rss_setting".
					" (eid, header, footer, keyword,".
					" disp_type, disp_num, disp_title, disp_body)".
					" values (%s, %s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["rss_header"]),
					mysql_str($SYS_FORM["cache"]["rss_footer"]),
					mysql_str($SYS_FORM["cache"]["keyword"]),
					mysql_num($SYS_FORM["cache"]["disp_type"]),
					mysql_num($SYS_FORM["cache"]["disp_num"]),
					mysql_num($SYS_FORM["cache"]["disp_title"]),
					mysql_num($SYS_FORM["cache"]["disp_body"]));

	if (!$q) {
		die("insert failuer...");
	}

	// urlに登録
	$d = mysql_exec("delete from rss_url where eid = %s", mysql_num($eid));
	$num = 0;
	foreach ($SYS_FORM["cache"]["url"] as $url) {
		$num++;
		$q = mysql_exec("insert into rss_url (eid, num, url) values (%s, %s, %s)",
						mysql_num($eid), mysql_num($num), mysql_str($url));
		if (!$q) {
			die("insert url failure...");
		}
	}

	mod_rss_crawl($eid);

	$html = '編集完了しました。';
	$data = array(title   => 'RSSの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	$d = mysql_uniq("select * from rss_setting where eid = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$header     = $d["header"];
		$footer     = $d["footer"];
		$keyword    = $d["keyword"];
		$disp_type  = $d["disp_type"];
		$disp_num   = $d["disp_num"];
		$disp_title = $d["disp_title"];
		$disp_body  = $d["disp_body"];
		$url        = get_url($eid);
	}
	else {
		$header     = '';
		$footer     = '';
		$keyword    = '';
		$disp_type  = 1;
		$disp_num   = MOD_RSS_DEFAULT_NUM;
		$disp_title = 0;
		$disp_body  = 0;
		$url        = array();
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$header     = $SYS_FORM["cache"]["rss_header"];
		$footer     = $SYS_FORM["cache"]["rss_footer"];
		$keyword    = $SYS_FORM["cache"]["keyword"];
		$disp_type  = $SYS_FORM["cache"]["disp_type"];
		$disp_num   = $SYS_FORM["cache"]["disp_num"];
		$disp_title = $SYS_FORM["cache"]["disp_title"];
		$disp_body  = $SYS_FORM["cache"]["disp_body"];
		$url        = $SYS_FORM["cache"]["url"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:url
	$add_url_block = '<div id="rss_add_input">URLの入力欄を増やす。</div>';

	$url_count = 0;
	if (count($url) > 0) {
		foreach ($url as $u) {
			$del_btn['url_'. $url_count] = true;
			$attr = array(name => 'url_'. $url_count, value => $u, size => 64);
			$url_input .= get_form("text", $attr);
			$url_count++;
		}
	}
	else {
		$del_btn['url_'. $url_count] = true;

		$attr = array(name => 'url_'. $url_count, value => $u, size => 64);
		$url_input .= get_form("text", $attr);
		$url_count++;
	}
	foreach ($del_btn as $del_id => $v) {
		$script = "\$(\\'#". $del_id. "\\').val(\\'\\');";
		$JQUERY["ready"][] = '$(\'#'. $del_id. '\').after('. '\' <a href="#" id="del_'. $del_id. '" '.
							 'onClick="'. $script. '">消</a>'. '\');';
	}

	$SYS_FORM["input"][] = array(title => 'RSSのURL',
								 name  => 'url_'. $url_count,
								 body  => $url_input. $add_url_block);

	// text:keyword
	$attr = array(name => 'keyword', value => $keyword,
				  size => 32, ahtml => '<div>複数は空白で区切ってください。</div>');
	$SYS_FORM["input"][] = array(title => '検索キーワード',
								 name  => 'keyword',
								 body  => get_form("text", $attr));

	// text:disp_num
	$attr = array(name => 'disp_num', value => $disp_num,
				  size => 3, ahtml => ' (最大'. MOD_RSS_MAX_NUM. '件)');
	$SYS_FORM["input"][] = array(title => 'RSSあたりの表示件数',
								 name  => 'disp_num',
								 body  => get_form("num", $attr));

	// text:disp_type
	$option = array(1 => '取得順', 2 => '記事の日付');
	$attr = array(name => 'disp_type', value => $disp_type, option => $option);
	$SYS_FORM["input"][] = array(title => '表示順',
								 name  => 'disp_type',
								 body  => get_form("radio", $attr));

	// checkbox:disp_body
	$attr = array(name => 'disp_title', value => $disp_title, option => array(1 => 'タイトルを表示する'),
				  ahtml => '<div>表示順が取得順の場合のみ設定できます。</div>');
	$SYS_FORM["input"][] = array(title => 'RSSのタイトル表示',
								 name  => 'disp_title',
								 body  => get_form("checkbox", $attr));

	// checkbox:disp_body
	$attr = array(name => 'disp_body', value => $disp_body, option => array(1 => '概要文も表示する'),
				  ahtml => '<div>文中のHTMLタグ等は無効になります。</div>');
	$SYS_FORM["input"][] = array(title => '概要文の表示',
								 name  => 'disp_body',
								 body  => get_form("checkbox", $attr));

	// fck:header
	$attr = array(name => 'rss_header', value => $header,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'ヘッダー (RSSの上側に表示)',
								 name  => 'rss_header',
								 body  => get_form("fck", $attr));

	// fck:footer
	$attr = array(name => 'rss_footer', value => $footer,
				  cols => 64, rows => 8, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => 'フッター (RSSの下側に表示)',
								 name  => 'rss_footer',
								 body  => get_form("fck", $attr));

	
	// url用のスクリプト
	$SITE_URL = CONF_URLBASE;
	$JQUERY["ready"][] = <<<___READY_CODE__
var url_count = ${url_count};
var site_url = '$SITE_URL';
\$('#rss_add_input').click(function() {
	url_count++;
	var url_out_count = 0;
	for(i=0;i<url_count;i++){
		var url = new String($('#url_'+i).val());
		if(url.substr(0,site_url.length) == site_url)continue;
		url_out_count ++;
	}
	if (url_out_count > 10) {
		alert('増やし過ぎると重くなります。');
	}
	\$('#rss_add_input').before('<div class="input_body">' +
								'<input type="text" class="input_text" id="url_' + url_count +
								'" name="url_' + url_count +
								'" size="64"></div>');


	script = 'jQuery\\(\\'#url_' + url_count + '\\'\\).val\\(\\'\\'\\);';
	$('#url_' + url_count).after(' <a href="#" id="del_' + url_count +  '" onClick="' + script + '">消</a>');

});

$('#disp_type_0').click(function() {
	$('#disp_title_0').removeAttr('disabled');
});
$('#disp_type_1').click(function() {
	$('#disp_title_0').attr('disabled', 'disabled');
});
___READY_CODE__;
	;
	if ($disp_type == 2) {
		$JQUERY["ready"][] = '$(\'#disp_title_0\').attr(\'disabled\', \'disabled\');';
	}

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$html = create_form(array(eid => $eid));

	$data = array(title   => 'RSSの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function get_url($eid) {
	$url = array();

	$q = mysql_full("select * from rss_url where eid = %s order by num",
					mysql_num($eid));
	if (!$q) {
		return array();
	}
	while ($d = mysql_fetch_array($q)) {
		$url[] = $d["url"];
	}
	return $url;
}

?>
