<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');

function mod_mailmag_block($id = 0) {
	$html = '';

	$s = mysql_uniq("select * from mailmag_setting".
					" where eid = %s",
					mysql_num($id));

	if (!$s) {
		if (is_owner($id)) {
			return 'はじめに基本設定を行って下さい。';
		}
		return $html;
	}

	$add_query = '';
	$limit = $s['disp_num'];
	if ($limit > 0) {
		$add_query = ' limit '. $limit;
	}
	$q = mysql_full('select * from mailmag_data as d'.
					' where d.pid = %s'.
					' order by d.initymd desc'.
					$add_query,
					mysql_num($id));

	$html .= '<div class="mailmag_header">'. $s["header"]. '</div>';

	if ($q && is_joined(get_gid($id))) {
		while ($d = mysql_fetch_array($q)) {
			$href    = "/index.php?eid=$d[id]&blk_id=$d[pid]";
			$subject = mb_strimwidth(strip_tags($d["subject"]), 0, 30, '...', 'UTF-8');
			$body    = mb_strimwidth(strip_tags($d["body"]), 0, 300, '...', 'UTF-8');
			$date    = date('n月j日 G時i分', tm2time($d["initymd"]));

			$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
					 '" class="mailmag_href" title="'. $subject. '"><span>'.
					 htmlspecialchars($subject, ENT_QUOTES). '</span></a>';
			if ($s["disp_body"] > 0 && $body != '') {
				$html .= '<div class="mailmag_body">'. $body. '</div>';
			}
			$html .= '<div class="common_date">by '.get_nickname(get_uid($d['id'])) .' at '.$date. '</div>';
		}
	}
	$html .= '<div class="mailmag_footer">'. $s["footer"]. '</div>';

	if (join_level(get_gid($id)) >= $s['write_level']) {
		$html .= make_href('メール作成&raquo;', '/modules/mailmag/post.php?eid='. $id, true);
	}
/*
	else {
		if (is_owner($id)) {
			$html .= make_href('メール作成&raquo;', '/modules/mailmag/post.php?eid='. $id);
		}
	}
*/

//	$data["content"] = $html;

	return $html;
}

?>
