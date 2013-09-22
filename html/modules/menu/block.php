<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_menu_block($id) {
	$q = mysql_full('select m.* from menu_data as m'.
					' left join mod_menu_data_pos as pos on m.id = pos.id'.
					' where m.pid = %s order by pos.position, m.hpos',
					mysql_num($id));

	if ($q) {
		$html = '';
		while ($d = mysql_fetch_array($q)) {
			if (!check_pmt($d["id"])) {
				continue;
			}
			$href = $d["href"];
			if (!preg_match('/^https?:\/\//', $href, $match)) {
				$href = CONF_URLBASE. $href;
			}
			$target = isset($d["target"]) ? $d["target"] : "_self";
			$html .= '<a href="'. $href.
					 '" target="'. $target.
					 '" class="menu_href"><span>'.
					 $d["title"]. '</span></a>';
		}
	}

	return $html;
}

?>
