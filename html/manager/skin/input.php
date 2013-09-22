<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data();
	case 'edit':
		input_data();
	case 'entry':
		entry_data();
	case 'new':
		input_new();
	default:
		select_data();
}

/* 登録*/
function regist_data() {
	global $SYS_FORM;

	$skin_id     = intval($_REQUEST['skin_id']);

	$q = mysql_uniq('select * from theme_skin where id = %s', mysql_num($skin_id));
	if (!$q) {
		show_error('スキンが見当たりません');
	}

	$filename    = $_REQUEST['filename'];
	$thumb       = $_REQUEST['thumb'];
	$title       = $_REQUEST['title'];
	$description = $_REQUEST['description'];

	$layout      = $_REQUEST['layout'];
	$var_title = $_REQUEST['var_title'] ? $_REQUEST['var_title'] : 'メイン';
	$var_id = $_REQUEST['variation']?intval($_REQUEST['variation']):$skin_id;
	$layout_id = $_REQUEST['layout']?intval($_REQUEST['layout']):$q['layout_id'];
	$pmt = $_REQUEST['pmt']?intval($_REQUEST['pmt']):$q['pmt'];
	
	$q = mysql_exec('update theme_skin set'.
					' filename = %s, thumb = %s, title = %s, description = %s,'.
					' parent_skin_id = %s, var_title = %s, layout_id = %s, pmt = %s'.
					' where id = %s',
					mysql_str($filename), mysql_str($thumb),
					mysql_str($title), mysql_str($description),
					mysql_num($var_id), mysql_str($var_title), mysql_num($layout_id), mysql_num($pmt),
					mysql_num($skin_id));

	if (!$q) {
		show_error(mysql_error());
	}

	$css = $_REQUEST['css'];
	if (get_magic_quotes_gpc()) {
		$css = stripslashes($css);
	}

	$tmp_path = CONF_BASEDIR. '/skin/'. rand_str(24). '_css.cgi';

	file_exists( ( $css_path = dirname(__FILE__)."/../../".'skin/'.$filename.'/'.$filename.'.css' ) )
	or file_exists( ( $css_path = dirname(__FILE__)."/../../".'skin/'.$filename.'.css' ) );
	
	if ($css_fh = fopen($tmp_path, "w")) {
		flock($css_fh, LOCK_EX);
		fputs($css_fh, $css);
		flock($css_fh, LOCK_UN);
		fclose($css_fh);
		rename($tmp_path, $css_path);

		chmod($css_path, 0666);
	}

	$tpl = $_REQUEST['tpl'];
	if (get_magic_quotes_gpc()) {
		$tpl = stripslashes($tpl);
	}

	$tmp_path = CONF_BASEDIR. '/skin/'. rand_str(24). '_tpl.cgi';

	file_exists( ( $tpl_path = dirname(__FILE__)."/../../".'skin/'.$filename.'/'.$filename.'.tpl' ) )
	or file_exists( ( $tpl_path = dirname(__FILE__)."/../../".'skin/'.$filename.'.tpl' ) );

	if ($tpl_fh = fopen($tmp_path, "w")) {
		flock($tpl_fh, LOCK_EX);
		fputs($tpl_fh, $tpl);
		flock($tpl_fh, LOCK_UN);
		fclose($tpl_fh);

		rename($tmp_path, $tpl_path);
		chmod($tpl_path, 0666);
	}

	$ref = '/manager/skin/list.php';

	$html = '編集完了しました。';
	$data = array(title   => 'スキン編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'スキン選択に戻る',)));

	show_input($data);

	exit(0);
}

function input_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$skin_id = intval($_REQUEST['skin_id']);

	$q = mysql_uniq('select * from theme_skin where id = %s', mysql_num($skin_id));
	if (!$q) {
		show_error('スキンが見当たりません');
	}

	$filename    = $q['filename'];
	$thumb       = $q['thumb'];
	$title       = $q['title'];
	$description = $q['description'];
	$variation   = $q['parent_skin_id'];
	$var_title   = $q['var_title'];
	$layout_id = intval($q['layout_id']);
	$pmt = $q['pmt'];
	
	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'skin_id', value => $skin_id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin
	$attr = array(name => 'filename', value => $filename);
	$SYS_FORM["input"][] = array(title => 'ファイルヘッダ',
								 name  => 'filename',
								 body  => get_form("text", $attr));

	$attr = array(name => 'thumb', value => $thumb, size => 32, bhtml => CONF_URLBASE. '/skin/t/ ');
	$SYS_FORM["input"][] = array(title => 'サムネイルの画像ファイル名',
								 name  => 'thumb',
								 body  => get_form("text", $attr));

	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'スキンのタイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	$attr = array(name => 'description', value => $description, height =>'180px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'スキンの説明',
								 name  => 'description',
								 body  => get_form("textarea", $attr));

	// select:pmt
	$pmt_option = array('7' => '特に指定しない',
				 '3' => 'ポータルページ&amp;グループページ',
				 '6' => 'グループページ&amp;マイページ',
				 '5' => 'ポータルページ&amp;マイページ',
				 '1' => 'ポータルページのみ',
				 '2' => 'グループページのみ',
				 '4' => 'マイページのみ',
				 '0' => '管理者のみ使用可能',
				 '-1'=> '使用不可'
	);
	$attr = array(name => 'pmt', value => $pmt, option => $pmt_option);
	$SYS_FORM["input"][] = array(title => '公開範囲の選択',
								 name  => 'pmt',
								 body  => get_form("select", $attr));

	// select:layout_id
	$l = mysql_full('select id, filename, title from theme_layout as tl');

	$layout_option = array('' => '選択して下さい');
	if ($l) {
		while ($r = mysql_fetch_array($l)) {
			$title = $r['title'] ? $r['title'] : 'タイトル未設定';
			$layout_option[$r['id']] = $title;
		}
	}
	$attr = array(name => 'layout', value => $layout_id, option => $layout_option);
	$SYS_FORM["input"][] = array(title => 'カラムレイアウトの選択',
								 name  => 'layout',
								 body  => get_form("select", $attr));


	/* バリエーション登録 */
	$s = mysql_full('select * from theme_skin');

	if (!$s) { show_error('謎エラー'. mysql_error()); }

	$select_option = array('' => 'このスキンを主スキンとする');
	while ($r = mysql_fetch_array($s)) {
		if ($r['id'] == $skin_id) continue;
		$title = $r['title'] ? $r['title'] : 'タイトル未設定';
		$select_option[$r['id']] = $title. ' ('. $r['filename']. ')';
	}

	$attr = array(title => '主スキン', name => 'variation', value => $variation, option => $select_option,
				  bhtml => '既存のスキンのバリエーションである場合は選択して下さい。<br>');
	$sub_attr = array(title => '表示名', name => 'var_title', value => $var_title, size => 24);

	$SYS_FORM["input"][] = array(title => 'バリエーション登録',
								 name  => 'variation',
								 body  => get_form("select", $attr). get_form("text", $sub_attr));

	file_exists( ( $css_path = dirname(__FILE__)."/../../".'skin/'.$filename.'/'.$filename.'.css' ) )
	or file_exists( ( $css_path = dirname(__FILE__)."/../../".'skin/'.$filename.'.css' ) );

	if(!is_writable($css_path))$SYS_FORM["error"]["css"] = "'$css_path'の書き込み権限がないため、ここでの編集は無効です。";
	if (is_file($css_path)) {
		$fp = fopen ($css_path, "r");
		while (!feof($fp)) {
			$css .= fgets($fp, 1024);
		}
		fclose ($fp);
	}
	$css = mb_convert_encoding($css, 'UTF-8', 'auto');

	$attr = array(name => 'css', value => $css, height =>'300px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'CSS編集',
								 name  => 'css',
								 body  => get_form("textarea", $attr));

	file_exists( ( $tpl_path = dirname(__FILE__)."/../../".'skin/'.$filename.'/'.$filename.'.tpl' ) )
	or file_exists( ( $tpl_path = dirname(__FILE__)."/../../".'skin/'.$filename.'.tpl' ) );

	if(!is_writable($tpl_path))$SYS_FORM["error"]["tpl"] = "'$tpl_path'の書き込み権限がないため、ここでの編集は無効です。";
	if (is_file($tpl_path)) {
		$fp = fopen ($tpl_path, "r");
		while (!feof($fp)) {
			$tpl .= fgets($fp, 1024);
		}
		fclose ($fp);
	}
	$tpl = mb_convert_encoding($tpl, 'UTF-8');

	$attr = array(name => 'tpl', value => $tpl, height =>'300px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'テンプレート直接編集 (取り扱いにご注意下さい)',
								 name  => 'tpl',
								 body  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'スキンの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function select_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$s = mysql_full('select * from theme_skin');

	if (!$s) { show_error('謎エラー'. mysql_error()); }

	$select_option = array();
	while ($r = mysql_fetch_array($s)) {
		$title = $r['title'] ? $r['title'] : 'タイトル未設定';
		$select_option[$r['id']] = $title. ' ('. $r['filename']. ')';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'edit');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin
	$attr = array(title => 'スキン:', name => 'skin_id', value => '', option => $select_option);
	$SYS_FORM["input"][] = array(title => '編集するスキンの選択',
								 name  => 'skin_id',
								 body  => get_form("select", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'スキンの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_new() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	// hidden:action
	$attr = array(name => 'action', value => 'entry');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));


	// select:skin
	$comment = '<span style="color: #f00;">あらかじめ、テンプレートファイルをアップロードしておいてください。</span><br>';
	$attr = array(name => 'filename', value => $filename, bhtml => $comment);
	$SYS_FORM["input"][] = array(title => 'ファイルヘッダ',
								 name  => 'filename',
								 body  => get_form("text", $attr));

	$attr = array(name => 'thumb', value => $thumb, size => 32, bhtml => CONF_URLBASE. '/skin/t/ ');
	$SYS_FORM["input"][] = array(title => 'サムネイルの画像ファイル名',
								 name  => 'thumb',
								 body  => get_form("text", $attr));

	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'スキンのタイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	$attr = array(name => 'description', value => $description, height =>'180px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'スキンの説明',
								 name  => 'description',
								 body  => get_form("textarea", $attr));
								 
	// select:pmt
	$pmt_option = array('7' => '特に指定しない',
				 '3' => 'ポータルページ&amp;グループページ',
				 '6' => 'グループページ&amp;マイページ',
				 '5' => 'ポータルページ&amp;マイページ',
				 '1' => 'ポータルページのみ',
				 '2' => 'グループページのみ',
				 '4' => 'マイページのみ',
				 '0' => '管理者のみ使用可能',
				 '-1'=> '使用不可'
	);
	$attr = array(name => 'pmt', value => $pmt, option => $pmt_option);
	$SYS_FORM["input"][] = array(title => '公開範囲の選択',
								 name  => 'pmt',
								 body  => get_form("select", $attr));

	//layout_id
	$layout_id = '';
	$l = mysql_full('select id, filename, title from theme_layout as tl');

	$layout_option = array('' => '選択して下さい');
	if ($l) {
		while ($r = mysql_fetch_array($l)) {
			$title = $r['title'] ? $r['title'] : 'タイトル未設定';
			$layout_option[$r['id']] = $title;
		}
	}

	$lbs = mysql_uniq('select * from theme_skin where id = %s',
					  mysql_num($skin_id));

	if ($lbs) {
		$layout_id = intval($lbs['layout_id']);
	}

	// select:layout_id
	$attr = array(name => 'layout', value => $layout_id, option => $layout_option);
	$SYS_FORM["input"][] = array(title => 'カラムレイアウトの選択',
								 name  => 'layout',
								 body  => get_form("select", $attr));


	/* バリエーション登録 */
	$s = mysql_full('select * from theme_skin');

	if (!$s) { show_error('システムエラー'. mysql_error()); }

	$select_option = array('' => '選択して下さい');
	while ($r = mysql_fetch_array($s)) {
		if ($r['id'] == $skin_id) continue;
		$title = $r['title'] ? $r['title'] : 'タイトル未設定';
		$select_option[$r['id']] = $title. ' ('. $r['filename']. ')';
	}

	$variation = ''; $var_title = '';

	$attr = array(title => '主スキン', name => 'variation', value => $variation, option => $select_option,
				  bhtml => '既存のスキンのバリエーションである場合は選択して下さい。<br>');
	$sub_attr = array(title => '名称', name => 'var_title', value => $var_title, size => 24);

	$SYS_FORM["input"][] = array(title => 'バリエーション登録',
								 name  => 'variation',
								 body  => get_form("select", $attr). get_form("text", $sub_attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'スキンの追加',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function entry_data() {
	global $SYS_FORM;

	$filename    = $_REQUEST['filename'];
	$thumb       = $_REQUEST['thumb'] ? $_REQUEST['thumb'] : 'no_image.gif';
	$title       = $_REQUEST['title'];
	$description = $_REQUEST['description'];

	$layout      = $_REQUEST['layout'];
	$variation   = $_REQUEST['variation'];
	$var_title = $_REQUEST['var_title'] ? $_REQUEST['var_title'] : 'メイン';
	$pmt = $_REQUEST['pmt'];
	
	if (!$filename) {
		show_error('ファイルヘッダを入力してください。');
	}

	mysql_exec("lock table theme_skin write");
	$c = mysql_uniq('select max(id) from theme_skin');

	$max = $c['max(id)'];

	$id = $max + 1;
	$var_id = $variation? intval($variation): $id;
	
	$q = mysql_exec('insert into theme_skin '.
					' (id, filename, thumb, title, description, pmt, layout_id, parent_skin_id, var_title)'.
					' values(%s, %s, %s, %s, %s, %s, %s, %s, %s)',
					mysql_str($id), mysql_str($filename), mysql_str($thumb),
					mysql_str($title), mysql_str($description), mysql_str($pmt),
					mysql_num(intval($layout)),mysql_num($var_id), mysql_str($var_title));

	mysql_exec("unlock table");
	if (!$q) {
		show_error(mysql_error());
	}

	$ref = '/manager/skin/list.php';

	$html = '編集完了しました。';
	$data = array(title   => 'スキン追加完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'スキン選択に戻る',)));

	show_input($data);

	exit(0);
}


?>
