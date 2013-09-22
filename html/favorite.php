<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/lib.php';

if (!is_login()) {
	show_error('ログインして下さい。');
}

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'resend':
		;
	default:
		print_form();
}

exit(0);

function print_form() {
	global $SYS_FORM;

	$g = mysql_full('select g.*, gm.level from group_member as gm'.
					' inner join page as g on gm.gid = g.gid'.
					' where gm.uid = %s'.
					' group by g.gid',
					mysql_num(myuid()));
	
	$joined_group = array();
	if ($g) {
		while ($r = mysql_fetch_array($g)) {
			$joined_group[] = array('gid' => $r['gid'],
									'sitename' => $r['sitename'],
									'level' => $r['level']);
		}
	}

	$html .= '<h4>参加中のグループページ</h4>';
	$html .= '<ul>';
	foreach ($joined_group as $j) {
		$html .= '<li>';
		$html .= make_href($j['sitename'], '/index.php?gid='. $j['gid'], false, '_parent');
		if ($j['level'] == 100) {
			$html .= '[管理者]';
		}
		$html .= '</li>';
	}
	$html .= '</ul>';

	$data = array('title'   => 'お気に入り',
				  'icon'    => 'favorite',
				  'content' => $html);

	show_dialog($data);
}

?>
