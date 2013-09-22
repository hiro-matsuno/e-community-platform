<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';
include_once dirname(__FILE__). '/func.php';

function mod_enquete_block($id) {
	global $COMUNI_DEBUG, $JQUERY;

	$q = mysql_uniq('select * from enquete_status where pid = %s',
					mysql_num($id));

	if ($q) {
		$p = mysql_uniq('select * from enquete_data where id = %s',
						mysql_num($q['eid']));

		if (!$p) {
			return mod_enquete_none();
		}

		$enq_id   = $p['id'];
		$subject  = $p['subject'];
		$note     = $p['note'];
		$type     = $p['type'];
		$result   = $p['result'];
		$startymd = $p['startymd'] ? strtotime($p['startymd']) : null;
		$endymd   = $p['endymd'] ? strtotime($p['endymd']) : null;
	}
	else {
		return mod_enquete_none();
	}

	if ($startymd) {
		if (time() - $startymd < 0) {
			return mod_enquete_none();
		}
		$dstr = '実施期間: '. date('Y年m月d日H時i分', $startymd). '～';
	}
	if ($endymd) {
		$dstr .= date('Y年m月d日H時i分', $endymd);
	}
	$dstr = preg_replace('/00時00分/', '', $dstr);

//	if (is_owner($id)) {
//		$result = 1;
//	}

	$c = mysql_uniq('select count(*) as count from enquete_form_data where eid = %s',
					mysql_num($enq_id));

	if ($c['count'] == 0 || !$c) {
		return mod_enquete_none();
	}

	$html = '';

	$html .= '<h2 class="enquete_tb">'. $subject. '</h2>';
	$html .= '<div class="enquete_nb">'. $note. '</div>';
	$html .= '<div style="font-size: 0.9em; text-align: right;">'. $dstr. '</div>';

	if ($endymd) {
		if (time() - $endymd > 0) {
			if ($result == 0) {
				return mod_enquete_none();
			}
			$html .= '<div>このアンケートは終了しました。</div>';
			$html .= mod_enquete_result($enq_id, $result);
			return $html;
		}
	}

	if ($type == 0) {
		$allow_type = 0;
		$c = mysql_uniq('select * from mod_enquete_allow where eid = %s', mysql_num($enq_id));
		if ($c) {
			$allow_type = $c['type'];
		}
		if ($allow_type == 2) {
			if (!is_joined(get_gid($id))) {
				$content .= '<div class="enquete_fwb">';
				$content .= '送信するためにはグループへの参加が必要です。';
				$content .= '</div>';
				return $html. $content;
			}
		}
		else if ($allow_type == 1) {
			if (!is_login()) {
				$href = make_href('ログイン', '/login.php?type=dialog&ref='. urlencode(home_url($id)), true);
				$content .= '<div class="enquete_fwb">';
				$content .= '送信するためには'. $href. 'が必要です。';
				$content .= '</div>';

				return $html. $content;
			}
		}
		$html .= mod_enquete_print($enq_id, 0);
	}
	else {
		$html .= mod_enquete_link($enq_id);
	}

	if ($result == 2) {
		$html .= mod_enquete_result($enq_id, $result);
	}
	else if ($result == 1) {
		if (time() - intval($endymd) > 0) {
			$html .= mod_enquete_result($enq_id, $result);
		}
	}

	return $html;
}

function mod_enquete_link($eid = null) {
	$link = CONF_URLBASE. '/index.php?module=enquete&eid='. $eid;

	$allow_type = 0;
	$c = mysql_uniq('select * from mod_enquete_allow where eid = %s', mysql_num($eid));
	if ($c) {
		$allow_type = $c['type'];
	}

	if ($allow_type == 2) {
		if (!is_joined(get_gid($eid))) {
			$content .= '<div class="enquete_fwb">';
			$content .= '送信するためにはグループへの参加が必要です。';
			$content .= '</div>';
			return $content;
		}
		$content .= '<div class="enquete_fwb">';
		$content .= '<form action="'. $link. '" method="GET" class="enquete_form">';
		$content .= '<input type="hidden" name="module" value="enquete">';
		$content .= '<input type="hidden" name="eid" value="'. $eid. '">';
		$content .= '<div class="enquete_sswb"><input type="submit" value="送信画面へ" class="enquete_sbb" style="cursor: pointer;"></div>';
		$content .= '</form></div>';
	}
	else if ($allow_type == 1) {
		if (!is_login()) {
			$href = make_href('ログイン', '/login.php?type=dialog&ref='. urlencode(home_url($eid)), true);
			$content .= '<div class="enquete_fwb">';
			$content .= '送信するためには'. $href. 'が必要です。';
			$content .= '</div>';
			return $content;
		}
		$content .= '<div class="enquete_fwb">';
		$content .= '<form action="'. $link. '" method="GET" class="enquete_form">';
		$content .= '<input type="hidden" name="module" value="enquete">';
		$content .= '<input type="hidden" name="eid" value="'. $eid. '">';
		$content .= '<div class="enquete_sswb"><input type="submit" value="送信画面へ" class="enquete_sbb" style="cursor: pointer;"></div>';
		$content .= '</form></div>';
	}
	else {
		$content .= '<div class="enquete_fwb">';
		$content .= '<form action="'. $link. '" method="GET" class="enquete_form">';
		$content .= '<input type="hidden" name="module" value="enquete">';
		$content .= '<input type="hidden" name="eid" value="'. $eid. '">';
		$content .= '<div class="enquete_sswb"><input type="submit" value="送信画面へ" class="enquete_sbb" style="cursor: pointer;"></div>';
		$content .= '</form></div>';
	}
	return $content;
}

?>
