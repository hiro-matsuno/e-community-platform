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
	default:
		input_data($eid, $pid);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT, $SYS_FORM;

	$b = mysql_uniq('select * from bosai_web_block'.
					' where block_id = %s',
					mysql_num($pid));

	$target_id = null;
	if ($b) {
		$target_id = $b['eid'];
	}
	else {
		show_error('不明なパーツです。');
	}

	$f = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s'.
					' order by d.num',
					mysql_num($target_id));

	if ($f) {
		while ($c = mysql_fetch_array($f)) {
			$option[$c['eid']] = $c['name'];
		}
	}
	else {
		$option[0] = '分類が未登録です。';
	}

	$f = mysql_full('select d.* from bosai_web_template_bysite as d'.
					' where d.pid = %s'.
					' order by d.category, d.num',
					mysql_num($pid));

	if (!$f) {
		$f = array();
	}

	$list = array();
	$list[] = array(category => '時期',
					subject => 'タイトル',
					body    => '内容');

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$subject = $r['subject'] ? $r['subject'] : '無題';
			$count = intval($r['count']) ? intval($r['count']) : 1;
			switch (intval($r['display'])) {
				case '2':
					$display = '<div style="white-space: nowrap; color: #8abfd6;">承認済</div>';
					break;
				case '1':
					$count++;
					$display = '<div style="white-space: nowrap; color: #fca890;">承認待ち</div>';
					break;
				default:
					$display = '<div style="white-space: nowrap; color: #999;">編集中</div>';
			}
			$list[] = array(id      => $r['id'],
							category => $option[$r['category']],
							subject => $r['subject'],
							body    => clip_str($r['body'], 50));
//							updymd  => date('Y年m月d日 H時i分', tm2time($r['updymd'])));
		}
	}

	set_return_url();

	$editor = array('編集' => '/modules/bosai_web/template_bysite.php?eid=',
					'削除.edit_delbtn thickbox' => '/del_content.php?module=bosai_web_bysite&eid=');


	$c = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s',
					mysql_num($pid));

	$html .= create_auth_list($editor, $list). return_button();

	$data = array(title   => '防災ウェブユーザー雛形一覧',
				  icon    => 'write',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

?>
