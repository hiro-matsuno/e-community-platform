<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

define('SKINDIR',CONF_BASEDIR.'/skin');

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'step2':
		step2();
		break;
	case 'regist':
		write_skin();
		break;
	default:
		input_new();
}

function step2(){
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSS, $COMUNI_HEAD_JS;	
	
	$filename = $_REQUEST['filename'];
	$overwrite = $_REQUEST['overwrite'];
	$title = htmlspecialchars($_REQUEST['title']);

	//スキン用ディレクトリの確認・作成
	if(!$overwrite){
		if(file_exists(SKINDIR.'/'.$filename))
			$SYS_FORM['error']['filename'] = SKINDIR.'/'.$filename.'が存在します。<br>'.
											'別の名前を指定してください。';
		if(file_exists(SKINDIR.'/'.$filename.'.tpl'))
			$SYS_FORM['error']['filename'] = SKINDIR.'/'.$filename.'.tpl'.'が存在します。<br>'.
											'別の名前を指定してください。';
		if(file_exists(SKINDIR.'/'.$filename.'.css'))
			$SYS_FORM['error']['filename'] = SKINDIR.'/'.$filename.'.css'.'が存在します。<br>'.
											'別の名前を指定してください。';
	}
	
	if(!$title)
		$SYS_FORM['error']['title'] = 'スキンのタイトルを入力してください';
		
	$SYS_FORM['cache']['filename'] = $filename;
	$SYS_FORM['cache']['overwrite'] = $overwrite;
	$SYS_FORM['cache']['title'] = $title;
	$SYS_FORM['cache']['description'] = htmlspecialchars($_REQUEST['description']);
	$SYS_FORM['cache']['withtitle'] = intval($_REQUEST['withtitle']);
	$SYS_FORM['cache']['column'] = intval($_REQUEST['column']);
	$SYS_FORM['cache']['pmt'] = intval($_REQUEST['pmt']);
	$SYS_FORM['cache']['variation'] = intval($_REQUEST['variation']);
	$SYS_FORM['cache']['var_title'] = $_REQUEST['var_title'];
	
	if(isset($SYS_FORM['error']))
		input_new();

	$file_head = SKINDIR.'/'.$filename;

	//ファイルアップロード時のエラー処理
	switch($_FILES['headfile']['error']){
		case UPLOAD_ERR_OK:
			//アップロード成功:
			break;
        case UPLOAD_ERR_INI_SIZE:
        	show_error( 'ファイルサイズの制限'.ini_get('upload_max_filesize').'バイトを超過しています。');
        	break;
        case UPLOAD_ERR_FORM_SIZE:
        	show_error('ファイルサイズの制限'.$max_filesize.'バイトを超過しています。');
        	break;
        case UPLOAD_ERR_NO_FILE:
        	show_error('ファイルを選択してください。');
        	break;
        default:
        	show_error('ファイルのアップロード中にエラーが発生しました。');
        	break;
	}
	//ヘッダ画像のコピー
	mkdir($file_head);
	chmod($file_head,0777);

	$ext = array_pop(explode('.',$_FILES['headfile']['name']));
	$banimg = "ban.$ext";
	copy($_FILES['headfile']['tmp_name'],$file_head.'/'.$banimg);
	chmod($file_head.'/'.$banimg,0777);
	$SYS_FORM['cache']['bannar_file'] = htmlspecialchars($filename.'/'.$banimg);
       
	//以下入力フォーム生成
	$COMUNI_HEAD_CSS[] = "/lib/farbtastic/farbtastic.css";
	$COMUNI_HEAD_JS[] = "/lib/farbtastic/farbtastic_mod.js";

	$JQUERY['ready'][] = <<<__JS__
	$('#bg_color').addClass('colorwell');
	$('#link_color').addClass('colorwell');
	$('#link_visited_color').addClass('colorwell');
	$('#page_title_color').addClass('colorwell');
	$('#block_title_bg_color').addClass('colorwell');
	$('#block_title_color').addClass('colorwell');
	$('#footer_bg_color').addClass('colorwell');
	$('#footer_color').addClass('colorwell');
	var f = jQuery.farbtastic('#color_picker');
	var p = $('#color_picker').css('opacity', 0.25);
	var selected;
	$('.colorwell')
		.each(function() { f.linkTo(this); $(this).css('opacity', 0.75); })
		.focus(function() {
			if (selected) {
				$(selected).css('opacity', 0.75).removeClass('colorwell-selected');
			}
			f.linkTo(this);
			p.css('opacity', 1);
			$(selected = this).css('opacity', 1).addClass('colorwell-selected');
		});
__JS__;

	foreach($SYS_FORM['cache'] as $var => $val){
		$attr = array(name => $var, value => $val);
		$SYS_FORM['input'][] = array(body => get_form('hidden',$attr));
	}
	
	$page_title_color = '#000000';
	$bg_color = '#FFFFFF';
	$link_color = '#0000FF';
	$link_visited_color = '#9c3838';
	$bannar_file;
	$block_title_bg_color = '#00fff2';
	$block_title_color = '#000000';
	$footer_bg_color = '#00fff2';
	$footer_color = '#000000';
	
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM['input'][] = array(body => get_form('hidden',$attr));
	
	
	$attr = array(name => 'bg_color', value => $bg_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'ページの背景色',
								 name  => 'bg_color',
								 body  => get_form("text", $attr));
								 
	$attr = array(name => 'link_color', value => $link_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'リンク文字色',
								 name  => 'link_color',
								 body  => get_form("text", $attr));
								 
	$attr = array(name => 'link_visited_color', value => $link_visited_color, size => 8);
	$SYS_FORM["input"][] = array(title => '訪問済みリンク文字色',
								 name  => 'link_visited_color',
								 body  => get_form("text", $attr));

	if($withtitle){
		$attr = array(name => 'page_title_color', value => $page_title_color, size => 8);
		$SYS_FORM["input"][] = array(title => 'ページのタイトル文字色',
									 name  => 'page_title_color',
									 body  => get_form("text", $attr));
	}

	$attr = array(name => 'block_title_bg_color', value => $block_title_bg_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'パーツのタイトル背景',
								 name  => 'block_title_bg_color',
								 body  => get_form("text", $attr));

	$attr = array(name => 'block_title_color', value => $block_title_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'パーツのタイトル文字色',
								 name  => 'block_title_color',
								 body  => get_form("text", $attr));

	$attr = array(name => 'footer_bg_color', value => $footer_bg_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'フッターの背景色',
								 name  => 'footer_bg_color',
								 body  => get_form("text", $attr));

	$attr = array(name => 'footer_color', value => $footer_color, size => 8);
	$SYS_FORM["input"][] = array(title => 'フッターの文字色',
								 name  => 'footer_color',
								 body  => get_form("text", $attr));

	$SYS_FORM["input"][] = array(title => '色選択ツール',
								 name  => 'color_picker',
								 body  => '<div id="color_picker">Now loading a color picker tool...</div>');

	$SYS_FORM["action"] = 'create.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';
	$SYS_FORM["cancel"] = '戻る';

	$html = '';
	$html .= '各部の色を設定します。<br>直接数値を入力するほか、入力エリアをクリックして色選択ツールで入力することもできます。';
	$html .= create_form(array(eid => 0));

	$data = array(title   => 'スキンの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
								 
}

function write_skin(){
	$filename = $_REQUEST['filename'];
	$bg_color = $_REQUEST['bg_color'];
	$link_color = $_REQUEST['link_color'];
	$link_visited_color = $_REQUEST['link_visited_color'];
	$bannar_file = $_REQUEST['bannar_file'];
	$page_title_color = $_REQUEST['page_title_color'];
	$block_title_bg_color = $_REQUEST['block_title_bg_color'];
	$block_title_color = $_REQUEST['block_title_color'];
	$footer_bg_color = $_REQUEST['footer_bg_color'];
	$footer_color = $_REQUEST['footer_color'];
	$withtitle = $_REQUEST['withtitle'];
	$column = $_REQUEST['column'];
	$pmt = $_REQUEST['pmt'];

	list(,$h) = getimagesize(SKINDIR.'/'.$bannar_file);
	$bannar_height = $h;

	if($withtitle){
		if($column == 2)$source = 'withtitle_2c';
		else $source = 'withtitle_3c';
	}else{
		if($column == 2)$source = 'nontitle_2c';
		else $source = 'nontitle_3c';
	}

	$src_head = CONF_BASEDIR.'/manager/skin/tplfile/'.$source;
	$dst_head = SKINDIR.'/'.$filename;

	copy($src_head.'.tpl',$dst_head.'.tpl');
	chmod($dst_head.'.tpl',0777);
	
	$css_fh = fopen($dst_head.'.css','w');
	include_once $src_head.'.css.php';
	fwrite($css_fh,$css_file_content);
	fclose($css_fh);
	chmod($dst_head.'.css',0777);
	
	$file_dirh = opendir($src_head);

	while(($f = readdir($file_dirh)) !== false){
		if($f=='.' or $f=='..')continue;
		copy($src_head.'/'.$f,$dst_head.'/'.$f);
		chmod($dst_head.'/'.$f,0777);
	}

	$title = htmlspecialchars($_REQUEST['title']);
	$description = htmlspecialchars($_REQUEST['description']);
	$var_title = htmlspecialchars($_REQUEST['var_title']);
	$var_id = intval($_REQUEST['variation']);
	$layout = $column ==2?5:6;
	//2column_nocss or 3column_nocss

	mysql_query('lock table theme_skin write');

	$q = mysql_uniq('select id from theme_skin where filename=%s',mysql_str($filename));
	if($q){
		$id = $q['id'];
		
		$q = mysql_exec('delete from theme_skin where filename=%s',mysql_str($filename));
	}else{
		$c = mysql_uniq('select max(id) from theme_skin');
		$id = $c['max(id)'] + 1;
	}
	$var_id = $var_id? intval($var_id): $id;

	$q = mysql_exec('insert into theme_skin '.
					' (id, filename, thumb, title, description, pmt, layout_id, parent_skin_id, var_title)'.
					' values(%s, %s, %s, %s, %s, %s, %s, %s, %s)',
					mysql_num($id), mysql_str($filename), mysql_str('no_image.gif'),
					mysql_str($title), mysql_str($description), mysql_str($pmt),
					mysql_num($layout),mysql_num($var_id), mysql_str($var_title));
	if(!$q)show_error(mysql_error());
	
	mysql_query('unlock table');
	
	$ref = '/manager/skin/list.php';

	$html = '編集完了しました。';
	$data = array(title   => 'スキン編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'スキン選択に戻る',)));

	show_input($data);

	exit(0);
}

function input_new(){
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	if(!is_writable(SKINDIR))show_error(SKINDIR.'に書き込みできません。<br>書き込み可能となるように設定してください。');

	if(isset($SYS_FORM['cache'])){
		$filename = $SYS_FORM['cache']['filename'];
		$title = $SYS_FORM['cache']['title'];
		$description = $SYS_FORM['cache']['description'];
		$withtitle = $SYS_FORM['cache']['withtitle'];
		$column = $SYS_FORM['cache']['column'];
		$variation = $SYS_FORM['cache']['variation'];
		$var_title = $SYS_FORM['cache']['var_title'];
		if(!isset($SYS_FORM['error']['headfile']))
			$SYS_FORM['error']['headfile'] = 'お手数ですがもう一度ファイルを指定してください';
	}else{
		$i = 0;
		do{
			$filename = sprintf('skin%04d',++$i);
		}while(file_exists(SKINDIR.'/'.$filename));
		$title = "新規スキン$i";

		$JQUERY['ready'][] = <<<__JS__
			$('#var_title').val($('#column').children("option:selected").text());
__JS__;
	}
	
	$JQUERY['ready'][] = <<<__JS__
	$('#column').change(
		function(){
			$('#var_title').val($('#column').children("option:selected").text());
		}
	)
__JS__;
	
	$attr = array(name => 'action', value => 'step2');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array('name' => 'overwrite', option => array(1 => '同名のファイルを上書き'), value => array(1 => $overwrite));
	$chk_overwrite = get_form('checkbox', $attr);
	$attr = array(name => 'filename', value => $filename);
	$SYS_FORM["input"][] = array(title => 'ファイルヘッダ',
								 name  => 'filename',
								 body  => get_form("text", $attr).$chk_overwrite);
	
	$attr = array(name => 'title', value => $title, size => 32);
	$SYS_FORM["input"][] = array(title => 'スキンのタイトル',
								 name  => 'title',
								 body  => get_form("text", $attr));

	$attr = array(name => 'description', value => $description, height =>'180px', width => '100%');
	$SYS_FORM["input"][] = array(title => 'スキンの説明',
								 name  => 'description',
								 body  => get_form("textarea", $attr));

	$note = 'ページ上部に掲載される画像を指定します。<br>'.
			'画像の幅は２カラムの場合は780ピクセル、３カラムの場合は880ピクセルとしてください。<br>';
	$attr = array(name => 'headfile', bhtml => $note);
	$SYS_FORM["input"][] = array(title => 'ページヘッダの背景画像',
								name => 'headfile',
								body => get_form('file', $attr));

	//select:title
	$attr = array(name => 'withtitle', option => array(0=>'タイトル非表示',1=>'タイトル表示'), value => $withtitle);
	$SYS_FORM["input"][] = array(title => 'ヘッダ画像上のページタイトル表示',
								 name  => 'withtitle',
								 body  => get_form("select", $attr));

	//select:column
	$attr = array(name => 'column', option => array(2=>'２カラム',3=>'３カラム'), value => $column);
	$SYS_FORM["input"][] = array(title => 'カラム数の選択',
								 name  => 'column',
								 body  => get_form("select", $attr));

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

	/* バリエーション登録 */
	$s = mysql_full('select * from theme_skin');

	if (!$s) { show_error('謎エラー'. mysql_error()); }

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

	$SYS_FORM["action"] = 'create.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '次へ';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'スキンの作成',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
