<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';

function mod_list_u_main($id = null) {
	global $SYS_PAGE_NAVI;

	$limit = 30;

	set_navilink($limit);

	$content = array();

	//自分のマイページを一番上に表示するため
	if (is_login()) {
		$m = mysql_uniq('select * from page as d'.
						' where d.uid = %s',
						mysql_num(myuid()));
		if ($m) {
			$content[] = mod_list_u_main_href($m['uid'], $m['sitename'], $d['description']);
		}
	}

	$f = mysql_full('select d.* from page as d'.
					' inner join user on user.id = d.uid'.
					' inner join element as e'.
					' on d.id = e.id'.
					' left join unit as u'.
					' on e.unit = u.id'.
					' where'.
					' user.enable > 0'.
					' and d.uid != %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' order by d.initymd DESC limit %s, %s;',
					mysql_num(myuid()),
					mysql_num(public_status()), mysql_num(myuid()),
					mysql_num($SYS_PAGE_NAVI['offset']), mysql_num($limit));

	if ($f) {
		while ($d = mysql_fetch_array($f)) {
			$content[] = mod_list_u_main_href($d['uid'], $d['sitename'], $d['description']);
		}
	}

	return implode("\n", $content);
}

function mod_list_u_main_href($owner = 0, $sitename = '無題', $desc = '') {
	$class = 'list_u_block';
	if ($owner == myuid()) {
		$class = 'list_u_block_owner';
	}
	return '<a class="'. $class. '" href="'. '/user.php?uid='. $owner. '">'.
		   '<span>'. $sitename. '</span>'.
		   '</a>'.
		   '<div class="list_u_main_desc">'. $desc. '</div>';
}

?>
