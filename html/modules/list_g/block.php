<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_list_g_block($id = null) {
	$limit = 5;

	$content = array();
	$joined  = array();

	if (is_login()) {
		$m = mysql_full('SELECT g.*, gm.level FROM page AS g'.
						' INNER JOIN group_member AS gm ON g.gid = gm.gid'.
						' WHERE gm.uid = %s and g.enable = 1'.
						' ORDER BY gm.level DESC',
						mysql_num(myuid()));
		if ($m) {
			while ($d = mysql_fetch_array($m)) {
				$content[] = mod_list_g_block_href($d['gid'], $d['sitename'], $d['level']);
				$joined[$d['gid']] = true;
			}
		}
	}

	$portal_id = portal_gid();

	$f = mysql_full('SELECT g.* FROM page AS g'.
					' INNER JOIN element AS e'.
					' ON g.id = e.id'.
					' LEFT JOIN unit AS u ON e.unit = u.id'.
					' INNER JOIN owner AS o ON e.id = o.id'.
					' WHERE'.
					' g.gid != %s'.
					' and g.gid > 0'.
					' and g.enable = 1'.
					' and (e.unit <= %s OR u.uid = %s OR o.uid = %s)'.
					' ORDER BY RAND() LIMIT %s;',
					mysql_num($portal_id),
					mysql_num(public_status()), mysql_num(myuid()), mysql_num(myuid()),
					mysql_num($limit));
	
	if ($f) {
		$cut = false;
		while ($d = mysql_fetch_array($f)) {
			if (isset($joined[$d['gid']])) {
				continue;
			}
			$content[] = mod_list_g_block_href($d['gid'], $d['sitename'], 0);
		}
//		if ($cut == true) {
//			array_pop($content);
//		}
	}

	$content[] = '<div class="list_g_block_more">'.
				 '<a href="'. CONF_URLBASE. "/index.php?module=list_g&eid=$id&blk_id=$id". '">'.
				 'もっと見る &raquo;'.
				 '</a>'.
				 '</div>';

	return implode("\n", $content);
}

function mod_list_g_block_href($gid = 0, $sitename = '無題', $level = 0) {
	$class   = 'list_g_block';
	$sub_str = '';
	if ($level == 100) {
		$class   = 'list_g_block_owner';
		$sub_str = mod_list_g_block_href_sub($level);
	}
	return '<a class="'. $class. '" href="'. '/group.php?gid='. $gid. '">'.
		   '<span>'. $sitename. '</span>'.
		   '</a>'. $sub_str;
}

function mod_list_g_block_href_sub($level = 0) {
	$class = 'common_date';

	switch ($level) {
		case 100:
			$str = 'あなたが管理者です。';
		break;
		default:
			$str = '現在参加中です。';
	}

	return '<div class="'. $class. '">'. $str. '</div>';
}

?>
