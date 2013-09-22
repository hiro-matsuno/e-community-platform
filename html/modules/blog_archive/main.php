<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_blog_archive_main($id = 0) {
	$sdate = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m');
	$blk_id = ($_REQUEST['blk_id'])?($_REQUEST['blk_id']):($_REQUEST['pid']);
	if(!$blk_id)return '';

	if (!preg_match('/^[0-9]+-[0-9]+$/', $sdate)) {
		return 'what? ';
	}

	$blog_id = array();
	$q = mysql_uniq('select * from blog_archive_setting where id = %s',
					mysql_num($id));
	if($q){
		$qq = mysql_full('select * from blog_archive_list where id = %s',
							mysql_num($blk_id));

		if ($qq) {
			while ($r = mysql_fetch_array($qq)) {
				$blog_id[] = $r['blog_id'];
			}
		}
	}else {
		$b = mysql_full('select * from block where pid = %s and module = %s',
						mysql_num(get_site_id($blk_id)), mysql_str('blog'));

		if ($b) {
			while ($r = mysql_fetch_array($b)) {
				$blog_id[] = $r['id'];
			}
		}
	}

	if (count($blog_id) > 0) {
		$pid_query = 'IN ('. implode(',', $blog_id). ')';
	}
	else {
		return '';
	}

	$q = mysql_full('SELECT d.*'.
					' FROM blog_data AS d'.
					' INNER JOIN element ON d.id = element.id'.
					' INNER JOIN owner as o ON o.id = d.id'.
					' LEFT JOIN unit ON element.unit = unit.id'.
					' WHERE'.
					' d.pid '. $pid_query.
					' and DATE_FORMAT(d.initymd, \'%%Y-%%m\') = %s'.
					' and (element.unit <= %s or unit.uid = %s)'.
					' ORDER BY d.initymd DESC',
					mysql_str($sdate),
					mysql_num(public_status($blk_id)), mysql_num(myuid()));

	if (!$q) {
		return;
	}
	list($year, $month) = explode('-', $sdate);
	$html = '<h4>'. $year. '年'. intval($month). '月</h4>';
	while ($b = mysql_fetch_array($q)) {
		$date = date('Y年n月d日 G時i分', strtotime($b['initymd']));
		$html .= <<<BODY
<div style="margin: 0; padding: 0">
<div style="padding: 3px;">
<div class="common_href"><a href="/index.php?module=blog&eid=${b["id"]}&blk_id=${b['blk_id']}">${b["subject"]}</a></div>
<div class="common_date">${date}</div>
</div></div>
<div style="clear: both; height: 5px;"></div>
BODY;
	}
	return $html;
}

?>
