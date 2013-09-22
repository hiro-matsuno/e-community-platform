<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

$skin_level = get_skin_level();

admin_check();

print_list();

function print_list() {
	global $skin_level;

	$html = '<h3>スキンの追加</h3>';
	$add_link = mkhref(array('s' => 'アップロード済みスキンの登録', 'h' => 'input.php?action=new'));
	$create_link = mkhref(array('s'=>'新規スキンの作成', 'h' => 'create.php'));
	$html .= "<ul><li>$add_link</li><li>$create_link</li></ul>";

	$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : null;
	$level  = isset($_REQUEST['level']) ? $_REQUEST['level'] : 'all';

	if ($target) {
		$q = mysql_exec('update theme_skin set pmt = %s where id = %s',
						mysql_num($level), mysql_num($target));

		if (!mysql_affected_rows()) {
			$i = mysql_exec('insert into theme_skin(id, pmt) values (%s, %s)',
							mysql_num($target), mysql_num($level));
		}
		header('Location: '. CONF_URLBASE. '/manager/skin/pmt.php?level='. $level);
		exit(0);
	}

	$addq = '';
	if ($level == 'all') {
		$addq = ' order by id desc';
	}
	else {
		$addq = sprintf(' where pmt = %s order by id desc', mysql_num(intval($level)));
	}

	$q = mysql_full('select s.id, s.filename, s.title, s.pmt from theme_skin as s'.
					$addq);

	$list = array();
	$list[] = array('id'    => 'スキンID',
					'title' => 'タイトル (filename)',
					'edit'   => '編集');

	$style = array('uid' => 'width: 80px;', 'pmt' => 'width: 200px;');

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$edit_links = mkhref(array('s' => '[編集]', 'h' => "input.php?skin_id=$r[id]&action=edit"));
			$edit_links .= mkhref(array('s' => '[削除]', 'h' => "delete.php?skin_id=$r[id]&action=delete_confirm"));
			$list[] = array('id'    => $r['id'],
							'title' => $r['title']. ' ('. $r['filename']. ')',
							'edit'   => $edit_links);
		}
	}

	$html .= '<h3>既存スキンの編集</h3>';
//	$html .= '<div class="sub_menu" style="font-size: 0.8em; padding: 3px;">';
//	$html .= '<a href="pmt.php">全部表示</a> | ';
//	foreach ($skin_level as $l => $v) {
//		$html .= '<a href="pmt.php?level='. $l. '">'. $v. '</a> | ';
//	}
//	$html .= '</div>';

	$html .= create_list($list, $style);

	$data = array(title   => 'スキン一覧',
				  content => $html);

	show_input($data);

	exit(0);
}

function get_skin_level() {
	return array('7' => '特に指定しない',
				 '3' => 'ポータルページ&amp;グループページ',
				 '6' => 'グループページ&amp;マイページ',
				 '5' => 'ポータルページ&amp;マイページ',
				 '1' => 'ポータルページのみ',
				 '2' => 'グループページのみ',
				 '4' => 'マイページのみ',
				 '0' => '管理者のみ使用可能',
				 '-1'=> '使用不可'
	);
}

function select_tab($name = '', $value = '') {
	global $skin_level;

	list($n, $t) = explode('_', $name);

	$attr = array('name' => $name, 'value' => $value, 'option' => $skin_level,
				  'onChange' => 'location.href=\'pmt.php?target='. $t. '&level=\' + this.value;');
	return get_form('select', $attr);
}

?>
