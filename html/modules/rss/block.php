<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');
include_once(dirname(__FILE__). '/common.php');

function mod_rss_block($id = 0) {
	$html = '';

	$updated = date('m-d H:i', mod_rss_reload($id));

	$html .= '<!-- Updated: '. $updated. ' -->';

	$i = mysql_uniq("select * from rss_setting".
					" where eid = %s",
					mysql_num($id));

	if (!$i) {
		return $html;
	}

	switch($i['disp_type']) {
		case 2:
			$order = 'initymd desc';
		break;
		default:
			$order = 'id';
	}
	$q = mysql_full("select * from rss_data".
					" where eid = %s order by ${order}".
					" limit %s",
					mysql_num($id), mysql_num($i['disp_num']));

	if (!$q) {
		return $html;
	}

	$html .= '<div class="rss_header">'. $i["header"]. '</div>';
	$sitename = '';
	while ($d = mysql_fetch_array($q)) {
		$href   = $d["url"];
//		$body   = $d["body"];
		$body   = mb_strimwidth(strip_tags($d["body"]), 0, 256, '...', 'UTF-8');
		$body   = trim(mb_ereg_replace("　", " ", $body));
		$date   = date('n月j日 G時i分', strtotime($d["initymd"]));
//		$date   = $d["initymd"];
		$target = '_blank';
		if (strpos($href, CONF_URLBASE) !== false) {
			$target = '_top';
		}
		if ($i['disp_type'] < 2 && $i['disp_title'] == 1) {
			if ($sitename != $d["sitename"]) {
				$html .= '<div class="rss_sitename">'. $d["sitename"]. '</div>';
				$sitename = $d["sitename"];
			}
		}
		$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
				 '" target="'. htmlspecialchars($target, ENT_QUOTES).
				 '" class="rss_href" title="'. $d["sitename"]. '"><span>'.
				 htmlspecialchars($d["title"], ENT_QUOTES). '</span></a>';
		if ($i["disp_body"] > 0 && $body != '') {
			$html .= '<div class="rss_body">'. $body. '</div>';
		}
		$html .= '<div class="rss_date">'. $date. '</div>';
	}
	$html .= '<div class="rss_footer">'. $i["footer"]. '</div>';

//	$data["content"] = $html;

	return $html;
}

function mod_rss_reload($id = 0) {
	global $SYS_REFRESH;

	if ($SYS_REFRESH == true) {
		mod_rss_crawl($id);
		return time();
	}

	$q = mysql_uniq('select rct.updymd from rss_setting as rs'.
					' inner join rss_crawl_time as rct on rs.eid = rct.eid'.
					' where rs.eid = %s',
					mysql_num($id));

	if ($q) {
		if ($q["updymd"]) {
			$cur_time = strtotime($q["updymd"]);
			if ((time() - $cur_time) < MOD_RSS_RELOAD) {
				return $cur_time;
			}
		}
		mod_rss_crawl($id);
	}
	else {
		mod_rss_crawl($id);
		return time();
	}

	return time();
}

?>
