<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_reporter_block($id) {
	global $COMUNI_DEBUG, $JQUERY;

	$q = mysql_full("select o.uid, rb.eid, rb.site_id, d.*, ra.display".
					" from blog_data as d".
					" inner join element on d.id = element.id".
					" left join unit on element.unit = unit.id".
					" inner join reporter_block as rb".
					" on rb.block_id = d.pid".
					" inner join reporter_auth as ra".
					" on d.id = ra.id".
					" inner join owner as o".
					" on d.id = o.id".
					" where (element.unit <= %s or unit.uid = %s)".
					" and rb.eid = %s".
					" and ra.display = 2".
					" order by d.initymd DESC",
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num($id));
					
	$html = '';
	if ($q) {
		while ($d = mysql_fetch_array($q)) {
			$sitename = get_writer_name($d['uid'], $d['site_id']);
//			$sitehref = '/location.php?eid='. $d['site_id'];
			$href   = "/index.php?module=blog&eid=$d[id]&blk_id=$d[pid]";
			$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES). '" class="common_href"><span>'.
					 htmlspecialchars($d["subject"], ENT_QUOTES). '</span></a>';
			$html .= '<div class="common_date">by '. $sitename. ' at '.
					 date('n月d日 G時i分', strtotime($d["initymd"])).
					 '</div>';
		}
	}
	$html .= '<div class="common_feed">'.
		 '<a href="/modules/reporter/feed.php?id='. $id. '" target="_blank">'.
		 '<img src="/image/feed.png" width="16" height="16" alt="feed" border="0"></a>'.
		 '</div>';

	return $html;
}

?>
