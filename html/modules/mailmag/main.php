<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');

function mod_mailmag_main($id = 0) {
	$html = '';
	
	$q = mysql_uniq('select pid from mailmag_data where id = %s',
					mysql_num($id));
	$pid = $q['pid'];
	$s = mysql_full("select * from mailmag_setting".
					" where eid = %s",
					mysql_num($pid));

	if (!$s) {
		return $html;
	}

	$d = mysql_uniq('select * from mailmag_data'.
					' where id = %s',
					mysql_num($id));

	if (!$d) {
		return $html;
	}

	$subject = $d["subject"];
	$body    = $d["body"];

	$date   = date('n月j日 G時i分', tm2time($d["initymd"]));

	$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
			 '" class="mailmag_href" title="'. $subject. '"><span>'.
			 htmlspecialchars($subject, ENT_QUOTES). '</span></a>';
	$html .= '<div class="mailmag_body">'. $body. '</div>';
	$html .= '<div class="mailmag_date">'. $date. '</div>';

	return $html;
}

?>
