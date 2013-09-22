<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_blog_archive_block($id = 0) {
	global $SYS_BLOG_ID;

	$q = mysql_uniq('select * from blog_archive_setting where id = %s',
					mysql_num($id));

	$blog_id = array();
	if (!$q) {
		$limit = 8;
		$b = mysql_full('select * from block where pid = %s and module = %s',
						mysql_num(get_site_id($id)), mysql_str('blog'));

		if ($b) {
			while ($r = mysql_fetch_array($b)) {
				$blog_id[] = $r['id'];
			}
		}
	}
	else {
		$limit = $q['latest_num'];
		$qq = mysql_full('select * from blog_archive_list where id = %s',
						mysql_num($id));
	
		if ($qq) {
			while ($r = mysql_fetch_array($qq)) {
				$blog_id[] = $r['blog_id'];
			}
		}
	}

	$b = mysql_exec("select d.* from blog_data as d".
					" inner join element on d.id = element.id".
					" inner join owner as o on o.id = d.id".
					" inner join block as b on d.pid = b.id".
					" left join unit on element.unit = unit.id".
					" where".
					" d.pid in (%s)".
					" and (element.unit <= %s or unit.uid = %s)".
					" order by d.initymd DESC limit %s;",
					implode(',',array_map('intval',$blog_id)),
					mysql_num(public_status($id)),
					mysql_num(myuid()), mysql_num($limit));

	$blog = array();

	if ($b) {
		while ($r = mysql_fetch_array($b)) {
			$blog[] = array(id      => $r["id"],
							blk_id  => $r['pid'],
							subject => ($r["subject"] ? $r["subject"] : '無題'),
							body    => $r["body"],
							date    => $r["initymd"]);
		}
	}

	$html = '';
	if (count($blog) > 0) {
		$html = '<div class="blog_archive_latest">新着記事</div>';
	}
	foreach ($blog as $b) {
		$date = date('n月d日 G時i分', strtotime($b['date']));
		$html .= <<<BODY
<div style="margin: 0; padding: 0">
<div style="padding: 3px;">
<div class="common_href"><a href="/index.php?module=blog&eid=${b["id"]}&blk_id=${b['blk_id']}">${b["subject"]}</a></div>
<div class="common_date">${date}</div>
</div></div>
<div style="clear: both; height: 5px;"></div>
BODY;
	}

	if (isset($SYS_BLOG_ID) && $SYS_BLOG_ID > 0) {
		$html .= 'バックナンバー';
		$html .= mod_blog_archive_get_month_index($id);
	}

	return $html;
}

function mod_blog_archive_get_month_index($id = 0) {
	$blog_id = array();
	$q = mysql_uniq('select * from blog_archive_setting where id = %s',
					mysql_num($id));
	if($q){
		$q = mysql_full('select * from blog_archive_list where id = %s',
						mysql_num($id));
	
		if ($q) {
			while ($r = mysql_fetch_array($q)) {
				$blog_id[] = $r['blog_id'];
			}
		}
	}else {
		$b = mysql_full('select * from block where pid = %s and module = %s',
						mysql_num(get_site_id($id)), mysql_str('blog'));

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

	$q = mysql_full('SELECT DATE_FORMAT(d.initymd, \'%%Y-%%m\') AS m, count(*)'.
					' FROM blog_data AS d'.
					' INNER JOIN element ON d.id = element.id'.
					' INNER JOIN owner as o ON o.id = d.id'.
					' LEFT JOIN unit ON element.unit = unit.id'.
					' WHERE'.
					' d.pid '. $pid_query.
					' and (element.unit <= %s or unit.uid = %s)'.
					' GROUP BY DATE_FORMAT(d.initymd, \'%%Y-%%m\')'.
					' ORDER BY d.initymd DESC',
					mysql_num(public_status($id)), mysql_num(myuid()));

	if (!$q) {
		return;
	}
	$html = '';
	while ($r = mysql_fetch_array($q)) {
		list($year, $month) = explode('-', $r['m']);
		$html .= '<a href="/index.php?module=blog_archive&blk_id='. $id .'&date='. $r['m']. '" class="common_href">'. $year. '年'. $month. '月'. '</a>';
	}
	return $html;
}

?>
