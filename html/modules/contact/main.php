<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';
require_once dirname(__FILE__). '/func.php';

function mod_contact_main($id = 0) {
	global $COMUNI_HEAD_CSSRAW, $COMUNI_TPATH;

	$d = mysql_uniq('select * from mod_contact_setting where id = %s', mysql_num($id));

	if (!$d) {
		if (is_owner($id)) {
			return 'はじめに機能設定を完了して下さい。';
		}
		else {
			return '';
		}
	}

	$COMUNI_HEAD_CSSRAW[] = $d['css'];

	$COMUNI_TPATH[] = array('name' => get_block_name($id));

	if (isset($_REQUEST['finish'])) {
		return 'メールを送信しました。';
	}

	if (!isset($_REQUEST['confirm'])) {
		$html = $d['note'];
	}

	$html .= mod_contact_create_form($id);

	return $html;
}

?>
