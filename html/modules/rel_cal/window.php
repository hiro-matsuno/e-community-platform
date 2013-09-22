<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$eid  = intval($_REQUEST["eid"]);
$pid  = intval($_REQUEST["pid"]);
$date = $_REQUEST["date"];

if ($pid > 0) {
	$content = mod_rel_cal_bydate($pid);
}
else {
	$content = mod_rel_cal_byid($eid);
}

list($year, $month, $day) = split('-', $_REQUEST["date"]);

$data = array(title   => $year. '年'. $month. '月'. $day. '日のスケジュール',
			  icon    => 'write',
			  content => $content);

show_dialog2($data);

function mod_rel_cal_bydate($blk_id) {
	global $COMUNI_DEBUG, $JQUERY;

	$data = array(id       => $blk_id,
				  editlink => '',
				  title    => 'GLIST',
				  content  => '');

	$now     = date('Y/m/d H:i:s');

	$date = $_REQUEST["date"];

	// 今月の予定
	$q = mysql_full('select d.*, o.uid from schedule_data as d'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' left join owner as o on o.id = d.id'.
					' where DATE_FORMAT(d.startymd, \'%%Y-%%m-%%d\') <= %s'.
					' and DATE_FORMAT(d.endymd, \'%%Y-%%m-%%d\') >= %s'.
					' and d.pid = %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' order by d.startymd DESC',
					mysql_str($date), mysql_str($date),
					mysql_num($blk_id),
					mysql_num(public_status($blk_id)), mysql_num(myuid()));

	while ($s = mysql_fetch_array($q)) {
		$id       = $s["id"];
		$owner  = get_handle($s["uid"]);
		$subject  = $s["subject"];
		$body     = clip_str($s["body"], 300);
		$startymd = date('Y年m月d日 H時i分', strtotime($s["startymd"]));
		$endymd   = $s["endymd"];

		if ($endymd) {
			$date = $startymd . ' から '. date('Y年m月d日 H時i分', strtotime($endymd)). ' まで';
		}
		else {
			$date = $startymd;
		}

		$buff .= <<<__CONTENT__
<div class="schedule_d_subject">${subject}</div>
<div class="schedule_d_date">${date}<br>&nbsp;&nbsp;&nbsp;&nbsp;By ${owner}</div>
<div class="schedule_d_body">${body}
<div style="text-align: right;"><a href="/index.php?module=rel_cal&eid=${id}&blk_id=$blk_id" target="_parent">詳細を見る&raquo;</a></div>
</div>
<hr>
__CONTENT__;
		;
	}

	return $buff;
}

function mod_rel_cal_byid($id) {
	global $COMUNI_DEBUG, $JQUERY;

	$data = array(id       => $id,
				  editlink => '',
				  title    => 'GLIST',
				  content  => '');

	$now     = date('Y/m/d H:i:s');

	list($year, $month, $day) = split('-', $_REQUEST["date"]);

	$year  = intval($year);
	$month = intval($month);
	$day   = intval($day);

	if (!check_pmt($id)) {
		return '403';
	}

	// 今月の予定
	$s = mysql_uniq("select * from schedule_data".
					" where id = %s",
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

	$comment = load_comment($id);

	$map = view_map($id);

	$buff .= <<<__CONTENT__
<div class="schedule_d_subject">${subject}</div>
<div class="schedule_d_date">${date}</div>
<div class="schedule_d_body">${body}${map}</div>
<hr>
${comment}
__CONTENT__;
	;

	return $buff;
}


?>
