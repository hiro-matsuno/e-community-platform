<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$eid = intval($_REQUEST["eid"]);
$pid = intval($_REQUEST["pid"]);

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;

	// 親IDチェック
	if ($pid == 0) {
//		if (!($pid = get_pid($eid))) {
			show_error('パーツIDが不明です。');
		//}
	}

	$f = mysql_full("select rb.eid, rb.site_id, d.*, ra.display".
					" from blog_data as d".
					" inner join reporter_block as rb".
					" on rb.block_id = d.pid".
					" left join reporter_auth as ra".
					" on d.id = ra.id".
					" where rb.eid = %s".
					' order by d.updymd desc',
					mysql_num($pid));

	if (!$f) {
		$f = array();
	}

	$list = array();

	$list[] = array(display => '状態',
					subject => '題名',
					updymd => '更新日時');

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$href = "/index.php?module=reporter&eid=$r[id]&blk_id=$r[pid]";
			$subject = $r['subject'] ? $r['subject'] : '無題';
			switch (intval($r['display'])) {
				case '2':
					$display = '<div style="white-space: nowrap; color: #8abfd6;">承認済</div>';
					break;
				case '1':
					$display = '<div style="white-space: nowrap; color: #fca890;">承認待ち</div>';
					break;
				default:
					$display = '<div style="white-space: nowrap; color: #999;">編集中</div>';
			}
			$sitename = get_site_name($r['site_id']);
//			$sitehref = '/location.php?eid='. $r['site_id'];
			$sitehref = '/index.php?site_id='. $r['site_id'];
			$list[] = array(id      => $r['id'],
							display => $display,
							subject => clip_str($subject, 48).
									   '<div style="text-align: right;">from '.
									   make_href($sitename, $sitehref, null, '_blank', 48).
									   '</div>',
							body    => clip_str($r['body'], 50),
							updymd  => date('Y年m月d日 H時i分', tm2time($r['updymd'])));
		}
	}

	set_return_url();

	$editor = array('校正/承認' => "/index.php?module=reporter&blk_id=$pid&eid=");

	$html = create_auth_list($editor, $list);

	$data = array(title   => '市民レポーター投稿一覧',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
