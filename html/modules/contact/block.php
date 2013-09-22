<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_contact_block($id = 0) {
	global $COMUNI_HEAD_CSSRAW;

	$d = mysql_uniq('select * from mod_contact_setting where id = %s', mysql_num($id));

	if (!$d) {
		if (is_owner($id)) {
			return 'はじめに機能設定を完了して下さい。';
		}
		else {
			return '';
		}
	}

	if (!isset($_REQUEST['module'])) {
		$COMUNI_HEAD_CSSRAW[] = $d['css'];
	}

	$html = $d['note'];

	if (mysql_uniq('select * from mod_contact_form_data where eid = %s limit 1', mysql_num($id))) {
		$html .= $d['href'];
	}
	else {
		if (is_owner($id)) {
			$html .= '<div style="background: #efefef; text-align: center; line-height: 1.5em; color: #f00;">「編集」からフォームを作成して下さい</div>';
		}
		else {
			$html .= '<div style="text-align: center;">フォーム作成中...</div>';
		}
	}

	return $html;
}

?>
