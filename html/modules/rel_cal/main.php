<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';

function mod_rel_cal_main($id) {
	global $SYS_BLOG_ID;

	$now     = date('Y/m/d H:i:s');

	list($year, $month, $day) = split('-', $_REQUEST["date"]);

	$year  = intval($year);
	$month = intval($month);
	$day   = intval($day);

	if (!check_pmt($id)) {
		return '403';
	}

	// 今月の予定
	$s = mysql_uniq("select d.*, o.uid from schedule_data as d".
					' inner join owner as o on o.id = d.id'.
					" where d.id = %s",
					mysql_num($id));

	if ($s) {
		$id       = $s["id"];
		$subject  = $s["subject"];
		$body     = $s["body"];
		$startymd = date('Y年m月d日 H時i分', strtotime($s["startymd"]));
		$endymd   = $s["endymd"];
	}
	else {
		return 'miss';
	}

	if ($endymd) {
		$date = $startymd . ' から '. date('Y年m月d日 H時i分', strtotime($endymd)). ' まで';
	}
	else {
		$date = $startymd;
	}

	$writer = get_handle($s['uid']);
	$comment = load_comment($id);

	$map = view_map($id);

	$buff .= <<<__CONTENT__
<div class="common_href">${subject}</div>
<div class="common_date">${date}<br>by ${writer}</div>
<div class="common_body">${body}${map}</div>
<hr>
${comment}
__CONTENT__;
	;

	return $buff;
}

?>
