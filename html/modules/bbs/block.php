<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');

function mod_bbs_block($id = 0) {
	$html = '';

	$f = mysql_fullpmt($id, 'mod_bbs_thread', 0, 8);

	if (!$f) {
		return '現在スレッドはありません。';
	}
	while ($res = mysql_fetch_assoc($f)) {
		$title = $res['title'];
		$href  = "/index.php?module=bbs&eid=$res[id]&blk_id=$res[pid]";
		$body  = clip_str($res['body'], 128);
		$date  = date('n月d日 G時i分', tm2time($res['updymd']));

		$html .= '<a href="'. htmlspecialchars($href, ENT_QUOTES).
				 '" class="common_href"><span>'.
				 htmlspecialchars($title, ENT_QUOTES). '</span></a>';
		$html .= '<div class="common_body">'. $body. '</div>';
		$html .= '<div class="common_date">作成日:'. $date. '</div>';
	}

	$href  = '/index.php?module=bbs&show=all&blk_id='. $id;
	$html .= '<div style="text-align: right;">'.
			 make_href('全てのスレッド&raquo;', $href).
			 '</div>';

	return $html;
}

?>
