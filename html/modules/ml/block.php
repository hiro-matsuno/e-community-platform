<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once(dirname(__FILE__). '/config.php');
include_once(dirname(__FILE__). '/func.php');

function mod_ml_block($id = 0) {
	$html = '';

	$q = mysql_uniq("select * from mod_ml_setting".
					" where id = %s",
					mysql_num($id));

	if (!$q) {
		if (is_owner($id)) {
			return 'はじめに基本設定を行って下さい。';
		}
		return $html;
	}

	$base_href = CONF_URLBASE. '/index.php?module=ml&pid='. $id. '&eid='. $id. '&action=';

	$html .= '<div class="mod_ml_header">'. $q["header"]. '</div>';
	$html .= '<h3 class="mod_ml_title">'. $q['title']. '</h3>';
	$html .= '<div class="mod_ml_desc">'. $q["desc"]. '</div>';
	if (mod_ml_is_join($id)) {
		$html .= '<div class="mod_ml_joind">';
		$html .= '(現在参加中)';
		$html .= '</div>';
		$html .= '<div class="mod_ml_post">';
		$html .= make_href('このメッセージングリストへ投稿&raquo;', $base_href. 'post');
		$html .= '</div>';

		if ($q['archive_pmt'] > 0) {
			$html .= '<div class="mod_ml_regist">';
			$html .= make_href('これまでのメールを参照する&raquo;', $base_href. 'backnumber');
			$html .= '</div>';
		}
		$html .= '<div class="mod_ml_quit">';
		$html .= make_href('このメッセージングリストから退会&raquo;', $base_href. 'quit');
		$html .= '</div>';
	}
	else {
		if (is_login()) {
			$html .= '<div class="mod_ml_regist">';
			$html .= make_href('このメッセージングリストに参加&raquo;', $base_href. 'regist');
			$html .= '</div>';
			if ($q['archive_pmt'] > 1) {
				$html .= '<div class="mod_ml_backnumber">';
				$html .= make_href('これまでのメールを参照する&raquo;', $base_href. 'backnumber');
				$html .= '</div>';
			}
		}
		else if ($q['archive_pmt'] > 1) {
			$html .= '<div class="mod_ml_backnumber">';
			$html .= make_href('これまでのメールを参照する&raquo;', $base_href. 'backnumber');
			$html .= '</div>';
		}
	}
	$html .= '<div class="mod_ml_footer">'. $q["footer"]. '</div>';

	mod_ml_tmp_css();

	return $html;
}

?>
