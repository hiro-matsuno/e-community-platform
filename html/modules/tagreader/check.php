<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/../../lib.php';

$q = mysql_full("select * from blog_data");

$html = '';
if ($q) {
	while ($d = mysql_fetch_array($q)) {
		$block_id = $d['pid'];
		if (!exists_pid($block_id)) {
			$b = mysql_uniq('select * from block where id = %s', mysql_num($d['pid']));
			if ($b) {
				$title = '<span style="color: #fc0;">pid not found but block exists. ['. $b['id']. ']'. $d["subject"];
			}
			else {
				$title = '<span style="color: #f00;">pid not found. ['. $block_id. ']'. $d["subject"];
			}
		}
		else {
			$title = ''. $d["subject"];
		}
		$href   = CONF_URLBASE. "/index.php?module=blog&eid=$d[id]&blk_id=$d[pid]";
		$target = '_blank';
		$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
				 '" target="'. htmlspecialchars($target, ENT_QUOTES).
				 '" title="'. $d["sitename"]. '"><span>'.
				 $title. '</span></a><br>';
	}
}

$data = array(title   => 'チェック',
			  icon    => 'write',
			  content => $html);

show_input($data);

function exists_pid($block_id = null) {
	if (mysql_uniq('select * from block where id = %s', mysql_num($block_id))) {
		return true;
	}
	return false;
}

?>
