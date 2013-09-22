<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

display_css();

/* ふりわけ。*/
$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($act) {
	case 'set':
		set_menubar();
	break;
	case 'select':
		select_menubar();
	break;
	case 'regist':
		regist_data();
	case 'entry':
		input_data();
	break;
	case 'edit':
		edit_menubar();
	break;
	default:
		action_list();
}

/* 登録*/
function regist_data() {
	global $SYS_FORM;

	$id    = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$title = htmlesc($_POST['title']);
	$css   = $_POST['css'];

	if ($id > 0) {
		$u = mysql_exec('update menubar_css set title = %s, css = %s'.
						' where id = %s',
						mysql_str($title), mysql_str($css), mysql_num($id));
		if (!$u) {
			show_error('更新に失敗。'. mysql_error());
		}
	}
	else {
		$i = mysql_exec('insert into menubar_css (title, css)'.
						' values(%s, %s)',
						mysql_str($title), mysql_str($css));
		if (!$i) {
			show_error('追加に失敗。'. mysql_error());
		}
	}

	$ref = '/manager/css/menubar.php';

	$html = '編集完了しました。';
	$data = array(title   => 'メニューバー編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'メニューバー管理に戻る',)));

	show_input($data);

	exit(0);
}

function input_data() {
	global $SYS_FORM;

	$id    = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$title = '';
	$css   = '';

	if ($id > 0) {
		$q = mysql_uniq('select * from menubar_css where id = %s',
						mysql_num($id));
		if (!$q) {
			show_error('見当たりません。');
		}

		$title = $q['title'];
		$css   = $q['css'];
	}

	// hidden:id
	$attr = array('name' => 'id', 'value' => $id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	// hidden:action
	$attr = array('name' => 'action', 'value' => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	// hidden:title
	$attr = array('name' => 'title', 'value' => $title, 'size' => 32);
	$SYS_FORM["input"][] = array('title' => 'タイトル',
								 'name'  => 'title',
								 'body'  => get_form("text", $attr));
	$attr = array('name' => 'css', 'value' => $css, 'height' =>'500px', 'width' => '100%');
	$SYS_FORM["input"][] = array('title' => 'スタイルシート',
								 'name'  => 'css',
								 'body'  => get_form("textarea", $attr));

	$SYS_FORM['input'][] = array('title' => 'メニューバーで使用される HTML',
								 'body'  => example_code());

	$SYS_FORM["action"] = 'menubar.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '追加';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array('title'   => 'メニューバーの追加',
				  'icon'    => 'write',
				  'content' => $html);

	show_input($data);

	exit(0);
}

/* 登録*/
function set_menubar() {
	global $SYS_FORM;

	$id    = isset($_POST['id']) ? intval($_POST['id']) : 0;

	$d = mysql_exec('delete from menubar where id = %s',
					mysql_num(0));
	$i = mysql_exec('insert into menubar (id, menubar)'.
					' values(%s, %s)',
					mysql_num(0), mysql_num($id));
	if (!$i) {
		show_error('追加に失敗。'. mysql_error());
	}

	$ref = '/manager/css/menubar.php';

	$html = '編集完了しました。';
	$data = array(title   => 'メニューバー編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref,
															 string => 'メニューバー管理に戻る',)));

	show_input($data);

	exit(0);
}

function select_menubar() {
	global $SYS_FORM;

	$c = mysql_uniq('select * from menubar where id = 0');
	if ($c) {
		$current = $c['menubar'];
	}

	$f = mysql_full('select * from menubar_css order by id');

	$menubar = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$menubar[$r['id']] = $r['title'];
		}
		$attr = array('name' => 'id', 'value' => $current, 'option' => $menubar);
		$SYS_FORM["input"][] = array('title' => 'メニューバーの選択',
									 'body'  => get_form("select", $attr));
	}

	// hidden:action
	$attr = array('name' => 'action', 'value' => 'set');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$SYS_FORM["action"] = 'menubar.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'メニューバーを設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array('title'   => 'メニューバーの設定',
				  'icon'    => 'write',
				  'content' => $html);

	show_input($data);

	exit(0);
}

function action_list() {
	$html = <<<_HTML_
<ul class="menubar_index">
<li><a href="menubar.php?action=select">メニューバーを設定する</a></li>
<li><a href="menubar.php?action=entry">新たにメニューバーを追加する</a></li>
<li><a href="menubar.php?action=edit">既存のメニューバーを編集する</a></li>
</ul>
_HTML_
	;

	$data = array('title'   => 'メニューバーの管理',
				  'icon'    => 'write',
				  'content' => $html);

	show_input($data);
}

function edit_menubar() {
	global $SYS_FORM;

	$f = mysql_full('select * from menubar_css order by id');

	$menubar = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$menubar[$r['id']] = $r['title'];
		}
		$attr = array('name' => 'id', 'value' => '', 'option' => $menubar);
		$SYS_FORM["input"][] = array('title' => '登録済メニューバーの編集',
									 'body'  => get_form("select", $attr));
	}
	else {
		show_error('先に追加を行って下さい。');
	}

	// hidden:action
	$attr = array('name' => 'action', 'value' => 'entry');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$SYS_FORM["action"] = 'menubar.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '編集';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array('title'   => 'メニューバーの編集',
				  'icon'    => 'write',
				  'content' => $html);

	show_input($data);

	exit(0);
}

function display_css() {
	global $COMUNI_HEAD_CSSRAW;

	$COMUNI_HEAD_CSSRAW[] =  <<<___CSS___
.menubar_index {
	list-style-type: square;
	margin: 10px 25px;
}

.entry_button {
	display: block;
	padding: 3px;
	margin: 2px;
	width: 8em;
	text-align: center;
	background: #fefefe;
	border: solid 1px #d8ddde;
	font-weight: bold;
}
___CSS___
	;
}

function example_code() {
	$url      = CONF_SITEURL;
	$sitename = CONF_SITENAME;

	return <<<__TMPL__
<div style="color: #999999; font-size: 0.9em;">
&lt;div id="menubar"&gt;<br>
&lt;div id="menubar_logoimg"&gt;&lt;a href="${url}"&gt;&lt;/a&gt;&lt;/div&gt;<br>
&lt;div id="menubar_logotxt"&gt;${sitename}&lt;/div&gt;<br>
&lt;div id="menubar_menutxt"&gt;<br>
&lt;a href=""&gt;マイページ&lt;/a&gt; - &lt;a href="" &gt;各種設定&lt;/a&gt; - &lt;a href=""&gt;ログイン&lt;/a&gt;<br>
&lt;/div&gt;<br>
&lt;/div&gt;<br>
&lt;div id="menubar_clear"&gt;&lt;/div&gt;<br>
</div>
__TMPL__
	;
}

?>
