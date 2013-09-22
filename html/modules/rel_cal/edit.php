<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	default:
		input_data($eid, $pid);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;

	// 親IDチェック
	if ($pid == 0) {
			show_error('パーツIDが不明です。');
	}
	// 編集チェック
	if (!is_owner($pid)) {
		show_error('編集権限がありません。');
	}

	$f = mysql_full("select * from schedule_data as d".
					" where d.pid = %s",
					mysql_num($pid));

	$list = array();

	$list[] = array(id       => '',
					subject  => '題名',
					body     => '内容',
					startymd => '開始日時',
					endymd   => '終了日時',
					updymd  => '更新日時');
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$href = "/index.php?module=rel_cal&eid=$r[id]&blk_id=$r[pid]";
			$subject = $r['subject'] ? $r['subject'] : '無題';
			if ($r['endymd']) {
				$endymd = date('Y年m月d日<\b\r />H時i分', strtotime($r['endymd']));
			}
			else {
				$endymd = '';
			}
			$list[] = array(id      => $r['id'],
							subject => make_href($subject, $href, null, '_blank', 32),
							body    => clip_str($r['body'], 50),
							startymd => date('Y年m月d日<\b\r />H時i分', strtotime($r['startymd'])),
							endymd   => $endymd,
							updymd   => date('Y年m月d日<\b\r />H時i分', strtotime($r['updymd'])));
		}
	}

	set_return_url();

	$edit_url = '/modules/rel_cal/input.php?eid=';
	$del_url  = '/modules/rel_cal/delete.php?eid=';
	$html = create_edit_list($edit_url, $del_url, $list);

	$data = array(title   => 'スケジュールの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
