<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';
include_once(dirname(__FILE__). '/common.php');

function mod_tagreader_block($id) {
	$i = mysql_uniq("select * from tagreader_setting".
					" where eid = %s",
					mysql_num($id));

	$updated = date('m-d H:i', mod_tagreader_crawl($id));

	if (!$i) {
		return;
	}

	$q = mysql_full("select * from tagreader_data as d".
					" inner join element on d.article_id = element.id".
					" left join unit on element.unit = unit.id".
					" where (element.unit <= %s or unit.uid = %s)".
					" and d.eid = %s order by d.initymd DESC",
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num($id));

	$html = $sqq. '<div class="tagreader_header">'. $i["header"]. '</div>';
	if ($q) {
		$num = 0;
		while ($d = mysql_fetch_array($q)) {
			if (!viewable($d['article_id'],$d['blk_id'])) {
				continue;
			}
//			if (!exists_pid($d['article_id'])) {
//				continue;
//			}
			$href   = $d["url"];
//			$body   = $d["body"];
			$body   = mb_strimwidth(strip_tags($d["body"]), 0, 256, '...', 'UTF-8');
			$body   = trim(mb_ereg_replace("　", " ", $body));
			$date   = date('n月j日 G時i分', strtotime($d["initymd"]));
//			$date   = $d["initymd"];
			$target = '_blank';
			$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
					 '" target="'. htmlspecialchars($target, ENT_QUOTES).
					 '" class="tagreader_href" title="'. $d["sitename"]. '"><span>'.
					 htmlspecialchars($d["title"], ENT_QUOTES). '</span></a>';
			if ($i["disp_body"] > 0 && $body != '') {
				$html .= '<div class="tagreader_body">'. $body. '</div>';
			}
			$num++;
			if ($num >= $i['disp_num']) {
				break;
			}
//			$html .= '<div class="tagreader_date">'. $date. '</div>';
		}
	}
	$html .= '<div class="tagreader_footer">'. $i["footer"]. '</div>';

	return $html;
}

function viewable($eid = 0, $block_id = 0) {
	if (!mysql_uniq('select * from block where id = %s', $block_id)) {
		return false;
	}
	$o = mysql_uniq('select * from owner where id = %s',
					mysql_num($eid));
	if ($o) {
		$gid = $o['gid'];
		$uid = $o['uid'];
/*
		$u = mysql_uniq('select enable from user where id = %s',
							mysql_num($uid));
		if (!$u) {
			return false;
		}
		else {
			if ($u['enable'] < 1) {
				return false;
			}
		}
*/
		if ($gid > 0) {
			$g = mysql_uniq('select id, enable from page where gid = %s',
							mysql_num($gid));
			if (!$g) {
				return false;
			}
			if (!is_portal($gid) and $g['enable'] < 1) {
				return false;
			}
			if (!check_pmt($g['id'])) {
				return false;
			}
		}
		else {
			$m = mysql_uniq('select id from page where uid = %s',
							mysql_num($uid));
			if (!$m) {
				return false;
			}
			if (!check_pmt($m['id'])) {
				return false;
			}
		}
	}
	else {
		return false;
	}
	return check_pmt($eid);
}

?>
