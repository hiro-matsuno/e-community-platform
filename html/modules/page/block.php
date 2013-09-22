<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_page_config($eid) {
	global $COMUNI;

	if (!is_owner($eid)) {
		return;
	}

	$menu = array();
	$menu[] = array(title => '編集',
					url => '/modules/page/input.php?pid='. $eid,
					inline => false);

	return block_edit_menu($eid, $menu);
}

function mod_page_block($eid) {
	$title = 'ページ';

	$content = mod_page_get_latest($eid);

	return array(id => $eid, editlink => mod_page_config($eid), title => $title, content => $content);
}

function mod_page_get_latest($eid) {
	$data = mysql_uniq("select * from page_data where pid = %s".
					   " order by initymd desc limit 1;", mysql_num($eid));

	if (!$data) {
		return;
	}

	//旧仕様対策 記事のほうが公開範囲が狭ければ、パーツの公開範囲を記事に合わせてしまう
	if (!check_pmt($data["id"])) {
		set_pmt(array('eid' => $eid,
					  'uid' => get_uid($eid),
					  'gid' => get_gid($eid),
					  'unit' => get_pmt($data["id"])));
	}

	if ($data["subject"]) {
		return '<h3 class="m_page_subject">'. $data["subject"]. '</h3>'.
			   '<div class="m_page_body">'. $data["body"]. '</div>';
	}
	else {
		return '<div class="m_page_body">'. $data["body"]. '</div>';
	}
}

function mod_page_mkbody($key = null, $title = null, $data = null) {
	if (!$data) {
		return;
	}

	$title = '<div class="profile_title">'. $title. '</div>';
	$class = 'profile_body';
	switch ($key) {
		case 'thumb':
			$title   = '';
			$content = '<img src="/u/t/b/'. $data. '" border="0">';
			$class   = 'profile_icon';
			break;
		case 'sex':
			$option = array(1 => '男性', 2 => '女性', 3 => '秘密');
			$content = $option[$data];
			break;
		case 'birthday':
			$content = date('Y年m月d日', $data);
			break;
		case 'blood':
			$option = array(1 => 'A型', 2 => 'B型', 3 => 'O型', 4 => 'AB型', 5 => '?');
			$content = $option[$data];
			break;
		case 'name':
		case 'place':
		case 'birthplace':
		case 'hobby':
		case 'job':
 		case 'profile':
		case 'fav1':
		case 'fav2':
		case 'fav3':
		default:
			$content = $data;
	}

	$body = '<div class="'. $class. '">'. $content. '</div>';

	return $title. $body;
}

?>
