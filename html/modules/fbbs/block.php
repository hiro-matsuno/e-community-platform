<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');

function mod_fbbs_block($id = 0) {
	$html = '';

	$s = mysql_uniq('select * from mod_fbbs_setting where id = %s', mysql_num($id));

	if ($s) {
		$header = $s['header'];
		$footer = $s['footer'];
		$view_type = $s['view_type'];
		$view_num  = $s['view_num'];
	}
	else {
		$header = '';
		$footer = '';
		$view_type = 1;
		$view_num  = 10;
	}

	$allow = true;
	$c = mysql_uniq('select * from mod_fbbs_allow where eid = %s', mysql_num($id));
	if ($c) {
		$allow_type = $c['type'];
	}

	if ($allow_type == 2) {
		if (!is_joined(get_gid($id))) {
			$allow = false;
		}
	}
	else if ($allow_type == 1) {
		if (!is_login()) {
			$allow = false;
		}
	}

	$html .= '<div>'. $header. '</div>';

	$html .= mod_fbbs_get_thread($id, $view_type, $view_num);

	if ($allow == true) {
		$href  = '/index.php?module=fbbs&action=input_thread&pid='. $id. '&blk_id='. $id;
		$html .= '<div style="text-align: right;">'.
				 make_href('新規スレッド作成&raquo;', $href).
				 '</div>';
	}

	$href  = '/index.php?module=fbbs&action=get_all_thread&eid='. $id. '&blk_id='. $id;
	$html .= '<div style="text-align: right;">'.
			 make_href('全てのスレッドを表示&raquo;', $href).
			 '</div>';


	$html .= '<div>'. $footer. '</div>';

	return $html;
}

function mod_fbbs_block_is_mkthread($id = null) {
	$allow_type = 0;
	$c = mysql_uniq('select * from mod_enquete_allow where eid = %s', mysql_num($eid));
	if ($c) {
		$allow_type = $c['type'];
	}

	if ($allow_type == 2) {
		if (!is_joined(get_gid($id))) {
			$content .= '<div class="enquete_fwb">';
			$content .= '回答するためにはグループへの参加が必要です。';
			$content .= '</div>';
		}
	}

}

function mod_fbbs_block_get_author($uid = 0, $name = null, $mail = null, $url = null) {
	if ($uid == 0) {
		$name = isset($name) ? $name : '名無し';
		if ($mail && $mail != '') {
			$name = '<a href="mailto:'. $mail. '">'. $name. '</a>';
		}
		return $name. mod_fbbs_block_get_url($url);
	}
	else {
		$name = get_handle($uid);
		if ($mail && $mail != '') {
			$name = '<a href="mailto:'. $mail. '">'. $name. '</a>';
		}
		return $name. mod_fbbs_block_get_url($url);
	}
	return mod_fbbs_block_get_href(get_handle($uid), $url);
}

function mod_fbbs_block_get_href($str = '名無し', $url = null) {
	if ($url) {
		return '&nbsp;<a href="'. $url. '" target="_blank">'. $str. '</a>';
	}
	else {
		return $str;
	}
}

function mod_fbbs_block_get_url($url = null) {
	if ($url) {
		return '&nbsp;<a href="'. $url. '" target="_blank">URL</a>';
	}
}

function mod_fbbs_get_thread($id = 0, $view_type = 1, $view_num = 0) {
	$q = mysql_full('select * from mod_fbbs_data as d'.
					' inner join mod_fbbs_element_relation as e on d.id = e.id'.
					' where e.pid = %s'.
					' order by d.initymd desc'.
					' limit %s',
					mysql_num($id), mysql_num($view_num));

	$html .= '<ul class="mod_fbbs_thread_list">';
	while ($res = mysql_fetch_assoc($q)) {
		$b =mysql_uniq('select * from mod_fbbs_backnumber where thread_id = %s', $res['id']);

		if ($b) {
			if ((time() - strtotime($b['initymd'])) > 60 * 60 * 24 * 7) {
				continue;
			}
		}

//		$count = mod_fbbs_count($res['id']);
		$href = '/index.php?module=fbbs&action=get_thread&eid='. $res['id']. '&o=desc'. '&blk_id='. $id;
		$html .= '<li><a href="'. $href. '">'. $res['title']. '</a><!-- ('. $count. ')--></li>'. "\n";
		$html .= '<div class="mod_fbbs_thread_autor">'.
			 ' 投稿者: '. 
			 mod_fbbs_block_get_author($res['uid'], $res['name'], $res['mail'], $res['url']).
			 ' 作成日時: '.
			 date('n月j日 G時i分', tm2time($res['updymd'])).
			 '</div>';
		if ($view_type > 1) {
			$html .= '<div class="mod_fbbs_block_body">'. $res['body']. '</div>'. "\n";
		}
		else {
			$html .= '<div class="mod_fbbs_block_body_nodata"></div>'. "\n";
		}
//		$html .= mod_fbbs_get_child($res['id'], $res['id'], $view_type);
	}
	$html .= '</ul>';

	return $html;
}

function mod_fbbs_count($id) {
	$c = mysql_uniq('select count(*) as count from mod_fbbs_data where parent_id = %s',
					mysql_num($id));

	$count = isset($c) ? intval($c['count']) : 0;

	if ($count > 0) {
		$count += mod_fbbs_count($res['id']);
	}
	return $count;
}

function mod_fbbs_get_child($id = 0, $thread_id = 0, $view_type = 1) {
	$q = mysql_full('select * from mod_fbbs_data where parent_id = %s'.
					' order by initymd ',
					mysql_num($id));

	if (mysql_num_rows($q) < 1) {
		return;
	}

	$html = '<ul class="mod_fbbs_tree_list">';
	while ($res = mysql_fetch_assoc($q)) {
		$href  = '/index.php?module=fbbs&action=get_thread&eid='. $thread_id. '#res'. $res['id'];

		$html .= '<li><a href="'. $href. '">'. $res['title']. '</a></li>'. "\n";
		if ($view_type > 1) {
			$html .= '<div class="mod_fbbs_block_body">'. $res['body']. '</div>'. "\n";
		}
		$html .= mod_fbbs_get_child($res['id'], $thread_id);
		$html .= '</li>';
	}
	$html .= '</ul>';

	return $html;
}

?>
