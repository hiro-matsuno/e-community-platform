<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_bosai_web_block($id) {
	global $COMUNI_DEBUG, $JQUERY;

	$html = '';

	if (is_owner($id)) {
		$q = mysql_full('select a.display, count(*)'.
						' from bosai_web_auth as a'.
						' inner join bosai_web_block as b'.
 						' on a.pid = b.block_id'.
						' where b.eid = %s'.
						' group by a.display',
						mysql_num($id));
		$auth_count  = 0;
		$total_count = 0;
		if ($q) {
			while ($r = mysql_fetch_array($q)) {
				$total_count += intval($r['count(*)']);
				if ($r['display'] == 1) {
					$auth_count = intval($r['count(*)']);
				}
			}
		}

		$html .= '<div style="text-align: right; font-size: 0.9em;">'.
				 '未承認/投稿件数 ('. $auth_count. '/'. $total_count. ')</div>';
	}

	$q = mysql_full("select o.uid, rb.eid, rb.site_id, d.*, bc.count, ra.display".
					" from blog_data as d".
					" inner join element on d.id = element.id".
					" left join unit on element.unit = unit.id".
					" inner join bosai_web_block as rb".
					" on rb.block_id = d.pid".
					" inner join bosai_web_auth as ra".
					" on d.id = ra.id".
					" inner join bosai_web_count as bc".
					" on d.id = bc.id".
					" inner join owner as o".
					" on d.id = o.id".
					" where (element.unit <= %s or unit.uid = %s)".
					" and rb.eid = %s".
					" and ra.display = 2".
					" order by d.updymd desc",
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num($id));

	if ($q) {
		while ($d = mysql_fetch_array($q)) {
			$sitename = get_writer_name($d['uid'], $d['site_id']);
//			$sitehref = '/location.php?eid='. $d['site_id'];
			$href   = "/index.php?module=blog&eid=$d[id]&blk_id=$d[pid]";
			$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES). '" class="common_href"><span>'.
					 htmlspecialchars($d["subject"], ENT_QUOTES). ' (第'. $d['count']. '報)</span></a>';
			$html .= '<div class="common_date">by '. $sitename. ' at '.
					 date('n月d日 G時i分', strtotime($d["initymd"])).
					 '</div>';
		}
	}
	$html .= '<div class="common_feed">'.
		 '<a href="/modules/bosai_web/feed.php?id='. $id. '" target="_blank">'.
		 '<img src="/image/feed.png" width="16" height="16" alt="feed" border="0"></a>'.
		 '</div>';

	return $html;
}

?>
