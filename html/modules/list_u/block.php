<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_list_u_block($id = null) {
	$limit = 5;

	$content = array();

	//自分のマイページを一番上に表示するため
	if (is_login()) {
		$m = mysql_uniq('select * from page'.
						' where uid = %s',
						mysql_num(myuid()));
		if ($m) {
			$content[] = mod_list_u_block_href($m['uid'], $m['sitename']);
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
					' order by d.initymd DESC limit %s;',
					mysql_num(myuid()),
					mysql_num(public_status()), mysql_num(myuid()),
					mysql_num($limit));

	if ($f) {
		$cut = true;
		while ($d = mysql_fetch_array($f)) {
			$content[] = mod_list_u_block_href($d['uid'], $d['sitename']);
		}
	}

	$content[] = '<div class="list_u_block_more">'.
				 '<a href="'. CONF_URLBASE. "/index.php?module=list_u&eid=$id&blk_id=$id". '">'.
				 'もっと見る &raquo;'.
				 '</a>'.
				 '</div>';

	return implode("\n", $content);
}

function mod_list_u_block_href($owner = 0, $sitename = '無題') {
	$class = 'list_u_block';
	if ($owner == myuid()) {
		$class = 'list_u_block_owner';
	}
	return '<a class="'. $class. '" href="'. '/user.php?uid='. $owner. '">'.
		   '<span>'. $sitename. '</span>'.
		   '</a>';
}

?>
